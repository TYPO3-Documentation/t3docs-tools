<?php

namespace T3docs\T3docsTools\GitHub;

use T3docs\T3docsTools\Configuration;
use T3docs\T3docsTools\GitHub\GitHubApi;

class GithubRepository
{
    /** @var Configuration */
    protected $config;

    /** @var array */
    protected $repos;

    /** @var GitHubApi  */
    protected $api;

    /**
     * @var string
     */
    protected $token;

    /**
     * GithubRepository constructor.
     * @param string $type 'docs' (default): all docs, 'all': all
     * @param string GitHub API token
     */
    public function __construct($type = 'docs', string $token = null)
    {
        $this->config = Configuration::getInstance();
        $this->api = new GitHubApi('https://api.github.com/', $token);
        $this->repos = $this->api->get($this->config->getRepositoriesUrl());
        $this->filterRepos($type);
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * @deprecated not used
     */
    protected function fetchBranchInfos()
    {

        foreach ($this->repos as $name => $repo) {
            if ($this->repos[$name]['branches'] ?? false) {
                continue;
            }

            $this->repos[$name]['branches'] = $this->getBranchInfosForRepoName($name);
        }

    }

    public function getBranchInfosForRepoName(string $reponame) : array
    {
        $branches = $this->api->get('https://api.github.com/repos/TYPO3-Documentation/' . $reponame . '/branches');
        $branchnames = [];

        foreach ($branches as $branch) {
            $branchnames[$branch['name']] = $branch['name'];

        }
        return $branchnames;
    }


    /**
     * Filter out unwanted repos, e.g. archived, ignored, etc.
     *
     * @param $type = 'docs' (default): all docs repos, 'all': all repos
     */
    protected function filterRepos($type = 'docs')
    {
        foreach($this->repos as $key => $repo) {
            $name = $repo['name'] ?? '';
            if ($type === 'docs'  && strpos($name, 'TYPO3CMS-') !== 0) {
                unset($this->repos[$key]);
                continue;
            }
            if ($repo['archived']
                || in_array($name, $this->config->getIgnoredRepos())
            ) {
                unset($this->repos[$key]);
                continue;
            }
            unset($this->repos[$key]);
            $this->repos[$name] = $repo;
        }
    }

    /**
     * Get all repository names
     *
     * @return array
     */
    public function getNames() : array
    {
        $names = [];
        foreach($this->repos as $repo) {
            $names[] = $repo['name'] ?? '';

        }
        return $names;
    }

    public function getRepos() : array
    {
        return $this->repos;
    }



    public function getCommitsUrl($repoName) : string
    {
        return $this->repos[$repoName]['commits_url'];
    }

    /**
     * We can use the commit API to get all commits for a repo:
     * https://api.github.com/repos/<user>/<repo>/commits
     *
     * !!!
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
     * @param string $repoName
     * @param int $year
     * @param int $month
     * @return string
     */
    public function getCommits(string $repoName, int $year, int $month=0) : array
    {
        if ($month) {
            $startDate = new \DateTimeImmutable($year . '-' . $month . '-01 00:00');
            $endDate = new \DateTimeImmutable($year . '-' . $month . '-31 23:59');
        } else {
            $startDate = new \DateTimeImmutable($year . '-01-01 00:00');
            $endDate = new \DateTimeImmutable($year . '-12-31 23:59');
        }
        $commitsUrl = $this->getCommitsUrl($repoName);
        $params = '?since=' . $startDate->format(\DateTime::ATOM);
        $params .= '&until=' . $endDate->format(\DateTime::ATOM);
        $commitsUrl = str_replace('{/sha}', $params, $commitsUrl);
        return $this->api->getAllWithPagination($commitsUrl);
    }

    /**
     * @param string $repoName (if empty, get from all repos)
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getContributors(int $year, int $month=0) : array
    {
        $names = $this->getNames();
        $contributors = [];

        foreach ($names as $name) {
            $commits = $this->getCommits($name, $year, $month);

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
        return $contributors;
    }


    public function remoteBranchExists(string $name, string $branch) : bool
    {
        //git ls-remote --heads git@github.com:user/repo.git branch-name
        //"branches_url": "https://api.github.com/repos/TYPO3-Documentation/DocsTypo3Org-Homepage/branches{/branch}",

        $branches = $this->getBranchInfosForRepoName($name);

        return (isset($branches[$branch]));


    }


}