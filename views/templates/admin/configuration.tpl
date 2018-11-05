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
<div class="tabbable js-root-tabbable">
    <ul class="nav nav-tabs js-root-nav-tabs">
        {foreach from=$aFcPayoneForms key=sFormType item=aForm name=formtypes}
            <li {if $sFormType == $sFcPayoneActiveFormType}class="active"{/if}>
                <a href="#pane-{$sFormType|escape:'html':'UTF-8'}-{$smarty.foreach.formtypes.iteration|escape:'html':'UTF-8'}" data-toggle="tab">
                    {assign var=sFcPayoneFormTitleIdent value='FC_PAYONE_BACKEND_CONFIGURATION_TITLE_'|cat:$sFormType|upper|escape:'html':'UTF-8'}
                    {$oFcPayoneTranslator->translate($sFcPayoneFormTitleIdent)|escape:'html':'UTF-8'}
                </a>
            </li>
        {/foreach}
    </ul>
    <div class="panel">
        <div class="tab-content">
            {foreach from=$aFcPayoneForms key=sFormType item=aFormTypes name=formtypes}
                {assign var=blFcPayoneHasActiveForm value=false}
                {foreach from=$aFormTypes item=aForm name=forms}
                    {if $aForm.active}
                        {assign var=blFcPayoneHasActiveForm value=true}
                    {/if}
                {/foreach}
                <div id="pane-{$sFormType|escape:'html':'UTF-8'}-{$smarty.foreach.formtypes.iteration|escape:'html':'UTF-8'}" class="tab-pane {if $sFormType == $sFcPayoneActiveFormType}active{/if}">
                    <div class="tabbable row tab-content js-sub-tabbable">
                        <div class="sidebar col-lg-3">
                            <ul class="nav nav-pills js-sub-nav-tabs">
                                {foreach from=$aFormTypes item=aForm name=forms}
                                    <li class="nav-item col-xs-12 {if $aForm.active || (!$blFcPayoneHasActiveForm && $smarty.foreach.forms.first)} active{/if}"><a href="#pane-{$sFormType|escape:'html':'UTF-8'}-sub{$smarty.foreach.forms.iteration|escape:'html':'UTF-8'}" data-toggle="tab">{$aForm.title|escape:'html':'UTF-8'}</a></li>
                                {/foreach}
                            </ul>
                        </div>
                        <div  class="tab-content col-lg-9">
                            {foreach from=$aFormTypes item=aForm name=forms}
                                <div id="pane-{$sFormType|escape:'html':'UTF-8'}-sub{$smarty.foreach.forms.iteration|escape:'html':'UTF-8'}" class="tab-pane {if $aForm.active || (!$blFcPayoneHasActiveForm && $smarty.foreach.forms.first)}active{/if}">
                                    {$aForm.content}
                                </div>
                            {/foreach}
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
</div>