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

class User extends Base
{

    /**
     * Invoice address
     *
     * @var object
     */
    protected $oInvoiceAddress = null;

    /**
     * Delivery address
     *
     * @var object
     */
    protected $oDeliveryAddress = null;

    /**
     * Country with state needed
     *
     * @var array
     */
    protected $aCountryWithStates = array(
        'US',
        'CA',
        'CN',
        'JP',
        'MX',
        'BR',
        'AR',
        'ID',
        'TH',
        'IN'
    );

    /**
     * Sets user params
     */
    public function build()
    {
        parent::build();

        if ($this->getInvoiceAddress()) {
            $this->setInvoiceAddressParams();
            $this->setDeliveryAddressParams();
            $this->setPersonalInfoParams();
        }
    }

    /**
     * Returns delivery address object
     * @return \Address
     */
    protected function getInvoiceAddress()
    {
        if ($this->oInvoiceAddress === null) {
            $this->oInvoiceAddress = false;
            $oCart = $this->getCart();
            $sAddressId = $oCart->id_address_invoice;
            $this->oInvoiceAddress = $this->getHelper()->fcPayoneGetAddress($sAddressId);
        }

        return $this->oInvoiceAddress;
    }

    /**
     * Returns delivery object
     * @return \Address
     */
    protected function getDeliveryAddress()
    {
        if ($this->oDeliveryAddress === null) {
            $this->oDeliveryAddress = false;
            $oCart = $this->getCart();
            if ($oCart->id_address_delivery) {
                $sAddressId = $oCart->id_address_delivery;
                $this->oDeliveryAddress = $this->getHelper()->fcPayoneGetAddress($sAddressId);
            } else {
                $this->oDeliveryAddress = $this->getInvoiceAddress();
            }
        }

        return $this->oDeliveryAddress;
    }

    /**
     * Returns true if givin country need state in request
     *
     * @param string $sCountryIso
     * @return boolean
     */
    protected function isStateNeeded($sCountryIso)
    {
        if (in_array($sCountryIso, $this->aCountryWithStates)) {
            return true;
        }
        return false;
    }

    /**
     * Returns ip address
     *
     * @return string
     */
    protected function getIp()
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $sIP = $_SERVER["HTTP_X_FORWARDED_FOR"];
            $sIP = preg_replace('/,.*$/', '', $sIP);
        } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $sIP = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $sIP = $_SERVER["REMOTE_ADDR"];
        }
        return $sIP;
    }

    /**
     * Returns phone number
     *
     * @return string
     */
    protected function getPhoneNumber()
    {
        $oAddress = $this->getInvoiceAddress();
        if ($oAddress->phone) {
            return $oAddress->phone;
        }
        return $oAddress->phone_mobile;
    }

    /**
     * Returns gender
     *
     * @return string
     */
    protected function getGender()
    {
        $oCustomer = $this->getCustomer();
        if ($oCustomer->id_gender == 2) {
            return 'f';
        }
        return 'm';
    }

    /**
     * Returns salutation
     *
     * @return string
     */
    protected function getSalutation()
    {
        if ($this->getGender() == 'm') {
            return 'Mr.';
        } else {
            return 'Mrs.';
        }
    }

    /**
     * Sets state
     *
     * @param object $oCountry
     * @param object $oAddress
     * @param boolean $blShipping
     */
    protected function setState($oCountry, $oAddress, $blShipping = false)
    {
        if ($this->isStateNeeded($oCountry->iso_code)) {
            $oState = $this->getHelper()->fcPayoneGetState($oAddress->id_state);
            $sState = '';
            if ($oState->iso_code) {
                $sState = $oCountry->iso_code . '-' . $oState->iso_code;
            }
            if ($blShipping) {
                $this->setParam('shipping_state', $sState);
            } else {
                $this->setParam('state', $sState);
            }
        }
    }

    /**
     * Adds invoice params
     */
    protected function setInvoiceAddressParams()
    {
        $oCustomer = $this->getCustomer();
        $oInvoiceAddress = $this->getInvoiceAddress();
        $aCountry = $oInvoiceAddress->getCountryAndState($oInvoiceAddress->id);
        $oCountry = $this->getHelper()->fcPayoneGetCountry($aCountry['id_country']);
        $this->setParam('salutation', $this->getSalutation());
        #$this->fcPayoneSetParameter('title','');
        $this->setParam('firstname', $oInvoiceAddress->firstname);
        $this->setParam('lastname', $oInvoiceAddress->lastname);
        $this->setParam('company', $oInvoiceAddress->company);
        $this->setParam('businessrelation', trim($oInvoiceAddress->company) == '' ? 'b2c' : 'b2b');
        $this->setParam('street', $oInvoiceAddress->address1);
        $this->setParam('addressaddition', $oInvoiceAddress->address2);
        $this->setParam('zip', $oInvoiceAddress->postcode);
        $this->setParam('city', $oInvoiceAddress->city);
        $this->setParam('country', $oCountry->iso_code);
        $this->setState($oCountry, $oInvoiceAddress);
        $this->setParam('email', $oCustomer->email);
        $this->setParam('telephonenumber', $this->getPhoneNumber());
        $this->setParam('birthday', str_replace('-', '', $oCustomer->birthday));
        $this->setParam('vatid', $oInvoiceAddress->vat_number);
    }

    /**
     * Sets personal infos to params
     */
    protected function setPersonalInfoParams()
    {
        $oInvoiceAddress = $this->getInvoiceAddress();
        $oLanguage = $this->getLanguage();
        $sUserId = Registry::getUser()->getPayoneUserIdByCustomerId($oInvoiceAddress->id_customer);
        if ($sUserId) {
            $this->setParam('userid', $sUserId);
        }
        //$this->setParam('customerid', $oInvoiceAddress->id_customer);
        $this->setParam('language', $oLanguage->iso_code);
        $this->setParam('personalid', '');
        $this->setParam('gender', $this->getGender());
        $this->setParam('ip', $this->getIp());
    }

    /**
     * Adds delivery params
     */
    protected function setDeliveryAddressParams()
    {
        $oDeliveryAddress = $this->getDeliveryAddress();
        $aCountry = $oDeliveryAddress->getCountryAndState($oDeliveryAddress->id);
        $oCountry = $this->getHelper()->fcPayoneGetCountry($aCountry['id_country']);
        $this->setState($oCountry, $oDeliveryAddress, true);
        $this->setParam('shipping_firstname', $oDeliveryAddress->firstname);
        $this->setParam('shipping_lastname', $oDeliveryAddress->lastname);
        $this->setParam('shipping_company', $oDeliveryAddress->company);
        $this->setParam('shipping_street', $oDeliveryAddress->address1);
        $this->setParam('shipping_zip', $oDeliveryAddress->postcode);
        $this->setParam('shipping_city', $oDeliveryAddress->city);
        $this->setParam('shipping_country', $oCountry->iso_code);
    }
}
