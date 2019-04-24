<?php
/**
 * PAYONE Prestashop Connector is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PAYONE Prestashop Connector is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with PAYONE Prestashop Connector. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 *
 * @author    patworx multimedia GmbH <service@patworx.de>
 * @copyright 2003 - 2018 BS PAYONE GmbH
 * @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link      http://www.payone.de
 */

namespace Payone\Request\Builder;

use Payone\Base\Registry;
use Payone\Base\Reference;

class Base
{

    /**
     * Request params
     *
     * @var array
     */
    protected $aParams = null;

    /**
     * Selected payment object
     *
     * @var object
     */
    protected $oPayment = null;

    /**
     * Submitted form
     *
     * @var array
     */
    protected $aForm = null;

    /**
     * Currency
     *
     * @var object
     */
    protected $oCurrency = null;

    /**
     * Cart
     *
     * @var object
     */
    protected $oCart = null;

    /**
     * Customer
     *
     * @var object
     */
    protected $oCustomer = null;

    /**
     * Language
     *
     * @var object
     */
    protected $oLanguage = null;

    /**
     * constructor
     */
    public function __construct()
    {
    }

    /**
     * init
     */
    public function build()
    {
    }

    /**
     * Sets payone payment
     *
     * @param object $oPayment
     */
    public function setPayment($oPayment)
    {
        $this->oPayment = $oPayment;
    }

    /**
     * Returns selected payment
     *
     * @return object
     */
    public function getPayment()
    {
        return $this->oPayment;
    }

    /**
     * Sertzs payone form
     *
     * @param array $aForm
     */
    public function setForm($aForm)
    {
        $this->aForm = $aForm;
    }

    /**
     * Returns payone form
     *
     * @return array
     */
    public function getForm()
    {
        return $this->aForm;
    }

    /**
     * Returns cart
     *
     * @param object $oCart
     */
    public function setCart($oCart)
    {
        $this->oCart = $oCart;
    }

    /**
     * Returns cart
     *
     * @return object
     */
    protected function getCart()
    {
        return $this->oCart;
    }

    /**
     * Returns currency object
     * @return \Currency
     */
    public function getCurrency()
    {
        if ($this->oCurrency === null) {
            $this->oCurrency = false;
            $oCart = $this->getCart();
            $this->oCurrency = $this->getHelper()->fcPayoneGetCurrency($oCart->id_currency);
        }
        return $this->oCurrency;
    }

    /**
     * Returns customer object
     * @return \Customer
     */
    protected function getCustomer()
    {
        if ($this->oCustomer === null) {
            $this->oCustomer = false;
            $oCart = $this->getCart();
            $this->oCustomer = $this->getHelper()->fcPayoneGetCustomer($oCart->id_customer);
        }
        return $this->oCustomer;
    }

    /**
     * Returns language object
     *
     * @return string
     */
    protected function getLanguage()
    {
        if ($this->oLanguage === null) {
            $this->oLanguage = false;
            $oCart = $this->getCart();
            $this->oLanguage = $this->getHelper()->fcPayoneGetLanguage($oCart->id_lang);
        }
        return $this->oLanguage;
    }

    /**
     * Returns request bulder instance
     *
     * @return object
     */
    public function getHelper()
    {
        return Registry::getHelperPrestashop();
    }

    /**
     * Returns request param
     *
     * @return array
     */
    public function getParams()
    {
        return $this->aParams;
    }

    /**
     * Add parameter to request
     *
     * @param string $sParameter parameter key
     * @param string $sValue parameter value
     * @param bool $blAddAsNullIfEmpty add parameter with value NULL if empty. Default is false
     *
     * @return null
     */
    protected function setParam($sParameter, $sValue, $blAddAsNullIfEmpty = false)
    {
        if ($blAddAsNullIfEmpty === true && empty($sValue)) {
            $sValue = 'NULL';
        }
        $this->aParams[$sParameter] = $sValue;
    }

    /**
     * Returns parameter value
     *
     * @param string $sParameter parameter key
     * @return mixed
     */
    protected function getParam($sParameter)
    {
        return $this->aParams[$sParameter];
    }

    /**
     * Deeltes parameter value
     *
     * @param string $sParameter parameter key
     */
    protected function deleteParam($sParameter)
    {
        unset($this->aParams[$sParameter]);
    }

    /**
     * Returns connection mode
     *
     * @return string
     */
    protected function getConnectionMode()
    {
        return \Configuration::get('FC_PAYONE_CONNECTION_MODE_LIVE') ? 'live' : 'test';
    }

    /**
     * Add basket to request
     *
     * @return boolean
     */
    protected function sendBasketContent()
    {
        return \Configuration::get('FC_PAYONE_MISC_SEND_BASKET');
    }

    /**
     * Returns reference
     *
     * @return string
     */
    public function getReference()
    {
        $oReference = new Reference();
        $oReference->createReference();
        return $oReference->getReference();
    }
}
