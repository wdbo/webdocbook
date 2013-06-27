Docbook : installation instructions
===================================

**DocBook** is quite simple to install as long as you can use Apache's `.htaccess` files.


## Requirements

To allow DocBook to work on your webserver, you need the following environment:

-   a webserver running a Linux/UNIX operating system,
-   your requests must be handled by [Apache 2](http://httpd.apache.org/)
    (or, at least, `.htaccess` files must be activated)[^1]
-   [PHP 5.3.0](http://php.net/) or higher.

If you had not downloaded an "**out-of-the-box**" version of DocBook, you will need to manually
build your installation. To do so, you need to install [Composer](http://getcomposer.org/)
on your system.

As the package uses some internal Apache's features, you will need to:

-   create a new directory in your webserver HTTP accessible part,
-   set up the rights of the apache-user of your system upon directories,
-   define a new virtual-host in your system using the sample configuration below,
-   define an `.htaccess` file in your directory root using the sample file below,
-   enables the [mod_rewrite](http://httpd.apache.org/docs/2.2/en/mod/mod_rewrite.html) 
    Apache module (*see the [FAQ](#faq) section below for an "how-to"*)


## Installation step-by-step

### Get a copy of the sources

You have four ways to get a copy of the sources:

-   create a project via Composer:

        ~$ php path/to/composer.phar create-project atelierspierrot/docbook your/path/to/docbook dev-master --no-dev

    you can select a specific version replacing `dev-master` by the version number 

-   make a clone of the [GitHub repository](https://github.com/atelierspierrot/docbook):

        ~$ git clone git://github.com/atelierspierrot/docbook.git your/path/to/docbook

-   get an archive from GitHub, untar it in your target directory

-   download a tag version of the sources, see <https://github.com/atelierspierrot/docbook/tags>.

For each stable version of DocBook, a new tag may exist named **vX.Y.Z**, if you prefer to
use a download version rather than a Git clone, use a tag preferably.

When available, any tag named like **vX.Y.Z-outofthebox** is a full and already installed 
copy of the concerned version. Note that this kind of package may not work on every systems
but it is the best practice to use them as they are already full and ready-to-use.


### Setting up the virtual host

For more infos about virtual hosts in Apache, how to define them and how to enable the related 
new domain see: <http://httpd.apache.org/docs/2.2/en/vhosts/>.

To allow DocBook to work, you have to define a new virtual host defining a directory for
web classic access to the `www/` directory.

Depending on your system and your version of Apache, the virtual host definition may be added
in the `/etc/apache2/httpd.conf` file or in a new file `/etc/apache/sites-available/your.domain`.
In this second case, after defining your host, you will need to enable it and restart the
Apache server on your system. See the [FAQ](#faq) section below for more infos.

This is a classic DocBook virtual host configuration:

    <VirtualHost *:80>
        ServerAdmin your@email
        ServerName your.domain
        DocumentRoot /your/document/root/path/www
        <Directory "/your/document/root/path/www">
            AllowOverride All
            Order allow,deny
            allow from all
        </Directory>
    </VirtualHost>

After that you will need to restart Apache with a command like (*depending on your
system and your Apache's version*):

    ~$ sudo /etc/init.d/apache2 restart

An `.htaccess` file is included in the distribution so you may not have to worry about it.
If you encountered errors when browsing to your installation, see the [FAQ](#faq) section below.

### Test your new DocBook in a browser

Load the domain name you defined in your virtual host in a browser and check if the 
application works.

If you have an error like:

>    You need to run Composer on the project to build dependencies and auto-loading
>    (see: http://getcomposer.org/doc/00-intro.md#using-composer)!

it means that your DocBook is not yet installed (*some required dependencies are missing*).
To finish the installation, just run:

    ~$ php path/to/composer.phar install

or, if you installed Composer globally in your environment:

    ~$ php composer install

Once the installation has finished, reload the page in your browser.

At the beginning, your DocBook contains three symbolic links to the Markdown files of the
package for demonstration. You can delete them as you like and begin to write and organize
your own contents.


## FAQ

### How-to: enable an new site in Apache2

If your virtual host is defined in a single file in `/etc/apache/sites-available/your.domain`,
you need to enable it running:

    ~$ a2ensite your.domain

Then restart Apache running:

    ~$ sudo /etc/init.d/apache2 restart


### How-to: enable an Apache module

To enable the Apache module `mod_NAME` on your server, just run the following command:

    ~$ a2enmod NAME

Once you have enabled all required modules, restart Apache running:

    ~$ sudo /etc/init.d/apache2 restart


### Error: nothing seems to work but I got no error message

If you see the default page of Apache, just check that the `www/.htaccess` exists. If not,
make a copy of the `src/config/www_htaccess.dist.txt` to `www/.htaccess` and reload the 
page in your browser.

### Error: "Internal Server Error"

If your encountered an "Internal Server Error" trying to access your 
virtual host's domain name, try the following:

-   uncomment the line defining the `RewriteBase` setting in the `www/.htaccess` file
    (*somewhere around line 35*):

        RewriteBase /

-   if you still have an internal error, try to copy the entire `www/.htaccess` file content
    and paste it in your first `Directory` definition of your virtual host like in the
    chapter above

-   if you still have an error, come to ask us explaining your system & configuration at
    <https://github.com/atelierspierrot/docbook/issues>.

### Error: "An error occured while trying to create directory 'XXX'!"

This message means that DocBook tries to create a new directory but has not enough rights to
do so in your system. To correct this error, just creates the `XXX` directory mentionned in
the error message manually and set it full rights running [^2]:

    ~$ mkdir XXX && chmod 777 XXX


[^1]: DocBook may works correctly with an Apache 1 version but we have not tested this case.
[^2]: If your system refuses to set the CHMOD on 777, try to set it on 755 (*some hosting servers
disallow using a chmod of 777*).

