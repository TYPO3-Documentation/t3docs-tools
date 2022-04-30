<?php

namespace T3docs\T3docsTools;

use Symfony\Component\Yaml\Yaml;

class Configuration
{
    public const HOST_TYPE_GITHUB = 'github';
    public const HOST_TYPE_GITLAB = 'gitlab';

    public const CHECK_HOST_AND_USER = 0;
    public const CHECK_HOST_ONLY = 1;

    /** @var Configuration */
    public static $instance;

    /** @var array */
    protected $config;

    public function __construct()
    {
        list($scriptName) = get_included_files();
        $dirName = dirname($scriptName);

        if (is_file($dirName . '/config.local.yml')) {
            $config = array_merge_recursive(
                Yaml::parseFile($dirName . '/config.yml'),
                Yaml::parseFile($dirName . '/config.local.yml')
            );
        } else {
            $config = Yaml::parseFile($dirName . '/config.yml');
        }

        $this->config = $config;
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Configuration();
        }
        return self::$instance;
    }

    public function getFilteredHosts(string $host): array
    {
        $hosts = isset($this->config['hosts']) ?
            array_keys($this->config['hosts']) : [];

        if (empty($host) || $host === 'all') {
            return $hosts;
        } else {
            return array_intersect($hosts, explode(" ", $host));
        }
    }

    public function getSortedFilteredHosts(string $host): array
    {
        $hosts = $this->getFilteredHosts($host);
        natcasesort($hosts);
        return $hosts;
    }

    public function getTypeOfHost(string $host): string
    {
        return $this->config['hosts'][$host]['type'] ?? '';
    }

    public function getHttpUrlOfHost(string $host): string
    {
        return $this->config['hosts'][$host]['http_url'] ?? '';
    }

    public function getSshUrlOfHost(string $host): string
    {
        return $this->config['hosts'][$host]['ssh_url'] ?? '';
    }

    public function getApiUrlOfHost(string $host): string
    {
        return $this->config['hosts'][$host]['api_url'] ?? '';
    }

    public function getFilteredUsers(string $host, string $user, int $filterType = self::CHECK_HOST_AND_USER): array
    {
        $filteredUsers = [];
        $hosts = $this->getFilteredHosts($host);

        foreach ($hosts as $h) {
            $users = isset($this->config['hosts'][$h]['repos']) ?
                array_keys($this->config['hosts'][$h]['repos']) : [];

            if (empty($user) || $user === 'all') {
                $usersIdentifiers = array_map(function($u) use ($h) { return $this->composeUserIdentifier($h, $u); }, $users);
                $filteredUsers = array_merge($filteredUsers, $usersIdentifiers);
            } else {
                $filter = explode(" ", $user);
                $filter = array_filter($filter, function($u) use ($h) { return $this->isUserIdentifierOfHost($h, $u); });
                $filter = array_map(function($u) use ($h) { return $this->extractFromUserIdentifier($u, 'user'); }, $filter);
                if ($filterType === self::CHECK_HOST_AND_USER) {
                    $users = array_intersect($users, $filter);
                } elseif ($filterType === self::CHECK_HOST_ONLY) {
                    $users = $filter;
                }
                $usersIdentifiers = array_map(function($u) use ($h) { return $this->composeUserIdentifier($h, $u); }, $users);
                $filteredUsers = array_merge($filteredUsers, $usersIdentifiers);
            }
        }

        return $filteredUsers;
    }

    protected function isUserIdentifierOfHost(string $host, string $userIdentifier): bool
    {
        return !str_contains($userIdentifier, ':') || str_starts_with($userIdentifier, $host.':');
    }

    protected function extractFromUserIdentifier(string $userIdentifier, string $part): string
    {
        $parts = ['host' => '', 'user' => $userIdentifier];
        if (str_contains($userIdentifier, ':')) {
            list($parts['host'], $parts['user']) = explode(':', $userIdentifier, 2);
        }
        return $parts[$part];
    }

    protected function composeUserIdentifier(string $host, string $user): string
    {
        return $host . ':' . $user;
    }

    public function getSortedFilteredUsers(string $host, string $user, int $filterType = self::CHECK_HOST_AND_USER): array
    {
        $users = $this->getFilteredUsers($host, $user, $filterType);
        natcasesort($users);
        return $users;
    }

    public function getIncludedRepos(string $host, string $user): array
    {
        return $this->config['hosts'][$host]['repos'][$user]['include'] ?? [];
    }

    public function getIgnoredRepos(string $host, string $user): array
    {
        return $this->config['hosts'][$host]['repos'][$user]['ignore'] ?? [];
    }
}
