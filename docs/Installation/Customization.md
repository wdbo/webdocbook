Customizing *WebDocBook*
=====================


Fallback system
---------------

The application is constructed to allow user to over-write the templates used to build the pages.
This feature is quite simple:

-   by default, the templates are embedded with the application in the `src/templates/` directory ;
-   any file found in the `user/templates/` will be taken primary to the default template.


Build custom templates
----------------------

As this application uses [Twig](http://twig.sensiolabs.org/) to build its views, if you
want to write your own templates you may follow [Twig's documentation](http://twig.sensiolabs.org/documentation).


----
**Copyleft (ↄ) 2008-2015 [Pierre Cassat & contributors](http://webdocbook.com/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
