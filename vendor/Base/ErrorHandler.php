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

namespace Payone\Base;

class ErrorHandler
{

    /**
     * Error key ident
     *
     * @var string
     */
    protected $sErrorKeyIdent = 'sFcPayoneErrorKeys';

    /**
     * Returns context instance
     *
     * @return mixed
     */
    protected function getContext()
    {
        return \Context::getContext();
    }

    /**
     * Returns error key ident
     *
     * @return string
     */
    protected function getErrorKeyIdent()
    {
        return $this->sErrorKeyIdent;
    }

    /**
     * Sets var to session
     *
     * @param string $sKey
     * @param string $sValue
     */
    public function setSessionVar($sKey, $sValue)
    {
        $this->getContext()->cookie->{$sKey} = $sValue;
    }

    /**
     * Gets var from session
     *
     * @param string $sKey
     * @return boolean
     */
    public function getSessionVar($sKey)
    {
        if (isset($this->getContext()->cookie->$sKey)) {
            return $this->getContext()->cookie->$sKey;
        }
        return false;
    }

    /**
     * Gets var from session
     *
     * @param string $sKey
     * @return boolean
     */
    public function deleteSessionVar($sKey)
    {
        if (isset($this->getContext()->cookie->$sKey)) {
            unset($this->getContext()->cookie->$sKey);
        }
    }

    /**
     * Set error
     *
     * @param string $sType
     * @param string $sMessage
     * @param boolean $blTranslate
     */
    public function setError($sType, $sMessage, $blTranslate = false)
    {
        $sErrorKey = uniqid();

        if ($blTranslate) {
            $sMessage = \Payone\Base\Registry::getTranslator()->translate($sMessage);
        }

        $this->setSessionVar($sErrorKey, $sMessage);
        $this->updateErrorKeys($sType, $sErrorKey);
    }

    /**
     * Update error key collection
     *
     * @param string $sType
     * @param string $sKey
     */
    protected function updateErrorKeys($sType, $sKey)
    {
        $aErrorKeys = \Tools::jsonDecode($this->getSessionVar($this->getErrorKeyIdent()), true);
        if (!isset($aErrorKeys[$sType]) || !is_array($aErrorKeys[$sType])) {
            $aErrorKeys[$sType] = array();
        }
        if (is_array($aErrorKeys[$sType]) && !in_array($sKey, $aErrorKeys[$sType])) {
            $aErrorKeys[$sType][] = $sKey;
        }
        $sErrorKeys = \Tools::jsonEncode($aErrorKeys);
        $this->setSessionVar($this->getErrorKeyIdent(), $sErrorKeys);
    }

    /**
     * Returns array with validation errors
     *
     * @param string $sType
     * @return array
     */
    public function getErrors($sType = null)
    {
        $aErrorKeys = $this->getErrorKeys($sType);
        $aErrors = array();
        if (is_array($aErrorKeys) && count($aErrorKeys) > 0) {
            foreach ($aErrorKeys as $sErrorKey) {
                $aErrors[] = $this->getSessionVar($sErrorKey);
            }
        }
        return $aErrors;
    }

    /**
     * Deletes error and removes key from collection
     *
     * @param string $sType
     */
    public function deleteErrors($sType = null)
    {
        $aErrorKeys = $this->getErrorKeys($sType);
        if (is_array($aErrorKeys) && count($aErrorKeys) > 0) {
            foreach ($aErrorKeys as $sErrorKey) {
                $this->deleteSessionVar($sErrorKey);
            }
            $this->deleteErrorKeys($sType);
        }
    }

    /**
     * Deeltes error key collection
     *
     * @param string $sType
     */
    protected function deleteErrorKeys($sType = null)
    {
        if (!$sType) {
            $this->setSessionVar($this->getErrorKeyIdent(), null);
        }
        $aTypeErrorKeys = $this->getErrorKeys($sType);
        $aAllErrorKeys = $this->getErrorKeys();
        if (isset($aTypeErrorKeys) && isset($aAllErrorKeys)) {
            foreach ($aAllErrorKeys as $sIndex => $sErrorKey) {
                if (in_array($sErrorKey, $aTypeErrorKeys)) {
                    unset($aAllErrorKeys[$sIndex]);
                }
            }
        }
        $sErrorKeys = \Tools::jsonEncode($aAllErrorKeys);
        $this->setSessionVar($this->getErrorKeyIdent(), $sErrorKeys);
    }

    /**
     * Returns error keys
     *
     * @param string $sType
     * @return array
     */
    protected function getErrorKeys($sType = null)
    {
        $aErrorKeys = array();
        $aSessionErrorKeys = \Tools::jsonDecode($this->getSessionVar($this->getErrorKeyIdent()), true);
        if ($sType && isset($aSessionErrorKeys[$sType]) &&
            is_array($aSessionErrorKeys[$sType]) && count($aSessionErrorKeys[$sType]) > 0
        ) {
            foreach ($aSessionErrorKeys[$sType] as $sErrorKey) {
                $aErrorKeys[] = $sErrorKey;
            }
        } elseif (is_array($aSessionErrorKeys)) {
            foreach ($aSessionErrorKeys as $sType => $aErrorKeysByType) {
                foreach ($aErrorKeysByType as $sErrorKey) {
                    $aErrorKeys[] = $sErrorKey;
                }
            }
        }
        return $aErrorKeys;
    }
}
