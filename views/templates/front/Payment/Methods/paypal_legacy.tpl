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
{capture name=path}{$oFcPayonePayment->getTitle()|escape:'html':'UTF-8'}{/capture}
<div class="box">
    <fieldset>
        <h3 class="page-subheading">{$oFcPayonePayment->getTitle()|escape:'html':'UTF-8'}</h3>
        {if $oFcPayonePayment->getDescription()}
        <p><strong class="dark">{$oFcPayonePayment->getDescription()|escape:'html':'UTF-8'}</strong></p>
        {/if}
        <p>
            {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_PAYMENT_ORDER_AMOUNT')|escape:'html':'UTF-8'}
            <span class="price">{convertPrice price=$total}</span>
            {if (int)Configuration::get('PS_TAX') == 1}
                {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_PAYMENT_ORDER_AMOUNT_TAX')|escape:'html':'UTF-8'}
            {/if}
        </p>
    </fieldset>
</div>