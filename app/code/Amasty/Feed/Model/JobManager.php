<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model;

use Magento\Framework\App\ResourceConnection;

class JobManager
{
    public const JOB_STATUS_DONE = 0;
    public const JOB_STATUS_FAILED = 1;
    public const JOB_STATUS_PROCESSING = 2;

    public const DEFAULT_JOBS_LIMIT = 4;

    /**
     * @var array
     */
    private $allPids = [];

    /**
     * @var array
     */
    private $jobsInProgress = [];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var int
     */
    private $maxJobs;

    public function __construct(
        ResourceConnection $resourceConnection,
        $maxJobs = self::DEFAULT_JOBS_LIMIT
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->maxJobs = $maxJobs;
    }

    public function fork(): int
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $pid = \pcntl_fork();
        if ($pid == -1) {
            throw new \RuntimeException('Could not fork a child process.');
        } elseif ($pid) {
            $this->allPids[] = $pid;
            $this->jobsInProgress[$pid] = $pid;
            // Prevent issues related to lost db descriptors in master process
            $this->resourceConnection->closeConnection();
        }

        return $pid;
    }

    public function getJobStatus(int $pid, bool $waitForTermination = false): int
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        switch (pcntl_waitpid($pid, $status, $waitForTermination ? 0 : WNOHANG)) {
            case $pid:
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                return pcntl_wexitstatus($status) === 0 ? self::JOB_STATUS_DONE : self::JOB_STATUS_FAILED;
            case 0:
                return self::JOB_STATUS_PROCESSING;
            default:
                return self::JOB_STATUS_FAILED;
        }
    }

    public function waitForFreeSlot(): int
    {
        while (count($this->jobsInProgress) >= $this->maxJobs) {
            foreach ($this->jobsInProgress as $pid) {
                switch ($this->getJobStatus($pid)) {
                    case self::JOB_STATUS_DONE:
                        unset($this->jobsInProgress[$pid]);
                        return $pid;
                    case self::JOB_STATUS_FAILED:
                        throw new \RuntimeException('One of feed generation workers had failed.');
                    default:
                        continue 2;
                }
            }

            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            sleep(1);
        }

        return 0;
    }

    public function waitForJobs(array $pids): \Generator
    {
        foreach ($pids as $pid) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            if (pcntl_waitpid($pid, $status) === -1) {
                throw new \RuntimeException(
                    'Error while waiting for feed chunk generated; Status: ' . $status
                );
            }
            unset($this->jobsInProgress[$pid]);

            yield $pid;
        }
    }

    public function waitForJobCompletion()
    {
        return $this->waitForJobs($this->jobsInProgress);
    }

    public function waitForAllJobs()
    {
        $result = [];
        foreach ($this->waitForJobCompletion() as $pid) {
            $result[] = $pid;
        }

        return $result;
    }

    /**
     * Protect against Zombie children
     */
    public function __destruct()
    {
        $this->waitForJobs($this->allPids);
    }
}
