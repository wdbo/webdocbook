Docbook : a simple Markdown CMS
===============================

**DocBook** is a simple PHP tool to build rich HTML views from Markdown files following a 
filesystem architecture. It embeds some classic website features like a search in contents,
some RSS feeds generation or translations switching.

**DocBook** builds a "like-a-book" interactive website from simple Markdown files.


## Features

DocBook is a simple application organized beyond a filesystem architecture of Markdown files.
Each file is a "page" and each sub-directory is a "section" of pages. The title of the files 
or directories is used to be the title of the page or section.

### Special files

Any file named `INDEX.md` in a directory will be considered as its index and be delivered if 
no other file is requested in the URL.

Any file named `README.md` in a directory will be displayed at the bottom of the directory 
contents indexing, just like the default behavior of Apache.

### Files infos

For each file, a set of dependences can be defined to overwrite the default value, that must 
itself be defined at the project root directory. These files are, by default:

-   `*.copyright.md`: a copyright, license or protection information for the file content,
-   `*.author(s).md`: an information about the author(s) of the file,
-   `*.changelog.md`: the evolutions informations of the file.

### Markdown syntax

The Markdown syntax used by DocBook follows the [Markdown Extended](https://github.com/atelierspierrot/markdown-extended)
rules. They are inherited from:

-   the original [John Gruber's syntax](http://daringfireball.net/projects/markdown/syntax),
-   the ["extra" version from Michel Fortin](http://michelf.ca/projects/php-markdown/concepts/),
-   some features are also included from the [Fletcher Penny's MultiMarkdown](http://fletcher.github.com/peg-multimarkdown/).

### Translations

Each document can be translated, naming the translation file like:

    CONTENT.md
    CONTENT.fr.md // translation in French
    ...

### URL routing

Globally or for each of your DocBook directories, the following routes are available:

-   `*/sitemap`: build a sitemap XML from this position and through its children,
-   `*/notes`: build a page referencing all footnotes from this position and through its children,
-   `*/glossary`: build a page referencing all glossary entries from this position and through 
    its children,
-   `*/bibliography`: build a page referencing all bibliographic entries from this position and 
    through its children,
-   `*/rss`: build an RSS feed of contents from this position and through its children,
-   `*/search?s=A&ln=LN`: process a search of string "A" in contents from this position and through 
    its children in the specified language if defined.

For each file of your DocBook, the following URLs are available:

-   `*/ln?LN`: get this content in LN language if present,
-   `*/download`: download the original file of the page,
-   `*/htmlonly`: get the plain HTML version of the page,
-   `*/plain`: get the plain text version of the page.

### Data files organization

All your Markdown files, the real pages of your website, have to be stored in the `www/` 
directory or sub-directories.

Any assets, image or other media file, that you want to include or use in your Markdown
contents must be stores in an `assets/` sub-directory in the current directory. If you do
not follow this rule, your file will not be accessible by Apache.

By default, any file contained in a directory named `wip/` will not be displayed publicly 
and will not be referenced in the sitemap neither in the index ; to view it, you will have 
to manually write its URL (see the [Routing](#url-routing) section of this document to 
learn more about the application URLs' construction). 

Knowing that, a classic DocBook directory organization should be:

    | chapter-name/
    | ------------- README.md           // the first file shown loading the directory
    | ------------- assets/             // a directory containing your medias
    | ------------- wip/                // a directory containing your work-in-progress contents
    | ------------- PAGE.md             // a Markdown content file (page 1)
    | ------------- OTHER-PAGE.md       // another Markdown content file (page 2)
    | ------------- OTHER-PAGE.fr.md    // the french translation of page 2
    | ------------- sub-chapter1/       // a sub-directory containing a sub-chapter
    | ------------- sub-chapter2/       // a sub-directory containing another sub-chapter

### The DocBook chapters

All your first depth directories (directories contains directly in your DocBook `www/` root)
are considered as your chapters and are listed in the header navigation bar for quick access.


## Organization

For more informations about how to use DocBook every day, browse the `/docbookdoc` URL of
your installation to read the DocBook user manual. A link to this manual is always accessible
in the header navigation bar.

### Architecture

The default global architecture of your DocBook is:

    | src/
    | tmp/
    | user/
    | www/

-   `src/` contains the PHP sources of the application and the template files ; to define a 
    new template, put your file here ; it must follow an architecture like:

        | src/
        | ---- config/
        | ---- DocBook/
        | ---- templates/

-   `tmp/` is a sub-directory to store some configurations and some cached files ; the best 
    practice is to not touch them but you can, in extreme conditions, erase all their 
    contents without worry ; it is (re-)generated by the application ;

-   `user/` is the directory to put your own user configuration or templates (*see the 
    [Fallback system](#fallback-system) section for more infos*) ; you can create it as it
    doesn't exist in the distribution ; it may follow an architecture like:

        | user/
        | ---- config/
        | ---- templates/

-   `www/` sub-directory must be the `DOCUMENT_ROOT` of your virtual host (*anything outside 
    this directory is not used in HTML pages*) ; it must follow an architecture like:

        | www/
        | ---- docbook_assets/
        | ---- .htaccess
        | ---- index.php

**NOTE** - A `vendor/` sub-directory will be created by the application in both `src/` and 
`www/docbook_assets/` directories to store the vendor external packages used by DocBook ;
do not modify them.

### Fallback system

The application is constructed to allow user to over-write some configuration settings and
the templates used to build the pages. This feature is quite simple:

-   by default, some configurations and templates are embedded with the application in 
    the `src/config/` and `src/templates/` directories ;
-   any file found in the `user/config/` will be taken primary to the default config and
    any file found in the `user/templates/` will be taken primary to the default templates.

Templates follows a specific rule as the application can use a collection of templates to
build different designs for pages.

### Custom templates

As this application uses [Twig](http://twig.sensiolabs.org/) to build its views, if you
want to write your own templates you may follow [Twig's documentation](http://twig.sensiolabs.org/documentation).


## License / Dependencies

**DocBook** is an open-source application released under a
[GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html). You can freely
download it, use it or distribute it as long as you stay in the license scope.

**DocBook** is developed with the help of the following third-parties:

-   [Bootstrap](http://twitter.github.io/bootstrap/), a responsive front-end framework, 
    released under [Apache license v2](http://www.apache.org/licenses/LICENSE-2.0),
-   [Twig](http://twig.sensiolabs.org/), a template engine for PHP, 
    released under [BSD license](http://opensource.org/licenses/BSD-3-Clause),
-   [jQuery](http://jquery.com/), an open-source javascript library, 
    released under [MIT license](http://github.com/jquery/jquery/blob/master/MIT-LICENSE.txt),
-   [HTML5shiv](http://code.google.com/p/html5shiv/), an old browsers HTML5 explainer, 
    released under [MIT license](http://github.com/jquery/jquery/blob/master/MIT-LICENSE.txt),

**DocBook** is based on some of our other packages:

-   [PHP Patterns](http://github.com/atelierspierrot/patterns)
-   [PHP Library](http://github.com/atelierspierrot/library)
-   [PHP Web Filesystem](http://github.com/atelierspierrot/webfilesystem)
-   [PHP Markdown Extended](http://github.com/atelierspierrot/markdown-extended)
-   [PHP Internationalization](http://github.com/atelierspierrot/internationalization)


----
**Copyleft (c) 2008-2013 [Les Ateliers Pierrot](http://www.ateliers-pierrot.fr/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
