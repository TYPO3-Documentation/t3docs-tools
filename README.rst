.. highlight:: shell

============
t3docs-tools
============

Suite of tools, mostly for bulk changes in the repositories of the TYPO3 Documentation
Team and the TYPO3 Core Team, and to output some statistics, where

* part of this is written in PHP (with focus on remote actions in the VCS host) and
* some of them are bash scripts (focused on local actions in the cloned repositories).

Installation
============

.. code-block:: bash

    git clone git@github.com:TYPO3-Documentation/t3docs-tools.git
    cd t3docs-tools
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

The `config.yml` file is used to filter out some repositories that are not yet
archived but should not be maintained any longer. The official, version controlled
configuration can be overridden with a custom, non version controlled
`config.local.yml` file.

The `bash/config.sh` file configures the local folder of the cloned repositories,
which is generated-data/ by default. The settings can be overridden with a custom
`bash/config.local.sh` file.

The local repositories of each user namespace (officially "friendsoftypo3", "typo3"
and "typo3-documentation" of host "github.com") are cloned into local subfolders
following the pattern generated-data/<host>/<user>, i.e. currently into

* generated-data/github.com/typo3-documentation/ and
* generated-data/github.com/typo3/ and
* generated-data/github.com/friendsoftypo3/,

– for separate and general processing. Additional hosts and user namespaces can be
defined in the config.yml and config.local.yml already mentioned.

Usage: PHP
==========

The PHP scripts are located in the project root folder.

get-repo-names.php
------------------

List the remote repos::

    php get-repo-names.php [<type>] [<host>] [<user>] [<token>] [<force>]

    Arguments:
       type: Consider all repositories or only those starting with "TYPO3CMS-" (all, docs). [default: "docs"]
       host: Consider the repositories of this host (all, github.com, ..), which has to be defined in the /config.yml or /config.local.yml. [default: "github.com"]
       user: Consider the repositories of this user namespace (all, github.com:friendsoftypo3, github.com:typo3, github.com:typo3-documentation, ..), which has to be defined in the /config.yml or /config.local.yml. [default: "github.com:typo3-documentation"]
       token: Fetch the repositories using this GitHub / GitLab API token to overcome rate limitations. [default: ""]
       force: Allow user namespaces not configured in the /config.yml or /config.local.yml. Requires a specific user namespace, not the generic "all". [default: 0]

Example::

    php get-repo-names.php docs all github.com:typo3-documentation

get-repo-branches.php
---------------------

List the branches of the remote repos::

    php get-repo-branches.php [<type>] [<host>] [<user>] [<token>] [<force>]

    Arguments:
       type: Consider all repositories or only those starting with "TYPO3CMS-" (all, docs). [default: "docs"]
       host: Consider the repositories of this host (all, github.com, ..), which has to be defined in the /config.yml or /config.local.yml. [default: "github.com"]
       user: Consider the repositories of this user namespace (all, github.com:friendsoftypo3, github.com:typo3, github.com:typo3-documentation, ..), which has to be defined in the /config.yml or /config.local.yml. [default: "github.com:typo3-documentation"]
       token: Fetch the repositories using this GitHub / GitLab API token to overcome rate limitations. [default: ""]
       force: Allow user namespaces not configured in the /config.yml or /config.local.yml. Requires a specific user namespace, not the generic "all". [default: 0]

Example::

    php get-repo-branches.php all all github.com:typo3

get-contributors.php
--------------------

List the contributors of the remote repos or a specific repo::

    php get-contributors.php [<year>] [<month>] [<type>] [<host>] [<user>] [<repo>] [<token>] [<force>]

    Arguments:
       year: Consider commits of this year, "0" means the current year. [default: "0"]
       month: Consider commits of this month, "0" means all months. [default: "0"]
       type: Consider all repositories or only those starting with "TYPO3CMS-" (all, docs). [default: "docs"]
       host: Consider the repositories of this host (all, github.com, ..), which has to be defined in the /config.yml or /config.local.yml. [default: "github.com"]
       user: Consider the repositories of this user namespace (all, github.com:friendsoftypo3, github.com:typo3, github.com:typo3-documentation, ..), which has to be defined in the /config.yml or /config.local.yml. [default: "github.com:typo3-documentation"]
       repo: Consider commits of this specific repository, "" means of all repositories. [default: ""]
       token: Fetch the repositories using this GitHub / GitLab API token to overcome rate limitations. [default: ""]
       force: Allow user namespaces not configured in the /config.yml or /config.local.yml. Requires a specific user namespace, not the generic "all". [default: 0]

