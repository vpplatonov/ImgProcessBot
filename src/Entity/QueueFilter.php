<?php
namespace Entity;

use Entity\Queue;
use Entity\QueueAwareTrait;
use Entity\QueueFilterAwareInterface;

class QueueFilter implements
QueueFilterAwareInterface
{
    const PROTOCOL = "/^(http|https)\:\/\//i";

    use QueueAwareTrait;

    public function filterQueue(Queue $queue)
    {
        //$this->queue = $queue;
        // @TODO: check http & https protocol only
        try {
            $this->validateQueue($queue);
        }
        catch (\Exception $e) {
            echo $e->getMessage() . ' for Queue.'. "\n";
            $queue->setStatus(Queue::FAILED);
        }

        return $queue;
    }

    protected function validateQueue(Queue $queue)
    {
        if (!preg_match(self::PROTOCOL, $queue->getUrl())) {
            throw new \Exception('invalid protocol in ' . $queue->getUrl());
        }
    }
}
