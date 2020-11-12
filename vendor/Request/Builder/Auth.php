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
 * @copyright 2003 - 2020 BS PAYONE GmbH
 * @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link      http://www.payone.de
 */

namespace Payone\Request\Builder;

class Auth extends Base
{

    /**
     * Sets auth params
     */
    public function build()
    {
        parent::build();
        $this->setParam('mid', \Configuration::get('FC_PAYONE_CONNECTION_MERCHANTID')); //Payone Merchant ID
        $this->setParam('portalid', \Configuration::get('FC_PAYONE_CONNECTION_PORTALID')); //Payone Portal ID
        $this->setParam('key', md5(\Configuration::get('FC_PAYONE_CONNECTION_PORTALKEY'))); //Payone Portal Key
        $this->setParam('aid', \Configuration::get('FC_PAYONE_CONNECTION_SUBID')); //Payone Sub ID
        $this->setParam('encoding', 'UTF-8'); //Encoding
        $this->setParam('integrator_name', 'prestashop');
        $this->setParam('integrator_version', _PS_VERSION_);
        $this->setParam('solution_name', 'fatchip');
        $this->setParam('solution_version', _FCPAYONE_VERSION_);
        $this->setParam('api_version', '3.10');
    }
}
