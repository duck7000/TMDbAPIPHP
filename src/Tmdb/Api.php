<?php

#############################################################################
# TMDbAPIPHP                                    ed (github user: duck7000)  #
# written by ed (github user: duck7000)                                     #
# ------------------------------------------------------------------------- #
# This program is free software; you can redistribute and/or modify it      #
# under the terms of the GNU General Public License (see doc/LICENSE)       #
#############################################################################

namespace Tmdb;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Accessing cd album information through musicBrainz API
 * @author Ed (duck7000)
 */
class Api
{

    private $cache;
    private $logger;
    private $config;
    protected $apiUrl;
    protected $apiVersion;
    protected $apiKey;

    /**
     * API constructor.
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     * @param Config $config
     */
    public function __construct($cache, $logger, $config)
    {
        $this->cache = $cache;
        $this->logger = $logger;
        $this->config = $config;
        $this->apiUrl = $this->config->apiUrl;
        $this->apiVersion = $this->config->apiVersion;
        $this->apiKey = $this->config->apiKey;
    }

    /**
     * Search request for TitleSearch class for text
     * @param string $searchInputString input search string
     * @param string $searchType like movie, tv, person or multi
     * @return \stdClass
     */
    public function doTextSearch($searchInputString, $searchType)
    {
        $url = $this->apiUrl . '/' . $this->apiVersion .
            '/search/' . $searchType .
            '?query=' . $searchInputString .
            '&include_adult=true' .
            '&page=1' .
            '&api_key=' . $this->apiKey;
        return $this->execRequest($url);
    }

    /**
     * Search request for TitleSearch class for external id
     * @param string $externalId input external id number
     * @param string $externalSource input external id source
     * @return \stdClass
     */
    public function doExternalIdSearch($externalId, $externalSource)
    {
        $url = $this->apiUrl . '/' . $this->apiVersion .
            '/find/' . $externalId .
            '?external_source=' . $externalSource .
            '&api_key=' . $this->apiKey;
        return $this->execRequest($url);
    }

    /**
     * Get request for Movie class fetchMovieData()
     * @param string $tmdbMovieId input TMDb ID
     * @param array $inputMethods add additional data like videos, images etc
     * @return \stdClass
     */
    public function doMovieLookup($tmdbMovieId, $inputMethods)
    {
        $url = $this->apiUrl . '/' . $this->apiVersion . '/movie/' . $tmdbMovieId;
        if (!empty($inputMethods)) {
            $url .= '?append_to_response=' . implode(",", $inputMethods);
        } else {
            $url .= '?';
        }
        $url .= '&api_key=' . $this->apiKey;
        return $this->execRequest($url);
    }

    /**
     * Execute request
     * @param string $url
     * @return \stdClass
     */
    public function execRequest($url)
    {
        $request = new Request($url, $this->config);
        $request->sendRequest();
        if (200 == $request->getStatus() || 307 == $request->getStatus()) {
            return json_decode($request->getResponseBody());
        } elseif (404 == $request->getStatus()) {
            return false;
        } else {
            $this->logger->error(
                "[API] Failed to retrieve query. Response headers:{headers}. Response body:{body}",
                array('headers' => $request->getLastResponseHeaders(), 'body' => $request->getResponseBody())
            );
            if ($this->config->throwHttpExceptions) {
                throw new \Exception("Failed to retrieve query");
            } else {
                return new \StdClass();
            }
        }
    }

}
