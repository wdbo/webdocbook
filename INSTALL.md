Docbook : installation instructions
===================================

**DocBook** is quite simple to install as long as you have access to the `root` user of
your system.


## Requirements

To allow DocBook to work on your webserver, you need the following environment:

-   a webserver running a Linux/UNIX operating system,
-   your requests must be handled by [Apache 2](http://httpd.apache.org/) or higher
    (or at least, `.htaccess` files must be activated)[^1]
-   [PHP 5.3](http://php.net/) or higher.

To build a new DocBook installation, you need the following to install [Composer](http://getcomposer.org/)
on your system.

As the package uses some internal Apache's features, you will need to:

-   create a new directory in your webserver HTTP accessible part,
-   set up the rights of the apache-user of your system upon directories,
-   define a new virtual-host in your system using the sample configuration below,
-   define an `.htaccess` file in your directory root using the sample file below.


## Installation step-by-step

### Get a copy of the sources

You have three ways to get a copy of the sources:

-   make a clone of the [GitHub repository](https://github.com/atelierspierrot/docbook):

        ~$ git clone git://github.com/atelierspierrot/docbook.git your/path/to/docbook

-   get an archive from GitHub, untar it in your wanted directory

-   download a tag version of the sources, see <https://github.com/atelierspierrot/docbook/tags>.


### Setting up the virtual host

For more infos about virtual hosts in Apache, how to define them and how to enable the related 
new domain see: <http://httpd.apache.org/docs/2.2/en/vhosts/>.

To allow DocBook to work, you have to define a new virtual host defining a directory for
web classic access to the `www/` directory and a CGI access to the `bin/` directory.

To do so, just open your `/etc/hosts` configuration file and add:

    <VirtualHost *:80>
        ServerAdmin your@email
        ServerName your.domain
    
        DocumentRoot /your/document/root/path/www
        <Directory "/your/document/root/path/www">
            AllowOverride All
            Order allow,deny
            allow from all
        </Directory>
    
        ScriptAlias /cgi-bin/ /your/document/root/path/bin/
        <Directory "/your/document/root/path/bin">
            AllowOverride All
            Order allow,deny
            allow from all
        </Directory>    

    </VirtualHost>

After that you will need to restart Apache with a command like (*depending on your
system and your Apache's version*):

    ~$ sudo /etc/init.d/apache2 restart

Some `.htaccess` files are included in the distribution so you may not have to worry about
them. If you encountered errors when browsing to your installation, see the 
[Errors](#errors-on-installation) section below.

### Test your new DocBook in a browser

Load the domain name you defined in your virtual host in a browser and check if the 
application works.

If you have an error like:

>    You need to run Composer on the project to build dependencies and auto-loading
>    (see: http://getcomposer.org/doc/00-intro.md#using-composer)!

it means that your DocBook is not yet installed (*some required dependencies are missing*).
To finish the installation, just run:

    ~$ php path/to/composer.phar install

or, if you install Composer globally in your environment:

    ~$ php composer install

Once the installation has finished, reload the page in your browser.

At the beginning, your DocBook contains three symbolic links to the Markdown files of the
package for demonstration. You can delete them as you like and begin to write and organize
your own contents.


## Errors on installation

### Nothing seems to work but no error

If your see the default page of Apache, just check that the `www/.htaccess` exists. If not,
make a copy of the `src/www_htaccess.dist.txt` to `www/.htaccess` and reload the page in
your browser.

### Internal server error

If your encountered an error like "Internal Server Error" trying to access your 
virtual host's domain name, try the following:

-   uncomment the line defining the `RewriteBase` setting in the `www/.htaccess` file
    (*somewhere around line 35*):

        #RewriteBase /

-   if you still have an internal error, try to copy the entire `www/.htaccess` file content
    and paste it in your first `Directory` definition of your virtual host like in the
    chapter above

-   if you still have an error, come to ask us explaining your system & configuration at
    <https://github.com/atelierspierrot/docbook/issues>.


[^1]: Some features requires Apache from version 2.0.23 but may not render an error with
lower versions.