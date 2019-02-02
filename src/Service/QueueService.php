<?php

namespace App\Service;

use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Queue\Models\PeekMessagesOptions;
use MicrosoftAzure\Storage\Queue\QueueRestProxy;
use Psr\Log\LoggerInterface;

class QueueService
{
    private $logger;
    private $queueClient;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->queueClient = QueueRestProxy::createQueueService($_SERVER['AZURE_STORAGE_CONNECTION_STRING']);
    }

    public function sendMessage($msg, $queue = 'inbox')
    {
        try {
            $this->createQueue($queue);
            $this->queueClient->createMessage($queue, $msg);
        } catch (ServiceException $exception) {
            $this->logger->error('failed to create the messages: ' . $exception->getCode() . ':' . $exception->getMessage());
            throw $exception;
        }
    }

    public function peekMessages($queue = 'inbox')
    {
        try {
            // OPTIONAL: Set peek message options.
            $message_options = new PeekMessagesOptions();
            $message_options->setNumberOfMessages(10); // Default value is 1.

            $listMessagesResult = $this->queueClient->peekMessages($queue, $message_options);
            return $listMessagesResult->getQueueMessages();
        } catch (ServiceException $exception) {
            $this->logger->error('failed to get the messages: ' . $exception->getCode() . ':' . $exception->getMessage());
            throw $exception;
        }
    }

    public function getMessage($queue = 'inbox')
    {
        try {
            // $message_options = new ListMessagesOptions();
            // $message_options->setNumberOfMessages(5);
            $listMessagesResult = $this->queueClient->listMessages($queue);
            return $listMessagesResult->getQueueMessages();
        } catch (ServiceException $exception) {
            $this->logger->error('failed to get the messages: ' . $exception->getCode() . ':' . $exception->getMessage());
            throw $exception;
        }
    }

    public function deleteMessage($messageId, $popReceipt, $queue = 'inbox')
    {
        try {
            $this->queueClient->deleteMessage($queue, $messageId, $popReceipt);
        } catch (ServiceException $exception) {
            $this->logger->error('failed to delete the message: ' . $exception->getCode() . ':' . $exception->getMessage());
            throw $exception;
        }
    }

    public function createQueue($name)
    {
        try {
            $this->queueClient->createQueue($name);

        } catch (ServiceException $exception) {
            $this->logger->error('failed to create the queue: ' . $exception->getCode() . ':' . $exception->getMessage());
        }
    }
}
