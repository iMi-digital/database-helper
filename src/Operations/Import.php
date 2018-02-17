<?php

namespace IMI\DatabaseHelper\Operations;
use IMI\DatabaseHelper\Compressor\AbstractCompressor;
use IMI\DatabaseHelper\Mysql;

/**
 * Class Import
 *
 * @package IMI\DatabaseHelper\Operations
 */
class Import {
    protected $_isPipeViewerAvailable;

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

    public function getExecString($fileName, $compression = null)
    {
        $compressor = AbstractCompressor::create($compression);
        $compressor->setIsPipeViewerAvailable($this->isPipeViewerAvailable());

        $execString = $compressor->getDecompressingCommand(
            $this->_helper->getClientTool() . ' ' . $this->_helper->getMysqlClientToolConnectionString(),
            $fileName
        );

        return $execString;
    }

}
