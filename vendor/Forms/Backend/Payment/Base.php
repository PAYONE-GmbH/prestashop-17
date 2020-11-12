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

namespace Payone\Forms\Backend\Payment;

class Base extends \Payone\Forms\Backend\Base
{

    /**
     * Instance of payment
     *
     * @var object
     */
    protected $oPayment = null;

    /**
     * Returns form payment
     *
     * @return object
     */
    protected function getPayment()
    {
        return $this->oPayment;
    }

    /**
     * Sets form payment
     *
     * @param object $oPayment
     *
     */
    public function setPayment($oPayment)
    {
        $this->sIdent = $oPayment->getId();
        $this->oPayment = $oPayment;
    }

    /**
     * Returns request state field
     *
     * @return array
     */
    protected function getFieldRequestType()
    {
        $aField = array(
            'col' => 3,
            'type' => 'select',
            'hint' => $this->translate('FC_PAYONE_BACKEND_PAYMENT_REQUEST_TYPE_DESC'),
            'name' => 'FC_PAYONE_PAYMENT_REQUEST_TYPE_' . \Tools::strtoupper($this->getIdent()),
            'label' => $this->translate('FC_PAYONE_BACKEND_PAYMENT_REQUEST_TYPE'),
            'options' => array(
                'id' => 'id_option',
                'query' => $this->getRequestTypes(),
                'name' => 'name'
            ),
        );
        return $aField;
    }

    /**
     * Returns mapping field for transaction state
     *
     * @param string $sState
     * @return array
     */
    protected function getFieldTransactionMapping($sState)
    {
        $sStateIdent = \Tools::strtoupper($sState);
        $sName = 'FC_PAYONE_PAYMENT_TRANSACTION_MAPPING_' . \Tools::strtoupper($this->getIdent()) . '_' . $sStateIdent;
        $aField = array(
            'col' => 3,
            'type' => 'select',
            'name' => $sName,
            'label' => $this->translate('FC_PAYONE_BACKEND_PAYMENT_TRANSACTION_MAPPING_' . $sStateIdent),
            'options' => array(
                'id' => 'id_option',
                'query' => $this->getOrderStates(),
                'name' => 'name'
            ),
        );
        return $aField;
    }

    /**
     * Add transaciton mapping to form
     *
     * @param array $aForm
     * @return array
     */
    protected function addTransactionMappingFields($aForm)
    {
        $aStates = \Payone\Base\Transaction::getStates();
        foreach ($aStates as $sState) {
            $aForm['form']['input'][] = $this->getFieldTransactionMapping($sState);
        }
        return $aForm;
    }

    /**
     * Hook for form post processing
     *
     * @param array $aForm
     * @return array
     */
    protected function postProcess($aForm)
    {
        $aProcessedForm = parent::postProcess($aForm);
        $aProcessedForm = $this->addTransactionMappingFields($aProcessedForm);
        return $aProcessedForm;
    }

    /**
     * Returns default form fields for payment methods
     * @return array
     */
    protected function getDefaultPaymentForm()
    {
        $aForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->getTitle(),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    $this->getFieldActive(),
                    $this->getFieldMode(),
                    $this->getFieldRequestType(),
                    $this->getFieldCountry(),
                ),
                'submit' => $this->getFieldSubmit(),
            ),
        );
        return $this->postProcess($aForm);
    }

    /**
     * Returns form fields
     *
     * @return array
     */
    public function getForm()
    {
        return $this->getDefaultPaymentForm();
    }

    /**
     * Form payment title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->translate('FC_PAYONE_PAYMENT_TITLE_' . \Tools::strtoupper($this->getIdent()));
    }
}
