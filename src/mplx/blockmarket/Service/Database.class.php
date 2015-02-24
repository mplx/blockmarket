<?php
/**
 * Blockmarket
 * collect and visualize blockmarket data
 *
 * @package     blockmarket
 **/

namespace mplx\blockmarket\Service;

/**
* Database service
*/
class Database
{
    /**
    * PDO Object
    *
    * @var \PDO
    */
    protected $pdo;

    /**
    * Database host (FQDN)
    *
    * @var string $db_host
    */
    protected $db_host;

    /**
    * Database name
    *
    * @var string $db_name
    */
    protected $db_name;

    /**
    * Database username
    *
    * @var string $db_user
    */
    protected $db_user;

    /**
    * Database password
    *
    * @var string $db_pass
    */
    protected $db_pass;

    /**
    * Database TCP port
    *
    * @var int $db_port
    */
    protected $db_port;

    /**
    * Connection status
    *
    * @var bool $status
    */
    protected $status = false;

    /**
    * PDOStatement of last query
    *
    * @var \PDOStatement $last
    */
    protected $last;

    /**
    * PDOException of last query
    *
    * @var \PDOException $error
    */
    protected $error;

    /**
    * Constructor
    *
    * @param string $host
    * @param string $user
    * @param string $pass
    * @param string $db
    * @param int $port
    */
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

    /**
    * Connect to database
    *
    * @return false|\PDO
    */
    protected function connect()
    {
        try {
            $this->pdo = new \PDO(
                'mysql:host=' . $this->db_host . ';dbname=' . $this->db_name . ';charset=utf8',
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

    /**
    * Return PDO object
    *
    * @return \PDO
    */
    public function pdo()
    {
        return $this->pdo;
    }

    /**
    * Execute query, optionally fetch result
    *
    * @param string $query
    * @param bool $fetch
    * @return \PDOStatement
    */
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

    /**
    * Execute SQL statement
    *
    * @param string $query
    * @return \PDOStatement
    */
    public function exec($query)
    {
        try {
            $this->last = $this->pdo()->exec($query);
        } catch (\PDOException $e) {
            $this->error($e);
        }
        return $this->last;
    }

    /**
    * Return number of rows from last query
    *
    * @return int
    */
    public function getNumRows()
    {
        return $this->last->rowCount();
    }

    /**
    * Quote a string
    *
    * @param string $value
    * @return string
    */
    public function quote($value)
    {
        return $this->pdo()->quote($value);
    }

    /**
    * Convert null and (string)null to (string)null or quote string
    *
    * @param string $value
    * @return string
    */
    public function quoteOrNull($value)
    {
        if (is_null($value) || $value == 'null') {
            return 'null';
        } else {
            return $this->quote($value);
        }
    }

    /**
    * Convert null to (string)null or quote string
    *
    * @param string $value
    * @return string
    */
    public function nullToNull($value)
    {
        if (is_null($value)) {
            return 'null';
        } else {
            return $this->quote($value);
        }
    }

    /**
    * Check if table exists in database
    *
    * @param string $table
    * @return \PDOStatement
    */
    public function tableExists($table)
    {
        try {
            return $this->query("SHOW TABLES LIKE " . $this->quote($table)) > 0;
        } catch (\PDOException $e) {
            $this->error($e);
        }
        return $this->last;
    }

    /**
    * Get connection status
    *
    * @return bool
    */
    public function getStatus()
    {
        return $this->status;
    }

    /**
    * Get message from PDOException and throw Exception
    *
    * @param \PDOException $e
    * @throsw \Exception
    */
    protected function error(\PDOException $e)
    {
        throw new \Exception('SQL error: ' . $e->getMessage());
    }

    /**
    * Get configuration property
    *
    * @param string $key
    * @return string|int
    */
    public function getConf($key)
    {
        $result = $this->query("SELECT `value` FROM `config` WHERE `key` = ". $this->quote($key));
        return $result[0]['value'];
    }

    /**
    * Set configuration property
    *
    * @param string $key
    * @param string|int $value
    * @return string|int
    */
    public function setConf($key, $value)
    {
        $query = "UPDATE `config` SET `value` = " . $this->quote($value) . " WHERE `key` = ". $this->quote($key);
        $result = $this->query($query);
        return $result[0]['value'];
    }
}
