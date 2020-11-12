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

use Payone\Base\Mandate;

class Debit extends Base
{

    /**
     * Returns mandate object if available
     *
     * @return \Payone\Base\Mandate
     */
    public function getMandate()
    {
        $oMandate = new Mandate();
        if ($oMandate->setMandateFromContext($this->getContext()) && (
                $oMandate->getMandateStatus() == 'pending' || $oMandate->getMandateStatus() == 'active')
            && $oMandate->getMandateText() != ''
        ) {
            return $oMandate;
        }
    }

    /**
     * Gets form data
     *
     * @return array
     */
    public function getFormData()
    {
        $aForm = array();
        if (\Tools::isSubmit('fcpayonesubmit')) {
            $aForm = parent::getFormData();
        }
        if (!isset($aForm) || count($aForm) == 0) {
            $aForm['bankcountry'] = 'DE';
            $aForm['bankaccount'] = '';
            $aForm['bankcode'] = '';
            $aForm['iban'] = '';
            $aForm['bic'] = '';
            $aForm['bankaccountholder'] = '';
            $aForm['bankdatatype'] = 1;
        }
        return $aForm;
    }

    /**
     * Sets form data after submit
     *
     * @return void
     */
    public function setFormData()
    {
        parent::setFormData();

        $this->setJsValidation(); //jquery included
        $this->getController()->addJS($this->getHelper()->getModulePath() . 'views/js/frontend/fcpayonedebit.js');
        $this->setAjaxValidationUrl();
        $this->setMandate();
        $aAvailableCountrys = $this->getFormCountries();

        if (is_array($aAvailableCountrys) && count($aAvailableCountrys) > 0) {
            $this->getSmarty()->assign('aFcPayoneCountries', $aAvailableCountrys);
        }
    }

    /**
     * Sets mandate to frontend
     */
    protected function setMandate()
    {
        $oMandate = $this->getMandate();
        if ($oMandate) {
            $this->getSmarty()->assign('sFcPayoneMandateText', $oMandate->getMandateText());
            $this->getSmarty()->assign('blFcPayoneShowMandate', true);
            $this->getSmarty()->assign('sFcPayoneMandateStatus', $oMandate->getMandateStatus());
        } else {
            $this->getSmarty()->assign('blFcPayoneShowMandate', false);
        }
    }
}
