WebDocBook : the web like a book
===============================

**WebDocBook** is a simple PHP app to build rich HTML5 views from Markdown files following a 
filesystem architecture. It embeds some classic CMS' website features like a search in contents,
some RSS feeds generation or translations switching.

**WebDocBook** builds a "like-a-book" interactive website from simple Markdown files.

To begin, watch the <http://webdocbook.com/> website.

Key features
------------

-   WebDocBook is a simple application organized beyond a filesystem architecture of Markdown files.
    Each file is a "page" and each sub-directory is a "section" of pages. The title of the files 
    or directories is used to be the title of the page or section;
-   WebDocBook views' are **HTML5 valid** with the help of [Bootstrap](http://twitter.github.io/bootstrap/);
-   WebDocBook is highly **configurable** and **customizable**;
-   WebDocBook uses the [**Markdown Extended**](http://aboutmde.org/) advanced syntax;
-   WebDocBook generates sitemaps and RSS feeds easily;
-   WebDocBook DOES NOT use a database.


Quick install
-------------

1.  To install **WebDocBook**, you will first need [Composer](http://getcomposer.org/) which is
    required to install all dependencies.

2.  Once Composer is installed, you just have to run:

        $ composer create-project wdbo/webdocbook your/path/to/webdocbook 1.* --no-dev

3.  Configure a virtual-host on your web-server to point to the `www/` directory of your
    installation.

4.  Browse to your new virtual-host and fix the boot errors if there are any.

That's it! Your *WebDocBook* is ready!

To begin, you can copy or link some of your contents in the `www/` directory of your
installation.

To get help, you can have a look at the documentation in `docs/`.


Organization overview
---------------------

All the Markdown files, the real pages of the website, have to be stored in the `www/` 
directory or its sub-directories.

Any file named `INDEX.md` in a directory will be considered as its index and be delivered if 
no other file is requested in the URL.

Any file named `README.md` in a directory will be displayed at the bottom of the directory 
contents indexing, just like the default behavior of Apache.

Any asset, image or other media file, that you want to include or use in a Markdown
content must be stored in an `assets/` sub-directory in the current directory. If you do
not follow this rule, your file will not be accessible by the web-server.

By default, any file contained in a directory named `wip/` will not be displayed publicly 
and will not be referenced in the *sitemap* neither in the index ; to view it, you will have 
to manually write its URL. 

Knowing that, a classic *WebDocBook* directory organization should be:

    | chapter-name/
    | ------------- README.md           // the first file shown loading the directory
    | ------------- assets/             // a directory containing your medias
    | ------------- wip/                // a directory containing your work-in-progress contents
    | ------------- PAGE.md             // a Markdown content file (page 1)
    | ------------- OTHER-PAGE.md       // another Markdown content file (page 2)
    | ------------- OTHER-PAGE.fr.md    // the french translation of page 2
    | ------------- sub-chapter1/       // a sub-directory containing a sub-chapter
    | ------------- sub-chapter2/       // a sub-directory containing another sub-chapter

All your first depth directories (directories contained directly in your *WebDocBook* `www/` root)
are considered as your chapters and are listed in the header navigation bar of each page
for quick access.


License / Dependencies
----------------------

**WebDocBook** is an open-source software released under a
[GNU General Public License version 3](http://github.com/wdbo/webdocbook/blob/master/LICENSE). 
You can freely download it, use it or distribute it as long as you stay in the license 
conditions. See the `LICENSE` file for more info.

The frontend of *WebDocBook* is developed with the help of the following third-parties:

-   [Bootstrap](http://twitter.github.io/bootstrap/), a responsive front-end framework, 
    released under [Apache license v2](http://www.apache.org/licenses/LICENSE-2.0),
    written by [Mark Otto](http://twitter.com/mdo) & [Jacob Thornton](http://twitter.com/fat),    
-   [jQuery](http://jquery.com/), an open-source javascript library, 
    released under [MIT license](http://github.com/jquery/jquery/blob/master/MIT-LICENSE.txt),
    written by [John Resig](http://ejohn.org/) & the [jQuery Foundation and other contributors](http://jquery.org/),
-   [HTML5shiv](http://code.google.com/p/html5shiv/), an old browsers HTML5 explainer, 
    released under [MIT license](http://github.com/jquery/jquery/blob/master/MIT-LICENSE.txt),
    written by [Albert Farkas](http://twitter.com/afarkas), [John David Dalton](http://twitter.com/jdalton), 
    [Jonathan Neal](http://twitter.com/jon_neal) & [Remy Sharp](http://twitter.com/rem),
-   [Tablesorter](http://mottie.github.io/tablesorter/docs/), a *jQuery* plugin, 
    released under [MIT license](http://github.com/jquery/jquery/blob/master/MIT-LICENSE.txt),
    written by [Christian Bach](http://twitter.com/lovepeacenukes),
-   [Highlight](http://webcodingstudio.com/blog/jquery-syntax-highlight-plugin), a *jQuery* plugin, 
    released under [MIT license](http://github.com/jquery/jquery/blob/master/MIT-LICENSE.txt),
    written by [Evgeny Matsakov](http://webcodingstudio.com/),
-   [Font Awesome](http://fortawesome.github.io/Font-Awesome/), an iconic font and CSS toolkit, 
    released under [SIL OFL 1.1 license](http://scripts.sil.org/OFL),
    written by [Dave Gandy](http://twitter.com/davegandy),
-   [MathJax](http://www.mathjax.org), a javascript library to write mathematics in HTML, 
    released under [Apache 2.0 license](http://www.apache.org/licenses/),
    written by the MathJax Consortium.

The default icon of *WebDocBook* is *yin-yang* made by [Silmathoron](http://silmathoron.deviantart.com/)
under a [CC-BY-NC-SA license](https://creativecommons.org/licenses/by-nc-sa/3.0/legalcode).

The backend of *WebDocBook* is developed with the help of the following packages:

-   [Twig](http://twig.sensiolabs.org/), a template engine for PHP, 
    released under [BSD license](http://opensource.org/licenses/BSD-3-Clause),
    written by [Fabien Potencier](http://connect.sensiolabs.com/api/alternates/4aed4f5d-e0cb-4320-902f-885fddaa7d15),
-   [MarkdownExtended](http://github.com/piwi/markdown-extended), a PHP markdown parser following the
    [MarkdownExtended specifications](http://aboutmde.org/), 
    released under [BSD license](http://opensource.org/licenses/BSD-3-Clause),
    written by [myself](http://e-piwi.fr/),
-   it is also based on the following [Les Ateliers Pierrot's](http://www.ateliers-pierrot.fr/) packages:
    -   [PHP Patterns](http://github.com/atelierspierrot/patterns),
    -   [PHP Library](http://github.com/atelierspierrot/library),
    -   [PHP Web Filesystem](http://github.com/atelierspierrot/webfilesystem),
    -   [PHP Internationalization](http://github.com/atelierspierrot/internationalization).
