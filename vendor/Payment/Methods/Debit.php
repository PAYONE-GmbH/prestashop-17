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

class Debit extends Base
{

    /**
     * ID
     *
     * @var string
     */
    protected $sId = 'debit';

    /**
     * Clearing type
     *
     * @var string
     */
    protected $sClearingType = 'elv';

    /**
     * Template file path
     *
     * @var string
     */
    protected $sTemplate = 'debit.tpl';

    /**
     * Available request type
     *
     * @var array
     */
    protected $aRequestTypes = array(Base::REQUEST_PREAUTH, Base::REQUEST_AUTH);


    /**
     * Payment frontend controller
     *
     * @var string
     */
    protected $sController = 'paymentdebit';

    /**
     * Returns true if bankaccount should be shown
     *
     * @return boolean
     */
    public function showBankAccount()
    {
        return \Configuration::get('FC_PAYONE_PAYMENT_SHOW_BANKACCOUNT_DEBIT');
    }

    /**
     * Returns true if bic should be shown
     *
     * @return boolean
     */
    public function showBic()
    {
        return \Configuration::get('FC_PAYONE_PAYMENT_SHOW_BIC_DEBIT');
    }
}
