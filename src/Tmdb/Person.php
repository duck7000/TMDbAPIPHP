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
 * A person on TMDb API
 * @author ed (github user: duck7000)
 */
class Person extends MdbBase
{

    protected $results = array();
    protected $tmdbId = null;
    protected $imdbId = null;
    protected $name = null;
    protected $gender = null;
    protected $biography = null;
    protected $birthday = null;
    protected $deathday = null;
    protected $homepage = null;
    protected $popularity = null;
    protected $placeOfBirth = null;
    protected $knownForDepartment = null;
    protected $profileImgPath = null;
    protected $alsoKnownAs = array();
    protected $cast = array();
    protected $crew = array();

    /**
     * @param string $id TMDb person id
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
     * Fetch person data of a TMDb ID
     * @return array (see wiki for details)
     */
    public function fetchPersonData()
    {
        // Data request
        $data = $this->api->doPersonLookup($this->tmdbID);
        if (empty($data)) {
            return $this->results;
        }
        $this->tmdbId = isset($data->id) ? $data->id : null;
        $this->imdbId = isset($data->imdb_id) ? $data->imdb_id : null;
        $this->name = isset($data->name) ? $data->name : null;
        $this->gender = isset($data->gender) ? $this->genderIdToName($data->gender) : null;
        $this->biography = isset($data->biography) ? $data->biography : null;
        $this->birthday = isset($data->birthday) ? $data->birthday : null;
        $this->deathday = isset($data->deathday) ? $data->deathday : null;
        $this->homepage = isset($data->homepage) ? $data->homepage : null;
        $this->popularity = isset($data->popularity) ? $data->popularity : null;
        $this->placeOfBirth = isset($data->place_of_birth) ? $data->place_of_birth : null;
        $this->knownForDepartment = isset($data->known_for_department) ? $data->known_for_department : null;
        $this->profileImgPath = isset($data->profile_path) ? $this->config->baseImageUrl . '/' .
                                                             $this->config->profileImageSize .
                                                             $data->profile_path : null;
        // also known as
        if (isset($data->also_known_as) &&
            is_array($data->also_known_as) &&
            count($data->also_known_as) > 0
           )
        {
            foreach ($data->also_known_as as $alsoKnownAs) {
                if (!empty($alsoKnownAs)) {
                    $this->alsoKnownAs[] = $alsoKnownAs;
                }
            }
        }
        //additional input methods
        // credits cast
        if (isset($data->combined_credits->cast) &&
            is_array($data->combined_credits->cast) &&
            count($data->combined_credits->cast) > 0
           )
        {
            foreach ($data->combined_credits->cast as $cast) {
                $this->cast[] = array(
                    'id' => isset($cast->id) ? $cast->id : null,
                    'title' => isset($cast->title) ? $cast->title : null,
                    'originalTitle' => isset($cast->original_title) ? $cast->original_title : null,
                    'imgPosterPath' => isset($cast->poster_path) ? $this->config->baseImageUrl . '/' .
                                                                   $this->config->posterImageSize .
                                                                   $cast->poster_path : null,
                    'character' => isset($cast->character) ? $cast->character : null,
                    'creditId' => isset($cast->credit_id) ? $cast->credit_id : null,
                    'releaseDate' => isset($cast->release_date) ? $cast->release_date : null,
                    'mediaType' => isset($cast->media_type) ? $cast->media_type : null,
                );
            }
        }
        // credits crew
        if (isset($data->combined_credits->crew) &&
            is_array($data->combined_credits->crew) &&
            count($data->combined_credits->crew) > 0
           )
        {
            foreach ($data->combined_credits->crew as $crew) {
                $type = isset($crew->department) ? str_replace(' ', '', $crew->department) : 'Uncategorized';
                $this->crew[$type][] = array(
                    'id' => isset($crew->id) ? $crew->id : null,
                    'title' => isset($crew->title) ? $crew->title : null,
                    'originalTitle' => isset($crew->original_title) ? $crew->original_title : null,
                    'imgPath' => isset($crew->poster_path) ? $this->config->baseImageUrl . '/' .
                                                             $this->config->posterImageSize .
                                                             $crew->poster_path : null,
                    'creditId' => isset($crew->credit_id) ? $crew->credit_id : null,
                    'job' => isset($crew->job) ? $crew->job : null,
                    'releaseDate' => isset($crew->release_date) ? $crew->release_date : null,
                    'mediaType' => isset($crew->media_type) ? $crew->media_type : null,
                );
            }
        }
        // results array
        $this->results = array(
            'id' => $this->tmdbId,
            'imdbId' => $this->imdbId,
            'name' => $this->name,
            'gender' => $this->gender,
            'biography' => $this->biography,
            'birthday' => $this->birthday,
            'deathday' => $this->deathday,
            'homepage' => $this->homepage,
            'popularity' => $this->popularity,
            'placeOfBirth' => $this->placeOfBirth,
            'knownForDepartment' => $this->knownForDepartment,
            'profileImgPath' => $this->profileImgPath,
            'alsoKnownAs' => $this->alsoKnownAs,
            'cast' => $this->cast,
            'crew' => $this->crew
        );
        return $this->results;
    }

    /**
     * Get gender name from id
     * @param int $genderId
     * @return string gender name
     */
    public function genderIdToName($genderId)
    {
        $ar = array(
            '0' => 'Not set / not specified',
            '1' => 'Female',
            '2' => 'Male',
            '3' => 'Non-binary'
        );
        if (isset($ar[$genderId])) {
            return $ar[$genderId];
        } else {
            return $ar[0];
        }
    }
}
