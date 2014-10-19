<?php
namespace GetStream\Stream;

class HttpBinFeed extends Feed
{
    const API_ENDPOINT = 'http://eu.httpbin.org';

    /**
     * @param  array $activity_data
     * @return mixed
     */
    public function addActivity($activity_data)
    {
        return $this->makeHttpRequest('post', 'POST', $activity_data);
    }

    /**
     * @param  int $activity_id
     * @param  bool $foreign_id
     * @return mixed
     */
    public function removeActivity($activity_id, $foreign_id = false)
    {
        return $this->makeHttpRequest('delete', 'DELETE');
    }

    /**
     * @param  int $offset
     * @param  int $limit
     * @param  array $options
     * @return mixed
     */
    public function getActivities($offset = 0, $limit = 20, $options = [])
    {
        $query_params = array_merge(['offset' => $offset, 'limit' => $limit], $options);

        return $this->makeHttpRequest('get', 'GET', null, $query_params);
    }
}
