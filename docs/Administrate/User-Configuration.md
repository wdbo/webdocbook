Administration of *WebDocBook*
==============================


Administration panel
--------------------

Some configuration variables are natively editable via the `/admin` page of
*WebDocBook*. On this page, you can edit all common configurations in an HTML
form and choose to enable or disable some of the rendering features of the app.

You can choose to not allow access to the admin page in the user config.


Default user config
-------------------

The default values of the user configuration of *WebDocBook* are the followings
(in [INI format](http://en.wikipedia.org/wiki/INI_file)):

```ini
[userconf]
app_name=WebDocBook
timezone="Europe/London"
default_language=en
app_icon=icons/webdocbook-24-white.png
app_favicon=icons/favicon.ico
app_icon_precomposed=icons/webdocbook-57.png
app_icon_precomposed_sized[144x144]=icons/webdocbook-144.png
app_icon_precomposed_sized[114x114]=icons/webdocbook-114.png
app_icon_precomposed_sized[72x72]=icons/webdocbook-72.png
show_rss=1
show_vcs=1
show_wip=1
expose_admin=1
readme_filename='README.md'
index_filename='INDEX.md'
assets_directory='assets'
wip_directory='wip'
minify_assets=0         ;; experimental
merge_assets=0          ;; experimental

```


----
**Copyleft (ↄ) 2008-2017 [Pierre Cassat & contributors](http://webdocbook.com/)** - Paris, France - Some rights reserved.

Scripts are licensed under the [GNU General Public License version 3](http://www.gnu.org/licenses/gpl.html).

Contents are licensed under the [Creative Commons - Attribution - Share Alike - Unported - version 3.0](http://creativecommons.org/licenses/by-sa/3.0/) license.
