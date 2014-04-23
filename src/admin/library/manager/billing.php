<?php
/**
 * @package   com_simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Simplerenew\Manager;

defined('_JEXEC') or die();

class Billing
{
    /**
     * @var Pimple
     */
    protected $container = null;
    /**
     * @var PaymentInterface
     */
    public $payment = null;

    public function __construct()
    {
        $this->container = new \Pimple();

        $this->payment = new Payment\Creditcard();
    }
}
