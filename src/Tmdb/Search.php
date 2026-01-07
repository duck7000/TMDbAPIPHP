<?php

#############################################################################
# TMDbAPIPHP                                    ed (github user: duck7000)  #
# written by ed (github user: duck7000)                                     #
# ------------------------------------------------------------------------- #
# This program is free software; you can redistribute and/or modify it      #
# under the terms of the GNU General Public License (see doc/LICENSE)       #
#############################################################################

namespace Tmdb;

class Search extends MdbBase
{

    /**
     * Search for titles matching text input search query
     * @param string $searchString input search string
     * @param string $searchType input search type, default: multi
     * @param bool $includeAdult include adult results or not, default: true
     * Possible search Types:
     *      "tv"
     *      "movie"
     *      "person"
     *      "keyword"
     *      "company"
     *      "collection"
     *      "multi" (search within all types)
     * @return
     * Array(
     *      [0] => Array(
     *          [id] => 940751
     *          [name] => Reasons
     *          [originalName] => Reasons
     *          [date] => 2022-06-23
     *          [type] => movie
     *          [adult] => true
     *          [imgUrl] => https://image.tmdb.org/t/p/w185/7JW6WKINXbvGxAFRvDiaN4SMY2h.jpg
     *      )
     *  )
     */
    public function textSearch($searchString, $searchType = "multi", $includeAdult = true)
    {
        $results = array();
        $data = $this->api->doTextSearch(rawurlencode($searchString), $searchType, $includeAdult);
        if (empty($data) || empty((array) $data)) {
            return $results;
        }
        foreach ($data->results as $value) {
            if ($searchType === 'movie' || (isset($value->media_type) && $value->media_type === 'movie')) {
                $results[] = array(
                    'id' => isset($value->id) ? $value->id : null,
                    'name' => isset($value->title) ? $value->title : null,
                    'originalName' => isset($value->original_title) ? $value->original_title : null,
                    'date' => isset($value->release_date) ? $value->release_date : null,
                    'type' => isset($value->media_type) ? $value->media_type : 'movie',
                    'adult' => isset($value->adult) ? $value->adult : false,
                    'imgUrl' => isset($value->poster_path) ? $this->config->baseImageUrl . '/' .
                                                             $this->config->posterImageSize .
                                                             $value->poster_path : null,
                );
            } elseif ($searchType === 'person'|| (isset($value->media_type) && $value->media_type === 'person')) {
                $results[] = array(
                    'id' => isset($value->id) ? $value->id : null,
                    'name' => isset($value->name) ? $value->name : null,
                    'originalName' => isset($value->original_name) ? $value->original_name : null,
                    'type' => isset($value->media_type) ? $value->media_type : 'person',
                    'adult' => isset($value->adult) ? $value->adult : false,
                    'imgUrl' => isset($value->profile_path) ? $this->config->baseImageUrl . '/' .
                                                              $this->config->profileImageSize .
                                                              $value->profile_path : null,
                );
            } elseif ($searchType === 'tv'|| (isset($value->media_type) && $value->media_type === 'tv')) {
                $results[] = array(
                    'id' => isset($value->id) ? $value->id : null,
                    'name' => isset($value->name) ? $value->name : null,
                    'originalName' => isset($value->original_name) ? $value->original_name : null,
                    'type' => isset($value->media_type) ? $value->media_type : 'tv',
                    'adult' => isset($value->adult) ? $value->adult : false,
                    'date' => isset($value->first_air_date) ? $value->first_air_date : null,
                    'imgUrl' => isset($value->poster_path) ? $this->config->baseImageUrl . '/' .
                                                             $this->config->posterImageSize .
                                                             $value->poster_path : null,
                );
            } elseif ($searchType === 'keyword'|| (isset($value->media_type) && $value->media_type === 'keyword')) {
                $results[] = array(
                    'id' => isset($value->id) ? $value->id : null,
                    'name' => isset($value->name) ? $value->name : null
                );
            } elseif ($searchType === 'company'|| (isset($value->media_type) && $value->media_type === 'company')) {
                $results[] = array(
                    'id' => isset($value->id) ? $value->id : null,
                    'name' => isset($value->name) ? $value->name : null,
                    'originCountry' => isset($value->origin_country) ? $value->origin_country : null,
                    'imgUrl' => isset($value->logo_path) ? $this->config->baseImageUrl . '/' .
                                                           $this->config->logoImageSize .
                                                           $value->logo_path : null,
                );
            } elseif ($searchType === 'collection'|| (isset($value->media_type) && $value->media_type === 'collection')) {
                $results[] = array(
                    'id' => isset($value->id) ? $value->id : null,
                    'name' => isset($value->name) ? $value->name : null,
                    'originalName' => isset($value->original_name) ? $value->original_name : null,
                    'adult' => isset($value->adult) ? $value->adult : false,
                    'imgUrl' => isset($value->poster_path) ? $this->config->baseImageUrl . '/' .
                                                             $this->config->posterImageSize .
                                                             $value->poster_path : null
                );
            }
        }
        return $results;
    }

    /**
     * Translate IMDb tt or nm id to TMDb id
     * @param string $externalId input imdb id (complete number, all characters)
     * @return
     * Array(
     *      [0] => Array(
     *          [id] => 3021
     *          [type] => movie
     *      )
     * )
     */
    public function imdbToTmdb($externalId)
    {
        $results = array();
        $data = $this->api->doExternalIdSearch($externalId, "imdb_id");
        if (empty($data) || empty((array) $data)) {
            return $results;
        }
        $castData = (array) $data;
        foreach ($castData as $value) {
            if (empty($value)) {
                continue;
            }
            foreach ($value as $key => $item) {
                if (empty($item)) {
                    continue;
                }
                $results = array(
                    'id' => isset($item->id) ? $item->id : null,
                    'type' => isset($item->media_type) ? $item->media_type : null
                );
            }
        }
        return $results;
    }
}
