<?php

namespace Balance\Bundle\RabbitMQBundle\Writer\File;

use Akeneo\Component\Buffer\BufferFactory;
use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Pim\Component\Connector\Writer\File\CsvProductWriter as BaseCsvProductWriter;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire;

/**
 * Write product data into a RabbitMQ queue
 *
 * @author    Maxim Baibakov <maxim@balanceinternet.com.au>
 * @copyright 2016 Balance Internet (http://www.balanceinternet.com.au)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RabbitMQProductWriter extends BaseCsvProductWriter
{
    const QUEUE = 'akeneo.products.new';

    protected $amqpHost;

    protected $amqpPort;

    protected $amqpUsername;

    protected $amqpPassword;

    protected $amqpVituralhost;

    protected $amqpExchange;

    protected $amqpRouteKey;

    /**
     * Set the amcq_host character
     *
     * @param string $amcq_host
     */
    public function setAmqpHost($amcq_host)
    {
        $this->amqpHost = $amcq_host;
    }

    /**
     * Get the amcq_host character
     *
     * @return string
     */
    public function getAmqpHost()
    {
        return $this->amqpHost;
    }

    /**
     * Set the amqpPort character
     *
     * @param string $amqpPort
     */
    public function setAmqpPort($amqpPort)
    {
        $this->amqpPort = $amqpPort;
    }

    /**
     * Get the amqpPort character
     *
     * @return string
     */
    public function getAmqpPort()
    {
        return $this->amqpPort;
    }

    /**
     * Set the amqpUsername character
     *
     * @param string $amqpUsername
     */
    public function setAmqpUsername($amqpUsername)
    {
        $this->amqpUsername = $amqpUsername;
    }

    /**
     * Get the amqpUsername character
     *
     * @return string
     */
    public function getAmqpUsername()
    {
        return $this->amqpUsername;
    }

    /**
     * Set the amqpPassword character
     *
     * @param string $amqpPassword
     */
    public function setAmqpPassword($amqpPassword)
    {
        $this->amqpPassword = $amqpPassword;
    }

    /**
     * Get the amqpPassword character
     *
     * @return string
     */
    public function getAmqpPassword()
    {
        return $this->amqpPassword;
    }

    /**
     * Set the amqpVituralHost character
     *
     * @param string $amqpVituralHost
     */
    public function setAmqpVituralhost($amqpVituralHost)
    {
        $this->amqpVituralhost = $amqpVituralHost;
    }

    /**
     * Get the amqpVituralHost character
     *
     * @return string
     */
    public function getAmqpVituralHost()
    {
        return $this->amqpVituralhost;
    }

    /**
     * Set the amqpExchange character
     *
     * @param string $amqpExchange
     */
    public function setAmqpExchange($amqpExchange)
    {
        $this->amqpExchange = $amqpExchange;
    }

    /**
     * Get the amqpExchange character
     *
     * @return string
     */
    public function getAmqpExchange()
    {
        return $this->amqpExchange;
    }

    /**
     * Set the amqpRouteKey character
     *
     * @param string $amqpRouteKey
     */
    public function setAmqpRouteKey($amqpRouteKey)
    {
        $this->amqpRouteKey = $amqpRouteKey;
    }

    /**
     * Get the amqpRouteKey character
     *
     * @return string
     */
    public function getAmqpRouteKey()
    {
        return $this->amqpRouteKey;
    }

    /**
     * Set the amqpHeaderTo character
     *
     * @param string $amqpHeaderTo
     */
    public function setAmqpHeaderTo($amqpHeaderTo)
    {
        $this->amqpHeaderTo = $amqpHeaderTo;
    }

    /**
     * Get the amqpHeaderTo character
     *
     * @return string
     */
    public function getAmqpHeaderTo()
    {
        return $this->amqpHeaderTo;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return
            array_merge(
                parent::getConfigurationFields(),
                [
                    'amqpHost' => [
                        'options' => [
                            'label' => 'rabbit_connector.export.amqpHost.label',
                            'help'  => 'rabbit_connector.export.amqpHost.help'
                        ]
                    ],
                    'amqpPort' => [
                        'options' => [
                            'label' => 'rabbit_connector.export.amqpPort.label',
                            'help'  => 'rabbit_connector.export.amqpPort.help'
                        ]
                    ],
                    'amqpUsername' => [
                        'options' => [
                            'label' => 'rabbit_connector.export.amqpUsername.label',
                            'help'  => 'rabbit_connector.export.amqpUsername.help'
                        ]
                    ],
                    'amqpPassword' => [
                        'options' => [
                            'label' => 'rabbit_connector.export.amqpPassword.label',
                            'help'  => 'rabbit_connector.export.amqpPassword.help'
                        ]
                    ],
                    'amqpVituralhost' => [
                        'options' => [
                            'label' => 'rabbit_connector.export.amqpVituralhost.label',
                            'help'  => 'rabbit_connector.export.amqpVituralhost.help'
                        ]
                    ],
                    'amqpExchange' => [
                        'options' => [
                            'label' => 'rabbit_connector.export.amqpExchange.label',
                            'help'  => 'rabbit_connector.export.amqpExchange.help'
                        ]
                    ],
                    'amqpRouteKey' => [
                        'options' => [
                            'label' => 'rabbit_connector.export.amqpRouteKey.label',
                            'help'  => 'rabbit_connector.export.amqpRouteKey.help'
                        ]
                    ],
                    'amqpHeaderTo' => [
                        'options' => [
                            'label' => 'rabbit_connector.export.amqpHeaderTo.label',
                            'help'  => 'rabbit_connector.export.amqpHeaderTo.help'
                        ]
                    ],
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            $this->buffer->write($item);
        }
    }

    /**
     * Flush items into a queue
     */
    public function flush()
    {
        $connection = new AMQPStreamConnection(
            $this->getAmqpHost(),
            $this->getAmqpPort(),
            $this->getAmqpUsername(),
            $this->getAmqpPassword(),
            $this->getAmqpVituralHost()
        );
        $channel = $connection->channel();
        $channel->queue_declare(self::QUEUE, false, true, false, false);
        foreach ($this->buffer as $incompleteItem) {

            $msg = new AMQPMessage($incompleteItem);
            $headers = new Wire\AMQPTable();
            $headers->set('to', $this->getAmqpHeaderTo());
            $msg->set('application_headers', $headers);
            $channel->basic_publish($msg, $this->getAmqpExchange(), $this->getAmqpRouteKey());

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }

        $channel->close();
        $connection->close();
    }

}


