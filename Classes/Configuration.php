<?php

namespace T3docs\T3docsTools;

class Configuration
{
    /** @var Configuration */
    public static $instance;

    /** @var array */
    protected $config;

    public function __construct(string $configFile = null)
    {
        if (!$configFile) {
            list($scriptName) = get_included_files();
            $dirName = dirname($scriptName);
            $configFile = $dirName . '/config.yml';
        }
        $content = file_get_contents($configFile);
        $this->config = yaml_parse($content);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Configuration();
        }
        return self::$instance;
    }

    public function getConfiguration() : array
    {
        return $this->config;
    }

    public function getRepositoriesUrl()
    {
        return $this->config['github']['cmd']['listRepos'];
    }

    public function getIgnoredRepos() : array
    {
        $config = $this->config;
        return array_merge(
            $config['github']['repos']['ignore'],
            $config['github']['repos']['merged'],
            $config['github']['repos']['nobranch']
        );
    }

}
