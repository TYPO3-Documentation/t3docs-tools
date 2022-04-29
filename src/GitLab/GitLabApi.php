<?php

namespace T3docs\T3docsTools\GitLab;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class GitLabApi
{
    /**
     * @var Client Guzzle HTTP client
     */
    protected $client;

    /**
     * @var string GitLab access token
     */
    protected $token;

    /**
     * @param string $baseUrl GitHub API base URL
     * @param string $token GitLab access token
     */
    public function __construct(string $baseUrl, string $token = '')
    {
        $this->client = new Client(['base_uri' => $baseUrl]);
        if (!empty($token)) {
            $this->token = $token;
        }
    }

    /**
     * Send GET HTTP request to GitLab API.
     *
     * @param string $url GitLab URL or path
     * @return array GitLab response object decoded
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $url): array
    {
        $options = [];
        if (!empty($this->token)) {
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token
                ]
            ];
        }

        try {
            $response = $this->client->request('GET', $url, $options);
        } catch (ClientException $e) {
            error_log("HTTP {$e->getCode()} thrown for \"GET $url\": {$e->getMessage()}");
            return [];
        }
        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * Send GET HTTP request to GitLab API with support of pagination.
     *
     * See: https://docs.gitlab.com/ee/api/#pagination
     *
     * @param string $url GitLab URL or path
     * @return array GitLab response object decoded
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAllWithPagination(string $url): array
    {
        $options = [];
        if (!empty($this->token)) {
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token
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
                error_log("HTTP {$e->getCode()} thrown for \"GET $nextPageUrl\": {$e->getMessage()}");
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
     * Parse next page URL from GitLab API response.
     *
     * @param array $responseHeaders Headers of GitLab API response
     * @return string Next page URL
     */
    protected function getNextPageUrl(array $responseHeaders): string
    {
        if (!isset($responseHeaders['Link'][0])) {
            return '';
        }

        $matches = [];
        preg_match('/<([^>]*)>; rel="next"/', $responseHeaders['Link'][0], $matches);
        return $matches[1] ?? '';
    }
}