Example::

    php get-contributors.php 2021 8 all github.com typo3-documentation t3docs-screenshots

generate-changelog-issue.php
----------------------------

Create text for an issue including list of tasks to be checked off and link to original issue::

    php generate-changelog-issue.php <url> [<issue>] [<token>]

    Arguments:
       url: Absolute changelog URL or TYPO3 version. For example "https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/11.5/Index.html" or "11.5".
       issue: ID of existing issue. If empty, all issues of changelog URL are printed. [default: ""]
       token: Fetch the changelog issues using this GitHub API token to overcome rate limitations. [default: ""]

Examples:

Create the text for a changelog issue for version 10.1::

    php generate-changelog-issue.php "https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/10.1/Index.html"

or::

        php generate-changelog-issue.php "10.1"

Show only the changelogs of the 12.0 branch that are not yet included in issue 121::

    php generate-changelog-issue.php "12.0" 121

manuals-json-show-count.php
---------------------------

Shows global statistics extracted from Intercept's manuals.json.
If no filename is specified, the file is fetched on-the-fly from the remote server::

    php -f manuals-json-show-count.php [<filename>]

Example::

    curl "https://intercept.typo3.com/assets/docs/manuals.json" > ~/Downloads/manuals.json
    php -f manuals-json-show-count.php ~/Downloads/manuals.json

manuals-json-show-ext-info.php
------------------------------

Shows extension specific information extracted from Intercept's manuals.json.
If no filename is specified, the file is fetched on-the-fly from the remote server::

    php -f manuals-json-show-ext-info.php <extension key> [<filename>]

Example::

    curl "https://intercept.typo3.com/assets/docs/manuals.json" > ~/Downloads/manuals.json
    php -f manuals-json-show-ext-info.php rtehtmlarea ~/Downloads/manuals.json

Usage: bash scripts
===================

The bash scripts are located in subfolder bash/.

collect-stats.sh
----------------

Collect statistics on all branches of all local repositories. Currently supported is the display of the number of
automatically generated screenshots::

    ./bash/collect-stats.sh [<type>] [<host>] [<user>]

    Arguments:
       type: Collect the statistics of all repositories or only of those starting with "TYPO3CMS-" (all, docs). [default: "all"]
       host: Collect the statistics in the local repositories of this host (all, github.com, ..). Multiple hosts must be separated by space, e.g. "github.com gitlab.com". [default: "all"]
       user: Collect the statistics in the local repositories of this user namespace (all, github.com:friendsoftypo3, github.com:typo3, github.com:typo3-documentation, ..). Multiple users must be separated by space, e.g. "github.com:friendsoftypo3 github.com:typo3". [default: "all"]

Example::

    ./bash/collect-stats.sh all github.com typo3

The repositories must already exist in generated-data/. Call get-repos.sh to clone or update first.

exec-repos.sh
-------------

Execute a custom command in all branches of all local repositories::

    ./bash/exec-repos.sh <command> [<host>] [<user>]

    Arguments:
       command: Execute this command in all branches of all local repositories. This parameter can also be the absolute file path of a bash script.
       host: Execute the command in the local repositories of this host (all, github.com, ..). Multiple hosts must be separated by space, e.g. "github.com gitlab.com". [default: "all"]
       user: Execute the command in the local repositories of this user namespace (all, github.com:friendsoftypo3, github.com:typo3, github.com:typo3-documentation, ..). Multiple users must be separated by space, e.g. "github.com:friendsoftypo3 github.com:typo3". [default: "all"]

Example - Command as string::

    ./bash/exec-repos.sh "grep -rnIE '\`https://typo3\.org' --exclude-dir='.git' ." all all

Example - Command in file::

    cp command/replace-and-push.sh.tmpl command/my-command.sh
    # adapt command/my-command.sh to your use case
    ./bash/exec-repos.sh "$(pwd)/command/my-command.sh" github.com typo3-documentation

