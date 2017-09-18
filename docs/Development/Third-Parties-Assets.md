Vendor assets used by *WebDocBook*
================================

The `www/webdocbook_assets/vendor/` directory stores all the third-parties 
*assets* packages that are used by *WebDocBook*. You will find here some 
links to these packages and their actual version numbers.

To rebuild them, you can use the `bower.json` configuration file of
the `www/webdocbook_assets/vendor/` directory and make a `bower install`
or `bower update`.

@TODO - the best would be to find "portable" replacements for the jQuery
plugins `jquery.highlights` and `jquery.juizScrollTo`.


Basic rules for dependencies assets
-----------------------------------

We have to keep in mind that any dependency MUST be light-weight in
*WebDocBook* (we usually only need some few files from each dependency).

-   try to use only minified versions of JS and CSS
-   only include one "package" file (bower.json, package.json ...)
-   always include any "license" file


jQuery
------

-   <http://jquery.com/download/>
-   actual version: 1.11.2


Bootstrap
---------

-   <http://getbootstrap.com/customize/>
-   actual version 3.3.1

As WebDocBook does not use the *Glyphicons* any more, the package 
MUST be downloaded manually using a specific configuration.

The package configuration (basically all but the Glyphicons) is stored
at <http://gist.github.com/19ee64a16ea9662a97d2>.
To rebuild it, you can directly load 
<http://getbootstrap.com/customize/?id=19ee64a16ea9662a97d2>.


HTML5shiv
---------

-   <http://github.com/aFarkas/html5shiv>
-   actual version: 3.6.2


Font Awesome
------------

-   <http://fortawesome.github.io/Font-Awesome/>
-   actual version: 4.2.0


jQuery Table Sorter
-------------------

-   <http://github.com/Mottie/tablesorter>
-   actual version: 2.18.4


jQuery syntax highlight
-----------------------

-   <http://github.com/ematsakov/highlight>
-   actual version: 1.2


MathJax
-------

- <http://www.mathjax.org/>
- actual version: 2.3.0

See <http://github.com/mathjax/MathJax-docs/wiki/Guide%3A-reducing-size-of-a-mathjax-installation>
to learn what files need to be kept.

----
**Copyleft (ↄ) 2008-2017 [Pierre Cassat & contributors](http://webdocbook.com/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
