<?php
/**
 * @package   Simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

if (!defined('SIMPLERENEW_LOADED')) {
    require_once JPATH_ADMINISTRATOR . '/components/com_simplerenew/include.php';
}

JFormHelper::loadFieldClass('Checkboxes');

class JFormFieldPlans extends JFormFieldCheckboxes
{
    /**
     * @var array
     */
    protected $options = null;

    public function __construct($form = null)
    {
        parent::__construct($form);

        if (!SimplerenewFactory::getApplication()->isSite()) {
            $lang = SimplerenewFactory::getLanguage();
            $lang->load('com_simplerenew', SIMPLERENEW_SITE);
        }
    }

    protected function getOptions()
    {
        if ($this->options === null) {
            $this->options = array();

            // Recognized Field Attributes
            $format = $this->element['format'] ? (string)$this->element['format'] : '%name%';

            $orders    = array(
                'ordering' => 'p.ordering',
                'code'     => 'p.code',
                'name'     => 'p.name',
                'group'    => 'g.title'
            );
            $listOrder = $this->element['order'];
            $listOrder = empty($orders[$listOrder]) ? $orders['ordering'] :  $orders[$listOrder];
            $listDir = $this->element['direction'] ? (string)$this->element['direction'] : 'ASC';

            $db       = SimplerenewFactory::getDbo();
            $fields   = array_map(
                array($db, 'quoteName'),
                array(
                    'p.code',
                    'p.name',
                    'p.amount',
                    'p.trial_length',
                    'p.trial_unit',
                    'p.group_id'
                )
            );
            $fields[] = $db->quoteName('g.title', 'group');

            $query = $db->getQuery(true)
                ->select($fields)
                ->from('#__simplerenew_plans p')
                ->innerJoin('#__usergroups g on g.id = p.group_id')
                ->order($listOrder . ' ' . $listDir);

            $list = $db->setQuery($query)->loadObjectList();

            foreach ($list as $plan) {
                $text = str_replace(
                    array(
                        '{code}',
                        '{name}',
                        '{amount}',
                        '{trial}',
                        '{group}'
                    ),
                    array(
                        $plan->code,
                        $plan->name,
                        JHtml::_('currency.format', $plan->amount),
                        JHtml::_('plan.trial', $plan->trial_length, $plan->trial_unit),
                        $plan->group
                    ),
                    $format
                );

                $option           = JHtml::_('select.option', $plan->code, $text);
                $option->checked  = false;
                $option->group    = $plan->group;
                $option->group_id = $plan->group_id;
                $this->options[]  = $option;
            }
        }
        return $this->options;
    }
}
