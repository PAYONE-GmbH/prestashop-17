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

namespace Payone\Request\Builder;

use Payone\Base\Registry;

class Items extends Base
{

    /**
     * Item index-> pass as reference
     *
     * @var int
     */
    protected $iItemIndex = 1;


    /**
     * Sets user params
     */
    public function build()
    {
        parent::build();
        $aSummary = $this->getCart()->getSummaryDetails();
        $iIndex = $this->addProducts(1, $aSummary);
        $iIndexWithShipping = $this->addShipping($iIndex, $aSummary);
        $this->addDiscounts($iIndexWithShipping, $aSummary);
    }

    /**
     * Returns amount converted for request
     *
     * @param float $dAmount
     * @return float
     */
    public function getConvertedAmount($dAmount)
    {
        return Registry::getHelper()->getConvertedAmount($dAmount);
    }

    /**
     * Adds items to param
     *
     * @param array $aItems
     */
    protected function addItemsToParams($aItems)
    {
        foreach ($aItems as $sKey => $sValue) {
            $this->setParam($sKey, $sValue);
        }
    }

    /**
     * Adds product
     *
     * @param int $iItemIndex
     * @param array $aSummary
     * @return int
     */
    protected function addProducts($iItemIndex, $aSummary)
    {
        $aOrderItems = array();
        foreach ($aSummary['products'] as $aProduct) {
            $aOrderItems['it[' . $iItemIndex . ']'] = 'goods';
            $aOrderItems['id[' . $iItemIndex . ']'] = $aProduct['reference'];
            $aOrderItems['pr[' . $iItemIndex . ']'] = $this->getConvertedAmount($aProduct['price_wt']);
            $aOrderItems['no[' . $iItemIndex . ']'] = $aProduct['quantity'];
            $aOrderItems['de[' . $iItemIndex . ']'] = $aProduct['name'];
            $aOrderItems['va[' . $iItemIndex . ']'] = $this->getConvertedAmount($aProduct['rate']);
            $iItemIndex++;
        }
        $this->addItemsToParams($aOrderItems);
        return $iItemIndex;
    }

    /**
     * Adds shipping
     * @param int $iItemIndex
     * @param array $aSummary
     * @return int
     */
    protected function addShipping($iItemIndex, $aSummary)
    {
        $aOrderItems = array();
        if ($aSummary['total_shipping'] > 0) {
            $aOrderItems['it[' . $iItemIndex . ']'] = 'shipment';
            $aOrderItems['id[' . $iItemIndex . ']'] = $aSummary['carrier']->id_reference;
            $aOrderItems['pr[' . $iItemIndex . ']'] = $this->getConvertedAmount($aSummary['total_shipping']);
            $aOrderItems['no[' . $iItemIndex . ']'] = 1;
            $aOrderItems['de[' . $iItemIndex . ']'] = $aSummary['carrier']->name;
            $dTax = (
                $aSummary['total_shipping'] - $aSummary['total_shipping_tax_exc']
                ) * 100 / $aSummary['total_shipping_tax_exc'];
            $aOrderItems['va[' . $iItemIndex . ']'] = $this->getConvertedAmount($dTax);
            $iItemIndex++;
            $this->addItemsToParams($aOrderItems);
        }
        return $iItemIndex;
    }

    /**
     * Adds discounts
     *
     * @param int $iItemIndex
     * @param array $aSummary
     * @return int
     */
    protected function addDiscounts($iItemIndex, $aSummary)
    {
        if (count($aSummary['discounts']) > 0) {
            $aOrderItems = array();
            $sDiscountType = 'voucher';
            foreach ($aSummary['discounts'] as $aDiscount) {
                $aOrderItems['it[' . $iItemIndex . ']'] = $sDiscountType;
                $aOrderItems['id[' . $iItemIndex . ']'] = $aDiscount['id_cart_rule'];
                $aOrderItems['pr[' . $iItemIndex . ']'] = $this->getConvertedAmount(($aDiscount['value_real'] * -1));
                $aOrderItems['no[' . $iItemIndex . ']'] = $aDiscount['quantity'];
                $aOrderItems['de[' . $iItemIndex . ']'] = $aDiscount['name'] . ' ' . $aDiscount['code'];
                $dTax = ($aDiscount['value_real'] - $aDiscount['value_tax_exc']) * 100 / $aDiscount['value_tax_exc'];
                $aOrderItems['va[' . $iItemIndex . ']'] = $this->getConvertedAmount($dTax);
                $iItemIndex++;
            }
            $this->addItemsToParams($aOrderItems);
        }
        return $iItemIndex;
    }
}
