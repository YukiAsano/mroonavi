<?php

if (!isset($_GET['type'])) {
    exit();
}

// プロジェクトルート
$rootDir = realpath(dirname(__FILE__).'/../../');

// DBクラス
require_once $rootDir . '/libs/Database.php';

// DB設定iniファイル
$dbConfig = parse_ini_file($rootDir.'/configs/database.ini');

// DB接続インスタンス

switch ($_GET['type']) {

}

switch ($_GET['type']) {
    case 'shop':
        require_once $rootDir . '/mods/shop.php';
        $obj = shop::getInstance($dbConfig);
        $obj->connect();
        $ret = $obj->getData($_GET);

        break;
    default:
        break;
}

echo json_encode($ret, true);
