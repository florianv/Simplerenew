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

?>
<div class="page-header">
    <h1><?php echo JText::_('COM_SIMPLERENEW_CHANGE_PLAN'); ?></h1>
</div>

<div class="ost-section">

    <?php
    echo SimplerenewHelper::renderModule('simplerenew_plans_top');
    echo $this->loadTemplate('plans');
    echo SimplerenewHelper::renderModule('simplerenew_plans_bottom');

    $showCoupon = $this->get('State')->get('coupon.allow');
    if ($showCoupon < 0 || $showCoupon == 2) {
        echo $this->loadTemplate('coupon');
    }
    ?>

    <?php echo $this->loadtemplate('billing'); ?>

    <?php echo SimplerenewHelper::renderModule('simplerenew_submit_top'); ?>
    <div class="m-bottom m-top">
        <input
            type="submit"
            value="<?php echo JText::_('COM_SIMPLERENEW_CHANGE_BUTTON'); ?>"
            class="btn-main btn-big"/>
    </div>
    <?php echo SimplerenewHelper::renderModule('simplerenew_submit_bottom'); ?>
</div>
<!-- /.ost-section -->

<input
    type="hidden"
    name="task"
    value="subscription.change"/>

<input
    type="hidden"
    name="id"
    value="<?php echo $this->subscription->id; ?>"/>
