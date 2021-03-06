<?php
/**
 * @package   com_simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Simplerenew\Gateway\Recurly;

use Simplerenew\Configuration;
use Simplerenew\Exception;
use Simplerenew\Gateway\AbstractGatewayBase;

defined('_JEXEC') or die();

require_once __DIR__ . '/api/autoloader.php';

abstract class AbstractRecurlyBase extends AbstractGatewayBase
{
    /**
     * @var \Recurly_Client
     */
    protected $client = null;

    /**
     * @var string
     */
    protected $currency = 'USD';

    /**
     * @var string
     */
    protected $transparentUrl = 'https://api.recurly.com/transparent/';

    public function __construct(Configuration $config = null)
    {
        parent::__construct($config);

        // Initialise the native Recurly API
        $mode = $this->gatewayConfig->get('mode', 'test');

        if ($apiKey = $this->gatewayConfig->get($mode.'Apikey')) {
            $this->client = new \Recurly_Client($apiKey);
        }

        if ($subDomain = $this->gatewayConfig->get($mode . 'Subdomain')) {
            $this->transparentUrl .= $subDomain;
        }

        $this->currency = $this->gatewayConfig->get('currency', 'USD');
    }

    /**
     * Get the desired currency amount from a Recurly currency object
     *
     * @param \Recurly_CurrencyList $amounts
     * @param string                $currency
     *
     * @return float
     */
    protected function getCurrency(\Recurly_CurrencyList $amounts, $currency = null)
    {
        $currency = $currency ? : $this->currency;

        if (isset($amounts[$currency])) {
            $amount = $amounts[$currency]->amount_in_cents / 100;
            return $amount;
        }

        return 0.0;
    }

    /**
     * @return string
     */
    public function getTransparentUrl()
    {
        return $this->transparentUrl;
    }

    /**
     * Determine whether the current configuration is usable/valid
     *
     * @return bool
     */
    public function validConfiguration()
    {
        if ($this->client instanceof \Recurly_Client) {
            return ($this->client->apiKey() != '');
        }
        return false;
    }
}
