Special files in *WebDocBook*
==========================

Special files
-------------

### Chapter's files

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

### Meta-files

*WebDocBook* is designed to use some special *meta-files* for each chapter. A *meta-file* is
loaded for each chapter's content, allowing to define some specific contents (meta-data, references ...)
once for a whole chapter contents.

-   `.references` can contains all images, links, abbreviations or footnotes references of the chapter ;
    its content is added at the bottom of each file (you can define some specific references in
    the file and the global ones will be added)
-   `.meta` can contains some global meta-data of the chapter ; its content is added at the top of each
    file ;
-   `.footer` and `.header` can contain a Markdown or HTML contents that will be added at the top
    or bottom of the content of each chapter's page.


Options
-------

A special `wdb` meta-data can be defined globally or in each Markdown file to enable or disable
some of the *WebDocBook* rendering features.

-   `book_pager` enables a pager at the bottom of the content (basically the same pager as the
    one of the breadcrumbs bar)
-   `maths` enables the loading of the [MathJax library](http://www.mathjax.org/), which is **disabled** by default
    (it is quite heavy so the default behavior is to NOT load its files for a page)
-   `notoc` disables the automatic table of contents



----
**Copyleft (ↄ) 2008-2015 [Pierre Cassat & contributors](http://webdocbook.com/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
