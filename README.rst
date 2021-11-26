.. highlight:: shell

===========
t3doc-tools
===========

Suite of tools, mostly for bulk changes in the TYPO3 documentation repositories
and to output some statistics.

* part of this is written in PHP (focused on remote actions in GitHub)
* some of them are bash scripts (focused on local actions in the cloned repositories)

Installation
============

.. code-block:: bash

    git clone <url to repository>
    cd <repository folder>
    composer install

Configuration
=============

There are several repositories in https://github.com/TYPO3-Documentation.

Documentation repositories typically begin with "TYPO3CMS-".

The config.yml file is used to filter out some repositories that are not yet
archived but should not be maintained any longer.

Usage: PHP
==========

The PHP scripts are located in the project root folder.

get-repo-names.php
------------------

Show list of currently relevant docs repos::

    php get-repo-names.php [<type>]

type can be:

* 'docs' (default): all repositories that are documentation, i.e. the names begin with "TYPO3CMS-"
* 'all': all repositories

get-repo-branches.php
---------------------

Show all branches for these repos::

    php get-repo-branches.php [<type>]

type can be:

* 'docs' (default): all repositories that are documentation, i.e. the names begin with "TYPO3CMS-"
* 'all': all repositories

get-contributors.php
--------------------

Fetch the list of contributors of the repos::

    php get-contributors.php <year> [<GitHub token>]

The GitHub token is necessary in order to make several requests to GitHub to get
the commits of all repositories.

generate-changelog-issue.php
----------------------------

Create text for an issue including list of tasks to be checked off and link to original issue::

    php generate-changelog-issue.php <url to changelog or version> [<changelog issue in T3DocsTeam>]

Examples:

Create the text for a changelog issue for version 10.1::

    php generate-changelog-issue.php "https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.1/Index.html"

or::

    php generate-changelog-issue.php "10.1"

Show only the changelogs of the master branch that are not yet included in issue 121::

    php generate-changelog-issue.php "master" 121

manuals-json-show-count.php
---------------------------

Shows global statistics extracted from Intercept's manuals.json.
If no filename is specified, the file is fetched on-the-fly from the remote server::

    php -f manuals-json-show-count.php [<filename>]

Example::

    cd ~/Downloads
    wget https://intercept.typo3.com/assets/docs/manuals.json
    php -f manuals-json-show-count.php ~/Downloads/manuals.json

manuals-json-show-ext-info.php
------------------------------

Shows extension specific information extracted from Intercept's manuals.json.
If no filename is specified, the file is fetched on-the-fly from the remote server::

    php -f manuals-json-show-ext-info.php <extension key> [<filename>]

Example::

    wget -O /tmp/manuals.json https://intercept.typo3.com/assets/docs/manuals.json
    php -f manuals-json-show-ext-info.php rtehtmlarea /tmp/manuals.json

Usage: bash scripts
===================

The bash scripts are located in subfolder bashScripts/.

get-repos.sh
------------

Clones all TYPO3 documentation repositories from remote to local folder generated-data/repos/::

    ./bashScripts/get-repos.sh

grep-for-settings.sh
--------------------

This searches for a string in Documentation/Settings.cfg in all branches of all local repositories
and stops on first hit::

    ./bashScripts/grep-for-settings.sh <string>

Example::

    ./bashScripts/grep-for-settings.sh t3tssyntax

The repositories must already exist in generated-data/repos/. Call get-repos.sh first.
