# Docbook : ROADMAP

This file is the "ROADMAP" of the application : it lists all features wanted. Each line 
begining by "DONE" is a feature already developed. Other lines are still TO BE developed.

This file is written in French.


## Liens utiles

<http://fletcherpenney.net/multimarkdown/cms/>

<http://httpd.apache.org/docs/2.2/fr/mod/mod_autoindex.html>


## Spécifications

- DONE: "INDEX.md" comme vrai index
- DONE: "README.md" comme texte de l'index listing
- faire un "sitemap.xml" (redirigé via Apache)
- navigation: previous / next / head / menu des pages et sous-répertoires
- DONE: interface riche avec Bootstrap (HTML5)
- DONE: outil de recherche (?)
- DONE: gestion des traductions avec extension ".LN.md"
- DONE: FancyIndexing des contenus (icônes ? - icônes de Bootstrap)
- "TOC" globale
- fichiers globaux (utilisés par défaut pour toutes les pages si non redéfinis) : copyright info, authors list, aside, changelog
- DONE: page 404
- page d'accueil listant les dernières modifs, les infos système éventuelles, des liens vers d'autres sites (config)
- RSS


## Technique

- DONE: setup en htaccess ou virtual host
- DONE: package seul avec dépendances via Composer et Bower
- DONE: pouvoir gérer les pages via un GIT => faire la doc
- DONE: système de cache ? => cache de Twig
- toutes les variables de config définissables dans "htaccess"


## Idées construction pages

- <http://docs.xfce.org/xfce/thunar/bulk-renamer/start> : boîte à outils dont les liens n'apparaissent qu'au MouseOver, sympa

