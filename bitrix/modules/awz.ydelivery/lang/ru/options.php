<?
$MESS["AWZ_YDELIVERY_OPT_TITLE"] = "Настройки модуля AWZ: Яндекс Доставка";
$MESS["AWZ_YDELIVERY_OPT_SECT1"] = "Общие настройки";
$MESS["AWZ_YDELIVERY_OPT_MESS1"] = "ПВЗ удалены";
$MESS["AWZ_YDELIVERY_OPT_MESS2"] = "ПВЗ загружены";
$MESS["AWZ_YDELIVERY_OPT_MESS3"] = "Для сохранения настроек модуля, нажмите кнопку сохранения еще раз!";
$MESS["AWZ_YDELIVERY_OPT_SECT2"] = "Настройки #PROFILE_NAME#";
$MESS["AWZ_YDELIVERY_OPT_SECT3"] = "Права доступа";
$MESS["AWZ_YDELIVERY_OPT_L_BARCODE_TEMPLATE"] = "Шаблон генерации штрих кода посылки";
$MESS["AWZ_YDELIVERY_OPT_L_BARCODE_TEMPLATE_MACROS1"] = "случайных 2 символа (буквы и цифры)";
$MESS["AWZ_YDELIVERY_OPT_L_BARCODE_TEMPLATE_MACROS2"] = "случайных 3 сивола (буквы и цифры)";
$MESS["AWZ_YDELIVERY_OPT_L_BARCODE_TEMPLATE_MACROS3"] = "дата заказа ([год][месяц][день])";
$MESS["AWZ_YDELIVERY_OPT_L_BARCODE_TEMPLATE_MACROS4"] = "дата заказа (месяц)";
$MESS["AWZ_YDELIVERY_OPT_L_BARCODE_TEMPLATE_MACROS5"] = "дата заказа (день)";
$MESS["AWZ_YDELIVERY_OPT_L_BARCODE_TEMPLATE_MACROS6"] = "дата заказа (год)";
$MESS["AWZ_YDELIVERY_OPT_L_BARCODE_TEMPLATE_MACROS7"] = "секунд с начала дня до фактического времени генерации кода";
$MESS["AWZ_YDELIVERY_OPT_L_BARCODE_TEMPLATE_MACROS8"] = "номер заказа";
$MESS["AWZ_YDELIVERY_OPT_L_ONDEL"] = "Отправлять запрос на отмену заявки в Доставку перед ее удалением";
$MESS["AWZ_YDELIVERY_OPT_L_DELPVZ"] = "Удалить ПВЗ из базы";
$MESS["AWZ_YDELIVERY_OPT_L_UPPVZ"] = "Обновить ПВЗ в базе";
$MESS["AWZ_YDELIVERY_OPT_L_UPPVZ_BG"] = "Обновлять ПВЗ на сайте при их получении на карте (при отключении опции будут обновляться раз в неделю на агенте)";
$MESS["AWZ_YDELIVERY_OPT_L_PROP_NAME"] = "Код свойства заказа, в котором хранится имя клиента (через запятую если несколько)";
$MESS["AWZ_YDELIVERY_OPT_L_PROPPVZ"] = "Код свойства заказа, в которое писать ид ПВЗ";
$MESS["AWZ_YDELIVERY_OPT_L_PROPPVZ_ADR"] = "Код свойства заказа, в которое писать адрес ПВЗ";
$MESS["AWZ_YDELIVERY_OPT_L_PROPPVZ_ADR_CORD"] = "Код свойства заказа (latitude,longitude), в котором хранятся координаты адреса <br>(можно указать 2 свойства через запятую, если координаты разбиты на 2 свойства)";
$MESS["AWZ_YDELIVERY_OPT_L_PROPPVZ_ADR_TMPL"] = "Шаблон адреса ПВЗ";
$MESS["AWZ_YDELIVERY_OPT_L_PROPPVZ_ADR_TMPL_DESC"] = "#PHONE# - телефон, <br>#ADDRESS# - полный адрес, <br>#ID# - ид ПВЗ, <br>#NAME# - название ПВЗ, <br>#TYPE# - тип ПВЗ";
$MESS["AWZ_YDELIVERY_OPT_L_PROPPVZ_DATE"] = "Код свойства заказа в которое писать дату доставки";
$MESS["AWZ_YDELIVERY_OPT_L_STATUS_AUTOCREATE"] = "Создавать заявку в доставку при получении заказом статуса";
$MESS["AWZ_YDELIVERY_OPT_L_STATUS_SYNC"] = "Включить синхронизацию статусов";
$MESS["AWZ_YDELIVERY_OPT_L_STATUS_SYNC_INTERVAL"] = "Интервал проверки статусов в часах";
$MESS["AWZ_YDELIVERY_OPT_L_STATUS_SYNC_MAX"] = "Максимум запросов на 1 трек-код";
$MESS["AWZ_YDELIVERY_OPT_L_STATUS_SYNC_FIN"] = "Финализировать проверку в статусах";
$MESS["AWZ_YDELIVERY_OPT_L_PAY_CHANGE"] = "Привязка платежных систем для";
$MESS["AWZ_YDELIVERY_OPT_L_PAY_CHANGE_HIDE"] = "Включить скрытие недоступных платежных систем в компоненте оформления заказа";
$MESS["AWZ_YDELIVERY_OPT_L_STATUS_SYNC_FROM"] = "Из каких статусов разрешено переводить заказ";
$MESS["AWZ_YDELIVERY_OPT_L_STATUS_SYNC_TO"] = "В какой статус переводить";
$MESS["AWZ_YDELIVERY_OPT_L_BTN_SAVE"] = "Сохранить";
$MESS["AWZ_YDELIVERY_OPT_L_GROUP1"] = "Загрузка и обновление списка ПВЗ";
$MESS["AWZ_YDELIVERY_OPT_L_GROUP1_DESC"] = "Отметьте опции и сохраните настройки! <br>При одновременной активности тестового апи и боевого будут загружены оба варианта ПВЗ. <br> Список ПВЗ обновляется автоматически на агенте 1 раз в неделю, либо при их получении для карты.";
$MESS["AWZ_YDELIVERY_OPT_L_GROUP2_DESC"] = "Ручное обновление статусов в заявке также не работает без данной опции!";
$MESS["AWZ_YDELIVERY_OPT_L_GROUP3_DESC"] = "Рекомендуется протестировать правильность заполнения заявок в ручном режиме (в заказе будет кнопка на создание заявки)!";
$MESS["AWZ_YDELIVERY_OPT_L_GROUP4_DESC"] = "Например, Максимальный срок доставки в часах разделить на интервал проверки в часах.";
$MESS["AWZ_YDELIVERY_OPT_L_GROUP5_DESC"] = "Проверяется 15 последних не финализированных заказов с учетом данного срока раз в 10 минут. Не рекомендуется выставлять менее 4 часов (15*10/4 = 37 заказов в час, если количество заказов больше, то уменьшайте интервал).";
$MESS["AWZ_YDELIVERY_OPT_L_PROFILE_PROP"] = "Привязка свойств";
$MESS["AWZ_YDELIVERY_OPT_L_PROFILE_PAYS"] = "Привязка платежных систем";
$MESS["AWZ_YDELIVERY_OPT_L_PROFILE_AUTO"] = "Автоматизация";
$MESS["AWZ_YDELIVERY_OSIBKA_BRONIROVANIE"] = "Ошибка бронирование заказа";
$MESS["AWZ_YDELIVERY_ZAKAZ_ZAGRUJEN"] = "Заказ загружен";
$MESS["AWZ_YDELIVERY_ZAKAZ_PODTVERJDEN"] = "Заказ подтвержден";
$MESS["AWZ_YDELIVERY_ZAREZERVIROVANO_MEST"] = "Зарезервировано место под посылку (К2)";
$MESS["AWZ_YDELIVERY_OJIDAETSA_POSTAVKA"] = "Ожидается поставка (К2)";
$MESS["AWZ_YDELIVERY_NEOBHODIMO_PERESOZDA"] = "Необходимо пересоздать заказ";
$MESS["AWZ_YDELIVERY_IDET_POISK_ISPOLNITE"] = "Идет поиск исполнителя";
$MESS["AWZ_YDELIVERY_ZAKAZ_SOZDAN_V_SORTI"] = "Заказ создан в сортировочном центре";
$MESS["AWZ_YDELIVERY_ZAKAZ_SOZDAN_V_SISTE"] = "Заказ создан в системе службы доставки";
$MESS["AWZ_YDELIVERY_NA_SKLADE_SORTIROVOC"] = "На складе сортировочного центра";
$MESS["AWZ_YDELIVERY_GOTOV_K_OTPRAVKE_V_S"] = "Готов к отправке в службу доставки";
$MESS["AWZ_YDELIVERY_NA_SKLADE_SLUJBY_DOS"] = "На складе службы доставки";
$MESS["AWZ_YDELIVERY_NET_NA_SKLADE"] = "Нет на складе";
$MESS["AWZ_YDELIVERY_OJIDAETSA_V_SLUJBE_D"] = "Ожидается в службе доставки";
$MESS["AWZ_YDELIVERY_ZAKAZ_PODGOTOVLEN_NA"] = "Заказ подготовлен на магистраль";
$MESS["AWZ_YDELIVERY_NA_MAGISTRALI"] = "На магистрали";
$MESS["AWZ_YDELIVERY_V_GORODE_POLUCATELA"] = "В городе получателя";
$MESS["AWZ_YDELIVERY_POLQZOVATELQ_POLOJIL"] = "Пользователь положил посылку в корзину (К2)";
$MESS["AWZ_YDELIVERY_KURQER_NAZNACEN"] = "Курьер назначен (K2)";
$MESS["AWZ_YDELIVERY_GOTOVY_DOSTAVITQ_K"] = "Готовы доставить (К2)";
$MESS["AWZ_YDELIVERY_POSYLKA_DOSTAVLAETSA"] = "Посылка доставляется клиенту";
$MESS["AWZ_YDELIVERY_SROK_HRANENIA_ZAKAZA"] = "Срок хранения заказа в службе доставки увеличен";
$MESS["AWZ_YDELIVERY_SROK_HRANENIA_ZAKAZA1"] = "Срок хранения заказа в службе доставки истек";
$MESS["AWZ_YDELIVERY_ZAKAZ_OTMENEN"] = "Заказ отменен";
$MESS["AWZ_YDELIVERY_DOSTAVLEN"] = "Доставлен";
$MESS["AWZ_YDELIVERY_DOSTAVLEN_CASTICNO"] = "Доставлен частично";
$MESS["AWZ_YDELIVERY_DOSTAVKA_PERENESENA"] = "Доставка перенесена";
$MESS["AWZ_YDELIVERY_DOSTAVKA_PERENESENA1"] = "Доставка перенесена по просьбе клиента";
$MESS["AWZ_YDELIVERY_DOSTAVKA_PERENESENA2"] = "Доставка перенесена службой доставки";
$MESS["AWZ_YDELIVERY_NEUDACNAA_POPYTKA_VR"] = "Неудачная попытка вручения";
$MESS["AWZ_YDELIVERY_ZAKAZ_NE_MOJET_BYTQ"] = "Заказ не может быть доставлен";
$MESS["AWZ_YDELIVERY_DOSTAVKA_OTMENENA_PO"] = "Доставка отменена по просьбе клиента";
$MESS["AWZ_YDELIVERY_ZAAVKA_NA_DOSTAVKU_O"] = "Заявка на доставку отклонена (до поступления товара на склад";
$MESS["AWZ_YDELIVERY_DOSTAVLEN_PODTVERJ"] = "Доставлен - подтверждено";
$MESS["AWZ_YDELIVERY_GOTOVITSA_K_VOZVRATU"] = "Готовится к возврату";
$MESS["AWZ_YDELIVERY_GOTOVITSA_K_VOZVRATU1"] = "Готовится к возврату в СЦ";
$MESS["AWZ_YDELIVERY_VOZVRATNYY_ZAKAZ_NA"] = "Возвратный заказ на складе сортировочного центра";
$MESS["AWZ_YDELIVERY_GOTOV_DLA_PEREDACI_M"] = "Готов для передачи магазину";
$MESS["AWZ_YDELIVERY_OTMENEN_SORTIROVOCNY"] = "Отменен сортировочным центром";
$MESS["AWZ_YDELIVERY_VOZVRAT_NA_PUTI_K_MA"] = "Возврат на пути к магазину";
$MESS["AWZ_YDELIVERY_VODITELQ_PRIEHAL_V_T"] = "Водитель приехал в точку возврата.";
$MESS["AWZ_YDELIVERY_ZAKAZ_VOZVRASEN_V_TO"] = "Заказ возвращен в точку отправления";
$MESS["AWZ_YDELIVERY_ZAKAZ_ZAVERSEN"] = "Заказ завершен";
$MESS["AWZ_YDELIVERY_OTMENA_PODTVERJDEN"] = "Отмена - подтверждено";
$MESS["AWZ_YDELIVERY_ZAKAZ_BYL_OTMENEN_KL"] = "Заказ был отменен клиентом платно (водитель уже приехал)";
$MESS["AWZ_YDELIVERY_V_PUNKTE_SAMOVYVOZA"] = "В пункте самовывоза / в дарксторе";
$MESS["AWZ_YDELIVERY_OSIBKA_SOZDANIA_ZAKA"] = "Ошибка создания заказа в сортировочном центре";
$MESS["AWZ_YDELIVERY_UTERAN"] = "Утерян";
$MESS["AWZ_YDELIVERY_STATUS_UTOCNAETSA"] = "Статус уточняется";
$MESS["AWZ_YDELIVERY_OTMENEN_POLQZOVATELE"] = "Отменен пользователем";
$MESS["AWZ_YDELIVERY_VSE_STATUSY"] = "Все статусы";
$MESS["AWZ_YDELIVERY_OTKLUCITQ"] = "Отключить";
$MESS["AWZ_YDELIVERY_OPT_L_UPPVZ_LINK"] = "Связь ПВЗ с внешними источниками";
$MESS["AWZ_YDELIVERY_OPT_L_UPPVZ_LOAD"] = "Перейти к загрузке";
$MESS["AWZ_YDELIVERY_OPT_L_UPPVZ_LINK_ON"] = "Включить поиск ПВЗ по внешним кодам";
$MESS["AWZ_YDELIVERY_OPT_MAP_ADRESS"] = "Включить строку поиска адреса на Яндекс Карте (обязателен ключ API)";
$MESS["AWZ_YDELIVERY_OPT_MAP_ADRESS_DESC"] = "Кабинет разработчика на Яндексе";
$MESS["AWZ_YDELIVERY_OPT_MAP_ADRESS_KEY1"] = "Ключ JavaScript API";
$MESS["AWZ_YDELIVERY_OPT_MAP_ADRESS_KEY2"] = "Ключ API Геосаджеста";
$MESS["AWZ_YDELIVERY_OPT_BALUN_VARIANT"] = "Включить стандартные иконки Яндекс Карты";
$MESS["AWZ_YDELIVERY_OPT_DSBL_STATUS"] = "Отключить статусы в логистической платформе";
$MESS["AWZ_YDELIVERY_OPT_DSBL_STATUS_INFO"] = "Отключен настройками";
$MESS["AWZ_YDELIVERY_OPT_L_YM_TRADING_ON"] = "Включить обработку ПВЗ из json запроса модуля yandex.market (должны быть загружены внешние коды пвз яндекс маркета)";
$MESS["AWZ_YDELIVERY_OPT_L_YM_TRADING2_ON"] = "Включить обработку Адреса из json запроса модуля yandex.market";

