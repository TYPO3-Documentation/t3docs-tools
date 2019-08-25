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

    public function __construct()
    {
        $this->config = Configuration::getInstance();
        $this->api = new GitHubApi();
        $this->repos = $this->api->get($this->config->getRepositoriesUrl());
        $this->filterRepos();
    }

    protected function fetchBranchInfos()
    {
        foreach ($this->repos as $name => $repo)
        {
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
     */
    protected function filterRepos()
    {
        foreach($this->repos as $key => $repo) {
            $name = $repo['name'] ?? '';
            if ((strpos($name, 'TYPO3CMS-') !== 0)
                || $repo['archived']
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

    public function remoteBranchExists(string $name, string $branch) : bool
    {
        //git ls-remote --heads git@github.com:user/repo.git branch-name
        //"branches_url": "https://api.github.com/repos/TYPO3-Documentation/DocsTypo3Org-Homepage/branches{/branch}",

        $branches = $this->getBranchInfosForRepoName($name);

        return (isset($branches[$branch]));


    }


}