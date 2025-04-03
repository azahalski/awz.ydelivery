# AWZ: Яндекс Доставка

Интеграция с логистической платформой Яндекс Доставка для Cms Bitrix    
Маркетплейс 1с-Битрикс https://marketplace.1c-bitrix.ru/solutions/awz.ydelivery/

## 1. Установка

вариант 1    
Установите модуль с Маркетплейса 1с-Битрикс кнопкой "Установить"    
либо по ссылке https://<МОЙ_САЙТ>/bitrix/admin/update_system_partner.php?addmodule=awz.ydelivery    
    
вариант 2    
Скопируйте файлы модуля в папку /bitrix/modules/awz.ydelivery/    
Перейдите в админ панели: Маркетплейс - Установленные решения
   
версия для cp1251 в папке dist

## 2. События модуля

<!-- event-start -->

**OnCalcBeforeReturn**    
Позволяет переопределить или изменить результат расчета перед установкой даты доставки в свойство заказа

| Параметр | Описание |
| --- | --- |
| `shipment` `\Bitrix\Sale\Shipment` | Объект отгрузки
| `calcResult` `\Bitrix\Sale\Delivery\CalculationResult` | Объект расчета доставки

должен вернуть [`result` => `\Bitrix\Main\Result`] в случае ошибки    
[`disableWriteDate` => `bool`] отключить установку даты в свойство заказа
изменение данных расчета через объект `\Bitrix\Sale\Delivery\CalculationResult`

```php
//добавим 1-2 дня к сроку доставки если сегодня суббота или воскресенье

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    'awz.ydelivery', 'OnCalcBeforeReturn', 
    array('handlersDelivery','OnCalcBeforeReturn')
);

class handlersDelivery {

    public static function OnCalcBeforeReturn(\Bitrix\Main\Event $event){

        $result = new \Bitrix\Main\Result();
        $calc = $event->getParameter('calcResult');

        $addDay = 0;
        if(date("w") == 0){ //воскресенье
            $addDay += 1;
        }elseif(date("w") == 6){ //суббота
            $addDay += 2;
        }

        if($addDay){
            $fromDay = $calc->getPeriodFrom();
            if($fromDay>0){
                $fromDay += $addDay;
                $calc->setPeriodFrom($fromDay);
                $calc->setPeriodDescription($fromDay.' д.');
            }
            $toDay = $calc->getPeriodFrom();
            if($toDay>0 && $toDay!=$fromDay){
                $toDay += $addDay;
                $calc->setPeriodTo($toDay);
                $calc->setPeriodDescription($fromDay.'-'.$toDay.' д.');
            }
        }
        
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array('result'=>$result)
        );

    }

}
```

**changePrepareData**    
Позволяет переопределить подготовленную заявку в доставку

| Параметр | Описание |
| --- | --- |
| `result` `\Bitrix\Main\Result` | Объект с данными заявки |

должен вернуть [`result` => `\Bitrix\Main\Result`] с новыми данными либо ошибкой    
изменение данных возможно и через объект на входе `\Bitrix\Main\Result` с данными заявки

```php
//переопределим минимальный и максимальный срок доставки перед получением списка офферов (0-7 дней)
//вернем ошибку если сумма доставки в заказе меньше 500 рублей

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    'awz.ydelivery', 'changePrepareData', 
    array('handlersDelivery','changePrepareData')
);

class handlersDelivery {

    public static function changePrepareData(\Bitrix\Main\Event $event){

        $result = new \Bitrix\Main\Result();

        $prepareData = $event->getParameter('result')->getData();

        $orderId = $prepareData['info']['operator_request_id'];
        $order = \Bitrix\Sale\Order::load($orderId);
        if($order->getDeliveryPrice()<500){
            $result->addError(new \Bitrix\Main\Error('Неверная сумма доставки'));
        }else{
            $prepareData['destination']['interval']['from'] = strtotime(date('d.m.Y'));
            $prepareData['destination']['interval']['to'] += 7*86400;
            $result->setData($prepareData);
        }

        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array('result'=>$result)
        );

    }

}
```

**onBeforeReturnBarCode**    
Позволяет переопределить сгенерированный штрих код места в посылке

| Параметр | Описание |
| --- | --- |
| `order` `\Bitrix\Sale\Order` | Объект заказа
| `barCode` `string` | Сгенерированный штрих код

должен вернуть [`barCode` => `string`] с новым штрих кодом

