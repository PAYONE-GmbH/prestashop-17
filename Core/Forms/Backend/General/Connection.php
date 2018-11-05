<?php
/**
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
 * @author    patworx multimedia GmbH <service@patworx.de>
 * @copyright 2003 - 2018 BS PAYONE GmbH
 * @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link      http://www.payone.de
 */

namespace Payone\Forms\Backend\General;

class Connection extends \Payone\Forms\Backend\Base
{
    /**
     * Form ident
     *
     * @var string
     */
    protected $sIdent = 'Connection';

    /**
     * Returns form fields for connection form
     *
     * @return array
     */
    public function getForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->getTitle(),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    $this->getFieldActive(),
                    array(
                        'type' => 'switch',
                        'label' => $this->translate('FC_PAYONE_BACKEND_CONNECTION_MODE_LIVE'),
                        'name' => 'FC_PAYONE_CONNECTION_MODE_LIVE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'FC_PAYONE_CONNECTION_MODE_LIVE_ON',
                                'value' => true,
                            ),
                            array(
                                'id' => 'FC_PAYONE_CONNECTION_MODE_LIVE_OFF',
                                'value' => false,
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'hint' => $this->translate('FC_PAYONE_BACKEND_CONNECTION_MERCHANTID_DESC'),
                        'name' => 'FC_PAYONE_CONNECTION_MERCHANTID',
                        'label' => $this->translate('FC_PAYONE_BACKEND_CONNECTION_MERCHANTID'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'hint' => $this->translate('FC_PAYONE_BACKEND_CONNECTION_PORTALID_DESC'),
                        'name' => 'FC_PAYONE_CONNECTION_PORTALID',
                        'label' => $this->translate('FC_PAYONE_BACKEND_CONNECTION_PORTALID'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'hint' => $this->translate('FC_PAYONE_BACKEND_CONNECTION_PORTALKEY_DESC'),
                        'name' => 'FC_PAYONE_CONNECTION_PORTALKEY',
                        'label' => $this->translate('FC_PAYONE_BACKEND_CONNECTION_PORTALKEY'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'hint' => $this->translate('FC_PAYONE_BACKEND_CONNECTION_SUBID_DESC'),
                        'name' => 'FC_PAYONE_CONNECTION_SUBID',
                        'label' => $this->translate('FC_PAYONE_BACKEND_CONNECTION_SUBID'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'hint' => $this->translate('FC_PAYONE_BACKEND_CONNECTION_REF_PREFIX_DESC'),
                        'name' => 'FC_PAYONE_CONNECTION_REF_PREFIX',
                        'label' => $this->translate('FC_PAYONE_BACKEND_CONNECTION_REF_PREFIX'),
                    ),
                ),
                'submit' => $this->getFieldSubmit(),
            ),
        );
    }
}
