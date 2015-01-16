Virtual host with *Apache*
==========================

A default `.htaccess` file could be:

    # no indexing
    Options -Indexes +FollowSymLinks
    
    # setting the error pages
    ErrorDocument 404 /index.php?not_found
    ErrorDocument 403 /index.php?forbidden
    ErrorDocument 500 /index.php?error
    
    # setting the default DocBook markdown handler for each `.md` files
    # and the INCLUDES functionality
    <IfModule mod_mime.c>
        AddType text/html .md
    </IfModule>
    
    # the rewrite URLs rules
    <IfModule mod_rewrite.c>
        RewriteEngine On
        #RewriteBase /
    
        # no access to anything beginning with a dot
        RewriteRule ^(.*/)?\.(.*)/ - [F]
    
        # special rule for `sitemap.xml` (one per dir)
        RewriteRule ^(.*)sitemap.xml index.php?$1/sitemap
    
        # skip all this for internal assets
        RewriteCond %{REQUEST_URI} ^(.*)webdocbook_assets/(.*)
        RewriteRule "." - [skip=100]
    
        # if the dir exists, handle it by index.php anyway
        RewriteCond %{REQUEST_FILENAME} -d
        RewriteRule ^(.*) index.php?$1
    
        # `index.php` will handle all requests
        RewriteRule ^(.*) index.php?$1
    </IfModule>
