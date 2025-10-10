<?php
//not remove this comment, fix empty include.php
use Bitrix\Main\Loader;

Loader::includeModule('sale');

//min version main 18.1.1

$module_id = 'awz.ydelivery';

$arJsConfig = [
    'awz_yd_lib' => [
        'js' => '/bitrix/js/'.$module_id.'/script.js',
        'css' => '/bitrix/css/'.$module_id.'/style.css',
        'lang' => '/bitrix/modules/'.$module_id.'/lang/'.LANGUAGE_ID.'/js/js_script.php',
        'rel' => ['jquery3'],
    ],
];
foreach ($arJsConfig as $ext => $arExt) {
    CJSCore::RegisterExt($ext, $arExt);
}