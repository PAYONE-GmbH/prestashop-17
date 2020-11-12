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

namespace Payone\Payment\Methods\Wallet;

use Payone\Base\Registry;

class PayPalExpress extends PayPal
{

    /**
     * ID
     *
     * @var string
     */
    protected $sId = 'wallet_paypal_express';

    /**
     * Payment templates
     *
     * @var array
     */
    protected $aPayPalExpressTemplates = array(
        'cart' => 'views/templates/front/Payment/Methods/paypal_express_btn_cart.tpl',
    );

    /**
     * Payment frontend controller
     *
     * @var string
     */
    protected $sController = 'paymentpaypalexpress';

    /**
     * Returns paypal express template path
     *
     * @param string $sType cart, details,...
     * @return string
     */
    public function getPayPalExpressTemplate($sType)
    {
        if (!$sType) {
            return;
        }
        if (version_compare(_PS_VERSION_, '1.7.0', '<')) {
            return str_replace(".tpl", "_legacy.tpl", $this->aPayPalExpressTemplates[$sType]);
        } else {
            return $this->aPayPalExpressTemplates[$sType];
        }

    }

    /**
     * Returns paypal express image
     *
     * @return string
     */
    public function getPayPalExpressImage()
    {
        $iActLang = (int)\Context::getContext()->cart->id_lang;
        $sImage = \Configuration::get('FC_PAYONE_PAYPAL_EXPRESS_IMG_' . $iActLang);
        if (!$sImage) {
            $sImage = \Configuration::get(
                'FC_PAYONE_PAYPAL_EXPRESS_IMG_' . (int)\Configuration::get('PS_LANG_DEFAULT')
            );
        }
        return Registry::getHelper()->getModuleUrl() . 'views/img/Payment/Methods/' . $sImage;
    }

    /**
     * Checks if payment is valid for checkout
     * no country validation
     *
     * @return boolean
     */
    public function isValidForCheckout()
    {
        if ($this->isActive()) {
            return true;
        }
        return false;
    }
}
