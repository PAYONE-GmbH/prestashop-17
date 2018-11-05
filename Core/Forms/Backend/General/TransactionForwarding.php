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

namespace Payone\Forms\Backend\General;

class TransactionForwarding extends \Payone\Forms\Backend\Base
{
    /**
     * Form ident
     *
     * @var string
     */
    protected $sIdent = 'TransactionForwarding';

    /**
     * Returns form fields for connection form
     *
     * @return array
     */
    public function getForm()
    {
        $aForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->getTitle(),
                    'icon' => 'icon-cogs',
                ),
                'submit' => $this->getFieldSubmit(),
            ),
        );

        return $this->addTransactionFields($aForm);
    }


    /**
     * Add forwarding input for each transaction type
     *
     * @param array $aForm
     * @return array
     */
    protected function addTransactionFields($aForm)
    {
        $aStates = \Payone\Base\Transaction::getStates();
        foreach ($aStates as $sState) {
            $sState = \Tools::strtoupper($sState);
            $aForm['form']['input'][] = array(
                'col' => 9,
                'type' => 'textarea',
                'hint' => $this->translate('FC_PAYONE_BACKEND_TRANSACTION_FORWARDING_DESC'),
                'name' => 'FC_PAYONE_TRANSACTION_FORWARDING_' . $sState,
                'label' => $sState,
            );
        }
        return $aForm;
    }
}
