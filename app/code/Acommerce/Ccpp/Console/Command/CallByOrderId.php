<?php

namespace Acommerce\Ccpp\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Acommerce\Ccpp\Cron\InquiryTransaction;

/**
 * Class SomeCommand
 */
class CallByOrderId extends Command
{
    const OrderId = 'orderid';

    /**
     * InquiryTransaction
     *
     * @var InquiryTransaction
     */
    protected $inquiryTransaction;

//
//    public function __construct(
//        InquiryTransaction $inquiryTransaction
//    ) {
//        $this->inquiryTransaction = $inquiryTransaction;
//        parent::__construct();
//    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('ccpp:call:orderid');
        $this->setDescription('Call to 2c2p to check order status by Sale OrderID.');
        $this->addOption(
            self::OrderId,
            null,
            InputOption::VALUE_REQUIRED,
            'Sales OrderID'
        );

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($orderId = $input->getOption(self::OrderId)) {
            $output->writeln('<info>Provided $orderId is `' . $orderId . '`</info>');
            $inquiryTransaction = \Magento\Framework\App\ObjectManager::getInstance()->create(
                'Acommerce\Ccpp\Cron\InquiryTransaction'
            );
            $result = $inquiryTransaction->callByOrderId($orderId);
            $output->writeln('<info>' . $result . '</info>');
            $output->writeln('<info>Yes! You are already connected to 2c2p.</info>');
        }else{
            $output->writeln('<error>Please provided the OrderId from 2c2p Backend.</error>');
        }
    }
}
