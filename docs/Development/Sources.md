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


App source code
---------------

*WebDocBook* is constructed following an [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)
pattern and embed its own exceptions classes to fallback to the error pages. It also defines a
collection of *file-system* classes to construct the views of its contents directories and files.
Finally, it uses the [Twig](http://twig.sensiolabs.org/) template engine to construct its HTML.

### Namespace

The whole app is embedded in the `WebDocBook` PHP namespace. As it uses [Composer](http://getcomposer.com/),
the package's classes are all named following their class name (in camel-case-underscored) 
and all included in the namespace.

### Kernel

The `\WebDocBook\Kernel` class is the core of the app. It handles the default configurations and 
application paths. It tests that all paths exist and are writable if necessary. It also defines
the internal methods to administrate an installation (executed by the [Composer scripts](Composer-Scripts.md).

The Kernel is called at each run and the application will break with an error if something went
wrong on boot.

A classic *WebDocBook* usage MUST *boot* the kernel using something like the following:

    \WebDocBook\Kernel::boot();

### Front controller

The `\WebDocBook\FrontController` class is in charge to handle each *http* request and to return the
response. It distributes the work to concerned controller (in the `\WebDocBook\Controller` namespace)
following some routing rules. 

A classic PHP interface should be something like the following:

    \WebDocBook\FrontController::getInstance()
        ->distribute();

### Exceptions

Some custom exception classes are defined to manage any exception occurring at runtime.

-   `\WebDocBook\Exception\Exception` : the default *exception* class of *WebDocBook*
-   `\WebDocBook\Exception\RuntimeException` : the *500 internal server error* page
-   `\WebDocBook\Exception\NotFoundException` : the *404 not found* error page

### Controllers

All controllers are stored in the `\WebDocBook\Controller` namespace and MUST extend the
`\WebDocBook\Abstracts\AbstractController` class.

The distribution of the request to the appropriate controller and controller's method is
quite simple and based on the `routes` configuration table. The `FrontController` will search
matching rule in the table for current request URI and call a method in concerned controller
named `[action name]Action()` passing it the requested document path as only argument. A controller
method must return an array with 2 or 3 items:

    [ template file , content (, params) ]

### File-system contents

As most of *WebDocBook*'s work is to handle some file contents, a special and global `File` model
is defined in the `\WebDocBook\Model\File` class. It basically creates a child object corresponding
to the document type requested and stored in the `\WebDocBook\Filesystem\WDBFileType` namespace.

The file types are defined in the `file_types` configuration table.

### Templating

The templating is handled by the `\WebDocBook\Templating\TemplateBuilder` object which constructs
the views with the help of the [Twig](http://twig.sensiolabs.org/) template engine through the
`WebDocBook_Templating_Twig_Extension` defined by the application.

The default template files are stored in the `src/templates/` directory and are named with a
`.html.twig` file extension to identify that they must follow the *Twig* syntax. The full list
of templates used by the application is defined in the `templates` configuration table.

### Markdown-Extended

*WebDocBook* embeds its own customization of the default `HTML` output-format of the
[Markdown Extended](http://github.com/piwi/markdown-extended) package. This customization basically
adds the links to go back to the table of contents after each title.



----
**Copyleft (ↄ) 2008-2017 [Pierre Cassat & contributors](http://webdocbook.com/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
