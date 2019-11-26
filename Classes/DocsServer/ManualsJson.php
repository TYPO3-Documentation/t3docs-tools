<?php

namespace T3docs\T3docsTools\DocsServer;

class ManualsJson
{
    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $count;

    public function __construct(string $fileName = null)
    {

    }

    public function readFile(string $fileName = null) : bool
    {
        if ($fileName === null) {
            $fileName = 'manuals.json';
        }
        $this->fileName = $fileName;


        $str = file_get_contents($this->fileName);
        if (!$str) {
            return false;
        }
        $this->data = json_decode($str, true);
        return true;
    }

    public function getCount() : array
    {
        if (!$this->count) {
            $this->extractInformation();
        }
        return $this->count;
    }

    public function printCount()
    {
        $count = $this->getCount();
        var_dump($count);
    }

    public function isOldUrl(string $url): bool
    {
        $startsWith = 'https://docs.typo3.org/typo3cms/extensions/';

        return (substr($url, 0, strlen($startsWith)) === $startsWith);
    }

    public function isNewUrl(string $url): bool
    {
        $startsWith = 'https://docs.typo3.org/p/';

        return (substr($url, 0, strlen($startsWith)) === $startsWith);
    }


    protected function extractInformation()
    {
        $this->count = [
            'extensions' => 0,
            'hasOldUrl' => 0,
            'hasNewUrl' => 0,
            'hasBoth' => 0,
            'errors' => 0,
        ];

        foreach ($this->data as $extkey => $values) {
            if (!($values['docs'] ?? false)) {
                $this->count['errors']++;
                continue;
            }
            $hasOldUrl = false;
            $hasNewUrl = false;
            foreach ($values['docs'] as $version => $urls) {
                if (!($urls['url'] ?? false)) {
                    $this->count['errors']++;
                    continue;
                }
                $url = $urls['url'];
                if (!$hasOldUrl && $this->isOldUrl($url)) {
                    $hasOldUrl = true;
                } elseif (!$hasNewUrl && $this->isNewUrl($url)) {
                    $hasNewUrl = true;
                    print("has new url:extkey=$extkey version=$version url=$url\n");
                }


            }
            if ($hasOldUrl) {
                $this->count['hasOldUrl']++;
            }
            if ($hasNewUrl) {
                $this->count['hasNewUrl']++;
            }
            if ($hasOldUrl && $hasNewUrl) {
                $this->count['hasBoth']++;
            }
            $this->count['extensions']++;
        }

    }


}