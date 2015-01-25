WebDocbook user manual
======================

Global organization
-------------------

Each page of *WebDocBook* has a top menu that, in most cases depending on your device, will stay
static at the top of your window. It contains the global menu of your first hierarchy chapters
and some tools to go to the top and bottom of the page, and a menu of your current contents.

Then, if you are seeing a directory, the first table lists its structure: pages and sub-directories.
The content of the current directory "README" file is shown below if found.

Clicking on a page's link will let you visualize the content of the page.

### Chapter structure

A classic *WebDocBook* directory organization should be:

    | chapter-name/
    | ------------- README.md           // the first file shown loading the directory
    | ------------- PAGE.md             // a Markdown content file (page 1)
    | ------------- OTHER-PAGE.md       // another Markdown content file (page 2)
    | ------------- OTHER-PAGE.fr.md    // the french translation of page 2
    | ------------- ...                 // as many pages as you like
    | ------------- sub-chapter1/       // a sub-directory containing a sub-chapter
    | --------------------------- ...   // your sub-chapter 1 pages and assets ...
    | ------------- sub-chapter2/       // a sub-directory containing another sub-chapter
    | --------------------------- ...   // your sub-chapter 2 pages and assets ...
    | ------------- assets/             // a directory containing your medias
    | --------------------- image1.png  
    | --------------------- image2.gif  
    | --------------------- ...  
    | ------------- wip/                // a directory containing your work-in-progress contents
    | ------------------ WorkInProgress-page.md
    | ------------------ ...  

### Routing

| URI                       | Scope             | Usage                                                                                     |
|---------------------------|:-----------------:|-------------------------------------------------------------------------------------------|
| `*/sitemap`               | chapters          | build a sitemap XML from this position and through its children                           |
| `*/rss`                   | chapters & files  | build an RSS feed of contents from this position and through its children                 |
| `*/search?s=A&lang=LN`    | chapters & files  | process a search of string "A" in contents from this position and through its children    |
| `*/?lang=LN`              | chapters & files  | get this content in LN language if present                                                |
| `*/download`              | files             | download the original file of the page                                                    |
| `*/htmlonly`              | files             | get the plain HTML version of the page                                                    |
| `*/plain`                 | files             | get the plain text version of the page                                                    |


Special files & directories
---------------------------

### Chapter contents

| Name          | Type      | Usage                                                                                 |
|---------------|:---------:|---------------------------------------------------------------------------------------|
| `INDEX.md`    | MD file   | homepage of a chapter: content is displayed instead of the chapter files listing      |
| `README.md`   | MD file   | introduction of a chapter: content is displayed below the chapter files listing       |
| `assets`      | directory | medias directory: contents are web-accessible                                         |
| `wip`         | directory | work-in-progress: contents are not listed                                             |


### Meta-files

| Name          | Type          | Usage                                             |
|---------------|:-------------:|---------------------------------------------------|
| `.references` | WDB-MetaFile  | markdown references added to each chapter's page  |
| `.meta`       | WDB-MetaFile  | markdown meta-data added to each chapter's page   |
| `.header`     | MD file       | header added to each chapter's page rendering     |
| `.footer`     | MD file       | footer added to each chapter's page rendering     |

### Meta-data

| Name          | Usage                                             |
|---------------|---------------------------------------------------|
| `notoc`       | disable table of contents                         |
| `maths`       | enable mathematics JS library                     |
| `book_page`   | add a pager at the bottom of each chapter's page  |


*[MD]: Markdown (file using the markdown syntax)
*[WDB-MetaFile]: WebDocBook meta file (simple text file with comments begin by a sharp)
