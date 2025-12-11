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

    protected $popularResults = array();
    protected $upcomingResults = array();
    protected $topRatedResults = array();
    protected $nowPlayingResults = array();

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
     * Fetch popular movies
     * @return array
     */
    public function popular()
    {
        // Data request
        $resultData = $this->api->doListLookup("movie", "popular", 25);
        if (empty($resultData)) {
            return $this->popularResults;
        }
        foreach ($resultData as $data) {
            // results array
            $this->popularResults[] = array(
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
        return $this->popularResults;
    }

    /**
     * Fetch popular movies
     * @return array
     */
    public function upcoming()
    {
        // Data request
        $resultDataUpcoming = $this->api->doListLookup("movie", "upcoming", 50);
        if (empty($resultDataUpcoming)) {
            return $this->upcomingResults;
        }
        foreach ($resultDataUpcoming as $data) {
            $releaseDate = isset($data->release_date) ? $data->release_date : null;
            if(empty($releaseDate) || strtotime($releaseDate) < strtotime(date("Y-m-d"))) {
                 continue;
             }
            // results array
            $this->upcomingResults[] = array(
                'id' => isset($data->id) ? $data->id : null,
                'title' => isset($data->title) ? $data->title : null,
                'originalTitle' => isset($data->original_title) ? $data->original_title : null,
                'releaseDate' => $releaseDate,
                'popularity' => isset($data->popularity) ? $data->popularity : null,
                'voteCount' => isset($data->vote_count) ? $data->vote_count : null,
                'voteAverage' => isset($data->vote_average) ? $data->vote_average : null,
                'posterImgPath' => isset($data->poster_path) ? $this->config->baseImageUrl . '/' .
                                                               $this->config->posterImageSize .
                                                               $data->poster_path : null
            );
        }
        return $this->sortByDate($this->upcomingResults, "ASC");
    }

    /**
     * Fetch popular movies
     * @return array
     */
    public function topRated()
    {
        // Data request
        $topRatedData = $this->api->doListLookup("movie", "top_rated", 25);
        if (empty($topRatedData)) {
            return $this->topRatedResults;
        }
        foreach ($topRatedData as $data) {
            // results array
            $this->topRatedResults[] = array(
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
        return $this->topRatedResults;
    }

    /**
     * Fetch popular movies
     * @return array
     */
    public function nowPlaying()
    {
        // Data request
        $nowPlayingData = $this->api->doListLookup("movie", "now_playing", 25);
        if (empty($nowPlayingData)) {
            return $this->nowPlayingResults;
        }
        foreach ($nowPlayingData as $data) {
            // results array
            $this->nowPlayingResults[] = array(
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
        return $this->nowPlayingResults;
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
