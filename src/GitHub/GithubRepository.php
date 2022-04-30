<?php

namespace T3docs\T3docsTools\GitHub;

use DateTime;
use DateTimeImmutable;
use T3docs\T3docsTools\Configuration;

class GithubRepository
{
    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var GitHubApi
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
     * @param string $host GitHub host identifier of YAML configuration
     * @param string $token GitHub API token
     */
    public function __construct(string $host, string $token = '')
    {
        $this->config = Configuration::getInstance();
        $this->api = new GitHubApi($this->config->getApiUrlOfHost($host), $token);
        $this->host = $host;
    }

    /**
     * @param string $user GitHub user namespace
     * @param string $type GitHub repository type, docs="TYPO3CMS-*", all="*"
     */
    public function fetchRepos(string $user = 'typo3-documentation', string $type = 'docs'): void
    {
        $this->getRepos($user);
        $this->filterRepos($user, $type);
    }

    /**
     * Retrieve all repositories of GitHub user namespace.
     *
     * @param string $user GitHub user namespace
     */
    protected function getRepos(string $user)
    {
        $this->repos = $this->api->get("users/$user/repos?per_page=100");
    }

    /**
     * Filter out unwanted repos, e.g. ignored, archived, not-included, etc.
     *
     * @param string $user GitHub user namespace
     * @param string $type GitHub repository type, docs="TYPO3CMS-*", all="*"
     */
    protected function filterRepos(string $user, string $type): void
    {
        $repos = [];

        foreach ($this->repos as $repo) {
            if (empty($repo['name'])
                || $type === 'docs' && strpos($repo['name'], 'TYPO3CMS-') !== 0
                || $repo['archived']
                || in_array($repo['name'], $this->config->getIgnoredRepos($this->host, $user))
                || !empty($this->config->getIncludedRepos($this->host, $user))
                    && !in_array($repo['name'], $this->config->getIncludedRepos($this->host, $user))
            ){
                continue;
            }
            $repos[$repo['name']] = $repo;
        }

        $this->repos = $repos;
    }

    /**
     * Load branch names of GitHub repository via GitHub API.
     *
     * @param string $user GitHub user namespace
     * @param string $repoName GitHub repository name
     * @return array Branch names of GitHub repository
     */
    public function fetchBranchNamesOfRepo(string $user, string $repoName): array
    {
        $branches = $this->api->get("repos/$user/$repoName/branches");
        $branchNames = [];

        foreach ($branches as $branch) {
            $branchNames[] = $branch['name'];
        }

        return $branchNames;
    }

    public function fetchIssue(string $user, string $repoName, int $issueId): array
    {
        $issue = $this->api->get("repos/$user/$repoName/issues/$issueId");
        return $issue;
    }

    /**
     * Load contributors of GitHub repositories via GitHub API.
     *
     * @param string $repoName GitHub repository name (default=all repositories)
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
                $id = $commit['author']['id'];
                if (!isset($contributors[$id])) {
                    $contributors[$id] = [
                        'name' => $commit['commit']['author']['name'],
                        'email' => $commit['commit']['author']['email'],
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
            $names[] = $repo['name'] ?? '';
        }

        return $names;
    }

    /**
     * Load all commits of GitHub repository via GitHub API.
     *
     * - by default, this returns only 30 commits
     *   you can change the number of commits with per_page= (max 100)
     * - you can get further pages with page=
     *
     * You MUST use the URLs supplied in the HTTP header link,
     * example :
     * Link: <https://api.github.com/repositories/54468502/commits?since=2019-01-01T00%3A00%3A00+01%3A00&until=2019-12-31T23%3A59%3A00+01%3A00&page=2>; rel="next", <https://api.github.com/repositories/54468502/commits?since=2019-01-01T00%3A00%3A00+01%3A00&until=2019-12-31T23%3A59%3A00+01%3A00&page=7>; rel="last"
     *
     * see https://developer.github.com/v3/#pagination
     * https://developer.github.com/v3/guides/traversing-with-pagination/
     *
     * @param string $repoName GitHub repository name
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
        $commitsUrl = $this->repos[$repoName]['commits_url'];
        $params = '?since=' . $startDate->format(DateTime::ATOM);
        $params .= '&until=' . $endDate->format(DateTime::ATOM);
        $commitsUrl = str_replace('{/sha}', $params, $commitsUrl);
        return $this->api->getAllWithPagination($commitsUrl);
    }
}
