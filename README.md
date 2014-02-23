# webtrees-geneaujaubart

Please note that this file is an extension of the one contained in the standard 
**[webtrees](http://webtrees.net)** project, so please read the latter carefully
beforehand. You can find a copy on the [Github repository](https://github.com/fisharebest/webtrees).

This documentation focuses specifically on the customisations and modules 
implemented for the [GeneaJaubart website](http://genea.jaubart.com/wt/).

## Contents

* [License](#license)
* [Introduction](#introduction)
* [List of Perso modules](#list-of-perso-modules)
* [General notes](#general-notes)
* [System requirements](#system-requirements)
* [Installation](#installation)
* [Upgrading](#upgrading)
* [Contacts](#contacts)


### License

* **webtrees-geneajaubart: webtrees for the GeneaJaubart website**
* Copyright (C) 2011 to 2014 Jonathan Jaubart.
* Derived from **webtrees** - Copyright (C) 2010 to 2014  webtrees development team.
* Derived from PhpGedView - Copyright (C) 2002 to 2010  PGV Development Team.

This program is free software; you can redistribute it and/or modify it under the
terms of the GNU General Public License as published by the Free Software
Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

See the GPL.txt included with this software for more detailed licensing
information.


### Introduction

Initially user of PhpGedView, I started developing some customisations and personal 
modules in 2009 in order either to fill some gaps in features or to adapt the software
to my liking. This is when the Rural theme was first created for instance.

When the main PGV developers moved to create **webtrees**, I slowly migrated my code 
to the new platform, taking advantage of the evolved architecture to refactor some of
the modules.

My personal and professional constraints have not allowed me to provide the same level
of support as I used to, nevertheless I have always wished to share my changes 
with the general **webtrees** audience. I was maintaining an SVN repository on Assembla,
but since the migration of **webtrees** to Github, I have as well created the current
Git repository, which should simplify the upgrade process.

Please read carefully the instructions below, as some modules need changes in the core
code, hence cannot be just added to a standard **webtrees** installation.

*Jonathan Jaubart*

### List of Perso modules

Themes:

* **Rural theme**

Mandatory modules:

* **Perso Config** (`perso_config`)
  * Central configuration module for all Perso Modules.
* **Perso Hooks** (`perso_hooks`)
  * Allows hooking Perso modules in core code more easily.

Available modules:

* **Perso Administrative Tasks** (`perso_admintasks`)
  * Runs administrative tasks on a scheduled manner.
* **Perso Certificates** (`perso_certificates`)
  * Alternative management of certificates supporting sources.
* **Perso General** (`perso_general`)
  * Placeholder module for miscellaneous integrations.
* **Perso Geographical Dispersion** (`perso_geodispersion`)
  * Provide geographical dispersion analysis on Sosa ancestors.
* **Perso Patronymic Lineage** (`perso_patronymiclineage`)
  * Alternative to Branches page (created before the latter).
* **Perso Sources** (`perso_issourced`)
  * Provides information about the level and quality of sourced for records.
* **Perso Sosa** (`perso_sosa`)
  * Module to manage Sosa ancestors, and provide statistics.
* **Perso Welcome Block** (`perso_welcome_block`)
  * Merge of standard welcome and login blocks, with display of Piwik statistics
  
### General notes

Please note that the modules are translated in English and French only. Other
languages will display English texts where no translation is available in the
standard **webtrees**.

You can use the `/data/language/fr.php` file as a template to translate missing
expressions in other languages.

**webtrees-geneajaubart** is not guaranteed to work nicely with other custom
modules or themes.

Even though they are supposed to be catered for, standard themes other than
the Rural theme might present weird displays or alignments. Please contact
[Jonathan Jaubart](#contacts) to report it.

### System requirements

No additional server configuration or requirements are necessary compared to a
standard **webtrees** installation.


### Installation

The installation is similar to the standard **webtrees** one.

You need however to select the modules you wish to use. They are two ways to do so:

* Either install the whole code, then enable only the ones required in the module 
administration page;
* Or not copy the corresponding `perso_` folders under the `/modules_v3/` folder.

**Please remember that the `perso_config` and `perso_hooks` are required for most 
of the modules.**

Steps:

1. Download latest stable version from the [webtrees-geneajaubart Github repository](https://github.com/jon48/webtrees-geneajaubart/archive/master.zip).
2. Unzip the files.
3. If you do not want some modules, delete them from the `/modules_v3/` folder 
(except `perso_config` and `perso_hooks`).
4. Upload the files to an empty directory on your web server.
5. Open your web browser and type the URL for your **webtrees** site (for example,
   [http://www.yourserver.com/webtrees](http://www.yourserver.com/webtrees)) into
   the address bar.
6. The **webtrees** setup wizard will start automatically. Simply follow the steps,
   answering each question as you proceed.
7. Upload or create a GEDCOM file.

You should now have a pretty much standard installation of **webtrees**.

Some additional steps are required to complete the specific **webtrees-geneajaubart**
steps:

1. Log in, and go to the administrative page.
2. In the right panel, click on the *Hooks (Perso)* link. That will register and enable
the modules hooks.
3. In the right panel, click on the *Central Perso configuration* link. Depending on
the enabled modules, configurations items will be displayed, organised by modules.

The basis for **webtrees-geneajaubart** is now complete. Further configuration might be
required for specific modules.


### Upgrading

The automatic upgrade process introduced in **webtrees** is not integrated with modules,
hence cannot be used with **webtrees-geneajaubart** (even though the logic has not been
removed). Hence, **DO NOT USE the automatic upgrade mechanism**.

1. Take a backup of your installation (follow standard backup procedure).
2. Download the latest version of **webtrees-geneajaubart** available from
   [webtrees-geneajaubart Github repository](https://github.com/jon48/webtrees-geneajaubart/archive/master.zip)
3. While you are in the middle of uploading the new files,
   a visitor to your site would encounter a mixture of new and old files.  This
   could cause unpredictable behaviour or errors.  To prevent this, create the
   file **data/offline.txt**.  While this file exists, visitors will see a
   “site unavailable - come back later” message.
4. If you do not want some modules, delete them from the `/modules_v3/` folder 
(except `perso_config` and `perso_hooks`).
4. Upload the files to your web server, overwriting the existing files.
5. Delete the file **data/offline.txt**.


### Contacts

General questions on the standard **webtrees** software should be addressed to the
[official forum](http://www.webtrees.net/index.php/forum)

You can contact the author (Jonathan Jaubart) of the **webtrees-geneajaubart**
project through his personal [GeneaJaubart website](http://genea.jaubart.com/wt/) (link
at the bottom of the page).

