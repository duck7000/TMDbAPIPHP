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
    protected $lists = array();

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
     * Get a users list of custom lists.
     * @return array
     */
    public function lists()
    {
        // Data request
        $resultListData = $this->api->doUserAccountListLookup($this->tmdbID, "lists");
        if (empty($resultListData) || empty((array) $resultListData)) {
            return $this->lists;
        }
        foreach ($resultListData as $resultData) {
            $this->lists[] = array(
                'id' => isset($resultData->id) ? $resultData->id : null,
                'name' => isset($resultData->name) ? $resultData->name : null,
                'description' => isset($resultData->description) ? $resultData->description : null,
                'favoriteCount' => isset($resultData->favorite_count) ? $resultData->favorite_count : null,
                'itemCount' => isset($resultData->item_count) ? $resultData->item_count : null,
                'listType' => isset($resultData->list_type) ? $resultData->list_type : null,
                'posterImgPath' => isset($resultData->poster_path) ? $this->config->baseImageUrl . '/' .
                                                                     $this->config->posterImageSize .
                                                                     $resultData->poster_path : null
            );
        }
        return $this->lists;
    }

    /**
     * Fetch user favorite movies
     * @return array
     */
    public function favoriteMovie()
    {
        return $this->userList($this->tmdbID, "favorite", "movies");
    }

    /**
     * Fetch user favorite tv shows
     * @return array
     */
    public function favoriteTv()
    {
        return $this->userList($this->tmdbID, "favorite", "tv");
    }

    /**
     * Fetch user rated movies
     * @return array
     */
    public function ratedMovie()
    {
        return $this->userList($this->tmdbID, "rated", "movies");
    }

    /**
     * Fetch user rated tv shows
     * @return array
     */
    public function ratedTv()
    {
        return $this->userList($this->tmdbID, "rated", "tv");
    }

    /**
     * Fetch user watchlist movies
     * @return array
     */
    public function watchlistMovie()
    {
        return $this->userList($this->tmdbID, "watchlist", "movies");
    }

    /**
     * Fetch user watchlist tv shows
     * @return array
     */
    public function watchlistTv()
    {
        return $this->userList($this->tmdbID, "watchlist", "tv");
    }

    /**
     * Fetch user movies, watchlist or tv shows
     * @param string|int $id input account Id
     * @param string $listType account lookup list type: details, favorite, rated, watchlist
     * @param string $mediaType account lookup media type: movies, tv
     * @return array
     */
    private function userList($id, $listType, $mediaType)
    {
        $results = array();
        $listData = $this->api->doUserAccountListLookup($id, $listType, $mediaType);
        if (empty($listData) || empty((array) $listData)) {
            return $results;
        }
        foreach ($listData as $data) {
            if ($mediaType == 'movies') {
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
            } else {
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
        }
        return $results;
    }
}
