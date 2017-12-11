<?php

namespace Davamigo\Infrastructure\Core\Event;

use Davamigo\Domain\Core\Event\Event;
use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\Event\EventConsumer;
use Davamigo\Domain\Core\Event\EventConsumerException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPExceptionInterface;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * Event consumer implementation using AMQP (Advanced Message Queuing Protocol) which works with many queuing systems
 * such as RabbitMQ, Apache ActiveMQ and Apache Qpid.
 *
 * @package Davamigo\Infrastructure\Core\Event
 * @author davamigo@gmail.com
 */
class AmqpEventConsumer implements EventConsumer
{
    /**
     * The AMQP connection
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
     * Indicates if the process in started
     *
     * @var bool
     */
    private $running = false;

    /**
     * Base event object to decode the read message into an event
     *
     * @var Event
     */
    private $baseEvent = null;

    /**
     * Callback func to call wen new event received
     *
     * function func(EventInterfave $event): void
     *
     * @var callable
     */
    private $callback = null;

    /**
     * Timeout in seconds to wait for messages until an AMQPTimeoutException is thrown.
     * 0 = No timeout. Default: 3600 seconds (1 hour).
     *
     * @var int
     */
    private $waitTimeout = self::DEFAULT_WAIT_TIMEOUT;

    /**
     * Constant: Default timeout for wait to messages (seconds)
     */
    const DEFAULT_WAIT_TIMEOUT = 3600;

    /**
     * Restart attempts to start consuming events after a communications error.
     * 0 = No restart. Default: 5.
     *
     * @var int
     */
    private $restartAttempts = self::DEFAULT_RESTART_ATTEMPTS;

    /**
     * Constant: Default restart attempts
     */
    const DEFAULT_RESTART_ATTEMPTS = 5;

    /**
     * Wait time after a failure restart (in seconds)
     * 0 = No wait. Default: 15 seconds
     *
     * @var int
     */
    private $restartWaitTime = self::DEFAULT_RESTART_WAIT_TIME;

    /**
     * Constant: Default restart wait time in seconds
     */
    const DEFAULT_RESTART_WAIT_TIME = 15;

    /**
     * AmqpEventBus constructor.
     *
     * @param AMQPStreamConnection $connection  AMQ Connection object
     * @param LoggerInterface      $logger      Monolog object
     * @param array                $options     Configuration options
     */
    public function __construct(
        AMQPStreamConnection $connection,
        LoggerInterface $logger,
        array $options = []
    ) {
        $this->connection = $connection;
        $this->logger = $logger;
        $this->readOptions($options);
        $this->configureResources();
    }

    /**
     * Called in the constructor to configure the resources (exchanges & queues).
     *
     * Overwrite it to configure the actual resources.
     *
     * @return $this
     */
    public function configureResources() : EventConsumer
    {
        return $this;
    }

    /**
     * Starts consuming events from a queue. When new event arrives the callback function is called.
     *
     * @param string   $resource  The name of the queue to consume.
     * @param Event    $baseEvent The base event object to unserialize the event data.
     * @param callable $callback  Callback func to call wen new event received.
     * @return $this
     * @throws EventConsumerException
     */
    public function start($resource, Event $baseEvent, callable $callback) : EventConsumer
    {
        $this->baseEvent = $baseEvent;
        $this->callback = $callback;

        // Prepare AMQP system to receive events
        $channel = $this->enableBasicConsume($resource);

        // While the process in running
        while ($this->running && count($channel->callbacks)) {
            $timeoutOccurred = false;
            $errorOccurred = false;

            try {
                // Wait for some expected AMQP methods and dispatch to them.
                $channel->wait(null, false, $this->waitTimeout);
            } catch (AMQPTimeoutException $exc) {
                // Exception thrown when wait ends with a timeout
                $this->logger->warning(
                    'EventConsumer::channel->wait() warning - ' . get_class($exc) . ' - ' . $exc->getMessage()
                );
                $timeoutOccurred = true;
            } catch (AMQPExceptionInterface $exc) {
                // Exception thrown when some AMQP error occurred
                $this->logger->error(
                    'EventConsumer::channel->wait() error - ' . get_class($exc) . ' - ' . $exc->getMessage()
                );
                $errorOccurred = true;
            } catch (\Exception $exc) {
                // Generic exception
                $this->logger->critical(
                    'EventConsumer::channel->wait() critical - ' . get_class($exc) . ' - ' . $exc->getMessage()
                );
                $errorOccurred = true;
            }

            if ($errorOccurred || $timeoutOccurred) {
                $attempts = $this->computeAttempts($timeoutOccurred);
                $channel = $this->reconnect($resource, $attempts);
                if (null == $channel) {
                    $this->stop();
                }
            }
        }

        $this->logger->info('EventConsumer - Stopped listening queue "' . $resource . '".');

        return $this;
    }

    /**
     * Stops consuming events from a queue.
     *
     * @return $this
     */
    public function stop() : EventConsumer
    {
        $this->running = false;
        return $this;
    }

    /**
     * Reads the configuration options
     *
     * @param array $options
     * @return $this
     */
    protected function readOptions(array $options) : EventConsumer
    {
        $this->waitTimeout = $options['wait_timeout'] ?? self::DEFAULT_WAIT_TIMEOUT;
        $this->restartAttempts = $options['restart_attempts'] ?? self::DEFAULT_RESTART_ATTEMPTS;
        $this->restartWaitTime = $options['restart_wait_time'] ?? self::DEFAULT_RESTART_WAIT_TIME;
        return $this;
    }

