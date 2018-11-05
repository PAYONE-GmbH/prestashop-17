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

namespace Payone\Forms\Backend;

use Payone\Base\Registry;

class Base
{
    /**
     * Module
     *
     * @var null
     */
    protected $oModule = null;

    /**
     * Order
     *
     * @var null
     */
    protected $oOrder = null;

    /**
     * Context
     *
     * @var null
     */
    protected $oContext = null;

    /**
     * Set module
     *
     * @param $oModule
     */
    public function setModule($oModule)
    {
        $this->oModule = $oModule;
    }

    /**
     * Returns module
     *
     * @return null
     */
    protected function getModule()
    {
        return $this->oModule;
    }

    /**
     * Set order
     *
     * @param $oOrder
     */
    public function setOrder($oOrder)
    {
        $this->oOrder = $oOrder;
    }

    /**
     * Return order
     *
     * @return null
     */
    protected function getOrder()
    {
        return $this->oOrder;
    }

    /**
     * Set context
     *
     * @param $oContext
     */
    public function setContext($oContext)
    {
        $this->oContext = $oContext;
    }

    /**
     * Returns context
     *
     * @return null
     */
    protected function getContext()
    {
        return $this->oContext;
    }

    /**
     * Returns smarty
     *
     * @return object
     */
    protected function getSmarty()
    {
        return $this->getContext()->smarty;
    }

    /**
     * Submit name
     *
     * @var string
     */
    protected $sSubmitName = 'submitPayone';

    /**
     * Form ident
     *
     * @var string
     */
    protected $sIdent = null;

    /**
     * Returns submit name
     *
     * @return string
     */
    public function getSubmitName()
    {
        return $this->sSubmitName . $this->getIdent();
    }

    /**
     * Returns form ident
     *
     * @return string
     */
    public function getIdent()
    {
        return $this->sIdent;
    }

    /**
     * Call form field getter fr givin type
     * and return result
     *
     * @return array
     */
    public function getFields()
    {
        $aFields = array();
        $aForm = $this->getForm();
        foreach ($aForm['form']['input'] as $aInput) {
            $aFields[] = $aInput['name'];
        }

        return $aFields;
    }

    /**
     * Returns array with payment request modes for payment
     * eg. preautorize, autohorize...
     *
     * @return array
     */
    protected function getRequestTypes()
    {
        $aRequestModes = array();
        $aPaymentRequestModes = $this->getPayment()->getRequestTypes();
        if (is_array($aPaymentRequestModes) && count($aPaymentRequestModes) > 0) {
            foreach ($aPaymentRequestModes as $sMode) {
                $aRequestModes[] = array(
                    'id_option' => $sMode,
                    'name' => $this->translate('FC_PAYONE_BACKEND_PAYMENT_REQUEST_TYPE_' . \Tools::strtoupper($sMode))
                );
            }
        }
        return $aRequestModes;
    }

    /**
     * Handles form updates
     * serialzes array data for config save
     *
     * @param string $sKey
     */
    public function handleUpdate($sKey)
    {
        if (strpos($sKey, '[]') !== false) {
            $sCleanKey = str_replace('[]', '', $sKey);
            $aValues = \Tools::getValue($sCleanKey);
            $sValues = \Tools::jsonEncode($aValues);
            \Configuration::updateValue($sCleanKey, $sValues);
        } else {
            \Configuration::updateValue($sKey, \Tools::getValue($sKey));
        }
    }

    /**
     * Handles form get
     * checks for multiple input field and unserialiezed content
     *
     * @param string $sKey
     *
     * @return mixed
     */
    protected function handleGet($sKey)
    {
        if (strpos($sKey, '[]') !== false) {
            $sCleanKey = str_replace('[]', '', $sKey);
            $sValues = \Configuration::get($sCleanKey);
            return \Tools::jsonDecode($sValues, true);
        } else {
            return \Configuration::get($sKey);
        }
    }

    /**
     * Returns form field values array
     * input name => value
     * @param array $aFields
     *
     * @return array
     */
    public function getValues($aFields)
    {
        $aFormValues = array();
        foreach ($aFields as $sKey) {
            $aFormValues[$sKey] = $this->handleGet($sKey);
        }
        return $aFormValues;
    }

    /**
     * Returns active  field
     *
     * @return array
     */
    protected function getFieldActive()
    {
        $aField = array(
            'type' => 'switch',
            'label' => $this->translate('FC_PAYONE_BACKEND_PAYMENT_ACTIVE'),
            'name' => 'FC_PAYONE_PAYMENT_ACTIVE_' . \Tools::strtoupper($this->getIdent()),
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'FC_PAYONE_PAYMENT_ACTIVE_ON',
                    'value' => true,
                ),
                array(
                    'id' => 'FC_PAYONE_PAYMENT_ACTIVE_OFF',
                    'value' => false,
                )
            ),
        );
        return $aField;
    }

    /**
     * Returns mode test/live  field
     *
     * @return array
     */
    protected function getFieldMode()
    {
        $aField = array(
            'type' => 'switch',
            'label' => $this->translate('FC_PAYONE_BACKEND_PAYMENT_MODE_LIVE'),
            'name' => 'FC_PAYONE_PAYMENT_MODE_LIVE_' . \Tools::strtoupper($this->getIdent()),
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'FC_PAYONE_PAYMENT_MODE_LIVE_ON',
                    'value' => true,
                ),
                array(
                    'id' => 'FC_PAYONE_PAYMENT_MODE_LIVE_OFF',
                    'value' => false,
                )
            ),
        );
        return $aField;
    }

    /**
     * Returns country field
     *
     * @return array
     */
    protected function getFieldCountry()
    {
        $aField = array(
            'col' => 3,
            'type' => 'select',
            'hint' => $this->translate('FC_PAYONE_BACKEND_PAYMENT_COUNTRY_DESC'),
            'name' => 'FC_PAYONE_PAYMENT_COUNTRY_' . \Tools::strtoupper($this->getIdent()) . '[]',
            'label' => $this->translate('FC_PAYONE_BACKEND_PAYMENT_COUNTRY'),
            'default_value' => (int)\Context::getContext()->country->id,
            'multiple' => true,
            'options' => array(
                'query' => $this->getCountries(),
                'id' => 'id_country',
                'name' => 'name'
            ),
        );
        return $aField;
    }

    /**
     * Returns form submit button field
     *
     * @return array
     */
    protected function getFieldSubmit()
    {
        return array('title' => $this->translate('FC_PAYONE_BACKEND_SAVE'));
    }

    /**
     * Returns form countries
     *
     * @return array
     */
    protected function getCountries()
    {
        $aValidCountries = array();
        $aCountries = \Country::getCountries((int)\Context::getContext()->language->id);
        foreach ($aCountries as $sKey => $aCountry) {
            if ($this->getPayment()->isValidCountry($aCountry['iso_code'])) {
                $aValidCountries[$sKey] = $aCountry;
            }
        }
        return $aValidCountries;
    }

    /**
     * Returns payment form title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->translate('FC_PAYONE_BACKEND_TITLE_' . \Tools::strtoupper($this->getIdent()));
    }

    /**
     * Hook for form post processing
     *
     * @param array $aForm
     *
     * @return array
     */
    protected function postProcess($aForm)
    {
        return $aForm;
    }

    /**
     * Translate string
     *
     * @param $sString
     * @return string
     */
    protected function translate($sString)
    {
        return Registry::getTranslator()->translate($sString);
    }
}
