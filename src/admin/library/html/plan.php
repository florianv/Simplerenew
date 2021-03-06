<?php
/**
 * @package   Simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Simplerenew\Api\Plan;

defined('_JEXEC') or die();

abstract class JHtmlPlan
{
    /**
     * Return a standard format plan name
     *
     * @param string $name
     * @param string $amount
     * @param int    $length
     * @param string $unit
     * @param int    $trial_length
     * @param string $trial_unit
     *
     * @return string
     */
    public static function name(
        $name,
        $amount = null,
        $length = null,
        $unit = null,
        $trial_length = null,
        $trial_unit = null
    ) {

        $text = $name;

        if ($amount > 0) {
            $text .= ' ' . JHtml::_('currency.format', $amount);
        }

        if ($length > 0 && $unit) {
            $text .= ' ' . JText::plural('COM_SIMPLERENEW_PLAN_LENGTH_' . $unit, $length, $length);
        }

        if ($trial_length > 0 && $trial_unit) {
            $text .= ' - ' . self::trial($trial_length, $trial_unit);
        }

        return $text;
    }

    /**
     * Return standard format trial period text
     *
     * @param mixed  $length
     * @param string $unit
     *
     * @return string
     */
    public static function trial($length, $unit = null)
    {
        if (func_num_args() == 1) {
            if (is_object($length)) {
                $plan = (array)$length;
            }

            if (!empty($plan['trial_length']) && !empty($plan['trial_unit'])) {
                $plan   = $length;
                $length = $plan->trial_length;
                $unit   = $plan->trial_unit;
            }
        }

        if ($length && $unit) {
            return JText::plural(
                'COM_SIMPLERENEW_TRIAL_' . $unit,
                $length
            );
        }

        return '';
    }
}
