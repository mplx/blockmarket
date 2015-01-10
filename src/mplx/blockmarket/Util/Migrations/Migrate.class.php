<?php

namespace mplx\blockmarket\Util\Migrations;

use mplx\blockmarket\Service\Database;

class Migrate
{
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function run()
    {
        if (! $this->db->getStatus()) {
            trigger_error('Database not connected?');
        }
        if (! $this->dbExists()) {
            $result = $this->createSchema();
        }

        $schema = $this->getSchema();

        if ($schema < 2) {
            $result = $this->upgradeDatabase002();
            $this->setSchema(2);
        }

        /*
        if ($schema<3) {
            $this->setSchema(3);
        }

        if ($schema<4) {
            $this->setSchema(4);
        }
        */

        return true;
    }

    private function dbExists()
    {
        return $this->db->tableExists('config');
    }

    private function createSchema()
    {
        $queries=array();
        // @codingStandardsIgnoreStart
        $queries[] = "CREATE TABLE `config` (`key` varchar(255) NOT NULL, `value` varchar(255) NOT NULL, PRIMARY KEY (`key`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $queries[] = "ALTER TABLE `config` ADD COLUMN `lastmodified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `value`;";
        $queries[] = "INSERT INTO `blockmarketdb`.`config` (`key`, `value`) VALUES ('schema', '1');";
        $queries[] = "CREATE TABLE `stocks` (`id_stock` SMALLINT UNSIGNED NOT NULL, `title` VARCHAR(50) NOT NULL, `lastmodified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id_stock`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $queries[] = "CREATE TABLE `prices` (`stock_id` SMALLINT UNSIGNED NOT NULL, `ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `marketvalue` FLOAT(11,5) NOT NULL DEFAULT '0', PRIMARY KEY (`stock_id`, `ts`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        // @codingStandardsIgnoreEnd
        foreach ($queries as $q) {
            $result = $this->db->query($q, false);
        }
        return true;
    }

    private function getSchema()
    {
        return $this->db->getConf('schema');
    }

    private function setSchema($value)
    {
        return $this->db->setConf('schema', $value);
    }

    private function upgradeDatabase002()
    {
        $queries=array();
        // @codingStandardsIgnoreStart
        $queries[] = "TRUNCATE TABLE `stocks`";
        $queries[] = "ALTER TABLE `stocks` ADD COLUMN `title_original` VARCHAR(50) NOT NULL AFTER `title`";
        $queries[] = "ALTER TABLE `stocks` ADD COLUMN `title_wiki` VARCHAR(50) NULL AFTER `title_original`;";
        $queries[] = "ALTER TABLE `stocks` ADD COLUMN `icon_path` VARCHAR(50) NULL DEFAULT NULL AFTER `title_wiki`;";
        $queries[] = "ALTER TABLE `stocks` ADD COLUMN `enabled` BIT(1) NOT NULL DEFAULT 1 AFTER `icon_path`;";
        // @codingStandardsIgnoreEnd
        foreach ($queries as $q) {
            //echo "*** " . $q . PHP_EOL;
            $result = $this->db->query($q, false);
        }
        return true;
    }
}
