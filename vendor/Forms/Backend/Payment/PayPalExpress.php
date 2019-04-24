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

namespace Payone\Forms\Backend\Payment;

use Payone\Base\Registry;

class PayPalExpress extends Base
{
    /**
     * Returns form fields for paypal express payment
     *
     * @return array
     */
    public function getForm()
    {
        $aForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->getTitle(),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    $this->getFieldActive(),
                    $this->getFieldMode(),
                    $this->getFieldRequestType(),
                    array(
                        'type' => 'file_lang',
                        'label' => $this->translate('FC_PAYONE_BACKEND_PAYPAL_EXPRESS_IMG'),
                        'name' => 'FC_PAYONE_PAYPAL_EXPRESS_IMG',
                        'hint' => $this->translate('FC_PAYONE_BACKEND_PAYPAL_EXPRESS_IMG_DESC'),
                        'lang' => true,
                    ),

                ),
                'submit' => $this->getFieldSubmit(),
            ),
        );
        return $this->postProcess($aForm);
    }

    /**
     * Handles form updates
     * serialzes array data for config save
     *
     * @param string $sKey
     */
    public function handleUpdate($sKey)
    {
        if (strpos($sKey, 'FC_PAYONE_PAYPAL_EXPRESS_IMG') !== false) {
            foreach ($_FILES as $sFileKey => $aFile) {
                if ((strpos($sFileKey, $sKey)) !== false &&
                    $_FILES[$sFileKey]['name'] != '' && $_FILES[$sFileKey]['error'] == 0
                ) {
                    $sError = \ImageManager::validateUpload($_FILES[$sFileKey], 4000000);
                    if ($sError) {
                        return $sError;
                    }

                    $aFileName = explode('_', $sFileKey);
                    if (is_array($aFileName) && count($aFileName) > 0) {
                        $iLang = array_pop($aFileName);
                        $sFileName = 'paypal_express_' . $iLang . '.png';
                        $sFilePath = Registry::getHelper()->getModulePath() . 'views/img/Payment/Methods/' . $sFileName;
                        if (file_exists($sFilePath)) {
                            @unlink($sFilePath);
                        }
                        if (\ImageManager::resize($_FILES[$sFileKey]['tmp_name'], $sFilePath)) {
                            \Configuration::updateValue($sFileKey, $sFileName);
                        }
                    }
                }
            }
        } else {
            parent::handleUpdate($sKey);
        }
    }

    /**
     * Call form field getter fr given type
     * and return result
     *
     * @return array
     */
    public function getFields()
    {
        $aFields = array();
        $aForm = $this->getForm();
        foreach ($aForm['form']['input'] as $aInput) {
            if ($aInput['name'] == 'FC_PAYONE_PAYPAL_EXPRESS_IMG') {
                foreach (\Language::getLanguages(false) as $aLang) {
                    $aFields[] = $aInput['name'] . '_' . $aLang['id_lang'];
                }
            } else {
                $aFields[] = $aInput['name'];
            }
        }
        return $aFields;
    }
}
