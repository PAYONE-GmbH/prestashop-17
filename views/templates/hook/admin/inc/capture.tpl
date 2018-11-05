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
<div class="col-xs-6">
    <form class="submitPayoneForm" name="submitPayoneCaptureForm" method="post" action="#formPayonePanel"
          data-payone-confirm="{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_CAPTURE_CONFIRM')}">
        <h4>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_ACTION')|escape:'html':'UTF-8'}: {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_ACTION_CAPTURE')|escape:'html':'UTF-8'}</h4>
        <div class="row">
            <div class="col-xs-12">
                {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_PREAUTHORIZED_AMOUNT')|escape:'html':'UTF-8'}:
                {$aFcPayoneFirstTransactionData.data.price|escape:'html':'UTF-8'}
                {if isset($sFcPayoneCurrencyIso)}
                    {$sFcPayoneCurrencyIso|escape:'html':'UTF-8'}
                {/if}
            </div>
        </div>
        <div class="form-group">
            <div class="input-group">
                <label for="payone_amount_capture">{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_AMOUNT')|escape:'html':'UTF-8'}
                    {if isset($sFcPayoneCurrencyIso)}
                        {$sFcPayoneCurrencyIso|escape:'html':'UTF-8'}
                    {/if}
                </label>
                {if $blFcPayoneDisableAmountInput}
                    <input type="hidden" name="payone_amount" value="{$dFcPayoneFixedAmount|escape:'html':'UTF-8'}">
                    <input id="payone_amount_capture" type="text" disabled name="payone_amount" class="form-control"
                       value="{$dFcPayoneFixedAmount|escape:'html':'UTF-8'}">
                {else}
                    <input id="payone_amount_capture" type="text" name="payone_amount" class="form-control"
                       value="{if isset($smarty.request.payone_amount)}{$smarty.request.payone_amount|escape:'html':'UTF-8'}{/if}">
                {/if}
            </div>
        </div>

        {if isset($blFcPayoneAccountSettlement) && $blFcPayoneAccountSettlement}
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="hidden" name="payone_settleaccount" value="0">
                        <input type="checkbox" name="payone_settleaccount" value="1" checked>
                        {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_SETTLEACCOUNT')|escape:'html':'UTF-8'}
                    </label>
                </div>
            </div>
        {/if}

        <div class="form-group">
            <button type="submit" name="submitPayoneCapture"
                    class="btn btn-primary">{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_ACTION_EXECUTE')|escape:'html':'UTF-8'}</button>
        </div>
    </form>
</div>