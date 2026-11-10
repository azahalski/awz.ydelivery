<?php
namespace Awz\Ydelivery;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Order;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Application;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Sale\Result;

class Checker {

    public static function runJob($points){
        if(!is_array($points)) return;
        foreach($points as $point){
            PvzTable::updatePvz($point);
        }
    }

    public static function agentGetPickpoints($force=false){

        if(Option::get(Handler::MODULE_ID, "UPDATE_PVZ_BG", "Y", "") == 'Y' && !$force) {
            return "\\Awz\\Ydelivery\\Checker::agentGetPickpoints();";
        }

		$isUtf8 = Application::getInstance()->isUtfMode();

        $deliveryProfileList = Helper::getActiveProfileIds(Helper::DOST_TYPE_PVZ);

        if(!empty($deliveryProfileList)){
            foreach($deliveryProfileList as $profileId=>$profileName){
                $api = Helper::getApiByProfileId($profileId);

                //if($api->isTest()) continue;

				if (!$isUtf8){
				$api->setStandartJson(true);
				}
                $pvzResult = $api->getPickpoints();
				if (!$isUtf8){
					$api->setStandartJson(false);
				}
                if($pvzResult->isSuccess()){
                    $pvzData = $pvzResult->getData();
                    foreach($pvzData['result']['points'] as $point){
						if (!$isUtf8){
							$point = Json::decode(json_encode($point));
						}
                        PvzTable::updatePvz($point);
                    }
                }

                break;
            }
        }

        return "\\Awz\\Ydelivery\\Checker::agentGetPickpoints();";

    }

    /**
     * перевод статусов яндекса в не дублирующие статусы модуля
     * (костыль с добавлением предыдущего статуса)
     *
     * @param $histStatus
     * @return array
     */
    public static function unDoubleStatus($histStatus){
        $dublicateStatusList = [
            'DELIVERY_PROCESSING_STARTED',
            'DELIVERY_LOADED',
            'DELIVERY_AT_START',
            'DRAFT',
            'DELIVERY_TRANSPORTATION_RECIPIENT',
            'DELIVERY_AT_START_SORT',
            'DELIVERY_UPDATED_BY_RECIPIENT',
            'DELIVERY_UPDATED_BY_DELIVERY'
        ];

        $statList = [];
        $prevStatus = [];
        foreach($histStatus as $stat){
            if(isset($statList[$stat['status']]) || in_array($stat['status'], $dublicateStatusList)){
                $statusCode = $prevStatus['status'].'_'.$stat['status'];
                $statusMess = $prevStatus['description'].', '.$stat['description'];
                if(isset($statList[$statusCode])){
                    $statusCode = strtoupper(Helper::translit($prevStatus['description'])).'_'.$statusCode;
                    $statusMess = $prevStatus['description'].', '.$statusMess;
                }
                $statList[$statusCode] = $stat;
                $statList[$statusCode]['hash'] = md5(serialize($stat));
                $statList[$statusCode]['status_m'] = $statusCode;
                $statList[$statusCode]['status_d'] = $statusMess;
            }else{
                $statList[$stat['status']] = $stat;
                $statList[$stat['status']]['hash'] = md5(serialize($stat));
                $statList[$stat['status']]['status_m'] = $stat['status'];
                $statList[$stat['status']]['status_d'] = $stat['description'];

            }
            $prevStatus = $stat;
        }
        return $statList;
    }

