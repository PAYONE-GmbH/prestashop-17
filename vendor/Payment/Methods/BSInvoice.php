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

namespace Payone\Payment\Methods;

class BSInvoice extends Base
{

    /**
     * ID
     *
     * @var string
     */
    protected $sId = 'bsinvoice';

    /**
     * Clearing type
     *
     * @var string
     */
    protected $sClearingType = 'rec';

    /**
     * Available request type
     *
     * @var array
     */
    protected $aRequestTypes = array(Base::REQUEST_PREAUTH, Base::REQUEST_AUTH);

    /**
     * True if bank data is required for debit request
     *
     * @var bool
     */
    protected $blNeedBankDataForDebit = false;

    /**
     * Clearing type
     *
     * @var string
     */
    protected $sSubClearingType = 'POV';

    /**
     * Disable amount input for capture/refund
     * eg. secure invoice
     *
     * @var bool
     */
    protected $blDisableAmountInput = true;

    /**
     * Add items to capture request
     *
     * @var bool
     */
    protected $blIsItemsRequiredInCaptureRequest = true;

    /**
     * Add items to debit/refund request
     *
     * @var bool
     */
    protected $blIsItemsRequiredInDebitRequest = true;

}
