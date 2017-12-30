<?php
namespace Entity;

use Entity\Queue;
//use Entity\QueueSheduler;
use Entity\QueueFilter;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

class QueueSheduler
{
    const FILE_PATTERN = '/^(.*)\.txt/i';
    const DELIMITER = "\n";

    private $file_name = '';
    private $file_seek_pointer = 0;
    private $file_num_lines = 0;
    private $file_index = 0;
    private $file_dir = './data';
    private $channel;

    public function __construct($filename = null, AMQPChannel $channel)
    {
        if (empty($filename))
        {
            $this->takeThemAll();
        }
        else {
            $this->file_name = $filename;
        }
        $this->channel = $channel;
        return $this;
    }

    public function setFieName($filename)
    {
        $this->filename = $filename;
    }

    public function takeThemAll()
    {
        $dir = realpath('./data');
        $file_array = file_scan_directory($dir, self::FILE_PATTERN);

        if (empty($file_array))
        {
            throw new \Exeption('invalid file extension ' . $file_ext);
        }
        $this->file_dir = $dir;
        $this->file_name = $file_array;
    }

    /**
     * generate Queue object from file.
     *
     * return @queue Entity\Queue
     */
    public function getQueues()
    {
        $id = 0;
        $queues = [];
        $file = '';

        if (is_array($this->file_name))
        {
            reset($this->file_name);
            $file = current($this->file_name); //[$this->file_index];
  //          echo $file . "\n";
        }
        else
        {
            $file = $this->file_name;
        }

        $handle = fopen($this->file_dir . '/' . $file,'r');

        if ($handle)
        {

            if ($this->file_seek_pointer != 0)
            {
                fseek($handle,$this->file_seek_pointer);
            }
            while ($line = fgets($handle,4096))
            {
                if ($id != 0 && $line[0] != $id )
                {
                    break;
                }
                else
                {
                    $id = $line[0];
                }
                array_push($queues, $line);

            }

            if (feof($handle))
            {
                if (is_array($this->file_name))
                {
                  array_shift($this->file_name);
                }
                else {
                  $this->file_name = null;
                }
                $this->file_seek_pointer = 0;
            }
            else
            {
                $this->file_seek_pointer = ftell($handle);
            }

            fclose($handle);
        }
        else
        {
            throw new \Exeption('error opening file for reading ' . $file );
        }


        // use RabbitMQ for send queue.
        $this->channel->queue_declare('downloader', 'fanout', false, false, false);
        $filter = new QueueFilter();
        foreach($queues as $q)
        {
            $queue = new Queue($q);
            $queue->setFilter($filter);
            $queue->validate();

            $prop = array(
                    'status' => $queue->getStatus(),
                    // direct, topic, headers и fanout
//                   'content_type' => 'fanout',
                    'content_encoding' => 'UTF8',
//                    'application_headers' => 'table_object',
                    'delivery_mode' => 2, // make message persistent
//                    'priority' => 'octet',
//                    'correlation_id' => 'shortstr',
//                    'reply_to' => 'shortstr',
//                    'expiration' => 'shortstr',
//                    'message_id' => 'shortstr',
                    'timestamp' => time(),
                    'type' => $queue->getStatus(),
//                    'user_id' => 'shortstr',
//                    'app_id' => 'shortstr',
//                    'cluster_id' => 'shortstr',
            );
            $msg = new AMQPMessage($queue->getUrl(), $prop);
            $this->channel->basic_publish($msg, 'downloader');
        }
        return count($queues);
    }
}

/**
 * Helper for scan dir.
 *
 * @param unknown $dir
 * @param unknown $pattern
 */
function file_scan_directory($dir,$pattern)
{
    $file_array = scandir($dir);
    foreach ($file_array as $key => $file)
    {
        preg_match($pattern,$file,$matches);
        if (!isset($matches[0]))
        {
            unset ($file_array[$key]);
        }
    }

    return  $file_array;
}
