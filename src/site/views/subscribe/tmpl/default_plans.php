<?php
/**
 * @package   Simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

/**
 * @var SimplerenewViewSubscribe $this
 */

echo $this->stepHeading(JText::plural('COM_SIMPLERENEW_HEADING_PLANLIST', count($this->plans)));
?>
<div class="ost-section">
    <div class="block12">
        <?php
        foreach ($this->plans as $code => $plan):
            $planId  = 'plan_code_' . $code;
            $classes = 'plan_code ' . $planId;
            $checked = $plan->selected ? ' checked' : '';
            ?>
            <div class="<?php echo $classes; ?>">
                <span class="simplerenew-plan <?php echo $planId; ?>">
                    <input<?php echo $checked; ?>
                        type="radio"
                        name="planCode"
                        id="<?php echo $planId; ?>"
                        value="<?php echo $plan->code; ?>"/>
                    <?php
                    echo JHtml::_(
                        'plan.name',
                        $plan->name,
                        $plan->amount,
                        $plan->length,
                        $plan->unit,
                        $plan->trial_length,
                        $plan->trial_unit
                    );
                    ?>
                </span>
            </div>

        <?php
        endforeach; ?>
    </div>
</div>
<!-- /.ost-section -->

