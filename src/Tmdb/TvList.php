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
 * Tv lists on TMDb API
 * @author ed (github user: duck7000)
 */
class TvList extends MdbBase
{

    protected $popularResults = array();
    protected $topRatedResults = array();
    protected $onTheAirResults = array();
    protected $airingTodayResults = array();

    /**
     * @param Config $config OPTIONAL override default config
     * @param Logger $cache OPTIONAL override the default logger with a custom one.
     * @param CacheInterface $cache OPTIONAL override the default cache with any PSR-16 cache.
     */
    public function __construct(?Config $config = null, ?LoggerInterface $logger = null, ?CacheInterface $cache = null)
    {
        parent::__construct($config, $logger, $cache);
    }

    /**
     * Fetch popular tv series
     * @return array
     */
    public function popular()
    {
        // Data request
        $resultData = $this->api->doListLookup("tv", "popular", 25);
        if (empty($resultData) || empty((array) $resultData)) {
            return $this->popularResults;
        }
        foreach ($resultData as $data) {
            // results array
            $this->popularResults[] = array(
                'id' => isset($data->id) ? $data->id : null,
                'name' => isset($data->name) ? $data->name : null,
                'originalName' => isset($data->original_name) ? $data->original_name : null,
                'firstAirDate' => isset($data->first_air_date) ? $data->first_air_date : null,
                'popularity' => isset($data->popularity) ? $data->popularity : null,
                'voteCount' => isset($data->vote_count) ? $data->vote_count : null,
                'voteAverage' => isset($data->vote_average) ? $data->vote_average : null,
                'posterImgPath' => isset($data->poster_path) ? $this->config->baseImageUrl . '/' .
                                                               $this->config->posterImageSize .
                                                               $data->poster_path : null
            );
        }
        return $this->popularResults;
    }

    /**
     * Fetch top rated tv series
     * @return array
     */
    public function topRated()
    {
        // Data request
        $topRatedData = $this->api->doListLookup("tv", "top_rated", 25);
        if (empty($topRatedData) || empty((array) $topRatedData)) {
            return $this->topRatedResults;
        }
        foreach ($topRatedData as $data) {
            // results array
            $this->topRatedResults[] = array(
                'id' => isset($data->id) ? $data->id : null,
                'name' => isset($data->name) ? $data->name : null,
                'originalName' => isset($data->original_name) ? $data->original_name : null,
                'firstAirDate' => isset($data->first_air_date) ? $data->first_air_date : null,
                'popularity' => isset($data->popularity) ? $data->popularity : null,
                'voteCount' => isset($data->vote_count) ? $data->vote_count : null,
                'voteAverage' => isset($data->vote_average) ? $data->vote_average : null,
                'posterImgPath' => isset($data->poster_path) ? $this->config->baseImageUrl . '/' .
                                                               $this->config->posterImageSize .
                                                               $data->poster_path : null
            );
        }
        return $this->topRatedResults;
    }

    /**
     * Fetch on the air tv series
     * @return array
     */
    public function onTheAir()
    {
        // Data request
        $onTheAirData = $this->api->doListLookup("tv", "on_the_air", 25);
        if (empty($onTheAirData) || empty((array) $onTheAirData)) {
            return $this->onTheAirResults;
        }
        foreach ($onTheAirData as $data) {
            // results array
            $this->onTheAirResults[] = array(
                'id' => isset($data->id) ? $data->id : null,
                'name' => isset($data->name) ? $data->name : null,
                'originalName' => isset($data->original_name) ? $data->original_name : null,
                'firstAirDate' => isset($data->first_air_date) ? $data->first_air_date : null,
                'popularity' => isset($data->popularity) ? $data->popularity : null,
                'voteCount' => isset($data->vote_count) ? $data->vote_count : null,
                'voteAverage' => isset($data->vote_average) ? $data->vote_average : null,
                'posterImgPath' => isset($data->poster_path) ? $this->config->baseImageUrl . '/' .
                                                               $this->config->posterImageSize .
                                                               $data->poster_path : null
            );
        }
        return $this->onTheAirResults;
    }

    /**
     * Fetch airing today tv series
     * @return array
     */
    public function airingToday()
    {
        // Data request
        $airingTodayData = $this->api->doListLookup("tv", "airing_today", 25);
        if (empty($airingTodayData) || empty((array) $airingTodayData)) {
            return $this->airingTodayResults;
        }
        foreach ($airingTodayData as $data) {
            // results array
            $this->airingTodayResults[] = array(
                'id' => isset($data->id) ? $data->id : null,
                'name' => isset($data->name) ? $data->name : null,
                'originalName' => isset($data->original_name) ? $data->original_name : null,
                'firstAirDate' => isset($data->first_air_date) ? $data->first_air_date : null,
                'popularity' => isset($data->popularity) ? $data->popularity : null,
                'voteCount' => isset($data->vote_count) ? $data->vote_count : null,
                'voteAverage' => isset($data->vote_average) ? $data->vote_average : null,
                'posterImgPath' => isset($data->poster_path) ? $this->config->baseImageUrl . '/' .
                                                               $this->config->posterImageSize .
                                                               $data->poster_path : null
            );
        }
        return $this->airingTodayResults;
    }
}
