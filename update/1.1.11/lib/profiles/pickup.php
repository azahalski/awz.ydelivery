<?php
namespace Awz\Ydelivery\Profiles;

use Awz\Ydelivery\Handler;
use Awz\Ydelivery\Helper;
use Awz\Ydelivery\PvzTable;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Security;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\EntityPropertyValue;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Shipment;

Loc::loadMessages(__FILE__);

class Pickup extends Base
{
    protected static $isProfile = true;
    protected $parent = null;

    public static $cachePoints = array();

    public function __construct(array $initParams)
    {
        if(empty($initParams["PARENT_ID"]))
            throw new ArgumentNullException('initParams[PARENT_ID]');
        parent::__construct($initParams);
        $this->parent = Manager::getObjectById($this->parentId);
        if(!($this->parent instanceof Handler))
            throw new ArgumentNullException('parent is not instance of \Awz\Ydelivery\Handler');
        if(isset($initParams['PROFILE_ID']) && intval($initParams['PROFILE_ID']) > 0)
            $this->serviceType = intval($initParams['PROFILE_ID']);
    }

    public static function getClassTitle()
    {
        return Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_NAME');
    }

    public static function getClassDescription()
    {
        return Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_DESC');
    }

    public function getParentService()
    {
        return $this->parent;
    }

    public function isCalculatePriceImmediately()
    {
        return $this->getParentService()->isCalculatePriceImmediately();
    }

    public static function isProfile()
    {
        return self::$isProfile;
    }

    public function isCompatible(Shipment $shipment)
    {
        $calcResult = self::calculateConcrete($shipment);
        return $calcResult->isSuccess();
    }

