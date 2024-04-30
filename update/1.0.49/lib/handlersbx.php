<?php
namespace Awz\Ydelivery;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\EntityPropertyValue;
use Bitrix\Sale\Order;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem\Manager as PayManager;
use Bitrix\Sale\ResultError;

Loc::loadMessages(__FILE__);

class handlersBx {

    public static function registerHandler(){

        $result = new EventResult(
            EventResult::SUCCESS,
            array(
                'Awz\Ydelivery\Handler' => '/bitrix/modules/awz.ydelivery/lib/handler.php',
                'Awz\Ydelivery\Profiles\Pickup' => '/bitrix/modules/awz.ydelivery/lib/profiles/pickup.php',
                'Awz\Ydelivery\Profiles\Standart' => '/bitrix/modules/awz.ydelivery/lib/profiles/standart.php',
            )
        );

        return $result;

    }

    public static function OnAdminSaleOrderEditDraggable($args){
        $res = array(
            'getScripts'=>array('\Awz\Ydelivery\handlersBx','editDraggableAddScript')
        );
        return $res;
    }

    public static function editDraggableAddScript($args){
        if(isset($args['ORDER']) && $args['ORDER'] instanceof Order){

            $order = $args['ORDER'];
            $propertyCollection = $order->getPropertyCollection();
            /* @var EntityPropertyValue $prop*/
            $profileId = Helper::getProfileId($order, Helper::DOST_TYPE_PVZ);
            $prop = $propertyCollection->getItemByOrderPropertyCode(Helper::getPropPvzCode($profileId));

            if(!$prop) return '';

            $content = 'BX.addCustomEvent("onAfterSaleOrderTailsLoaded", function(){';
            $content .= "BX.insertAfter(BX.create('a', {
                      attrs: {
                         className: 'adm-btn adm-btn-green adm-btn-add',
                         href: '/',
                         onclick: 'BX.SidePanel.Instance.open(\"/bitrix/admin/awz_ydelivery_picpoint_list.php?LANG=ru&page=order_edit&order=".$order->getId()."\",{cacheable: false});return false;'
                      },
                      text: '".Loc::getMessage('AWZ_YDELIVERY_HANDLERBX_CHOISE')."'
                   }), BX.findChild(BX('tab_order_edit_table'), {tag: 'input', attribute: {name: 'PROPERTIES[".$prop->getPropertyId()."]'}}, true));";

            $content .= '});';
            return '<script>'.$content.'</script>';
        }
        return null;
    }

    public static function OnEndBufferContent(&$content){
        global $APPLICATION;
        if($APPLICATION->GetCurPage(false) == '/bitrix/admin/sale_order_ajax.php'){
            if($_REQUEST['action'] == 'changeDeliveryService' && $_REQUEST['formData']['order_id']){
                if(!Loader::includeModule('awz.ydelivery')) return;

                $profileId = $_REQUEST['formData']['SHIPMENT'][1]['PROFILE'] ? $_REQUEST['formData']['SHIPMENT'][1]['PROFILE'] : $_REQUEST['formData']['SHIPMENT'][1]['DELIVERY_ID'];
                if($profileId){
                    $delivery = Helper::deliveryGetByProfileId($profileId);
                    if(in_array($delivery['CLASS_NAME'],array('\Awz\Ydelivery\Profiles\Pickup', '\Awz\Ydelivery\Handler'))){
                        $json = Json::decode($content);
                        if($delivery['CLASS_NAME'] == '\Awz\Ydelivery\Handler'){
                            preg_match('/value="([0-9]+)"/is',$json['SHIPMENT_DATA']['PROFILES'], $mc);
                            $profileId = $mc[1];
                        }
                        $json['SHIPMENT_DATA']['PROFILES'] .= '<br><a href="#" class="adm-btn adm-btn-green adm-btn-add" onclick="BX.SidePanel.Instance.open(\'/bitrix/admin/awz_ydelivery_picpoint_list.php?LANG=ru&profile_id='.$profileId.'&order='.intval($_REQUEST['formData']['order_id']).'&from=changeDeliveryService\',{cacheable: false});return false;">'.Loc::getMessage('AWZ_YDELIVERY_HANDLERBX_CHOISE_PVZ').'</a>';
                        $content = Json::encode($json);
                    }
                }


            }
        }
    }

    public static function OnSaleOrderBeforeSaved(Event $event){

        $request = Context::getCurrent()->getRequest();
        /* @var Order $order*/
        $order = $event->getParameter("ENTITY");
        $propertyCollection = $order->getPropertyCollection();

        $checkMyDeliveryPvz = Helper::getProfileId($order, Helper::DOST_TYPE_PVZ);

        if(!$checkMyDeliveryPvz) {
            $event->addResult(
                new EventResult(
                    EventResult::SUCCESS, $order
                )
            );
        }else{
			$pointId = false;
            $errorText = '';
            $setPoints = false;
            if($request->get('AWZ_YD_POINT_ID')){
                $pointId = preg_replace('/([^0-9A-z\-])/i', '', $request->get('AWZ_YD_POINT_ID'));
            }
            if(!$pointId && (Option::get(Handler::MODULE_ID, 'YM_TRADING_ON_'.$checkMyDeliveryPvz, 'N', '')=='Y')){
                $rawInput = file_get_contents('php://input');
                if($rawInput && strpos($rawInput,'{')!==false){
                    try{
                        $postData = Json::decode($rawInput);
                        if(isset($postData['order']['delivery']['outlet']['code'])){
                            $pointId = $postData['order']['delivery']['outlet']['code'];
                        }
                    }catch (\Exception $e){

                    }
                }
            }

            /* @var EntityPropertyValue $prop*/
            $checkIsProp = false;
            $propAddress = false;
            foreach($propertyCollection as $prop){
                if($prop->getField('CODE') == Helper::getPropPvzCode($checkMyDeliveryPvz)){
                    $checkIsProp = true;
                    if($pointId){
                        $prop->setValue($pointId);
                    }
                    if($prop->getValue()){
                        $setPoints = true;
						$pointId = $prop->getValue();
                    }
                }elseif($prop->getField('CODE') == Helper::getPropAddress($checkMyDeliveryPvz)){
                    $propAddress = $prop;
                }
            }

            if($pointId){
                $pointData = PvzTable::getPvz($pointId);
                if($pointData){

                    if($propAddress){
                        $propAddress->setValue(Helper::formatPvzAddress($checkMyDeliveryPvz, $pointData));
                    }
                    $paymentYandexAr = Helper::getYandexPaymentIdFromOrder($order, $checkMyDeliveryPvz);
                    $checkRightPayment = false;
                    foreach($paymentYandexAr as $paymentYandex){
                        if(in_array($paymentYandex, $pointData['PRM']['payment_methods'])){
                            $checkRightPayment = true;
                            break;
                        }
                    }
                    if(!$checkRightPayment){
                        $errorText = Loc::getMessage('AWZ_YDELIVERY_HANDLERBX_ERR_PAY1');
                    }
                }else{
                    //$setPoints = false;
                    $errorText = Loc::getMessage('AWZ_YDELIVERY_HANDLERBX_ERR_PVZDATA');
                }
            }

            if(!$setPoints || $errorText){
                if(!$errorText) $errorText = Loc::getMessage('AWZ_YDELIVERY_HANDLERBX_ERR_PVZ');
                if(!$checkIsProp){
                    $errorText = Loc::getMessage('AWZ_YDELIVERY_HANDLERBX_ERR_PVZ_PROP');
                }
                $event->addResult(
                    new EventResult(
                        EventResult::ERROR,
                        ResultError::create(
                            new Error($errorText, "DELIVERY")
                        )
                    )
                );
            }else{
                $event->addResult(
                    new EventResult(
                        EventResult::SUCCESS, $order
                    )
                );
            }
        }

        $results = $event->getResults();

        foreach($results as $result){
            if($result->getType() == EventResult::ERROR){
                return;
            }
        }

        $statusCheck = false;
        $checkMyDeliveryAdr = Helper::getProfileId($order, Helper::DOST_TYPE_ADR);
        if($checkMyDeliveryPvz){
            $statusCheck = Helper::getStatusAutoCreate($checkMyDeliveryPvz);
        }elseif($checkMyDeliveryAdr){
            $statusCheck = Helper::getStatusAutoCreate($checkMyDeliveryAdr);
        }

        //print_r($statusCheck);
        //die();

        if($statusCheck){
            $oldValues = $event->getParameter("VALUES");
            $arOrderVals = $order->getFields()->getValues();
            if(
                isset($oldValues['STATUS_ID']) &&
                ($oldValues['STATUS_ID'] != $arOrderVals['STATUS_ID']) &&
                ($statusCheck == $arOrderVals['STATUS_ID'])
            ){
                $result = OffersTable::addFromOrder($order);
                if(!$result->isSuccess()){
                    foreach($result->getErrors() as $err){
                        $event->addResult(
                            new EventResult(
                                EventResult::ERROR,
                                ResultError::create(
                                    $err
                                )
                            )
                        );
                    }
                }
            }
        }

        //SaleOrderAjax::$ar

    }

    public static function OrderDeliveryBuildList(&$arResult, &$arUserResult, $arParams)
    {
        \CJSCore::Init(['ajax', 'awz_yd_lib']);

        $key = Option::get("fileman", "yandex_map_api_key");
        $key1 = Option::get(Handler::MODULE_ID, "yandex_map_api_key", "", "");
        if($key1) $key = $key1;
        $key2 = Option::get(Handler::MODULE_ID, "yandex_map_suggest_api_key", "", "");
        $host = 'api-maps.yandex.ru';
        if($key){
            $host = 'enterprise.api-maps.yandex.ru';
        }
        $setSearchAddress = "N";
        if($key && $key2){
            $setSearchAddress = Option::get(Handler::MODULE_ID, "MAP_ADDRESS", "N", "");
        }
        $setBallon2 = Option::get(Handler::MODULE_ID, "BALUN_VARIANT", "N", "");

        Asset::getInstance()->addString('<script>window._awz_yd_lib_setSearchAddressYa = "'.$setSearchAddress.'";</script>', true);
        Asset::getInstance()->addString('<script>window._awz_yd_lib_setBallonVariant = "'.$setBallon2.'";</script>', true);
        Asset::getInstance()->addJs(
            '//'.$host.'/2.1/?lang=ru_RU&apikey='.$key.'&suggest_apikey='.$key2,
            true
        );
    }

    public static function OnAdminContextMenuShow(&$items){
        $isPage = ($GLOBALS['APPLICATION']->GetCurPage()=='/bitrix/admin/sale_order_edit.php') ||
            ($GLOBALS['APPLICATION']->GetCurPage()=='/bitrix/admin/sale_order_view.php');
        $orderId = intval($_REQUEST['ID']);
        if($isPage && $orderId){
            $order = Order::load($orderId);
            if(!$order) return;
            $checkProfileId = Helper::getProfileId($order);
            if(!$checkProfileId) return;
            $resOrder = OffersTable::getList(array('select'=>array('ID'),'filter'=>array('ORDER_ID'=>$order->getId())))->fetch();
            if($resOrder){
                $items[] = array(
                    "TEXT"=>Loc::getMessage('AWZ_YDELIVERY_HANDLERBX_BTN_OPEN_OLD'),
                    "LINK"=>"javascript:BX.SidePanel.Instance.open(\"/bitrix/admin/awz_ydelivery_offers_list_edit.php?LANG=ru&page=order_edit&id=".$resOrder['ID']."\",{cacheable: true})",
                    "TITLE"=>Loc::getMessage('AWZ_YDELIVERY_HANDLERBX_BTN_OPEN_OLD'),
                    "ICON"=>"adm-btn",
                );
            }else{
                $items[] = array(
                    "TEXT"=>Loc::getMessage('AWZ_YDELIVERY_HANDLERBX_BTN_OPEN_NEW'),
                    "LINK"=>"javascript:BX.SidePanel.Instance.open(\"/bitrix/admin/awz_ydelivery_offers_list_edit.php?LANG=ru&page=order_edit&ORDER_ID=".$order->getId()."\",{cacheable: true})",
                    "TITLE"=>Loc::getMessage('AWZ_YDELIVERY_HANDLERBX_BTN_OPEN_NEW'),
                    "ICON"=>"adm-btn",
                );
            }
        }

    }

    public static function OnEpilog()
    {
        global $APPLICATION;
        $page = $APPLICATION->GetCurPage(false);
        if(strpos($page,'/shop/orders/details/')!==false){
            $orderId = preg_replace('/.*details\/([0-9]+)\/.*?/',"$1",$page);
            if($orderId){
                $order = Order::load($orderId);
                if(!$order) return;
                $checkProfileId = Helper::getProfileId($order);
                if(!$checkProfileId) return;

                Extension::load('ui.buttons');
                Extension::load('ui.buttons.icons');

                $resOrder = OffersTable::getList(array('select'=>array('ID'),'filter'=>array('ORDER_ID'=>$order->getId())))->fetch();
                if($resOrder){
                    $link = "javascript:BX.SidePanel.Instance.open(\"/bitrix/admin/awz_ydelivery_offers_list_edit.php?LANG=ru&page=order_edit&id=".$resOrder['ID']."\",{cacheable: true})";
                    $containerHTML = "<div class=\"pagetitle-container\" id=\"awz_ydelivery_btn_admin\"><a href='".$link."' class=\"ui-btn ui-btn-light-border ui-btn-icon-info\" style=\"margin-left:12px;\">".Loc::getMessage('AWZ_YDELIVERY_HANDLERBX_HANDLER_NAME')."</a></div>";
                }else{
                    $link = "javascript:BX.SidePanel.Instance.open(\"/bitrix/admin/awz_ydelivery_offers_list_edit.php?LANG=ru&page=order_edit&ORDER_ID=".$order->getId()."\",{cacheable: true})";
                    $containerHTML = "<div class=\"pagetitle-container\" id=\"awz_ydelivery_btn_admin\"><a href='".$link."' class=\"ui-btn ui-btn-light-border ui-btn-icon-add\" style=\"margin-left:12px;\">".Loc::getMessage('AWZ_YDELIVERY_HANDLERBX_HANDLER_NAME')."</a></div>";
                }
                $APPLICATION->AddViewContent('inside_pagetitle', $containerHTML, 21000);
            }

        }
    }

    /**
     * отключает платежки в случае выбора пвз
     *
     * @param $order
     * @param $arUserResult
     * @param $request
     * @param $arParams
     * @param $arResult
     * @param $arDeliveryServiceAll
     * @param $arPaySystemServiceAll
     */
    public static function OnSaleComponentOrderCreated($order, $arUserResult, $request, $arParams, $arResult, &$arDeliveryServiceAll, &$arPaySystemServiceAll){

        /* @var $order Order */

        $profilePvz = Helper::getProfileId($order, Helper::DOST_TYPE_PVZ);
        if($profilePvz){

            $hidePaySystems = Option::get(
                Handler::MODULE_ID,
                'HIDE_PAY_ON_'.$profilePvz,
                'N', ''
            );

            if($hidePaySystems != 'Y') return;

            $pointId = false;
            if($request->get('AWZ_YD_POINT_ID')){
                $pointId = preg_replace('/([^0-9A-z\-])/i', '', $request->get('AWZ_YD_POINT_ID'));
            }
            if($pointId){
                $pointData = PvzTable::getPvz($pointId);

                $paymentCollection = $order->getPaymentCollection();
                $paymentId = 0;
                $paymentOb = null;
                if($paymentCollection){
                    foreach ($paymentCollection as $payment) {
                        $paymentOb = $payment;
                        $paymentId = $payment->getPaymentSystemId();
                    }
                }

                foreach($arPaySystemServiceAll as $key=>$payment){
                    $ydPayCodesActive = Helper::checkYandexPaymentId($profilePvz, $payment['ID']);

                    $checkRightPayment = false;
                    foreach($ydPayCodesActive as $paymentYandex){
                        if(in_array($paymentYandex, $pointData['PRM']['payment_methods'])){
                            $checkRightPayment = true;
                            break;
                        }
                    }

                    if(!$checkRightPayment){
                        if($payment['ID'] == $paymentId) $paymentId = 0;
                        unset($arPaySystemServiceAll[$key]);
                    }

                }

                if(!$paymentId) {
                    foreach($arPaySystemServiceAll as $key=>$payment){
                        if($paymentOb){
                            $paySystemService = PayManager::getObjectById($payment['ID']);
                            $paymentOb->setFields(array(
                                'PAY_SYSTEM_ID' => $paySystemService->getField("PAY_SYSTEM_ID"),
                                'PAY_SYSTEM_NAME' => $paySystemService->getField("NAME"),
                            ));
                            break;
                        }
                    }
                }

            }
        }
    }

}