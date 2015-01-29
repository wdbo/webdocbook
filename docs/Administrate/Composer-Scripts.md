The Composer scripts to administrate *WebDocBook*
=================================================

Some [Composer](http://getcomposer.org) scripts are defined to process
simple actions on a *WebDocBook* installation.

Usage
-----

Their usage is quite simple, you just have to run on a terminal from the
root directory of your installation (containing the `composer.json` file):

    $ composer SCRIPT_NAME

To run the `clear-cache` script for instance, use:

    $ composer wdb-clear-cache

To get a list of all current available scripts, use:

    $ composer list | grep wdb-


### Options

If your installation is not "classic" (if your root directory is not the
root directory of *WebDocBook*), you must specify the absolute path of your
installation as a `--basedir` argument:

    $ composer SCRIPT_NAME -- --basedir=PATH

Please note that the double-dash (`--`) between the script name and the 
argument is REQUIRED.

Below is a common example running a composer's script from the root
directory of a project:

    $ composer SCRIPT_NAME -- --basedir=.

You can also define this value globally in your `composer.json` configuration
file adding a "extra > wdb-basedir" entry:

    "extra": {
        "wdb-basedir": "."
    }


Available scripts
-----------------

The following scripts are available to administrate your application:

-   `clear-cache` : clear all files in the `var/cache/` directory
-   `clear-i18n` : clear all files in the `var/i18n/` directory
-   `flush` : process both `clear-cache` and `clear-i18n` scripts
-   `init` : install or re-install configuration files in the `user/config/` directory



----
**Copyleft (ↄ) 2008-2015 [Pierre Cassat & contributors](http://webdocbook.com/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
