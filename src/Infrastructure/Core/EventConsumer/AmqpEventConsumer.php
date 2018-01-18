<?php

namespace Davamigo\Infrastructure\Core\EventConsumer;

use Davamigo\Domain\Core\Event\Event;
use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\Event\EventCollection;
use Davamigo\Domain\Core\EventConsumer\EventConsumer;
use Davamigo\Domain\Core\EventConsumer\EventConsumerException;
use Davamigo\Infrastructure\Config\AmqpConfigurator;
use Davamigo\Infrastructure\Helper\AmqpHelper;
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
     * The connection object with AMQL queue system
     *
     * @var AMQPStreamConnection
     */
    protected $connection = null;

    /**
     * The configuration object for AMQL queue system
     *
     * @var AmqpConfigurator
     */
    protected $config = null;

    /**
     * Supported events list. $events = [ 'event_name' => 'event_class_name' ]
     *
     * @var array
     */
    protected $events = [];

    /**
     * The monolog object to log events
     *
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * Indicates if listening events
     *
     * @var bool
     */
    private $listening = false;

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
     * This will restart the communication with the AMPQ server to avoid broken pipes.
     * 0 = No timeout. Default: 3600 seconds (1 hour).
     *
     * @var int
     */
    private $waitTimeout = self::DEFAULT_WAIT_TIMEOUT;
    const OPTION_WAIT_TIMEOUT = 'wait_timeout';
    const DEFAULT_WAIT_TIMEOUT = 3600;

    /**
     * Restart attempts to listen events after a communications error occurred.
     * 0 = No restart attemps. Default: 5.
     *
     * @var int
     */
    private $restartAttempts = self::DEFAULT_RESTART_ATTEMPTS;
    const OPTION_RESTART_ATTEMPTS = 'restart_attempts';
    const DEFAULT_RESTART_ATTEMPTS = 5;

    /**
     * Wait time after a failure restart (in seconds).
     * 0 = No wait. Default: 15 seconds
     *
     * @var int
     */
    private $restartWaitTime = self::DEFAULT_RESTART_WAIT_TIME;
    const OPTION_RESTART_WAIT_TIME = 'restart_wait_time';
    const DEFAULT_RESTART_WAIT_TIME = 15;

    /**
     * AmqpEventBus constructor.
     *
     * @param AMQPStreamConnection             $connection AMQ Connection object
     * @param AmqpConfigurator                 $config     Configuration object
     * @param EventCollection|Event[]|iterable $events     List of supported events
     * @param LoggerInterface                  $logger     Monolog object
     * @param array                            $options    Configuration options
     * @throws EventConsumerException
     */
    public function __construct(
        AMQPStreamConnection $connection,
        AmqpConfigurator $config,
        $events,
        LoggerInterface $logger,
        array $options = []
    ) {
        $this->connection = $connection;
        $this->config = $config;
        $this->events = [];
        $this->logger = $logger;
        $this->addSupportedEvents($events);
        $this->readOptions($options);
    }

    /**
     * Starts listening for events from a queue or topic.
     *
     * @param string   $topic    The topic to consume (usually the name of the queue).
     * @param callable $callback Callback func to call wen new event received
     * @return $this
     * @throws EventConsumerException
     */
    final public function listen($topic, callable $callback) : EventConsumer
    {
        $this->callback = $callback;

        // Prepare AMQP system to receive events
        $channel = $this->enableBasicConsume($topic);

        // While the process in running
        while ($this->listening && count($channel->callbacks)) {
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
                $this->logger->debug($exc);
                $errorOccurred = true;
            } catch (\Exception $exc) {
                // Generic exception
                $this->logger->critical(
                    'EventConsumer::channel->wait() critical - ' . get_class($exc) . ' - ' . $exc->getMessage()
                );
                $this->logger->debug($exc);
                $errorOccurred = true;
            }

            if ($errorOccurred || $timeoutOccurred) {
                $attempts = $this->computeAttempts($timeoutOccurred);
                $channel = $this->reconnect($topic, $attempts);
                if (null == $channel) {
                    $this->stop();
                }
            }
        }

        $this->logger->info('EventConsumer - Stopped listening queue "' . $topic . '".');

        return $this;
    }

    /**
     * Stops consuming events.
     *
     * @return $this
     */
    final public function stop() : EventConsumer
    {
        $this->listening = false;
        return $this;
    }

    /**
     * Adds new events to the supported events list
     *
     * The $event parameter can be:
     * $events = [ Event ]
     * $events = [ 'event_class_name' ]
     * $events = [ 'event_name' => Event ]
     * $events = [ 'event_name' => 'event_clas_name' ]
     *
     * @param EventCollection|Event[]|iterable $events List of supported events
     * @return void
     * @throws EventConsumerException
     */
    public function addSupportedEvents($events) : void
    {
        foreach ($events as $name => $event) {
            $eventName = null;
            $eventClass = null;

            if (is_string($event) && class_exists($event) && is_subclass_of($event, Event::class)) {
                $eventClass = $event;
            } elseif (is_object($event) && is_subclass_of($event, Event::class)) {
                $eventClass = get_class($event);
            } else {
                $type = is_object($event) ? get_class($event) : gettype($event);
                throw new EventConsumerException('EventConsumer error: ' . $type . ' is not a valid event class!');
            }

            if (!is_numeric($name)) {
                $eventName = $name;
            } else {
                $eventName = $eventClass;
            }

            $this->events[$eventName] = $eventClass;
        }
    }

    /**
     * Reads the configuration options
     *
     * @param array $options
     * @return $this
     * @throws EventConsumerException
     */
    protected function readOptions(array $options) : EventConsumer
    {
        $this->waitTimeout = $options[self::OPTION_WAIT_TIMEOUT] ?? self::DEFAULT_WAIT_TIMEOUT;
        $this->restartAttempts = $options[self::OPTION_RESTART_ATTEMPTS] ?? self::DEFAULT_RESTART_ATTEMPTS;
        $this->restartWaitTime = $options[self::OPTION_RESTART_WAIT_TIME] ?? self::DEFAULT_RESTART_WAIT_TIME;
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

            // Configure exchanges and queue
            $this->configureResources($channel);

            // Limit to 1 message per worker at the same time
            $channel->basic_qos(null, 1, null);

            // Tell the server to deliver us the messages from the queue.
            $channel->basic_consume($resource, '', false, false, false, false, array($this, 'eventReceivedCallback'));
        } catch (AMQPExceptionInterface $exc) {
            $msg = 'EventConsumer error - Can not connect to a queue ' . $resource . '!';
            throw new EventConsumerException($msg, 0, $exc);
        }

        $this->listening = true;

        $this->logger->info('EventConsumer - Started listening queue "' . $resource . '"...');

        return $channel;
    }

    /**
     * Called in the constructor to configure the resources (exchanges & queues).
     *
     * Overwrite it to configure the actual resources.
     *
     * @param AMQPChannel $channel
     * @return $this
     * @throws EventConsumerException
     */
    protected function configureResources(AMQPChannel $channel) : EventConsumer
    {
        $exchange = $this->config->getDefaultExchange();
        $queues = $this->config->getDefaultQueues();
        AmqpHelper::bindExchangeAndQueue($channel, $exchange, $queues);
        return $this;
    }

    /**
     * Callback function to process event
     *
     * @param AMQPMessage $msg
     * @return void
     */
    public function eventReceivedCallback(AMQPMessage $msg) : void
    {
        $this->logger->info(
            'EventConsumer - Event received.',
            [ 'raw_data' => $msg->getBody() ] + $msg->delivery_info + $msg->get_properties()
        );

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
            $this->logger->debug($exc);

            // Requeue message for further action by sending NACK
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
     * @throws EventConsumerException
     */
    protected function decodeEventData(string $jsonData, array $metadata)
    {
        $data = json_decode($jsonData, true);

        $eventClass = $this->getEventClass($data);

        /** @var Event $event */
        $event = call_user_func($eventClass . '::create', $data);
        if ($event instanceof EventBase) {
            $event->addMetadata($metadata);
            $event->setTopic($metadata['exchange'] ?? null);
            $event->setRoutingKey($metadata['routing_key'] ?? null);
        }

        return $event;
    }

    /**
     * Returns the event class for the received data
     *
     * @param array $data
     * @return string
     * @throws EventConsumerException
     */
    protected function getEventClass(array $data) : string
    {
        // Validate the data contains an event
        if (!isset($data['type']) || 'event' != $data['type']) {
            throw new EventConsumerException('EventConsumer error - The data received is not an event!');
        }

        // Get event name from event data
        if (!isset($data['name'])) {
            throw new EventConsumerException('EventConsumer error - The event received has no name!');
        }
        $name = $data['name'];

        // Get event class from supported events
        if (!isset($this->events[$name])) {
            throw new EventConsumerException('EventConsumer error - Unrecognized event!');
        }

        return $this->events[$name];
    }

    /**
     * Sends ACK confirmation to the queue
     *
     * @param AMQPMessage $msg
     * @return void
     * @throws EventConsumerException
     */
    protected function sendAck(AMQPMessage $msg) : void
    {
        // Get data for ACK
        $ackChannel = $msg->get('channel');
        $deliveryTag = $msg->get('delivery_tag');

        try {
            // Send ACK message to the queue
            $ackChannel->basic_ack($deliveryTag);
        } catch (AMQPExceptionInterface $exc) {
            // Exception thrown when some AMQP error occurred
            $this->logger->warning(
                'EventConsumer - Error sending ACK for an event: ' . get_class($exc) . ' - ' . $exc->getMessage()
            );
            $this->logger->debug($exc);

            throw new EventConsumerException('EventConsumer - Error sending ACK for an event', 0, $exc);
        }
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

        try {
            // Send NACK message to the queue and requeue message for further action
            $ackChannel->basic_reject($deliveryTag, true);
        } catch (AMQPExceptionInterface $exc) {
            // Exception thrown when some AMQP error occurred
            $this->logger->warning(
                'EventConsumer - Error sending NACK for an event: ' . get_class($exc) . ' - ' . $exc->getMessage()
            );
            $this->logger->debug($exc);

            throw new EventConsumerException('EventConsumer - Error sending NACK for an event', 0, $exc);
        }
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
                $this->logger->debug($exc);

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
    final protected function sleep(int $seconds) : void
    {
        call_user_func('sleep', $seconds);
    }
}
