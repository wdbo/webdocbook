The sources of *DocBook*
========================


App structure
-------------

The structure described below MUST be fixed and not over-writable:

    [ROOT_DIR]
    |
    | composer.json
    |
    | src/                              : docbook internals: this can NOT be edited, moved or removed
    | ----- config/
    | ----- DocBook/
    | ----- templates/
    | ----- vendor/
    |
    | user/
    | ----- config/
    | ------------- docbook.ini         : this can be edited
    | ------------- docbook_i18n.csv    : this can be edited
    | ------------- user_config.ini     : this is handled by the '/admin' page of DocBook
    | ----- templates/                  : this is optional
    |
    | var/
    | ----- cache/
    | ----- log/
    | ----- i18n/
    |
    | www/
    | ----- index.php                   : this can be renamed in user/config/docbook.ini
    | ----- docbook_assets/             : this can be renamed in user/config/docbook.ini
    | -------------------- vendor/


Namespace
---------

The whole app is embedded in the `DocBook` PHP namespace. As it uses [Composer](http://getcomposer.com/),
the package's classes are all named following their class name (in camel-case-underscored) 
and all included in the namespace.


Kernel
------


----
**Copyleft (c) 2008-2015 [Les Ateliers Pierrot](http://www.ateliers-pierrot.fr/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
