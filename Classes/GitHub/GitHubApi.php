<?php

namespace T3docs\T3docsTools\GitHub;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class GitHubApi
{
    protected $client;

    public function __construct(string $url = null)
    {
        if ($url === null) {
            $url = 'https://api.github.com/';
        }
        $this->client = new Client(['base_uri' => $url]);
    }

    public function get(string $path) : array
    {
        $response = $this->client->request('GET', $path);
        if ($response->getStatusCode() !== 200) {
            return [];
        }
        return json_decode($response->getBody(), true);
    }


}