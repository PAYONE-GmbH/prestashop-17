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
<section class="box {if !$blFcPayoneShowMandate}js-payone-validate{/if}"
     data-payone-validation-url="{$sFcPayoneValidationUrl|escape:'html':'UTF-8'}" data-payone-payment-id="{$oFcPayonePayment->getId()|escape:'html':'UTF-8'}">
    <fieldset>
        <h3>{$oFcPayonePayment->getTitle()|escape:'html':'UTF-8'}</h3>
        {if $oFcPayonePayment->getDescription()|escape:'html':'UTF-8'}
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
            <label for="bankcountry" class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_COUNTRY')|escape:'html':'UTF-8'}
                <sup>*</sup></label>
            <div class="col-lg-5">
                <select id="bankcountry" name="fcpayone_form[bankcountry]" class="form-control form-control-select"
                        {if $blFcPayoneShowMandate}disabled="true"{/if}>
                    {foreach from=$aFcPayoneCountries key=iso item=name}
                        <option value="{$iso|escape:'html'}"
                                {if $iso eq $fcpayone_form.bankcountry}selected="selected"{/if}>{$name|escape:'html'}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        {*<div class="form-group row required">
            <label class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_PAYMENT_DEBIT_ACCOUNT_HOLDER')}
                <sup>*</sup></label>
            <div class="col-lg-5">
                <input class="form-control" type="text" name="fcpayone_form[bankaccountholder]" maxlength="50"
                       value="{if isset($fcpayone_form.bankaccountholder)}{$fcpayone_form.bankaccountholder}{/if}"
                       {if $blFcPayoneShowMandate}disabled="true"{/if} />
            </div>
        </div>*}
        {if $blFcPayoneShowMandate}
            <input type="hidden" name="fcpayone_form[bankdatatype]" value="{$fcpayone_form.bankdatatype|escape:'html':'UTF-8'}">
        {else}
            {if $oFcPayonePayment->showBankAccount()}
                <div id="bankdatatype" class="form-group row required text-center">
                    <input id="bankdatatype1" class="form-control" type="radio" name="fcpayone_form[bankdatatype]"
                           value="1"{if ( !isset($fcpayone_form.bankdatatype) || !$fcpayone_form.bankdatatype ) || $fcpayone_form.bankdatatype == '1'} checked{/if} {if $blFcPayoneShowMandate}disabled="true"{/if} />
                    <label for="bankdatatype1">
                        {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_IBAN')|escape:'html':'UTF-8'}
                        {if $oFcPayonePayment->showBic()}
                        /{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_BIC')|escape:'html':'UTF-8'}
                        {/if}
                    </label>
                    <input id="bankdatatype2" class="form-control" type="radio" name="fcpayone_form[bankdatatype]"
                           value="2"{if $fcpayone_form.bankdatatype == '2'} checked{/if} {if $blFcPayoneShowMandate}disabled="true"{/if} />
                    <label for="bankdatatype2">
                        {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_BANK_ACCOUNT')|escape:'html':'UTF-8'}
                        /{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_BANK_CODE')|escape:'html':'UTF-8'}
                    </label>
                </div>
            {else}
                <input type="hidden" name="fcpayone_form[bankdatatype]" value="{$fcpayone_form.bankdatatype|escape:'html':'UTF-8'}">
            {/if}
        {/if}
        <div id="ibanbic">
            <div class="form-group row required">
                <label for="iban" class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_IBAN')|escape:'html':'UTF-8'}
                    <sup>*</sup></label>
                <div class="col-lg-5">
                    <input id="iban" class="form-control js-payone-validate-input" type="text" name="fcpayone_form[iban]"
                           maxlength="35" value="{if isset($fcpayone_form.iban)}{$fcpayone_form.iban|escape:'html':'UTF-8'}{/if}"
                           {if $blFcPayoneShowMandate}disabled="true"{/if} />
                </div>
            </div>
            {if $oFcPayonePayment->showBic()}
                <div class="form-group row required">
                    <label for="bic" class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_BIC')|escape:'html':'UTF-8'}
                        <sup>*</sup></label>
                    <div class="col-lg-5">
                        <input id="bic" class="form-control js-payone-validate-input" type="text" name="fcpayone_form[bic]"
                               maxlength="11" value="{if isset($fcpayone_form.bic)}{$fcpayone_form.bic|escape:'html':'UTF-8'}{/if}"
                               {if $blFcPayoneShowMandate}disabled="true"{/if} />
                    </div>
                </div>
            {/if}
        </div>
        {if $oFcPayonePayment->showBankAccount()}
            <div id="bankaccountbankcode">
                <div class="form-group row required">
                    <label for="bankaccount" class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_BANK_ACCOUNT')|escape:'html':'UTF-8'}
                        <sup>*</sup></label>
                    <div class="col-lg-5">
                        <input id="bankaccount" class="form-control js-payone-validate-input" type="text"
                               name="fcpayone_form[bankaccount]" maxlength="14"
                               value="{if isset($fcpayone_form.bankaccount)}{$fcpayone_form.bankaccount|escape:'html':'UTF-8'}{/if}"
                               {if $blFcPayoneShowMandate}disabled="true"{/if} />
                    </div>
                </div>
                <div class="form-group row required">
                    <label for="bankcode" class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_BANK_CODE')|escape:'html':'UTF-8'}
                        <sup>*</sup></label>
                    <div class="col-lg-5">
                        <input id="bankcode" class="form-control js-payone-validate-input" type="text" name="fcpayone_form[bankcode]"
                               maxlength="8" value="{if isset($fcpayone_form.bankcode)}{$fcpayone_form.bankcode|escape:'html':'UTF-8'}{/if}"
                               {if $blFcPayoneShowMandate}disabled="true"{/if} />
                    </div>
                </div>
            </div>
        {/if}
        {if $blFcPayoneShowMandate}
            <input type="hidden" name="fcpayone_form[bankcountry]"
                   value="{if isset($fcpayone_form.bankcountry)}{$fcpayone_form.bankcountry|escape:'html':'UTF-8'}{/if}">
            <input type="hidden" name="fcpayone_form[bankaccountholder]"
                   value="{if isset($fcpayone_form.bankaccountholder)}{$fcpayone_form.bankaccountholder|escape:'html':'UTF-8'}{/if}">
            <input type="hidden" name="fcpayone_form[bankdatatype]"
                   value="{if isset($fcpayone_form.bankdatatype)}{$fcpayone_form.bankdatatype|escape:'html':'UTF-8'}{/if}">
            <input type="hidden" name="fcpayone_form[iban]"
                   value="{if isset($fcpayone_form.iban)}{$fcpayone_form.iban|escape:'html':'UTF-8'}{/if}">
            <input type="hidden" name="fcpayone_form[bic]"
                   value="{if isset($fcpayone_form.bic)}{$fcpayone_form.bic|escape:'html':'UTF-8'}{/if}">
            <input type="hidden" name="fcpayone_form[bankaccount]"
                   value="{if isset($fcpayone_form.bankaccount)}{$fcpayone_form.bankaccount|escape:'html':'UTF-8'}{/if}">
            <input type="hidden" name="fcpayone_form[bankcode]"
                   value="{if isset($fcpayone_form.bankcode)}{$fcpayone_form.bankcode|escape:'html':'UTF-8'}{/if}">
            <div class="form-group row">
                <label class="form-control-label col-lg-4"></label>
                <div class="col-lg-5">
                    {$sFcPayoneMandateText}
                </div>
                {if $sFcPayoneMandateStatus == 'pending'}
                    <label class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_DEBIT_MANDATE_ACCEPT')|escape:'html':'UTF-8'}
                        <sup>*</sup></label>
                    <div class="col-lg-5">
                        <div class="checkbox">
                            <label>
                                <input class="form-control js-payone-validate-input" type="checkbox"
                                       name="fcpayone_form[mandate_accepted]" value="1"><br/>
                            </label>
                        </div>
                    </div>
                {else}
                    <input type="hidden" name="fcpayone_form[mandate_accepted]" value="1"><br/>
                {/if}
            </div>
            <input type="hidden" name="fcpayone_form[mandate_loaded]" value="1">
        {else}
            <input type="hidden" name="fcpayone_form[mandate_load]" value="1">
        {/if}
    </fieldset>
</section>