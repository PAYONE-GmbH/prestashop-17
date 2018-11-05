{*
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
* @category  Payone
* @package   fcpayone
* @author    patworx multimedia GmbH <service@patworx.de>
* @copyright 2003 - 2018 BS PAYONE GmbH
* @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
* @link      http://www.payone.de
*}
{if $oFcPayPalExpress}
<div id="{$oFcPayPalExpress->getId()|escape:'html':'UTF-8'}">
    <form action="{$link->getModuleLink($sFcPayoneModuleId, $oFcPayPalExpress->getController(), ['payone_payment' => $oFcPayPalExpress->getParentId(), 'payone_payment_sub' => $oFcPayPalExpress->getId(), 'express_checkout_init' => true, 'express_checkout_type' => 'cart'], true)|escape:'htmlall':'UTF-8'}" method="POST">
        <button type="submit">
            <img src="{$oFcPayPalExpress->getPayPalExpressImage()|escape:'html':'UTF-8'}" alt="{$oFcPayPalExpress->getTitle()|escape:'html':'UTF-8'}">
        </button>
    </form>
</div>
{/if}
