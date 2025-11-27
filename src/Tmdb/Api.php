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
     * @return \stdClass
     */
    public function doMovieLookup($tmdbMovieId)
    {
        $url = $this->apiUrl . '/' . $this->apiVersion . '/movie/' . $tmdbMovieId;
        $url .= '?append_to_response=alternative_titles,credits,images,keywords,recommendations,videos';
        $url .= '&api_key=' . $this->apiKey;
        return $this->setCache($tmdbMovieId, $url);
    }

    /**
     * Get request for Person class fetchPersonData()
     * @param string $tmdbPersonId input TMDb ID
     * @return \stdClass
     */
    public function doPersonLookup($tmdbPersonId)
    {
        $url = $this->apiUrl . '/' . $this->apiVersion . '/person/' . $tmdbPersonId;
        $url .= '?append_to_response=combined_credits';
        $url .= '&api_key=' . $this->apiKey;
        return $this->setCache($tmdbPersonId, $url);
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

    /**
     * Caching return data
     * @param string $id TMDb id from doMovieLookup(), doTvLookup() and doPersonLookup()
     * @param string $url exec url from doMovieLookup(), doTvLookup() and doPersonLookup()
     * @return \stdClass
     */
    public function setCache($id, $url)
    {
        $key = $id . '.json';
        $fromCache = $this->cache->get($key);

        if ($fromCache != null) {
            return json_decode($fromCache);
        }
        $data = $this->execRequest($url);
        $this->cache->set($key, json_encode($data));
        return $data;
    }

}
