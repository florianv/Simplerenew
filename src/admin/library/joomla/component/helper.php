<?php
/**
 * @package   Simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

abstract class SimplerenewComponentHelper extends JComponentHelper
{
    public static function getParams($option = 'com_simplerenew', $strict = false)
    {
        return parent::getParams($option, $strict);
    }
}
