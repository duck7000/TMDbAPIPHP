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
    }

    /**
     * Search request for TitleSearch class for text
     * @param string $searchInputString input search string
     * @param string $searchType like movie, tv, person or multi
     * @return \stdClass
     */
    public function doTextSearch($searchInputString, $searchType)
    {
        $url = $this->apiUrl;
        $url .= '/search/';
        $url .= $searchType;
        $url .= '?';
        $url .= 'query=';
        $url .= $searchInputString;
        $url .= '&';
        $url .= 'include_adult=true';
        $url .= '&';
        $url .= 'page=1';
        return $this->execRequest($url);
    }

    /**
     * Search request for TitleSearch class for external id
     * @param string $externalId input external id number
     * @param string $externalSource input external id (imdb only)
     * @return \stdClass
     */
    public function doExternalIdSearch($externalId, $externalSource)
    {
        $url = $this->apiUrl;
        $url .= '/find/';
        $url .= $externalId;
        $url .= '?';
        $url .= 'external_source=';
        $url .= $externalSource;
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
        $url = $this->apiUrl;
        $url .= '/';
        $url .= $movieType;
        $url .= '/';
        $url .= $tmdbId;
        $url .= '?';
        $url .= 'append_to_response=';
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
        return $this->setCache($tmdbId, $url);
    }

    /**
     * Get request for Tv seasons and episodes for fetchTvData()
     * @param string $tmdbTvId input TMDb ID
     * @return \stdClass
     */
    public function doTvSeasonsLookup($tmdbTvId, $totalSeasons)
    {
        $appendUrl = $this->apiUrl;
        $appendUrl .= '/tv/';
        $appendUrl .= $tmdbTvId;
        $appendUrl .= '?';
        $appendUrl .= 'append_to_response=';
        $season = 1;
        while($season <= $totalSeasons) {
            $appendUrl .= 'season/' . $season;
            if ($season < $totalSeasons) {
                $appendUrl .= ',';
            }
            $season++;
        }
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
        $queryUrl = $this->apiUrl;
        $queryUrl .= '/';
        $queryUrl .= $mediaType;
        $queryUrl .= '/';
        $queryUrl .= $listName;
        $queryUrl .= '?';
        $queryUrl .= 'page=';
        $firstReturnData = $this->execRequest($queryUrl . $page);
        $totalPages = isset($firstReturnData->total_pages) ? $firstReturnData->total_pages : 1;
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
        $url = $this->apiUrl;
        $url .= '/trending/';
        $url .= $type;
        $url .= '/';
        $url .= $timeWindow;
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
        $url = $this->apiUrl;
        $url .= '/';
        $url .= $mediaType;
        $url .= '/';
        $url .= $tmdbId;
        $url .= '/watch/providers';
        return $this->setCache($tmdbId, $url, '_WatchProviders');
    }

    /**
     * Get request for Collection, keyword and Company class
     * @param int $tmdbId input TMDb ID
     * @param string $type collection, keyword or company
     * @return \stdClass
     */
    public function doTypeLookup($tmdbId, $type)
    {
        $url = $this->apiUrl;
        $url .= '/';
        $url .= $type;
        $url .= '/';
        $url .= $tmdbId;
        return $this->execRequest($url);
    }

    /**
     * Get request for UserAccount class
     * @param string|int $accountId input account Id
     * @param string $listType account lookup list type: details, favorite, rated or watchlist
     * @param string $mediaType account lookup media type: movies or tv
     * @return \stdClass
     */
    public function doUserAccountListLookup($accountId, $listType, $mediaType = "movies")
    {
        $page = 1;
        $Listresults = array();
        $url = $this->apiUrl;
        $url .= '/account/';
        $url .= $accountId;
        if ($listType == 'details') {
            return $this->execRequest($url);
        } else {
            $url .= '/';
            $url .= $listType;
            $url .= '/';
            $url .= $mediaType;
            $url .= '?';
            $url .= 'page=';
            $firstData = $this->execRequest($url . $page);
            $totalPages = isset($firstData->total_pages) ? $firstData->total_pages : 1;
            while($page <= $totalPages) {
                $requestData = $this->execRequest($url . $page);
                $Listresults = array_merge($Listresults, $requestData->results);
                $page++;
                unset($requestData);
            }
            return $Listresults;
        }
    }

    /**
     * Get request for Changes class
     * @param string $mediaType account lookup media type: movie, tv, person
     * @return \stdClass
     */
    public function doChangesLookup($mediaType = "movie")
    {
        $page = 1;
        $ListChangesresults = array();
        $url = $this->apiUrl;
        $url .= '/';
        $url .= $mediaType;
        $url .= '/';
        $url .= 'changes';
        $url .= '?';
        $url .= 'page=';
        $firstData = $this->execRequest($url . $page);
        $totalPages = isset($firstData->total_pages) ? $firstData->total_pages : 1;
        while($page <= $totalPages) {
            $requestData = $this->execRequest($url . $page);
            $ListChangesresults = array_merge($ListChangesresults, $requestData->results);
            $page++;
            unset($requestData);
        }
        return $ListChangesresults;
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
