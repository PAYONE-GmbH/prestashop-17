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

namespace Payone\Request;

use Payone\Base\Registry;

class Dispatcher
{
    /**
     * URL of PAYONE Server API
     *
     * @var string
     */

    protected $sApiUrl = 'https://api.pay1.de/post-gateway/';

    /**
     * URL of PAYONE Server API
     *
     * @var string
     */
    protected $sFrontendApiUrl = 'https://secure.pay1.de/frontend/';

    /**
     * use certification for request
     *
     * @var bool
     */
    protected $blUsePayoneCertification = true;

    /**
     * Request timeout
     *
     * @var int
     */
    protected $iTimeout = 45;

    /**
     * Returns true if cert should be used
     *
     * @return bool
     */
    protected function useCertification()
    {
        return $this->blUsePayoneCertification;
    }

    /**
     * Sets certification usage
     *
     * @param $blUseCert
     */
    public function setCertificationUsage($blUseCert)
    {
        $this->blUsePayoneCertification = $blUseCert;
    }

    /**
     * Check for certification and sets usage to false
     * if not found
     */
    protected function checkCertification()
    {
        $sCertificateFilePath = $this->getCertificationFile();
        if (file_exists($sCertificateFilePath) === false) {
            $this->setCertificationUsage(false);
        }
    }

    /**
     * Returns path to certification file
     *
     * @return string
     */
    protected function getCertificationFile()
    {
        return Registry::getHelper()->getModulePath() . 'cert/cacert.pem';
    }

    /**
     * Returns timeout
     *
     * @return int
     */
    protected function getTimeout()
    {
        return $this->iTimeout;
    }

    /**
     * Sets request timeout
     *
     * @param $iTimeout
     */
    public function setTimeout($iTimeout)
    {
        $this->iTimeout = (int)$iTimeout;
    }

    /**
     * Returns api url
     *
     * @return string
     */
    protected function getApiUrl()
    {
        return $this->sApiUrl;
    }

    /**
     * Returns frontend api url
     *
     * @return string
     */
    protected function getFrontendApiUrl()
    {
        return $this->sFrontendApiUrl;
    }

    /**
     * Sends request
     *
     * @param array $aRequest
     * @param boolean $blOnlyUrl return the url
     * @param boolean $blUseFileGetContents
     * @param string $sRequestBaseUrl url for non payone requests
     *
     * @return string|array
     */
    public function dispatchRequest(
        $aRequest,
        $blOnlyUrl = false,
        $blUseFileGetContents = false,
        $sRequestBaseUrl = null
    ) {
        $sRequestUrl = $this->getRequestUrl($aRequest, $sRequestBaseUrl);
        if ($blOnlyUrl) {
            return $sRequestUrl;
        }

        $aRequestUrl = parse_url($sRequestUrl);
        if ($blUseFileGetContents) {
            $aRequestResponse = $this->useFileGetContents($aRequest);
        } else {
            if (function_exists("curl_init")) {
                $aRequestResponse = $this->useDefaultCurl($aRequestUrl);
            } elseif (file_exists("/usr/local/bin/curl") || file_exists("/usr/bin/curl")) {
                $aRequestResponse = $this->useCustomCurl($aRequestUrl);
            } else {
                $aRequestResponse = $this->useSocket($aRequestUrl);
            }
        }

        return array(
            'request' => $aRequest,
            'requestUrl' => $sRequestUrl,
            'response' => $aRequestResponse
        );
    }

    /**
     * Returns request uri
     *
     * @param array $aRequest
     * @return string
     */
    protected function getRequestUri($aRequest)
    {
        $sRequestUri = '';
        if (is_array($aRequest) && count($aRequest) > 0) {
            foreach ($aRequest as $sKey => $sValue) {
                if (is_array($sValue)) {
                    foreach ($sValue as $i => $val1) {
                        $sRequestUri .= "&" . $sKey . "[" . $i . "]=" . urlencode($val1);
                    }
                } else {
                    $sRequestUri .= "&" . $sKey . "=" . urlencode($sValue);
                }
            }
        }
        return $sRequestUri;
    }

    /**
     * Returns request url
     *
     * @param array $aRequest
     * @param string $sRequestBaseUrl (optional) for non payone requests
     *
     * @return string
     */
    protected function getRequestUrl($aRequest, $sRequestBaseUrl = null)
    {
        if ($sRequestBaseUrl !== null) {
            $sUrl = rtrim($sRequestBaseUrl, '?') . '?' . $this->getRequestUri($aRequest);
        } else {
            $sUrl = $this->getApiUrl() . '?' . $this->getRequestUri($aRequest);
        }

        return $sUrl;
    }

