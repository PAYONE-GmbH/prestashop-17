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
 * @copyright 2003 - 2020 BS PAYONE GmbH
 * @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link      http://www.payone.de
 */

namespace Payone\Request\Builder;

use Payone\Base\Registry;

class Mandate extends Base
{

    /**
     * File reference for getfile
     *
     * @var string
     */
    protected $sFileReference = null;

    /**
     * Transaction mode
     *
     * @var string live|test
     */
    protected $sMode = null;

    /**
     * Mandate requst type
     *
     * @var string getfile|mandatemanage
     */
    protected $sMandateRequestType = null;

    /**
     * Sets mandate params
     *
     */
    public function build()
    {
        parent::build();
        if ($this->getMandateRequestType() == 'managemandate') {
            $this->setManageMandateParams();
        } elseif ($this->getMandateRequestType() == 'getfile') {
            $this->setGetFileParams();
        }
    }


    /**
     * Returns manage request params
     *
     */
    protected function setManageMandateParams()
    {
        $this->setParam('request', 'managemandate');
        $this->setParam('reference', $this->getReference());
        $this->setAuthParams();
        $this->setPaymentParams();
        $this->setUserParams();
    }

    /**
     * Sets getfile request params
     *
     */
    protected function setGetFileParams()
    {
        $this->setParam('request', 'getfile');
        $this->setAuthParams();
        $this->setParam('file_reference', $this->getFileReference());
        $this->setParam('file_type', 'SEPA_MANDATE');
        $this->setParam('file_format', 'PDF');
        $this->setParam('mode', $this->getMode());
        $this->deleteParam('aid');
        if ($this->getMode() == 'test') {
            $this->deleteParam('integrator_name');
            $this->deleteParam('integrator_version');
            $this->deleteParam('solution_name');
            $this->deleteParam('solution_version');
        }
    }

    /**
     * Sets mandate request type
     *
     * @param $sType
     */
    public function setMandateRequestType($sType)
    {
        $this->sMandateRequestType = $sType;
    }

    /**
     * Returns mandate request type
     *
     * @return string $sType
     */
    protected function getMandateRequestType()
    {
        return $this->sMandateRequestType;
    }

    /**
     * Sets mode
     *
     * @param $sMode
     */
    public function setMode($sMode)
    {
        $this->sMode = $sMode;
    }

    /**
     * Returns mode
     *
     * @return string $sMode
     */
    protected function getMode()
    {
        return $this->sMode;
    }

    /**
     * Sets file reference
     *
     * @param string $sFileReference
     */
    public function setFileReference($sFileReference)
    {
        $this->sFileReference = $sFileReference;
    }

    /**
     * Returns file reference
     *
     * @return string $sFileReference
     */
    protected function getFileReference()
    {
        return $this->sFileReference;
    }

    /**
     * Sets auth params
     */
    protected function setAuthParams()
    {
        $oBuilder = new \Payone\Request\Builder\Auth;
        $oBuilder->build();
        $aAuthParams = $oBuilder->getParams();
        foreach ($aAuthParams as $sParam => $sValue) {
            $this->setParam($sParam, $sValue);
        }
    }

    /**
     * Set payment params
     */
    protected function setPaymentParams()
    {
        $this->setParam('clearingtype', $this->getPayment()->getClearingType());
        $sMode = $this->getPayment()->getMode();
        $this->setParam('mode', $sMode);
        $this->setParam('currency', $this->getCurrency()->iso_code);
        $aPaymentInfo = $this->getForm();
        if ($aPaymentInfo['bankdatatype'] == 1) {
            $this->setParam('iban', $aPaymentInfo['iban']);
            if ($this->getPayment()->showBic()) {
                $this->setParam('bic', $aPaymentInfo['bic']);
            }
        } else {
            $this->setParam('bankaccount', $aPaymentInfo['bankaccount']);
            $this->setParam('bankcode', $aPaymentInfo['bankcode']);
        }
        $this->setParam('bankcountry', $aPaymentInfo['bankcountry']);
    }

    /**
     * Sets user params
     */
    protected function setUserParams()
    {
        $oLanguage = $this->getLanguage();
        $oCustomer = $this->getCustomer();
        $oCart = $this->getCart();
        $sUserId = Registry::getUser()->getPayoneUserIdByCustomerId($oCustomer->id);
        if ($sUserId) {
            $this->setParam('userid', $sUserId);
        }
        $this->setParam('customerid', $oCustomer->id);
        $this->setParam('email', $oCustomer->email);
        $this->setParam('language', $oLanguage->iso_code);

        $sAddressId = $oCart->id_address_invoice;
        $oInvoiceAddress = $this->getHelper()->fcPayoneGetAddress($sAddressId);
        $aCountry = $oInvoiceAddress->getCountryAndState($oInvoiceAddress->id);
        $oCountry = $this->getHelper()->fcPayoneGetCountry($aCountry['id_country']);
        $this->setParam('country', $oCountry->iso_code);
        $this->setParam('lastname', $oInvoiceAddress->lastname);
        $this->setParam('lastname', $oInvoiceAddress->firstname);
        $this->setParam('city', $oInvoiceAddress->city);
    }
}
