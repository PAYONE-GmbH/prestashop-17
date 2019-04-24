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

namespace Payone\Payment\Methods\OnlineTransfer;

class OnlineTransfer extends \Payone\Payment\Methods\Base
{

    /**
     * ID
     *
     * @var string
     */
    protected $sId = 'onlinetransfer';

    /**
     * Clearing type
     *
     * @var string
     */
    protected $sClearingType = 'sb';

    /**
     * Payment need redirect urls
     *
     * @var boolean
     */
    protected $blIsRedirectPayment = true;

    /**
     * Available request type
     *
     * @var array
     */
    protected $aRequestTypes = array(
        \Payone\Payment\Methods\Base::REQUEST_PREAUTH,
        \Payone\Payment\Methods\Base::REQUEST_AUTH
    );

    /**
     * Marker for sub payments
     *
     * @var boolean
     */
    protected $blHasSubPayments = true;

    /**
     * Payment template
     *
     * @var string
     */
    protected $sTemplate = 'onlinetransfer.tpl';

    /**
     * Flag for iban/bic form fields
     *
     * @var boolean
     */
    protected $blHasIbanBic = false;

    /**
     * Bank groups
     *
     * @var array
     */
    protected $aBankGroups = array();

    /**
     * Returns account settlement possiblity
     *
     * @var bool
     */
    protected $blAllowAccountSettlement = true;

    /**
     * True if bank data is required for debit request
     *
     * @var bool
     */
    protected $blNeedBankDataForDebit = true;

    /**
     * Returns array with bank groups
     *
     * @return array
     */
    public function getBankGroups()
    {
        return $this->aBankGroups;
    }

    /**
     * Returns true if iban/bic should be shown
     *
     * @return boolean
     */
    public function hasIbanBic()
    {
        return $this->blHasIbanBic;
    }
}