    protected function getConfigStructure()
    {
        $result = array(
            "MAIN" => array(
                'TITLE' => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_INTG'),
                'DESCRIPTION' => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_INTG_DESC'),
                'ITEMS' => array(
                    'TEST_MODE' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_TEST_MODE'),
                        "DEFAULT" => 'Y'
                    ),
                    'TOKEN' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_TOKEN'),
                        "DEFAULT" => ''
                    ),
                    'TOKEN_TEST' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_TOKEN_TEST'),
                        "DEFAULT" => ''
                    ),
                    'STORE_ID' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_SOURCE'),
                        "DEFAULT" => ''
                    ),
                    'STORE_ID_TEST' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_SOURCE_TEST'),
                        "DEFAULT" => ''
                    ),
                    'NDS' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_NDS'),
                        "DEFAULT" => '0'
                    ),
                    'BTN_CLASS' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_BTN_CLASS'),
                        "DEFAULT" => 'btn btn-primary'
                    ),
                    'ERROR_COST_DSBL' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_DSBL1'),
                        "DEFAULT" => 'Y'
                    ),
                    'ERROR_COST_DSBL_SROK' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_DSBL2'),
                        "DEFAULT" => 'Y'
                    ),
                    'ERROR_COST' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_COST_DEF'),
                        "DEFAULT" => '500.00'
                    ),
                    'WEIGHT_DEFAULT' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_WEIGHT_DEF'),
                        "DEFAULT" => '3000'
                    ),
                    /*'PRED_DEFAULT' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_DEM_DEF'),
                        "DEFAULT" => '10'
                    ),*/
                    'PRED_DEFAULT_DX' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_DEM_DEF_DX'),
                        "DEFAULT" => '10'
                    ),
                    'PRED_DEFAULT_DY' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_DEM_DEF_DY'),
                        "DEFAULT" => '10'
                    ),
                    'PRED_DEFAULT_DZ' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_DEM_DEF_DZ'),
                        "DEFAULT" => '10'
                    ),
                    'ADD_HOUR' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_ADD_HOUR'),
                        "DEFAULT" => '3'
                    ),
					'SROK_FROM_STARTDAY' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_SROK_FROM_STARTDAY'),
                        "DEFAULT" => 'N'
                    ),
                    'CACHE_TTL_GEOID' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_CACHE_TTL_GEOID'),
                        "DEFAULT" => '2592000'
                    ),
                    'CACHE_TTL_COST' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_CACHE_TTL_COST'),
                        "DEFAULT" => '604800'
                    ),
                    'CACHE_TTL_SROK' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_CACHE_TTL_SROK'),
                        "DEFAULT" => '3600'
                    ),
                    'CACHE_TTL_POINTS' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_CACHE_TTL_POINTS'),
                        "DEFAULT" => '3600'
                    ),
                    'CACHE_TTL_POINTS2' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_CACHE_TTL_POINTS2'),
                        "DEFAULT" => '604800'
                    ),
                    'SET_PVZ_AUTO_EXPERIMENTAL' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SET_PVZ_AUTO_EXPERIMENTAL'),
                        "DEFAULT" => 'N'
                    ),
                    'SROK_DAYS_EMPTYPVZ' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SROK_DAYS_EMPTYPVZ'),
                        "DEFAULT" => '0'
                    ),
                    'ADD_PRICE'=> array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_SETT_ADD_PRICE'),
                        "DEFAULT" => 0
                    )
                )
            )
        );
        return $result;
    }

    protected function calculateConcrete(Shipment $shipment = null)
    {

        $logOrder = false;
        $isMarket = false;
        if(Option::get(Handler::MODULE_ID, 'YM_TRADING_ON_'.$this->getId(), 'N', '')=='Y'){
            $rawInput = file_get_contents('php://input');
            if($rawInput && strpos($rawInput,'{')!==false){
                try{
                    $postData = Json::decode($rawInput);
                    if(isset($postData['order']['businessId']) || isset($postData['cart']['businessId'])){
                        $isMarket = true;
                    }
                }catch (\Exception $e){

                }
            }
        }

        $config = $this->getConfigValues();
        $api = Helper::getApiFromProfile($this);
        if($api->isTest()){
            $config['MAIN']['STORE_ID'] = $config['MAIN']['STORE_ID_TEST'];
        }

        $result = new CalculationResult();

        $weight = $shipment->getWeight();
        $order = $shipment->getCollection()->getOrder();
        $items = Helper::getItems($order, $this->getId());
        //echo'<pre>';print_r($items);echo'</pre>';
        if(!$weight) {
            $weight = 0;
            foreach($items as $item){
                $weight += $item['physical_dims']['weight_gross'] * $item['count'];
            }
        }

        $maxWd = 0;
        $allWd = 0;
        foreach($items as $item){
            if(!isset($item['physical_dims']['dx'], $item['physical_dims']['dy'], $item['physical_dims']['dz'])){
                continue;
            }
            if($item['physical_dims']['dx'] > $maxWd) $maxWd = $item['physical_dims']['dx'];
            if($item['physical_dims']['dy'] > $maxWd) $maxWd = $item['physical_dims']['dy'];
            if($item['physical_dims']['dz'] > $maxWd) $maxWd = $item['physical_dims']['dz'];
            $allWd_ = $item['physical_dims']['dx'] + $item['physical_dims']['dy'] + $item['physical_dims']['dz'];
            if($allWd_ > $allWd) $allWd = $allWd_;
        }

        /*
         * Условия почты РФ:  (отключено в модуле)
         *
         * (Если доставка курьером до двери)
         * Д-Ш-В - 60-60-60 (см)
         * Сумма трёх сторон 140 см
         * Макс. Вес 29 кг.
         *
         * (Если доставка в отделение почты)
         * Д-Ш-В - 60-60-60 (см)
         * Сумма трех сторон 140 см
         * Макс. Вес 20 кг.
         *
         * ВАЖНО: оценочная стоимость не должна быть ниже суммы: заказ + доставка.
         *
         * ВАЖНО: для получения нужен обязательно паспорт и ФИО получателя
         * */

        /*
         * Ограничения для одного заказа при доставке в пункты выдачи:
         * Максимальный вес — 30 кг.
         * Максимальная длина одной стороны — 150 см.
         * Максимальная сумма длин всех сторон — 300 см.
         *
         * Доставка до ПСТ
         * Максимальная стоимость заказа 250 000 рублей.
         * Максимальный вес заказа 20 кг
         * Максимальные габариты заказа 118 см по сумме 3х сторон, одна сторона не превышает 40 см
         */

        $disableTerminal = false;
        if($weight>20000) $disableTerminal = true;

        if($maxWd>40 || $allWd>118){
            $disableTerminal = true;
        }

        if($allWd>300 || $maxWd>150 || $weight>30000){
            $result->addError(new Error(Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_ERR_WD')));
            return $result;
        }

        $props = $order->getPropertyCollection();
        $locationCode = $props->getDeliveryLocation()->getValue();
        if($locationCode && (strlen($locationCode) == strlen(intval($locationCode)))) {
            if ($loc = LocationTable::getRowById($locationCode)) {
                $locationCode = $loc['CODE'];
            }
        }
        $locationName = '';
        $locationGeoId = '';
        if($locationCode) {
            $res = LocationTable::getList(array(
                'filter' => array(
                    '=CODE' => $locationCode,
                    '=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                    '=PARENTS.TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                ),
                'select' => array(
                    'I_ID' => 'PARENTS.ID',
                    'I_NAME_LANG' => 'PARENTS.NAME.NAME',
                    'I_TYPE_CODE' => 'PARENTS.TYPE.CODE',
                    'I_TYPE_NAME_LANG' => 'PARENTS.TYPE.NAME.NAME',
                ),
                'order' => array(
                    'PARENTS.DEPTH_LEVEL' => 'asc'
                )
            ));
            while ($item = $res->fetch()) {
                if ($locationName) {
                    $locationName .= ', ' . $item['I_NAME_LANG'];
                } else {
                    $locationName = $item['I_NAME_LANG'];
                }
            }
        }

        $event = new Event(
            Handler::MODULE_ID, "onAfterLocationNameCreate",
            array(
                'shipment'=>$shipment,
                'calcResult'=>$result,
                'profileId'=>$this->getId(),
                'order'=>$order,
                'location'=>$locationName
            )
        );
        $event->send();
        if ($event->getResults()) {
            foreach ($event->getResults() as $evenResult) {
                if ($evenResult->getType() == EventResult::SUCCESS) {
                    $r = $evenResult->getParameters();
                    if(isset($r['location'])){
                        $locationName = $r['location'];
                    }
                }
            }
        }

        if(!$locationName){
            $result->addError(new Error(Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_ERR_REGION')));
            return $result;
        }
        $res = LocationTable::getList(array(
            'filter' => array(
                '=CODE' => $locationCode,
                '=EXTERNAL.SERVICE.CODE' => 'YAMARKET',
            ),
            'select' => array(
                'EXTERNAL.*',
                //'EXTERNAL.SERVICE.*'
            )
        ))->fetch();
        if(isset($res['SALE_LOCATION_LOCATION_EXTERNAL_XML_ID'])){
            $locationGeoId = $res['SALE_LOCATION_LOCATION_EXTERNAL_XML_ID'];
        }

        $pointId = false;

        foreach($props as $prop){
            if($prop->getField('CODE') == Helper::getPropPvzCode($this->getId())){
                if($prop->getValue()){
                    $pointId = $prop->getValue();
                }
            }
        }

        $pointHtml = '';
        $request = Context::getCurrent()->getRequest();
        if($request->get('AWZ_YD_POINT_ID')){
            $pointId = preg_replace('/([^0-9A-z\-])/i', '', $request->get('AWZ_YD_POINT_ID'));
        }
        if($isMarket){
            $rawInput = file_get_contents('php://input');
            if($rawInput && strpos($rawInput,'{')!==false){
                try{
                    if(isset($postData['order']['delivery']['outlet']['code'])){
                        $pointId = $postData['order']['delivery']['outlet']['code'];
                    }
                }catch (\Exception $e){

                }
            }
        }

        $pointData = array();
        if($pointId){
            $pointData = PvzTable::getPvz($pointId);
        }
        if($pointId){
            $blnRes = Helper::getBaloonHtml($pointId, true);
            if($blnRes->isSuccess()){
                $blnData = $blnRes->getData();
                $pointHtml = $blnData['html'];
            }
        }


        $data = array(
            //'client_price'=>1000,
            //'total_assessed_price'=>1000,
            'tariff'=>'self_pickup',
            'total_weight'=>(int)$weight,
            'source'=>array(),
            'destination'=>array(
                //'address'=>$locationName
                //'geo_id'=>$locationGeoId
            )
        );

        if(!$locationGeoId){
            $ttl = intval($config['MAIN']['CACHE_TTL_GEOID']);
            if(!$ttl) $ttl = 2592000;
            $api->setCacheParams(md5(serialize(array($config, $locationName, 'geo_id'))), $ttl);
            $geoIdResult = $api->geo_id($locationName);
            if($geoIdResult->isSuccess()){
                $geoIdData = $geoIdResult->getData();
                if(isset($geoIdData['result']['variants'][0]['geo_id'])){
                    $locationGeoId = $geoIdData['result']['variants'][0]['geo_id'];
                }
            }else{
                $api->cleanCache();
            }
        }

        if(isset($pointData['PVZ_ID'])){
            $data['destination']['platform_station_id'] = $pointData['PVZ_ID'];
        }else{
            $data['destination']['address'] = $locationName;
            if($locationGeoId){
                $ttl = intval($config['MAIN']['CACHE_TTL_POINTS2']);
                if(!$ttl) $ttl = 604800;
                $api->setCacheParams(md5(serialize(array($data, $config, $locationGeoId, 'pickpoints'))), $ttl);
                $pickpointsResult = $api->getPickpoints(array('geo_id'=>intval($locationGeoId)));
                if($pickpointsResult->isSuccess()){
                    $pickpoints = $pickpointsResult->getData();

                    $allPoints = array();
                    foreach($pickpoints['result']['points'] as $point){
                        if(!in_array($point['type'],array('pickup_point'))) continue;
                        $allPoints[] = $point['id'];
                    }

                    self::$cachePoints[$this->getId()][$locationGeoId] = $allPoints;

                    if(isset($pickpoints['result']['points'])){
                        foreach($pickpoints['result']['points'] as $point){
                            if(!in_array($point['type'],array('terminal','pickup_point'))) continue;
                            $data['destination'] = array('platform_station_id'=>$point['id']);
                            if($point['type']=='pickup_point'){
                                break;
                            }
                        }
                    }

                    $resMinPoints = PvzTable::getList(array(
                        'select'=>array('PVZ_ID'),
                        'filter'=>array('=PVZ_ID'=>$allPoints, '>DOST_DAY'=>0),
                        'limit'=>1,
                        'order'=>array('DOST_DAY'=>'ASC')
                    ))->fetch();
                    if($resMinPoints){
                        $data['destination'] = array('platform_station_id'=>$resMinPoints['PVZ_ID']);
                    }

                }else{
                    $api->cleanCache();
                }

                $pvzCandidate = Helper::getPointIdFromGeoId($locationGeoId, $config);
                if($pvzCandidate){
                    $data['destination'] = array('platform_station_id'=>$pvzCandidate);
                }
            }

        }

        if($config['MAIN']['STORE_ID']){
            $data['source'] = array('platform_station_id'=>$config['MAIN']['STORE_ID']);
        }elseif($config['MAIN']['STORE_ADRESS']){
            $data['source'] = array('address'=>$config['MAIN']['STORE_ADRESS']);
        }

        $ttl = intval($config['MAIN']['CACHE_TTL_COST']);
        if(!$ttl) $ttl = 604800;
        $api->setCacheParams(md5(serialize(array($data, $config, 'calc'))), $ttl);
        $r = $api->calc($data);
        if($r->isSuccess()){
            $calkData = $r->getData();
            if($calkData['result']['message']){
                $r->addError(new Error($calkData['result']['message']));
            }
        }else{
            $api->cleanCache();
        }

        if(!$r->isSuccess()){
            if($config['MAIN']['ERROR_COST_DSBL']=='Y'){
                $result->addErrors($r->getErrors());
                return $result;
            }else{
                $result->setDescription('<p style="color:red;">'.Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_ERR_COST').'</p>');

                global $USER;
                if($USER->IsAdmin()){
                    $result->setDescription($result->getDescription()."<br>### ".implode("; ",$r->getErrorMessages())." ###");
                }

                $result->setDeliveryPrice($config['MAIN']['ERROR_COST']);
                $result->setPeriodDescription('-');
                return $result;
            }
        }

        $calkData = $r->getData();
        if($calkData['result']['pricing_total']){
            $ttl = intval($config['MAIN']['CACHE_TTL_SROK']);
            if(!$ttl) $ttl = 3600;
            $pvzCandidate = Helper::getPointIdFromGeoId($locationGeoId, $config);
            if(!$pvzCandidate && isset($data['destination']['platform_station_id'])){
                $pvzCandidate = $data['destination']['platform_station_id'];
            }
            if($pvzCandidate && !isset($pointData['PVZ_ID'])){
                $pointData = PvzTable::getPvz($pvzCandidate);
            }
            $api->setCacheParams(md5(serialize(array($pointData, $data, $config, 'grafik'))), $ttl);
            if(isset($pointData['PVZ_ID'])){
                $r = $api->grafik(array('station_id'=>$config['MAIN']['STORE_ID'], 'self_pickup_id'=>$pointData['PVZ_ID']));
            }elseif($pvzCandidate){
                $r = $api->grafik(array('station_id'=>$config['MAIN']['STORE_ID'], 'self_pickup_id'=>$pvzCandidate));
            }else{
                $r = $api->grafik(array('station_id'=>$config['MAIN']['STORE_ID'], 'full_address'=>$locationName));
            }
            if(!$r->isSuccess()){
                $api->cleanCache();
            }
            //print_r($r->getData());
            if(!$r->isSuccess()){
                if($locationGeoId && isset($pointData['PVZ_ID']))
                    Helper::getPointIdFromGeoId($locationGeoId, $config, (string) $pointData['PVZ_ID'], 0, true);
            }
            if($r->isSuccess()){
                $grafikData = $r->getData();
                if(isset($grafikData['result']['offers']) && !empty($grafikData['result']['offers'])){
                    //$result->addData();
                    $tmpData = $result->getTmpData();
                    if(!is_array($tmpData)) $tmpData = array();
                    $tmpData['offers'] = $grafikData['result']['offers'];
                    $result->setTmpData($tmpData);
                    foreach($grafikData['result']['offers'] as $offer){
						if($config['MAIN']['SROK_FROM_STARTDAY']=='Y'){
                            $offer['from'] = date('c',strtotime(date('d.m.Y',strtotime($offer['from'])+intval($config['MAIN']['ADD_HOUR'])*60*60)));
                        }
                        $fromDay = ceil((strtotime($offer['from']) - time())/86400);
                        if($fromDay>0){
                            $result->setPeriodFrom($fromDay);
                            $result->setPeriodDescription($fromDay.' '.Loc::getMessage("AWZ_YDELIVERY_D"));
                            if($locationGeoId && isset($pointData['PVZ_ID']))
                                Helper::getPointIdFromGeoId($locationGeoId, $config, (string) $pointData['PVZ_ID'], $fromDay);
                        }
                        $toDay = ceil((strtotime($offer['from']) - time())/86400);
                        if($toDay>0 && $toDay!=$fromDay){
                            $result->setPeriodTo($toDay);
                            $result->setPeriodDescription($fromDay.'-'.$toDay.' '.Loc::getMessage("AWZ_YDELIVERY_D"));
                        }

                        break;
                    }
                    if($config['MAIN']['SROK_DAYS_EMPTYPVZ'] && !$request->get('AWZ_YD_POINT_ID')){
                        $result->setPeriodFrom(intval($config['MAIN']['SROK_DAYS_EMPTYPVZ']));
                        $result->setPeriodTo(intval($config['MAIN']['SROK_DAYS_EMPTYPVZ']));
                        $result->setPeriodDescription(intval($config['MAIN']['SROK_DAYS_EMPTYPVZ']).' '.Loc::getMessage("AWZ_YDELIVERY_D"));
                    }
                }elseif($config['MAIN']['ERROR_COST_DSBL_SROK'] == 'Y'){
                    if($locationGeoId && isset($pointData['PVZ_ID']))
                        Helper::getPointIdFromGeoId($locationGeoId, $config, (string) $pointData['PVZ_ID'], 0, true);
                    $result->addError(
                        new Error(
                            Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_ERR_NOGRAF')
                        )
                    );
                    return $result;
                }
            }elseif($config['MAIN']['ERROR_COST_DSBL_SROK'] == 'Y'){
                $result->addError(
                    new Error(
                        Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_ERR_NOGRAF')
                    )
                );
                return $result;
            }

            if(!$config['MAIN']['ADD_PRICE']) $config['MAIN']['ADD_PRICE'] = 0;
            $calkData['result']['pricing_total'] = (float) $calkData['result']['pricing_total'];
            if(mb_strpos($config['MAIN']['ADD_PRICE'],'%')){
                $calkData['result']['pricing_total'] = round($calkData['result']['pricing_total'],2) + intval($config['MAIN']['ADD_PRICE'])*round($calkData['result']['pricing_total'],2)*0.01;
            }else{
                $calkData['result']['pricing_total'] = round($calkData['result']['pricing_total'],2) + intval($config['MAIN']['ADD_PRICE']);
            }
            $calkData['result']['pricing_total'] = round($calkData['result']['pricing_total'], 2);


            $result->setDeliveryPrice(
                roundEx(
                    $calkData['result']['pricing_total'],
                    SALE_VALUE_PRECISION
                )
            );
        }

        $signer = new Security\Sign\Signer();

        $signedParameters = $signer->sign(base64_encode(serialize(array(
            'address'=>$locationName,
            'geo_id'=>$locationGeoId,
            'profile_id'=>$this->getId(),
            's_id'=>bitrix_sessid(),
            'terminal'=>$disableTerminal ? 'N' : 'Y',
            'user'=>'',
            'order'=>''
        ))));



        $buttonHtml = '<a id="AWZ_YD_POINT_LINK" class="'.$config['MAIN']['BTN_CLASS'].'" href="#" onclick="window.awz_yd_modal.show(\''.Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_BTN_OPEN').'\',\''.$signedParameters.'\');return false;">'.Loc::getMessage('AWZ_YDELIVERY_PROFILE_PICKUP_BTN_OPEN').'</a><div id="AWZ_YD_POINT_INFO">'.$pointHtml.'</div>';
        $result->setDescription($result->getDescription().
            '<!--btn-ydost-start-->'.
            $buttonHtml
            .'<!--btn-ydost-end-->'
        );

        $disableWriteDate = false;
        $event = new Event(
            Handler::MODULE_ID, "OnCalcBeforeReturn",
            array('shipment'=>$shipment, 'calcResult'=>$result)
        );
        $event->send();
        if ($event->getResults()) {
            foreach ($event->getResults() as $evenResult) {
                if ($evenResult->getType() == EventResult::SUCCESS) {
                    $r = $evenResult->getParameters();
                    if(isset($r['disableWriteDate'])){
                        $disableWriteDate = $r['disableWriteDate'];
                    }
                    if(isset($r['result']) || isset($r['RESULT'])){
                        $rOb = isset($r['result']) ? $r['result'] : $r['RESULT'];
                        if($rOb instanceof Result){
                            if(!$rOb->isSuccess()) {
                                foreach ($rOb->getErrors() as $error) {
                                    $result->addError($error);
                                }
                            }
                        }
                    }
                }
            }
        }

        //DATE_CODE_
        $request = Context::getCurrent()->getRequest();
        if(!$request->isAdminSection() && !$disableWriteDate){
            $AWZ_YD_POINT_DATE = date('d.m.Y', time() + $result->getPeriodFrom()*86400);
            if($this->getId() == Helper::getProfileId($order, Helper::DOST_TYPE_ALL)){
                $code = Helper::getPropDateCode($this->getId());
                if($code && ($order->getField('DELIVERY_ID') == $this->getId())){
                    /* @var EntityPropertyValue $prop*/
                    foreach($props as $prop){
                        if($prop->getField('CODE') == $code){
                            $prop->setValue($AWZ_YD_POINT_DATE);
                            break;
                        }
                    }
                }
            }
        }

        return $result;

    }

    public static function onBeforeAdd(array &$fields = array()): Result
    {
        if(!$fields['LOGOTIP']){
            $fields['LOGOTIP'] = Handler::getLogo();
        }
        return new Result();
    }
}