<?php
namespace Balance\Bundle\RabbitMQBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\Debug;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConsumerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('balance:consumer:consume')
            ->setDescription('Balance Consumer consume')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('akeneo.products.new2', false, false, false, false);

        $output->writeln("[*] Waiting for messages. To exit press CTRL+C\n");
        $callback = function($msg) {
            echo " [x] Received ", $msg->body, "\n";
        };

        $channel->basic_consume('akeneo.products.new2', '', false, true, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }
    }
}