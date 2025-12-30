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
 * User account info, favorites, rated, watchlist at TMDb API
 * @author ed (github user: duck7000)
 */
class UserAccount extends MdbBase
{

    protected $details = array();
    protected $favoriteMovies = array();
    protected $favoriteTv = array();
    protected $ratedMovies = array();
    protected $ratedTv = array();
    protected $watchlistMovies = array();
    protected $watchlistTv = array();

    /**
     * @param string|int $id TMDb account id
     * @param Config $config OPTIONAL override default config
     * @param Logger $cache OPTIONAL override the default logger with a custom one.
     * @param CacheInterface $cache OPTIONAL override the default cache with any PSR-16 cache.
     */
    public function __construct(string $id, ?Config $config = null, ?LoggerInterface $logger = null, ?CacheInterface $cache = null)
    {
        parent::__construct($config, $logger, $cache);
        $this->setid($id);
    }

    /**
     * Fetch user account details
     * @return array
     */
    public function details()
    {
        // Data request
        $resultData = $this->api->doUserAccountListLookup($this->tmdbID, "details");
        if (empty($resultData) || empty((array) $resultData)) {
            return $this->details;
        }
        $this->details = array(
            'id' => isset($resultData->id) ? $resultData->id : null,
            'name' => isset($resultData->name) ? $resultData->name : null,
            'username' => isset($resultData->username) ? $resultData->username : null
        );
        return $this->details;
    }

    /**
     * Fetch user favorite movies
     * @return array
     */
    public function favoriteMovie()
    {
        // Data request
        $favoriteData = $this->api->doUserAccountListLookup($this->tmdbID, "favorite", "movies");
        if (empty($favoriteData) || empty((array) $favoriteData)) {
            return $this->favoriteMovies;
        }
        foreach ($favoriteData as $data) {
            $this->favoriteMovies[] = array(
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
        return $this->favoriteMovies;
    }

    /**
     * Fetch user favorite tv shows
     * @return array
     */
    public function favoriteTv()
    {
        // Data request
        $favoriteTvData = $this->api->doUserAccountListLookup($this->tmdbID, "favorite", "tv");
        if (empty($favoriteTvData) || empty((array) $favoriteTvData)) {
            return $this->favoriteTv;
        }
        foreach ($favoriteTvData as $data) {
            $this->favoriteTv[] = array(
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
        return $this->favoriteTv;
    }

    /**
     * Fetch user rated movies
     * @return array
     */
    public function ratedMovie()
    {
        // Data request
        $ratedData = $this->api->doUserAccountListLookup($this->tmdbID, "rated", "movies");
        if (empty($ratedData) || empty((array) $ratedData)) {
            return $this->ratedMovies;
        }
        foreach ($ratedData as $data) {
            $this->ratedMovies[] = array(
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
        return $this->ratedMovies;
    }

    /**
     * Fetch user rated tv shows
     * @return array
     */
    public function ratedTv()
    {
        // Data request
        $ratedTvData = $this->api->doUserAccountListLookup($this->tmdbID, "rated", "tv");
        if (empty($ratedTvData) || empty((array) $ratedTvData)) {
            return $this->ratedTv;
        }
        foreach ($ratedTvData as $data) {
            $this->ratedTv[] = array(
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
        return $this->ratedTv;
    }

    /**
     * Fetch user watchlist movies
     * @return array
     */
    public function watchlistMovie()
    {
        // Data request
        $watchlistData = $this->api->doUserAccountListLookup($this->tmdbID, "watchlist", "movies");
        if (empty($watchlistData) || empty((array) $watchlistData)) {
            return $this->watchlistMovies;
        }
        foreach ($watchlistData as $data) {
            $this->watchlistMovies[] = array(
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
        return $this->watchlistMovies;
    }

    /**
     * Fetch user watchlist tv shows
     * @return array
     */
    public function watchlistTv()
    {
        // Data request
        $watchlistTvData = $this->api->doUserAccountListLookup($this->tmdbID, "watchlist", "tv");
        if (empty($watchlistTvData) || empty((array) $watchlistTvData)) {
            return $this->watchlistTv;
        }
        foreach ($watchlistTvData as $data) {
            $this->watchlistTv[] = array(
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
        return $this->watchlistTv;
    }
}
