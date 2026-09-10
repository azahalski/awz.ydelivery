<?php
namespace Awz\Ydelivery\Api\Controller;

use Awz\Ydelivery\Handler;
use Awz\Ydelivery\PvzTable;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter\Scope;
use Awz\Ydelivery\Api\Filters\Sign;
use Awz\Ydelivery\Helper;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Order;

Loc::loadMessages(__FILE__);

class PickPoints extends Controller
{

    public function configureActions()
    {
        return array(
            'list' => array(
                'prefilters' => array(
                    new Scope(Scope::AJAX),
                    new Sign(array('address','geo_id','profile_id','page','order','user','s_id','terminal'))
                )
            ),
            'baloon' => array(
                'prefilters' => array(
                    new Scope(Scope::AJAX),
                    new Sign(array('address','geo_id','profile_id','page','order','user','s_id'))
                )
            ),
            'setorder' => array(
                'prefilters' => array(
                    new Scope(Scope::AJAX),
                    new Sign(array('address','geo_id','profile_id','page','order','user','s_id'))
                )
            )
        );
    }

    public function setorderAction($address = '', $geo_id = '', $profile_id = '', $page = '', $user = '', $order='', $point=''){

        Loader::includeModule('sale');

        if(!$user || !$order || !$point || !$page){
            $this->addError(
                new Error(
                    Loc::getMessage('AWZ_YDELIVERY_API_CONTROL_PICKPOINTS_ERR_REQ'),
                    100
                )
            );
            return null;
        }

        $orderOb = Order::load($order);

        global $APPLICATION;
        // Если заказ не найден, предотвращаем Fatals
        if (!$orderOb) {
            $this->addError(new Error('Заказ не найден', 404));
            return null;
        }
        $saleModulePermissions = $APPLICATION->GetGroupRight("sale");
        if ($saleModulePermissions < "W") { // W - полный доступ (изменение)
            $this->addError(new Error('Доступ запрещен', 403));
            return null;
        }

        $propertyCollection = $orderOb->getPropertyCollection();
        $res = null;

        if(!$profile_id){
            $profile_id = Helper::getProfileId($orderOb, Helper::DOST_TYPE_PVZ);
        }

        $pointData = PvzTable::getPvz($point);
        if(!$pointData){
            $this->addError(
                new Error(
                    Loc::getMessage('AWZ_YDELIVERY_API_CONTROL_PICKPOINTS_ERR_POINT_DATA'),
                    100
                )
            );
            return null;
        }
        $addressPvz = Helper::formatPvzAddress($profile_id, $pointData);

        $isSet = false;
        $addMessAddress = '';
        foreach($propertyCollection as $prop){
            if($prop->getField('CODE') == Helper::getPropPvzCode($profile_id)){
                $prop->setValue($point);
                $isSet = true;
            }elseif($addressPvz && ($prop->getField('CODE') == Helper::getPropAddress($profile_id))){
                $prop->setValue($addressPvz);
                $isSet = true;
                $addMessAddress .= ', '.Loc::getMessage('AWZ_YDELIVERY_API_CONTROL_PICKPOINTS_OK_ADDR_ADD', array('#PROP#'=>$prop->getField('CODE')));
            }
        }
        if($isSet){
            $res = $orderOb->save();
        }
        if(!$res){
            $this->addError(
                new Error(
                    Loc::getMessage('AWZ_YDELIVERY_API_CONTROL_PICKPOINTS_ERR_PROP'), 100
                )
            );
            return null;
        }else{
            if($res->isSuccess()){
                return Loc::getMessage(
                    'AWZ_YDELIVERY_API_CONTROL_PICKPOINTS_OK_ADDR',
                    array("#POINT#"=>$point, "#PROP#"=>Helper::getPropPvzCode($profile_id))
                ).$addMessAddress;
            }else{
                $this->addErrors($res->getErrors());
                return null;
            }

        }

    }
    public function baloonAction($s_id='', $address = '', $geo_id = '', $profile_id = '', $page = '', $id = '')
    {
        if(!$id){
            $this->addError(
                new Error(Loc::getMessage('AWZ_YDELIVERY_API_CONTROL_PICKPOINTS_ID_ERR'), 100)
            );
            return null;
        }
        if(bitrix_sessid() != $s_id){
            $this->addError(
                new Error(Loc::getMessage('AWZ_YDELIVERY_API_CONTROL_PICKPOINTS_SESS_ERR'), 100)
            );
            return null;
        }

        $hideBtn = ($page === 'pvz-edit') ? true : false;

        $bResult = Helper::getBaloonHtml($id, $hideBtn);
        if(!$bResult->isSuccess()){
            $this->addErrors($bResult->getErrors());
            return null;
        }
        $resultData = $bResult->getData();

        return $resultData['html'];
    }

