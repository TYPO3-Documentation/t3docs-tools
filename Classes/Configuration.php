<?php

namespace T3docs\T3docsTools;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

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
        $this->config = Yaml::parseFile($configFile);
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
        return $this->config['github']['repos']['ignore'] ?? [];
    }

}
