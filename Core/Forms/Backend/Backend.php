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

class Backend extends Base
{

    /**
     * Array with all payment methods
     *
     * @var array
     */
    protected $aFcPayoneBackendForms = null;

    /**
     * Returns array with all payment methods
     *
     * @return array
     */
    public function getConfigurationForms()
    {
        if ($this->aFcPayoneBackendForms === null) {
            $aForms = array();
            $aGeneralForms = $this->getGeneralForms();
            ksort($aGeneralForms);
            $aForms['settings'] = $aGeneralForms;
            $aPaymentForms = $this->getPaymentForms();
            $aForms += $aPaymentForms;
            $this->aFcPayoneBackendForms = $aForms;
        }
        return $this->aFcPayoneBackendForms;
    }

    /**
     * Returns array with payment forms
     *
     * @return array
     */
    protected function getPaymentForms()
    {
        $aForms = array();
        $aPayments = Registry::getPayment()->getPaymentMethods();
        $aForms = $this->addSpecialPaymentForms($aForms, 'pre');
        foreach ($aPayments as $oPayment) {
            $oForm = null;
            if (!$oPayment->hasSubPayments()) {
                if (($oForm = $this->getPaymentFormClass($oPayment))) {
                    $oForm->setPayment($oPayment);
                    $aForms['general'][$oForm->getTitle()] = $oForm;
                }
                if (isset($aForms['general'])) {
                    ksort($aForms['general']);
                }
            } else {
                $aSubPayments = $oPayment->getSubPayments();
                foreach ($aSubPayments as $oSubPayment) {
                    $oForm = $this->getPaymentFormClass($oSubPayment);
                    if ($oForm) {
                        $oForm->setPayment($oSubPayment);
                        $aForms[$oPayment->getId()][$oForm->getTitle()] = $oForm;
                    }
                }
                if (isset($aForms[$oPayment->getId()])) {
                    ksort($aForms[$oPayment->getId()]);
                }
            }
        }
        return $this->addSpecialPaymentForms($aForms, 'after');
    }

    /**
     * Adds special forms to payment tab
     *
     * @param $aForms
     * @param $sPosition
     * @return mixed
     */
    protected function addSpecialPaymentForms($aForms, $sPosition = 'after')
    {
        if ($sPosition == 'pre') {
            $oCreditCardGeneralForm = new \Payone\Forms\Backend\Payment\CreditCardGeneral;
            if ($oCreditCardGeneralForm) {
                $aForms['creditcard'][$oCreditCardGeneralForm->getTitle()] = $oCreditCardGeneralForm;
            }
        }
        return $aForms;
    }

    /**
     * Returns payment form object
     *
     * @param object $oPayment
     * @return object
     */
    protected function getPaymentFormClass($oPayment)
    {
        $sBackendFormClass = $oPayment->getBackendFormClass();

        if (!class_exists($sBackendFormClass) &&
            ($oParentPayment = Registry::getPayment()->getParentPaymentMethod($oPayment->getParentId()))
        ) {
            $sBackendFormClass = $oParentPayment->getBackendFormClass();
        }

        if (class_exists($sBackendFormClass) && ($oForm = new $sBackendFormClass)) {
            return $oForm;
        }
    }

    /**
     * Retursn general forms
     *
     * @return array
     */
    protected function getGeneralForms()
    {
        $aForms = array_merge(
            $this->getConnectionForm(),
            $this->getTransactionForwardingForm(),
            $this->getMiscForm()
        );
        return $aForms;
    }

    /**
     * Returns connection form
     *
     * @return array
     */
    protected function getConnectionForm()
    {
        $aForm = array();
        $oConfigForm = new \Payone\Forms\Backend\General\Connection;
        if ($oConfigForm) {
            $aForm[$oConfigForm->getTitle()] = $oConfigForm;
        }
        return $aForm;
    }

    /**
     * Returns transaction forwarding form
     *
     * @return array
     */
    protected function getTransactionForwardingForm()
    {
        $aForm = array();
        $oTransactionForm = new \Payone\Forms\Backend\General\TransactionForwarding();
        if ($oTransactionForm) {
            $aForm[$oTransactionForm->getTitle()] = $oTransactionForm;
        }
        return $aForm;
    }

    /**
     * Returns misc form
     *
     * @return array
     */
    protected function getMiscForm()
    {
        $aForm = array();
        $oMiscForm = new \Payone\Forms\Backend\General\Misc();
        if ($oMiscForm) {
            $aForm[$oMiscForm->getTitle()] = $oMiscForm;
        }
        return $aForm;
    }
}
