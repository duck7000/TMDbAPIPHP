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
 * Trending list on TMDb API
 * @author ed (github user: duck7000)
 */
class Trending extends MdbBase
{

    protected $trendingResults = array();

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
     * Fetch trending movie, persons or tv series
     * @param string $type trending type: movie, person, tv, all (default: all)
     * @param string $timeWindow time window: day, week (default: week)
     * @return array
     */
    public function trending($type = "all", $timeWindow = "week")
    {
        // Data request
        $resultData = $this->api->doListTrendingLookup($type, $timeWindow);
        if (empty($resultData->results)) {
            return $this->trendingResults;
        }
        foreach ($resultData->results as $value) {
            if (isset($value->media_type) && $value->media_type === 'movie') {
                $this->trendingResults[] = array(
                    'id' => isset($value->id) ? $value->id : null,
                    'name' => isset($value->title) ? $value->title : null,
                    'originalName' => isset($value->original_title) ? $value->original_title : null,
                    'date' => isset($value->release_date) ? $value->release_date : null,
                    'type' => $value->media_type,
                    'imgUrl' => isset($value->poster_path) ? $this->config->baseImageUrl . '/' .
                                                             $this->config->posterImageSize .
                                                             $value->poster_path : null,
                );
            } elseif (isset($value->media_type) && $value->media_type === 'person') {
                $this->trendingResults[] = array(
                    'id' => isset($value->id) ? $value->id : null,
                    'name' => isset($value->name) ? $value->name : null,
                    'originalName' => isset($value->original_name) ? $value->original_name : null,
                    'type' => $value->media_type,
                    'imgUrl' => isset($value->profile_path) ? $this->config->baseImageUrl . '/' .
                                                              $this->config->profileImageSize .
                                                              $value->profile_path : null,
                );
            } elseif (isset($value->media_type) && $value->media_type === 'tv') {
                $this->trendingResults[] = array(
                    'id' => isset($value->id) ? $value->id : null,
                    'name' => isset($value->name) ? $value->name : null,
                    'originalName' => isset($value->original_name) ? $value->original_name : null,
                    'type' => $value->media_type,
                    'date' => isset($value->first_air_date) ? $value->first_air_date : null,
                    'imgUrl' => isset($value->poster_path) ? $this->config->baseImageUrl . '/' .
                                                             $this->config->posterImageSize .
                                                             $value->poster_path : null,
                );
            }
        }
        return $this->trendingResults;
    }
}
