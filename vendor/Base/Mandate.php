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

class Mandate
{

    /**
     * Mandate status
     * pending...
     * @var string
     */
    protected $sMandateStatus = null;

    /**
     * Mandate id
     *
     * @var string
     */
    protected $sMandateIdent = null;

    /**
     * Mandate text to display
     *
     * @var string
     */
    protected $sMandateText = null;

    /**
     * Creditor id
     *
     * @var string
     */
    protected $sCreditorIdent = null;

    /**
     * Iban
     *
     * @var string
     */
    protected $sIban = null;

    /**
     * Bic
     * @var type
     */
    protected $sBic = null;

    /**
     * If mandate is loaded
     *  no cookie value
     *
     * @var boolean
     */
    protected $blLoaded = false;

    /**
     * Order ID
     *
     * @var int
     */
    protected $iOrderId = null;

    /**
     * File content
     *
     * @var string
     */
    protected $sFileContent = null;

    /**
     * Mandate table
     *
     * @var string
     */
    protected static $sTable = 'fcpayonemandates';

    /**
     * Sets mandate status
     *
     * @param string $sStatus
     */
    public function setMandateStatus($sStatus)
    {
        $this->sMandateStatus = $sStatus;
    }

    /**
     * Returns mandate status
     *
     * @return string
     */
    public function getMandateStatus()
    {
        return $this->sMandateStatus;
    }

    /**
     * Sets mandate ident
     *
     * @param string $sId
     */
    public function setMandateIdent($sId)
    {
        $this->sMandateIdent = $sId;
    }

    /**
     * Returns mandate ident
     *
     * @return string
     */
    public function getMandateIdent()
    {
        return $this->sMandateIdent;
    }

    /**
     * Sets mandate text
     *
     * @param string $sText
     */
    public function setMandateText($sText)
    {
        $this->sMandateText = $sText;
    }

    /**
     * Returns mandate text
     *
     * @return string
     */
    public function getMandateText()
    {
        return $this->sMandateText;
    }

    /**
     * Sets creditor ident
     *
     * @param string $sId
     */
    public function setCreditorIdent($sId)
    {
        $this->sCreditorIdent = $sId;
    }

    /**
     * Returns creditor ident
     *
     * @return string
     */
    public function getCreditorIdent()
    {
        return $this->sCreditorIdent;
    }

    /**
     * Set Iban
     *
     * @param string $sIban
     */
    public function setIban($sIban)
    {
        $this->sIban = $sIban;
    }

    /**
     * Returns iban
     *
     * @return string
     */
    public function getIban()
    {
        return $this->sIban;
    }

    /**
     * Set bic
     *
     * @param string $sBic
     */
    public function setBic($sBic)
    {
        $this->sBic = $sBic;
    }

    /**
     * Returns bic
     *
     * @return string
     */
    public function getBic()
    {
        return $this->sBic;
    }

    /**
     * Sets order id
     *
     * @param int $iOrderId
     */
    public function setOrderId($iOrderId)
    {
        $this->iOrderId = $iOrderId;
    }

    /**
     * Returns order id
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->iOrderId;
    }

    /**
     * Sets file content
     *
     * @param string $sFileContent
     */
    public function setFileContent($sFileContent)
    {
        $this->sFileContent = $sFileContent;
    }

    /**
     * Returns file content
     *
     * @return string
     */
    public function getFileContent()
    {
        return $this->sFileContent;
    }

    /**
     * Request table
     *
     * @return string
     */
    public static function getTable()
    {
        return self::$sTable;
    }

    /**
     * Sets mandate properies from givin or current context
     *
     * @param object $oContext
     *
     * @return boolean
     */
    public function setMandateFromContext($oContext = null)
    {
        if (!$oContext) {
            $oContext = \Context::getContext();
        }
        if (isset($oContext->cookie->sFcPayoneMandate) &&
            ($aMandate = \Tools::jsonDecode($oContext->cookie->sFcPayoneMandate, true))
        ) {
            $this->setMandateIdent($aMandate['mandate_identification']);
            $this->setMandateStatus($aMandate['mandate_status']);
            $this->setMandateText(urldecode($aMandate['mandate_text']));
            $this->setCreditorIdent($aMandate['creditor_identifier']);
            $this->setIban($aMandate['iban']);
            if (isset($aMandate['bic'])) {
                $this->setBic($aMandate['bic']);
            }
            return true;
        }
    }

    /**
     * Returns true if mandate file is available
     * if not trys to download it
     *
     * @param int $iOrderId
     * @return boolean
     */
    public function getMandateFile($iOrderId)
    {
        if ($iOrderId) {
            $sTable = _DB_PREFIX_ . self::getTable();
            $iCleanOrderId = (int)\pSQL($iOrderId);
            $sQ = "select mandate_identifier, file_content from {$sTable} where id_order = '{$iCleanOrderId}'";
            $aMandateData = \Db::getInstance()->getRow($sQ);
            $sFileContent = $aMandateData['file_content'];
            if (!$sFileContent) {
                $sFileContent = $this->downloadMandatePdf($iOrderId);
            } else {
                $this->setFileContent($sFileContent);
                $this->setMandateIdent($aMandateData['mandate_identifier']);
            }
            if ($sFileContent) {
                return true;
            }
        }
    }

    /**
     * Downloads mandate pdf
     * and updates mandate in db
     *
     * @param $iOrderId
     * @return bool
     */
    protected function downloadMandatePdf($iOrderId)
    {
        $oOrder = new \Payone\Base\Order;
        $aOrderData = $oOrder->getOrderData($iOrderId);
        if (count($aOrderData) > 0) {
            $oRequest = new \Payone\Request\Request();
            $oRequest->setAdditionalSaveData('reference', $aOrderData['reference']);
            $sTable = _DB_PREFIX_ . self::getTable();
            $iCleanOrderId = (int)\pSQL($iOrderId);
            $sQ = "select mandate_identifier from {$sTable} where id_order = '{$iCleanOrderId}'";
            $sFileReference = \Db::getInstance()->getValue($sQ);
            $blProcessed = $oRequest->processMandateGetFile($sFileReference, $aOrderData['mode']);
            if (!$blProcessed) {
                Registry::getErrorHandler()->setError(
                    'order',
                    'FC_PAYONE_ERROR_MANDATE_FILE_REQUEST_FAILED',
                    true
                );
                return false;
            }
            $oResponse = new \Payone\Response\Response();
            $oResponse->setResponse($oRequest->getResponse(true));
            if (($sFileContent = $oResponse->processMandateGetFile())) {
                $sEncodedContent = base64_encode($sFileContent);
                $sQ = "update $sTable set file_content = '{$sEncodedContent}' where id_order = '{$iCleanOrderId}'";
                \Db::getInstance()->Execute($sQ);
                $this->setMandateIdent($sFileReference);
                $this->setFileContent($sEncodedContent);
                return $sFileContent;
            }
        }
    }

    /**
     * saves mandate to db
     *
     * @return boolean
     */
    public function save()
    {
        $aData = array();
        $aData['id_order'] = \pSQL($this->getOrderId());
        $aData['mandate_identifier'] = \pSQL($this->getMandateIdent());
        $aData['file_content'] = base64_encode($this->getFileContent());
        $aData['date'] = date('Y-m-d H:i:s');
        return (bool)\Db::getInstance()->insert(self::getTable(), $aData);
    }

    /**
     * Outputs mandate pdf to download
     */
    public function outputMandateFile()
    {
        header("Content-type:application/pdf");
        header("Content-Disposition:attachment;filename=" . $this->getMandateIdent() . '.pdf');
        echo base64_decode($this->getFileContent());
    }
}
