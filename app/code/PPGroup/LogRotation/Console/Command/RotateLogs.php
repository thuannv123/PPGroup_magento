<?php

namespace PPGroup\LogRotation\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PPGroup\LogRotation\Logger\Logger;
use PPGroup\LogRotation\Model\Rotate as RotateLog;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RotateLogs extends Command
{
    const ROTATE_ARGUMENT = 'rotate';
    const SKIP_CLEAR_OPTION = 'skip-clear';

    /**
     * @var RotateLog
     */
    protected $rotateLog;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * RotateLogs constructor.
     * @param RotateLog $rotateLog
     * @param Logger $logger
     * @param State $state
     * @param DateTime $dateTime
     */
    public function __construct(
        RotateLog $rotateLog,
        Logger $logger,
        State $state,
        DateTime $dateTime
    ) {
        $this->rotateLog = $rotateLog;
        $this->logger = $logger;
        $this->state = $state;
        $this->dateTime = $dateTime;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("ppgroup:varlog:rotate");
        $this->setDescription("Rotate the var logs");
        $this->setDefinition([
            new InputArgument(
                self::ROTATE_ARGUMENT,
                InputArgument::OPTIONAL,
                'Generate'
            ),
            new InputOption(
                self::SKIP_CLEAR_OPTION,
                's',
                InputOption::VALUE_NONE,
                'Skip clearing the deprecated folders'
            )
        ]);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->input = $input;
        $this->output = $output;

        $totalStartTime = microtime(true);

        try {
            $this->state->setAreaCode(Area::AREA_GLOBAL);
        } catch (LocalizedException $e) {
            $this->output->writeln('<error>There was a problem while setAreCode.</error>');
            return Cli::RETURN_FAILURE;
        }

        try {
            $enabled = $this->rotateLog->getConfig()->isLogRotationEnabled();

            if (!$enabled) {
                $this->output->writeln('<error>Please enable the var log rotation on admin-end.</error>');
                return Cli::RETURN_FAILURE;
            }

            if (!$input->getOption(self::SKIP_CLEAR_OPTION)) {
                $this->rotateLog->rotateLogs();
            } else {
                $this->rotateLog->rotateLogs(false);
            }

            $totalEndTime = microtime(true);
            $totalResultTime = $totalEndTime - $totalStartTime;
            $this->output->writeln('<info>Total execution time: ' . gmdate('H:i:s', $totalResultTime) . '</info>');

        } catch (\Exception $e) {
            $this->output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
