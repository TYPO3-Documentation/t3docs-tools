<?php

namespace T3docs\T3docsTools\GitHub;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class GitHubApi
{
    /**
     * @var Client Guzzle HTTP client
     */
    protected $client;

    /**
     * @var string GitHub access token
     */
    protected $token;

    /**
     * @param string $token GitHub access token
     */
    public function __construct(string $token = '')
    {
        $this->client = new Client(['base_uri' => 'https://api.github.com']);
        if (!empty($token)) {
            $this->token = $token;
        }
    }

    /**
     * Send GET HTTP request to GitHub API.
     *
     * @param string $url GitHub URL or path
     * @return array GitHub response object decoded
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $url): array
    {
        $options = [];
        if (!empty($this->token)) {
            $options = [
                'headers' => [
                    'Authorization' => 'token ' . $this->token
                ]
            ];
        }

        try {
            $response = $this->client->request('GET', $url, $options);
        } catch (ClientException $e) {
            print("HTTP {$e->getCode()} thrown for \"GET $url\": {$e->getMessage()}");
            return [];
        }
        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * Send GET HTTP request to GitHub API with support of pagination.
     *
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
     * @param string $url GitHub URL or path
     * @return array GitHub response object decoded
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAllWithPagination(string $url): array
    {
        $options = [];
        if (!empty($this->token)) {
            $options = [
                'headers' => [
                    'Authorization' => 'token ' . $this->token
                ]
            ];
        }
        $url .= '&per_page=100';

        $results = [];
        $nextPageUrl = $url;
        while (!empty($nextPageUrl)) {
            try {
                $response = $this->client->request('GET', $nextPageUrl, $options);
            } catch (ClientException $e) {
                print("HTTP {$e->getCode()} thrown for \"GET $nextPageUrl\": {$e->getMessage()}");
                return [];
            }
            if ($response->getStatusCode() !== 200) {
                return [];
            }
            $results = array_merge($results, json_decode($response->getBody(), true));
            $nextPageUrl = $this->getNextPageUrl($response->getHeaders());
        }

        return $results;
    }

    /**
     * Parse next page URL from GitHub API response.
     *
     * @param array $responseHeaders Headers of GitHub API response
     * @return string Next page URL
     */
    protected function getNextPageUrl(array $responseHeaders): string
    {
        if (!isset($responseHeaders['Link'][0])) {
            return '';
        }

        $matches = [];
        preg_match('/<(https:\/\/api.github.com[^>]*)>; rel="next"/', $responseHeaders['Link'][0], $matches);
        return $matches[1] ?? '';
    }
}
