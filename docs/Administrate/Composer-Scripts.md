The Composer scripts to administrate *WebDocBook*
=================================================

Some [Composer](http://getcomposer.org) scripts are defined to process
simple actions on a *WebDocBook* installation.

Their usage is quite simple, you just have to run on a terminal from the
root directory of your installation (containing the `composer.json` file):

    $ composer SCRIPT_NAME

To run the `clear-cache` script for instance, use:

    $ composer wdb-clear-cache

To get a list of all current available scripts, use:

    $ composer list | grep wdb-

If your installation is not "classic" (if your root directory is not the
root directory of *WebDocBook*), you must specify the absolute path of your
installation as a `--basedir` argument:

    $ composer SCRIPT_NAME -- --basedir=PATH

Please note that the double-dash (`--`) between the script name and the 
argument is REQUIRED.

Below is a common example running a composer's script from the root
directory of a project:

    $ composer SCRIPT_NAME -- --basedir=.
