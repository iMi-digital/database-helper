<?php

namespace IMI\DatabaseHelper;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function getMysqlDummyHelper()
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

}