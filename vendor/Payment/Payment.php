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

namespace Payone\Payment;

class Payment
{

    /**
     * Array with payment method names
     * dont add OnlineTransfer or CreditCard
     * these are payment collections, it would break
     *
     * @var array
     */
    protected static $aPaymentMethodBaseNames = array(
        'AdvancePayment',
        'CashOnDelivery',
        'CreditCard',
        'Debit',
        'Invoice',
        'BSInvoice',
        'Wallet',
        'OnlineTransfer',
    );

    /**
     * Array with payment method names
     * dont add OnlineTransfer or CreditCard
     * these are payment collections, it would break
     *
     * @var array
     */
    protected static $aSubPaymentMethodBaseNames = array(
        'CreditCard' => array(
            'Visa',
            'MasterCard',
            'Amex',
            'Diners',
            'Jcb',
            'MaestroInternational',
            'MaestroUK',
            'Discover',
            'CarteBleue',
        ),
        'OnlineTransfer' => array(
            'Eps',
            'Giropay',
            'IDeal',
            'PostFinanceCard',
            'PostFinanceEFinance',
            'Sofortbanking',
        ),
        'Wallet' => array(
            'PayPal',
            'PayPalExpress',
            'Paydirekt',
        ),
    );

    /**
     * Payment method classes
     *
     * @var array
     */
    protected $aPaymentMethodClasses = null;

    /**
     * Array with all payment methods instances
     *
     * @var arrays
     */
    protected $aPaymentMethods = null;

    /**
     * Sub Payment method classes
     *
     * @var array
     */
    protected $aSubPaymentMethodClasses = null;

    /**
     * Array with all sub payment methods instances
     *
     * @var arrays
     */
    protected $aSubPaymentMethods = null;

    /**
     * Returns base names
     *
     * @return array
     */
    public static function getPaymentMethodBaseNames()
    {
        return self::$aPaymentMethodBaseNames;
    }

    /**
     * Returns base names for sub payments
     *
     * @param main payment type
     * @return mixed
     */
    public static function getSubPaymentMethodBaseNames($sMainType = null)
    {
        if ($sMainType) {
            if (isset(self::$aSubPaymentMethodBaseNames[$sMainType])) {
                return self::$aSubPaymentMethodBaseNames[$sMainType];
            }
        } else {
            return self::$aSubPaymentMethodBaseNames;
        }
    }

    /**
     * Returns array with payment method classes
     *
     * @return array
     */
    protected function getPaymentMethodClasses()
    {
        if ($this->aPaymentMethodClasses == null) {
            $aClassNames = array();
            $aBaseNames = self::getPaymentMethodBaseNames();
            foreach ($aBaseNames as $sName) {
                $subPaymentMethodBaseNames = self::getSubPaymentMethodBaseNames($sName);
                if (!is_null($subPaymentMethodBaseNames)) {
                    if (count($subPaymentMethodBaseNames) > 0) {
                        $sName .= '\\' . $sName;
                    }
                }
                $aClassNames[] = 'Payone\Payment\Methods\\' . $sName;
            }
            $this->aPaymentMethodClasses = $aClassNames;
        }
        return $this->aPaymentMethodClasses;
    }

    /**
     * Returns array with payment method classes
     *
     * @return array
     */
    protected function getSubPaymentMethodClasses()
    {
        if ($this->aSubPaymentMethodClasses == null) {
            $aClassNames = array();
            $aPayments = self::getSubPaymentMethodBaseNames();

            foreach ($aPayments as $sMainPayment => $aSubPayments) {
                foreach ($aSubPayments as $sSubPaymentBaseName) {
                    $aClassNames[$sMainPayment][] = 'Payone\Payment\Methods\\' .
                                                    $sMainPayment . '\\' . $sSubPaymentBaseName;
                }
            }
            $this->aSubPaymentMethodClasses = $aClassNames;
        }
        return $this->aSubPaymentMethodClasses;
    }

