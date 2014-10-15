<?php

namespace GetStream\Stream;


class BaseFeed
{
    protected $feed;
    protected $feed_type;
    protected $feed_id;
    protected $token;
    protected $api_key;
    protected $client;
    const API_ENDPOINT = 'https://getstream.io/api';

    public function __construct($client, $feed, $api_key, $token)
    {
        Client::validateFeed($feed);
        $this->feed = $feed;
        $feed_components = explode(':', $feed);
        $this->feed_type = $feed_components[0];
        $this->feed_id = $feed_components[1];
        $this->token = $token;
        $this->api_key = $api_key;
        $this->client = $client;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getFeedId()
    {
        return $this->feed;
    }

    public function signToField($to)
    {
        $recipients = [];
        foreach ($to as $recipient) {
            Client::validateFeed($recipient);
            $recipient_feed = $this->client->feed($recipient);
            $recipient_token = $recipient_feed->getToken();
            $recipients[] = "$recipient $recipient_token";
        }
        return $recipients;
    }

    public function addActivity($activity_data)
    {
        if (array_key_exists('to', $activity_data)) {
            $activity_data['to'] = $this->signToField($activity_data['to']);
        }
        return $this->makeHttpRequest("feed/{$this->feed_type}/{$this->feed_id}/", 'POST', $activity_data);
    }

    public function addActivities($activities_data)
    {
        foreach ($activities_data as $i => $activity) {
            if (array_key_exists('to', $activity)) {
                $activities_data[$i]['to'] = $this->signToField($activity['to']);
            }
        }
        $data = array("activities" => $activities_data);
        return $this->makeHttpRequest("feed/{$this->feed_type}/{$this->feed_id}/", 'POST', $data);
    }

    public function removeActivity($activity_id, $foreign_id = false)
    {
        $query_params = array();
        if ($foreign_id === true) {
            $query_params['foreign_id'] = 1;
        }
        return $this->makeHttpRequest("feed/{$this->feed_type}/{$this->feed_id}/{$activity_id}/", 'DELETE', null, $query_params);
    }

    public function getActivities($offset = 0, $limit = 20, $options = array())
    {
        $query_params = ['offset' => $offset, 'limit' => $limit];
        if (array_key_exists('mark_read', $options) && is_array($options['mark_read'])) {
            $options['mark_read'] = implode(",", $options['mark_read']);
        }
        $query_params = array_merge($query_params, $options);
        return $this->makeHttpRequest("feed/{$this->feed_type}/{$this->feed_id}/", 'GET', null, $query_params);
    }

    public function followFeed($feed)
    {
        Client::validateFeed($feed);
        $data = ["target" => $feed];
        if ($this->client !== null) {
            $target_feed = $this->client->feed($feed);
            $data["target_token"] = $target_feed->getToken();
        }
        return $this->makeHttpRequest("feed/{$this->feed_type}/{$this->feed_id}/follows/", 'POST', $data);
    }

    public function followers($offset = 0, $limit = 25)
    {
        $query_params = array(
            "limit" => $limit,
            "offset" => $offset,
        );
        return $this->makeHttpRequest("feed/{$this->feed_type}/{$this->feed_id}/followers/", 'GET', null, $query_params);
    }

    public function following($offset = 0, $limit = 25, $filter = array())
    {
        $query_params = array(
            "limit" => $limit,
            "offset" => $offset,
            "filter" => implode(',', $filter),
        );
        return $this->makeHttpRequest("feed/{$this->feed_type}/{$this->feed_id}/follows/", 'GET', null, $query_params);
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
