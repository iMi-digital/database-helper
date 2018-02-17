<?php

namespace IMI\DatabaseHelper;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class DatabaseHelperTest
 *
 * @covers  \IMI\DatabaseHelper\Mysql
 */
class MysqlTest extends TestCase
{

    /**
     * @return Mysql
     */
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
        exec($mysqlTool . ' < ' . escapeshellarg(dirname(__FILE__) . '/data/employees.sql'));
        $this->cleanUpLog[] = $helper;
        return $helper;
    }

    protected function tearDown() {
        /*foreach($this->cleanUpLog as $helper) {
            $helper->dropDatabase();
        }*/

        parent::tearDown();
    }


    /**
     * @test
     */
    public function testHelperInstance()
    {
        $this->assertInstanceOf('\IMI\DatabaseHelper\Mysql', $this->getHelper());
    }

    /**
     * @test
     */
    public function getConnection()
    {
        $this->assertInstanceOf('\PDO', $this->getHelper()->getConnection());
    }

    /**
     * @test
     */
    public function dsn()
    {
        $this->assertStringStartsWith('mysql:', $this->getHelper()->dsn());
    }

    /**
     * @test
     */
    public function mysqlUserHasPrivilege()
    {
        $this->assertTrue($this->getHelper()->mysqlUserHasPrivilege('SELECT'));
    }

    /**
     * @test
     */
    public function getMysqlVariableValue()
    {
        $helper = $this->getHelper();

        // verify (complex) return value with existing global variable
        $actual = $helper->getMysqlVariableValue('version');

        $this->assertInternalType('array', $actual);
        $this->assertCount(1, $actual);
        $key = '@@version';
        $this->assertArrayHasKey($key, $actual);
        $this->assertInternalType('string', $actual[$key]);

        // quoted
        $actual = $helper->getMysqlVariableValue('`version`');
        $this->assertEquals('@@`version`', key($actual));

        // non-existent global variable
        try {
            $helper->getMysqlVariableValue('nonexistent');
            $this->fail('An expected exception has not been thrown');
        } catch (RuntimeException $e) {
            $this->assertEquals("Failed to query mysql variable 'nonexistent'", $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function getMysqlVariable()
    {
        $helper = $this->getHelper();

        // behaviour with existing global variable
        $actual = $helper->getMysqlVariable('version');
        $this->assertInternalType('string', $actual);

        // behavior with existent session variable (INTEGER)
        $helper->getConnection()->query('SET @existent = 14;');
        $actual = $helper->getMysqlVariable('existent', '@');
        $this->assertSame("14", $actual);

        // behavior with non-existent session variable
        $actual = $helper->getMysqlVariable('nonexistent', '@');
        $this->assertNull($actual);

        // behavior with non-existent global variable
        try {
            $helper->getMysqlVariable('nonexistent');
            $this->fail('An expected Exception has not been thrown');
        } catch (RuntimeException $e) {
            // test against the mysql error message
            $this->assertStringEndsWith(
                "SQLSTATE[HY000]: 1193: Unknown system variable 'nonexistent'",
                $e->getMessage()
            );
        }

        // invalid variable type
        try {
            $helper->getMysqlVariable('nonexistent', '@@@');
            $this->fail('An expected Exception has not been thrown');
        } catch (InvalidArgumentException $e) {
            // test against the mysql error message
            $this->assertEquals(
                'Invalid mysql variable type "@@@", must be "@@" (system) or "@" (session)',
                $e->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function getTables()
    {
        $helper = $this->getHelperWithTestDb();

        $tables = $helper->getTables();
        $this->assertInternalType('array', $tables);
        $this->assertNotEmpty($tables);
        $this->assertContains('employees', $tables);
    }

    /**
     * @test
     */
    public function resolveTables()
    {
        $this->markTestIncomplete();

        $tables = $this->getHelper()->resolveTables(array('catalog\_*'));
        $this->assertContains('catalog_product_entity', $tables);
        $this->assertNotContains('catalogrule', $tables);

        $definitions = array(
            'catalog_glob' => array('tables' => array('catalog\_*')),
            'directory'    => array('tables' => array('directory_country directory_country_format')),
        );

        $tables = $this->getHelper()->resolveTables(
            array('@catalog_glob', '@directory'),
            $definitions
        );
        $this->assertContains('catalog_product_entity', $tables);
        $this->assertContains('directory_country', $tables);
        $this->assertNotContains('catalogrule', $tables);
    }

    /**
     * @test
     */
    public function getMysqlClientToolConnectionString()
    {
        $connectionString = $this->getHelper()->getMysqlClientToolConnectionString();
        $this->assertInternalType('string', $connectionString);
    }

}
