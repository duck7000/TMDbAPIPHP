<?php

#############################################################################
# TMDbAPIPHP                                    ed (github user: duck7000)  #
# written by ed (github user: duck7000)                                     #
# ------------------------------------------------------------------------- #
# This program is free software; you can redistribute and/or modify it      #
# under the terms of the GNU General Public License (see doc/LICENSE)       #
#############################################################################

namespace Tmdb;

/**
 * The request class
 * Here we emulate a browser accessing the API.
 */
class Request
{
    private $ch;
    private $page;
    private $responseHeaders = array();
    private $config;

    /**
     * No need to call this.
     * @param string $url URL to open
     */
    public function __construct($url, Config $config)
    {
        $this->config = $config;
        $this->ch = curl_init($url);
        curl_setopt($this->ch, CURLOPT_ENCODING, "");
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array(&$this, "callbackCurloptHeaderFunction"));
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_FAILONERROR, true);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->config->curloptTimeout);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 20);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '. $this->config->apiToken,
            'Accept: application/json'
        ));
    }

    /**
     * Send a request to the website
     * @return bool
     */
    public function sendRequest()
    {
        $this->responseHeaders = array();
        $this->page = curl_exec($this->ch);
        if ($this->page !== false) {
            return true;
        }
        return false;
    }

    /**
     * Get the Response body
     * @return string
     */
    public function getResponseBody()
    {
        return $this->page;
    }

    /**
     * HTTP status code of the last response
     * @return int|null
     */
    public function getStatus()
    {
        $headers = $this->getLastResponseHeaders();
        if (empty($headers[0])) {
            return null;
        }
        if (!preg_match("#^HTTP/[\d\.]+ (\d+)#i", $headers[0], $matches)) {
            return null;
        }
        return (int)$matches[1];
    }

    /**
     * Get last response headers
     * @return array
     */
    public function getLastResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * Callback curl opt header function
     * @param string $ch curl_init
     * @param string $str
     * @return int
     */
    private function callbackCurloptHeaderFunction($ch, $str)
    {
        $len = strlen($str);
        if ($len) {
            $this->responseHeaders[] = $str;
        }
        return $len;
    }
    
    /**
     * Get the URL to redirect to if a 30* was returned
     * @return string
     */
    public function getRedirect()
    {
        $status = $this->getStatus();
        if ($status == 301 || $status == 302 || $status == 303 || $status == 307 || $status == 308) {
            foreach ($this->getLastResponseHeaders() as $header) {
                if (strpos(trim(strtolower($header)), 'location') !== 0) {
                    continue;
                }
                $aline = explode(': ', $header);
                $target = trim($aline[1]);
                $urlParts = parse_url($target);
                if (!isset($urlParts['host'])) {
                    $initialRequestUrlParts = parse_url($url);
                    $target = $initialRequestUrlParts['scheme'] . "://" . $initialRequestUrlParts['host'] . $target;
                }
                return $target;
            }
        }
    }

}
