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
    <form class="submitPayoneForm" name="submitPayoneDebitForm" method="post" action="#formPayonePanel"
          data-payone-confirm="{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_DEBIT_CONFIRM')}">
        <h4>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_ACTION')|escape:'html':'UTF-8'}: {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_ACTION_DEBIT')|escape:'html':'UTF-8'}</h4>
        <div class="form-group">
            <div class="input-group">
                <label for="payone_amount_debit">{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_AMOUNT')|escape:'html':'UTF-8'}
                    {if isset($sFcPayoneCurrencyIso)}
                        {$sFcPayoneCurrencyIso|escape:'html':'UTF-8'}
                    {/if}
                </label>
                {if $blFcPayoneDisableAmountInput}
                    <input type="hidden" name="payone_amount" value="{$dFcPayoneFixedAmount|escape:'html':'UTF-8'}">
                    <input id="payone_amount_debit" type="text" disabled name="payone_amount" class="form-control"
                           value="{$dFcPayoneFixedAmount|escape:'html':'UTF-8'}">
                {else}
                    <input id="payone_amount_debit" type="text" name="payone_amount" class="form-control"
                           value="{if isset($smarty.request.payone_amount)}{$smarty.request.payone_amount|escape:'html':'UTF-8'}{/if}">
                {/if}
            </div>
        </div>
        {if isset($blFcPayoneBankDataNeeded) && $blFcPayoneBankDataNeeded}
            <div class="form-group">
                <button class="btn btn-primary js-payone-open-bankdata">{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_ACTION_SHOW_BANKDATA')|escape:'html':'UTF-8'}</button>
            </div>
            <div class="js-payone-bankdata" style="display:none;">
                <div class="form-group">
                    <div class="input-group">
                        <label for="bankcountry">{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_BANKCOUNTRY')|escape:'html':'UTF-8'} </label>
                        <select id="bankcountry" class="selectbox" name="payone_bankcountry">
                            <option name="DE"
                                    {if isset($smarty.request.payone_bankcountry) && $smarty.request.payone_bankaccount == 'DE'}selected{/if}>
                                DE
                            </option>
                            <option name="AT"
                                    {if isset($smarty.request.payone_bankcountry) && $smarty.request.payone_bankaccount == 'AT'}selected{/if}>
                                AT
                            </option>
                            <option name="NL"
                                    {if isset($smarty.request.payone_bankcountry) && $smarty.request.payone_bankaccount == 'NL'}selected{/if}>
                                NL
                            </option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <label for="bankaccount">{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_BANKACCOUNT')|escape:'html':'UTF-8'} </label>
                        <input id="bankaccount" type="text" name="payone_bankaccount" class="form-control"
                               value="{if isset($smarty.request.payone_bankaccount)}{$smarty.request.payone_bankaccount|escape:'html':'UTF-8'}{/if}">
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <label for="bankcode">{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_BANKCODE')|escape:'html':'UTF-8'} </label>
                        <input id="bankcode" type="text" name="payone_bankcode" class="form-control"
                               value="{if isset($smarty.request.payone_bankcode)}{$smarty.request.payone_bankcode|escape:'html':'UTF-8'}{/if}">
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <label for="holder">{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_BANKACCOUNTHOLDER')|escape:'html':'UTF-8'} </label>
                        <input id="holder" type="text" name="payone_bankaccountholder" class="form-control"
                               value="{if isset($smarty.request.payone_bankaccountholder)}{$smarty.request.payone_bankaccountholder|escape:'html':'UTF-8'}{/if}">
                    </div>
                </div>
            </div>
        {/if}
        <div class="form-group">
            <button type="submit" name="submitPayoneDebit"
                    class="btn btn-primary">{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_ACTION_EXECUTE')|escape:'html':'UTF-8'}</button>
        </div>
    </form>
</div>