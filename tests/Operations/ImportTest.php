<?php

namespace IMI\DatabaseHelper\Operations;

use IMI\DatabaseHelper\TestCase;

class ImportTest extends TestCase {

    public function getInstance() {
        $instance = new Import($this->getHelper());
        $instance->setIsPipeViewerAvailable(true);
        return $instance;
    }

    public function testInstance()
    {
        $this->assertInstanceOf('\IMI\DatabaseHelper\Operations\Import', $this->getInstance());
    }

    /**
     * @test
     */
    public function getExecString()
    {
        $instance = $this->getInstance();
        $instance->setFilename('foo.sql');
        $this->assertStringStartsWith("pv foo.sql | mysql -h",
            implode(PHP_EOL, $instance->createExec()->getCommands())
        );

        $instance = $this->getInstance();
        $instance->setFilename('foo.sql');
        $instance->setCompression('gzip');
        $this->assertStringStartsWith("pv -cN gzip 'foo.sql' | gzip -d | pv -cN mysql | mysql",
            implode(PHP_EOL, $instance->createExec()->getCommands())
        );
    }

}
