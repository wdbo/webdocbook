The sources of *WebDocBook*
========================


App structure
-------------

The structure described below MUST be fixed and not over-writable:

    [ROOT_DIR]
    |
    | composer.json
    |
    | src/                              : WebDocBook internals: this can NOT be edited, moved or removed
    | ----- WebDocBook/
    | ----- templates/
    | ----- vendor/
    |
    | user/
    | ----- config/
    | ------------- webdocbook.ini      : this can be edited
    | ------------- webdocbook_i18n.csv : this can be edited
    | ------------- user_config.ini     : this is handled by the '/admin' page of WebDocBook
    | ----- templates/                  : this is optional
    |
    | var/
    | ----- cache/
    | ----- log/
    | ----- i18n/
    |
    | www/
    | ----- index.php                   : this can be renamed in user/config/webdocbook.ini
    | ----- webdocbook_assets/          : this can be renamed in user/config/webdocbook.ini
    | -------------------- vendor/


Namespace
---------

The whole app is embedded in the `WebDocBook` PHP namespace. As it uses [Composer](http://getcomposer.com/),
the package's classes are all named following their class name (in camel-case-underscored) 
and all included in the namespace.


Kernel
------


----
**Copyleft (ↄ) 2008-2015 [Pierre Cassat & contributors](http://webdocbook.com/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
