.. highlight:: shell

===========
t3doc-tools
===========

Suite of tools, mostly for bulk changes in the repositories of the TYPO3 Documentation
Team and the TYPO3 Core Team, and to output some statistics, where

* part of this is written in PHP (focused on remote actions in GitHub) and
* some of them are bash scripts (focused on local actions in the cloned repositories).

Installation
============

.. code-block:: bash

    git clone <url to repository>
    cd <repository folder>
    composer install

Configuration
=============

There are several repositories in

* https://github.com/TYPO3-Documentation and
* https://github.com/TYPO3

which are the home of the TYPO3 Documentation Team and the TYPO3 Core Team respectively.

The names of the documentation manual repositories usually start with "TYPO3CMS-".
These can be processed specifically.

The config.yml file is used to filter out some repositories that are not yet
archived but should not be maintained any longer.

The bashScripts/config.sh file configures the local folder of the cloned repositories,
which is generated-data/repos/ by default. The settings can be overridden with a custom
bashScripts/config.local.sh file.

The local repositories of each GitHub user namespace (currently "typo3-documentation" and "typo3")
are cloned into local subfolders following the pattern generated-data/repos/<user>,
i.e. currently into

* generated-data/repos/typo3-documentation/ and
* generated-data/repos/typo3/,

– for separate and general processing.

Usage: PHP
==========

The PHP scripts are located in the project root folder.

get-repo-names.php
------------------

List the remote repos::

    php get-repo-names.php [<type>] [<user>] [<token>]

    Arguments:
       type: Consider all repositories or only those starting with "TYPO3CMS-" (all, docs). [default: "docs"]
       user: Consider the repositories of this GitHub user namespace (typo3-documentation, typo3), which has to be defined in the /config.yml. [default: "typo3-documentation"]
       token: Fetch the repositories using this GitHub API token to overcome GitHub rate limitations. [default: ""]

Example::

    php get-repo-names.php docs typo3-documentation

get-repo-branches.php
---------------------

List the branches of the remote repos::

    php get-repo-branches.php [<type>] [<user>] [<token>]

    Arguments:
       type: Consider all repositories or only those starting with "TYPO3CMS-" (all, docs). [default: "docs"]
       user: Consider the repositories of this GitHub user namespace (typo3-documentation, typo3), which has to be defined in the /config.yml. [default: "typo3-documentation"]
       token: Fetch the repositories using this GitHub API token to overcome GitHub rate limitations. [default: ""]

Example::

    php get-repo-branches.php all typo3

get-contributors.php
--------------------

List the contributors of the remote repos or a specific repo::

    php get-contributors.php [<year>] [<month>] [<type>] [<user>] [<repo>] [<token>]

    Arguments:
       year: Consider commits of this year, "0" means the current year. [default: "0"]
       month: Consider commits of this month, "0" means all months. [default: "0"]
       type: Consider all repositories or only those starting with "TYPO3CMS-" (all, docs). [default: "docs"]
       user: Consider the repositories of this GitHub user namespace (typo3-documentation, typo3), which has to be defined in the /config.yml. [default: "typo3-documentation"]
       repo: Consider commits of this specific repository, "" means of all repositories. [default: ""]
       token: Fetch the repositories using this GitHub API token to overcome GitHub rate limitations. [default: ""]

Example::

    php get-contributors.php 2021 8 all typo3-documentation t3docs-screenshots

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

collect-stats.sh
----------------

Collect statistics on all branches of all local repositories. Currently supported is the display of the number of
automatically generated screenshots::

    ./bashScripts/collect-stats.sh [<type>] [<user>]

    Arguments:
       type: Collect the statistics of all repositories or only of those starting with "TYPO3CMS-" (all, docs). [default: "docs"]
       user: Collect the statistics in the local repositories of this GitHub user namespace (all, typo3-documentation, typo3). [default: "typo3-documentation"]

Example::

    ./bashScripts/collect-stats.sh all typo3

The repositories must already exist in generated-data/repos/. Call get-repos.sh to clone or update first.

get-repos.sh
------------

Clones all TYPO3 documentation repositories (all) or only those starting with \"TYPO3CMS-\" (docs)
from remote to local folder generated-data/repos/::

    ./bashScripts/get-repos.sh [<type>] [<user>] [<token>]

    Arguments:
       type: Fetch all repositories or only those starting with "TYPO3CMS-" (all, docs). [default: "all"]
       user: Fetch the repositories of this GitHub user namespace (all, typo3-documentation, typo3), which has to be defined in the /config.yml. [default: "typo3-documentation"]
       token: Fetch the repositories using this GitHub API token to overcome GitHub rate limitations. [default: ""]

Example::

    ./bashScripts/get-repos.sh docs typo3-documentation

grep-for-settings.sh
--------------------

This searches for a string in Documentation/Settings.cfg in all branches of those local repositories
starting with \"TYPO3CMS-\" and stops on first hit::

    ./bashScripts/grep-for-settings.sh <argument> [<user>]

    Arguments:
       argument: Search for this string in the Documentation/Settings.cfg files of the local repositories.
       user: Search in the local repositories of this GitHub user namespace (all, typo3-documentation, typo3). [default: "typo3-documentation"]

Example::

    ./bashScripts/grep-for-settings.sh t3tssyntax typo3-documentation

The repositories must already exist in generated-data/repos/. Call get-repos.sh to clone or update first.

search-repos.sh
---------------

Execute a custom search command in all branches of all local repositories::

    ./bashScripts/search-repos.sh <command> [<user>]

    Arguments:
       command: Execute this search command in all branches of all local repositories.
       user: Execute the search command in the local repositories of this GitHub user namespace (all, typo3-documentation, typo3). [default: "typo3-documentation"]

Example::

    ./bashScripts/search-repos.sh "grep -rnIE '\`https://typo3\.org' --exclude-dir='.git' ." all

The repositories must already exist in generated-data/repos/. Call get-repos.sh to clone or update first.

versionbranch-exist.sh
----------------------

Lists all local repositories for which a specific version branch exists::

    ./bashScripts/versionbranch-exist.sh <version> [<user>]

    Arguments:
       version: List all local repositories having a branch matching this version.
       user: List local repositories of this GitHub user namespace (all, typo3-documentation, typo3). [default: "typo3-documentation"]

Example::

    ./bashScripts/versionbranch-exist.sh "7.6" typo3

The repositories must already exist in generated-data/repos/. Call get-repos.sh to clone or update first.

versionbranch-not-exist.sh
--------------------------

Lists all local repositories for which a specific version branch does not exist::

    ./bashScripts/versionbranch-not-exist.sh <version> [<user>]

    Arguments:
       version: List all local repositories not having a branch matching this version.
       user: List local repositories of this GitHub user namespace (all, typo3-documentation, typo3). [default: "typo3-documentation"]

Example::

    ./bashScripts/versionbranch-not-exist.sh "11.5" typo3-documentation

The repositories must already exist in generated-data/repos/. Call get-repos.sh to clone or update first.
