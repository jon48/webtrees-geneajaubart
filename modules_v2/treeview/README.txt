Here is a new module for displaying and printing a genealogic tree written by Daniel Faivre (contact available by webtrees team bug report tool).

It widely improve performances and get rid of bugs reported with the older tree module.

Functionnalities implemented :
- display the treeview in the individual page AND as interactive treeview page (it's both a chart and a report, but cannot appear in the charts menu);
- automatic sizing in the html parent element (fixed if fixed, and auto-sizing inside if the container has not 1 or 2 fixed size);
- display details with direct links to persons and families;
- dynamically load required persons boxes after drag, far more quickly than with the previous tree module (less Ajax requests);
- CSS for printing tree;
- switch fixed/compact tree;
- show / hide birth and death dates;
- memorize preferences;
- align left, right, and center on root Person;
- style by default, overided by the current theme style CSS section for TreeView;
- custom screen and print TreeView styles over the default and the theme's styles (WYSIWYG);
- integration with Lightbox module;
- load all visible boxes / close all boxes buttons;
- load full resolution images instead of thumbnails for printing;
- help file, WHICH CONTAIN IMPORTANT INFORMATIONS about these features.

Installation : uncompress the files in the /WT_MODULES_DIR/ directory and activate the module in webtrees.

Note : developped for and tested with webtrees trunk rev 10996

-----
Known bugs :
- tree borders sometimes not drawn in IE 6;
- css removal not redrawn in IE 6 (bug IE 6). That cause default style not to be activated immediately after changing for default style,
  but just after the following action that cause a DOM refresh;
- minor position issue for submenu and handler in the toolbox with IE6;

-----
Roadmap:(TODO list)
- workarounds for IE bugs;
- include in the webtree distribution;
- styles for existing themes;
- some translations;
- open all boxes function;

-----
Daniel Faivre
Contact : by https://launchpad.net/~geomaticien
