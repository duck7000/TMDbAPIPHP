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
 * Accessing movie, tv and person through TMDb API
 * @author Ed (duck7000)
 */
class Api
{

    private $cache;
    private $logger;
    private $config;
    protected $apiUrl;
    protected $apiKey;
    protected $apendOptions = array(
        'alternative_titles',
        'credits',
        'external_ids',
        'images',
        'keywords',
        'recommendations',
        'videos'
    );

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
        $this->apiUrl = $this->config->apiUrl . '/' . $this->config->apiVersion;
        $this->apiKey = '&api_key=' . $this->config->apiKey;
    }

    /**
     * Search request for TitleSearch class for text
     * @param string $searchInputString input search string
     * @param string $searchType like movie, tv, person or multi
     * @return \stdClass
     */
    public function doTextSearch($searchInputString, $searchType)
    {
        $url = $this->apiUrl . '/search/' . $searchType .
            '?query=' . $searchInputString .
            '&include_adult=true' .
            '&page=1' .
            $this->apiKey;
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
        $url = $this->apiUrl . '/find/' . $externalId .
            '?external_source=' . $externalSource .
            $this->apiKey;
        return $this->execRequest($url);
    }

    /**
     * Get request for Movie class fetchMovieData()
     * @param string $tmdbMovieId input TMDb ID
     * @return \stdClass
     */
    public function doMovieLookup($tmdbMovieId)
    {
        $url = $this->apiUrl . '/movie/' . $tmdbMovieId;
        $url .= '?append_to_response=';
        foreach ($this->apendOptions as $key => $value) {
            $url .= $value;
            if ($key !== array_key_last($this->apendOptions)) {
                $url .= ',';
            }
        }
        $url .= $this->apiKey;
        return $this->setCache($tmdbMovieId, $url);
    }

    /**
     * Get request for Person class fetchPersonData()
     * @param string $tmdbPersonId input TMDb ID
     * @return \stdClass
     */
    public function doPersonLookup($tmdbPersonId)
    {
        $url = $this->apiUrl . '/person/' . $tmdbPersonId;
        $url .= '?append_to_response=combined_credits';
        $url .= $this->apiKey;
        return $this->setCache($tmdbPersonId, $url);
    }

    /**
     * Get request for Tv class fetchTvData()
     * @param string $tmdbTvId input TMDb ID
     * @return \stdClass
     */
    public function doTvLookup($tmdbTvId)
    {
        $url = $this->apiUrl . '/tv/' . $tmdbTvId;
        $url .= '?append_to_response=';
        foreach ($this->apendOptions as $key => $value) {
            $url .= $value;
            if ($key !== array_key_last($this->apendOptions)) {
                $url .= ',';
            }
        }
        $url .= $this->apiKey;
        return $this->setCache($tmdbTvId, $url);
    }

    /**
     * Get request for Tv seasons and episodes for fetchTvData()
     * @param string $tmdbTvId input TMDb ID
     * @param int $totalSeasons total number of seasons off this tv series
     * @return \stdClass
     */
    public function doTvSeasonsLookup($tmdbTvId, $totalSeasons)
    {
        $appendUrl = $this->apiUrl . '/tv/' . $tmdbTvId;
        $appendUrl .= '?append_to_response=';
        $season = 1;
        while($season <= $totalSeasons) {
            $appendUrl .= 'season/' . $season;
            if ($season < $totalSeasons) {
                $appendUrl .= ',';
            }
            $season++;
        }
        $appendUrl .= $this->apiKey;
        return $this->setCache($tmdbTvId, $appendUrl, '_Seasons');
    }

    /**
     * Get watch providers for fetchMovieData()
     * @param string $tmdbMovieId input TMDb ID
     * @return \stdClass
     */
    public function doMovieWatchProviderLookup($tmdbMovieId)
    {
        $url = $this->apiUrl . '/movie/' . $tmdbMovieId . '/watch/providers';
        $url .= '?' . $this->apiKey;
        return $this->setCache($tmdbMovieId, $url, '_WatchProviders');
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
     * @param string $ext cache filename extension
     * @return \stdClass
     */
    public function setCache($id, $url, $ext = '')
    {
        $key = $id . $ext . '.json';
        $fromCache = $this->cache->get($key);

        if ($fromCache != null) {
            return json_decode($fromCache);
        }
        $data = $this->execRequest($url);
        $this->cache->set($key, json_encode($data));
        return $data;
    }

}
