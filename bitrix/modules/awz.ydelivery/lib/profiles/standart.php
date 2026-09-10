<?php
namespace Awz\Ydelivery\Profiles;

use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Awz\Ydelivery\Helper;
use Awz\Ydelivery\Handler;
use Bitrix\Main\Result;
use Bitrix\Main\Security;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\EntityPropertyValue;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Shipment;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class Standart extends Base
{
    protected static $isProfile = true;
    protected $parent = null;

    protected static $isCalculatePriceImmediately = true;

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
        $this->parent = Manager::getObjectById($this->parentId);
    }

    public static function getClassTitle()
    {
        return Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_NAME');
    }

    public static function getClassDescription()
    {
        return Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_DESC');
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
                'TITLE' => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_INTG'),
                'DESCRIPTION' => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_INTG_DESC'),
                'ITEMS' => array(
                    'TEST_MODE' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_TEST_MODE'),
                        "DEFAULT" => 'Y'
                    ),
                    'TOKEN' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_TOKEN'),
                        "DEFAULT" => ''
                    ),
                    'TOKEN_TEST' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_TOKEN_TEST'),
                        "DEFAULT" => ''
                    ),
                    'STORE_ID' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_SOURCE'),
                        "DEFAULT" => ''
                    ),
                    'STORE_ID_TEST' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_SOURCE_TEST'),
                        "DEFAULT" => ''
                    ),
                    'NDS' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_NDS'),
                        "DEFAULT" => '0'
                    ),
                    'BTN_CLASS' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_BTN_CLASS'),
                        "DEFAULT" => 'btn btn-primary'
                    ),
                    'SHOW_MAP' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_SHOW_MAP'),
                        "DEFAULT" => 'N'
                    ),
                    'SHOW_MAP_IGN' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_SHOW_MAP_IGN'),
                        "DEFAULT" => 'N'
                    ),
                    'ERROR_COST_DSBL' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_DSBL1'),
                        "DEFAULT" => 'Y'
                    ),
                    'ERROR_COST_DSBL_SROK' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_DSBL2'),
                        "DEFAULT" => 'Y'
                    ),
                    'ERROR_COST' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_COST_DEF'),
                        "DEFAULT" => '500.00'
                    ),
                    'WEIGHT_DEFAULT' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_WEIGHT_DEF'),
                        "DEFAULT" => '3000'
                    ),
                    /*'PRED_DEFAULT' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_DEM_DEF'),
                        "DEFAULT" => '10'
                    ),*/
                    'PRED_DEFAULT_DX' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_DEM_DEF_DX'),
                        "DEFAULT" => '10'
                    ),
                    'PRED_DEFAULT_DY' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_DEM_DEF_DY'),
                        "DEFAULT" => '10'
                    ),
                    'PRED_DEFAULT_DZ' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_DEM_DEF_DZ'),
                        "DEFAULT" => '10'
                    ),
                    'ADD_HOUR' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_ADD_HOUR'),
                        "DEFAULT" => '3'
                    ),
					'SROK_FROM_STARTDAY' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_SROK_FROM_STARTDAY'),
                        "DEFAULT" => 'N'
                    ),
                    'ADD_PRICE'=> array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_ADD_PRICE'),
                        "DEFAULT" => 0
                    )
                )
            )
        );
        return $result;
    }

    protected function calculateConcrete(Shipment $shipment = null)
    {
        $request = Context::getCurrent()->getRequest();
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
		$cPlaces = [];
        foreach($items as $item){
            if(!isset($item['physical_dims']['dx'], $item['physical_dims']['dy'], $item['physical_dims']['dz'])){
                continue;
            }
            if($item['physical_dims']['dx'] > $maxWd) $maxWd = $item['physical_dims']['dx'];
            if($item['physical_dims']['dy'] > $maxWd) $maxWd = $item['physical_dims']['dy'];
            if($item['physical_dims']['dz'] > $maxWd) $maxWd = $item['physical_dims']['dz'];
            $allWd_ = $item['physical_dims']['dx'] + $item['physical_dims']['dy'] + $item['physical_dims']['dz'];
            if($allWd_ > $allWd) $allWd = $allWd_;
			$ost = intval($item['count']);
            while($ost>0){
                $ost = $ost -1;
                $cPlaces[] = [
                    'physical_dims'=>[
                        'weight_gross'=>$item['physical_dims']['weight_gross'],
                        'dx'=>$item['physical_dims']['dx'],
                        'dy'=>$item['physical_dims']['dy'],
                        'dz'=>$item['physical_dims']['dz']
                    ]
                ];
            }
        }

        /*
         * Доставка до двери
         * Максимальная стоимость заказа 250 000 рублей.
         * Максимальный вес заказа 200 кг
         * Максимальные габариты заказа 500 см по сумме 3х сторон, одна сторона не превышает 300 см
         * */
        if($allWd>500 || $maxWd>300 || $weight>200000){
            $result->addError(new Error(Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_ERR_WD')));
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
            $result->addError(
                new Error(
                    Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_ERR_REGION')
                )
            );
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

        $data = array(
            //'client_price'=>1000,
            //'total_assessed_price'=>1000,
            'tariff'=>'time_interval',
            'total_weight'=>(int)$weight,
            'source'=>array(),
            'destination'=>array(
                'address'=>$locationName
            )
        );
		if(!empty($cPlaces)){
			$data['places'] = $cPlaces;
		}

        if($request->get('AWZ_YD_CORD_ADRESS')){
            $data['destination']['address'] = $request->get('AWZ_YD_CORD_ADRESS');
        }

        $marketPostData = array();
        if(Option::get(Handler::MODULE_ID, 'YM_TRADING_ON_'.$this->getId(), 'N', '')=='Y'){
            $rawInput = file_get_contents('php://input');
            if($rawInput && strpos($rawInput,'{')!==false){
                try{
                    $marketPostData = \Bitrix\Main\Web\Json::decode($rawInput);
                    //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/cart.txt', print_r($marketPostData, true)."\n\n", FILE_APPEND);
                }catch (\Exception $e){

                }
            }

            if(isset($marketPostData['cart']['delivery']['address']['country'])){
                $preparedMarketAddress = [$marketPostData['cart']['delivery']['address']['country']];
                if(isset($marketPostData['cart']['delivery']['address']['city'])){
                    $preparedMarketAddress[] = $marketPostData['cart']['delivery']['address']['city'];
                }
                if(isset($marketPostData['cart']['delivery']['address']['street'])){
                    $preparedMarketAddress[] = $marketPostData['cart']['delivery']['address']['street'];
                }
                if(isset($marketPostData['cart']['delivery']['address']['house'])){
                    $preparedMarketAddress[] = $marketPostData['cart']['delivery']['address']['house'];
                }
                $data['destination']['address'] = implode(", ", $preparedMarketAddress);
            }
            if(isset($marketPostData['order']['delivery']['address']['country'])){
                $preparedMarketAddress = [$marketPostData['order']['delivery']['address']['country']];
                if(isset($marketPostData['order']['delivery']['address']['city'])){
                    $preparedMarketAddress[] = $marketPostData['order']['delivery']['address']['city'];
                }
                if(isset($marketPostData['order']['delivery']['address']['street'])){
                    $preparedMarketAddress[] = $marketPostData['order']['delivery']['address']['street'];
                }
                if(isset($marketPostData['order']['delivery']['address']['house'])){
                    $preparedMarketAddress[] = $marketPostData['order']['delivery']['address']['house'];
                }
                $data['destination']['address'] = implode(", ", $preparedMarketAddress);
            }
        }


        $pointHtml = '';
        $pointHtmlAdd = '';

        if($config['MAIN']['STORE_ID']){
            $data['source'] = array('platform_station_id'=>$config['MAIN']['STORE_ID']);
        }elseif($config['MAIN']['STORE_ADRESS']){
            $data['source'] = array('address'=>$config['MAIN']['STORE_ADRESS']);
        }

        $api->setCacheParams(md5(serialize(array($data, $config, 'calc'))), 86400);
        $r = $api->calc($data);

        if(
            $config['MAIN']['SHOW_MAP']=='Y' &&
            $config['MAIN']['SHOW_MAP_IGN']=='Y' &&
            !$r->isSuccess() &&
            ($data['destination']['address'] != $locationName)
        ){
            $data['destination']['address'] = $locationName;
            $api->setCacheParams(md5(serialize(array($data, $config, 'calc'))), 86400);
            $r = $api->calc($data);
            if($r->isSuccess()){
                $pointHtmlAdd .= '<br><br>'.'<!--info-ydost-start-->'.Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_SETT_SHOW_MAP_ERR').' '.$locationName.'<!--info-ydost-end-->';
            }
        }

        if($r->isSuccess()){
            $calkData = $r->getData();
            if($calkData['result']['message']){
                $r->addError(new Error($calkData['result']['message']));
            }
        }

        if(!$r->isSuccess()){
            if($config['MAIN']['ERROR_COST_DSBL']=='Y'){
                $result->addErrors($r->getErrors());
                return $result;
            }else{
                $result->setDescription('<p style="color:red;">'.Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_ERR_COST').'</p>');

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

            $api->setCacheParams(md5(serialize(array($data, $config, 'grafik'))), 3600);
            $r = $api->grafik(array(
                'station_id'=>$config['MAIN']['STORE_ID'],
                'full_address'=>$data['destination']['address']
            ));
            if($r->isSuccess()){
                $grafikData = $r->getData();
                if(isset($grafikData['result']['offers']) && !empty($grafikData['result']['offers'])){
                    $tmpData = $result->getTmpData();
                    if(!is_array($tmpData)) $tmpData = array();
                    $tmpData['offers'] = $grafikData['result']['offers'];
                    $result->setTmpData($tmpData);
                    foreach($grafikData['result']['offers'] as $offer){
						if($config['MAIN']['SROK_FROM_STARTDAY']=='Y'){
                            $offer['from'] = date('c',strtotime(date('d.m.Y',strtotime($offer['from']))));
                        }
                        $fromDay = ceil((strtotime($offer['from']) - time())/86400);
                        if($fromDay>0){
                            $result->setPeriodFrom($fromDay);
                            $result->setPeriodDescription($fromDay.' '.Loc::getMessage("AWZ_YDELIVERY_D"));
                        }
                        $toDay = ceil((strtotime($offer['from']) - time())/86400);
                        if($toDay>0 && $toDay!=$fromDay){
                            $result->setPeriodTo($toDay);
                            $result->setPeriodDescription($fromDay.'-'.$toDay.' '.Loc::getMessage("AWZ_YDELIVERY_D"));
                        }

                        break;
                    }
                }elseif($config['MAIN']['ERROR_COST_DSBL_SROK'] == 'Y'){
                    $result->addError(
                        new Error(
                            Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_ERR_NOGRAF')
                        )
                    );
                    return $result;
                }
            }elseif($config['MAIN']['ERROR_COST_DSBL_SROK'] == 'Y'){
                $result->addError(
                    new Error(
                        Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_ERR_NOGRAF')
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

        if($config['MAIN']['SHOW_MAP']=='Y'){
            $signer = new Security\Sign\Signer();

            $signedParameters = $signer->sign(base64_encode(serialize(array(
                'address'=>$locationName,
                'geo_id'=>$locationGeoId,
                'profile_id'=>$this->getId(),
                's_id'=>bitrix_sessid(),
                'user'=>'',
                'order'=>''
            ))));


            if($request->get('AWZ_YD_CORD_ADRESS')){
                $pointHtml .= $request->get('AWZ_YD_CORD_ADRESS').$pointHtmlAdd;
            }

            $buttonHtml = '<a id="AWZ_YD_DOST_LINK" class="'.$config['MAIN']['BTN_CLASS'].'" href="#" onclick="window.awz_yd_modal.showgps(\''.Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_BTN_OPEN').'\',\''.$signedParameters.'\');return false;">'.Loc::getMessage('AWZ_YDELIVERY_PROFILE_STANDART_BTN_OPEN').'</a><div id="AWZ_YD_DOST_INFO">'.$pointHtml.'</div>';
            $result->setDescription($result->getDescription().
                '<!--btn-ydost-start-->'.
                $buttonHtml
                .'<!--btn-ydost-end-->'
            );
        }

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

        //запись координат
        $cordStr = $request->get('AWZ_YD_CORD');
        if(!$request->isAdminSection() && $cordStr){
            $addressCord = Helper::getPropAddressCord($this->getId());
            $addressCordValue = explode(',',$cordStr);
            if(!empty($addressCord) && ($this->getId() == Helper::getProfileId($order, Helper::DOST_TYPE_ALL))){
                /* @var EntityPropertyValue $prop*/
                foreach($props as $prop){
                    if($prop->getField('CODE') == $addressCord[0]){
                        if(count($addressCord)==1){
                            $prop->setValue(implode(',',$addressCordValue));
                            break;
                        }else{
                            $prop->setValue($addressCordValue[0]);
                        }
                    }elseif(isset($addressCord[1]) && ($prop->getField('CODE') == $addressCord[1])){
                        $prop->setValue($addressCordValue[1]);
                    }
                }
            }
        }

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