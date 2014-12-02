<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../libs/base.php';
require_once dirname(__FILE__) . '/../libs/Database.php';
set_time_limit(0);

class shopImport extends base {

    protected $Bulky = null;

    /*
     * ファイルパス
     */
    protected $jsonPath = '/json/##DATE##/##AREA##/';

    /*
     * ファイルプレフィックス
     */
    protected $filePrefix = 'shop';

    /*
     * ファイル拡張子
     */
    protected $fileExtension = 'json';

    /*
     * エリア
     */
    public $area = null;

    /*
     * Bulky取得
     */
    public function getBulky() {

        if (is_null($this->Bulky)) {

            // キャラセット取得
            $charset = $this->configs['charset'];

            // ホスト名取得
            $host = $this->configs['host'];

            // ユーザー名取得
            $user = $this->configs['user'];

            // パスワード取得
            $password = $this->configs['password'];

            // データーベース名取得
            $database = $this->configs['database'];

            // ポート番号取得
            $port = $this->configs['port'];

            // ソケット取得
            $socket = $this->configs['socket'];

            // DSN初期化
            $dsn = '';

            $dsn .= strtolower('mysql') . ':';

            // ホスト設定
            $dsn .= 'host=' . $host;

            // データベース名
            $dsn .= ';dbname=' . $database;

            // ポート
            if (!empty($port)) {
                $dsn .= ';port=' . $port;
            }

            // ソケット
            if (!empty($socket)) {
                $dsn .= ';unix_socket=' . $socket;
            }

            // オプション設定
            $options = array();

            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $charset;

            // Bulky準備
            $Bulky = new Yuyat_Bulky_QueueFactory(
                new Yuyat_Bulky_DbAdapter_PdoMysqlAdapter(
                    new PDO($dsn, $user, $password, $options)
                ),
                50
            );
        } else {
            $Bulky = $this->Bulky;
        }
        return $Bulky;
    }

    /*
     * ログ出力
     */
    public function outputLog($info) {
        // ログ出力
        error_log(json_encode($info)."\n", 3, dirname(__FILE__).'/../logs/import.log');
    }

