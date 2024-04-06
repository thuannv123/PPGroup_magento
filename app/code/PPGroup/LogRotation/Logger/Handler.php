<?php

namespace PPGroup\LogRotation\Logger;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Handler extends StreamHandler
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/var_log_rotation.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * @var DriverInterface
     */
    protected $filesystem;

    /**
     *  Timezone
     *
     * @var TimezoneInterface
     */
    private $timeZone;

    /**
     * Handler constructor.
     * @param DriverInterface $filesystem
     * @param null $filePath
     * @param null $fileName
     * @param TimezoneInterface|null $timeZone
     * @throws \Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        $filePath = null,
        $fileName = null,
        TimezoneInterface $timeZone = null
    ) {
        $this->filesystem = $filesystem;
        $this->timeZone = $timeZone ?: ObjectManager::getInstance()
            ->get(TimezoneInterface::class);
        if (!empty($fileName)) {
            $this->fileName = $this->sanitizeFileName($fileName);
        }
        parent::__construct(
            $filePath ? $filePath . $this->fileName : BP . DIRECTORY_SEPARATOR . $this->fileName,
            $this->loggerType
        );

        $this->setFormatter(new LineFormatter(null, null, true));
    }

    /**
     * Remove dots from file name and add date time: integration-2020-12-30.log
     *
     * @param string $fileName
     * @return string
     * @throws \InvalidArgumentException
     */
    private function sanitizeFileName($fileName)
    {
        if (!is_string($fileName)) {
            throw  new \InvalidArgumentException('Filename expected to be a string');
        }

        $parts = explode('/', $fileName);
        $parts = array_filter($parts, function ($value) {
            return !in_array($value, ['', '.', '..']);
        });

        $fileName = implode('/', $parts);

        return str_replace('.', ('-' . $this->timeZone->date()->format('Y-m-d') . '.'), $fileName);
    }

    /**
     * @{inheritDoc}
     *
     * @param $record array
     * @return void
     * @throws FileSystemException
     */
    public function write(array $record): void
    {
        $logDir = $this->filesystem->getParentDirectory($this->url);
        if (!$this->filesystem->isDirectory($logDir)) {
            $this->filesystem->createDirectory($logDir);
        }

        parent::write($record);
    }
}
