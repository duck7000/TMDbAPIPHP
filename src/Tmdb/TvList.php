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
     * Fetch list of popular tv shows
     * @return array
     */
    public function popularTv()
    {
        return $this->fetchList("popular", 25);
    }

    /**
     * Fetch list of top rated tv shows
     * @return array
     */
    public function topRatedTv()
    {
        return $this->fetchList("top_rated", 25);
    }

    /**
     * Fetch list of on the air tv shows
     * @return array
     */
    public function onTheAirTv()
    {
        return $this->fetchList("on_the_air", 25);
    }

    /**
     * Fetch list of airing today tv shows
     * @return array
     */
    public function airingTodayTv()
    {
        return $this->fetchList("airing_today", 25);
    }

    /**
     * Fetch list tv series
     * @param string $type list type: airing_today, on_the_air, top_rated, popular
     * @param int $pages how many pages of data to return
     * @return array
     */
    private function fetchList($type, $pages)
    {
        $results = array();
        $resultData = $this->api->doListLookup("tv", $type, $pages);
        if (empty($resultData) || empty((array) $resultData)) {
            return $results;
        }
        foreach ($resultData as $data) {
            // results array
            $results[] = array(
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
        return $results;
    }
}
