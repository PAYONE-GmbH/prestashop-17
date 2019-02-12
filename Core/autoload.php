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

/**
 * Its possible to create validaiton, request and form classes for specific substyps
 * like visa...
 * replace foreach with
 * if (stripos($sPaymentMethod, 'OnlineTransfer') !== false) {
 * $aFcPayoneAutoloadCoreClasses[] = 'Validation/Payment/OnlineTransfer/' . $sPaymentMethod . '.php';
 * } elseif (stripos($sPaymentMethod, 'CreditCard') !== false) {
 * $aFcPayoneAutoloadCoreClasses[] = 'Validation/Payment/CreditCard/' . $sPaymentMethod . '.php';
 * } else {
 * $aFcPayoneAutoloadCoreClasses[] = 'Validation/Payment/' . $sPaymentMethod . '.php';
 * }
 *
 * and create subfolders and subpamyne classes that extends the main classes
 */
/**
 * gaether all payment and subpayment class names
 */
$aFcPayonePayments = array();
$aFcPayoneSubPaymentBaseNames = array();
$sFcPayoneAutoloadBaseDir = _PS_MODULE_DIR_ . 'fcpayone/Core/';
if (file_exists($sFcPayoneAutoloadBaseDir . 'Payment/Payment.php')) {
    require_once $sFcPayoneAutoloadBaseDir . 'Payment/Payment.php';
    if (class_exists('Payone\Payment\Payment')) {
        require_once $sFcPayoneAutoloadBaseDir . 'Payment/Methods/Base.php';
        $aFcPayonePayments = Payone\Payment\Payment::getPaymentMethodBaseNames();
        $aFcPayoneSubPaymentBaseNames = Payone\Payment\Payment::getSubPaymentMethodBaseNames();
        foreach ($aFcPayoneSubPaymentBaseNames as $sKey => $aSubPayments) {
            foreach ($aSubPayments as $sSubPayment) {
                $aFcPayonePayments[] = $sSubPayment;
            }
        }
    }
}
if (count($aFcPayonePayments) > 0) {
    /**
     * add base classes
     */
    $aFcPayoneAutoloadCoreClasses = array();
    $aFcPayoneAutoloadCoreClasses[] = 'Translation/Translator.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Base/Registry.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Base/Log.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Helper/Helper.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Base/Reference.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Helper/HelperPrestashop.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Base/Transaction.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Base/User.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Base/Order.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Base/Mandate.php';
    //after logging so we can log errors
    $aFcPayoneAutoloadCoreClasses[] = 'Base/ErrorHandler.php';

    /**
     * add backend forms
     */
    $aFcPayoneAutoloadCoreClasses[] = 'Forms/Backend/Base.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Forms/Backend/General/Connection.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Forms/Backend/General/TransactionForwarding.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Forms/Backend/General/TransactionStateMapping.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Forms/Backend/General/Misc.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Forms/Backend/Payment/Base.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Forms/Backend/Order.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Forms/Backend/Payment/CreditCardGeneral.php';
    foreach ($aFcPayonePayments as $sPaymentMethod) {
        $aFcPayoneAutoloadCoreClasses[] = 'Forms/Backend/Payment/' . $sPaymentMethod . '.php';
    }
    $aFcPayoneAutoloadCoreClasses[] = 'Forms/Backend/Backend.php';

    /**
     * add frontend forms
     */
    $aFcPayoneAutoloadCoreClasses[] = 'Forms/Frontend/Base.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Forms/Frontend/Payment/Base.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Forms/Frontend/Payment/Default.php';
    foreach ($aFcPayonePayments as $sPaymentMethod) {
        $aFcPayoneAutoloadCoreClasses[] = 'Forms/Frontend/Payment/' . $sPaymentMethod . '.php';
    }
    $aFcPayoneAutoloadCoreClasses[] = 'Forms/Frontend/Frontend.php';

    /**
     * add payment mehtods
     */
    $aFcPayoneAutoloadCoreClasses[] = 'Payment/Methods/Base.php';
    foreach ($aFcPayonePayments as $sPaymentMethod) {
        if (in_array($sPaymentMethod, $aFcPayoneSubPaymentBaseNames['OnlineTransfer']) ||
            $sPaymentMethod == 'OnlineTransfer'
        ) {
            $aFcPayoneAutoloadCoreClasses[] = 'Payment/Methods/OnlineTransfer/' . $sPaymentMethod . '.php';
        } elseif (in_array($sPaymentMethod, $aFcPayoneSubPaymentBaseNames['CreditCard']) ||
            $sPaymentMethod == 'CreditCard'
        ) {
            $aFcPayoneAutoloadCoreClasses[] = 'Payment/Methods/CreditCard/' . $sPaymentMethod . '.php';
        } elseif (in_array($sPaymentMethod, $aFcPayoneSubPaymentBaseNames['Wallet']) ||
            $sPaymentMethod == 'Wallet'
        ) {
            $aFcPayoneAutoloadCoreClasses[] = 'Payment/Methods/Wallet/' . $sPaymentMethod . '.php';
        } else {
            $aFcPayoneAutoloadCoreClasses[] = 'Payment/Methods/' . $sPaymentMethod . '.php';
        }
    }

    /**
     * add validation
     */
    $aFcPayoneAutoloadCoreClasses[] = 'Validation/Base.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Validation/Payment/Base.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Validation/Payment/Default.php';
    foreach ($aFcPayonePayments as $sPaymentMethod) {
        $aFcPayoneAutoloadCoreClasses[] = 'Validation/Payment/' . $sPaymentMethod . '.php';
    }
    $aFcPayoneAutoloadCoreClasses[] = 'Validation/Validation.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Validation/Request/Request.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Validation/Response/Response.php';

    /**
     * add request classes
     */
    $aFcPayoneAutoloadCoreClasses[] = 'Request/Request.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Request/Dispatcher.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Request/Builder/Base.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Request/Builder/Auth.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Request/Builder/Items.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Request/Builder/User.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Request/Builder/Mandate.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Request/Builder/Order/Base.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Request/Builder/Order/Capture.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Request/Builder/Order/Debit.php';
    $aFcPayoneAutoloadCoreClasses[] = 'Request/Builder/Payment/Base.php';
    foreach ($aFcPayonePayments as $sPaymentMethod) {
        $aFcPayoneAutoloadCoreClasses[] = 'Request/Builder/Payment/' . $sPaymentMethod . '.php';
    }

    /**
     * add response classes
     */
    $aFcPayoneAutoloadCoreClasses[] = 'Response/Response.php';

    /**
     * require files
     */
    foreach ($aFcPayoneAutoloadCoreClasses as $sFile) {
        $sPath = $sFcPayoneAutoloadBaseDir . $sFile;
        if (file_exists($sPath)) {
            require_once $sPath;
        }
    }
}
