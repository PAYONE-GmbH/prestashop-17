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

namespace Payone\Forms\Frontend\Payment;

use Payone\Base\Registry;

class CreditCard extends Base
{
    /**
     * Sets form data after submit
     *
     * @return void
     */
    public function setFormData()
    {
        parent::setFormData();

        $oLang = new \Language($this->getContext()->cart->id_lang);
        $this->getSmarty()->assign('sFcPayoneRequestLang', $oLang->iso_code);

        $sRequestJsData = '';
        foreach ($this->getCreditCardRequestParams() as $sParam => $sValue) {
            $sRequestJsData .= '|' . $sParam . '=' . $sValue;
        }
        $this->getSmarty()->assign('sFcPayoneJsRequestData', $sRequestJsData);
        $this->setJsValidation(); //jquery included
        $this->getController()->addJS($this->getHelper()->getModulePath() . 'views/js/frontend/fcpayonecreditcard.js');
        $aAvailableCountrys = $this->getFormCountries();
        if (is_array($aAvailableCountrys) && count($aAvailableCountrys) > 0) {
            $this->getSmarty()->assign('aFcPayoneCountries', $aAvailableCountrys);
        }
        $aValidSubPayments = $this->getFormPayment()->getValidSubPayments();
        $this->getSmarty()->assign('aFcPayoneSubPayments', $aValidSubPayments);

        $this->getSmarty()->assign('blFcPayoneShowCvC', \Configuration::get('FC_PAYONE_CREDITCARD_GENERAL_SHOW_CVC'));
    }

    /**
     * Returns creditcard iframe request params
     *
     * @return array
     */
    protected function getCreditCardRequestParams()
    {
        $oPayment = $this->getFormPayment();
        $aParams = array();
        $aParams['aid'] = \Configuration::get('FC_PAYONE_CONNECTION_SUBID'); //Payone Sub ID
        $aParams['encoding'] = 'UTF-8'; //Encoding
        $aParams['mid'] = \Configuration::get('FC_PAYONE_CONNECTION_MERCHANTID'); //Payone Merchant ID
        $aParams['mode'] = $oPayment->getMode(); //Payone Merchant ID
        $aParams['portalid'] = \Configuration::get('FC_PAYONE_CONNECTION_PORTALID'); //Payone Portal ID
        $aParams['request'] = 'creditcardcheck';
        $aParams['responsetype'] = 'JSON';
        $aParams['storecarddata'] = 'yes';
        $aParams['hash'] = md5(implode('', $aParams) . \Configuration::get('FC_PAYONE_CONNECTION_PORTALKEY'));
        return $aParams;
    }
}
