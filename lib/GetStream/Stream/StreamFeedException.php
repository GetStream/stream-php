<?php
namespace GetStream\Stream;

use GuzzleHttp\Exception\ClientException;

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
        if ($e && $e instanceof ClientException) {
            $headerValues = $e->getResponse()->getHeader("x-ratelimit-" . $headerName);

            if ($headerValues) {
                return $headerValues[0];
            }
        }

        return null;
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
