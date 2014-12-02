<?php

class base {

    protected $configs = null;

    /*
     * コンストラクタ
     */
    public function __construct() {

        $this->configs = parse_ini_file(dirname(__FILE__).'/../configs/setting.ini');

    }

}
