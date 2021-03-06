Autoloading Classes
===================
It is the goal to make all used classes in the library autoloading. The core of Simplerenew is
in /library/simplerenew and all these classes MUST follow PSR4 autoloading standards. This means:

#### The directory tree defines the namespace
The [psr4autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md)
is a slightly modified version pulled from the github repository
([follow the link](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md)).
It is a general purpose autoloader that can be used for any set of classes that adhere to the psr4
standard. It is used to register /library/simplerenew as the base *Simplerenew* namespace. For example,
/library/simplerenew/Api is the root of the Simplerenew\Api sub-namespace.

#### Case MaTtErS!
+ All directories MUST follow initial caps standard
+ All file names MUST match the case of their class definitions
    + class AbstractApiBase == AbstractApiBase.php

CMS Abstracting/Autoloading
===========================
The /library folder is also used to abstract classes used from the hosting CMS. These folders
will use idiosyncratic autoloading structures suitable to the hosting CMS.

All subclass names MUST begin with Simplerenew.

Whenever possible, if it makes any sense at all, classes used from the CMS should be first subclassed in the appropriate directory and
used via the new subclass

#### Joomla classes
Joomla uses a camelCase standard for it's classes. The autoloading structure uses each uppercase
character after the Simplerenew prefix to determine the directory tree. Some examples:

+ SimplerenewModel : model.php (in the root directory) - inherits from JModelLegacy
+ SimplerenewModelAdmin : model/admin.php - inherits from JModelAdmin


+ Current known Exceptions
    + JDocument
    + JHtml
    + JHtmlSidebar
    + JRoute
    + JSubMenuHelper
    + JText

Built-in style sheets
=====================
We are providing a defined set of front-end themes for selection. Theme stylesheets are in the 'themes'
folder of main media css folder (in Joomla this would be /media/com_simplerenew/css).
Each css file should be named so that their main feature is understood.
e.g. 'blue.css'. The built-in theming can be turned off entirely allowing the template full control. Note that this might
also require template overrides for all Simplerenew views.

+ Joomla
    + Adding a new .css file in the themes folder will automatically appear in the dropdown selector
    + You must also add a language string for a new theme - COM_SIMPLERENEW_OPTION_STYLESHEET_&lt;name&gt;
    + All css files are loaded using standard Joomla media loading. Templates can therefore employ any matching override files.