    /**
     * Use default curl call
     *
     * @param array $aRequestUrl Description
     * @return array $aResponse
     */
    protected function useDefaultCurl($aRequestUrl)
    {
        $aResponse = array();
        $this->checkCertification();
        $oCurl = curl_init($aRequestUrl['scheme'] . "://" . $aRequestUrl['host'] . $aRequestUrl['path']);
        curl_setopt($oCurl, CURLOPT_POST, 1);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $aRequestUrl['query']);
        if ($this->useCertification()) {
            curl_setopt($oCurl, CURLOPT_CAINFO, $this->getCertificationFile());
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, true);  // force SSL certificate check
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 2);  // check hostname in SSL certificate
        } else {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, $this->getTimeout());

        $result = curl_exec($oCurl);
        if (curl_error($oCurl)) {
            $aResponse[] = "connection-type: 1 - errormessage=" . curl_errno($oCurl) . ": " . curl_error($oCurl);
        } else {
            $aResponse = explode("\n", $result);
        }
        curl_close($oCurl);
        return $aResponse;
    }

    /**
     * Use custom curl call
     *
     * @param array $aRequestUrl Description
     * @return array $aResponse
     */
    protected function useCustomCurl($aRequestUrl)
    {
        $aResponse = array();
        $sPostUrl = $aRequestUrl['scheme'] . "://" . $aRequestUrl['host'] . $aRequestUrl['path'];
        $sPostData = $aRequestUrl['query'];

        $sCurlPath = null;
        if (file_exists("/usr/local/bin/curl")) {
            $sCurlPath = "/usr/local/bin/curl";
        } elseif (file_exists("/usr/bin/curl")) {
            $sCurlPath = "/usr/bin/curl";
        } else {
            $aResponse[] = "errormessage=Path to curl could not be determined";
        }
        if ($sCurlPath) {
            $sEscapedTimeout = escapeshellarg($this->getTimeout());
            $sEscapedPostData = escapeshellarg($sPostData);
            $sEscapedPostUrl = escapeshellarg($sPostUrl);
            $sCommand = $sCurlPath . " -m {$sEscapedTimeout} -k -d {$sEscapedPostData} {$sEscapedPostUrl}";

            $aResponse = system($sCommand, $iSysOut);
            if ($iSysOut != 0) {
                $aResponse[] = "connection-type: 2 - errormessage=curl error(" . $iSysOut . ")";
            }
        }
        return $aResponse;
    }

    /**
     * Use custom socket call
     *
     * @param array $aRequestUrl Description
     * @return array $aResponse
     */
    protected function useSocket($aRequestUrl)
    {

        $iErrorNumber = '';
        $sErrorString = '';
        $aResponse = array();
        switch ($aRequestUrl['scheme']) {
            case 'https':
                $sScheme = 'ssl://';
                $iPort = 443;
                break;
            case 'http':
            default:
                $sScheme = '';
                $iPort = 80;
        }

        $oFsockOpen = fsockopen(
            $sScheme . $aRequestUrl['host'],
            $iPort,
            $iErrorNumber,
            $sErrorString,
            $this->getTimeout()
        );
        if (!$oFsockOpen) {
            $aResponse[] = "errormessage=fsockopen:Failed opening http socket connection: " .
                $sErrorString . " (" . $iErrorNumber . ")";
        } else {
            $sRequestHeader = "POST " . $aRequestUrl['path'] . " HTTP/1.1\r\n";
            $sRequestHeader .= "Host: " . $aRequestUrl['host'] . "\r\n";
            $sRequestHeader .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $sRequestHeader .= "Content-Length: " . \Tools::strlen($aRequestUrl['query']) . "\r\n";
            $sRequestHeader .= "Connection: close\r\n\r\n";
            $sRequestHeader .= $aRequestUrl['query'];

            fwrite($oFsockOpen, $sRequestHeader);

            $sResponseHeader = "";
            do {
                $sResponseHeader .= fread($oFsockOpen, 1);
            } while (!preg_match("/\\r\\n\\r\\n$/", $sResponseHeader) && !feof($oFsockOpen));

            while (!feof($oFsockOpen)) {
                $aResponse[] = fgets($oFsockOpen, 1024);
            }
            if (count($aResponse) == 0) {
                $aResponse[] = 'connection-type: 3 - ' . $sResponseHeader;
            }
        }
        return $aResponse;
    }

    /**
     * Creates stream context and send request via gile_get_contents
     *
     * @param array $aRequest
     * @return array|string
     */
    protected function useFileGetContents($aRequest)
    {
        $aOptions = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded",
                'method' => 'POST',
                'content' => http_build_query($aRequest),
            ),
        );
        $oContext = stream_context_create($aOptions);
        $mContent = \Tools::file_get_contents($this->getApiUrl(), false, $oContext);
        $aResponse = null;
        if ($mContent) {
            if (strpos($mContent, 'status') !== false) {
                $aResponse = explode("\n", $mContent);
            } else {
                $aResponse = $mContent;
            }
        }
        return $aResponse;
    }
}
