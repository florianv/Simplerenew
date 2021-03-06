<?php
/**
 * @package   Simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

abstract class SimplerenewRoute
{
    /**
     * @var array
     */
    protected static $items = null;

    /**
     * Get a raw url for the selected view,layout
     *
     * @param string $view
     * @param string $layout
     *
     * @return string|array
     */
    public static function get($view, $layout = null)
    {
        if ($query = self::getQuery($view, $layout)) {
            return 'index.php?' . http_build_query($query);
        }
        return null;
    }

    /**
     * Get the query array for the selected view,layout
     *
     * @param string $view
     * @param string $layout
     *
     * @return array
     */
    public static function getQuery($view, $layout = '')
    {
        if (self::$items === null) {
            $menu        = JApplication::getInstance('site')->getMenu();
            self::$items = $menu->getItems(array('component', 'access'), array('com_simplerenew', true));
        }

        $query = array(
            'option' => 'com_simplerenew',
            'view'   => $view
        );
        if ($layout) {
            $query['layout'] = $layout;
        }

        // Look for an existing menu item that matches the requests
        foreach (self::$items as $item) {
            $mView   = empty($item->query['view']) ? '' : $item->query['view'];
            $mLayout = empty($item->query['layout']) ? '' : $item->query['layout'];

            if ($mView == $view && $mLayout == $layout) {
                $query['Itemid'] = $item->id;
                if (!empty($query['view']) && $query['view'] == $view) {
                    unset($query['view']);
                }
                if (!empty($query['layout']) && $query['layout'] == $layout) {
                    unset($query['layout']);
                }
                break;
            } elseif ($mView == 'account' && empty($mLayout)) {
                // The account info view can always be used as a base
                $query['Itemid'] = $item->id;
            }
        }

        return $query;
    }
}
