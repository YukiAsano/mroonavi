<?php

class base {

    protected $configs = null;

    /*
     * $B%3%s%9%H%i%/%?(B
     */
    public function __construct() {

        $this->configs = parse_ini_file(dirname(__FILE__).'/../configs/setting.ini');

    }

}
