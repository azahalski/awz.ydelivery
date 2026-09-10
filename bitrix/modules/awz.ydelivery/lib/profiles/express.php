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

class Express extends Base
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
        return Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_NAME');
    }

    public static function getClassDescription()
    {
        return Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_DESC');
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
                'TITLE' => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_INTG'),
                'DESCRIPTION' => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_INTG_DESC'),
                'ITEMS' => array(
                    'TOKEN' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_TOKEN'),
                        "DEFAULT" => ''
                    ),
                    'STORE_CORD' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_STORE_CORD'),
                        "DEFAULT" => ''
                    ),
                    'STORE_CORD2' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_STORE_CORD2'),
                        "DEFAULT" => ''
                    ),
                    'STORE_EMAIL' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_STORE_EMAIL'),
                        "DEFAULT" => ''
                    ),
                    'STORE_PHONE' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_STORE_PHONE'),
                        "DEFAULT" => ''
                    ),
                    'STORE_NAME' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_STORE_NAME'),
                        "DEFAULT" => ''
                    ),
                    'STORE_TOWN' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_STORE_TOWN'),
                        "DEFAULT" => ''
                    ),
                    'STORE_ADDRESS' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_STORE_ADDRESS'),
                        "DEFAULT" => ''
                    ),
                    'TAXI_CLASS' => array(
                        'TYPE' => 'ENUM',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_TAXI_CLASS'),
                        "DEFAULT" => 'express',
                        "OPTIONS" => [
                            "express" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_TAXI_CLASS_EXPRESS'),
                            "cargo" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_TAXI_CLASS_CARGO'),
                            "courier" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_TAXI_CLASS_COURIER')
                        ]
                    ),
                    'PRO_COURIER' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_PRO_COURIER'),
                        "DEFAULT" => 'N'
                    ),
                    'SKIP_DOOR' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SKIP_DOOR'),
                        "DEFAULT" => 'Y'
                    ),
                    'AUTO_COURIER' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_AUTO_COURIER'),
                        "DEFAULT" => 'N'
                    ),
                    'THERMOBAG' => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_THERMOBAG'),
                        "DEFAULT" => 'N'
                    ),
                    'NDS' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_NDS'),
                        "DEFAULT" => '0'
                    ),
                    'BTN_CLASS' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_BTN_CLASS'),
                        "DEFAULT" => 'btn btn-primary'
                    ),
                    'WEIGHT_DEFAULT' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_WEIGHT_DEF'),
                        "DEFAULT" => '3000'
                    ),
                    'PRED_DEFAULT_DX' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_DEM_DEF_DX'),
                        "DEFAULT" => '10'
                    ),
                    'PRED_DEFAULT_DY' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_DEM_DEF_DY'),
                        "DEFAULT" => '10'
                    ),
                    'PRED_DEFAULT_DZ' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_DEM_DEF_DZ'),
                        "DEFAULT" => '10'
                    ),
                    'ERROR_COST' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_COST_DEF'),
                        "DEFAULT" => '500.00'
                    ),
                    'CACHE_TIME' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_CACHE_TIME'),
                        "DEFAULT" => '0'
                    ),
                    'STORE_NAME_SHOP' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_STORE_NAME_SHOP'),
                        "DEFAULT" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_STORE_NAME_SHOP_DEF')
                    ),
                    'STORE_NAME_SHOP2' => array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_STORE_NAME_SHOP2'),
                        "DEFAULT" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_STORE_NAME_SHOP_DEF2')
                    ),
                    'ADD_PRICE'=> array(
                        'TYPE' => 'STRING',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_ADD_PRICE'),
                        "DEFAULT" => 0
                    )
                    /*
                    'ADD_MINUTES' => array(
                        'TYPE' => 'NUMBER',
                        "NAME" => Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SETT_ADD_MINUTES'),
                        "DEFAULT" => '0'
                    ),*/
                    /*
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
                    */
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
        $result = new CalculationResult();

        $weight = $shipment->getWeight();
        $order = $shipment->getCollection()->getOrder();
        $items = Helper::getItems($order, $this->getId());


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
                    Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_ERR_REGION')
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
            'items'=>[],
            'requirements'=>[
                'taxi_class'=>$config['MAIN']['TAXI_CLASS'],
                'cargo_options'=>[],
                'pro_courier'=>$config['MAIN']['PRO_COURIER']==='Y'
            ],
            'route_points'=>[
                [
                    'coordinates'=>[
                        (float) $config['MAIN']['STORE_CORD2'],
                        (float) $config['MAIN']['STORE_CORD']
                    ]
                ]
            ],
            'skip_door_to_door'=>$config['MAIN']['SKIP_DOOR']==='Y'
        );
        if($config['MAIN']['AUTO_COURIER']=='Y')
            $data['requirements']['cargo_options'][] = 'auto_courier';
        if($config['MAIN']['THERMOBAG']=='Y')
            $data['requirements']['cargo_options'][] = 'thermobag';
        foreach($items as $itm){
            $data['items'][] = [
                'size'=>[
                    'length'=>$itm['physical_dims']['dy']/100,
                    'width'=>$itm['physical_dims']['dx']/100,
                    'height'=>$itm['physical_dims']['dz']/100,
                ],
                'weight'=>$itm['physical_dims']['weight_gross']/1000,
                'quantity'=>$itm['count']
            ];
        }

        if($request->get('AWZ_YD_CORD_ADRESS')){
            $locationAddress = $request->get('AWZ_YD_CORD_ADRESS');
        }
        if($request->get('AWZ_YD_CORD')){
            $locationAddressCord = $request->get('AWZ_YD_CORD');
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
                $locationAddress = implode(", ", $preparedMarketAddress);
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
                $locationAddress = implode(", ", $preparedMarketAddress);
            }
        }

        $pointHtml = '';
        $pointHtmlAdd = '';

        if($locationAddressCord){
            $cordAr = explode(',',$locationAddressCord);
            $data['route_points'][] = ['coordinates'=>[(float)$cordAr[1], (float)$cordAr[0]]];
        }elseif($locationAddress){
            $data['route_points'][] = ['fullname'=>$locationAddress];
        }

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
        }elseif(!$locationAddressCord){
            $pointHtml .= '<p style="color:red;">'.Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_SET_CORD').'</p>'.$pointHtmlAdd;
        }
        $buttonHtml = '<a id="AWZ_YD_DOST_LINK" class="'.$config['MAIN']['BTN_CLASS'].'" href="#" onclick="window.awz_yd_modal.showgps(\''.Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_BTN_OPEN').'\',\''.$signedParameters.'\');return false;">'.Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_BTN_OPEN').'</a><div id="AWZ_YD_DOST_INFO">'.$pointHtml.'</div>';
        $result->setDescription($result->getDescription().
            '<!--btn-ydost-start-->'.
            $buttonHtml
            .'<!--btn-ydost-end-->'
        );

        if($locationAddress){
            //запись координат
            $cordStr = $locationAddressCord;
            if(!$request->isAdminSection() && $cordStr){
                $addressCord = Helper::getPropAddressCord($this->getId());
                $addressCordValue = explode(',',$cordStr);
                if(!empty($addressCord) && ($this->getId() == Helper::getProfileId($order, Helper::DOST_TYPE_ALL))){
                    /* @var EntityPropertyValue $prop*/
                    foreach($props as $prop){
                        if($prop->getField('CODE') == $addressCord[0]){
                            if(count($addressCord)==1){
                                $prop->setValue($addressCordValue[0].','.$addressCordValue[1]);
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

            $api->clearCacheParams();
            if($config['MAIN']['CACHE_TIME']){
                $api->setCacheParams(md5(serialize(array($data, $config, 'calc'))), (int) $config['MAIN']['CACHE_TIME']);
            }
            $r = $api->calcExpress($data);

            if($r->isSuccess()){
                $calkData = $r->getData();
                //$result->setDescription($result->getDescription()."<br>### ".print_r($calkData, true)." ###");
                $result->setPeriodFrom(0);
                $result->setPeriodTo(0);
                if($calkData['result']['price']){

                    if(!$config['MAIN']['ADD_PRICE']) $config['MAIN']['ADD_PRICE'] = 0;
                    $calkData['result']['price'] = (float) $calkData['result']['price'];
                    if(mb_strpos($config['MAIN']['ADD_PRICE'],'%')){
                        $calkData['result']['price'] = round($calkData['result']['price'],2) + intval($config['MAIN']['ADD_PRICE'])*round($calkData['result']['price'],2)*0.01;
                    }else{
                        $calkData['result']['price'] = round($calkData['result']['price'],2) + intval($config['MAIN']['ADD_PRICE']);
                    }
                    $calkData['result']['price'] = round($calkData['result']['price'], 2);


                    $result->setDeliveryPrice(
                        roundEx(
                            $calkData['result']['price'],
                            SALE_VALUE_PRECISION
                        )
                    );
                }
            }
            if(!$r->isSuccess()){
                if($r->getErrors()[0]->getCode() == 'errors.suitable_offer_not_found'){
                    $result->setDescription('<p style="color:red;">'.Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_ERR_COST2').'</p>'.$result->getDescription());
                }else{
                    $result->setDescription('<p style="color:red;">'.Loc::getMessage('AWZ_YDELIVERY_PROFILE_EXSPRESS_ERR_COST').'</p>'.$result->getDescription());
                }
                global $USER;
                if($USER->IsAdmin()){
                    $result->setDescription($result->getDescription()."<br>### ".implode("; ",$r->getErrorMessages())." ###");
                }
                $result->setDeliveryPrice($config['MAIN']['ERROR_COST']);
                $result->setPeriodDescription('-');
                return $result;
            }
        }


        //print_r($r);
        //die();

        return $result;

        if($r->isSuccess()){
            $calkData = $r->getData();
            if($calkData['result']['message']){
                $r->addError(new Error($calkData['result']['message']));
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

            $result->setDeliveryPrice(
                roundEx(
                    $calkData['result']['pricing_total'],
                    SALE_VALUE_PRECISION
                )
            );
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