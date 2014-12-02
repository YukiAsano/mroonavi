<?php

require_once dirname(__FILE__) . '/../libs/base.php';
set_time_limit(0);

class getShopJson extends base {

    /*
     * レストラン取得API
     */
    private $_restaurantUrl = 'http://api.gnavi.co.jp/ver2/RestSearchAPI/';

    /*
     * URL
     */
    public $url = null;

    /*
     * LIMIT
     */
    public $limit = 500;

    /*
     * エリア
     */
    public $area = null;

    /*
     * URL設定
     */
    public function getUrl() {

        if (is_null($this->url)) {

            $url = $this->_restaurantUrl;

            $params = array(
                'keyid'  => $this->configs['gnavi_key'],
                'area'   => $this->area,
                'format' => 'json',
            );

            $url .= '?';
            $url .= http_build_query($params);

            $this->url = $url;
        }

        return $this->url;
    }

    /*
     * ログ出力
     */
    public function outputLog($info) {
        // ログ出力
        error_log(json_encode($info)."\n", 3, dirname(__FILE__).'/../logs/getshop.log');
    }

    /*
     * エリア取得
     */
    public function getRestaurant($jsonDate = null, $areaCd = null) {

        // 処理日時
        $date = is_null($jsonDate) ? date('Ymd') : $jsonDate;

        if (is_null($areaCd)) {
            // エリアコードがない
            $msg = 'Area code is not specified.';
            $errInfo = array(
                'date' => date('Y-m-d H:i:s'),
                'code' => 'getshop001',
                'success' => 'false',
                'info' => '',
                'msg' => $msg
            );
            $this->outputLog($errInfo);
            exit();
        }

        $this->area = $areaCd;

        // JSON出力先
        $dir = dirname(__FILE__) . '/json/' . $date . '/' . $this->area . '/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        // URL
        $url = $this->getUrl();

        // 取得件数
        $limit = $this->limit;

        $page = 0;
        $i = 0;

        // 最終ページ設定
        $json = file_get_contents($url.'&coordinates_mode=2&hit_per_page=1&offset_page=1');
        $arr = json_decode($json, true);

        if (!isset($arr['rest'])) {

            $msg = 'Failed to get the JSON file.';
            $errInfo = array(
                'date' => date('Y-m-d H:i:s'),
                'code' => 'getshop002',
                'success' => 'false',
                'info' => array(
                    'area' => $this->area,
                    'offset' => 0,
                    'total_hit_count' => 0,
                ),
                'msg' => $msg,
            );
            $this->outputLog($errInfo);
            exit();
        }

        // 最終ページ
        $total = $arr['total_hit_count'];
        $endPage = floor($total / $limit);

        while($page <= $endPage) {

            ++$page;

            unset($json);
            unset($arr);

            $attackUrl = $url.'&hit_per_page='.$limit.'&offset_page='.$page;

            $json = file_get_contents($attackUrl);

            file_put_contents($dir.'shop'.$page.'.json', $json);

            $arr = json_decode($json, true);

            if (!isset($arr['rest'])) {

                // 終了
                $msg = 'An error has occurred in the JSON file incorporation.';

                $errInfo = array(
                    'date' => date('Y-m-d H:i:s'),
                    'code' => 'getshop003',
                    'success' => $endPage === $page,
                    'info' => array(
                        'area' => $this->area,
                        'offset' => $page,
                        'total_hit_count' => $total,
                    ),
                    'msg' => $msg,
                );

                $this->outputLog($errInfo);
                exit();
            }
        }

        $msg = 'Successful incorporation';
        $info = array(
            'date' => date('Y-m-d H:i:s'),
            'code' => 'getshop004',
            'success' => true,
            'info' => array(
                'area' => $this->area,
                'offset' => $page,
                'total_hit_count' => $total,
            ),
            'msg' => $msg,
        );

        // ログ出力
        $this->outputLog($info);
        exit();
    }
}
