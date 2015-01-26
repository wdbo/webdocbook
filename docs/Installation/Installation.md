Installing *WebDocBook*
=======================

**WebDocBook** is quite simple to install as long as you can define a virtual-host
on your server and use *URL rewriting*.


Requirements
------------

To allow *WebDocBook* to work on your web-server, you need the following environment:

-   a web-server running a Linux/UNIX operating system,
-   you must be able to edit your server software configuration or at least one of its virtual hosts,
-   your server must allow URL rewriting,
-   you must be able to render some directories writable for your web-server user,
-   your server must run [PHP 5.3.0](http://php.net/) or higher,
-   your server must have a working [Composer](http://getcomposer.org/) command.

See the [FAQ](../Troubleshooting.md) page to get help about these requirements and configurations.

Additionally, you will need [Bower](http://bower.io/) to update *WebDocBook*'s assets if needed.


Installation step-by-step
-------------------------

### Get a copy of the sources

You have four ways to get a copy of the sources:

-   create a project via Composer:

        ~$ php path/to/composer.phar create-project wdbo/webdocbook your/path/to/docbook dev-master --no-dev

    you can select a specific version replacing `dev-master` by the version number 

-   make a clone of the [GitHub repository](http://github.com/wdbo/webdocbook):

        ~$ git clone git://github.com/wdbo/webdocbook.git your/path/to/docbook

-   get an archive from GitHub, *untar* it in your target directory

-   download a tag version of the sources, see <http://github.com/wdbo/webdocbook/tags>.

For each stable version of *WebDocBook*, a new tag may exist named **vX.Y.Z**, if you prefer to
use a download version rather than a Git clone, use a tag preferably.

When available, any tag named like **vX.Y.Z-outofthebox** is a full and already installed 
copy of concerned version. Note that this kind of package may not work on every systems
but it is the best practice to use them as they are already full and ready-to-use.


### Setting up the virtual host

Your server (or virtual host) must point to the `www/` directory of your installation.

See the [Virtual Host](Setup-Virtual-Host.md) page for an "how-to".


### Test your new WebDocBook in a browser

Load the domain name you defined in your virtual host in a browser and check if the 
application works.

If you have an error like:

>    You need to run Composer on the project to build dependencies and auto-loading
>    (see: http://getcomposer.org/doc/00-intro.md#using-composer)!

it means that your *WebDocBook* is not yet installed (*some required dependencies are missing*).
To finish the installation, just run:

    ~$ php path/to/composer.phar install

or, if you installed Composer globally in your environment:

    ~$ php composer install

Once the installation has finished, reload the page in your browser.

At the beginning, your *WebDocBook* contains three symbolic links to the Markdown files of the
package for demonstration. You can delete them as you like and [begin to write and organize
your own contents](../Organization.md).


----
**Copyleft (ↄ) 2008-2015 [Pierre Cassat & contributors](http://webdocbook.com/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
