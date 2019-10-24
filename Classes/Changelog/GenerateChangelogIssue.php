<?php

namespace T3docs\T3docsTools\Changelog;

use Symfony\Component\DomCrawler\Crawler;

class GenerateChangelogIssue
{

    public function getBaseUrl(string $url) : string
    {
        $a = explode('/', $url);

        array_pop($a);
        return implode('/', $a);
    }

    public function getChangelog(string $url) : void
    {
        $baseUrl = $this->getBaseUrl($url);

        $ids = [
            'breaking' => [
                'id' => 'breaking-changes',
                'title' => 'Breaking'],
            'features' => [
                'id' => 'features',
                'title' => 'Features'
                ],
            'deprecation' => [
                'id' => 'deprecation',
                'title' => 'Deprecation'
                ],
            'important' => [
                'id' =>'important',
                'title' => 'Important'
                ]
            ];

        $html = file_get_contents($url);
        $crawler = new Crawler($html);


        foreach ($ids as $key => $values) {

            print($values['title'] . "\n");

            $filter = $crawler->filter('#' . $values['id'] . ' > div > ul > li > a');

            foreach ($filter as $domElement) {

                $title = $domElement->nodeValue;
                $link = $domElement->getAttribute('href');
                $url = $baseUrl . '/' . $link;

                print("* [ ] [$title]($url)\n");

            }
            print("\n");
        }

        /*
        foreach ($ids as $key => $id) {
            $filtered = $crawler
                ->filter('#breaking-changes > div > ul > li > a')
                ->reduce(function (Crawler $node, $i) {
                    // filters every other node
                    //print("var_dump nodeName");
                    //var_dump($node->nodeName);
                    print("class of node: " .get_class($node) . "\n");
                });
        }
        */
    }

}