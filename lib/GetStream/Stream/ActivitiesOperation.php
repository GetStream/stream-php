<?php

namespace GetStream\Stream;

class ActivitiesOperation extends Feed
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @param ClientInterface $client
     * @param string $api_key
     * @param string $token
     */
    public function __construct(ClientInterface $client, $api_key, $token)
    {
        $this->client = $client;
        $this->api_key = $api_key;
        $this->token = $token;
    }

    /**
     * @param string $resource
     * @param string $action
     *
     * @return array
     */
    protected function getHttpRequestHeaders($resource, $action)
    {
        $headers = parent::getHttpRequestHeaders($resource, $action);
        $headers['Authorization'] = $this->token;

        return $headers;
    }

    public function partiallyUpdateActivity($data)
    {
        return $this->makeHttpRequest('activity/', 'POST', $data);
    }

    public function updateActivities($activities)
    {
        if (empty($activities)) {
            return;
        }

        return $this->makeHttpRequest('activities/', 'POST', compact('activities'));
    }

    public function getAppActivities($data = [])
    {
        $params = [];
        foreach ($data as $key => $value) {
            $params[$key] = implode(',', $value);
        }

        return $this->makeHttpRequest('activities/', 'GET', [], $params);
    }

    public function activityPartialUpdate($data = [])
    {
        return $this->makeHttpRequest('activity/', 'POST', $data);
    }
}
