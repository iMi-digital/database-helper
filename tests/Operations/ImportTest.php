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
        $this->assertStringStartsWith("pv foo.sql | mysql -h",
            $this->getInstance()->getExecString('foo.sql')
        );

        $this->assertStringStartsWith("pv -cN gzip 'foo.sql' | gzip -d | pv -cN mysql | mysql",
            $this->getInstance()->getExecString('foo.sql', 'gzip')
        );
    }

}
