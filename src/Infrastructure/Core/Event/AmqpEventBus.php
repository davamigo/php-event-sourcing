<?php

namespace Davamigo\Infrastructure\Core\Event;

use Davamigo\Domain\Core\Event\Event;
use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\Event\EventBus;
use Davamigo\Domain\Core\Event\EventBusException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPExceptionInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * Event bus implementation using AMQP (Advanced Message Queuing Protocol) which works with many queuing systems such
 * as RabbitMQ, Apache ActiveMQ and Apache Qpid.
 *
 * @package Davamigo\Infrastructure\Core\Event
 * @author davamigo@gmail.com
 */
class AmqpEventBus implements EventBus
{
    /**
     * The connection object with AMQL queue system
     *
     * @var AMQPStreamConnection
     */
    protected $connection = null;

    /**
     * The monolog object to log events
     *
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * AmqpEventBus constructor.
     *
     * @param AMQPStreamConnection $connection  AMQ Connection object
     * @param LoggerInterface      $logger      Monolog object
     */
    public function __construct(
        AMQPStreamConnection $connection,
        LoggerInterface $logger
    ) {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    /**
     * Publishes an event to the event bus.
     *
     * @param Event $event
     * @return $this
     * @throws EventBusException
     */
    public function publishEvent(Event $event): EventBus
    {
        if (!$event->topic() && $event instanceof EventBase) {
            $event->setTopic($this->getDefaultExchange());
        }

        // Create the message (serialize the event)
        $message = $this->createMessage($event);
        $resource = $event->topic();
        $routingKey = $event->routingKey();

        if (!$resource) {
            throw new EventBusException('EventBus - Error publishing an event: The topic parameter is mandatory.');
        }

        try {
            // Create a communications channel with the queue system
            $channel = $this->getChannel();

            // Configure exchanges and queue
            $this->configureResources($channel);

            // Set the channel in transaction mode
            $this->beginTransaction($channel);

            // Publish the message to the exchange/queue
            $this->publishMessage($channel, $message, $resource, $routingKey);

            // Wait for pending acks after sending the message
            $this->commitTransaction($channel);

            // Close the communications channel
            $this->closeChannel($channel);
        } catch (AMQPExceptionInterface $exc) {
            throw new EventBusException('EventBusException - Error publishing an event to AMQP queue system.', 0, $exc);
        }

        $this->logger->info(
            'EventBus - Event published. ' .
            'resource: ' . $resource . '. ' .
            'routing-key: ' . $routingKey . '. ' .
            'raw-data: ' . $message->getBody() .'.'
        );

        return $this;
    }

    /**
     * Returns the default exchange
     *
     * @return string
     */
    protected function getDefaultExchange() : string
    {
        return 'app.events';
    }

    /**
     * Returns the default queues
     *
     * @return string[]
     */
    protected function getDefaultQueues() : array
    {
        return [
            'app.events.storage',
            'app.events.model'
        ];
    }

    /**
     * Called in the constructor to configure the resources (exchanges & queues).
     *
     * Overwrite it to configure the actual resources.
     *
     * @param AMQPChannel $channel
     * @return $this
     */
    protected function configureResources(AMQPChannel $channel) : AmqpEventBus
    {
        $exchange = $this->getDefaultExchange();
        $queues = $this->getDefaultQueues();
        $this->bindExchangeAndQueue($channel, $exchange, $queues);

        return $this;
    }

    /**
     * Declares the exchange and the queue and binds them
     *
     * @param AMQPChannel $channel
     * @param string      $exchange
     * @param string[]    $queues
     * @return $this
     */
    final protected function bindExchangeAndQueue(
        AMQPChannel $channel,
        string $exchange,
        array $queues = []
    ) : AmqpEventBus {
        $channel->exchange_declare($exchange, 'fanout', false, true, false);
        foreach ($queues as $queue) {
            $channel->queue_declare($queue, false, true, false, false);
            $channel->queue_bind($queue, $exchange);
        }
        return $this;
    }

    /**
     * Create a communications channel with the queue system.
     *
     * @return AMQPChannel
     */
    final protected function getChannel() : AMQPChannel
    {
        return $this->connection->channel();
    }

    /**
     * Close the communications channel with the queue system.
     *
     * @param AMQPChannel $channel
     * @return $this
     */
    final protected function closeChannel(AMQPChannel $channel) : AmqpEventBus
    {
        $channel->close();
        return $this;
    }

    /**
     * Creates a message object to publish to the queue system.
     *
     * @param Event $event
     * @return AMQPMessage
     */
    protected function createMessage(Event $event) : AMQPMessage
    {
        $rawData = $this->encodeMessage($event->serialize());
        $metadata = $this->prepareMetatada($event->metadata());

        return new AMQPMessage($rawData, $metadata);
    }

    /**
     * Encodes the message in JSON format.
     *
     * @param array $data
     * @return string
     */
    protected function encodeMessage(array $data) : string
    {
        return json_encode($data);
    }

    /**
     * Prepares the metadata of the message.
     *
     * @param array $metadata
     * @return array
     */
    protected function prepareMetatada(array $metadata) : array
    {
        $metadata += [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ];

        unset($metadata['topic']);
        unset($metadata['routing_key']);

        return $metadata;
    }

    /**
     * Publish the message to the exchange/queue.
     *
     * @param AMQPChannel $channel
     * @param AMQPMessage $message
     * @param string      $resource
     * @param string|null $routingKey
     * @return AmqpEventBus
     * @return $this
     */
    final protected function publishMessage(
        AMQPChannel $channel,
        AMQPMessage $message,
        string $resource,
        $routingKey
    ) : AmqpEventBus {
        $channel->basic_publish($message, $resource, $routingKey);
        return $this;
    }

    /**
     * Sets the channel in transaction mode.
     *
     * @param AMQPChannel $channel
     * @return $this
     */
    final protected function beginTransaction(AMQPChannel $channel) : AmqpEventBus
    {
        $channel->tx_select();
        return $this;
    }

    /**
     * Waits for pending acks after sending the message.
     *
     * @param AMQPChannel $channel
     * @return $this
     */
    final protected function commitTransaction(AMQPChannel $channel) : AmqpEventBus
    {
        $channel->tx_commit();
        return $this;
    }
}
