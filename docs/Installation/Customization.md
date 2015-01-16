Customizing *WebDocBook*
=====================

Fallback system
---------------

The application is constructed to allow user to over-write some configuration settings and
the templates used to build the pages. This feature is quite simple:

-   by default, some configurations and templates are embedded with the application in 
    the `src/config/` and `src/templates/` directories ;
-   if a `user/docbook.config` file is found, it will override default configuration ;
-   any file found in the `user/templates/` will be taken primary to the default templates.

The templates follows a specific rule as the application can use a collection of templates
to build different designs for pages.

Custom templates
----------------

As this application uses [Twig](http://twig.sensiolabs.org/) to build its views, if you
want to write your own templates you may follow [Twig's documentation](http://twig.sensiolabs.org/documentation).


----
**Copyleft (c) 2008-2015 [Les Ateliers Pierrot](http://www.ateliers-pierrot.fr/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
