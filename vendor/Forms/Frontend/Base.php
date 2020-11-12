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

namespace Payone\Forms\Frontend;

use Payone\Base\Registry;

class Base
{

    /**
     * Payment method
     *
     * @var object
     */
    protected $oFormPayment = null;

    /**
     * Returns selected payment method
     *
     * @return object
     */
    protected function getFormPayment()
    {
        return $this->oFormPayment;
    }

    /**
     * Sets selected payment method
     *
     * @param object $oPayment
     *
     * @return void
     */
    public function setFormPayment($oPayment)
    {
        $this->oFormPayment = $oPayment;
    }

    /**
     * Sets form data after submit
     */
    protected function setFormData()
    {
        $aForm = $this->getFormData();
        if (is_array($aForm)) {
            $this->getSmarty()->assign('fcpayone_form', $aForm);
        }
    }

    /**
     * Sets form data after submit
     *
     * @return array
     */
    public function getFormData()
    {
        return $this->getCleanForm(\Tools::getValue('fcpayone_form'));
    }

    /**
     * Returns form html for givin type
     * Types: connection, payment
     *
     * @param object Payment
     * @return string
     */
    public function getForm()
    {
        $oPayment = $this->getFormPayment();
        if ($oPayment) {
            $this->setFormData();
            return $this->getSmarty()->fetch($this->getHelper()->getModulePath() . $oPayment->getTemplateFullPath());
        }
    }

    /**
     * Cleans form values
     *
     * @param array $aForm
     * @return array
     */
    protected function getCleanForm($aForm)
    {
        if (is_array($aForm) && count($aForm) > 0) {
            foreach ($aForm as $sKey => $sValue) {
                $aForm[$sKey] = trim($sValue);
            }
        }
        return $aForm;
    }

    /**
     * Returns form countries
     *
     * @return array
     */
    protected function getFormCountries()
    {
        $aValidCountries = array();
        $aCountries = \Country::getCountries($this->getContext()->language->id);
        foreach ($aCountries as $sKey => $aCountry) {
            if ($this->getFormPayment()->isValidCountry($aCountry['iso_code'])) {
                $aValidCountries[$aCountry['iso_code']] = $aCountry['name'];
            }
        }
        return $aValidCountries;
    }

    /**
     * Returns context object
     *
     * @return object
     */
    protected function getContext()
    {
        return \Context::getContext();
    }

    /**
     * Returns smarty object
     *
     * @return object
     */
    protected function getSmarty()
    {
        return $this->getContext()->smarty;
    }

    /**
     * Returns controller object
     *
     * @return object
     */
    protected function getController()
    {
        return $this->getContext()->controller;
    }

    /**
     * Returns helper
     *
     * @return \Payone\Helper\Helper
     */
    protected function getHelper()
    {
        return Registry::getHelper();
    }
}
