<?php
/**
 * @package   com_simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2013 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

if (!defined('SIMPLERENEW_LOADED')) {
    define('SIMPLERENEW_LOADED', 1);
    define('SIMPLERENEW_ADMIN', JPATH_ADMINISTRATOR . '/components/com_simplerenew');
    define('SIMPLERENEW_SITE', JPATH_SITE . '/components/com_simplerenew');
    define('SIMPLERENEW_MEDIA', JPATH_SITE . '/media/com_simplerenew');
    define('SIMPLERENEW_LIBRARY', SIMPLERENEW_ADMIN . '/library');

    // Initialise Simplerenew application
    require_once SIMPLERENEW_LIBRARY . '/init.php';
}

if (!defined('FOF_INCLUDED')) {
    require_once JPATH_LIBRARIES . '/fof/include.php';
}