```php
//сделаем наш штрик код места в формате <UNIXTIME>-<НОМЕР ЗАКАЗА>

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    'awz.ydelivery', 'onBeforeReturnBarCode', 
    array('handlersDelivery','onBeforeReturnBarCode')
);

class handlersDelivery {

    public static function onBeforeReturnBarCode(\Bitrix\Main\Event $event){
        $result = new \Bitrix\Main\Result();
        
        $order = $event->getParameter('order');
        
        $newBarCode = time().'-'.$order->getId();

        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array('barCode'=>$newBarCode)
        );
    }
}
```


**setOffer**    
Переопределяет метод выбора варианта доставки из списка офферов

| Параметр | Описание |
| --- | --- |
| `offers` `array` | $resp['result']['offers'] с ответа апи доставки |
| `order` `\Bitrix\Sale\Order` | Объект заказа |

Должен вернуть [`result` => `\Bitrix\Main\Result`] с новым оффером либо ошибку    

```php
//Будем проверять предложения только с датой отгрузки больше чем 2 дня от текущей
//Выберем минимальный по сроку доставки с учетом отгрузки выше

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    'awz.ydelivery', 'setOffer', 
    array('handlersDelivery','setOffer')
);

class handlersDelivery {

    public static function setOffer(\Bitrix\Main\Event $event){
        $result = new \Bitrix\Main\Result();
        $order = $event->getParameter('order');
        $offers = $event->getParameter('offers');

        if(!empty($offers['result']['offers'])){

            $findOffer = null;
            $minTime = time()+86400*30;
            foreach($offers['result']['offers'] as $offer){
                //пропускаем предложения с отгрузкой менее чем через 2 дня
                if(strtotime($offer['offer_details']['pickup_interval']['min']) < (time()+86400*2)){
                    continue;
                }
                if($minTime > strtotime($offer['offer_details']['delivery_interval']['min'])){
                    $minTime = strtotime($offer['offer_details']['delivery_interval']['min']);
                    $findOffer = $offer;
                }
            }

            if($findOffer){
                $result->setData(array('offer_id'=>$findOffer['offer_id']));
            }else{
                $result->addError(
                    new \Bitrix\Main\Error(
                        'Нет предложений по доставке в заказе '.$order->getId()
                    )
                );
            }

            return new \Bitrix\Main\EventResult(
                \Bitrix\Main\EventResult::SUCCESS,
                array('result'=>$result)
            );

        }

    }
    
}
```

**onBeforeReturnItems**    
Позволяет изменить параметры товаров в заявке

| Параметр | Описание |
| --- | --- |
| `order` `\Bitrix\Sale\Order` | Объект заказа |
| `items` `array` | Список товаров |

Должен вернуть [`items` => `array()`] с новым списком товаров  

```php
//добавим номер заказа в штрихкод товара в посылке (по умолчанию ид записи в корзине)

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    'awz.ydelivery', 'onBeforeReturnItems', 
    array('handlersDelivery','onBeforeReturnItems')
);

class handlersDelivery {

    public static function onBeforeReturnItems(\Bitrix\Main\Event $event){

        $items = $event->getParameter('items');
        $order = $event->getParameter('order');

        foreach($items as &$item){
            $item['barcode'] = $item['barcode'].'-'.$order->getId();
        }
        unset($item);

        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array('items'=>$items)
        );

    }
}
```

**onBeforeStatusUpdate**    
Вызывается перед запуском механизма обновления статусов модулем

| Параметр | Описание |
| --- | --- |
| `order` `\Bitrix\Sale\Order` | Объект заказа |
| `ordStatus` `string` | Код текущего статуса заказа |
| `newOrdStatus` `string` | Расчитанный Код нового статуса заказа |
| `startStatus` `string` | Предыдущий код статуса логистической платформы |
| `newStatus` `string` | Текущий код статуса логистической платформы |

```php
//для отмены обновления статуса должен вернуть пустой статус

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    'awz.ydelivery', 'onBeforeStatusUpdate', 
    array('handlersDelivery','onBeforeStatusUpdate')
);

class handlersDelivery {

    public static function onBeforeStatusUpdate(\Bitrix\Main\Event $event){
        $newOrdStatus = $event->getParameter('newOrdStatus');        
        
        if($newOrdStatus == 'F') $newOrdStatus = false;

        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array('newOrdStatus'=>$newOrdStatus)
        );
    }

}
```

```php
//для добавления ошибки в лог \Bitrix\Main\Result

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    'awz.ydelivery', 'onBeforeStatusUpdate', 
    array('handlersDelivery','onBeforeStatusUpdate')
);

class handlersDelivery {

    public static function onBeforeStatusUpdate(\Bitrix\Main\Event $event){
        $result = new \Bitrix\Main\Result();
        $result->addError(new \Bitrix\Main\Error('текст ошибки'));
        
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array('newOrdStatus'=>false, 'result'=>$result)
        );
    }

}

```

