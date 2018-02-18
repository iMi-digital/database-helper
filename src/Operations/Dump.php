<?php

namespace IMI\DatabaseHelper\Operations;

use IMI\DatabaseHelper\AbstractHelper;
use IMI\DatabaseHelper\Compressor\Compressor;
use IMI\DatabaseHelper\Mysql;
use IMI\DatabaseHelper\Util\Execs;
use IMI\DatabaseHelper\Util\VerifyOrDie;
use InvalidArgumentException;

class Dump extends AbstractHelper {


    /**
     * @var Mysql
     */
    protected $helper;


    protected $isStdout = false;
    protected $filename = 'dump.sql';
    protected $isForce = false;
    protected $addTime = true;
    protected $isOnlyCommand = false;
    protected $exclude = '';
    protected $strip = '';
    protected $compression;
    protected $isNoSingleTransaction;
    protected $isHumanReadable;
    protected $isRoutines;

    public function __construct( Mysql $helper, $output = null, $asker = null ) {
        parent::__construct( $output, $asker );
        $this->helper = $helper;
    }


    /**
     * @return Execs
     */
    public function createExec()
    {
        $execs = new Execs($this->helper->getDumpTool());
        $execs->setCompression($this->compression);
        $execs->setFileName($this->getFileName($execs->getCompressor()));

        if (!$this->isNoSingleTransaction) {
            $execs->addOptions('--single-transaction --quick');
        }

        if ($this->isHumanReadable) {
            $execs->addOptions('--complete-insert --skip-extended-insert ');
        }

        if ($this->isRoutines) {
            $execs->addOptions('--routines ');
        }

        $mysqlClientToolConnectionString = $this->helper->getMysqlClientToolConnectionString();

        $stripTables = $this->stripTables();
        if ($stripTables) {
            // dump structure for strip-tables
            $execs->add(
                '--no-data ' . $mysqlClientToolConnectionString .
                ' ' . implode(' ', $stripTables) . $this->postDumpPipeCommands()
            );
        }

        $excludeTables = $this->excludeTables();

        // dump data for all other tables
        $ignore = '';
        foreach (array_merge($excludeTables, $stripTables) as $ignoreTable) {
            $ignore .= '--ignore-table=' . $this->helper->getDbName() . '.' . $ignoreTable . ' ';
        }

        $execs->add($ignore . $mysqlClientToolConnectionString . $this->postDumpPipeCommands());

        return $execs;
    }

    /**
     * @return array
     */
    private function stripTables()
    {
        if (!$this->strip) {
            return array();
        }

        $stripTables = $this->resolveDatabaseTables($this->strip);

        if (!$this->isOnlyCommand) {
            $this->writeln(
                sprintf('<comment>No-data export for: <info>%s</info></comment>', implode(' ', $stripTables))
            );
        }

        return $stripTables;
    }

    /**
     * @return array
     */
    private function excludeTables()
    {
        if (!$this->exclude) {
            return array();
        }

        $excludeTables = $this->resolveDatabaseTables($this->exclude);

        if (!$this->isOnlyCommand) {
            $this->writeln(
                sprintf('<comment>Excluded: <info>%s</info></comment>', implode(' ', $excludeTables))
            );
        }

        return $excludeTables;
    }

    /**
     * @param string $list space separated list of tables
     * @return array
     */
    private function resolveDatabaseTables($list)
    {
        return $this->helper->resolveTables(explode(' ', $list));
    }

    /**
     * Commands which filter mysql data. Piped to mysqldump command
     *
     * @return string
     */
    protected function postDumpPipeCommands()
    {
        return ' | LANG=C LC_CTYPE=C LC_ALL=C sed -e ' . escapeshellarg('s/DEFINER[ ]*=[ ]*[^*]*\*/\*/');
    }


