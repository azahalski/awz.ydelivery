- исключение из лога pickup list;
- добавлено получение адреса с модуля yandex.market;
- логика вывода карты для отметки GPS;
- добавлены агенты расчета сроков;
- добавлен параметр НДС;
- добавлен выбор срока ПВЗ по умолчанию;
- улучшения поддержки yandex.market;
- фикс регулярок проверки точки ПВЗ при расчете;
- улучшение алгоритма получения текущего ПВЗ при расчете;
- добавлен статический кеш доступных ПВЗ для дальнейших расчетов доставки на обработчике onSaleDeliveryServiceCalculate;
- добавлен учет времени добавления с расчета сроков при расчете сроков;
- рефакторинг;
- добавлен параметр доп. срока доставки в контроллер, для карты и последующих расчетов;
- добавлен обработчик в контроллер для переопределения или фильтрации вывода списка ПВЗ на карту;
- замена deprecated CUtil::InitJSCore;
- добавление опций выбора вида точки на карте;
- добавление выбора доступных интервалов доставки перед запросом офферов;
- улучшена совместимость с php 8.1;