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

namespace Payone\Helper;

class Helper
{

    /**
     * Params that should be masked
     *
     * @var array
     */
    protected $aSensitiveParams = array(
        'key',
        'portalid'
    );

    /**
     * Returns module path
     *
     * @return string
     */
    public function getModulePath()
    {
        return _PS_MODULE_DIR_ . 'fcpayone/';
    }

    /**
     * Returns module core path
     *
     * @return string
     */
    public function getModuleCorePath()
    {
        return $this->getModulePath() . 'Core';
    }

    /**
     * Returns module url
     *
     * @return string
     */
    public static function getModuleUrl()
    {
        return \Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/fcpayone/';
    }

    /**
     * Returns amount converted for request
     *
     * @param float $dAmount
     * @return float
     */
    public function getConvertedAmount($dAmount)
    {
        return (number_format($dAmount, 2, '.', '') * 100);
    }

    /**
     * Returns array with params for masking
     *
     * @return array
     */
    protected function getSensitiveParams()
    {
        return $this->aSensitiveParams;
    }

    /**
     * Cleans request for save
     *
     * @param array $aData
     * @return array
     */
    public function cleanData($aData)
    {
        foreach ($this->getSensitiveParams() as $sParam => $sValue) {
            if (isset($aData[$sParam])) {
                $aData[$sParam] = md5($sValue);
            }
        }

        return $aData;
    }

    /**
     * Build module url
     *
     * @param string $sController
     * @param array $aParams
     *
     * @return string
     */
    public function buildModuleUrl($sController, $aParams)
    {
        return \Context::getContext()->link->getModuleLink('fcpayone', $sController, $aParams);
    }

    /**
     * Returns detected encoding
     *  based on http://php.net/manual/de/function.mb-detect-encoding.php#113983
     * @param $sString
     * @return false|mixed|null|string
     */
    public function detectEncoding($sString)
    {
        $sEncoding = null;
        if (method_exists('mb_detect_encoding')) {
            $sEncoding = mb_detect_encoding($sString, 'UTF-8', true);
        } elseif (method_exists('iconv')) {
            $aEncodings = array(
                'UTF-8',
                'ASCII',
                'ISO-8859-1',
                'ISO-8859-2',
                'ISO-8859-3',
                'ISO-8859-4',
                'ISO-8859-5',
                'ISO-8859-6',
                'ISO-8859-7',
                'ISO-8859-8',
                'ISO-8859-9',
                'ISO-8859-10',
                'ISO-8859-13',
                'ISO-8859-14',
                'ISO-8859-15',
                'ISO-8859-16',
                'Windows-1251',
                'Windows-1252',
                'Windows-1254',
            );

            foreach ($aEncodings as $sEncodingItem) {
                $sEncodedString = iconv($sEncodingItem, $sEncodingItem, $sString);
                if (md5($sEncodedString) == md5($sString)) {
                    $sEncoding = $sEncodingItem;
                    break;
                }
            }
        }
        return $sEncoding;
    }

    /**
     * Check if string is utf-8
     *
     * @param $sString
     * @return bool
     */
    public function isUTF8($sString)
    {
        $sEncoding = $this->detectEncoding($sString);
        if (\Tools::strtolower($sEncoding) == 'utf-8') {
            return true;
        }
        return false;
    }

    /**
     * Converts string to utf-8
     * @param $sString
     * @return string
     */
    public function convertToUTF8($sString)
    {
        if (!$this->isUTF8($sString)) {
            if (method_exists('iconv')) {
                $sActEncoding = $this->detectEncoding($sString);
                $sString = iconv($sActEncoding, 'UTF-8', $sString);
            } else {
                $sString = utf8_encode($sString);
            }
        }
        return $sString;
    }
}
