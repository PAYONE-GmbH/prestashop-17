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

namespace Payone\Response;

use Payone\Base\Registry;
use Payone\Validation\Response\Response as ResponseValidation;
use Payone\Base\User;

class Response
{

    /**
     * Request response
     *
     * @var array
     */
    protected $aResponse = array();

    /**
     * Sets request response
     *
     * @param array $aResponse
     */
    public function setResponse($aResponse)
    {
        $this->aResponse = $aResponse;
    }

    /**
     * Returns response
     *
     * @return array $aResponse
     */
    protected function getResponse()
    {
        return $this->aResponse;
    }

    /**
     * Process payment response
     *
     * @return mixed
     */
    public function processPayment()
    {
        $oValidation = new ResponseValidation();
        if (!$this->getResponse() || !$oValidation->validatePaymentResponse($this->getResponse())) {
            return false;
        }

        $aResponse = $this->getResponse();
        $sStatus = \Tools::strtolower($aResponse['status']);
        if ($sStatus == 'approved') {
            return $this->handleStateApproved();
        } elseif ($sStatus == 'accepted') {
            return 'accepted';
        } elseif ($sStatus == 'redirect') {
            $this->handleStateRedirect();
        }
    }

    /**
     * Handles redirect state
     *
     * @return boolean
     */
    protected function handleStateRedirect()
    {
        $this->createUser();
        $this->createTransaction();
        $aResponse = $this->getResponse();
        Registry::getLog()->log(
            'redirect to payment provider',
            1,
            array(null, 'Cart', \Context::getContext()->cart->id)
        );
        \Tools::redirect($aResponse['redirecturl']);
    }

    /**
     * Handles approved state
     *
     * @return boolean
     */
    protected function handleStateApproved()
    {
        $this->createUser();
        $this->createTransaction();
        return 'approved';
    }

    /**
     * Creates user from response
     */
    protected function createUser()
    {
        $aResponse = $this->getResponse();
        $oUser = new User;
        if (!$oUser->getCustomerIdByPayoneUserId($aResponse['userid'])) {
            $sCustomerId = \Context::getContext()->cart->id_customer;
            $oUser->setCustomerId($sCustomerId);
            $oUser->setPayoneUserId($aResponse['userid']);
            $oUser->save();
        }
    }

    /**
     * Creates transaction
     */
    protected function createTransaction()
    {
        $aResponse = $this->getResponse();
        if (isset($aResponse['txid'])) {
            \Context::getContext()->cookie->iFcPayoneTransactionId = $aResponse['txid'];
        }
    }

    /**
     * Process mandate create response
     *
     */
    public function processMandateManage()
    {
        if (!$this->getResponse()) {
            return false;
        }

        $oValidation = new ResponseValidation();
        $blValid = $oValidation->validateMandateManageResponse($this->getResponse());
        if ($blValid) {
            \Context::getContext()->cookie->sFcPayoneMandate = \Tools::jsonEncode($this->getResponse());
            return true;
        }
        return false;
    }

    public function isValidMandate()
    {
        if (!$this->getResponse()) {
            return false;
        }

        $oValidation = new ResponseValidation();
        $blValid = $oValidation->validateMandateApprovedAndActive($this->getResponse());
        if ($blValid) {
            return true;
        }
        return false;
    }

    /**
     * Process mandate getfile response
     *
     * @return boolean
     */
    public function processMandateGetFile()
    {
        if (!$this->getResponse()) {
            return false;
        }

        $oValidation = new ResponseValidation();
        $blValid = $oValidation->validateMandateGetFileResponse($this->getResponse());
        if ($blValid) {
            return $this->getResponse();
        }
        return false;
    }

    /**
     * Process paypal express responses
     *
     * @return bool
     */
    public function processPayPalExpress()
    {
        if (!$this->getResponse()) {
            return false;
        }
        $oValidation = new ResponseValidation();
        $blValid = $oValidation->validatePaymentResponse($this->getResponse());
        if ($blValid) {
            $aResponse = $this->getResponse();
            $sStatus = \Tools::strtolower($aResponse['status']);

            if ($sStatus == 'ok') {
                Registry::getLog()->log(
                    'start paypal express user creation',
                    1,
                    array(null, 'Cart', \Context::getContext()->cart->id)
                );
                return $this->handlePayPalExpressUser($aResponse);
            } elseif ($sStatus == 'redirect' && $aResponse['workorderid']) {
                Registry::getLog()->log('redirect to paypal', 1, array(null, 'Cart', \Context::getContext()->cart->id));
                \Context::getContext()->cookie->sFcPayoneWorkOrderId = $aResponse['workorderid'];
                \Tools::redirect($aResponse['redirecturl']);
            }
        }
        return false;
    }

