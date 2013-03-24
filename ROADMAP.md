# Docbook : ROADMAP


## Liens utiles

<http://fletcherpenney.net/multimarkdown/cms/>
<http://httpd.apache.org/docs/2.2/fr/mod/mod_autoindex.html>


## Spécifications

- "INDEX.md" comme vrai index
- DONE: "README.md" comme texte de l'index listing
- faire un "sitemap.xml" (redirigé via Apache)
- navigation: previous / next / head / menu des pages et sous-répertoires
- DONE: interface riche avec Bootstrap (HTML5)
- outil de recherche (?)
- gestion des traductions avec extension ".LN.md"
- DONE: FancyIndexing des contenus (icônes ? - icônes de Bootstrap)
- "TOC" globale
- fichiers globaux (utilisés par défaut pour toutes les pages si non redéfinis) : copyright info, authors list, aside, changelog
- DONE: page 404
- page d'accueil listant les dernières modifs, les infos système éventuelles, des liens vers d'autres sites (config)
- RSS


## Technique

- DONE: setup en htaccess ou virtual host
- DONE: package seul avec dépendances via Composer et Bower
- pouvoir gérer les pages via un GIT
- DONE: système de cache ?
- toutes les variables de config définissables dans "htaccess"


## Idées construction pages

- <http://docs.xfce.org/xfce/thunar/bulk-renamer/start> : boîte à outils dont les liens n'apparaissent qu'onMouseOver, sympa