```php
//для изменения даты следующей проверки и отмены смены статуса модулем

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    'awz.ydelivery', 'onBeforeStatusUpdate', 
    array('handlersDelivery','onBeforeStatusUpdate')
);

class handlersDelivery {

    public static function onBeforeStatusUpdate(\Bitrix\Main\Event $event){
        $checkDate = '01.01.2023';
        $result = new \Bitrix\Main\Result();
        $result->addError(new \Bitrix\Main\Error('запрещено менять статус до '.$checkDate));
        
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array(
                'lastDate'=>\Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime($checkDate)),
                'newOrdStatus'=>false, 
                'result'=>$result
            )
        );
    }

}

```

**onOfterStatusUpdate**    
Вызывается после механизма обновления статусов модулем

| Параметр | Описание |
| --- | --- |
| `order` `\Bitrix\Sale\Order` | Объект заказа |
| `ordStatus` `string` | Код текущего статуса заказа |
| `newOrdStatus` `string` | Расчитанный Код нового статуса заказа |
| `startStatus` `string` | Предыдущий код статуса логистической платформы |
| `newStatus` `string` | Текущий код статуса логистической платформы |
| `chekerUpdate` `array('unixtime', 'код статуса в битриксе')` | Текущий установленный код статуса заказа |
| `chekerUpdateErr` `array('string')` | Ошибки обновления статуса |

```php
//обработчик можно использовать для логирования

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    'awz.ydelivery', 'onOfterStatusUpdate', 
    array('handlersDelivery','onOfterStatusUpdate')
);

class handlersDelivery {

    public static function onOfterStatusUpdate(\Bitrix\Main\Event $event){
        $order = $event->getParameter('order');
        $chekerUpdateErr = $event->getParameter('chekerUpdateErr');
        if(!empty($chekerUpdateErr)){
            $message = 'order: '.$order->getId().', errors: '.implode('; ', $chekerUpdateErr);
            //записываем сообщение
        }
    }

}
```

**onBeforeShowListItems**    
Вызывается после механизма обновления статусов модулем

| Параметр | Описание |
| --- | --- |
| `params` `array()` | Конфигурация вывода списка |
| `custom` `bool` | Флаг указывающий на кастомный вывод страницы|

Должен вернуть [`params` => `array()`, `custom`=>`bool`] с новыми параметрами, либо флагом кастомного вызова

```php
//добавим отмену заказа в фильтр в списке заявок
$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler('awz.ydelivery', 'onBeforeShowListItems', array('handlersDelivery','onBeforeShowListItems'));
class handlersDelivery {

    public static function onBeforeShowListItems(\Bitrix\Main\Event $event){
        $params = $event->getParameter('params');
        $params['FIND'][] = array(
            "NAME"=>"ORD.CANCELED", "KEY"=>"ORD.CANCELED", "TITLE"=>"Заказ отменен", "GROUP"=>"ORD.CANCELED", "FILTER_TYPE"=>"=","TYPE"=>"LIST",
            "VALUES"=> array(
                'reference'=>array(
                    'все заказы',
                    'да',
                    'нет'
                ),
                'reference_id'=>array('','Y','N')
            )
        );
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array('params'=>$params)
        );
    }
}
```

**Пример получения списка ПВЗ со своих скриптов**

```php
use Bitrix\Main\Loader;
use Awz\Ydelivery\Helper;
use Awz\Ydelivery\Api\Controller\PickPoints;
use Bitrix\Main\Type\ParameterDictionary;
Loader::includeModule('awz.ydelivery');
class myController extends PickPoints {

    public function configureActions()
    {
        return array(
            'list' => array(
                'prefilters' => array(
                )
            )
        );
    }

}

$profileId = 101;

$controller = new myController();
$api = Helper::getApiByProfileId($profileId);

$locationName = 'Новосибирск';

$api->setCacheParams(md5(serialize(array($locationName, 'geo_id'))), 2592000);
$geoIdResult = $api->geo_id($locationName);
$geoId = 0;
if($geoIdResult->isSuccess()){
    $geoIdData = $geoIdResult->getData();
    if(isset($geoIdData['result']['variants'][0]['geo_id'])){
        $geoId = $geoIdData['result']['variants'][0]['geo_id'];
    }
}else{
    $api->cleanCache();
}

$controllerResult = $controller->run(
'list',
[new ParameterDictionary(['geo_id'=>$geoId, 'profile_id'=>$profileId])]
);
if(empty($controller->getErrors())){
    echo'<pre>';print_r($controllerResult);echo'</pre>';
}
```

<!-- event-end -->

<!-- cl-start -->
## История версий

https://github.com/azahalski/awz.ydelivery/blob/master/CHANGELOG.md

<!-- cl-end -->