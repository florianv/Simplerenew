<?php
/**
 * @package   com_simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2013 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

class Com_SimplerenewInstallerScript
{
    /**
     * @var array Obsolete folders/files to be deleted - use admin/site/media for location
     */
    protected $obsoleteItems = array(
        '/admin/library/configuration.json',
        '/media/js/tabs.js',
        '/admin/library/simplerenew/Notify/Handler/None.php'
    );

    /**
     * @var array Related extensions required or useful with the component
     *            type => [ (folder) => [ (element) => [ (publish), (uninstall), (ordering) ] ] ]
     */
    protected $relatedExtensions = array(
        'plugin' => array(
            'system' => array(
                'simplerenew' => array(1, 1, null)
            ),
            'user'   => array(
                'simplerenew' => array(1, 1, null)
            )
        )
    );

    /**
     * @var JInstaller
     */
    protected $installer = null;

    /**
     * @var SimpleXMLElement
     */
    protected $manifest = null;

    /**
     * @var string
     */
    protected $mediaFolder = null;

    /**
     * @var array
     */
    protected $messages = array();

    /**
     * @param JInstallerAdapterComponent $parent
     *
     * @return void
     */
    public function initprops($parent)
    {
        $this->installer = $parent->get('parent');
        $this->manifest  = $this->installer->getManifest();
        $this->messages  = array();

        if ($media = $this->manifest->media) {
            $path              = JPATH_SITE . '/' . $media['folder'] . '/' . $media['destination'];
            $this->mediaFolder = $path;
        }
    }

    /**
     * @param JInstallerAdapterComponent $parent
     *
     * @return bool
     */
    public function install($parent)
    {
        return true;
    }

    /**
     * @param JInstallerAdapterComponent $parent
     *
     * @return bool
     */
    public function discover_install($parent)
    {
        return $this->install($parent);
    }

    /**
     * @param JInstallerAdapterComponent $parent
     *
     * @return void
     */
    public function uninstall($parent)
    {
        $this->initprops($parent);
        $this->uninstallRelated();
        $this->showMessages();
    }

    /**
     * @param JInstallerAdapterComponent $parent
     *
     * @return bool
     */
    public function update($parent)
    {
        return true;
    }

    /**
     * @param string                     $type
     * @param JInstallerAdapterComponent $parent
     *
     * @return bool
     */
    public function preFlight($type, $parent)
    {
        $this->initprops($parent);

        if ($type == 'update') {
            $this->clearUpdateServers();
        }

        return true;
    }

    /**
     * @param string                     $type
     * @param JInstallerAdapterComponent $parent
     *
     * @return void
     */
    public function postFlight($type, $parent)
    {
        $this->setDefaultParams($type);
        $this->installRelated();
        $this->clearObsolete();

        // Temporary check for new ordering field
        // @TODO: Remove by 1st beta
        $db = JFactory::getDbo();
        $db->setQuery('Select name, id, ordering From #__simplerenew_plans order by name, code');
        $list    = $db->loadObjectList();
        $empties = array_filter(
            $list,
            function ($el) {
                return ($el->ordering == 0);
            }
        );
        if (count($empties) == count($list)) {
            foreach ($list as $i => $plan) {
                $plan->ordering = $i+1;
                $db->updateObject('#__simplerenew_plans', $plan, 'id');
            }
            $this->setMessage('Plan ordering has been initialised to alphabetical by Plan Name');
        }
        // END ordering check

        $this->showMessages();
    }

    /**
     * Install related extensions
     *
     * @return void
     */
    protected function installRelated()
    {
        if ($this->relatedExtensions) {
            $installer = new JInstaller();
            $source    = $this->installer->getPath('source');

            foreach ($this->relatedExtensions as $type => $folders) {
                foreach ($folders as $folder => $extensions) {
                    foreach ($extensions as $element => $settings) {
                        $path = $source . '/' . $type;
                        if ($type == 'plugin') {
                            $path .= '/' . $folder;
                        }
                        $path .= '/' . $element;
                        if (is_dir($path)) {
                            $current = $this->findExtension($type, $element, $folder);
                            $isNew   = empty($current);

                            $typeName = trim(($folder ? : '') . ' ' . $type);
                            $text     = 'COM_SIMPLERENEW_RELATED_' . ($isNew ? 'INSTALL' : 'UPDATE');
                            if ($installer->install($path)) {
                                $this->setMessage(JText::sprintf($text, $typeName, $element));
                                if ($isNew) {
                                    $current = $this->findExtension($type, $element, $folder);
                                    if ($settings[0]) {
                                        $current->publish();
                                    }
                                    if ($settings[2] && ($type == 'plugin')) {
                                        $this->setPluginOrder($current, $settings[2]);
                                    }
                                }
                            } else {
                                $this->setMessage(JText::sprintf($text . '_FAIL', $typeName, $element), 'error');
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Uninstall the related extensions that are useless without the component
     */
    protected function uninstallRelated()
    {
        if ($this->relatedExtensions) {
            $installer = new JInstaller();

            foreach ($this->relatedExtensions as $type => $folders) {
                foreach ($folders as $folder => $extensions) {
                    foreach ($extensions as $element => $settings) {
                        if ($settings[1]) {
                            if ($current = $this->findExtension($type, $element, $folder)) {
                                $msg     = 'COM_SIMPLERENEW_RELATED_UNINSTALL';
                                $msgtype = 'message';
                                if (!$installer->uninstall($current->type, $current->extension_id)) {
                                    $msg .= '_FAIL';
                                    $msgtype = 'error';
                                }
                                $this->setMessage(JText::sprintf($msg, $type, $element), $msgtype);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $type
     * @param string $element
     * @param string $folder
     *
     * @return JTable
     */
    protected function findExtension($type, $element, $folder = null)
    {
        $row = JTable::getInstance('extension');

        $terms = array(
            'type'    => $type,
            'element' => ($type == 'module' ? 'mod_' : '') . $element
        );
        if ($type == 'plugin') {
            $terms['folder'] = $folder;
        }
        $eid = $row->find($terms);
        if ($eid) {
            $row->load($eid);
            return $row;
        }
        return null;
    }

    /**
     * Set requested ordering for selected plugin extension
     * Accepted ordering arguments:
     * (n<=1 | first) First within folder
     * (* | last) Last within folder
     * (before:element) Before the named plugin
     * (after:element) After the named plugin
     *
     * @param JTable $extension
     * @param string $order
     *
     * @return void
     */
    protected function setPluginOrder(JTable $extension, $order)
    {
        if ($extension->type == 'plugin' && !empty($order)) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('extension_id, element');
            $query->from('#__extensions');
            $query->where(
                array(
                    $db->qn('folder') . ' = ' . $db->q($extension->folder),
                    $db->qn('type') . ' = ' . $db->q($extension->type)
                )
            );
            $query->order($db->qn('ordering'));

            $plugins = $db->setQuery($query)->loadObjectList('element');

            // Set the order only if plugin already successfully installed
            if (array_key_exists($extension->element, $plugins)) {
                $target = array(
                    $extension->element => $plugins[$extension->element]
                );
                $others = array_diff_key($plugins, $target);

                if ((is_numeric($order) && $order <= 1) || $order == 'first') {
                    // First in order
                    $neworder = array_merge($target, $others);
                } elseif (($order == '*') || ($order == 'last')) {
                    // Last in order
                    $neworder = array_merge($others, $target);
                } elseif (preg_match('/^(before|after):(\S+)$/', $order, $match)) {
                    // place before or after named plugin
                    $place    = $match[1];
                    $element  = $match[2];
                    $neworder = array();
                    $previous = '';

                    foreach ($others as $plugin) {
                        if ((($place == 'before') && ($plugin->element == $element)) || (($place == 'after') && ($previous == $element))) {
                            $neworder = array_merge($neworder, $target);
                        }
                        $neworder[$plugin->element] = $plugin;
                        $previous                   = $plugin->element;
                    }
                    if (count($neworder) < count($plugins)) {
                        // Make it last if the requested plugin isn't installed
                        $neworder = array_merge($neworder, $target);
                    }
                } else {
                    $neworder = array();
                }

                if (count($neworder) == count($plugins)) {
                    // Only reorder if have a validated new order
                    JModelLegacy::addIncludePath(
                        JPATH_ADMINISTRATOR . '/components/com_plugins/models',
                        'PluginsModels'
                    );
                    $model = JModelLegacy::getInstance('Plugin', 'PluginsModel');

                    $ids = array();
                    foreach ($neworder as $plugin) {
                        $ids[] = $plugin->extension_id;
                    }
                    $order = range(1, count($ids));
                    $model->saveorder($ids, $order);
                }
            }
        }
    }

    /**
     * Display messages from array
     *
     * @return void
     */
    protected function showMessages()
    {
        $app = JFactory::getApplication();
        foreach ($this->messages as $msg) {
            $app->enqueueMessage($msg[0], $msg[1]);
        }
    }

    /**
     * Add a message to the message list
     *
     * @param string $msg
     * @param string $type
     *
     * @return void
     */
    protected function setMessage($msg, $type = 'message')
    {
        $this->messages[] = array($msg, $type);
    }

    /**
     * Delete obsolete files and folders
     */
    protected function clearObsolete()
    {
        if ($this->obsoleteItems) {
            $admin = $this->installer->getPath('extension_administrator');
            $site  = $this->installer->getPath('extension_site');

            $search  = array('#^/admin#', '#^/site#');
            $replace = array($admin, $site);
            if ($this->mediaFolder) {
                $search[]  = '#^/media#';
                $replace[] = $this->mediaFolder;
            }

            foreach ($this->obsoleteItems as $item) {
                $path = preg_replace($search, $replace, $item);
                if (is_file($path)) {
                    $success = JFile::delete($path);
                } elseif (is_dir($path)) {
                    $success = JFolder::delete($path);
                } else {
                    $success = null;
                }
                if ($success !== null) {
                    $this->setMessage('Delete ' . $path . ($success ? ' [OK]' : ' [FAILED]'));
                }
            }
        }
    }

    /**
     * @param string $type
     *
     * @return void
     */
    protected function setDefaultParams($type)
    {
        /** @var JTableExtension $table */
        $table = JTable::getInstance('Extension');
        $table->load(array('element' => 'com_simplerenew'));

        $params = new JRegistry($table->params);

        $setParams = ($type == 'install');

        // On initial installation, set some defaults
        if ($setParams) {
            // Set defaults based on config.xml construction
            $path = $this->installer->getPath('source') . '/admin/config.xml';
            if (file_exists($path)) {
                $config      = new SimpleXMLElement($path, 0, true);
                $defaultFont = 'none';

                // Look for the first fontFamily entry that isn't 'none'
                $fonts = $config->xpath("//field[@name='fontFamily']/option");
                foreach ($fonts as $font) {
                    if ($font['value'] != 'none') {
                        $defaultFont = (string)$font['value'];
                        break;
                    }
                }
                $params->set('themes.fontFamily', $defaultFont);
            }
        }

        // Must have the default plan group set
        if ($params->get('basic.defaultGroup') == '') {
            $defaultGroup = JComponentHelper::getParams('com_users')->get('new_usertype');
            $params->set('basic.defaultGroup', $defaultGroup);
            $setParams = true;
        }

        // Must have default payment option set
        if (!$params->get('basic.paymentOptions', array())) {
            $params->set('basic.paymentOptions', array('cc'));
            $setParams = true;
        }

        // Check for older versions of params and fix
        $data = $params->toArray();
        if (!empty($data['advanced'])) {
            $data['themes'] = $data['advanced'];
            unset($data['advanced']);
            $setParams = true;
        }

        $codeMask = $params->get('basic.codeMask', $params->get('account.codeMask'));
        if ($codeMask) {
            $setParams = true;

            if (empty($data['account'])) {
                $data['account'] = array();
            }
            if (!empty($data['basic']['codeMask'])) {
                unset($data['basic']['codeMask']);
            }
            if (!empty($data['account']['codeMask'])) {
                unset($data['account']['codeMask']);
            }
            $prefix = substr($codeMask, 0, strpos($codeMask, '%s'));

            $data['account']['prefix'] = $prefix;
        }

        if ($setParams) {
            $params        = new JRegistry($data);
            $table->params = $params->toString();
            $table->store();
        }
    }

    /**
     * Use this in preflight to clear out obsolete update servers when the url has changed.
     */
    protected function clearUpdateServers()
    {
        $sr = JComponentHelper::getComponent('com_simplerenew');

        $db = JFactory::getDbo();
        $db->setQuery('Select update_site_id From #__update_sites_extensions where extension_id=' . (int)$sr->id);
        $list = $db->loadColumn();

        $db->setQuery('Delete From #__update_sites_extensions where extension_id=' . (int)$sr->id);
        $db->execute();

        $db->setQuery('Delete From #__update_sites Where update_site_id IN (' . join(',', $list) . ')');
        $db->execute();
    }
}
