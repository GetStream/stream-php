<?php

namespace GetStream\Stream;


class BaseFeed
{
    protected $feed;
    protected $feed_type;
    protected $feed_id;
    protected $token;
    protected $api_key;
    const API_ENDPOINT = 'https://getstream.io/api';

    public function __construct($feed, $api_key, $token)
    {
        Client::validateFeed($feed);
        $this->feed = $feed;
        $feed_components = explode(':', $feed);
        $this->feed_type = $feed_components[0];
        $this->feed_id = $feed_components[1];
        $this->token = $token;
        $this->api_key = $api_key;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function addActivity($activity_data)
    {
        return $this->makeHttpRequest("feed/{$this->feed_type}/{$this->feed_id}/", 'POST', $activity_data);
    }

    public function removeActivity($activity_id)
    {
        return $this->makeHttpRequest("feed/{$this->feed_type}/{$this->feed_id}/{$activity_id}/", 'DELETE');
    }

    public function getActivities($offset = 0, $limit = 20, $options = array())
    {
        $query_params = ['offset' => $offset, 'limit' => $limit];
        $query_params = array_merge($query_params, $options);
        return $this->makeHttpRequest("feed/{$this->feed_type}/{$this->feed_id}/", 'GET', null, $query_params);
    }

    public function followFeed($feed)
    {
        Client::validateFeed($feed);
        $data = ["target" => $feed];
        return $this->makeHttpRequest("feed/{$this->feed_type}/{$this->feed_id}/follows/", 'POST', $data);
    }

    public function unfollowFeed($feed)
    {
        Client::validateFeed($feed);
        return $this->makeHttpRequest("feed/{$this->feed_type}/{$this->feed_id}/follows/{$feed}/", 'DELETE');
    }

    public function delete()
    {
        return $this->makeHttpRequest("feed/{$this->feed_type}/{$this->feed_id}/", 'DELETE');
    }
}