    /**
     * Prepare AMQP system to receive events
     *
     * @param string $resource
     * @return AMQPChannel
     * @throws EventConsumerException
     */
    protected function enableBasicConsume(string $resource) : AMQPChannel
    {
        try {
            // Create a communications channel with the queue system
            $channel = $this->connection->channel();

            // Limit to 1 message per worker at the same time
            $channel->basic_qos(null, 1, null);

            // Tell the server to deliver us the messages from the queue.
            $channel->basic_consume(
                $resource,
                '',
                false,
                false,
                false,
                false,
                array($this, 'eventReceivedCallback')
            );
        } catch (AMQPExceptionInterface $exc) {
            $msg = 'EventConsumerException - Error connecting to a queue ' . $resource . '!';
            throw new EventConsumerException($msg, 0, $exc);
        }

        $this->running = true;

        $this->logger->info('EventConsumer - Started listening queue "' . $resource . '"...');

        return $channel;
    }

    /**
     * Callback function to process event
     *
     * @param AMQPMessage $msg
     * @return void
     */
    public function eventReceivedCallback(AMQPMessage $msg) : void
    {
        $this->logger->info('EventConsumer - Event received. raw-data: ' . $msg->getBody());
        $this->logger->debug('EventConsumer - Debug data: ' . json_encode($msg->delivery_info));

        // Read the received event
        $metadata = $msg->delivery_info + $msg->get_properties();
        $event = $this->decodeEventData($msg->getBody(), $metadata);

        try {
            // Call the callback func to process the new event
            call_user_func($this->callback, $event);
        } catch (\Exception $exc) {
            $this->logger->warning(
                'EventConsumer - Error procesing an event: ' . get_class($exc) . ' - ' . $exc->getMessage()
            );

            // Requeue message for further action
            $this->sendNack($msg);
            return;
        }

        // Send ACK message to the queue
        $this->sendAck($msg);
    }

    /**
     * Decodes the event data from a json string
     *
     * @param string $jsonData The raw event data
     * @param array  $metadata The metadata of the event
     * @return Event|array
     */
    protected function decodeEventData(string $jsonData, array $metadata)
    {
        $data = json_decode($jsonData, true);

        if (null === $this->baseEvent || !$this->baseEvent instanceof Event) {
            return [
                'data' => $data,
                'metadata' => $metadata
            ];
        }

        /** @var Event $event */
        $event = call_user_func(get_class($this->baseEvent) . '::create', $data);
        if ($event instanceof EventBase) {
            $event->addMetadata($metadata);
        }

        return $event;
    }

    /**
     * Sends ACK confirmation to the queue
     *
     * @param AMQPMessage $msg
     * @return void
     */
    protected function sendAck(AMQPMessage $msg) : void
    {
        // Get data for ACK
        $ackChannel = $msg->get('channel');
        $deliveryTag = $msg->get('delivery_tag');

        // Send ACK message to the queue
        $ackChannel->basic_ack($deliveryTag);
    }

    /**
     * Sends NACK confirmation to the queue
     *
     * @param AMQPMessage $msg
     * @return void
     */
    protected function sendNack(AMQPMessage $msg) : void
    {
        // Get data for NACK
        $ackChannel = $msg->get('channel');
        $deliveryTag = $msg->get('delivery_tag');

        // Send NACK message to the queue and requeue message for further action
        $ackChannel->basic_reject($deliveryTag, true);
    }

    /**
     * Computes the attempts to recconnect
     *
     * @param bool $timeoutOccurred
     * @return int
     */
    protected function computeAttempts(bool $timeoutOccurred) : int
    {
        $attempts = $this->restartAttempts;
        if ($timeoutOccurred && !$attempts) {
            $attempts = 1;
        }
        return $attempts;
    }

    /**
     * Reconnects to the AMQP server and returns an opened channel
     *
     * @param string $resource
     * @param int    $attempts
     * @return AMQPChannel|null
     */
    protected function reconnect(string $resource, int $attempts)
    {
        $channel = null;
        while ($attempts-- > 0 && null == $channel) {
            try {
                $this->logger->info('EventConsumer - Restarting connection...');

                // Reconnect to RabbitMQ
                $this->connection->reconnect();

                // Recreate channel & prepare AMQP system to receive events
                $channel = $this->enableBasicConsume($resource);
            } catch (\Exception $exc) {
                $this->logger->error(
                    'EventConsumer::connection->reconnect() - ' . get_class($exc) . ' - ' . $exc->getMessage()
                );
                if ($attempts > 0 && $this->restartWaitTime > 0) {
                    $this->logger->info('EventConsumer - Waiting ' . $this->restartWaitTime . ' seconds...');
                    $this->sleep($this->restartWaitTime);
                }
            }
        }

        return $channel;
    }

    /**
     * Sleep current process to wait to reconnect
     *
     * @param int $seconds
     * @return void
     * @codeCoverageIgnore
     */
    protected function sleep(int $seconds) : void
    {
        call_user_func('sleep', $seconds);
    }
}
