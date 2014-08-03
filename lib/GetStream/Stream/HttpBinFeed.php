<?php
namespace GetStream\Stream;


class HttpBinFeed extends Feed
{
    const API_ENDPOINT = 'http://eu.httpbin.org';

    public function addActivity($activity_data)
    {
        return $this->makeHttpRequest("post", 'POST', $activity_data);
    }

    public function removeActivity($activity_id, $foreign_id = false)
    {
        return $this->makeHttpRequest("delete", 'DELETE');
    }

    public function getActivities($offset = 0, $limit = 20, $options = array())
    {
        $query_params = ['offset' => $offset, 'limit' => $limit];
        $query_params = array_merge($query_params, $options);
        return $this->makeHttpRequest("get", 'GET', null, $query_params);
    }
}
