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

namespace Payone\Validation\Response;

class Response extends \Payone\Validation\Base
{

    /**
     * Validates response
     *
     * @param array $aResponse
     * @return boolean
     */
    public function validatePaymentResponse($aResponse)
    {
        $blNoErrors = $this->checkErrors($aResponse);
        return $blNoErrors;
    }

    /**
     * Validates response
     *
     * @param array $aResponse
     * @return boolean
     */
    public function validateMandateManageResponse($aResponse)
    {
        $blNoErrors = $this->checkErrors($aResponse);
        return $blNoErrors;
    }

    /**
     * Validates response
     *
     * @param array $aResponse
     * @return boolean
     */
    public function validateMandateGetFileResponse($aResponse)
    {
        $blNoErrors = $this->checkErrors($aResponse);
        return $blNoErrors;
    }

    /**
     * Validates response
     *
     * @param array $aResponse
     * @return boolean
     */
    public function validateOrderActionResponse($aResponse)
    {
        $blNoErrors = $this->checkErrors($aResponse);
        return $blNoErrors;
    }

    /**
     * Checks for error and sets payone
     * customermessage to output
     *
     * @param array $aResponse
     *
     * @return boolean
     */
    protected function checkErrors($aResponse)
    {
        if (isset($aResponse['status']) && $aResponse['status'] == 'ERROR') {
            $this->setError('validation', $aResponse['customermessage']);
            return false;
        }
        return true;
    }
}
