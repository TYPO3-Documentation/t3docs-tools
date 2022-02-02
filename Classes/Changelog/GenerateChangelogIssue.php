<?php

namespace T3docs\T3docsTools\Changelog;

use Symfony\Component\DomCrawler\Crawler;
use T3docs\T3docsTools\GitHub\GitHubApi;

class GenerateChangelogIssue
{

    /** @var array  */
    protected $changes = [
        'Breaking' => [
            'id' => 'breaking-changes',
            'title' => 'Breaking',
            // array:
            //  'title' =>
            //  'url' =>
            //  'line' =>
            'changelogs' => [],
        ],
        'Features' => [
            'id' => 'features',
            'title' => 'Features',
            'changelogs' => []
        ],
        'Deprecation' => [
            'id' => 'deprecation',
            'title' => 'Deprecation',
            'changelogs' => []
        ],
        'Important' => [
            'id' => 'important',
            'title' => 'Important',
            'changelogs' => []
        ]
    ];

    /** @var array  */
    protected $changesInIssue = [
        'Breaking' => [
            'id' => 'breaking-changes',
            'title' => 'Breaking',
            // array:
            //  'title' =>
            //  'url' =>
            //  'line' =>
            'changelogs' => [],
        ],
        'Features' => [
            'id' => 'features',
            'title' => 'Features',
            'changelogs' => []
        ],
        'Deprecation' => [
            'id' => 'deprecation',
            'title' => 'Deprecation',
            'changelogs' => []
        ],
        'Important' => [
            'id' => 'important',
            'title' => 'Important',
            'changelogs' => []
        ]
    ];

    public function __construct()
    {
        $this->api = new GitHubApi();
    }

    public function getBaseUrl(string $url): string
    {
        $a = explode('/', $url);

        array_pop($a);
        return implode('/', $a);
    }

    /**
     * Get body of issue. This is only necessary, if an existing issue
     * should be appended.
     *
     * Put changes from issue in $this->changesInIssue
     *
     * @return string
     */
    public function getChangesFromIssue(int $id, string $repo='TYPO3CMS-Reference-CoreApi') : array
    {
        $url = "https://api.github.com/repos/TYPO3-Documentation/$repo/issues/$id";

        $results = $this->api->get($url);
        $body = $results['body'] ?? '';

        $lines = explode("\n", $body);
        $types = array_keys($this->changes);

        $type = '';
        foreach ($lines as $line) {
            foreach ($types as $someType) {
                if (strpos($line, "# $someType", 0) === 0) {
                    $type = $someType;
                    break;
                }
            }
            if ($type && $line && strpos($line, '* [', 0) ===0) {
                // get title
                $matches = [];
                $result = preg_match('#\* \[[x ]?\] \[([^\]]*)#', $line, $matches);
                if ($result != 1 || !($matches[1] ?? false)) {
                    print("ERROR: No match for pattern title ... in line $line\n");
                    exit(1);
                }
                $title = $matches[1];

                // get $url
                $matches = [];
                $result = preg_match('#\* \[[x ]?\] \[[^\]]*\]\((.*)\)#', $line, $matches);
                if ($result != 1 || !($matches[1] ?? false)) {
                    print("ERROR: No match for pattern url ... in line $line\n");
                    exit(1);
                }
                $url = $matches[1];

                $this->changesInIssue[$type]['changelogs'][$title] = [
                    'title' => $title,
                    'url' => $url,
                    'line' => $line
                ];
            }

        }
        return $this->changesInIssue;
    }

    /**
     * Gets changelogs from rendered changelog, that are not already
     * included in existing issues.
     *
     * Prerequisite:
     * 1. getChangesFromIssue() was called previously to read existing
     *    changelogs from issue
     *
     * @param string $url
     * @return array
     */
    public function getNewChangelogs(string $url): array
    {
        $baseUrl = $this->getBaseUrl($url);

        $html = file_get_contents($url);
        $crawler = new Crawler($html);

        foreach ($this->changes as $type => $values) {

            $filter = $crawler->filter('#' . $values['id'] . ' > div > ul > li > a');
            $this->changes[$type] = [
                  'title' => $type,
                  'changelogs' => []
            ];

            foreach ($filter as $domElement) {

                $title = $this->escapeTitleFromChangelog($domElement->nodeValue);

                $url = $domElement->getAttribute('href');
                $url = $baseUrl . '/' . $url;

                if (!($this->changesInIssue[$type]['changelogs'][$title] ?? false)) {
                    $line = "* [ ] [$title]($url)";
                    $this->changes[$type]['changelogs'][$title] = [
                        'title' => $title,
                        'url' => $url,
                        'line' => $line
                    ];
                }

            }

        }
        return $this->changes;

    }

    private function escapeTitleFromChangelog($title) {
        return str_replace(['[', ']'], '_', $title);
    }

    public function getChangelog(string $url): array
    {
        $baseUrl = $this->getBaseUrl($url);


        $html = file_get_contents($url);
        $crawler = new Crawler($html);


        foreach ($this->changes as $key => $values) {

            $filter = $crawler->filter('#' . $values['id'] . ' > div > ul > li > a');

            foreach ($filter as $domElement) {

                $title = $domElement->nodeValue;
                $link = $domElement->getAttribute('href');
                $url = $baseUrl . '/' . $link;

                $this->changes[$key]['changelogs'][$title] = [
                    'title' => $title,
                    'url' => $url,
                    'line' => "* [ ] [$title]($url)"
                ];

            }

        }
        return $this->changes;

    }

    public function printChangelogs()
    {
        foreach ($this->changes as $key => $values) {
            $title = $values['title'];
            print("# " . $title . "\n\n");
            //print("\n<<!-- Deprecation: begin -->>\n\n");

            foreach ($values['changelogs'] as $changelog) {
                $title = $changelog['title'];
                $url = $changelog['url'];
                print("* [ ] [$title]($url)\n\n");
            }
            //print("\n<<!-- Deprecation: end -->>\n\n");
            print("\n\n");
        }
    }

}
