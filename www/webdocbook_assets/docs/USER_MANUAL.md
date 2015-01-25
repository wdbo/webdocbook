## WebDocbook user manual


### Organization

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

### WebDocBook pages structure

Each page of *WebDocBook* has a top menu that, in most cases depending on your device, will stay
static at the top of your window. It contains the global menu of your first hierarchy chapters
and some tools to go to the top and bottom of the page, and a menu of your current contents.

Then, if you are seeing a directory, the first table lists its structure: pages and sub-directories.
The content of the current directory "README" file is shown below if found.

Clicking on a page's link will let you visualize the content of the page.
