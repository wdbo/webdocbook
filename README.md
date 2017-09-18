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

Docker container
----------------

A [Dockerfile](https://docs.docker.com/engine/reference/builder/) is designed
to build a container with the full sources of WebDocBook. The file will create
a local docker image with your local sources, install the application in the
container and expose your WebDocBook app with both HTTP and HTTPS protocols.
You can configure some special ports mapping at runtime and mount in your
container some local volumes of Markdown files to use.

Below are the steps to build your container:

1.  prepare a Docker image with an installed application

        docker build -t wdbo_server .

    when building the image, you can customize environment variables using
    the `--build-arg <var>=<val>` argument ; a full list of available variables
    is present at the top of the `Dockerfile`

2.  create a new container based on that image (in the example
    below, the default 80 and 443 ports of the container are mapped
    to custom ones on the host and a special local volume is mounted
    instead of default WebDocBook data):

        docker run -d \
            --name wdbo_server_c \
            -p 8080:80 \
            -p 8443:443 \
            -v /path/to/your/md/files:/var/www/webdocbook/www \
                wdbo_server

3.  optionnaly, you can connect into your container with the "www-data" user:

        docker exec -ti -u www-data wdbo_server_c bash

4.  optionnaly, you can view the Apache and PHP logs with:

        docker logs -f wdbo_server_c

For development, you can mount the whole local sources as a volume of
the container to keep your work on it:

    docker run -d \
        --name wdbo_server_c \
        -p 8080:80 \
        -p 8443:443 \
        -v $(pwd):/var/www/webdocbook \
            wdbo_server

In this case, you will probably need to change the owner of the file-system
to match the server user of the container ("www-data" with UID/GID 1000).

License
-------

**WebDocBook** is an open-source software released under a
[GNU General Public License version 3](http://github.com/wdbo/webdocbook/blob/master/LICENSE).
You can freely download it, use it or distribute it as long as you stay in the license
conditions. See the `LICENSE` file for more info.
