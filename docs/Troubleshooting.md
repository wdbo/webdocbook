Troubleshooting
===============

Installation issues
-------------------

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

### How-to: make a custom configuration

The configuration of *WebDocBook* is stored by default in the `src/config/webdocbook.ini` file
that is initially a symbolic link to `src/WebSocBook/Resources/config/dist/webdocbook.dist.ini`. 
If you want to manually build a custom configuration file, you can replace the symbolic link by 
a hard copy of the distributed configuration file and edit it:

    $ unlink user/config/webdocbook.ini
    $ cp src/WebDocBook/Resources/config/webdocbook.dist.ini user/config/webdocbook.ini
    $ vi webdocbook.ini

### Error: nothing seems to work but I got no error message

If you see the default page of Apache, just check that the `www/.htaccess` exists. If not,
make a copy of the `src/config/dist/www_htaccess.dist.txt` to `www/.htaccess` and reload the 
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
    <http://github.com/wdbo/webdocbook/issues>.

### Error: "An error occurred while trying to create directory 'XXX'!"

This message means that *WebDocBook* tries to create a new directory but has not enough rights to
do so in your system. To correct this error, just create the `XXX` directory mentioned in
the error message manually and set it writable rights running:

    ~$ mkdir XXX && chmod 755 XXX

If the error still occurred, you can set the directory some full rights (*this is really NOT
recommended as it is a security failure*):

    ~$ chmod 777 XXX

----
**Copyleft (ↄ) 2008-2017 [Pierre Cassat & contributors](http://webdocbook.com/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
