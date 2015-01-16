# WebDocbook : ROADMAP

This file is the "ROADMAP" of the application : it lists all features wanted. Each line 
begining by "DONE" is a feature already developed. Other lines are still TO BE developed.

This file is written in French.


## Liens utiles

<http://fletcherpenney.net/multimarkdown/cms/>

<http://httpd.apache.org/docs/2.2/fr/mod/mod_autoindex.html>


## Spécifications

- menu des pages et sous-répertoires
- "TOC" globale
- fichiers globaux (utilisés par défaut pour toutes les pages si non redéfinis) : copyright info, authors list, aside, changelog
- page d'accueil listant les dernières modifs, les infos système éventuelles, des liens vers d'autres sites (config)
- URL routing:
    -   `*/notes`: build a page referencing all footnotes from this position and through its children,
    -   `*/glossary`: build a page referencing all glossary entries from this position and through 
        its children,
    -   `*/bibliography`: build a page referencing all bibliographic entries from this position and 
        through its children,
- Files infos:
    -   `*.copyright.md`: a copyright, license or protection information for the file content,
    -   `*.author(s).md`: an information about the author(s) of the file,
    -   `*.changelog.md`: the evolutions information of the file.

- DONE: "INDEX.md" comme vrai index
- DONE: "README.md" comme texte de l'index listing
- DONE: faire un "sitemap.xml" (redirigé via Apache)
- DONE: navigation: previous / next / head 
- DONE: interface riche avec Bootstrap (HTML5)
- DONE: outil de recherche (?)
- DONE: gestion des traductions avec extension ".LN.md"
- DONE: FancyIndexing des contenus (icônes ? - icônes de Bootstrap)
- DONE: page 404
- DONE: RSS


## Technique

- toutes les variables de config définissables dans "htaccess"

- DONE: setup en htaccess ou virtual host
- DONE: package seul avec dépendances via Composer et Bower
- DONE: pouvoir gérer les pages via un GIT => faire la doc
- DONE: système de cache ? => cache de Twig


## Idées construction pages

- <http://docs.xfce.org/xfce/thunar/bulk-renamer/start> : boîte à outils dont les liens n'apparaissent qu'au MouseOver, sympa

