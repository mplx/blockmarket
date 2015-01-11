<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Service;

class Database
{
    protected $pdo;

    protected $db_host;
    protected $db_name;
    protected $db_user;
    protected $db_pass;
    protected $db_port;

    protected $status = false;
    protected $last;
    protected $error;

    public function __construct($host = null, $user = null, $pass = null, $db = null, $port = 3306)
    {
        if ($host != null && $user != null && $pass != null && $db != null && $port != null) {
            $this->db_host = $host;
            $this->db_user = $user;
            $this->db_pass = $pass;
            $this->db_name = $db;
            $this->db_port = $port;
            $this->connect();
        } elseif (
                defined('BM_DB_HOST') &&
                defined('BM_DB_USER') &&
                defined('BM_DB_PASS') &&
                defined('BM_DB_NAME') &&
                defined('BM_DB_PORT')
            ) {
            $this->db_host = BM_DB_HOST;
            $this->db_user = BM_DB_USER;
            $this->db_pass = BM_DB_PASS;
            $this->db_name = BM_DB_NAME;
            $this->db_port = BM_DB_PORT;
            $this->connect();
        } elseif (
                isset($_SERVER[BM_DB_ENV_HOST_NAME]) &&
                isset($_SERVER[BM_DB_ENV_USER_NAME]) &&
                isset($_SERVER[BM_DB_ENV_PASS_NAME]) &&
                isset($_SERVER[BM_DB_ENV_DBNAME_NAME]) &&
                isset($_SERVER[BM_DB_ENV_PORT_NAME])
            ) {
            $this->db_host = $_SERVER[BM_DB_ENV_HOST_NAME];
            $this->db_user = $_SERVER[BM_DB_ENV_USER_NAME];
            $this->db_pass = $_SERVER[BM_DB_ENV_PASS_NAME];
            $this->db_name = $_SERVER[BM_DB_ENV_DBNAME_NAME];
            $this->db_port = $_SERVER[BM_DB_ENV_PORT_NAME];
            $this->connect();
        } elseif (
                getenv(BM_DB_ENV_HOST_NAME) &&
                getenv(BM_DB_ENV_USER_NAME) &&
                getenv(BM_DB_ENV_PASS_NAME) &&
                getenv(BM_DB_ENV_DBNAME_NAME) &&
                getenv(BM_DB_ENV_PORT_NAME)
            ) {
            $this->db_host = getenv(BM_DB_ENV_HOST_NAME);
            $this->db_user = getenv(BM_DB_ENV_USER_NAME);
            $this->db_pass = getenv(BM_DB_ENV_PASS_NAME);
            $this->db_name = getenv(BM_DB_ENV_DBNAME_NAME);
            $this->db_port = getenv(BM_DB_ENV_PORT_NAME);
            $this->connect();
        } else {
            return false;
        }
    }

    protected function connect()
    {
        try {
            $this->pdo = new \PDO(
                'mysql:host='.$this->db_host.';dbname='.$this->db_name.';charset=utf8',
                $this->db_user,
                $this->db_pass
            );
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->status = true;
            return $this->pdo;
        } catch (\PDOException $e) {
            $this->status = false;
            throw new \Exception('MySQL connection failed: '.$e->getMessage());
            return false;
        }
    }

    public function pdo()
    {
        return $this->pdo;
    }

    public function query($query, $fetch = true)
    {
        try {
            $this->last = $this->pdo()->query($query);
        } catch (\PDOException $e) {
            $this->error($e);
        }
        if ($fetch && $this->last != false) {
            $cmd = strtolower(substr($query, 0, 6));
            switch($cmd) {
                case 'insert':
                    $result = $this->getLastInsertedId();
                    break;
                case 'update':
                case 'delete':
                case 'show t':
                case 'trunca':
                    $result = $this->getNumRows();
                    break;
                default:
                    $result = $this->last->fetchAll(\PDO::FETCH_ASSOC);
                    break;
            }
        } else {
            $result = $this->last;
        }
        return $result;
    }

    public function exec($query)
    {
        try {
            $this->last = $this->pdo()->exec($query);
        } catch (\PDOException $e) {
            $this->error($e);
        }
        return $this->last;
    }

    public function getNumRows()
    {
        return $this->last->rowCount();
    }

    public function quote($value)
    {
        return $this->pdo()->quote($value);
    }

    public function quoteOrNull($value)
    {
        if (is_null($value) || $value == 'null') {
            return 'null';
        } else {
            return $this->quote($value);
        }
    }

    public function tableExists($table)
    {
        try {
            return $this->query("SHOW TABLES LIKE " . $this->quote($table)) > 0;
        } catch (\PDOException $e) {
            $this->error($e);
        }
        return $this->last;
    }

    public function getStatus()
    {
        return $this->status;
    }

    protected function error(\PDOException $e)
    {
        throw new \Exception('SQL error: ' . $e->getMessage());
    }

    public function getConf($key)
    {
        $result = $this->query("SELECT `value` FROM `config` WHERE `key` = ". $this->quote($key));
        return $result[0]['value'];
    }

    public function setConf($key, $value)
    {
        $query = "UPDATE `config` SET `value` = " . $this->quote($value) . " WHERE `key` = ". $this->quote($key);
        $result = $this->query($query);
        return $result[0]['value'];
    }

    public function getStocks($id = null)
    {
        if ($id) {
            $query = sprintf(
                "SELECT id_stock, title, title_wiki, icon_path FROM stocks WHERE id_stock = %d",
                $id
            );
        } else {
            $query = "SELECT id_stock, title, icon_path FROM stocks WHERE enabled=1 ORDER BY title ASC";
        }

        return $this->query($query);
    }
}
