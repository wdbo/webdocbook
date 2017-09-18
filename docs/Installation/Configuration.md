Configuring *WebDocBook*
========================

The configuration files of your installation are stored in the `user/config/` directories.


Global configuration file
-------------------------

The global configurations of your application are stored in the `webdocbook.ini` file.
This file follows a [INI notation](http://en.wikipedia.org/wiki/INI_file) organized by
array entries (*complex INI notation*) and is **REQUIRED** for the application to work 
correctly. 

You can update it **at your own risks** as long as you keep each variable name. Be warned
that a wrong value can brake everything. If something is broken and you can't find where
is the error, the original version of this configuration file is available in the application
sources at `src/WebDocBook/Resources/config/webdocbook.dist.ini`.


Translations file
-----------------

The language strings of translations used by the application is stored in the `webdocbook_i18n.csv`
file. Each language strings table is compiled and stored in a `var/i18n/ln.php` file if it is not
present. If such file is found, the original CSV file will **NOT** be parsed, even if it has been
modified (for performance reasons). Have a look at the [Administration scripts](Administration/Composer-Scripts.md)
to learn how to flush the PHP files and force them to be rebuilt.


----
**Copyleft (ↄ) 2008-2017 [Pierre Cassat & contributors](http://webdocbook.com/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
