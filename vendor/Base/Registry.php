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

namespace Payone\Base;

class Registry
{

    /**
     * Instance array
     *
     * @var array
     */
    protected static $aInstances = array();

    /**
     * Instance getter. Return existing instance or initializes the new one
     *
     * @param string $sRawClassName Class name
     *
     * @static
     *
     * @return Object
     */
    public static function get($sRawClassName)
    {
        $sClassName = \Tools::strtolower($sRawClassName);
        if (isset(self::$aInstances[$sClassName])) {
            return self::$aInstances[$sClassName];
        } else {
            self::$aInstances[$sClassName] = new $sClassName;
            return self::$aInstances[$sClassName];
        }
    }

    /**
     * Returns FcPayoneErrorHandler instance
     *
     * @static
     *
     * @return \Payone\Base\ErrorHandler
     */
    public static function getErrorHandler()
    {
        return self::get('\Payone\Base\ErrorHandler');
    }

    /**
     * Returns FcPayoneHelper instance
     *
     * @static
     *
     * @return \Payone\Helper\Helper
     */
    public static function getHelper()
    {
        return self::get('\Payone\Helper\Helper');
    }

    /**
     * Returns FcPayonePayment instance
     *
     * @static
     *
     * @return \Payone\Payment\Payment
     */
    public static function getPayment()
    {
        return self::get('\Payone\Payment\Payment');
    }

    /**
     * Returns FcPayoneHelperPrestashop instance
     *
     * @static
     *
     * @return \Payone\Base\ErrorHandler
     */
    public static function getHelperPrestashop()
    {
        return self::get('\Payone\Helper\HelperPrestashop');
    }

    /**
     * Returns transaction instance
     *
     * @static
     *
     * @return \Payone\Base\Transaction
     */
    public static function getTransaction()
    {
        return self::get('\Payone\Base\Transaction');
    }

    /**
     * Returns request instance
     *
     * @static
     *
     * @return \Payone\Request\Request
     */
    public static function getRequest()
    {
        return self::get('\Payone\Request\Request');
    }

    /**
     * Returns user instance
     *
     * @static
     *
     * @return \Payone\Base\User
     */
    public static function getUser()
    {
        return self::get('\Payone\Base\User');
    }

    /**
     * Returns cookie instance
     *
     * @static
     *
     * @return \Payone\Base\Cookie
     */
    public static function getCookie()
    {
        return self::get('\Payone\Base\Cookie');
    }

    /**
     * Returns translator instance
     *
     * @static
     *
     * @return \Payone\Translation\Translator
     */
    public static function getTranslator()
    {
        return self::get('\Payone\Translation\Translator');
    }

    /**
     * Returns log instance
     *
     * @static
     *
     * @return \Payone\Base\Log
     */
    public static function getLog()
    {
        return self::get('\Payone\Base\Log');
    }
}
