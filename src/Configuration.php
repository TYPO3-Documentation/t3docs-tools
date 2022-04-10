<?php

namespace T3docs\T3docsTools;

use Symfony\Component\Yaml\Yaml;

class Configuration
{
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

    public function getIncludedRepos(string $user): array
    {
        return $this->config['github']['repos'][$user]['include'] ?? [];
    }

    public function getIgnoredRepos(string $user): array
    {
        return $this->config['github']['repos'][$user]['ignore'] ?? [];
    }
}
