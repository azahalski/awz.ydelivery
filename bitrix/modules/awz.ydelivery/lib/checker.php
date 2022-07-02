<?php
namespace Awz\Ydelivery;

use Bitrix\Main\Config\Option;
use Bitrix\Sale\Order;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Application;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

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
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function agentGetStatus($disableTimer=false){

        if(!\Bitrix\Main\Loader::includeModule('sale')){
            return "\\Awz\\Ydelivery\\Checker::agentGetStatus();";
        }

        if($disableTimer){
            $r = PvzTable::getList(array('select'=>array('ID'),'limit'=>1))->fetch();
            if(!$r){
                self::agentGetPickpoints();
            }
        }

        $deliveryProfileList = Helper::getActiveProfileIds();

        $statusList = unserialize(Option::get(Handler::MODULE_ID, 'YD_STATUSLIST', '', ''));

        $checkedOrder = array();
        foreach($deliveryProfileList as $profileId=>$profileName){

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
                )*60*60;
            if($disableTimer) $opt_interval = 0;
            $opt_maxcount = Option::get(
                Handler::MODULE_ID,
                'CHECKER_COUNT_'.$profileId,
                '',''
            );
            $opt_CHECKER_FIN = unserialize(
                Option::get(
                Handler::MODULE_ID,
                'CHECKER_FIN_'.$profileId,
                '',''
                )
            );

            $filter = array(
                "!=HISTORY_FIN"=>'Y',
                '=ORD.CANCELED'=>'N',
                '<=LAST_DATE'=>\Bitrix\Main\Type\DateTime::createFromTimestamp(time()-$opt_interval)
            );
            if(!empty($checkedOrder)){
                $filter['!ORDER_ID'] = $checkedOrder;
            }

            $r = OffersTable::getList(
                array(
                    'select'=>array("*",'ORD_STATUS'=>'ORD.STATUS_ID','ORD_TRACKING_NUMBER'=>'ORD.TRACKING_NUMBER'),
                    'filter'=>$filter,
                    'limit'=>15,
                    'order'=>array('ID'=>'DESC')
                )
            );

            while($data = $r->fetch()){

                $finUp = $data['HISTORY'];
                if(!$finUp) $finUp = array();
                if(!isset($finUp['hist'])) $finUp['hist'] = array();
                if(!$finUp['errors']) $finUp['errors'] = array();

                $order = Order::load($data['ORDER_ID']);
                if(!$order) continue;


                if(Helper::getProfileId($order) != $profileId) continue;

                $checkedOrder[] = $data['ORDER_ID'];

                $ydRes = $api->offerHistory($data['OFFER_ID']);

                $finUp['count_resp'] = intval($finUp['count_resp']) + 1;

                $noUpdateDate = false;
                $startStatusCode = $finUp['lastStatusCode'];
                if($ydRes->isSuccess()){
                    $ydData = $ydRes->getData();

                    //$data['LAST_STATUS']
                    foreach($ydData['result']['state_history'] as $statRow){
                        if($statRow['status'])
                            $data['LAST_STATUS'] = $statRow['status'];
                    }
                    $lastStatusCode = '';
                    $upStatList = false;
                    foreach($ydData['result']['state_history'] as $statRow){
                        $lastStatusCode = $statRow['status'];
                        if($lastStatusCode && !isset($statusList[$lastStatusCode])){
                            $statusList[$lastStatusCode] = $statRow['description'];
                            $upStatList = true;
                        }
                        $hash = md5(serialize($statRow));
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

                }else{
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
                $arChange = array(
                    'HISTORY'=>$finUp,
                    'HISTORY_FIN'=>$finalize,
                    'LAST_STATUS'=>$data['LAST_STATUS']
                );
                if(!$noUpdateDate){
                    $arChange['LAST_DATE'] = \Bitrix\Main\Type\DateTime::createFromTimestamp(time());
                }
                OffersTable::update(
                    array('ID'=>$data['ID']),
                    $arChange
                );

                if($finUp['lastStatusCode']){
                    $lStatus = $finUp['lastStatusCode'];
                    $newStatus = false;
                    $opt = Option::get(
                        Handler::MODULE_ID,
                        'PARAMS_STATUS_TO_'.$profileId.'_'.$lStatus,
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
                        'PARAMS_STATUS_FROM_'.$profileId.'_'.$lStatus,
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
                        array('order'=>$order,
                            'ordStatus'=>$ordStatus,
                            'startStatus'=>$startStatusCode,
                            'newOrdStatus'=>$newStatus,
                            'newStatus'=>$lStatus
                        )
                    );
                    $event->send();
                    if ($event->getResults()) {
                        foreach ($event->getResults() as $evenResult) {
                            if ($evenResult->getType() == EventResult::SUCCESS) {
                                $r = $evenResult->getParameters();
                                if(isset($r['newOrdStatus']) && $r['newOrdStatus']){
                                    $newStatus = $r['newOrdStatus'];
                                }
                                if(isset($r['result']) && ($r['result'] instanceof \Bitrix\Main\Result)){
                                    if(!$r['result']->isSuccess()) {
                                        $finUp['errors'][] = $r['result']->getErrorMessages();
                                        OffersTable::update(
                                            array('ID'=>$data['ID']),
                                            array(
                                                'HISTORY'=>$finUp
                                            )
                                        );
                                    }
                                }
                            }
                        }
                    }

                    $chekerUpdate = array();
                    $chekerUpdateErr = array();
                    if($newStatus){
                        if($newStatus != $ordStatus) {

                            $order->setField('STATUS_ID', $newStatus);
                            $result = $order->save();
                            /* @var $result \Bitrix\Sale\Result */
                            if(!$result->isSuccess()){
                                $finUp['errors'][] = $result->getErrorMessages();
                                $chekerUpdateErr = $result->getErrorMessages();
                            }else{
                                if(!isset($finUp['setstatus'])){
                                    $finUp['setstatus'] = array();
                                }
                                $finUp['setstatus'][] = array(time(), $newStatus);
                                $chekerUpdate = array(time(), $newStatus);
                            }
                            OffersTable::update(
                                array('ID'=>$data['ID']),
                                array(
                                    'HISTORY'=>$finUp
                                )
                            );

                        }
                    }

                    $event = new Event(
                        Handler::MODULE_ID,
                        "onOfterStatusUpdate",
                        array(
                            'order'=>$order,
                            'ordStatus'=>$ordStatus,
                            'newOrdStatus'=>$newStatus,
                            'startStatus'=>$startStatusCode,
                            'newStatus'=>$lStatus,
                            'chekerUpdate'=>$chekerUpdate,
                            'chekerUpdateErr'=>$chekerUpdateErr
                        )
                    );
                    $event->send();


                }

                //die();

            }

        }

        return "\\Awz\\Ydelivery\\Checker::agentGetStatus();";

    }

}