    /**
     * @param Compressor $compressor
     *
     * @return string
     */
    protected function getFileName(Compressor $compressor)
    {
        $nameExtension = '.sql';

        $optionAddTime = $this->addTime;
        list($namePrefix, $nameSuffix) = $this->getFileNamePrefixSuffix($optionAddTime);

        if (
            (
                ($fileName = $this->filename) === null
                || ($isDir = is_dir($fileName))
            )
            && !$this->isStdout
        ) {
            $defaultName = VerifyOrDie::filename(
                $namePrefix . $this->helper->getDbName() . $nameSuffix . $nameExtension
            );
            if (isset($isDir) && $isDir) {
                $defaultName = rtrim($fileName, '/') . '/' . $defaultName;
            }
            if (!$this->isForce) {
                $fileName = $this->ask(
                    '<question>Filename for SQL dump:</question> [<comment>' . $defaultName . '</comment>]',
                    $defaultName
                );
            } else {
                $fileName = $defaultName;
            }
        } else {
            if ($optionAddTime) {
                $pathParts = pathinfo($fileName);
                $fileName = ($pathParts['dirname'] == '.' ? '' : $pathParts['dirname'] . '/') .
                            $namePrefix . $pathParts['filename'] . $nameSuffix . '.' . $pathParts['extension'];
            }
        }

        $fileName = $compressor->getFileName($fileName);

        return $fileName;
    }

    private function getFileNamePrefixSuffix($optionAddTime = null)
    {
        $namePrefix = '';
        $nameSuffix = '';
        if ($optionAddTime === null) {
            return array($namePrefix, $nameSuffix);
        }

        $timeStamp = date('Y-m-d_His');

        if (in_array($optionAddTime, array('suffix', true), true)) {
            $nameSuffix = '_' . $timeStamp;
        } elseif ($optionAddTime === 'prefix') {
            $namePrefix = $timeStamp . '_';
        } elseif ($optionAddTime !== 'no') {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid --add-time value %s, possible values are none (for) "suffix", "prefix" or "no"',
                    var_export($optionAddTime, true)
                )
            );
        }

        return array($namePrefix, $nameSuffix);
    }

    /**
     * @param bool $isStdout
     *
     * @return Dump
     */
    public function setIsStdout( $isStdout ) {
        $this->isStdout = $isStdout;

        return $this;
    }

    /**
     * @param string $filename
     *
     * @return Dump
     */
    public function setFilename( $filename ) {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @param bool $isForce
     *
     * @return Dump
     */
    public function setIsForce( $isForce ) {
        $this->isForce = $isForce;

        return $this;
    }

    /**
     * @param bool $addTime
     *
     * @return Dump
     */
    public function setAddTime( $addTime ) {
        $this->addTime = $addTime;

        return $this;
    }

    /**
     * @param bool $isOnlyCommand
     *
     * @return Dump
     */
    public function setIsOnlyCommand( $isOnlyCommand ) {
        $this->isOnlyCommand = $isOnlyCommand;

        return $this;
    }

    /**
     * @param string $exclude
     *
     * @return Dump
     */
    public function setExclude( $exclude ) {
        $this->exclude = $exclude;

        return $this;
    }

    /**
     * @param string $strip
     *
     * @return Dump
     */
    public function setStrip( $strip ) {
        $this->strip = $strip;

        return $this;
    }

    /**
     * @param string $compression
     *
     * @return Dump
     */
    public function setCompression( $compression ) {
        $this->compression = $compression;

        return $this;
    }

    /**
     * @param bool $isNoSingleTransaction
     *
     * @return Dump
     */
    public function setIsNoSingleTransaction( $isNoSingleTransaction ) {
        $this->isNoSingleTransaction = $isNoSingleTransaction;

        return $this;
    }

    /**
     * @param bool $isHumanReadable
     *
     * @return Dump
     */
    public function setIsHumanReadable( $isHumanReadable ) {
        $this->isHumanReadable = $isHumanReadable;

        return $this;
    }

    /**
     * @param bool $isRoutines
     *
     * @return Dump
     */
    public function setIsRoutines( $isRoutines ) {
        $this->isRoutines = $isRoutines;

        return $this;
    }


}
