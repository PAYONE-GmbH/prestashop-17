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

namespace Payone\Validation\Request;

class Request extends \Payone\Validation\Base
{

    /**
     * Needed request params for request
     *
     * @var array
     */
    protected $aNeededRequestParams = array(
        'mid',
        'portalid',
        'key',
        'mode'
    );

    /**
     * Performes different checks for request
     *
     * @param array $aRequest
     * @return boolean
     */
    public function validateRequest($aRequest)
    {

        if (!$aRequest) {
            $this->setError('validation', 'FC_PAYONE_ERROR_NO_REQUEST', true);
        }

        return $this->isValidRequest($aRequest);
    }

    /**
     * Checks if request is valid
     * @param array $aRequest
     * @return mixed
     */
    protected function isValidRequest($aRequest)
    {
        foreach ($this->aNeededRequestParams as $sParam) {
            if (!isset($aRequest[$sParam]) || $aRequest[$sParam] === '' || $aRequest[$sParam] === false) {
                $this->setError('validation', 'FC_PAYONE_ERROR_MISSING_CREDENTIALS', true);
                return false;
            }
        }
        return true;
    }
}