    /*
     * 検索用緯度経度更新
     */
    public function updateLatLng() {

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $db->exec(implode('', array(
                'UPDATE tbl_shop_mecab t1, ',
                '( ',
                '    SELECT ',
                '        t2.id, ',
                '        POINT (t2.longitude_wgs84, t2.latitude_wgs84) AS latlng ',
                '    FROM ',
                '        tbl_shop_mecab t2 ',
                '    WHERE ',
                '        t2.area_cd = "'.$this->area.'" ',
                ') A ',
                'SET t1.latlng = A.latlng ',
                'WHERE ',
                '    t1.id = A.id',
            )));

            $db->commit();

        } catch (PDOException $e) {
            $db->rollback();
            throw new Exception($e->getMessage());
        }

    }

    /*
     * 飲食店登録
     */
    public function setRestaurant($jsonDate = null, $areaCd = null) {

        if (is_null($jsonDate) || is_null($areaCd)) {
            // JSONの日付とエリアコードのどちらかがない
            $msg = 'Argument is not enough.';
            $errInfo = array(
                'date' => date('Y-m-d H:i:s'),
                'code' => 'import001',
                'success' => 'false',
                'info' => $jsonDate .'-'.$areaCd,
                'msg' => $msg
            );
            $this->outputLog($errInfo);
            exit();
        }

        $this->area = $areaCd;

        // JSONファイルパス
        $dirPath = dirname(__FILE__).$this->jsonPath;
        $dirPath = str_replace('##DATE##', $jsonDate, $dirPath);
        $dirPath = str_replace('##AREA##', $areaCd, $dirPath);
        $prefix = $this->filePrefix;
        $ext = $this->fileExtension;

        if (!file_exists($dirPath)) {
            // ディレクトリがない
            $msg = 'Directory does not exist.';
            $errInfo = array(
                'date' => date('Y-m-d H:i:s'),
                'code' => 'import002',
                'success' => 'false',
                'info' => $dirPath,
                'msg' => $msg
            );
            $this->outputLog($errInfo);
            exit();
        }

        $colNames = array(
            'gnavi_id',
            'update_date',
            'name',
            'name_kana',
            'business_hour',
            'holiday',
            'address',
            'tel',
            'fax',
            'pr_short',
            'pr_long',
            'access',
            'budget',
            'category',
            'category_name_l_1',
            'category_name_l_2',
            'category_name_l_3',
            'category_name_l_4',
            'category_name_l_5',
            'category_name_s_1',
            'category_name_s_2',
            'category_name_s_3',
            'category_name_s_4',
            'category_name_s_5',
            'mobile_site',
            'mobile_coupon',
            'pc_coupon',
            'latitude',
            'longitude',
            'latitude_wgs84',
            'longitude_wgs84',
            'district',
            'prefname',
            'areaname_s',
            'url',
            'url_mobile',
            'thumbnail',
            'qrcode',
            'area_cd',
            'create_date',
            // todo: どうする？
            'searchword',
        );

        // 処理時刻
        $date = date('Y-m-d H:i:s');

        // Bulkyさん
        $queueFactory = $this->getBulky();

        // todo: テーブルどうする？
        // $table = 'tbl_shop';
        $table = 'tbl_shop_mecab';

        // インサート対象のテーブルとカラムを設定したキューの作成
        $queue = $queueFactory->createQueue($table, $colNames);

        // DB接続インスタンス
        $db = Database::getInstance($this->configs);
        $db->connect();

        // エラーハンドラの設定
        $queue->on('error', function ($records) {
            $msg = 'Import Error!!!';
            $errInfo = array(
                'date' => date('Y-m-d H:i:s'),
                'code' => 'import003',
                'success' => 'false',
                'info' => $records,
                'msg' => $msg
            );
            $this->outputLog($errInfo);
            exit();
        });

        $page = 0;
        $i = 0;

        $db->beginTransaction();
        try {
            // インポートするエリア全削除
            $db->remove($table, array('area_cd' => $areaCd));
            $db->commit();
        } catch (PDOException $e) {
            $db->rollback();
            throw new Exception($e->getMessage());
        }

        while(1) {

            ++$page;

            // JSONファイルパス
            $attackPath = $dirPath.$prefix.$page.'.'.$ext;

            if (!file_exists($attackPath)) {
                if ($page === 1) {
                    // そもそもjsonファイルがない
                    $msg = 'JSON file does not exist.';
                    $errInfo = array(
                        'date' => date('Y-m-d H:i:s'),
                        'code' => 'import004',
                        'success' => 'false',
                        'info' => $attackPath,
                        'msg' => $msg
                    );
                    $this->outputLog($errInfo);
                    exit();
                } else {

                    // 正常終了
                    $this->updateLatLng();

                    $msg = 'Successful incorporation';
                    $errInfo = array(
                        'date' => date('Y-m-d H:i:s'),
                        'code' => 'import005',
                        'success' => 'true',
                        'info' => array(
                            'offset' => $page,
                            'count' => $i,
                            'total_hit_count' => $total,
                        ),
                        'msg' => $msg
                    );
                    $this->outputLog($errInfo);
                    exit();
                }
            }

            // JSON取得とデコード
            $json = file_get_contents($attackPath);
            $arr = json_decode($json, true);

            if (!isset($arr['rest'])) {

                // エラー終了
                $msg = 'JSON file is invalid.';
                $errInfo = array(
                    'date' => date('Y-m-d H:i:s'),
                    'code' => 'import006',
                    'success' => 'false',
                    'info' => array(
                        'offset' => $page,
                        'count' => $i,
                        'total_hit_count' => $total,
                    ),
                    'msg' => $msg
                );
                $this->outputLog($errInfo);
                exit();
            }

            foreach ($arr['rest'] as $data) {

                $save = array(
                    'gnavi_id' => $data['id'],
                    'update_date' => $data['update_date'],
                    'name' => $data['name']['name'],
                    'name_kana' => $data['name']['name_kana'],
                    'business_hour' => $data['business_hour'],
                    'holiday' => $data['holiday'],
                    'address' => $data['contacts']['address'],
                    'tel' => $data['contacts']['tel'],
                    'fax' => $data['contacts']['fax'] === array() ? null : $data['contacts']['fax'],
                    'pr_short' => $data['sales_points']['pr_short'],
                    'pr_long' => $data['sales_points']['pr_long'],
                    'access' => $data['access'],
                    'budget' => $data['budget'],
                    'category' => $data['categories']['category'],
                    'category_name_l_1' => isset($data['categories']['category_name_l'][0]) && !is_array($data['categories']['category_name_l'][0]) ? $data['categories']['category_name_l'][1] : null,
                    'category_name_l_2' => isset($data['categories']['category_name_l'][1]) && !is_array($data['categories']['category_name_l'][1]) ? $data['categories']['category_name_l'][1] : null,
                    'category_name_l_3' => isset($data['categories']['category_name_l'][2]) && !is_array($data['categories']['category_name_l'][2]) ? $data['categories']['category_name_l'][2] : null,
                    'category_name_l_4' => isset($data['categories']['category_name_l'][3]) && !is_array($data['categories']['category_name_l'][3]) ? $data['categories']['category_name_l'][3] : null,
                    'category_name_l_5' => isset($data['categories']['category_name_l'][4]) && !is_array($data['categories']['category_name_l'][4]) ? $data['categories']['category_name_l'][4] : null,
                    'category_name_s_1' => isset($data['categories']['category_name_s'][0]) && !is_array($data['categories']['category_name_s'][0]) ? $data['categories']['category_name_s'][1] : null,
                    'category_name_s_2' => isset($data['categories']['category_name_s'][1]) && !is_array($data['categories']['category_name_s'][1]) ? $data['categories']['category_name_s'][1] : null,
                    'category_name_s_3' => isset($data['categories']['category_name_s'][2]) && !is_array($data['categories']['category_name_s'][2]) ? $data['categories']['category_name_s'][2] : null,
                    'category_name_s_4' => isset($data['categories']['category_name_s'][3]) && !is_array($data['categories']['category_name_s'][3]) ? $data['categories']['category_name_s'][3] : null,
                    'category_name_s_5' => isset($data['categories']['category_name_s'][4]) && !is_array($data['categories']['category_name_s'][4]) ? $data['categories']['category_name_s'][4] : null,
                    'mobile_site' => $data['flags']['mobile_site'],
                    'mobile_coupon' => $data['flags']['mobile_coupon'],
                    'pc_coupon' => $data['flags']['pc_coupon'],
                    'latitude' => $data['location']['latitude'],
                    'longitude' => $data['location']['longitude'],
                    'latitude_wgs84' => $data['location']['latitude_wgs84'],
                    'longitude_wgs84' => $data['location']['longitude_wgs84'],
                    'district' => $data['location']['area']['district'],
                    'prefname' => $data['location']['area']['prefname'],
                    'areaname_s' => $data['location']['area']['areaname_s'],
                    'url' => $data['url'],
                    'url_mobile' => $data['url_mobile'],
                    'thumbnail' => $data['image_url']['thumbnail'],
                    'qrcode' => $data['image_url']['qrcode'],
                    'area_cd' => $areaCd,
                    'create_date' => $date,
                );

                foreach ($save as &$colVal) {
                    if (is_array($colVal)) {
                        if (count($colVal) === 0) {
                            $colVal = null;
                        } else if (isset($colVal['@attributes'])) {
                            $colVal = null;
                        } else {

                            $msg = 'Unknown error.';
                            $errInfo = array(
                                'date' => date('Y-m-d H:i:s'),
                                'code' => 'import007',
                                'success' => 'false',
                                'info' => array(
                                    'data' => $data,
                                    'save' => $save,
                                ),
                                'msg' => $msg
                            );
                            $this->outputLog($errInfo);
                            exit();
                        }
                    }
                }

                // todo: どうする？
                $save['searchword'] = implode(' ', array(
                    $save['name'],
                    $save['name_kana'],
                    $save['pr_short'],
                    $save['pr_long'],
                    $save['category'],
                    $save['category_name_l_1'],
                    $save['category_name_l_2'],
                    $save['category_name_l_3'],
                    $save['category_name_l_4'],
                    $save['category_name_l_5'],
                    $save['category_name_s_1'],
                    $save['category_name_s_2'],
                    $save['category_name_s_3'],
                    $save['category_name_s_4'],
                    $save['category_name_s_5'],
                ));
                $save['searchword'] = str_replace('　', ' ', $save['searchword']);
                $save['searchword'] = preg_replace('/\s+/', ' ', $save['searchword']);

                $queue->insert(array_values($save));

                ++$i;
            }
            $total = $arr['total_hit_count'];
        }

        $queue->flush();

    }

}
