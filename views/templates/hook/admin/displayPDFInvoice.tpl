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

{if (isset($aFcPayoneLastRequestWithClearingData))}
	<p>
		<strong>{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_PLEASE_TRANSFER')|escape:'html':'UTF-8'}</strong>
	</p>
	<p>{strip}
        {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_BANKACCOUNTHOLDER')|escape:'html':'UTF-8'} {$aFcPayoneLastRequestWithClearingData.clearing_bankaccountholder|escape:'html':'UTF-8'}<br />
        {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_BANKACCOUNT')|escape:'html':'UTF-8'} {$aFcPayoneLastRequestWithClearingData.clearing_bankaccount|escape:'html':'UTF-8'}<br />
        {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_BANKCODE')|escape:'html':'UTF-8'} {$aFcPayoneLastRequestWithClearingData.clearing_bankcode|escape:'html':'UTF-8'}<br />
        {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_BANKNAME')|escape:'html':'UTF-8'} {$aFcPayoneLastRequestWithClearingData.clearing_bankname|escape:'html':'UTF-8'}<br />
        {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_IBAN')|escape:'html':'UTF-8'} {$aFcPayoneLastRequestWithClearingData.clearing_bankiban|escape:'html':'UTF-8'}<br />
        {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_BIC')|escape:'html':'UTF-8'} {$aFcPayoneLastRequestWithClearingData.clearing_bankbic|escape:'html':'UTF-8'}<br />
        {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_ORDER_TRANSFER_USAGE')|escape:'html':'UTF-8'} {$usage|escape:'html':'UTF-8'}<br />
		{/strip}
	</p>
{/if}