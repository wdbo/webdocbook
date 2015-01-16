Routing in *WebDocBook*
====================

Globally or for each of your DocBook directories, the following routes are available:

-   `*/sitemap`: build a sitemap XML from this position and through its children,
-   `*/rss`: build an RSS feed of contents from this position and through its children,
-   `*/search?s=A&lang=LN`: process a search of string "A" in contents from this position and through 
    its children in the specified language if defined.

For each file of your DocBook, the following routes are available:

-   `*/?lang=LN`: get this content in LN language if present,
-   `*/download`: download the original file of the page,
-   `*/htmlonly`: get the plain HTML version of the page,
-   `*/plain`: get the plain text version of the page.

----
**Copyleft (c) 2008-2015 [Les Ateliers Pierrot](http://www.ateliers-pierrot.fr/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