$MESS["AWZ_YDELIVERY_ENQ_STAT_CREATED_IN_PLATFORM"] = "Заказ создан в логистической платформе";
$MESS["AWZ_YDELIVERY_ENQ_STAT_CREATED_IN_PLATFORM_DRAFT"] = "Заказ создан в логистической платформе, Заказ создан";
$MESS["AWZ_YDELIVERY_ENQ_STAT_VALIDATING"] = "Заказ находится на проверке";
$MESS["AWZ_YDELIVERY_ENQ_STAT_CREATED"] = "Заказ подтвержден";
$MESS["AWZ_YDELIVERY_ENQ_STAT_CREATED_DELIVERY_PROCESSING_STARTED"] = "Заказ подтвержден, Заказ создается в сортировочном центре";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_TRACK_RECEIVED"] = "Заказ создан в системе службы доставки";
$MESS["AWZ_YDELIVERY_ENQ_STAT_SORTING_CENTER_PROCESSING_STARTED"] = "Заказ обрабатывается в сортировочном центре";
$MESS["AWZ_YDELIVERY_ENQ_STAT_SORTING_CENTER_PROCESSING_STARTED_DELIVERY_LOADED"] = "Заказ обрабатывается в сортировочном центре, Заказ добавлен в текущую отгрузку";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_LOADED_DELIVERY_PROCESSING_STARTED"] = "Заказ добавлен в текущую отгрузку, Заказ подтвержден в сортировочном центре";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_PROCESSING_STARTED_DELIVERY_LOADED"] = "Заказ подтвержден в сортировочном центре, Заказ добавлен в текущую отгрузку";
$MESS["AWZ_YDELIVERY_ENQ_STAT_SORTING_CENTER_CANCELED"] = "Отменен сортировочным центром";
$MESS["AWZ_YDELIVERY_ENQ_STAT_RETURN_PREPARING"] = "Готовится к возврату";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_TRACK_RECEIVED_DELIVERY_LOADED"] = "Заказ создан в системе службы доставки, Заказ добавлен в текущую отгрузку";
$MESS["AWZ_YDELIVERY_ENQ_STAT_SORTING_CENTER_PROCESSING_STARTED_DELIVERY_PROCESSING_STARTED"] = "Заказ обрабатывается в сортировочном центре, Заказ создан в сортировочном центре";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_LOADED_DELIVERY_AT_START"] = "Заказ добавлен в текущую отгрузку, На складе сортировочного центра";
$MESS["AWZ_YDELIVERY_ENQ_STAT_SORTING_CENTER_PREPARED"] = "Готов к отправке в службу доставки";
$MESS["AWZ_YDELIVERY_ENQ_STAT_SORTING_CENTER_TRANSMITTED"] = "Отправлен в службу доставки";
$MESS["AWZ_YDELIVERY_ENQ_STAT_SORTING_CENTER_TRANSMITTED_DELIVERY_AT_START"] = "Отправлен в службу доставки, На складе службы доставки";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_AT_START_DELIVERY_AT_START_SORT"] = "На складе службы доставки, Заказ готовится к отправке";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_AT_START_SORT_DELIVERY_TRANSPORTATION_RECIPIENT"] = "Заказ готовится к отправке, Посылка доставляется клиенту";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_DELIVERED"] = "Доставлен";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERED_FINISH"] = "Доставлен полностью - подтверждено";
$MESS["AWZ_YDELIVERY_ENQ_STAT_RETURN_PREPARING_DRAFT"] = "Готовится к возврату, Заказ создан";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_TRANSPORTATION_RECIPIENT_DELIVERY_UPDATED_BY_RECIPIENT"] = "Посылка доставляется клиенту, Доставка перенесена по просьбе клиента";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_UPDATED_BY_RECIPIENT_DELIVERY_TRANSPORTATION_RECIPIENT"] = "Доставка перенесена по просьбе клиента, Посылка доставляется клиенту";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_TRANSPORTATION_RECIPIENT_DELIVERY_AT_START_SORT"] = "Посылка доставляется клиенту, Заказ готовится к отправке";
$MESS["AWZ_YDELIVERY_ENQ_STAT_ZAKAZ_GOTOVITSYA_K_OTPRAVKE_DELIVERY_AT_START_SORT_DELIVERY_TRANSPORTATION_RECIPIENT"] = "Заказ готовится к отправке, Заказ готовится к отправке, Посылка доставляется клиенту";
$MESS["AWZ_YDELIVERY_ENQ_STAT_ZAKAZ_PODTVERZHDEN_V_SORTIROVO_DELIVERY_PROCESSING_STARTED_DELIVERY_LOADED"] = "Заказ подтвержден в сортировочном центре, Заказ подтвержден в сортировочном центре, Заказ добавлен в текущую отгрузку";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_PROCESSING_STARTED_DELIVERY_PROCESSING_STARTED"] = "Заказ создан в сортировочном центре, Заказ подтвержден в сортировочном центре";
$MESS["AWZ_YDELIVERY_ENQ_STAT_ON_THE_TRACK"] = "Доставляется в город получателя";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_ARRIVED_PICKUP_POINT"] = "В пункте самовывоза";
$MESS["AWZ_YDELIVERY_ENQ_STAT_FINISHED"] = "Доставлен полностью - подтверждено";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_TRANSMITTED_TO_RECIPIENT"] = "Заказ вручен клиенту";
$MESS["AWZ_YDELIVERY_ENQ_STAT_SORTING_CENTER_TRANSMITTED_DELIVERY_AT_START_SORT"] = "Отправлен в службу доставки, Заказ готовится к отправке";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_AT_START_SORT_DELIVERY_AT_START"] = "Заказ готовится к отправке, На складе службы доставки";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_AT_START_DELIVERY_TRANSPORTATION_RECIPIENT"] = "На складе службы доставки, Посылка доставляется клиенту";
$MESS["AWZ_YDELIVERY_ENQ_STAT_CANCELLED"] = "Заказ отменен";
$MESS["AWZ_YDELIVERY_ENQ_STAT_RETURN_PREPARING_DELIVERY_PROCESSING_STARTED"] = "Готовится к возврату, Заказ подтвержден в сортировочном центре";
$MESS["AWZ_YDELIVERY_ENQ_STAT_CANCELLED_USER"] = "Отменен пользователем";
$MESS["AWZ_YDELIVERY_ENQ_STAT_CANCELED_IN_PLATFORM"] = "Заказ отменен в логистической платформе";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_UPDATED_BY_RECIPIENT_DELIVERY_AT_START_SORT"] = "Доставка перенесена по просьбе клиента, Заказ готовится к отправке";
$MESS["AWZ_YDELIVERY_ENQ_STAT_POSYLKA_DOSTAVLYAETSYA_KLIENTU_DELIVERY_TRANSPORTATION_RECIPIENT_DELIVERY_UPDATED_BY_RECIPIENT"] = "Посылка доставляется клиенту, Посылка доставляется клиенту, Доставка перенесена по просьбе клиента";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_TRANSPORTATION"] = "Доставляется в город получателя";
$MESS["AWZ_YDELIVERY_ENQ_STAT_SORTING_CENTER_RETURN_PREPARING"] = "Готовится к возврату";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_STORAGE_PERIOD_EXTENDED"] = "Срок хранения заказа в службе доставки увеличен";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_STORAGE_PERIOD_EXPIRED"] = "Срок хранения заказа в службе доставки истек";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_UPDATED"] = "Доставка перенесена отправителем";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_UPDATED_DELIVERY_AT_START_SORT"] = "Доставка перенесена отправителем, Заказ готовится к отправке";
$MESS["AWZ_YDELIVERY_ENQ_STAT_SORTING_CENTER_TRANSMITTED_DELIVERY_LOADED"] = "Отправлен в службу доставки, Заказ добавлен в текущую отгрузку";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_TRANSPORTATION_RECIPIENT_DELIVERY_UPDATED_BY_DELIVERY"] = "Посылка доставляется клиенту, Доставка перенесена службой доставки";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_UPDATED_BY_DELIVERY_DELIVERY_TRANSPORTATION_RECIPIENT"] = "Доставка перенесена службой доставки, Посылка доставляется клиенту";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DOSTAVKA_PERENESENA_PO_PROSBE__DELIVERY_UPDATED_BY_RECIPIENT_DELIVERY_TRANSPORTATION_RECIPIENT"] = "Доставка перенесена по просьбе клиента, Доставка перенесена по просьбе клиента, Посылка доставляется клиенту";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_AT_START_DELIVERY_LOADED"] = "На складе службы доставки, Заказ добавлен в текущую отгрузку";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_LOADED_DELIVERY_AT_START_SORT"] = "Заказ добавлен в текущую отгрузку, Заказ готовится к отправке";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_UPDATED_DELIVERY_TRANSPORTATION_RECIPIENT"] = "Доставка перенесена отправителем, Посылка доставляется клиенту";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_ATTEMPT_FAILED"] = "Неудачная попытка вручения";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_ATTEMPT_FAILED_DELIVERY_TRANSPORTATION_RECIPIENT"] = "Неудачная попытка вручения, Посылка доставляется клиенту";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_AT_START_SORT_DELIVERY_UPDATED_BY_DELIVERY"] = "Заказ готовится к отправке, Доставка перенесена службой доставки";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_UPDATED_BY_DELIVERY_DELIVERY_AT_START_SORT"] = "Доставка перенесена службой доставки, Заказ готовится к отправке";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_LOADED_DELIVERY_UPDATED_BY_DELIVERY"] = "Заказ добавлен в текущую отгрузку, Доставка перенесена службой доставки";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_UPDATED_BY_DELIVERY_DELIVERY_LOADED"] = "Доставка перенесена службой доставки, Заказ добавлен в текущую отгрузку";
$MESS["AWZ_YDELIVERY_ENQ_STAT_ZAKAZ_DOBAVLEN_V_TEKUSHCHUYU_O_DELIVERY_LOADED_DELIVERY_UPDATED_BY_DELIVERY"] = "Заказ добавлен в текущую отгрузку, Заказ добавлен в текущую отгрузку, Доставка перенесена службой доставки";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_UPDATED_BY_DELIVERY_DELIVERY_AT_START"] = "Доставка перенесена службой доставки, На складе службы доставки";
$MESS["AWZ_YDELIVERY_ENQ_STAT_DELIVERY_ATTEMPT_FAILED_DELIVERY_UPDATED_BY_DELIVERY"] = "Неудачная попытка вручения, Доставка перенесена службой доставки";
$MESS["AWZ_YDELIVERY_OPT_GRAFIK_OK"] = "График доставки загружен";
$MESS["AWZ_YDELIVERY_OPT_GRAFIK_FILE"] = "Файл с данными графика доставки";
$MESS["AWZ_YDELIVERY_OPT_GRAFIK_FILE_L1"] = "Загрузить файл с данными графиков доставки";
$MESS["AWZ_YDELIVERY_OPT_GRAFIK_FILE_L2"] = "Очистить данные графика доставки";
$MESS["AWZ_YDELIVERY_OPT_ENABLE_LOG_TITLE"] = "Логирование";
$MESS["AWZ_YDELIVERY_OPT_ENABLE_LOG"] = "Лог запросов";
$MESS["AWZ_YDELIVERY_OPT_ENABLE_LOG_1"] = "Отключить лог";
$MESS["AWZ_YDELIVERY_OPT_ENABLE_LOG_2"] = "Все запросы";
$MESS["AWZ_YDELIVERY_OPT_ENABLE_LOG_3"] = "Только ошибки";
$MESS["AWZ_YDELIVERY_OPT_ENABLE_LOG_PATH"] = "Просмотр лога";

