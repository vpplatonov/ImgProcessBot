<?php
namespace Entity;

use Entity\Queue;
use Entity\QueueSheduler;
use Entity\QueueFilter;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

class QueueDownloader
{
    private $save_dir = './data';
    private $channel = null;

    public function __construct(AMQPChannel $channel, $dir = '')
    {
        $this->save_dir = $dir;
        $this->channel = $channel;
        return $this;
    }

    /**
     * recieve Queue object from Messanger.
     *
     * var @queue Queue
     */
    public function startDownload()
    {
        // use RabbitMQ for send queue.
        $this->channel->queue_declare('downloader', 'fanout', false, false, false);

        $callback = function($msg) {
            echo " [x] Received ", $msg->body, "\n";
            sleep(1);

  //          print_r($msg::get_properties());


            $id = new ImageDownloader($msg);
            try
            {
                $queue = $id->download();
            }
            catch (\Exception $e)
            {
                $queue = new Queue($msg->body);
                $queue->setStatus(Queue::FAILED);
            }


            //  exchange w messenger for save status.
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume('downloader', '', false, true, false, false, $callback);

        while(count($this->channel->callbacks)) {
            $this->channel->wait();
        }

     //   return $queue;
    }
}