<?php
/**
 * @package   com_simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

class SimplerenewTablePlans extends SimplerenewTable
{
    /**
     * @param JDatabaseDriver $db
     */
    public function __construct(&$db)
    {
        parent::__construct('#__simplerenew_plans', 'id', $db);
    }

    public function store($updateNulls = false)
    {
        if (trim($this->alias) == '') {
            $this->alias = $this->code;
        }

        $this->alias = SimplerenewApplicationHelper::stringURLSafe($this->alias);

        // Verify that the code and alias are unique
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('count(*)')
            ->from($this->_tbl);

        if ($this->id) {
            $query->where('id <> ' . $db->quote($this->id));
        }
        $query->where(
            '('
            . join(
                ' OR ',
                array(
                    'alias = ' . $db->quote($this->alias),
                    'code = ' . $db->quote($this->code)
                )
            )
            . ')'
        );

        if ($db->setQuery($query)->loadResult() > 0) {
            $this->setError(JText::_('COM_SIMPLERENEW_ERROR_PLAN_DUPLICATE'));
            return false;
        }

        return parent::store($updateNulls);
    }
}
