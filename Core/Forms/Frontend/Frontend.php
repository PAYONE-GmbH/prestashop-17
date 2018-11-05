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

namespace Payone\Forms\Frontend;

use Payone\Base\Registry;

class Frontend
{

    /**
     * Returns frontend form
     *
     * @param object $oPayment
     * @return string
     */
    public function getForm($oPayment)
    {
        if ($oPayment && ($oForm = $this->getFormObject($oPayment))) {
            return $oForm->getForm();
        }
    }

    /**
     * Returns frontend form
     *
     * @param object $oPayment
     * @return string
     */
    public function getFormData($oPayment)
    {
        if ($oPayment && ($oForm = $this->getFormObject($oPayment))) {
            return $oForm->getFormData();
        }
    }

    /**
     * Returns frontend form object
     *
     * @param object $oPayment
     * @return string
     */
    public function getFormObject($oPayment)
    {
        if ($oPayment) {
            $sFrontendFormClass = $this->getFrontendFormClass($oPayment);
            if (class_exists($sFrontendFormClass) && ($oForm = new $sFrontendFormClass)) {
                $oForm->setFormPayment($oPayment);
                return $oForm;
            }
        }
    }

    /**
     * Returns frontend form class
     *
     * @param object $oPayment
     * @return string
     */
    protected function getFrontendFormClass($oPayment)
    {
        $sFrontendFormClass = $oPayment->getFrontendFormClass();

        if (!class_exists($sFrontendFormClass) &&
            ($oParentPayment = Registry::getPayment()->getParentPaymentMethod($oPayment->getParentId()))
        ) {
            $sFrontendFormClass = $oParentPayment->getFrontendFormClass();
        }

        if (!class_exists($sFrontendFormClass)) {
            $sFrontendFormClass = '\Payone\Forms\Frontend\Payment\PaymentDefault';
        }
        return $sFrontendFormClass;
    }
}
