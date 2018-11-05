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

{extends file='page.tpl'}

{block name='page_content'}

    {assign var='current_step' value='payment'}

    {if $nbProducts <= 0}
    <p class="warning">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CART_EMPTY')|escape:'html':'UTF-8'}</p>
    {else}

    {if isset($blFcPayoneError) && $blFcPayoneError}
    <div class="alert alert-danger">
        <p>{$oFcPayoneTranslator->translate('FC_PAYONE_ERROR_FILL_FIELDS')|escape:'html':'UTF-8'}</p>
        <ol>
            {foreach item=error from=$aFcPayoneErrorMessages}
            <li class="error">{$error|escape:'html':'UTF-8'}</li>
            {/foreach}
        </ol>
    </div>
    {/if}
    <div class="alert alert-danger js-payone-alert-box" style="display: none;"></div>
    <div class="row">
        <div class="col-xs-12">
            {if $oFcPayonePayment->isGroupedPayment()}
                {assign var=sFcPayonePaymentId value=$oFcPayonePayment->getParentId()}
                {assign var=sFcPayoneSubPaymentId value=$oFcPayonePayment->getId()}
            {else}
                {assign var=sFcPayonePaymentId value=$oFcPayonePayment->getId()}
                {assign var=sFcPayoneSubPaymentId value=false}
            {/if}
            <form action="{$link->getModuleLink($sFcPayoneModuleId, $oFcPayonePayment->getController(), ['payone_payment' => $sFcPayonePaymentId, 'payone_payment_sub' => $sFcPayoneSubPaymentId, 'payone_validate' => true], true)|escape:'htmlall':'UTF-8'}" method="post" class="std form-horizontal" id="mainPaymentForm">
                <input type="hidden" name="payone_secure_key" value="{$sPayoneSecureKey|escape:'html':'UTF-8'}">
                {$sFcPayonePaymentForm nofilter}
                {if isset($iFcPayoneConditions) AND isset($iFcPayoneConditionCmsId)}
                    <div class="row">
                        <div class="col-xs-12 col-md-12">
                            <h2>{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_TERMS_TITLE')|escape:'html':'UTF-8'}</h2>
                            <div class="box">
                                <p class="checkbox">
                                    <input type="checkbox" name="cgv" id="cgv" value="1"  />
                                    <label for="cgv">
                                        {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_TERMS_I_AGREE')|escape:'html':'UTF-8'}
                                    </label>
                                    <a href="{$sFcPayoneConditionLink|escape:'html':'UTF-8'}" class="iframe" rel="nofollow">{$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_TERMS_READ')|escape:'html':'UTF-8'}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                {/if}
                <p id="cart_navigation" class="cart_navigation clearfix submit">
                    <a class="button-exclusive btn btn-default" href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}">
                        <i class="icon-chevron-left"></i>
                        {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CHOOSE_OTHER_PAYMENT')|escape:'html':'UTF-8'}
                    </a>

                    <button id="submitOrder" class="button btn btn-default standard-checkout button-medium js-payone-payment-submit"  type="submit">
                        <span>
                            {if isset($blFcPayoneShowContinueButton) && $blFcPayoneShowContinueButton}
                                {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_CONTINUE')|escape:'html':'UTF-8'}
                            {else}
                                {$oFcPayoneTranslator->translate('FC_PAYONE_FRONTEND_COMPLETE_ORDER')|escape:'html':'UTF-8'}
                            {/if}
                            <i class="icon-chevron-right right"></i>
                        </span>
                    </button>
                </p>
                <input type="hidden" name="fcpayonesubmit" value="1">
            </form>
        </div>
    </div>
    {/if}

{/block}