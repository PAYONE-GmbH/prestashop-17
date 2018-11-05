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

namespace Payone\Helper;

class HelperPrestashop
{

    /**
     * Returns address object
     *
     * @param string $sId
     * @return \Address
     */
    public function fcPayoneGetAddress($sId = null)
    {
        if ($sId) {
            return new \Address($sId);
        }
        return new \Address();
    }

    /**
     * Returns cart object
     *
     * @param string $sId
     * @return \Cart
     */
    public function fcPayoneGetCart($sId)
    {
        if ($sId) {
            return new \Cart($sId);
        }
        return null;
    }

    /**
     * Returns customer object
     *
     * @param string $sId
     * @return \Customer
     */
    public function fcPayoneGetCustomer($sId = null)
    {
        if ($sId) {
            return new \Customer($sId);
        }
        return new \Customer();
    }

    /**
     * Returns country object
     *
     * @param string $sId
     * @return \Country
     */
    public function fcPayoneGetCountry($sId)
    {
        if ($sId) {
            return new \Country($sId);
        }
        return null;
    }

    /**
     * Returns Language object
     *
     * @param string $sId
     * @return \Language
     */
    public function fcPayoneGetLanguage($sId)
    {
        if ($sId) {
            return new \Language($sId);
        }
        return null;
    }

    /**
     * Returns state object
     *
     * @param string $sId
     * @return \State
     */
    public function fcPayoneGetState($sId)
    {
        if ($sId) {
            return new \State($sId);
        }
        return null;
    }

    /**
     * Returns currency object
     * @param string
     * @return \currency
     */
    public function fcPayoneGetCurrency($sId)
    {
        if ($sId) {
            return new \Currency($sId);
        }
        return null;
    }

    /**
     * Returns order object
     * @param string
     * @return \Order
     */
    public function fcPayoneGetOrder($sId)
    {
        if ($sId) {
            return new \Order($sId);
        }
        return null;
    }
}
