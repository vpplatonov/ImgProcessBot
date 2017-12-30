<?php
namespace Entity;

use Entity\QueueFilterAwareInterface as Filter;

class Queue
{
    const DOWNLOAD = 1;
    const DONE = 2;
    const FAILED = 3;

    private $status = self::DOWNLOAD;
    private $filename = '';
    private $url;
    private $protocol;
    private $filter = null;

    public function __construct($url = null)
    {
        if (empty($url)) {
            throw new \Exception('url not defined.');
        }
        $this->url = $url;
        return $this;
    }

    public function setFilter(Filter $f)
    {
        $this->filter = $f;
        return $this;
    }

    public function validate()
    {
        if ($this->filter instanceof Filter) {
            return $this->filter->filterQueue($this);
        }
        return $this;
    }

    /**
     * retun string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status = self::DOWNLOAD)
    {
        try {
            if (!in_array($status,[self::DOWNLOAD,self::DONE, self::FAILED])) {
                throw new \Exception('wrong status code: ' . $status);
            }
        }
        catch (\Exception $e) {
            echo $e->getMessage() . ' for Queue.'. "\n";
            $status = self::FAILED;
        }
        $this->status = $status;
        return $this;
    }
}
