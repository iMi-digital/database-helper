<?php

namespace IMI\DatabaseHelper\Operations;
use IMI\DatabaseHelper\Compressor\AbstractCompressor;
use IMI\DatabaseHelper\Mysql;
use IMI\DatabaseHelper\Util\Execs;

/**
 * Class Import
 *
 * @package IMI\DatabaseHelper\Operations
 */
class Import {
    protected $_isPipeViewerAvailable;
    protected $compression;

    /**
     * Import constructor.
     *
     * @param Mysql $helper TODO: Define and Accept and Interface
     */
    public function __construct(Mysql $helper) {
        $this->_helper = $helper;
    }

    public function isPipeViewerAvailable()
    {
        return $this->_isPipeViewerAvailable;
    }

    public function setIsPipeViewerAvailable($available)
    {
        $this->_isPipeViewerAvailable = $available;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function setCompression($compression)
    {
        $this->compression = $compression;
    }

    public function createExec()
    {
        $execs = new Execs('');

        $compressor = AbstractCompressor::create($this->compression);
        $compressor->setIsPipeViewerAvailable($this->isPipeViewerAvailable());

        $execString = $compressor->getDecompressingCommand(
            $this->_helper->getClientTool() . ' ' . $this->_helper->getMysqlClientToolConnectionString(),
            $this->filename
        );

        $execs->add($execString);

        return $execs;
    }

}