The command file should be placed in the `command/` folder, where backups of meaningful production runs with file
extension `.sh.tmpl` will be provided as templates and all custom command files with `.sh` are ignored by version
control.

The repositories must already exist in generated-data/. Call get-repos.sh to clone or update first.

get-repos.sh
------------

Clones all TYPO3 documentation repositories (all) or only those starting with \"TYPO3CMS-\" (docs)
from remote to local folder generated-data/::

    ./bash/get-repos.sh [<type>] [<host>] [<user>] [<token>]

    Arguments:
       type: Fetch all repositories or only those starting with "TYPO3CMS-" (all, docs). [default: "all"]
       host: Fetch the repositories of this host (all, github.com, ..), which has to be defined in the /config.yml or /config.local.yml. Multiple hosts must be separated by space, e.g. "github.com gitlab.com". [default: "all"]
       user: Fetch the repositories of this user namespace (all, github.com:friendsoftypo3, github.com:typo3, github.com:typo3-documentation, ..), which has to be defined in the /config.yml or /config.local.yml. Multiple users must be separated by space, e.g. "github.com:friendsoftypo3 github.com:typo3". [default: "all"]
       token: Fetch the repositories using this GitHub / GitLab API token to overcome rate limitations. [default: ""]

Example::

    ./bash/get-repos.sh docs all github.com:typo3-documentation

modify-repos.sh
---------------

Modify the local repositories by a specific action.

modify-repos.sh set-fork
~~~~~~~~~~~~~~~~~~~~~~~~

Set a remote "fork" repository if a given user namespace has a repository with a matching name::

    ./bash/modify-repos.sh set-fork <fork> [<host>] [<user>] [<token>]

    Arguments:
       fork: Set a remote "fork" repository if this user namespace has a repository with a matching name.
       host: Execute the action in the local repositories of this host (all, github.com, ..). Multiple hosts must be separated by space, e.g. "github.com gitlab.com". [default: "all"]
       user: Execute the action in the local repositories of this user namespace (all, github.com:friendsoftypo3, github.com:typo3, github.com:typo3-documentation, ..). Multiple users must be separated by space, e.g. "github.com:friendsoftypo3 github.com:typo3". [default: "all"]
       token: Use this GitHub / GitLab API token to overcome rate limitations. [default: ""]

Example::

    ./bash/modify-repos.sh set-fork marble github.com

The repositories must already exist in generated-data/. Call get-repos.sh to clone or update first.

versionbranch-exist.sh
----------------------

Lists all local repositories for which a specific version branch exists::

    ./bash/versionbranch-exist.sh <version> [<host>] [<user>]

    Arguments:
       version: List all local repositories having a branch matching this version.
       host: List local repositories of this host (all, github.com, ..). Multiple hosts must be separated by space, e.g. "github.com gitlab.com". [default: "all"]
       user: List local repositories of this user namespace (all, github.com:friendsoftypo3, github.com:typo3, github.com:typo3-documentation, ..). Multiple users must be separated by space, e.g. "github.com:friendsoftypo3 github.com:typo3". [default: "all"]

Example::

    ./bash/versionbranch-exist.sh "7.6" github.com typo3

The repositories must already exist in generated-data/. Call get-repos.sh to clone or update first.

versionbranch-not-exist.sh
--------------------------

Lists all local repositories for which a specific version branch does not exist::

    ./bash/versionbranch-not-exist.sh <version> [<host>] [<user>]

    Arguments:
       version: List all local repositories not having a branch matching this version.
       host: List local repositories of this host (all, github.com, ..). Multiple hosts must be separated by space, e.g. "github.com gitlab.com". [default: "all"]
       user: List local repositories of this user namespace (all, github.com:friendsoftypo3, github.com:typo3, github.com:typo3-documentation, ..). Multiple users must be separated by space, e.g. "github.com:friendsoftypo3 github.com:typo3". [default: "all"]

Example::

    ./bash/versionbranch-not-exist.sh "11.5" all github.com:typo3-documentation

The repositories must already exist in generated-data/. Call get-repos.sh to clone or update first.
