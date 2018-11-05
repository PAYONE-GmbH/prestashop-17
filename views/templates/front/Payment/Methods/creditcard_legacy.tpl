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
<script type="text/javascript" src="https://secure.pay1.de/client-api/js/v1/payone_hosted_min.js"></script>
{capture name=path}{$oFcPayonePayment->getTitle()|escape:'html':'UTF-8'}{/capture}
<div class="box">
    <fieldset>
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
        <div class="form-group required">
            <label for="cardtype" class="control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CREDITCARD_TYPE')|escape:'html':'UTF-8'}
                <sup>*</sup></label>
            <div class="col-lg-5">
                <select id="cardtype" class="form-control">
                    {foreach from=$aFcPayoneSubPayments item=oSubPayment}
                        <option value="{$oSubPayment->getSubClearingType()|escape:'html':'UTF-8'}"
                                data-payone-subpayment-id="{$oSubPayment->getId()|escape:'html':'UTF-8'}">{$oSubPayment->getTitle()|escape:'html':'UTF-8'}</option>
                    {/foreach}
                </select>
                <input type="hidden" name="payone_payment_sub" value=""/>
            </div>
        </div>
        <div class="form-group required">
            <label for="cardpanInput"
                   class="control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CREDITCARD_PAN')|escape:'html':'UTF-8'}
                <sup>*</sup></label>
            <div class="col-lg-5 inputIframe" id="cardpan">
            </div>
        </div>
        {if isset($blFcPayoneShowCvC) && $blFcPayoneShowCvC}
            <div class="form-group required">
                <label for="cvcInput"
                       class="control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CREDITCARD_CVC')|escape:'html':'UTF-8'}
                    <sup>*</sup></label>
                <div class="col-lg-5 inputIframe" id="cardcvc2">
                </div>
            </div>
        {/if}
        <div class="form-group required">
            <label for="expireInput"
                   class="control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CREDITCARD_EXPIRE')|escape:'html':'UTF-8'}
                <sup>*</sup></label>
            <div class=" inputIframe" id="expireInput">
                <span class="col-lg-1" id="cardexpiremonth"></span>
                <span class="col-lg-1" id="cardexpireyear"></span>
            </div>
        </div>
        <div class="form-group required">
            <label for="firstname"
                   class="control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CREDITCARD_FIRSTNAME')|escape:'html':'UTF-8'}
                <sup>*</sup></label>
            <div class="col-lg-5">
                <input id="firstname" class='form-control' id="firstname" type="text" name="fcpayone_form[firstname]"
                       value="">
            </div>

        </div>
        <div class="form-group required">
            <label for="lastname"
                   class="control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CREDITCARD_LASTNAME')|escape:'html':'UTF-8'}
                <sup>*</sup></label>

            <div class="col-lg-5">
                <input id="firstname" class='form-control' id="lastname" type="text" name="fcpayone_form[lastname]"
                       value="">
            </div>
        </div>
        <div class="form-group required">
            <div id="errorOutput"></div>
        </div>
    </fieldset>
</div>
{*<div id="paymentform"></div>*}
<span style="display:none;" class="js-payone-request-config"
      data-request-lang="{$sFcPayoneRequestLang|escape:'html':'UTF-8'}"
      data-request="{$sFcPayoneJsRequestData|escape:'html':'UTF-8'}"
      data-validation-expiredate-error="{$oFcPayoneTranslator->translate('FC_PAYONE_ERROR_CREDITCARD_EXPIRE_INVALID')|escape:'html':'UTF-8'}"
      data-validation-fields-error="{$oFcPayoneTranslator->translate('FC_PAYONE_ERROR_FILL_FIELDS')|escape:'html':'UTF-8'}">
</span>