    /**
     * @throws NotImplementedException
     * @throws ArgumentNullException
     * @throws LoaderException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function agentGetStatus($disableTimer=false){

        if(!Loader::includeModule('sale')){
            return "\\Awz\\Ydelivery\\Checker::agentGetStatus();";
        }

        if($disableTimer){
            $r = PvzTable::getList(['select'=> ['ID'],'limit'=>1])->fetch();
            if(!$r){
                self::agentGetPickpoints();
            }
        }

        $deliveryProfileList = Helper::getActiveProfileIds();
        $deliveryProfileListEx = Helper::getActiveProfileIds(Helper::DOST_TYPE_EX);

        $statusList = unserialize(Option::get(Handler::MODULE_ID, 'YD_STATUSLIST', '', ''));
        $statusListEx = unserialize(Option::get(Handler::MODULE_ID, 'YD_STATUSLIST_EX', '', ''));

        $checkedOrder = [];
        foreach($deliveryProfileList as $profileId=>$profileName){

            $isExtProfile = isset($deliveryProfileListEx[$profileId]);

            $activeChecker = Option::get(
                Handler::MODULE_ID,
                'CHECKER_ON_'.$profileId,
                '',''
            );

            if($activeChecker != 'Y') continue;
            $api = Helper::getApiByProfileId($profileId);

            $opt_interval = Option::get(
                Handler::MODULE_ID,
                'CHECKER_INTERVAL_'.$profileId,
                '',''
                );
            $opt_interval = floatval($opt_interval)*60*60;
            if(!$opt_interval) $opt_interval = 0;
            if($disableTimer) $opt_interval = 0;
            $opt_maxcount = Option::get(
                Handler::MODULE_ID,
                'CHECKER_COUNT_'.$profileId,
                '',''
            );
            if(!$opt_maxcount) $opt_maxcount = 0;
            $opt_CHECKER_FIN = unserialize(
                Option::get(
                Handler::MODULE_ID,
                'CHECKER_FIN_'.$profileId,
                '',''
                )
            );
            if(!is_array($opt_CHECKER_FIN)) $opt_CHECKER_FIN = [];

            $filter = [
                "!=HISTORY_FIN"=>'Y',
                '=ORD.CANCELED'=>'N',
                '<=LAST_DATE'=> DateTime::createFromTimestamp(time()-$opt_interval)
            ];
            if(!empty($checkedOrder)){
                $filter['!ORDER_ID'] = $checkedOrder;
            }

            $rOffers = OffersTable::getList(
                [
                    'select'=> ["*",'ORD_STATUS'=>'ORD.STATUS_ID','ORD_TRACKING_NUMBER'=>'ORD.TRACKING_NUMBER'],
                    'filter'=>$filter,
                    'limit'=>15,
                    'order'=> ['ID'=>'DESC']
                ]
            );

            $val_stat_disabled = Option::get(Handler::MODULE_ID, "CHECKER_FIN_DSBL", "", '');
            $val_stat_disabled = unserialize($val_stat_disabled);
            if(!is_array($val_stat_disabled)) $val_stat_disabled = [];

            $statusLinked = [];
            $statusLinkedHash = [];
            if($isExtProfile){
                $cursor = Option::get(Handler::MODULE_ID, "CHECKER_CURSOR_".$profileId, "", '');
                $cursor = '';
                $ydResClaimRes = $api->offersHistoryEx($cursor);
                if(!$ydResClaimRes->isSuccess()) continue;
                $ydResClaim = $ydResClaimRes->getData();
                foreach($ydResClaim['result']['events'] as $ev){
                    if($ev['change_type']!='status_changed') continue;
                    $claim_id = $ev['claim_id'];
                    if(!isset($statusLinked[$claim_id])){
                        $statusLinked[$claim_id] = [];
                    }
                    $hash = md5(serialize($ev));
                    $statDesc = isset($statusListEx[$ev['new_status']]) ? $statusListEx[$ev['new_status']] : $ev['new_status'];
                    $statusLinked[$claim_id][$hash] = [
                        'status'=>$ev['new_status'],
                        'description'=>$statDesc,
                        'timestamp'=>strtotime($ev['updated_ts']),
                        'hash'=>$hash,
                        'status_m'=>$ev['new_status'],
                        'status_d'=>$statDesc
                    ];
                    $statusLinkedHash[] = $hash;
                }
            }

            $claimUps = unserialize(Option::get(Handler::MODULE_ID, "CHECKER_CURSOR_UPS_".$profileId, "a:0:{}", ''));
            $findOrders = false;
            while($data = $rOffers->fetch()){
                $findOrders = true;
                $extUpTypeVersion = 1;
                if($isExtProfile && $extUpTypeVersion==1){
                    if(isset($statusLinked[$data['OFFER_ID']])) $extUpTypeVersion=2;
                    //echo'<pre>';print_r($extUpTypeVersion);echo'</pre>';
                    //die();
                    //print_r($finUp['lastStatusCode']);
                    //die();
                }

                $finUp = $data['HISTORY'];
                if(!$finUp) $finUp = [];
                if(!isset($finUp['hist'])) $finUp['hist'] = [];
                if(!$finUp['errors']) $finUp['errors'] = [];

                $order = Order::load($data['ORDER_ID']);
                if(!$order) continue;

                if(!Helper::getProfileId($order)) {
                    OffersTable::update(
                        ['ID'=>$data['ID']],
                        ['HISTORY_FIN'=>'Y']
                    );
                    continue;
                }

                if(Helper::getProfileId($order) != $profileId) continue;

                $checkedOrder[] = $data['ORDER_ID'];

                if($isExtProfile && $extUpTypeVersion==1){
                    $ydRes = $api->offerInfoEx($data['OFFER_ID']);
                }elseif(!$isExtProfile){
                    $ydRes = $api->offerHistory($data['OFFER_ID']);
                }

                $finUp['count_resp'] = intval($finUp['count_resp']) + 1;

                $noUpdateDate = false;
                $startStatusCode = $finUp['lastStatusCode'];

                if($isExtProfile && $extUpTypeVersion==2){
                    foreach($statusLinked[$data['OFFER_ID']] as $statRow){
                        $data['LAST_STATUS'] = $statRow['status_m'];
                    }
                    $lastStatusCode = '';
                    $upStatList = false;
                    foreach($statusLinked[$data['OFFER_ID']] as $statRow){

                        $lastStatusCode = $statRow['status_m'];
                        if($lastStatusCode && !isset($statusListEx[$lastStatusCode])){
                            $statusListEx[$lastStatusCode] = $statRow['status_d'];
                            $upStatList = true;
                        }
                        $hash = $statRow['hash'];
                        if(!in_array($hash, $claimUps)) $claimUps[] = $hash;
                        if(!isset($finUp['hist'][$hash])){
                            $finUp['hist'][$hash] = $statRow;
                            $noUpdateDate = true;
                            //не обновляем дату т.к. могут быть еще новые статусы
                            break;
                        }
                    }
                    $finUp['lastStatusCode'] = $lastStatusCode;
                    //обновление списка статусов, для настроек
                    if($upStatList)
                        Option::set(Handler::MODULE_ID, 'YD_STATUSLIST_EX', serialize($statusListEx), '');

                }elseif($ydRes->isSuccess()){
                    $ydData = $ydRes->getData();
                    if($isExtProfile && $extUpTypeVersion==1) {

                        $lastStatusCode = $ydData['result']['status'];
                        $statRow = [
                            'status' => $lastStatusCode,
                            'description' => isset($statusListEx[$lastStatusCode]) ? $statusListEx[$lastStatusCode] : $lastStatusCode,
                            'timestamp' => strtotime($ydData['result']['updated_ts']),
                            'hash' => md5($lastStatusCode),
                            //'status_m' => 'CREATED_IN_PLATFORM',
                            //'status_d' => 'Принят',
                        ];
                        $statRow['status_m'] = $statRow['status'];
                        $statRow['status_d'] = $statRow['description'];
                        //echo'<pre>';print_r($ydData);echo'</pre>';
                        //die();


                        $upStatList = false;
                        if ($lastStatusCode && !isset($statusListEx[$lastStatusCode])) {
                            $statusListEx[$lastStatusCode] = $lastStatusCode;
                            $upStatList = true;
                        }

                        $hash = $statRow['hash'];
                        if (!isset($finUp['hist'][$hash])) {
                            $finUp['hist'][$hash] = $statRow;
                        }

                        $finUp['lastStatusCode'] = $lastStatusCode;
                        if($lastStatusCode)
                            $data['LAST_STATUS'] = $lastStatusCode;
                        //обновление списка статусов, для настроек
                        if ($upStatList)
                            Option::set(Handler::MODULE_ID, 'YD_STATUSLIST_EX', serialize($statusListEx), '');

                    }
                    elseif(!$isExtProfile){

                        $undStatus = self::unDoubleStatus($ydData['result']['state_history']);

                        foreach($undStatus as $statRow){
                            if(in_array($statRow['status_m'], $val_stat_disabled)) continue;
                            if($statRow['status_m'])
                                $data['LAST_STATUS'] = $statRow['status_m'];
                        }
                        $lastStatusCode = '';
                        $upStatList = false;
                        foreach($undStatus as $statRow){

                            if(in_array($statRow['status_m'], $val_stat_disabled)) {
                                $hash = $statRow['hash'];
                                if(!isset($finUp['hist'][$hash])) {
                                    $finUp['hist'][$hash] = $statRow;
                                }
                                continue;
                            }

                            $lastStatusCode = $statRow['status_m'];
                            if($lastStatusCode && !isset($statusList[$lastStatusCode])){
                                $statusList[$lastStatusCode] = $statRow['status_d'];
                                $upStatList = true;
                            }
                            $hash = $statRow['hash'];
                            if(!isset($finUp['hist'][$hash])){
                                $finUp['hist'][$hash] = $statRow;
                                $noUpdateDate = true;
                                //не обновляем дату т.к. могут быть еще новые статусы
                                break;
                            }
                        }
                        $finUp['lastStatusCode'] = $lastStatusCode;
                        //обновление списка статусов, для настроек
                        if($upStatList)
                            Option::set(Handler::MODULE_ID, 'YD_STATUSLIST', serialize($statusList), '');

                    }
                }
                else{
                    $finUp['errors'][] = $ydRes->getErrorMessages();
                    $finUp['count_error'] = intval($finUp['count_error']) + 1;
                }

                $finalize = 'N';
                if($finUp['count_resp']>$opt_maxcount) {
                    $finalize = 'Y';
                    //проблема с заказом, статус не финализировался
                }
                //if(count($finUp['errors'])>48) {
                    //проблема с заказом, статус не финализировался, много ошибок
                //}
                if($finUp['lastStatusCode']){
                    if(in_array($finUp['lastStatusCode'],$opt_CHECKER_FIN)) $finalize = 'Y';
                }
                if($finalize == 'Y'){
                    $noUpdateDate = false;
                }
                $arChange = [
                    'HISTORY'=>$finUp,
                    'HISTORY_FIN'=>$finalize,
                    'LAST_STATUS'=>$data['LAST_STATUS']
                ];
                if(!$noUpdateDate){
                    $arChange['LAST_DATE'] = DateTime::createFromTimestamp(time());
                }

                OffersTable::update(
                    ['ID'=>$data['ID']],
                    $arChange
                );
                //echo'<pre>';print_r($arChange);echo'</pre>';
                //die();

                if($finUp['lastStatusCode']){
                    $lStatus = $finUp['lastStatusCode'];
                    $newStatus = false;
                    $opt = Option::get(
                        Handler::MODULE_ID,
                        'md5_'.md5('PARAMS_STATUS_TO_'.$profileId.'_'.$lStatus),
                        '', ''
                    );
                    if(!$opt) {
                        $newStatus = false;
                    }elseif($opt == 'DISABLE'){
                        $newStatus = false;
                    }elseif($opt){
                        $newStatus = $opt;
                    }
                    $ordStatus = $data['ORD_STATUS'];
                    $optOrd = Option::get(
                        Handler::MODULE_ID,
                        'md5_'.md5('PARAMS_STATUS_FROM_'.$profileId.'_'.$lStatus),
                        '', ''
                    );
                    $optOrd = unserialize($optOrd);
                    if(empty($optOrd)) {
                        $newStatus = false;
                    }elseif(in_array('ALL',$optOrd)){

                    }elseif(in_array($ordStatus,$optOrd)){

                    }else{
                        $newStatus = false;
                    }

                    //$startStatusCode


                    $event = new Event(
                        Handler::MODULE_ID,
                        "onBeforeStatusUpdate",
                        ['order'=>$order,
                            'ordStatus'=>$ordStatus,
                            'startStatus'=>$startStatusCode,
                            'newOrdStatus'=>$newStatus,
                            'newStatus'=>$lStatus
                        ]
                    );
                    $event->send();
                    if ($event->getResults()) {
                        foreach ($event->getResults() as $evenResult) {
                            if ($evenResult->getType() == EventResult::SUCCESS) {
                                $r = $evenResult->getParameters();
                                if(isset($r['newOrdStatus'])){
                                    $newStatus = $r['newOrdStatus'];
                                }
                                if(isset($r['lastDate'])){
                                    OffersTable::update(
                                        ['ID'=>$data['ID']],
                                        [
                                            'LAST_DATE'=>$r['lastDate']
                                        ]
                                    );
                                }
                                if(isset($r['result']) && ($r['result'] instanceof \Bitrix\Main\Result)){
                                    if(!$r['result']->isSuccess()) {
                                        $finUp['errors'][] = $r['result']->getErrorMessages();
                                        OffersTable::update(
                                            ['ID'=>$data['ID']],
                                            [
                                                'HISTORY'=>$finUp
                                            ]
                                        );
                                    }
                                }
                            }
                        }
                    }

                    $chekerUpdate = [];
                    $chekerUpdateErr = [];
                    if($newStatus){
                        if($newStatus != $ordStatus) {

                            $order->setField('STATUS_ID', $newStatus);
                            $result = $order->save();
                            /* @var $result Result */
                            if(!isset($finUp['setstatus'])){
                                $finUp['setstatus'] = [];
                            }
                            if(!$result->isSuccess()){
                                $finUp['errors'][] = $result->getErrorMessages();
                                $finUp['setstatus'][] = [
                                    time(), $newStatus,
                                    $finUp['lastStatusCode'], 'error:'.count($finUp['errors'])
                                ];
                                $chekerUpdateErr = $result->getErrorMessages();
                            }else{
                                $finUp['setstatus'][] = [
                                    time(), $newStatus,
                                    $finUp['lastStatusCode']
                                ];
                                $chekerUpdate = [time(), $newStatus];
                            }
                            OffersTable::update(
                                ['ID'=>$data['ID']],
                                [
                                    'HISTORY'=>$finUp
                                ]
                            );

                        }
                    }

                    $event = new Event(
                        Handler::MODULE_ID,
                        "onOfterStatusUpdate",
                        [
                            'order'=>$order,
                            'ordStatus'=>$ordStatus,
                            'newOrdStatus'=>$newStatus,
                            'startStatus'=>$startStatusCode,
                            'newStatus'=>$lStatus,
                            'chekerUpdate'=>$chekerUpdate,
                            'chekerUpdateErr'=>$chekerUpdateErr
                        ]
                    );
                    $event->send();


                }

                //die();

            }
            $claimAll = true;
            foreach($statusLinkedHash as $hash){
                if(!in_array($hash, $claimUps)) $claimAll = false;
            }
            if((!$claimAll || !$findOrders) && isset($ydResClaim['result']['cursor'])){
                $claimUps = [];
                Option::set(Handler::MODULE_ID, "CHECKER_CURSOR_".$profileId, $ydResClaim['result']['cursor'], '');
            }
            Option::set(Handler::MODULE_ID, "CHECKER_CURSOR_UPS_".$profileId, serialize($claimUps), '');
        }

        return "\\Awz\\Ydelivery\\Checker::agentGetStatus();";

    }

}