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
<section class="form-fields">
    <fieldset>
        <h3>{$oFcPayonePayment->getTitle()|escape:'html':'UTF-8'}</h3>
        {if $oFcPayonePayment->getDescription()}
            <p><strong class="dark">{$oFcPayonePayment->getDescription()|escape:'html':'UTF-8'}</strong></p>
            <br/>
        {/if}
        <p>
            {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_PAYMENT_ORDER_AMOUNT')|escape:'html':'UTF-8'}
            <span class="price">{Tools::displayPrice($total)}</span>
            {if (int)Configuration::get('PS_TAX') == 1}
                {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_PAYMENT_ORDER_AMOUNT_TAX')|escape:'html':'UTF-8'}
            {/if}
        </p>
        <div class="form-group row required">
            <label for="cardtype" class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CREDITCARD_TYPE')|escape:'html':'UTF-8'}
                <sup>*</sup></label>
            <div class="col-lg-5">
                <select id="cardtype" class="form-control form-control-select">
                    {foreach from=$aFcPayoneSubPayments item=oSubPayment}
                        <option value="{$oSubPayment->getSubClearingType()|escape:'html':'UTF-8'}"
                                data-payone-subpayment-id="{$oSubPayment->getId()|escape:'html':'UTF-8'}">{$oSubPayment->getTitle()|escape:'html':'UTF-8'}</option>
                    {/foreach}
                </select>
                <input type="hidden" name="payone_payment_sub" value=""/>
            </div>
        </div>
        <div class="form-group row required">
            <label for="cardpanInput"
                   class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CREDITCARD_PAN')|escape:'html':'UTF-8'}
                <sup>*</sup></label>
            <div class="col-lg-5 inputIframe" id="cardpan" data-iframe-height="39px" data-css="background: #f1f1f1; color: #7a7a7a; border: 1px solid rgba(0,0,0,.25); padding: .5rem 1rem; width: 99.8%; font-size: 1rem; line-height: 1.25">
            </div>
        </div>
        {if isset($blFcPayoneShowCvC) && $blFcPayoneShowCvC}
            <div class="form-group row required">
                <label for="cvcInput"
                       class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CREDITCARD_CVC')|escape:'html':'UTF-8'}
                    <sup>*</sup></label>
                <div class="col-lg-5 inputIframe" id="cardcvc2" data-iframe-height="39px" data-css="background: #f1f1f1; color: #7a7a7a; border: 1px solid rgba(0,0,0,.25); padding: .5rem 1rem; width: 90px; font-size: 1rem; line-height: 1.25">
                </div>
            </div>
        {/if}
        <div class="form-group row required">
            <label for="expireInput"
                   class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CREDITCARD_EXPIRE')|escape:'html':'UTF-8'}
                <sup>*</sup></label>
            <div class=" inputIframe" id="expireInput">
                <span class="col-lg-1" id="cardexpiremonth" data-iframe-height="42px" data-css="background: #f1f1f1; color: #7a7a7a; border: 1px solid rgba(0,0,0,.25); padding: .5rem 1rem; width: 60px; font-size: 1rem; line-height: 1.25"></span>
                <span class="col-lg-3" id="cardexpireyear" data-iframe-height="42px" data-css="background: #f1f1f1; color: #7a7a7a; border: 1px solid rgba(0,0,0,.25); padding: .5rem 1rem; width: 120px; font-size: 1rem; line-height: 1.25"></span>
            </div>
        </div>
        <div class="form-group row required">
            <label for="firstname"
                   class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CREDITCARD_FIRSTNAME')|escape:'html':'UTF-8'}
                <sup>*</sup></label>
            <div class="col-lg-5">
                <input id="firstname" class='form-control' id="firstname" type="text" name="fcpayone_form[firstname]"
                       value="">
            </div>

        </div>
        <div class="form-group row required">
            <label for="lastname"
                   class="form-control-label col-lg-4">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CREDITCARD_LASTNAME')|escape:'html':'UTF-8'}
                <sup>*</sup></label>

            <div class="col-lg-5">
                <input id="firstname" class='form-control' id="lastname" type="text" name="fcpayone_form[lastname]"
                       value="">
            </div>
        </div>
        <div class="form-group row required">
            <div id="errorOutput"></div>
        </div>
    </fieldset>
</section>
{*<div id="paymentform"></div>*}
<span style="display:none;" class="js-payone-request-config"
      data-request-lang="{$sFcPayoneRequestLang|escape:'html':'UTF-8'}"
      data-request="{$sFcPayoneJsRequestData|escape:'html':'UTF-8'}"
      data-validation-expiredate-error="{$oFcPayoneTranslator->translate('FC_PAYONE_ERROR_CREDITCARD_EXPIRE_INVALID')|escape:'html':'UTF-8'}"
      data-validation-fields-error="{$oFcPayoneTranslator->translate('FC_PAYONE_ERROR_FILL_FIELDS')|escape:'html':'UTF-8'}">
</span>