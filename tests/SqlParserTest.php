<?php

namespace IMI\DatabaseHelper;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class SqlParserTest
 *
 * @covers  \IMI\DatabaseHelper\SqlParser
 */
class SqlParserTest extends TestCase
{
    /**
     * @test
     */
    public function optimize()
    {
        $processedFile = SqlParser::optimize(dirname(__FILE__). '/data/multi_inserts.sql');

        $result = file_get_contents($processedFile);

        $expected = file_get_contents(dirname(__FILE__) . '/data/one_insert.sql');

        $this->assertEquals($expected, $result);
    }
}
