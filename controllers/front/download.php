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

use \Payone\Base\Registry;

class FcPayoneDownloadModuleFrontController extends ModuleFrontController
{

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $iOrderId = Tools::getValue('payone_orderid');
        $sIdent = Tools::getValue('payone_ident');
        $iCustomerId = Tools::getValue('payone_customer');
        $oValidation = new \Payone\Validation\Validation();
        if ($iOrderId && $sIdent && $iCustomerId &&
            $oValidation->validateMandateDownload($iOrderId, $iCustomerId, $sIdent)
        ) {
            $oMandate = new \Payone\Base\Mandate();
            if ($oMandate->getMandateFile($iOrderId)) {
                $oMandate->outputMandateFile();
            }
        } else {
            Registry::getLog()->log('mandate download check failed', 2, array(null, 'Mandate Download'));
        }
        exit;
    }
}
