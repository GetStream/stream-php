<?php
namespace GetStream\Stream;


class HttpBinFeed extends BaseFeed
{
    use GuzzleHttpTrait;
    const API_ENDPOINT = 'http://eu.httpbin.org';

    public function addActivity($activity_data)
    {
        return $this->makeHttpRequest("post", 'POST', $activity_data);
    }

    public function removeActivity($activity_id)
    {
        return $this->makeHttpRequest("delete", 'DELETE');
    }

    public function getActivities($offset = 0, $limit = 20)
    {
        $query_params = ['offset' => $offset, 'limit' => $limit];
        return $this->makeHttpRequest("get", 'GET', null, $query_params);
    }
}
