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
<div class="panel bootstrap">
    <div class="row">
        <div class="col-sm-3">
            <img src="{$sFcPayoneLogo|escape:'html':'UTF-8'}" class="img-responsive">
        </div>
        <div class="col-sm-9">
            {$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_INFO_PAYONE_DESC')|escape:'html':'UTF-8'}
        </div>
    </div>
    {if isset($sFcPayoneButtonUrl)}
    <br/>
    <br/>
    <div class="row">
        <div class="col-sm-3">
            <a class="btn btn-default button-payone" target="_blank" href="{$sFcPayoneButtonUrl|escape:'html':'UTF-8'}">{$oFcPayoneTranslator->translate('FC_PAYONE_BACKEND_INFO_PAYONE_BUTTON')|escape:'html':'UTF-8'}</a>
        </div>
    </div>
    {/if}
</div>
