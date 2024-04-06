<?php

namespace PPGroup\LogRotation\Cron;

use PPGroup\LogRotation\Model\Rotate as RotateLog;

class RotateLogs
{
    /**
     * @var RotateLog
     */
    protected $rotateLog;


    public function __construct(
        RotateLog $rotateLog
    ) {
        $this->rotateLog = $rotateLog;
    }

    public function execute()
    {
        if (!$this->rotateLog->getConfig()->isLogRotationEnabled()) {
            return $this;
        }

        try {
            $this->rotateLog->rotateLogs(true);
        } catch (\Exception $e) {
        }

        return $this;
    }
}
