<?php
namespace Entity;

use Entity\Queue;

interface QueueFilterAwareInterface
{
    public function filterQueue(Queue $queue);
}
