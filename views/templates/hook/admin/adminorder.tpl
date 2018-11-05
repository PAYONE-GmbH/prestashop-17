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
<div id="formPayonePanel" class="panel">
    <div class="panel-heading">
        <i class="icon-money"></i>
        {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_PANEL_TITLE')|escape:'html':'UTF-8'}
    </div>
    <div class="panel-body">

        {if isset($aFcPayoneErrors)}
            <div class="row">
                <div class="alert alert-danger">
                    <ol>
                        {foreach from=$aFcPayoneErrors item=sError}
                            <li class="error">{$sError|escape:'html':'UTF-8'}</li>
                        {/foreach}
                    </ol>
                </div>
            </div>
        {/if}

        <div class="row">
            {if isset($aFcPayoneOrderData)}
                <div class="col-xs-6">
                    <h4>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_INFO')|escape:'html':'UTF-8'}</h4>
                    <div class="table-responsive">
                        <table class="table" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_TXID')|escape:'html':'UTF-8'}</td>
                                <td>{$aFcPayoneOrderData.txid|escape:'html':'UTF-8'}</td>
                            </tr>
                            <tr>
                                <td>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_USERID')|escape:'html':'UTF-8'}</td>
                                <td>{$aFcPayoneOrderData.userid|escape:'html':'UTF-8'}</td>
                            </tr>
                            <tr>
                                <td>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_REFERENCE')|escape:'html':'UTF-8'}</td>
                                <td>{$aFcPayoneOrderData.reference|escape:'html':'UTF-8'}</td>
                            </tr>
                            {if isset($sFcPayoneDownloadLink)}
                                <tr>
                                    <td>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_DOWNLOAD_PDF')|escape:'html':'UTF-8'}</td>
                                    <td>
                                        <a href="{$sFcPayoneDownloadLink|escape:'html':'UTF-8'}" target="_blank">
                                            {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_DOWNLOAD')|escape:'html':'UTF-8'}
                                        </a>
                                    </td>
                                </tr>
                            {/if}
                            {if isset($aFcPayoneFirstTransaction)}
                                {if isset($aFcPayoneFirstTransaction.data.iban)}
                                    <tr>
                                        <td>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_IBAN')|escape:'html':'UTF-8'}</td>
                                        <td>{$aFcPayoneFirstTransaction.data.iban|escape:'html':'UTF-8'}</td>
                                    </tr>
                                {/if}
                                {if isset($aFcPayoneFirstTransaction.data.bic)}
                                    <tr>
                                        <td>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_BIC')|escape:'html':'UTF-8'}</td>
                                        <td>{$aFcPayoneFirstTransaction.data.bic|escape:'html':'UTF-8'}</td>
                                    </tr>
                                {/if}
                                {if isset($aFcPayoneFirstTransaction.data.cardexpiredate)}
                                    <tr>
                                        <td>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_CARD_EXPIRE')|escape:'html':'UTF-8'}</td>
                                        <td>{$aFcPayoneFirstTransaction.data.cardexpiredate|escape:'html':'UTF-8'}</td>
                                    </tr>
                                {/if}
                                {if isset($oFcPayonePayment)}
                                    <tr>
                                        <td>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_PAYMENT_TYPE')|escape:'html':'UTF-8'}</td>
                                        <td>{$oFcPayonePayment->getTitle()|escape:'html':'UTF-8'}</td>
                                    </tr>
                                {/if}
                                {if isset($aFcPayoneFirstTransaction.data.cardpan)}
                                    <tr>
                                        <td>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_CARD_PAN')|escape:'html':'UTF-8'}</td>
                                        <td>{$aFcPayoneFirstTransaction.data.cardpan|escape:'html':'UTF-8'}</td>
                                    </tr>
                                {/if}
                            {/if}
                        </table>
                    </div>
                </div>
            {/if}
            {if isset($aFcPayoneLastRequest)}
                <div class="col-xs-6">
                    <h4 class="clear">{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_LAST_REQUEST')|escape:'html':'UTF-8'}</h4>
                    {if isset($aFcPayoneLastRequest.status) && $aFcPayoneLastRequest.status == 'APPROVED'}
                        <span class="alert alert-success"
                              style="display:inline-block;">{$aFcPayoneLastRequest.status|escape:'html':'UTF-8'}</span>
                    {elseif $aFcPayoneLastRequest.status == 'ERROR'}
                        {if isset($aFcPayoneLastRequest.errormessage)}
                            <span class="alert alert-danger"
                                  style="display:inline-block;">{$aFcPayoneLastRequest.errormessage|escape:'html':'UTF-8'}</span>
                        {else}
                            <span class="alert alert-danger"
                                  style="display:inline-block;">{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_REQUEST_ERROR')|escape:'html':'UTF-8'}</span>
                        {/if}
                    {elseif isset($aFcPayoneLastRequest.status)}
                        <span class="alert alert-info"
                              style="display:inline-block;">{$aFcPayoneLastRequest.status|escape:'html':'UTF-8'}</span>
                    {/if}
                </div>
            {/if}
        </div>

        <div class="row">
            {if isset($sFcPayoneCaptureForm)}
                {$sFcPayoneCaptureForm}
            {/if}
            {if isset($sFcPayoneDebitForm)}
                {$sFcPayoneDebitForm}
            {/if}
        </div>


        {if isset($aFcPayoneTransactions)}
            <div class="row">
                <div class="col-xs-12">
                    <h4>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_TRANSACTIONS')|escape:'html':'UTF-8'}</h4>
                    <div class="table-responsive">
                        <table class="table" cellpadding="0" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_TRANSACTION_DATE')|escape:'html':'UTF-8'}</th>
                                <th>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_TRANSACTION_ACTION')|escape:'html':'UTF-8'}</th>
                                <th>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_TRANSACTION_RECEIVABLE')|escape:'html':'UTF-8'}</th>
                                <th>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_TRANSACTION_BALANCE')|escape:'html':'UTF-8'}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach from=$aFcPayoneTransactions item=aTransaction name=transactionList}
                                {if isset($aTransaction) && isset($aTransaction.txaction) }
                                    <tr>
                                        <td>{dateFormat date=$aTransaction.date full=true}</td>
                                        <td>{$oFcPayoneTranslator->translate($aTransaction.txaction_langident)|escape:'html':'UTF-8'}</td>
                                        <td>
                                            {if isset($aTransaction.calculated_receivable)}
                                                {$aTransaction.calculated_receivable|escape:'html':'UTF-8'}
                                            {/if}
                                        </td>
                                        <td>
                                            {if isset($aTransaction.calculated_payment)}
                                                {$aTransaction.calculated_payment|escape:'html':'UTF-8'}
                                            {/if}
                                        </td>
                                        <td class="actions">
                                            <button class="btn btn-default open_payment_information">
                                                {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_TRANSACTION_DETAILS')|escape:'html':'UTF-8'}
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="payment_information" style="display:none;">
                                        <td colspan="5">
                                            <div class="table-responsive">
                                                <table class="table" cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <th>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_TRANSACTION_PARAM')|escape:'html':'UTF-8'}</th>
                                                        <th>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_TRANSACTION_VALUE')|escape:'html':'UTF-8'}</th>
                                                    </tr>
                                                    {foreach from=$aTransaction.data key=sParam item=sValue}
                                                        {if $sValue|is_array}
                                                            {foreach from=$sValue item=sSubValue name=subvalues}
                                                                <tr>
                                                                    <td>{$sParam|escape:'html':'UTF-8'}
                                                                        : {$smarty.foreach.subvalues.iteration|escape:'html':'UTF-8'}</td>
                                                                    <td>{$sSubValue|escape:'html':'UTF-8'}</td>
                                                                </tr>
                                                            {/foreach}
                                                        {else}
                                                            <tr>
                                                                <td>{$sParam|escape:'html':'UTF-8'}</td>
                                                                <td>{$sValue|escape:'html':'UTF-8'}</td>
                                                            </tr>
                                                        {/if}
                                                    {/foreach}
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                    {if $smarty.foreach.transactionList.last}
                                        <tr>
                                            <td colspan="6" align="right">
                                                <span {if $aTransaction.data.balance > 0}class="text-danger"{/if}>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_TRANSACTION_END_BALANCE')|escape:'html':'UTF-8'}&nbsp;{$aTransaction.data.balance}</span>
                                                {if isset($sFcPayoneCurrencyIso)}
                                                    {$sFcPayoneCurrencyIso|escape:'html':'UTF-8'}
                                                {/if}
                                            </td>
                                        </tr>
                                    {/if}
                                {/if}
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        {/if}
    </div>
</div>
