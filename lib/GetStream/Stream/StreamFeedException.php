<?php
namespace GetStream\Stream;

class StreamFeedException extends \Exception
{
    private function getRateLimitValue($headerName)
    {
        /* Sample headers

           x-ratelimit-limit: 2000
           x-ratelimit-remaining: 1998
           x-ratelimit-reset: 1543604520

        */
        $e = $this->getPrevious();
        if ($e) {
            return (string)$e->getResponse()->getHeader("x-ratelimit-" . $headerName)[0];
        }
    }

    public function getRateLimitLimit()
    {
        return $this->getRateLimitValue("limit");
    }

    public function getRateLimitRemaining()
    {
        return $this->getRateLimitValue("remaining");
    }

    public function getRateLimitReset()
    {
        return $this->getRateLimitValue("reset");
    }
}
