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
<div class="box">
    <fieldset>
        {if $oFcPayonePayment->getImage()}
            <div class="col-lg-12">
                <img src="{$oFcPayonePayment->getImage()|escape:'html':'UTF-8'}" alt="{$oFcPayonePayment->getTitle()|escape:'html':'UTF-8'}" />
            </div>
        {/if}
        <h3 class="page-subheading">{$oFcPayonePayment->getTitle()|escape:'html':'UTF-8'}</h3>
        {if $oFcPayonePayment->getDescription()}
            <p><strong class="dark">{$oFcPayonePayment->getDescription()|escape:'html':'UTF-8'}</strong></p>
            <br/>
        {/if}
        <p>
            {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_PAYMENT_ORDER_AMOUNT')|escape:'html':'UTF-8'}
            <span class="price">{convertPrice price=$total}</span>
            {if $use_taxes == 1}
                {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_PAYMENT_ORDER_AMOUNT_TAX')|escape:'html':'UTF-8'}
            {/if}
        </p>
        <div class="col-xs-6">
            <div class="form-group">
                <label>
                    {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_BIRTHDATE')|escape:'html':'UTF-8'}<sup>*</sup>
                </label>
                <div class="row">
                    <div class="col-xs-4">
                        <select name="fcpayone_form[days]" id="days" class="form-control">
                            <option value="">-</option>
                            {foreach from=$aDays item=v}
                                <option value="{$v}" {if ($fcpayone_form.days == $v)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-xs-4">
                        <select id="months" name="fcpayone_form[months]" class="form-control">
                            <option value="">-</option>
                            {foreach from=$aMonth key=k item=v}
                                <option value="{$k}" {if ($fcpayone_form.months == $k)}selected="selected"{/if}>{l s=$v}&nbsp;</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-xs-4">
                        <select id="years" name="fcpayone_form[years]" class="form-control">
                            <option value="">-</option>
                            {foreach from=$aYears item=v}
                                <option value="{$v}" {if ($fcpayone_form.years == $v)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </fieldset>
</div>
