# webtrees-geneajaubart

Please note that this file is an extension of the one contained in the standard 
**[webtrees](http://webtrees.net)** project, so please read the latter carefully
beforehand. You can find a copy on the [Github repository](https://github.com/fisharebest/webtrees).

This documentation focuses specifically on the customisations and modules 
implemented for the [GeneaJaubart website](https://genea.jaubart.com/wt/).

## Contents

* [License](#license)
* [Introduction](#introduction)
* [List of MyArtJaub modules](#list-of-myartjaub-modules)
* [General notes](#general-notes)
* [System requirements](#system-requirements)
* [Installation](#installation)
* [Upgrading](#upgrading)
* [Issues / Security](#issues--security)
* [Contacts](#contacts)


### License

* **webtrees-geneajaubart: webtrees for the GeneaJaubart website**
* Copyright (C) 2009 to 2023 Jonathan Jaubart.
* Derived from **webtrees** - Copyright (C) 2010 to 2023  webtrees development team.
* Derived from PhpGedView - Copyright (C) 2002 to 2010  PGV Development Team.

This program is free software; you can redistribute it and/or modify it under the
terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later version.

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

Following the further code evolutions in the version 1.7.0 of **webtrees**, I decided
to split the library part of my code from the main **webtrees-geneajaubart** package, 
as well as renaming the modules from the too generic Perso prefix, to a more *branded*
name: MyArtJaub (a rather bad pun on my surname...). 

The version 2 of **webtrees** brought a major change of the mainstream code and 
architecture, which forced me to refactor and rewrite a large chunk of the library and 
the modules. Because of some dependencies and frequent upstream refactoring, I decided 
to skip the 2.0 branch, and wait for the 2.1 branch to provide a new version. Some 
features have been dropped, and their future implementation partly depend on whether 
some capabilities are (re-)introduced in **webtrees**.

My personal and professional constraints have not allowed me to provide the same level
of support as I used to, nevertheless I have always wished to share my changes 
with the general **webtrees** audience. Initially maintained in an SVN repository, all 
my code is now available in the current GitHub repositories.

Please read carefully the instructions below, as some modules require changes in the core
code, and cannot be just added to a standard **webtrees** installation.

*Jonathan Jaubart*

### List of MyArtJaub modules

Mandatory modules:

* **MyArtJaub Hooks** (`myartjaub_hooks`)
  * Allows hooking MyArtJaub modules in core code more easily.

Available modules:

* **MyArtJaub Administrative Tasks** (`myartjaub_admintasks`)
  * Runs administrative tasks on a scheduled manner.
* **MyArtJaub Certificates** (`myartjaub_certificates`)
  * Alternative management of certificates supporting sources.
* **MyArtJaub Geographical Dispersion** (`myartjaub_geodispersion`)
  * Provide geographical dispersion analysis on Sosa ancestors.  
* **MyArtJaub Miscellaneous Extensions** (`myartjaub_misc`)
  * Placeholder module for miscellaneous extensions.
* **MyArtJaub Patronymic Lineage** (`myartjaub_patronymiclineage`)
  * Alternative to Branches page (created before the latter).
* **MyArtJaub Sosa** (`myartjaub_sosa`)
  * Module to manage Sosa ancestors, and provide statistics.
* **MyArtJaub Sources** (`myartjaub_issourced`)
  * Provides information about the level and quality of sourced for records.
* **MyArtJaub Welcome Block** (`myartjaub_welcome_block`)
  * Merge of standard welcome and login blocks, with display of Matomo statistics

Other modules in separate repositories:

* **MyArtJaub Rural theme** ([jon48/webtrees-theme-rural](https://github.com/jon48/webtrees-theme-rural))
  * Custom theme, first designed for PhpGedView, using brown tones, and a delimited frame.
* **MyArtJaub Geographical Data for France** ([jon48/webtrees-mod-maj-geodata-france](https://github.com/jon48/webtrees-mod-maj-geodata-france))
  * Provide geographical data for use in the Geographical Dispersion module, for the France scope.
* **MyArtJaub Translation Tool** ([jon48/webtrees-mod-translationtool](https://github.com/jon48/webtrees-mod-translationtool))
  * A development utility to report on the status of translations in MyArtJaub modules.

### General notes

Please note that the modules are translated in English and French only. Other
languages will display English texts where no translation is available in the
standard **webtrees**.

Translations files are located under each module folder. You can then use the 
`/modules_v4/*module_name*/resources/lang/fr/messages.php` file as a template to translate 
missing expressions in other languages.

**webtrees-geneajaubart** relies heavily on the **webtrees-lib** library for 
most of its code. The latter then needs to be included, which can be done through
the standard composer commands.

**webtrees-geneajaubart** is not guaranteed to work nicely with other custom
modules or themes.

Even though they are supposed to be catered for, standard themes other than
the Rural theme might present weird displays or alignments. Please contact
[Jonathan Jaubart](#contacts) to report it.

### System requirements

**webtrees-geneajaubart** shares the same requirements and system configuration as a standard **webtrees** installation.

The MyArtJaub Sosa module has limited features when running on a SQLite database.

For a correct installation directly from the source code, you need to have **[composer](https://getcomposer.org/)** and 
**[npm](https://www.npmjs.com/)** installed on your computer.

### Installation

The installation is similar to the standard **webtrees** one.

You need however to select the modules you wish to use. They are two ways to do so:

* Either install the whole code, then enable only the ones required in the module 
administration page;
* Or not copy the corresponding `myartjaub_` folders under the `/modules_v4/` folder.

**Please remember that the `myartjaub_hooks` is required for most of the modules.**

Steps:

1. Download latest stable version from the [webtrees-geneajaubart Github repository](https://github.com/jon48/webtrees-geneajaubart/releases/latest).
2. Unzip the files and then upload them to an empty folder on your web server.
3. If you do not want some modules, delete them from the `/modules_v4/` folder 
(except `myartjaub_hooks`), or append the folder name with `.disabled`.
4. Open your web browser and type the URL for your **webtrees** site (for example,
   ``https://www.yourserver.com/webtrees`` into the address bar.
5. The **webtrees** setup wizard will start automatically. Simply follow the steps,
   answering each question as you proceed.

You should now have a pretty much standard installation of **webtrees**, and you can refer 
to the main documentation to set your preferences.

Further configuration might be required for specific modules.


### Upgrading

* **Automatic upgrade**

The automatic upgrade process  used by **webtrees** is not integrated with the MyArtJaub modules,
hence cannot be used with **webtrees-geneajaubart** (even though the logic has not been
removed, and you will receive a notification a new version is available for mainstream **webtrees**). 
Therefore, **DO NOT USE the automatic upgrade mechanism**.

* **Manual upgrade**


1. Take a backup of your installation (follow standard backup procedure).
2. Download the latest version of **webtrees-geneajaubart** available from 
   [webtrees-geneajaubart Github repository](https://github.com/jon48/webtrees-geneajaubart/releases/latest)
3. While you are in the middle of uploading the new files,
   a visitor to your site would encounter a mixture of new and old files. This
   could cause unpredictable behavior or errors. To prevent this, create the
   file **data/offline.txt**. While this file exists, visitors will see a
   “site unavailable - come back later” message.
4. Unzip the .ZIP file, and upload the files to your web server, overwriting the existing files.
5. Delete the file **data/offline.txt**.


* **Upgrading from version 1.7**

No version **2.0** of **webtrees-geneajaubart** has been published. However, versions **1.7** and **2.1** 
require different versions of PHP, without any overlap (PHP 7.3 is the latest version supported by 
**webtrees-geneajaubart** 1.7, and PHP 7.4 is the minimum version required by **webtrees-geneajaubart** 2.1).

As a consequence, when upgrading from **webtrees-geneajaubart** 1.7 to 2.1, it is required to upgrade 
the version of PHP at the same time to PHP 7.4 or above.

The possible migration paths are:

- Put the website offline, backup your installation, copy **webtrees-geneajaubart** 2.1, upgrade PHP to 
version 7.4 (or above), start your website. (only choose this option if you are confident with 

- Similarly to the former, and depending on your setup, you can as well have a second website ready with 
PHP 7.4 (or above) and the files for **webtrees-geneajaubart** 2.1, and enable it after shutting down the
website with version 1.7, making sure to point to the same database.

- Alternatively, and probably the safest approach, you can upgrade in 2 steps: manually upgrade your 
**webtrees-geneajaubart** 1.7 to the standard **webtrees** 2.0 following the [standard procedure](https://webtrees.net/upgrade/manual/),
keeping the same version of PHP. When you have confirmed your website is working fine, then you can switch
your version of PHP to 7.4 (or above), test again your website with version 2.0. Finally, you can proceed
with the manual installation of **webtrees-geneajaubart** 2.1 on top of **webtrees** 2.0.

Please make sure to carefully read the [official release notes for 2.0](https://webtrees.net/upgrade/2.0/),
and apply the required configuration changes when upgrading from 1.7 to 2.0/2.1. 

### Issues / Security

Issues should be raised in the [GitHub repository](https://github.com/jon48/webtrees-geneajaubart/issues)
for **jon48/webtrees-geneajaubart**.

A [security policy document](SECURITY.md) has been issued for this repository.

### Contacts

General questions on the standard **webtrees** software should be addressed to the
[official forum](https://www.webtrees.net/index.php/forum)

You can contact the author (Jonathan Jaubart) of the **webtrees-geneajaubart**
project through his personal [GeneaJaubart website](https://genea.jaubart.com/wt/) (link
at the bottom of the page).