    public function listAction($address = '', $geo_id = '', $profile_id = '', $page = '', $terminal='', $dost_day=0)
    {

        if(!$profile_id){
            $this->addError(
                new Error(Loc::getMessage('AWZ_YDELIVERY_API_CONTROL_PICKPOINTS_PROFILE_ERR'), 100)
            );
            return null;
        }
        if(!$address && !$geo_id){
            $this->addError(
                new Error(Loc::getMessage('AWZ_YDELIVERY_API_CONTROL_PICKPOINTS_ADDRGEO_ERR'), 100)
            );
            return null;
        }

        $api = Helper::getApiByProfileId($profile_id);

        $config = Helper::getConfigByProfileId($profile_id);
        $ttl = intval($config['MAIN']['CACHE_TTL_POINTS']);
        if(!$ttl) $ttl = 86400;

        $api->setCacheParams(md5(serialize(array($address, $geo_id, $profile_id))), $ttl);
        $pickpointsResult = $api->getPickpoints(array('geo_id'=>intval($geo_id)));
        if(!$pickpointsResult->isSuccess()){
            $this->addErrors($pickpointsResult->getErrors());
            return null;
        }
        $pickpoints = $pickpointsResult->getData();
        if(empty($pickpoints['result']['points'])){
            $this->addError(
                new Error(Loc::getMessage('AWZ_YDELIVERY_API_CONTROL_PICKPOINTS_PVZ_ERR'), 100)
            );
            return null;
        }
        $items = array();
        $itemsPrepare = array();
        $issetPositions = array();

        foreach($pickpoints['result']['points'] as $point){
            if($terminal == 'N' && $point['type']=='terminal') continue;
			if(!in_array($point['type'],array('terminal','pickup_point'))) continue;
            $key = md5(serialize($point['position']));
            if(isset($issetPositions[$key])) continue;
            $issetPositions[$key] = true;
            $itemsPrepare[$point['id']] = array(
                'id'=>$point['id'],
                'position'=>$point['position'],
                'payment_methods'=>$point['payment_methods'],
                'type'=>$point['type'],
                'days'=>0
            );
        }

        $items = $itemsPrepare;

        $event = new Event(
            Handler::MODULE_ID, "OnBeforelistActionReturn",
            array(
                'items'=>&$items,
                'dost_day'=>$dost_day,
                'address'=>$address,
                'geo_id'=>$geo_id,
                'profile_id'=>$profile_id,
                'page'=>$page,
                'terminal'=>$terminal
            )
        );
        $event->send();

        if($api->getLastResponse() && (Option::get(Handler::MODULE_ID, "UPDATE_PVZ_BG", "Y", "") == 'Y')) {
            Application::getInstance()->addBackgroundJob(
                array("\\Awz\\Ydelivery\\Checker", "runJob"),
                array($pickpoints['result']['points']),
                Application::JOB_PRIORITY_NORMAL
            );
        }

        return array(
            'page'=>$page,
            'address' => $address,
            'geo_id' => $geo_id,
            'profile_id' => $profile_id,
            'items' => $items,
            'from_cache'=>$api->getLastResponse() ? 0 : 1,
            'terminal'=>$terminal
        );
    }
}