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

namespace Payone\Payment\Methods\Wallet;

class Wallet extends \Payone\Payment\Methods\Base
{

    /**
     * ID
     *
     * @var string
     */
    protected $sId = 'wallet';


    /**
     * Clearing type
     *
     * @var string
     */
    protected $sClearingType = 'wlt';


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
    protected $sTemplate = 'wallet.tpl';

    /**
     * Marks wallet payments as grouped payments
     *
     * @var bool
     */
    protected $blIsGroupedPayment = true;
}
