.. highlight:: shell

===========
t3doc-tools
===========

Suite of tools, mostly for bulk changes in the TYPO3 documentation repositories
and to output some statistics.

* part of this is written in PHP
* some of the scripts are written in bash (for command line tool based functionality)


Installation
============

.. code-block:: bash

    git clone <url to repository>
    cd <dir>
    composer install

Configuration
=============

There are several repositories in https://github.com/TYPO3-Documentation

Documentation repositories typically begin with "TYPO3CMS-"

config.yml is used to filter out some repositories that are not yet
archived but should not be maintained any longer.

Usage: PHP
==========

get-repo-names
--------------

Show list of currently relevant docs repos::

    php get-repo-names.php [type]

type can be:

* 'docs' (default): all repositories that are documentation
* 'all': all repositories

get-repo-branches
-----------------

Show all branches for these repos::

    php get-repo-branches.php  [type]

type can be:

* 'docs' (default): all repositories that are documentation
* 'all': all repositories

get-contributors
----------------

    php get-contributors.php <year> [GitHub token]

* the token is necessary in order to make several requests to GitHub to get
  the commits for all repositories

generate-changelog-issue
------------------------

Create text for an issue including list of tasks to be checked off and link to original issue::

    php generate-changelog-issue.php <url to changelog>

For example::

    php generate-changelog-issue.php "https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.1/Index.html"

manuals-json-show-count
-----------------------

Show information from manuals.json from Intercept::

    wget -O /tmp/manuals.json https://intercept.typo3.com/assets/docs/manuals.json
    php -f manuals-json-show-count.php /tmp/manuals.json

Usage: bash scripts
===================

in bashScripts

get-repos.sh
------------

Get all repositories. Clones repositories in generated-data/repos

grepForSettings.sh
------------------

This searches for a string in Documentation/Settings.cfg in all branches in all repositories

    grepForSettings.sh t3tssyntax

The repositories must already exist in generated-data/repos/. Call get-repos.sh first.