$MESS["AWZ_YDELIVERY_ENQ_STATEX_NEW"] = "Новая заявка";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_ESTIMATING"] = "Оценка заявки";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_READY_FOR_APPROVAL"] = "Подтверждение заявки";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_ACCEPTED"] = "Заявка подтверждена";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_PERFORMER_LOOKUP"] = "Начинается поиск курьера";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_PERFORMER_DRAFT"] = "Производится поиск курьера";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_PERFORMER_FOUND"] = "Курьер в пути";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_PICKUP_ARRIVED"] = "Забор заказа";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_READY_FOR_PICKUP_CONFIRMATION"] = "Подтверждение забора заказа";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_PICKUPED"] = "Забор подтвержден";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_DELIVERY_ARRIVED"] = "Прибытие к получателю";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_READY_FOR_DELIVERY_CONFIRMATION"] = "Передача товара";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_PAY_WAITING"] = "Ожидает оплаты";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_DELIVERED"] = "Доставка подтверждена";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_DELIVERED_FINISH"] = "Заказ завершен";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_RETURNING"] = "Возврат товара";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_RETURN_ARRIVED"] = "Прибытие в точку возврата";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_READY_FOR_RETURN_CONFIRMATION"] = "Подтверждение возврата";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_RETURNED"] = "Возврат подтвержден";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_RETURNED_FINISH"] = "Завершен с возвратом";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_CANCELLED_BY_TAXI"] = "Отменен курьером";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_CANCELLED"] = "Отменен бесплатно";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_CANCELLED_WITH_PAYMENT"] = "Отменен платно с возвратом";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_CANCELLED_WITH_ITEMS_ON_HANDS"] = "Отменен платно без возврата";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_FAILED"] = "Выполнение невозможно";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_ESTIMATING_FAILED"] = "Не удалось оценить заявку";
$MESS["AWZ_YDELIVERY_ENQ_STATEX_PERFORMER_NOT_FOUND"] = "Не удалось найти курьера";

