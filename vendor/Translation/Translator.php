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

namespace Payone\Translation;

use \Payone\Base\Registry;

class Translator
{

    /**
     * Default lang array (en)
     *
     * @var array
     */
    protected static $aDefaultLang = array();

    /**
     * Flag for load state of default lang
     *
     * @var bool
     */
    protected static $blDefaultLangLoaded = false;

    /**
     * Translates string
     *
     * @param string $sString
     *
     * @return string
     */
    public static function translate($sString)
    {
        $sTranslation = \Translate::getModuleTranslation('fcpayone', $sString, 'translations');
        if ($sTranslation == $sString) {
            $sTranslation = self::getTranslationFromDefaultLang($sString);
        }
        return $sTranslation;
    }

    /**
     * Loads default lang
     * (en)
     */
    protected static function loadDefaultLang()
    {
        if (self::$blDefaultLangLoaded == false) {
            $sDefaultLangFile = Registry::getHelper()->getModulePath() . 'translations/en.php';
            if (file_exists($sDefaultLangFile)) {
                $_MODULE = array();
                require_once $sDefaultLangFile;
                self::$aDefaultLang = $_MODULE;
            }
            self::$blDefaultLangLoaded = true;
        }
    }

    /**
     * Returns default lang array
     *
     * @return array
     */
    protected static function getDefaultLang()
    {
        self::loadDefaultLang();
        return self::$aDefaultLang;
    }

    /**
     * Returns translated string or raw string if nothin was found
     *
     * @param $sString
     * @return string
     */
    protected static function getTranslationFromDefaultLang($sString)
    {
        $sKey = '<{fcpayone}prestashop>translations_' . md5($sString);
        $aDefaultLang = self::getDefaultLang();
        if ($aDefaultLang && isset($aDefaultLang[$sKey])) {
            return htmlspecialchars($aDefaultLang[$sKey], ENT_COMPAT, 'UTF-8');
        }
        return $sString;
    }
}
