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
     * Get request for Movie, Tv and Person class
     * @param string $tmdbId input TMDb ID
     * @param string $movieType input movie type like movie, tv or person
     * @return \stdClass
     */
    public function doLookup($tmdbId, $movieType)
    {
        $url = $this->apiUrl . '/' . $movieType . '/' . $tmdbId;
        $url .= '?append_to_response=';
        if ($movieType === 'person') {
            $url .= 'combined_credits';
        } else {
            $url .= 'alternative_titles,';
            $url .= 'credits,';
            $url .= 'external_ids,';
            $url .= 'images,';
            $url .= 'keywords,';
            $url .= 'recommendations,';
            $url .= 'videos';
        }
        $url .= $this->apiKey;
        return $this->setCache($tmdbId, $url);
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
     * Get request for Movie class list popular()
     * @param string $mediaType media type like movie, tv or person
     * @param string $listName list name like popular, upcoming, topRated
     * @param int $maxPages max number of data pages to return
     * @return array
     */
    public function doListLookup($mediaType, $listName, $maxPages)
    {
        $page = 1;
        $results = array();
        $queryUrl = $this->apiUrl . '/' . $mediaType . '/' . $listName . '?';
        $queryUrl .= $this->apiKey;
        $queryUrl .= '&page=';
        $firstReturnData = $this->execRequest($queryUrl . $page);
        $totalPages = $firstReturnData->total_pages;
        if ($totalPages < $maxPages) {
            $maxPages = $totalPages;
        }
        while($page <= $maxPages) {
            $requestData = $this->execRequest($queryUrl . $page);
            $results = array_merge($results, $requestData->results);
            $page++;
            unset($requestData);
        }
        return $results;
    }

    /**
     * Get request for Movie, Tv and Person class
     * @param string $type trending type: movie, person, tv, all (default: all)
     * @param string $timeWindow time window: day, week (default: week)
     * @return \stdClass
     */
    public function doListTrendingLookup($type, $timeWindow)
    {
        $url = $this->apiUrl . '/trending/' . $type . '/' . $timeWindow . '?';
        $url .= $this->apiKey;
        return $this->execRequest($url);
    }

    /**
     * Get watch providers for Movie and Tv class
     * @param string $tmdbId input TMDb ID
     * @param string $mediaType input type like movie or tv
     * @return \stdClass
     */
    public function doWatchProviderLookup($tmdbId, $mediaType)
    {
        $url = $this->apiUrl . '/' . $mediaType . '/' . $tmdbId . '/watch/providers';
        $url .= '?' . $this->apiKey;
        return $this->setCache($tmdbId, $url, '_WatchProviders');
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
