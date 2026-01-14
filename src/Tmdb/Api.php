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
     * @param bool $includeAdult include adult results or not
     * @return \stdClass
     */
    public function doTextSearch($searchInputString, $searchType, $includeAdult)
    {
        $url = $this->apiUrl;
        $url .= '/search/';
        $url .= $searchType;
        $url .= '?';
        $url .= 'query=';
        $url .= $searchInputString;
        $url .= '&';
        $url .= 'include_adult=';
        $url .= $includeAdult ? 'true' : 'false';
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
            $url .= 'combined_credits,';
            $url .= 'external_ids';
        } else {
            $url .= 'alternative_titles,';
            $url .= 'credits,';
            $url .= 'external_ids,';
            $url .= 'images,';
            $url .= 'keywords,';
            $url .= 'recommendations,';
            $url .= 'videos,';
            $url .= 'content_ratings,';
            $url .= 'release_dates,';
            $url .= 'reviews';
        }
        return $this->setCache($tmdbId, $url);
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
        return $this->setCachePaging('', $queryUrl, 'ListClasses_' . $listName . '_' . $mediaType, $maxPages);
    }

    /**
     * Get request for Trending class
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
        return $this->setCache('', $url, 'Trending_' . $type . '_'. $timeWindow);
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
     * Get request for Collection and Company class
     * @param int $tmdbId input TMDb ID
     * @param string $type collection or company
     * @return \stdClass
     */
    public function doTypeLookup($tmdbId, $type)
    {
        $url = $this->apiUrl;
        $url .= '/';
        $url .= $type;
        $url .= '/';
        $url .= $tmdbId;
        return $this->setCache($tmdbId, $url, '_' . $type);
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
        $url = $this->apiUrl;
        $url .= '/account/';
        $url .= $accountId;
        if ($listType == 'details') {
            return $this->setCache($accountId, $url, '_' . $listType);
        } else {
            $url .= '/';
            $url .= $listType;
            if ($listType !== 'lists') {
                $url .= '/';
                $url .= $mediaType;
            }
            $url .= '?';
            $url .= 'page=';
            $firstData = $this->execRequest($url . $page);
            $totalPages = isset($firstData->total_pages) ? $firstData->total_pages : 1;
            return $this->setCachePaging($accountId, $url, '_' . $listType . '_' . $mediaType, $totalPages);
        }
    }

    /**
     * Get request for Changes class
     * @param string $mediaType account lookup media type: movie, tv, person
     * @param int $days number of days to return, max 14 days
     * @return \stdClass
     */
    public function doChangesLookup($mediaType = "movie", $days = 1)
    {
        $page = 1;
        $url = $this->apiUrl;
        $url .= '/';
        $url .= $mediaType;
        $url .= '/';
        $url .= 'changes';
        $url .= '?';
        if ($days > 1 && $days < 15) {
            $url .= 'end_date=' . date("Y-m-d");
            $url .= '&';
            $url .= 'start_date=' . date('Y-m-d', strtotime('-' . $days . ' day', strtotime(date("Y-m-d"))));
        }
        $url .= 'page=';
        $firstData = $this->execRequest($url . $page);
        $totalPages = isset($firstData->total_pages) ? $firstData->total_pages : 1;
        return $this->setCachePaging('', $url, 'Changes_'. $mediaType, $totalPages);
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

    /**
     * Caching return data when paging is needed
     * @param string $id TMDb id (if available) from doChangesLookup(), doUserAccountListLookup() and doListLookup()
     * @param string $url exec url from doChangesLookup(), doUserAccountListLookup() and doListLookup()
     * @param string $ext cache filename extension
     * @param int $ext total pages to fetch
     * @return \stdClass
     */
    public function setCachePaging($id, $url, $ext, $totalPages)
    {
        $page = 1;
        $results = array();
        $key = $id . $ext . '.json';
        $fromCache = $this->cache->get($key);

        if ($fromCache != null) {
            return json_decode($fromCache);
        }
        while($page <= $totalPages) {
            $requestData = $this->execRequest($url . $page);
            $results = array_merge($results, $requestData->results);
            $page++;
            unset($requestData);
        }
        $this->cache->set($key, json_encode($results));
        return $results;
    }

    /**
     * Caching return data for seasons and episodes
     * @param int $id TMDb tv show id
     * @param int $totalSeasons total number of seasons
     * @return array()
     */
    public function setCacheSeasons($id, $totalSeasons)
    {
        $results = array();
        $key = $id . '_Seasons.json';
        $fromCache = $this->cache->get($key);

        if ($fromCache != null) {
            return json_decode($fromCache);
        }
        for ($currentStart = 1; $currentStart <= $totalSeasons; $currentStart += 20) {
            $currentEnd = min($currentStart + 20 - 1, $totalSeasons);
            $apiCallUrl = $this->doTvSeasonsLookup($id, $currentStart, $currentEnd);
            $requestData = $this->execRequest($apiCallUrl);
            $results = array_merge($results, (array) $requestData);
            unset($requestData);
        }
        $results = (object) $results;
        $this->cache->set($key, json_encode($results));
        return $results;
    }

    /**
     * Create API call url for setCacheSeasons()
     * @param int $tmdbTvId TMDb tv show id
     * @param int $startSeason start season number
     * @param int $endSeason end season number
     * @return string
     */
    private function doTvSeasonsLookup($tmdbTvId, $startSeason, $endSeason)
    {
        $appendUrl = $this->apiUrl;
        $appendUrl .= '/tv/';
        $appendUrl .= $tmdbTvId;
        $appendUrl .= '?';
        $appendUrl .= 'append_to_response=';
        while($startSeason <= $endSeason) {
            $appendUrl .= 'season/' . $startSeason;
            if ($startSeason < $endSeason) {
                $appendUrl .= ',';
            }
            $startSeason++;
        }
        return $appendUrl;
    }

}
