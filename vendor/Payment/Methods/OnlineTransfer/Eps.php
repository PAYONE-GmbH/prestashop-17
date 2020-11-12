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

namespace Payone\Payment\Methods\OnlineTransfer;

class Eps extends OnlineTransfer
{

    /**
     * ID
     *
     * @var string
     */
    protected $sId = 'onlinetransfer_eps';

    /**
     * ID of parent payment
     *
     * @var string
     */
    protected $sParentId = 'onlinetransfer';

    /**
     * Clearing type
     *
     * @var string
     */
    protected $sSubClearingType = 'EPS';

    /**
     * Array with whitelist country iso codes
     *
     * @var array
     */
    protected $aCountryWhitelist = array(# 'AT',
    );

    /**
     * Marker for sub payments
     *
     * @var boolean
     */
    protected $blHasSubPayments = false;

    /**
     * Bank groups
     *
     * @var array
     */
    protected $aBankGroups = array(
        'ARZ_OVB' => 'Volksbanken',
        'ARZ_BAF' => 'Bank f&uuml;r &Auml;rzte und Freie Berufe',
        'ARZ_NLH' => 'Nieder&ouml;sterreichische Landes-Hypo',
        'ARZ_VLH' => 'Vorarlberger Landes-Hypo',
        'ARZ_BCS' => 'Bankhaus Carl Sp&auml;ngler & Co. AG',
        'ARZ_HTB' => 'Hypo Tirol',
        'ARZ_HAA' => 'Hypo Alpe Adria',
        'ARZ_IKB' => 'Investkreditbank',
        'ARZ_OAB' => '&Ouml;sterreichische Apothekerbank',
        'ARZ_IMB' => 'Immobank',
        'ARZ_GRB' => 'G&auml;rtnerbank',
        'ARZ_HIB' => 'HYPO Investment',
        'BA_AUS' => 'Bank Austria',
        'BAWAG_BWG' => 'BAWAG',
        'BAWAG_PSK' => 'PSK Bank',
        'BAWAG_ESY' => 'easybank',
        'BAWAG_SPD' => 'Sparda Bank',
        'SPARDAT_EBS' => 'Erste Bank',
        'SPARDAT_BBL' => 'Bank Burgenland',
        'RAC_RAC' => 'Raiffeisen',
        'HRAC_OOS' => 'Hypo Ober&ouml;sterreich',
        'HRAC_SLB' => 'Hypo Salzburg',
        'HRAC_STM' => 'Hypo Steiermark',

    );
}
