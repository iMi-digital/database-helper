<?php

namespace IMI\DatabaseHelper;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function getHelper()
    {
        $hostNameEnv = getenv('PHPUNIT_DB_HOSTNAME');
        $dbNameEnv = getenv('PHPUNIT_DB_NAME');

        $dbSettings = [
            'host' => $hostNameEnv ? $hostNameEnv : 'localhost',
            'prefix' => '',
            'username' => getenv('PHPUNIT_DB_USERNAME'),
            'password' => getenv('PHPUNIT_DB_PASSWORD'),
            'dbname' => $dbNameEnv ? $dbNameEnv : 'phpunit_' . rand(1, PHP_INT_MAX),
        ];
        return new Mysql($dbSettings);
    }

    /**
     * Memorizes databases (helpers) to clean up after testing
     * @var Mysql[]
     */
    protected $cleanUpLog = [];

    protected function getHelperWithTestDb()
    {
        $helper = $this->getHelper();
        $mysqlTool = 'mysql ' . $helper->getMysqlClientToolConnectionString();
        $helper->createDatabase();
        $helper->forceReconnect();
        exec($mysqlTool . ' < ' . escapeshellarg(dirname(__FILE__) . '/data/employees.sql'));
        exec($mysqlTool . ' < ' . escapeshellarg(dirname(__FILE__) . '/data/employees.sql'));
        $this->cleanUpLog[] = $helper;
        return $helper;
    }

    protected function tearDown() {
        // cleanup currently disable, see
        // @link https://stackoverflow.com/questions/48844672/creating-database-using-mysql-and-querying-information-schema-leads-to-empty-res
        foreach($this->cleanUpLog as $helper) {
            $helper->dropDatabase();
        }

        parent::tearDown();
    }

}