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

use League\Flysystem\AdapterInterface;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


class ProducerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('balance:producer:publish')
            ->setDescription('Balance Producer publish')
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

        $msg = new AMQPMessage('Hello Akeneo!');
        $channel->basic_publish($msg, '', 'products.new');

        $output->writeln("[x] Sent 'Hello Akeneo!'\n");
        $channel->close();
        $connection->close();
    }
}