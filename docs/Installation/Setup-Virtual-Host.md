Virtual hosting
===============

To allow *WebDocBook* to work, you have to define a new virtual host pointing  to the `www/` 
directory of your *WebDocBook* installation.

Server running [Apache](http://httpd.apache.org/)
---------------------

Depending on your system and your version of Apache, the virtual host definition may be added
in the `/etc/apache2/httpd.conf` file or in a new file `/etc/apache/sites-available/your.domain`.
In this second case, after defining your host, you will need to enable it and restart the
Apache server on your system. See the [FAQ](../Troubleshooting.md) section below for more info.

This is a classic *WebDocBook* virtual host configuration:

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
If you encountered errors when browsing to your installation, see the [FAQ](../Troubleshooting.md) page.

For more information about virtual hosts in Apache, how to define them and how to enable the related 
new domain see: <http://httpd.apache.org/docs/2.2/en/vhosts/>.


Server running [Nginx](http://nginx.org/en/)
--------------------


----
**Copyleft (ↄ) 2008-2015 [Pierre Cassat & contributors](http://webdocbook.com/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
