.. highlight:: shell

===========
t3doc-tools
===========

Suite of tools, mostly for bulk changes in the TYPO3 documentation repositories.


Installation
============

git clone <url to repository>
cd <dir>
composer install

Configuration
=============

There are several repositories in https://github.com/TYPO3-Documentation

Documentation repositories typically begin with "TYPO3CMS-"

config.yml is used to filter out some repositories that are not yet
archived but should not be maintained any longer.

Usage
=====

Show list of currently relevant docs repos::

    php get-repo-names.php

Create text for an issue including list of tasks to be checked off and link to original issue::

    php generate-changelog-issue.php <url to changelog>

For example::

    php generate-changelog-issue.php "https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.1/Index.html"

