<?php
namespace Entity;

use Entity\Queue;
use Entity\QueueAwareTrait;
use PhpAmqpLib\Message\AMQPMessage;

class ImageDownloader
{
    const FILE_MASK = '//';
    use QueueAwareTrait;

    /**
     * @var Queue
     */
    private $queue = null;

    public function __construct(AMQPMessage $msg, $dir = '')
    {

        $this->queue = new Queue($msg->body);

        return $this;
    }

    public function download() {

        // download curl

        if ($this->queue instanceof Queue && $this->download_image($this->queue->getUrl()))
        {
            $this->queue->setStatus(Queue::DONE);
        }
        else
        {
            throw new \Exception('url not available.');
        }

        return $this->queue;
    }

    protected function download_image($image_url){
        $parts = parse_url($image_url);
        $image_file = $parts['path'];
        /*
        $image_file = preg_match(self::FILE_MASK, $image_url);
        if (array($image_file))
        {
            $image_file = $image_file[1];
        }
        else
        {
            return FALSE;
        }
        */

        $fp = fopen('data/img' . $image_file, 'w+');              // open file handle

        $ch = curl_init($image_url);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // enable if you want
        curl_setopt($ch, CURLOPT_FILE, $fp);          // output to file
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);      // some large value to allow curl to run for a long time
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        // curl_setopt($ch, CURLOPT_VERBOSE, true);   // Enable this line to see debug prints
        curl_exec($ch);
        curl_close($ch);                              // closing curl handle
        fclose($fp);
        return TRUE;
    }
}
