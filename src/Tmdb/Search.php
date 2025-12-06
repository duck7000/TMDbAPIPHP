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
     *          [imgUrl] => https://image.tmdb.org/t/p/w185/7JW6WKINXbvGxAFRvDiaN4SMY2h.jpg
     *      )
     *  )
     */
    public function textSearch($searchString, $searchType = "multi")
    {
        $results = array();
        $data = $this->api->doTextSearch(rawurlencode($searchString), $searchType);
        if (empty($data) || empty($data->results)) {
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
                    'imgUrl' => isset($value->poster_path) ? $this->config->baseImageUrl . '/' .
                                                             $this->config->posterImageSize .
                                                             $value->poster_path : null
                );
            }
        }
        return $results;
    }

    /**
     * Search for titles matching external id number
     * @param string $externalId input external id number (complete number, all characters)
     * @param string $externalSource input external id source, default: imdb_id
     * FOR NOW ONLY IMDb IS SUPPORTED!
     * Possible externalSource Types:
     *      "imdb_id" (incl tt or nm)
     *      "facebook_id"
     *      "instagram_id"
     *      "tvdb_id"
     *      "tiktok_id"
     *      "twitter_id"
     *      "wikidata_id"
     *      "youtube_id"
     * @return
     * Array(
     *      [0] => Array(
     *          [id] => 3021
     *          [name] => 1408
     *          [originalName] => 1408
     *          [date] => 2007-10-24 (person: null)
     *          [type] => movie
     *          [imgUrl] => https://image.tmdb.org/t/p/w185/yE9MCW7ZNxSw5SC1TMqm51pMBIV.jpg
     *      )
     * )
     */
    public function externalIdSearch($externalId, $externalSource = 'imdb_id')
    {
        $results = array();
        $data = $this->api->doExternalIdSearch($externalId, $externalSource);
        if (empty($data)) {
            return $results;
        }
        $castData = (array) $data;
        foreach ($castData as $value) {
            if (empty($value)) {
                continue;
            }
            foreach ($value as $key => $item) {
                if ($externalSource == 'imdb_id') {
                    if (stripos($externalId, "nm") !==false) {
                        // person, id start with nm
                        $results[] = array(
                            'id' => isset($item->id) ? $item->id : null,
                            'name' => isset($item->name) ? $item->name : null,
                            'originalName' => isset($item->original_name) ? $item->original_name : null,
                            'date' => null,
                            'type' => isset($item->media_type) ? $item->media_type : 'person',
                            'imgUrl' => isset($item->profile_path) ?
                                                 $this->config->baseImageUrl . '/' .
                                                 $this->config->profileImageSize .
                                                 $item->profile_path : null,
                        );
                    } else {
                        // movie or tv, id start with tt
                        $results[] = array(
                            'id' => isset($item->id) ? $item->id : null,
                            'name' => isset($item->title) ? $item->title : null,
                            'originalName' => isset($item->original_title) ? $item->original_title : null,
                            'date' => isset($item->release_date) ? $item->release_date : null,
                            'type' => isset($item->media_type) ? $item->media_type : 'movie',
                            'imgUrl' => isset($item->poster_path) ?
                                                 $this->config->baseImageUrl . '/' .
                                                 $this->config->posterImageSize .
                                                 $item->poster_path : null,
                        );
                    }
                } 
            }
        }
        return $results;
    }

}
