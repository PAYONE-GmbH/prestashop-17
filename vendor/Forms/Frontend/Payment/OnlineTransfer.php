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

class OnlineTransfer extends Base
{

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
            $aForm['payone_payment_sub'] = \Tools::getValue('payone_payment_sub');
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
        $this->getController()->addJS(
            $this->getHelper()->getModulePath() . 'views/js/frontend/fcpayoneonlinetransfer.js'
        );
        $this->setAjaxValidationUrl();

        $aValidSubPayments = $this->getFormPayment()->getValidSubPayments();
        $this->getSmarty()->assign('aFcPayoneSubPayments', $aValidSubPayments);
    }
}
