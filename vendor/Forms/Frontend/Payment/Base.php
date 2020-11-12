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

class Base extends \Payone\Forms\Frontend\Base
{
    /**
     * Sets form data after submit
     *
     * @return void
     */
    public function setFormData()
    {
        parent::setFormData();
        $this->getSmarty()->assign(
            array(
                'oFcPayonePayment' => $this->getFormPayment(),
                'total' => $this->getContext()->cart->getOrderTotal(true, \Cart::BOTH),
            )
        );
    }


    /**
     * Sets ajax validation url to frontend
     *
     * @return void
     */
    protected function setAjaxValidationUrl()
    {
        $this->getSmarty()->assign(
            'sFcPayoneValidationUrl',
            $this->getContext()->link->getModuleLink('fcpayone', 'ajax')
        );
    }

    /**
     * Sets base js validation to frontend
     *
     * @return void
     */
    protected function setJsValidation()
    {
        $this->getController()->addJquery();
        $this->getController()->addJS($this->getHelper()->getModulePath() . 'views/js/frontend/fcpayonevalidation.js');
    }
}
