<?php

namespace T3docs\T3docsTools\GitLab;

use DateTime;
use DateTimeImmutable;
use T3docs\T3docsTools\Configuration;

class GitLabRepository
{
    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var GitLabApi
     */
    protected $api;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var array
     */
    protected $repos;

    /**
     * @param string $host GitLab host identifier of YAML configuration
     * @param string $token GitLab API token
     */
    public function __construct(string $host, string $token = '')
    {
        $this->config = Configuration::getInstance();
        $this->api = new GitLabApi($this->config->getApiUrlOfHost($host), $token);
        $this->host = $host;
    }

    /**
     * @param string $user GitLab user namespace
     * @param string $type GitLab repository type, docs="TYPO3CMS-*", all="*"
     */
    public function fetchRepos(string $user = 'typo3-documentation', string $type = 'docs'): void
    {
        $this->getRepos($user);
        $this->filterRepos($user, $type);
    }

    /**
     * Retrieve all repositories of GitLab user namespace.
     *
     * Unfortunately GitLab distinguishes between user's and group's repositories in API,
     * so try both.
     *
     * @param string $user GitLab user namespace
     */
    protected function getRepos(string $user)
    {
        $repos = $this->api->get("users/$user/projects?per_page=100");
        if (empty($repos)) {
            $repos = $this->api->get("groups/$user/projects?per_page=100");
        }
        $this->repos = $repos;
    }

    /**
     * Filter out unwanted repos, e.g. ignored, archived, not-included, etc.
     *
     * @param string $user GitLab user namespace
     * @param string $type GitLab repository type, docs="TYPO3CMS-*", all="*"
     */
    protected function filterRepos(string $user, string $type): void
    {
        $repos = [];

        foreach ($this->repos as $repo) {
            if (empty($repo['path'])
                || $type === 'docs' && strpos($repo['path'], 'TYPO3CMS-') !== 0
                || ($repo['archived'] ?? false)
                || in_array($repo['path'], $this->config->getIgnoredRepos($this->host, $user))
                || !empty($this->config->getIncludedRepos($this->host, $user))
                    && !in_array($repo['path'], $this->config->getIncludedRepos($this->host, $user))
            ){
                continue;
            }
            $repos[$repo['path']] = $repo;
        }

        $this->repos = $repos;
    }

    /**
     * Load branch names of GitLab repository via GitLab API.
     *
     * @param string $user GitLab user namespace
     * @param string $repoName GitLab repository name
     * @return array Branch names of GitLab repository
     */
    public function fetchBranchNamesOfRepo(string $user, string $repoName): array
    {
        $branches = $this->api->get("projects/" . urlencode("$user/$repoName") . "/repository/branches");
        $branchNames = [];

        foreach ($branches as $branch) {
            $branchNames[] = $branch['name'];
        }

        return $branchNames;
    }

    /**
     * Load contributors of GitLab repositories via GitLab API.
     *
     * @param string $repoName GitLab repository name (default=all repositories)
     * @param int $year Consider commits of this year (default=current year)
     * @param int $month Consider commits of this month (default=all months)
     * @return array Contributors data
     */
    public function fetchContributors(string $repoName = '', int $year = 0, int $month = 0): array
    {
        $repos = empty($repoName) ? $this->getNames() : [$repoName];
        $contributors = [];

        foreach ($repos as $repo) {
            $commits = $this->getCommits($repo, $year, $month);

            foreach ($commits as $commit) {
                $id = $commit['author_email'] ?? $commit['author_name'];
                if (!isset($contributors[$id])) {
                    $contributors[$id] = [
                        'name' => $commit['author_name'],
                        'email' => $commit['author_email'],
                        'count' => 0
                    ];
                }
                $contributors[$id]['count']++;
            }
        }

        uasort($contributors, function ($a, $b) {
            return $a['count'] >= $b['count'] ? -1 : 1;
        });

        return $contributors;
    }

    /**
     * Get all repository names
     *
     * @return array
     */
    public function getNames(): array
    {
        $names = [];

        foreach ($this->repos as $repo) {
            $names[] = $repo['path'] ?? '';
        }

        return $names;
    }

    /**
     * Load all commits of GitLab repository via GitLab API.
     *
     * @param string $repoName GitLab repository name
     * @param int $year Consider commits of this year (0=current year)
     * @param int $month Consider commits of this month (0=all months)
     * @return array Commits data
     */
    protected function getCommits(string $repoName, int $year = 0, int $month = 0): array
    {
        $year = $year !== 0 ? $year : intval(date('Y'));
        if ($month !== 0) {
            $startDate = new DateTimeImmutable($year . '-' . $month . '-01 00:00');
            $endDate = new DateTimeImmutable($year . '-' . $month . '-31 23:59');
        } else {
            $startDate = new DateTimeImmutable($year . '-01-01 00:00');
            $endDate = new DateTimeImmutable($year . '-12-31 23:59');
        }
        $commitsUrl = "projects/" . $this->repos[$repoName]['id'] . "/repository/commits";
        $commitsUrl .= '?since=' . $startDate->format(DateTime::ATOM);
        $commitsUrl .= '&until=' . $endDate->format(DateTime::ATOM);
        return $this->api->getAllWithPagination($commitsUrl);
    }
}
