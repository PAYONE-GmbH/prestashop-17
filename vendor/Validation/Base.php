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

namespace Payone\Validation;

use Payone\Base\Registry;

class Base
{

    /**
     * Validation after redirect flag
     *
     * @var bool
     */
    protected $blAfterRedirect = false;

    /**
     * Error setter wrapper
     * @param string $sType
     * @param string $sMessage
     * @param boolean $blTranslate
     * @return void
     */
    protected function setError($sType, $sMessage, $blTranslate = false)
    {
        Registry::getErrorHandler()->setError($sType, $sMessage, $blTranslate);
    }


    /**
     * Sets validation mode to after redirect
     *
     * @param boolean $blIsAfterRedirect
     */
    public function setAfterRedirect($blIsAfterRedirect)
    {
        $this->blAfterRedirect = $blIsAfterRedirect;
    }

    /**
     * Returns true if validation is in after redirect mode
     *
     * @return boolean
     */
    protected function isAfterRedirect()
    {
        return $this->blAfterRedirect;
    }

    /**
     * Removes MSIE(\s)?(\S)*(\s) from browser agent information
     *
     * @param string $sAgent browser user agent idenfitier
     *
     * @return string
     */
    protected function cleanUserAgent($sAgent)
    {
        if ($sAgent) {
            $sAgent = preg_replace("/MSIE(\s)?(\S)*(\s)/", "", (string)$sAgent);
        }
        return $sAgent;
    }

    /**
     * Throws exception if useragent dosnt match
     *
     * @throw Exception
     */
    public function isValidUserAgent()
    {
        $sActUserAgent = $this->cleanUserAgent($_SERVER['HTTP_USER_AGENT']);
        $sCheckUserAgent = $this->cleanUserAgent(\Context::getContext()->cookie->sFcPayoneUserAgent);
        if ($sActUserAgent != $sCheckUserAgent) {
            throw new \Exception('FC_PAYONE_ERROR_USER_AGENT');
        }
    }
}
