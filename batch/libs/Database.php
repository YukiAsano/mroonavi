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

        // $B@\B3@_Dj3JG<(B
        $this->_config = $config;
    }

    // }}}
    // {{{ connect

    public function connect() {

        // $B%-%c%i%;%C%H<hF@(B
        $charset = $this->_config['charset'];

        // $B%[%9%HL><hF@(B
        $host = $this->_config['host'];

        // $B%f!<%6!<L><hF@(B
        $user = $this->_config['user'];

        // $B%Q%9%o!<%I<hF@(B
        $password = $this->_config['password'];

        // $B%G!<%?!<%Y!<%9L><hF@(B
        $database = $this->_config['database'];

        // $B%]!<%HHV9f<hF@(B
        $port = $this->_config['port'];

        // $B%=%1%C%H<hF@(B
        $socket = $this->_config['socket'];

        // DSN$B=i4|2=(B
        $dsn = '';

        $dsn .= strtolower('mysql') . ':';

        // $B%[%9%H@_Dj(B
        $dsn .= 'host=' . $host;

        // $B%G!<%?%Y!<%9L>(B
        $dsn .= ';dbname=' . $database;

        // $B%]!<%H(B
        if (!empty($port)) {
            $dsn .= ';port=' . $port;
        }

        // $B%=%1%C%H(B
        if (!empty($socket)) {
            $dsn .= ';unix_socket=' . $socket;
        }

        // $B%*%W%7%g%s@_Dj(B
        $options = array();

        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $charset;

        // PDO$B%*%V%8%'%/%H@8@.(B
        $this->_pdo = new PDO($dsn, $user, $password, $options);

        // $BNc30(B
        $this->_pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }

    // }}}
    // {{{ disconnect

    public function disconnect() {

        // $B%G!<%?%Y!<%9%O%s%I%iGK4~(B
        unset($this->_pdo);

        // NULL$B$r@_Dj(B
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

        // $B%/%(%j!<<B9T(B
        return $this->_pdo->exec($sql);

    }

    // }}}
    // {{{ execute

    public function execute($sql) {

        // $B%/%(%j!<<B9T(B
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

        // $B%/%(%j!<<B9T(B
        $stmt = $this->_pdo->query($sql);

        // $B%G!<%?<hF@(B
        $result = $stmt->fetch(
            $fetch_style,
            $cursor_orientation,
            $cursor_offset
        );

        $stmt->closeCursor();

        // PDOStatement$BGK4~(B
        unset($stmt);

        return $result;
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

        // $B%9%F!<%H%a%s%H%*%V%8%'%/%H<hF@(B
        $stmt = $this->_pdo->prepare($sql);

        // $B%G!<%?%P%$%s%I(B
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

        // $B%/%(%j!<<B9T(B
        $stmt->execute();

        // $B%G!<%?<hF@(B
        $result = $stmt->fetchAll($fetch_style);

        $stmt->closeCursor();

        // PDOStatement$BGK4~(B
        unset($stmt);

        return $result;
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

        $result = $stmt->execute($values);

        $stmt->closeCursor();

        // PDOStatement$BGK4~(B
        unset($stmt);

        return $result;
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

                $sql .= "  " . $key . " = ?";
                $i++;
            }

            array_push($values, $val);

        } else if (is_string($where)) {

            $sql .= "\nwhere\n  " . $where;

        }

        $stmt = $this->_pdo->prepare($sql);

        $result = $stmt->execute($values);

        $stmt->closeCursor();

        // PDOStatement$BGK4~(B
        unset($stmt);

        return $result;
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

        $result = $stmt->execute($values);

        $stmt->closeCursor();

        // PDOStatement$BGK4~(B
        unset($stmt);

        return $result;
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
