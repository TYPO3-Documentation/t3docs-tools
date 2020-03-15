<?php

namespace T3docs\T3docsTools\GitHub;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class GitHubApi
{
    protected $client;

    /**
     * If not used, the number of requests will be limited
     *
     * @var string GitHub access token
     */
    protected $token;

    public function __construct(string $url = null, string $token = null)
    {
        if ($url === null) {
            $url = 'https://api.github.com/';
        }
        $this->client = new Client(['base_uri' => $url]);
        if ($token) {
            $this->token = $token;
        }
    }


    /**
     *
     *
     * @param string $path
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $path) : array
    {
        $options = [];
        if ($this->token) {
            $options = [
                'headers' => [
                    'Authorization' => 'token ' . $this->token
                ]
            ];
        }
        try {
            $response = $this->client->request('GET', $path, $options);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            print("Exception:" . $e->getTraceAsString() . "\n");
            print("Url: $path\n");
            return [];
        }

        if ($response->getStatusCode() !== 200) {
            return [];
        }
        return json_decode($response->getBody(), true);
    }

    /**
     * - by default, this returns only 30 commits
     *   you can change the number of commits with per_page= (max 100)
     * - you can get further pages with page=
     *
     * You MUST use the URLs supplied in the HTTP header link,
     * example :
     * Link: <https://api.github.com/repositories/54468502/commits?since=2019-01-01T00%3A00%3A00+01%3A00&until=2019-12-31T23%3A59%3A00+01%3A00&page=2>; rel="next", <https://api.github.com/repositories/54468502/commits?since=2019-01-01T00%3A00%3A00+01%3A00&until=2019-12-31T23%3A59%3A00+01%3A00&page=7>; rel="last"
     *
     * https://developer.github.com/v3/#pagination
     * https://developer.github.com/v3/guides/traversing-with-pagination/
     *
     * @param string $path
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAllWithPagination(string $url) : array
    {
        $options = [];
        if ($this->token) {
            $options = [
                'headers' => [
                    'Authorization' => 'token ' . $this->token
                ]
            ];
        }
        $url .= '&per_page=100';

        $results = [];
        $newUrl = $url;
        while ($newUrl) {

            try {
                $response = $this->client->request('GET', $newUrl, $options);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                print("Exception:" . $e->getTraceAsString() . "\n");
                print("Url: $url\n");
                return [];
            }
            if ($response->getStatusCode() !== 200) {
                return [];
            }
            $responseHeaders = $response->getHeaders();
            if ($responseHeaders['Link'] ?? false) {
                $linkHeader = $responseHeaders['Link'];
                $newUrl = $this->getNextPage($linkHeader);
            } else {
                $newUrl = '';
            }
            $results = array_merge($results, json_decode($response->getBody(), true));
        }
        return $results;
    }

    protected function getNextPage(array $linkHeader) :string
    {
        if (!($linkHeader[0] ?? false)) {
            return '';
        }
        $matches = [];
        preg_match('/<(https:\/\/api.github.com[^>]*)>; rel="next"/', $linkHeader[0], $matches);
        if (!($matches[1] ?? false)) {
            return '';
        }
        return $matches[1];
    }


}