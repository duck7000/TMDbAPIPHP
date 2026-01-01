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
 * Movie lists on TMDb API
 * @author ed (github user: duck7000)
 */
class MovieList extends MdbBase
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
     * Fetch list of popular movies
     * @return array
     */
    public function popularMovie()
    {
        return $this->fetchList("popular", 25);
    }

    /**
     * Fetch list of top rated movies
     * @return array
     */
    public function topRatedMovie()
    {
        return $this->fetchList("top_rated", 25);
    }

    /**
     * Fetch list of now playing movies
     * @return array
     */
    public function nowPlayingMovie()
    {
        return $this->fetchList("now_playing", 25);
    }

    /**
     * Fetch list of upcoming movies sorted by release date ASC
     * @return array
     */
    public function upcomingMovie()
    {
        $temp = array();
        $upcomingResults = $this->fetchList("upcoming", 50);
        foreach ($upcomingResults as $item) {
            $releaseDate = isset($item["releaseDate"]) ? $item["releaseDate"] : null;
            if(empty($releaseDate) || strtotime($releaseDate) < strtotime(date("Y-m-d"))) {
                continue;
            }
            $temp[] = $item;
        }
        return $this->sortByDate($temp, "ASC");
    }

    /**
     * Fetch list movies
     * @param string $type list type: upcoming, now_playing, top_rated, popular
     * @param int $pages how many pages of data to return
     * @return array
     */
    private function fetchList($type, $pages)
    {
        $results = array();
        $resultData = $this->api->doListLookup("movie", $type, $pages);
        if (empty($resultData) || empty((array) $resultData)) {
            return $results;
        }
        foreach ($resultData as $data) {
            // results array
            $results[] = array(
                'id' => isset($data->id) ? $data->id : null,
                'title' => isset($data->title) ? $data->title : null,
                'originalTitle' => isset($data->original_title) ? $data->original_title : null,
                'releaseDate' => isset($data->release_date) ? $data->release_date : null,
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

    /**
     * Sort $results array by date
     * @param array $array
     * @param string $sortOrder ASC or DESC
     * @return sorted array
     */
    protected function sortByDate($array, $sortOrder)
    {
        if ($sortOrder == 'DESC') {
            // sort array by date
            usort($array, function($a, $b) {
                $ad = $a['releaseDate'];
                $bd = $b['releaseDate'];

                if ($ad == $bd) {
                return 0;
                }

                return $ad > $bd ? -1 : 1;
            });
        } else {
            // sort array by date
            usort($array, function($a, $b) {
                $ad = $a['releaseDate'];
                $bd = $b['releaseDate'];

                if ($ad == $bd) {
                return 0;
                }

                return $ad < $bd ? -1 : 1;
            });
        }
        return $array;
    }
}
