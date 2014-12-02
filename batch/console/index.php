<?php

$rootDir = realpath(dirname(__FILE__).'/../');

ini_set('date.timezone', 'Asia/Tokyo');
ini_set('error_log', ($rootDir.'/logs/php_error_log_batch.txt'));
ini_set('log_errors', true);

function error_handler($level, $message) {
    error_log($message);
    exit;
}

set_error_handler('error_handler');

// 処理タイプ
$type = str_replace('--type=', '', $argv[1]);

// 日付
$date = str_replace('--date=', '', $argv[2]);
$date = $date === '' ? null : $date;

//var_export($argv, true);

switch ($type) {
    case 'getshop':
        // エリアコード
        $areaCd = str_replace('--area=', '', $argv[3]);

        // JSON取得
        require_once($rootDir.'/gnavi/getShopJson.php');
        $cls = new getShopJson();
        $cls->getRestaurant($date, $areaCd);

        break;
    case 'import':
        // エリアコード
        $areaCd = str_replace('--area=', '', $argv[3]);

        require_once($rootDir.'/gnavi/shopImport.php');
        $cls = new shopImport();
        $cls->setRestaurant($date, $areaCd);
        break;
    default:
        break;
}
