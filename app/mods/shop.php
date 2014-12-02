<?php
class shop extends Database {

    // {{{ getInstance

    /*
     * オーバーライド
     */
    public static function getInstance($config = null)
    {
        if (!self::$_instance) {
            self::$_instance = new self($config);
        }
        return self::$_instance;
    }

    // }}}
    // {{{ getData

    public function getData($post) {

        $bind = array();

        // レスポンスベース
        $res = array(
            'success' => false,
            'limit' => 500,
            'start' => 0,
            'page' => 1,
            'total' => 0,
            'items' => array()
        );

        // test
        //$post['search1'] = 'イカ';
        //$post['search2'] = '直送';

        $query = '';
        $query .= 'SELECT ';
        $query .= '    name, ';
        $query .= '    address, ';
        $query .= '    tel, ';
        $query .= '    thumbnail, ';
        $query .= '    pc_coupon as coupon, ';
        $query .= '    latitude_wgs84 as lat, ';
        $query .= '    longitude_wgs84 as lng, ';
        $query .= '    url as url ';
        $query .= 'FROM ';
        $query .= '   tbl_shop_mecab ';
        $query .= 'WHERE ';
        $query .= '    id <> 0 ';

        // 検索ワード1
        if (isset($post['search1']) && $post['search1'] !== '') {
            $query .= '    AND ';
            $query .= '    MATCH (searchword) AGAINST (:search1) ';
            $bind['search1'] = $post['search1'];
        }

        // 検索ワード2
        if (isset($post['search2']) && $post['search2'] !== '') {
            $query .= '    AND ';
            $query .= '    MATCH (searchword) AGAINST (:search2) ';
            $bind['search2'] = $post['search2'];
        }

        // 検索ワード3
        if (isset($post['search3']) && $post['search3'] !== '') {
            $query .= '    AND ';
            $query .= '    MATCH (searchword) AGAINST (:search3) ';
            $bind[':search3'] = $post['search3'];
        }

        // 緯度経度
        if (
            isset($post['swlat']) && $post['swlat'] !== '' &&
            isset($post['swlng']) && $post['swlng'] !== '' &&
            isset($post['nelat']) && $post['nelat'] !== '' &&
            isset($post['nelng']) && $post['nelng'] !== ''
        ) {
           $query .= '    AND ';
           // // $query .= '    MBRContains(GeomFromText(\'LineString(:swlng :swlat, :nelng :nelat)\'), latlng) ';
           $query .= "    MBRContains(GeomFromText(CONCAT('LINESTRING(',:ne,', ',:sw,')')), latlng) ";
           $bind['ne'] = $post['nelng'] . ' ' . $post['nelat'];
           $bind['sw'] = $post['swlng'] . ' ' . $post['swlat'];
        }

        // 範囲テスト
        // $query .= '    AND ';
        // $query .= '    MBRContains(GeomFromText(\'LineString(140.05508422851562 36.00134056648952, 139.20089721679688 35.55345722493522)\'), latlng) ';

        $countQuery = preg_replace("/SELECT(.+?)FROM/", "SELECT COUNT(*) AS cnt FROM", $query);
        $countResult = $this->getRow($countQuery, $bind);

        if (!isset($countResult['cnt'])) {
            // とれない
            $res['success'] = false;
            return $res;
        } else if ((int)$countResult['cnt'] == 0) {
            // 取得できず
            $res['success'] = true;
            return $res;
        } else if ((int)$countResult['cnt'] > 500) {
            // 大杉
            $res['success'] = false;
            return $res;
        }

        // 実データの取得
        $rows = $this->getRows($query, $bind);

        $res['success'] = true;
        $res['total'] = $countResult['cnt'];
        $res['items'] = $rows;

        return $res;
    }

    // }}}


}
