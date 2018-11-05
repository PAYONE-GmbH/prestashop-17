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
<section class="box js-payone-validate" data-payone-validation-url="{$sFcPayoneValidationUrl|escape:'html':'UTF-8'}" data-payone-payment-id="{$oFcPayonePayment->getId()|escape:'html':'UTF-8'}">
    <fieldset>
        <h3>{$oFcPayonePayment->getTitle()|escape:'html':'UTF-8'}</h3>
        {if $oFcPayonePayment->getDescription()}
        <p><strong class="dark">{$oFcPayonePayment->getDescription()|escape:'html':'UTF-8'}</strong></p>
        {/if}
        <p>
            {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_PAYMENT_ORDER_AMOUNT')|escape:'html':'UTF-8'}
            <span class="price">{Tools::displayPrice($total)}</span>
            {if (int)Configuration::get('PS_TAX') == 1}
                {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_PAYMENT_ORDER_AMOUNT_TAX')|escape:'html':'UTF-8'}
            {/if}
        </p>
        <div class="form-group row required">
            <label for="ottype" class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_ONLINETRANSFER_TYPE')|escape:'html':'UTF-8'} <sup>*</sup></label>
            <div class="col-lg-5">
                <select id="ottype" name="payone_payment_sub" class="form-control form-control-select js-ot-type">
                    {foreach from=$aFcPayoneSubPayments item=oSubPayment}
                    <option value="{$oSubPayment->getId()|escape:'html':'UTF-8'}"
                            data-show-ibanbic="{if $oSubPayment->hasIbanBic()}true{/if}"
                            data-show-bankgroup="{if $oSubPayment->getBankGroups()}true{/if}"
                            data-payment-id="{$oSubPayment->getId()|escape:'html':'UTF-8'}"
                            {if isset($fcpayone_form.payone_payment_sub) && $fcpayone_form.payone_payment_sub == $oSubPayment->getId()}selected{/if}>{$oSubPayment->getTitle()|escape:'html':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        {foreach from=$aFcPayoneSubPayments item=oSubPayment}
        {if $oSubPayment->getBankGroups()}
        <div class="form-group row required js-payment-bankgroup" data-payment-id="{$oSubPayment->getId()|escape:'html':'UTF-8'}">
            <label for="bankgrouptype" class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_ONLINETRANSFER_BANK_GROUP')|escape:'html':'UTF-8'} <sup>*</sup></label>
            <div class="col-lg-5">
                <select id="bankgrouptype" name="fcpayone_form[bankgrouptype_{$oSubPayment->getId()|escape:'html':'UTF-8'}]" class="form-control form-control-select">
                    {foreach from=$oSubPayment->getBankGroups() key=sBankGroupValue item=sBankGroupTitle}
                    <option value="{$sBankGroupValue|escape:'html':'UTF-8'}">{$sBankGroupTitle|escape:'html':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        {/if}
        {/foreach}

        <div class="js-payment-ibanbic">
            <div class="form-group row required">
                <label for="iban" class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_IBAN')|escape:'html':'UTF-8'} <sup>*</sup></label>
                <div class="col-lg-5">
                    <input id="iban" class="form-control" type="text" name="fcpayone_form[iban]" maxlength="35" value="{if isset($fcpayone_form.iban)}{$fcpayone_form.iban|escape:'html':'UTF-8'}{/if}" />
                </div>
            </div>
            <div class="form-group row required">
                <label for="bic" class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_BIC')|escape:'html':'UTF-8'} <sup>*</sup></label>
                <div class="col-lg-5">
                    <input id="bic" class="form-control" type="text" name="fcpayone_form[bic]" maxlength="11" value="{if isset($fcpayone_form.bic)}{$fcpayone_form.bic|escape:'html':'UTF-8'}{/if}" />
                </div>
            </div>
        </div>
    </fieldset>
</section>