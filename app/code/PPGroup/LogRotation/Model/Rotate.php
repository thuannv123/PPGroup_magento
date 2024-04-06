<?php

namespace PPGroup\LogRotation\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Archive\Zip;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\Filesystem\Io\File;
use PPGroup\LogRotation\Logger\Logger;

/**
 * Rotate class contain logic to archive, delete, compress the logs
 * phpcs:disable Magento2.Files.LineLength.MaxExceeded
 * phpcs:disable Magento2.Functions.DiscouragedFunction
 */
class Rotate
{
    const ARCHIVE_DATE_FORMAT = 'Ymd';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var false|string
     */
    protected $archiveFolder;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Zip
     */
    protected $zip;

    /**
     * @var string
     */
    protected $logDirectory;

    /**
     * @var string;
     */
    protected $rotateDirectory;

    /**
     * @var Logger
     */
    protected $logger;


    /**
     * @var WriteInterface
     */
    protected $varDirectory;

    /**
     * @var Filesystem
     */
    protected $filesystem;


    public function __construct(
        Config $config,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        File $file,
        Zip $zip,
        Logger $logger
    ) {
        $this->config = $config;
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->zip = $zip;
        $this->logger = $logger;

        $this->archiveFolder = date(self::ARCHIVE_DATE_FORMAT);

        try {
            $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $this->logDirectory = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/' . 'log';
            $this->rotateDirectory = $this->logDirectory . '/' . $this->archiveFolder;
        } catch (FileSystemException $e) {
            $this->logger->error('Can\'t read the log folder: ' . $e->getMessage());
        }
    }


    /**
     * Make archive directory
     */
    public function makeDir()
    {
        try {
            $this->file->checkAndCreateFolder($this->rotateDirectory . '/');
        } catch (LocalizedException $e) {
            $this->logger->error('Can\'t create folder: ' . $e->getMessage());
        }
    }


    protected function _zipAndDeleteFile($sourceFile = null): bool
    {
        if (!$sourceFile) {
            return false;
        }

        $sourceFile = basename($sourceFile);

        $copy = $this->file->cp(
            $this->logDirectory . '/' . $sourceFile,
            $this->rotateDirectory . '/' . $sourceFile
        );

        if (!$copy) {
            $this->logger->error('Something went wrong while copying the rotation log.');
            return false;
        }

        if ($this->config->isZipEnabled()) {
            $archive = $this->zip->pack(
                $this->rotateDirectory . '/' . $sourceFile,
                $this->rotateDirectory . '/' . $sourceFile . '.zip'
            );

            if (!$archive) {
                $this->logger->error('Something went wrong while zipping the rotation log.');
                return false;
            }

            $this->file->rm($this->rotateDirectory . '/' . $sourceFile);
        }

        $this->file->rm($this->logDirectory . '/' . $sourceFile);

        return true;
    }

    /**
     * @return $this
     */
    protected function _removeDeprecatedLogFolder(): Rotate
    {

        $keepPeriodDays = [];

        $logRotationLifetime = $this->config->getLogRotationLifeTime();

        $periodDays = new \DatePeriod(
            (new \DateTime("now -$logRotationLifetime days"))->setTime(0, 0, 0),
            new \DateInterval('P1D'),
            (new \DateTime())->setTime(0, 0, 1)
        );

        $periodDays = iterator_to_array($periodDays);

        array_walk($periodDays, function (&$day) use (&$keepPeriodDays) {
            $keepPeriodDays[] = $day->format(self::ARCHIVE_DATE_FORMAT);
        });

        $existDirectories = glob($this->logDirectory . '/*', GLOB_ONLYDIR);
        $existDirectories = array_map('basename', $existDirectories);

        $targetToDeleteDirectories = array_filter(
            $existDirectories,
            function ($existDirectory) use (&$keepPeriodDays) {
                return !in_array($existDirectory, $keepPeriodDays);
            }
        );

        try {
            foreach ($targetToDeleteDirectories as $targetToDeleteDirectory) {
                $this->varDirectory->delete('log/' . $targetToDeleteDirectory);
            }
        } catch (\Exception $e) {
            $this->logger->error('Can\'t delete archived folder: ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * Remove deprecated folders on rotation logs backup
     *
     * @return $this
     */
    public function removeDeprecatedLogFolder(): Rotate
    {
        return $this->_removeDeprecatedLogFolder();
    }

    /**
     * Find all log files
     */
    public function findLogs(): array
    {
        $logDirectory = $this->logDirectory;
        return GLOB::glob("$logDirectory/*.log");
    }

    /**
     * Rotate log files process
     * @param bool $removeDeprecated
     * @return Rotate
     */
    public function rotateLogs($removeDeprecated = true): Rotate
    {
        $files = $this->findLogs();

        if ($files) {
            $this->makeDir();
            foreach ($files as $file) {
                $this->_zipAndDeleteFile($file);
            }
        }

        if ($removeDeprecated) {
            $this->_removeDeprecatedLogFolder();
        }

        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }
}
