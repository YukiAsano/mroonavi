<?php

class Database {

    // {{{ _instance

    protected static $_instance;

    // }}}
    // {{{ _pdo

    private $_pdo;

    // }}}
    // {{{ _config

    private $_config;

    // }}}
    // {{{ getInstance

    public static function getInstance($config = null)
    {
        if (!self::$_instance) {
            self::$_instance = new self($config);
        }
        return self::$_instance;
    }

    // }}}
    // {{{ __construct

    public function __construct($config) {

        // 接続設定格納
        $this->_config = $config;
    }

    // }}}
    // {{{ connect

    public function connect() {

        // キャラセット取得
        $charset = $this->_config['charset'];

        // ホスト名取得
        $host = $this->_config['host'];

        // ユーザー名取得
        $user = $this->_config['user'];

        // パスワード取得
        $password = $this->_config['password'];

        // データーベース名取得
        $database = $this->_config['database'];

        // ポート番号取得
        $port = $this->_config['port'];

        // ソケット取得
        $socket = $this->_config['socket'];


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

        // PDOオブジェクト生成
        $this->_pdo = new PDO($dsn, $user, $password, $options);

        // 例外
        $this->_pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }

    // }}}
    // {{{ disconnect

    public function disconnect() {

        // データベースハンドラ破棄
        unset($this->_pdo);

        // NULLを設定
        $this->_pdo = null;

    }

    // }}}
    // {{{ getPDO

    public function getPDO() {
        return $this->_pdo;
    }

    // }}}
    // {{{ exec

    public function exec($sql) {

        // クエリー実行
        return $this->_pdo->exec($sql);

    }

    // }}}
    // {{{ execute

    public function execute($sql) {

        // クエリー実行
        return $this->_pdo->prepare($sql)->execute();

    }

    // }}}
    // {{{ query

    public function query(
        $sql,
        $fetch_style = PDO::FETCH_ASSOC,
        $cursor_orientation = PDO::FETCH_ORI_NEXT,
        $cursor_offset = 0
    ) {

        // クエリー実行
        $stmt = $this->_pdo->query($sql);

        // データ取得
        return $stmt->fetch(
            $fetch_style,
            $cursor_orientation,
            $cursor_offset
        );
    }

    // }}}
    // {{{ getRow

    public function getRow($sql, $data = array(), $fetch_style = PDO::FETCH_ASSOC) {

        $result = $this->getRows($sql, $data, $fetch_style);

        if (count($result) !== 0 && is_array($result)) {
            return $result[0];
        }

        return $result;
    }

    // }}}
    // {{{ getRows

    public function getRows($sql, $data = array(), $fetch_style = PDO::FETCH_ASSOC) {

        // ステートメントオブジェクト取得
        $stmt = $this->_pdo->prepare($sql);

        // データバインド
        foreach($data as $key => $val) {

            $paramType = PDO::PARAM_STR;

            if (is_bool($val)) {
                $paramType = PDO::PARAM_BOOL;
            } else if (is_int($val)) {
                $paramType = PDO::PARAM_INT;
            } else if (is_null($val)) {
                $paramType = PDO::PARAM_NULL;
            }

            $stmt->bindValue(':' . $key, $val, PDO::PARAM_INT);
        }

        // クエリー実行
        $stmt->execute();

        // データ取得
        return $stmt->fetchAll($fetch_style);
    }

    // }}}
    // {{{ insert

    public function insert($table, $data) {

        $keys   = array_keys($data);
        $key_len = sizeof($keys);

        $qvalues = array();
        for ($i = 0; $i < $key_len; $i++) {
            array_push($qvalues , '?');
        }

        $values = array_values($data);

        $sql  = '';
        $sql .= "INSERT INTO " . $table . " (\n";
        $sql .= "  " . implode(",", $keys) . "\n";
        $sql .= ") VALUES (\n";
        $sql .= "  " . implode(",", $qvalues) . "\n";
        $sql .= ")\n";

        $stmt = $this->_pdo->prepare($sql);

        return $stmt->execute($values);
    }

    // }}}
    // {{{ remove

    public function remove($table, $where) {

        $sql  = '';
        $sql .= "delete from " . $table . " \n";

        $values = array();

        if (is_array($where)) {

            $sql .= "\nwhere\n";

            $i = 0;

            foreach($where as $key => $val) {

                if ($i > 0) {
                    $sql .= "\nand\n";
                }

                $sql .= "  " . $key . "?";
                $i++;
            }

            array_push($values, $val);

        } else if (is_string($where)) {

            $sql .= "\nwhere\n  " . $where;

        }

        $stmt = $this->_pdo->prepare($sql);

        return $stmt->execute($values);
    }

    // }}}
    // {{{ update

    public function update($table, $data, $where) {

        $sql  = '';
        $sql .= "UPDATE\n" ;
        $sql .= "  " . $table . "\n";
        $sql .= "SET\n";

        $values = array();

        $i = 0;
        foreach($data as $key => $val) {

            if ($i > 0) {
                $sql .= ",\n";
            }

            $sql .= "  " . $key . " = ?";

            array_push($values, $val);
            $i++;
        }

        if (is_array($where)) {

            $sql .= "\nWHERE\n";

            $i = 0;

            foreach($where as $key => $val) {

                if ($i > 0) {
                    $sql .= "\nAND\n";
                }

                $sql .= "  " . $key . "?";

                array_push($values, $val);
                $i++;
            }

        } else if (is_string($where)) {

            $sql .= "\nWHERE\n  " . $where;

        }

        $stmt = $this->_pdo->prepare($sql);

        return $stmt->execute($values);
    }

    // }}}
    // {{{ lockTable

    public function lockTable($table, $mode = 'WRITE') {

        $sql = '';
        $sql = "LOCK TABLES " . $table . " " . $mode;

        return $this->_pdo->exec($sql);
    }

    // }}
    // {{{ unlockTable

    public function unlockTable($table) {

        $sql = '';
        $sql = "UNLOCK TABLES";

        return $this->_pdo->exec($sql);
    }

    // }}}
    // {{{ beginTransaction

    public function beginTransaction() {

        return $this->_pdo->beginTransaction();

    }

    // }}
    // {{{ commit

    public function commit() {

        return $this->_pdo->commit();

    }

    // }}
    // {{{ rollBack

    public function rollBack() {

        return $this->_pdo->rollBack();

    }

    // }}

}

// }}}