    /**
     * Returns array with all payment methods
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        if ($this->aPaymentMethods === null) {
            $this->aPaymentMethods = array();
            foreach ($this->getPaymentMethodClasses() as $sClass) {
                if (class_exists($sClass)) {
                    $oPayment = new $sClass;
                    $this->aPaymentMethods[$oPayment->getId()] = $oPayment;
                }
            }
        }
        return $this->aPaymentMethods;
    }

    /**
     * Returns array with all payment methods
     *
     * @param string $sMainPayment main payment type CreditCard, OnlineTransfer
     * @return array
     */
    public function getSubPaymentMethods($sMainPayment)
    {
        if ($this->aSubPaymentMethods === null) {
            $this->aSubPaymentMethods = array();
            foreach ($this->getSubPaymentMethodClasses() as $sMainPaymentClass => $aSubPayments) {
                foreach ($aSubPayments as $sClass) {
                    if (class_exists($sClass)) {
                        $oPayment = new $sClass;
                        $this->aSubPaymentMethods[$sMainPaymentClass][$oPayment->getId()] = $oPayment;
                    }
                }
            }
        }
        if (isset($this->aSubPaymentMethods[$sMainPayment])) {
            return $this->aSubPaymentMethods[$sMainPayment];
        }
    }

    /**
     * Returns selected payment method
     *
     * @return object
     */
    public function getSelectedPaymentMethod()
    {
        $sSelectedId = \Tools::getValue('payone_payment');
        if (!$sSelectedId) {
            return;
        }

        $oSelectedPayment = $this->getSelectedMainPaymentMethod();
        if (!$oSelectedPayment) {
            return;
        }
        if ($oSelectedPayment->hasSubPayments()) {
            if (\Tools::getValue('payone_payment_sub')) {
                return $oSelectedPayment->getValidSubPayment(\Tools::getValue('payone_payment_sub'));
            } else {
                return $oSelectedPayment;
            }
        } elseif ($oSelectedPayment->isValidForCheckout()) {
            return $oSelectedPayment;
        }
    }

    /**
     * Returns selected main payment method
     *
     * @return object
     */
    public function getSelectedMainPaymentMethod()
    {
        $sSelectedId = \Tools::getValue('payone_payment');
        if (!$sSelectedId) {
            return;
        }

        $oSelectedPayment = null;
        $aPaymentMethods = $this->getPaymentMethods();
        foreach ($aPaymentMethods as $oPayment) {
            if ($oPayment && $oPayment->getId() == $sSelectedId) {
                $oSelectedPayment = $oPayment;
                break;
            }
        }
        return $oSelectedPayment;
    }

    /**
     * Returns parent payment method
     *
     * @param string $sParentId
     * @return object
     */
    public function getParentPaymentMethod($sParentId)
    {
        $aPaymentMethods = $this->getPaymentMethods();
        if (isset($aPaymentMethods) && isset($aPaymentMethods[$sParentId])) {
            return $aPaymentMethods[$sParentId];
        }
    }

    /**
     * Returns payment method
     *
     * @param string $sId
     * @param string $sParentId
     * @return mixed|null
     */
    public function getPaymentMethod($sId, $sParentId = null)
    {
        $oReturnPayment = null;
        if ($sParentId) {
            $aPaymentMethods = $this->getSubPaymentMethods($sParentId);
        } else {
            $aPaymentMethods = $this->getPaymentMethods();
        }
        if (!is_array($aPaymentMethods) || count($aPaymentMethods) == 0) {
            return $oReturnPayment;
        }

        foreach ($aPaymentMethods as $oPayment) {
            if ($oPayment->getId() == $sId) {
                $oReturnPayment = $oPayment;
            } elseif ($oPayment->hasSubPayments()) {
                $oReturnPayment = $this->getPaymentMethod($sId, (new \ReflectionClass($oPayment))->getShortName());
            }
            if ($oReturnPayment !== null) {
                break;
            }
        }
        return $oReturnPayment;
    }
}
