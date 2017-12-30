<?php

include 'autoloader.php';

require_once __DIR__ . '/vendor/autoload.php';

use Entity\QueueSheduler;
use Entity\QueueDownloader;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 *
 */
function start_sheduler($file, $queue_type = 'RabbitMQ') {
  if ($queue_type == 'RabbitMQ')
  {
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();

    // $sheduler = new QueueLoaderRabbitMQ($file);
    $sheduler = new QueueSheduler($file, $channel);
    $sheduler->getQueues();

    $channel->close();
    $connection->close();
  }
}

/**
 * $dir string
 */
function start_downloader($dir = '', $queue_type = 'RabbitMQ') {
    if ($queue_type == 'RabbitMQ')
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        // $sheduler = new QueueLoaderRabbitMQ($file);
        $downloader = new QueueDownloader($channel);
        $downloader->startDownload();

        $channel->close();
        $connection->close();
    }
}


