.. highlight:: shell

============
t3docs-tools
============

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
* https://github.com/TYPO3 and
* https://github.com/FriendsOfTYPO3

which are the home of the TYPO3 Documentation Team, the TYPO3 Core Team and the
Friends of TYPO3 respectively.

The names of the documentation manual repositories usually start with "TYPO3CMS-".
These can be processed specifically.

The config.yml file is used to filter out some repositories that are not yet
archived but should not be maintained any longer. The official, version controlled
configuration can be overridden with a custom, non version controlled
config.local.yml file.

The bash/config.sh file configures the local folder of the cloned repositories,
which is generated-data/repos/ by default. The settings can be overridden with a custom
bash/config.local.sh file.

The local repositories of each GitHub user namespace (officially "friendsoftypo3",
"typo3" and "typo3-documentation") are cloned into local subfolders following
the pattern generated-data/repos/<user>, i.e. currently into

* generated-data/repos/typo3-documentation/ and
* generated-data/repos/typo3/ and
* generated-data/repos/friendsoftypo3/,

– for separate and general processing. Additional user namespaces can be defined
in the config.yml and config.local.yml already mentioned.

Usage: PHP
==========

The PHP scripts are located in the project root folder.

get-repo-names.php
------------------

List the remote repos::

    php get-repo-names.php [<type>] [<user>] [<token>]

    Arguments:
       type: Consider all repositories or only those starting with "TYPO3CMS-" (all, docs). [default: "docs"]
       user: Consider the repositories of this GitHub user namespace (friendsoftypo3, typo3, typo3-documentation, ...), which has to be defined in the /config.yml or /config.local.yml. [default: "typo3-documentation"]
       token: Fetch the repositories using this GitHub API token to overcome GitHub rate limitations. [default: ""]

Example::

    php get-repo-names.php docs typo3-documentation

get-repo-branches.php
---------------------

List the branches of the remote repos::

    php get-repo-branches.php [<type>] [<user>] [<token>]

    Arguments:
       type: Consider all repositories or only those starting with "TYPO3CMS-" (all, docs). [default: "docs"]
       user: Consider the repositories of this GitHub user namespace (friendsoftypo3, typo3, typo3-documentation, ...), which has to be defined in the /config.yml or /config.local.yml. [default: "typo3-documentation"]
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
       user: Consider the repositories of this GitHub user namespace (friendsoftypo3, typo3, typo3-documentation, ...), which has to be defined in the /config.yml or /config.local.yml. [default: "typo3-documentation"]
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

The bash scripts are located in subfolder bash/.

collect-stats.sh
----------------

Collect statistics on all branches of all local repositories. Currently supported is the display of the number of
automatically generated screenshots::

    ./bash/collect-stats.sh [<type>] [<user>]

    Arguments:
       type: Collect the statistics of all repositories or only of those starting with "TYPO3CMS-" (all, docs). [default: "docs"]
       user: Collect the statistics in the local repositories of this GitHub user namespace (all, friendsoftypo3, typo3, typo3-documentation, ..). Multiple users must be separated by space, e.g. "friendsoftypo3 typo3".  [default: "typo3-documentation"]

Example::

    ./bash/collect-stats.sh all typo3

The repositories must already exist in generated-data/repos/. Call get-repos.sh to clone or update first.

exec-repos.sh
-------------

Execute a custom command in all branches of all local repositories::

    ./bash/exec-repos.sh <command> [<user>]

    Arguments:
       command: Execute this command in all branches of all local repositories. This parameter can also be the absolute file path of a bash script.
       user: Execute the search command in the local repositories of this GitHub user namespace (all, friendsoftypo3, typo3, typo3-documentation, ..). Multiple users must be separated by space, e.g. "friendsoftypo3 typo3". [default: "typo3-documentation"]

Example - Command as string::

    ./bash/exec-repos.sh "grep -rnIE '\`https://typo3\.org' --exclude-dir='.git' ." all

Example - Command in file::

    cp command/replace-and-push.sh.tmpl command/my-command.sh
    # adapt command/my-command.sh to your use case
    ./bash/exec-repos.sh "$(pwd)/command/my-command.sh" typo3-documentation

The command file should be placed in the `command/` folder, where backups of meaningful production runs with file
extension `.sh.tmpl` will be provided as templates and all custom command files with `.sh` are ignored by version
control.

The repositories must already exist in generated-data/repos/. Call get-repos.sh to clone or update first.

get-repos.sh
------------

Clones all TYPO3 documentation repositories (all) or only those starting with \"TYPO3CMS-\" (docs)
from remote to local folder generated-data/repos/::

    ./bash/get-repos.sh [<type>] [<user>] [<token>]

    Arguments:
       type: Fetch all repositories or only those starting with "TYPO3CMS-" (all, docs). [default: "all"]
       user: Fetch the repositories of this GitHub user namespace (all, friendsoftypo3, typo3, typo3-documentation, ..), which has to be defined in the /config.yml or /config.local.yml. Multiple users must be separated by space, e.g. "friendsoftypo3 typo3". [default: "typo3-documentation"]
       token: Fetch the repositories using this GitHub API token to overcome GitHub rate limitations. [default: ""]

Example::

    ./bash/get-repos.sh docs typo3-documentation

grep-for-settings.sh
--------------------

This searches for a string in Documentation/Settings.cfg in all branches of those local repositories
starting with \"TYPO3CMS-\" and stops on first hit::

    ./bash/grep-for-settings.sh <argument> [<user>]

    Arguments:
       argument: Search for this string in the Documentation/Settings.cfg files of the local repositories.
       user: Search in the local repositories of this GitHub user namespace (all, friendsoftypo3, typo3, typo3-documentation, ..). Multiple users must be separated by space, e.g. "friendsoftypo3 typo3". [default: "typo3-documentation"]

Example::

    ./bash/grep-for-settings.sh t3tssyntax typo3-documentation

The repositories must already exist in generated-data/repos/. Call get-repos.sh to clone or update first.

search-repos.sh
---------------

This command has been replaced with exec-repos.sh.

versionbranch-exist.sh
----------------------

Lists all local repositories for which a specific version branch exists::

    ./bash/versionbranch-exist.sh <version> [<user>]

    Arguments:
       version: List all local repositories having a branch matching this version.
       user: List local repositories of this GitHub user namespace (all, friendsoftypo3, typo3, typo3-documentation, ..). Multiple users must be separated by space, e.g. "friendsoftypo3 typo3". [default: "typo3-documentation"]

Example::

    ./bash/versionbranch-exist.sh "7.6" typo3

The repositories must already exist in generated-data/repos/. Call get-repos.sh to clone or update first.

versionbranch-not-exist.sh
--------------------------

Lists all local repositories for which a specific version branch does not exist::

    ./bash/versionbranch-not-exist.sh <version> [<user>]

    Arguments:
       version: List all local repositories not having a branch matching this version.
       user: List local repositories of this GitHub user namespace (all, friendsoftypo3, typo3, typo3-documentation, ..). Multiple users must be separated by space, e.g. "friendsoftypo3 typo3". [default: "typo3-documentation"]

Example::

    ./bash/versionbranch-not-exist.sh "11.5" typo3-documentation

The repositories must already exist in generated-data/repos/. Call get-repos.sh to clone or update first.