    /**
     * Returns user for paypal response
     *
     * @param $aResponse
     * @return mixed
     */
    protected function getPayPalExpressUser($aResponse)
    {
        /* Checks if a customer already exists for this e-mail address */
        $sEmail = $aResponse['add_paydata[email]'];
        if (\Validate::isEmail($sEmail)) {
            $oCustomer = Registry::getHelperPrestashop()->fcPayoneGetCustomer();
            $oCustomer->getByEmail($sEmail);
        }

        /* If the customer does not exist yet, create a new one */
        if (!\Validate::isLoadedObject($oCustomer)) {
            $oCustomer = Registry::getHelperPrestashop()->fcPayoneGetCustomer();
            $oCustomer->email = $sEmail;
            $oCustomer->firstname = $aResponse['add_paydata[shipping_firstname]'];
            $oCustomer->lastname = $aResponse['add_paydata[shipping_lastname]'];
            $oCustomer->passwd = \Tools::encrypt(\Tools::passwdGen());
            $oCustomer->add();
        }
        return $oCustomer;
    }

    /**
     * Returns address for paypal response
     *
     * @param $aResponse
     * @param $oCustomer
     * @return mixed
     */
    protected function getPayPalExpressAddress($aResponse, $oCustomer)
    {
        $sAddressAlias = 'PAYONE PayPal Express';
        $aAddresses = $oCustomer->getAddresses((int)\Configuration::get('PS_LANG_DEFAULT'));
        if (is_array($aAddresses) && count($aAddresses) > 0) {
            foreach ($aAddresses as $aAddress) {
                if ($aAddress['alias'] == $sAddressAlias) {
                    $sAddressId = (int)$aAddress['id_address'];
                    break;
                }
            }
        }

        //create or update paypal address
        $iAddressId = (isset($sAddressId) ? (int)$sAddressId : 0);
        $oAddress = Registry::getHelperPrestashop()->fcPayoneGetAddress($iAddressId);
        $oAddress->id_customer = (int)$oCustomer->id;
        $oAddress->id_country = (int)\Country::getByIso($aResponse['add_paydata[shipping_country]']);
        $oAddress->id_state = (int)\State::getIdByIso(
            $aResponse['add_paydata[shipping_country]'],
            (int)$oAddress->id_country
        );
        $oAddress->alias = $sAddressAlias;
        $oAddress->lastname = $oCustomer->lastname;
        $oAddress->firstname = $oCustomer->firstname;
        $oAddress->address1 = $aResponse['add_paydata[shipping_street]'];

        if (isset($aResponse['add_paydata[shipping_addressaddition]'])) {
            $oAddress->address2 = $aResponse['add_paydata[shipping_addressaddition]'];
        }

        $oAddress->city = $aResponse['add_paydata[shipping_city]'];
        $oAddress->postcode = $aResponse['add_paydata[shipping_zip]'];
        $oAddress->save();
        return $oAddress;
    }

    /**
     * Retrieves customer and address for paypal response
     * logs in user and updates cart
     *
     * @param $aResponse
     * @return bool
     */
    protected function handlePayPalExpressUser($aResponse)
    {
        $oCustomer = $this->getPayPalExpressUser($aResponse);

        if (!\Validate::isLoadedObject($oCustomer)) {
            Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_ORDER_PAYMENT_PAYPAL_NO_USER_FOUND');
            return false;
        }

        $oAddress = $this->getPayPalExpressAddress($aResponse, $oCustomer);

        if (!\Validate::isLoadedObject($oAddress)) {
            Registry::getErrorHandler()->setError('order', 'FC_PAYONE_ERROR_ORDER_PAYMENT_PAYPAL_NO_ADDRESS_FOUND');
            return false;
        }

        $oContext = \Context::getContext();
        //update customer cookie and loging
        $oContext->cookie->id_customer = (int)$oCustomer->id;
        $oContext->cookie->customer_lastname = $oCustomer->lastname;
        $oContext->cookie->customer_firstname = $oCustomer->firstname;
        $oContext->cookie->passwd = $oCustomer->passwd;
        $oContext->cookie->email = $oCustomer->email;
        $oContext->cookie->is_guest = $oCustomer->isGuest();
        $oContext->cookie->logged = 1;

        //update cart
        $oContext->cart->id_address_delivery = (int)$oAddress->id;
        $oContext->cart->id_address_invoice = (int)$oAddress->id;
        $oContext->cart->secure_key = $oCustomer->secure_key;
        $oContext->cart->delivery_option = \Tools::jsonEncode($oContext->cart->getDeliveryOption());
        $oContext->cart->update();

        \Hook::exec('authentication');
        Registry::getLog()->log(
            'created paypal express user',
            1,
            array(null, 'Cart', \Context::getContext()->cart->id)
        );
        return true;
    }

    /**
     * Process capture response
     *
     * @return bool
     */
    public function processCapture()
    {
        if (!$this->getResponse()) {
            return false;
        }

        $oValidation = new ResponseValidation();
        $blValid = $oValidation->validateOrderActionResponse($this->getResponse());
        if ($blValid) {
            return true;
        }
        return false;
    }

    /**
     * Process debit response
     *
     * @return bool
     */
    public function processDebit()
    {
        if (!$this->getResponse()) {
            return false;
        }

        $oValidation = new ResponseValidation();
        $blValid = $oValidation->validateOrderActionResponse($this->getResponse());
        if ($blValid) {
            return true;
        }
        return false;
    }
}
