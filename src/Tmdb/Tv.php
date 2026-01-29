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
 * A tv serie on TMDb API
 * @author ed (github user: duck7000)
 */
class Tv extends MdbBase
{

    protected $results = array();
    protected $tmdbId = null;
    protected $imdbId = null;
    protected $wikidataId = null;
    protected $tvdbId = null;
    protected $name = null;
    protected $originalName = null;
    protected $overview = null;
    protected $originalLanguage = null;
    protected $firstAirDate = null;
    protected $lastAirDate = null;
    protected $totalSeasons = 0;
    protected $totalEpisodes = 0;
    protected $tagline = null;
    protected $status = null;
    protected $adult = true;
    protected $type = null;
    protected $inProduction = false;
    protected $homepage = null;
    protected $popularity = null;
    protected $voteCount = null;
    protected $voteAverage = null;
    protected $posterImgPath = null;
    protected $genres = array();
    protected $originCountry = array();
    protected $createdBy = array();
    protected $productionCompanies = array();
    protected $productionCountries = array();
    protected $spokenLanguages = array();
    protected $alternativeTitles = array();
    protected $images = array();
    protected $keywords = array();
    protected $videos = array();
    protected $recommendations = array();
    protected $cast = array();
    protected $crew = array();
    protected $networks = array();
    protected $seasonsData = array();
    protected $seasonsEpisodes = array();
    protected $watchProviders = array();
    protected $reviews = array();
    protected $contentRatings = array();
    protected $seasons;

    /**
     * @param string $id TMDb id
     * @param Config $config OPTIONAL override default config
     * @param Logger $cache OPTIONAL override the default logger with a custom one.
     * @param CacheInterface $cache OPTIONAL override the default cache with any PSR-16 cache.
     */
    public function __construct(string|int $id, ?Config $config = null, ?LoggerInterface $logger = null, ?CacheInterface $cache = null)
    {
        parent::__construct($config, $logger, $cache);
        $this->setid($id);
        $this->seasons = new Seasons();
    }

    /**
     * Fetch tv series data of a TMDb ID
     * @return array
     */
    public function fetchTvData(): array
    {
        // Data request
        $data = $this->api->doLookup($this->tmdbID, "tv");
        if (empty($data) || empty((array) $data)) {
            return $this->results;
        }
        $this->tmdbId = isset($data->id) ? $data->id : null;
        $this->imdbId = isset($data->external_ids->imdb_id) ? $data->external_ids->imdb_id : null;
        $this->wikidataId = isset($data->external_ids->wikidata_id) ? $data->external_ids->wikidata_id : null;
        $this->tvdbId = isset($data->external_ids->tvdb_id) ? $data->external_ids->tvdb_id : null;
        $this->name = isset($data->name) ? $data->name : null;
        $this->originalName = isset($data->original_name) ? $data->original_name : null;
        $this->overview = isset($data->overview) ? $data->overview : null;
        $this->originalLanguage = isset($data->original_language) ? $data->original_language : null;
        $this->firstAirDate = isset($data->first_air_date) ? $data->first_air_date : null;
        $this->lastAirDate = isset($data->last_air_date) ? $data->last_air_date : null;
        $this->totalSeasons = isset($data->number_of_seasons) ? $data->number_of_seasons : 0;
        $this->totalEpisodes = isset($data->number_of_episodes) ? $data->number_of_episodes : 0;
        $this->tagline = isset($data->tagline) ? $data->tagline : null;
        $this->status = isset($data->status) ? $data->status : null;
        $this->adult = isset($data->adult) ? $data->adult : true;
        $this->type = isset($data->type) ? $data->type : null;
        $this->inProduction = isset($data->in_production) ? $data->in_production : false;
        $this->homepage = isset($data->homepage) ? $data->homepage : null;
        $this->popularity = isset($data->popularity) ? $data->popularity : null;
        $this->voteCount = isset($data->vote_count) ? $data->vote_count : null;
        $this->voteAverage = isset($data->vote_average) ? $data->vote_average : null;
        $this->posterImgPath = isset($data->poster_path) ? $this->config->baseImageUrl . '/' .
                                                           $this->config->posterImageSize .
                                                           $data->poster_path : null;
        // genres
        if (isset($data->genres) &&
            is_array($data->genres) &&
            count($data->genres) > 0
           )
        {
            foreach ($data->genres as $genreObject) {
                $this->genres[] = array(
                    'id' => isset($genreObject->id) ? $genreObject->id : null,
                    'name' => isset($genreObject->name) ? $genreObject->name : null
                );
            }
        }
        // origin country
        if (isset($data->origin_country) &&
            is_array($data->origin_country) &&
            count($data->origin_country) > 0
           )
        {
            foreach ($data->origin_country as $country) {
                if (!empty($country)) {
                    $this->originCountry[] = $country;
                }
            }
        }
        // created by
        if (isset($data->created_by) &&
            is_array($data->created_by) &&
            count($data->created_by) > 0
           )
        {
            foreach ($data->created_by as $person) {
                $this->createdBy[] = array(
                    'id' => isset($person->id) ?
                                  $person->id : null,
                    'name' => isset($person->name) ?
                                    $person->name : null
                );
            }
        }
        // production companies
        if (isset($data->production_companies) &&
            is_array($data->production_companies) &&
            count($data->production_companies) > 0
           )
        {
            foreach ($data->production_companies as $companyObject) {
                $this->productionCompanies[] = array(
                    'id' => isset($companyObject->id) ? $companyObject->id : null,
                    'name' => isset($companyObject->name) ? $companyObject->name : null,
                    'originCountry' => isset($companyObject->origin_country) ?
                                             $companyObject->origin_country : null,
                    'LogoImgPath' => isset($companyObject->logo_path) ? $this->config->baseImageUrl . '/' .
                                                                        $this->config->logoImageSize .
                                                                        $companyObject->logo_path : null
                );
            }
        }
        // production countries
        if (isset($data->production_countries) &&
            is_array($data->production_countries) &&
            count($data->production_countries) > 0
           )
        {
            foreach ($data->production_countries as $productionCountryObject) {
                $this->productionCountries[] = array(
                    'iso3166' => isset($productionCountryObject->iso_3166_1) ?
                                       $productionCountryObject->iso_3166_1 : null,
                    'name' => isset($productionCountryObject->name) ?
                                    $productionCountryObject->name : null
                );
            }
        }
        // spoken languages
        if (isset($data->spoken_languages) &&
            is_array($data->spoken_languages) &&
            count($data->spoken_languages) > 0
           )
        {
            foreach ($data->spoken_languages as $spokenLanguagesObject) {
                $this->spokenLanguages[] = array(
                    'iso639' => isset($spokenLanguagesObject->iso_639_1) ?
                                      $spokenLanguagesObject->iso_639_1 : null,
                    'name' => isset($spokenLanguagesObject->name) ?
                                    $spokenLanguagesObject->name : null,
                    'englishName' => isset($spokenLanguagesObject->english_name) ?
                                           $spokenLanguagesObject->english_name : null
                );
            }
        }

        //additional input methods
        // alternative titles (also known as)
        if (isset($data->alternative_titles->results) &&
            is_array($data->alternative_titles->results) &&
            count($data->alternative_titles->results) > 0
           )
        {
            foreach ($data->alternative_titles->results as $alternativeTitlesObject) {
                $this->alternativeTitles[] = array(
                    'iso3166' => isset($alternativeTitlesObject->iso_3166_1) ?
                                       $alternativeTitlesObject->iso_3166_1 : null,
                    'title' => isset($alternativeTitlesObject->title) ?
                                     $alternativeTitlesObject->title : null,
                    'type' => isset($alternativeTitlesObject->type) ?
                                    $alternativeTitlesObject->type : null
                );
            }
        }
        // images
        if (isset($data->images)) {
            $imagesArray = (array) $data->images;
            foreach ($imagesArray as $indexKey => $imageData) {
                foreach ($imageData as $imageItem) {
                    $this->images[$indexKey][] = array(
                        'imgPath' => isset($imageItem->file_path) ? $this->config->baseImageUrl . '/' .
                                                                   $this->config->backdropImageSize .
                                                                   $imageItem->file_path : null,
                        'height' => isset($imageItem->height) ?
                                          $imageItem->height : null,
                        'width' => isset($imageItem->width) ?
                                         $imageItem->width : null,
                        'aspectRatio' => isset($imageItem->aspect_ratio) ?
                                               $imageItem->aspect_ratio : null
                    );
                }
            }
        }
        // keywords
        if (isset($data->keywords->results) &&
            is_array($data->keywords->results) &&
            count($data->keywords->results) > 0
           )
        {
            foreach ($data->keywords->results as $keyword) {
                $this->keywords[] = array(
                    'id' => isset($keyword->id) ?
                                  $keyword->id : null,
                    'name' => isset($keyword->name) ?
                                    $keyword->name : null
                );
            }
        }
        // videos
        if (isset($data->videos->results) &&
            is_array($data->videos->results) &&
            count($data->videos->results) > 0
           )
        {
            foreach ($data->videos->results as $videos) {
                $type = isset($videos->type) ? str_replace(' ', '', $videos->type) : 'Uncategorized';
                $this->videos[$type][] = array(
                    'tmdbVideoId' => isset($videos->id) ? $videos->id : null,
                    'name' => isset($videos->name) ? $videos->name : null,
                    'key' => isset($videos->key) ? $videos->key : null,
                    'site' => isset($videos->site) ? $videos->site : null,
                    'size' => isset($videos->size) ? $videos->size : null,
                    'official' => isset($videos->official) ? $videos->official : false
                );
            }
        }
        // recommendations
        if (isset($data->recommendations->results) &&
            is_array($data->recommendations->results) &&
            count($data->recommendations->results) > 0
           )
        {
            foreach ($data->recommendations->results as $recommendation) {
                $this->recommendations[] = array(
                    'id' => isset($recommendation->id) ? $recommendation->id : null,
                    'title' => isset($recommendation->name) ? $recommendation->name : null,
                    'originalTitle' => isset($recommendation->original_name) ?
                                             $recommendation->original_name : null,
                    'firstAirDate' => isset($recommendation->first_air_date) ?
                                            $recommendation->first_air_date : null,
                    'overview' => isset($recommendation->overview) ?
                                        $recommendation->overview : null,
                    'mediaType' => isset($recommendation->media_type) ?
                                         $recommendation->media_type : null,
                    'voteAverage' => isset($recommendation->vote_average) ?
                                           $recommendation->vote_average : null,
                    'imgPath' => isset($recommendation->poster_path) ? $this->config->baseImageUrl . '/' .
                                                                       $this->config->posterImageSize .
                                                                       $recommendation->poster_path : null,
                );
            }
        }
        // cast
        if (isset($data->credits->cast) &&
            is_array($data->credits->cast) &&
            count($data->credits->cast) > 0
           )
        {
            foreach ($data->credits->cast as $cast) {
                $this->cast[] = array(
                    'id' => isset($cast->id) ? $cast->id : null,
                    'name' => isset($cast->name) ? $cast->name : null,
                    'originalName' => isset($cast->original_name) ? $cast->original_name : null,
                    'imgPath' => isset($cast->profile_path) ? $this->config->baseImageUrl . '/' .
                                                              $this->config->profileImageSize .
                                                              $cast->profile_path : null,
                    'character' => isset($cast->character) ? $cast->character : null,
                    'creditId' => isset($cast->credit_id) ? $cast->credit_id : null,
                    'order' => isset($cast->order) ? $cast->order : null
                );
            }
        }
        // crew
        if (isset($data->credits->crew) &&
            is_array($data->credits->crew) &&
            count($data->credits->crew) > 0
           )
        {
            foreach ($data->credits->crew as $crew) {
                $type = isset($crew->department) ? str_replace(' ', '', $crew->department) : 'Uncategorized';
                $this->crew[$type][] = array(
                    'id' => isset($crew->id) ? $crew->id : null,
                    'name' => isset($crew->name) ? $crew->name : null,
                    'originalName' => isset($crew->original_name) ? $crew->original_name : null,
                    'imgPath' => isset($crew->profile_path) ? $this->config->baseImageUrl . '/' .
                                                              $this->config->profileImageSize .
                                                              $crew->profile_path : null,
                    'creditId' => isset($crew->credit_id) ? $crew->credit_id : null,
                    'job' => isset($crew->job) ? $crew->job : null,
                );
            }
        }
        // networks
        if (isset($data->networks) &&
            is_array($data->networks) &&
            count($data->networks) > 0
           )
        {
            foreach ($data->networks as $network) {
                $this->networks[] = array(
                    'id' => isset($network->id) ?
                                  $network->id : null,
                    'name' => isset($network->name) ?
                                    $network->name : null,
                    'imgLogoPath' => isset($network->logo_path) ? $this->config->baseImageUrl . '/' .
                                                                  $this->config->logoImageSize .
                                                                  $network->logo_path : null
                );
            }
        }
        // Seasons, info about each season
        if (isset($data->seasons) &&
            is_array($data->seasons) &&
            count($data->seasons) > 0
           )
        {
            foreach ($data->seasons as $key => $seasonsObject) {
                $this->seasonsData[$key + 1] = array(
                    'id' => isset($seasonsObject->id) ? $seasonsObject->id : null,
                    'name' => isset($seasonsObject->name) ? $seasonsObject->name : null,
                    'overview' => isset($seasonsObject->overview) ? $seasonsObject->overview : null,
                    'airdate' => isset($seasonsObject->air_date) ? $seasonsObject->air_date : null,
                    'seasonNumber' => isset($seasonsObject->season_number) ? $seasonsObject->season_number : null,
                    'episodeCount' => isset($seasonsObject->episode_count) ? $seasonsObject->episode_count : null,
                    'voteAverage' => isset($seasonsObject->vote_average) ? $seasonsObject->vote_average : null,
                    'posterImgPath' => isset($seasonsObject->poster_path) ? $this->config->baseImageUrl . '/' .
                                                                            $this->config->posterImageSize .
                                                                            $seasonsObject->poster_path : null
                );
            }
        }
        // seasons and episodes
        if ($this->totalSeasons > 0) {
            $this->seasonsEpisodes = $this->seasons->fetchSeasonsEpisodes($this->tmdbID, $this->totalSeasons);
        }
        // Watch providers for this movie
        if (!empty($data->{"watch/providers"}->results)) {
            $watchProviderResults = (array) $data->{"watch/providers"}->results;
            foreach ($watchProviderResults as $country => $providerItems) {
                $buy = array();
                $rent = array();
                $flatrate = array();
                // buy
                if (isset($providerItems->buy) &&
                    is_array($providerItems->buy) &&
                    count($providerItems->buy) > 0
                   )
                {
                    foreach ($providerItems->buy as $provider) {
                        $buy[] = array(
                            'providerId' => isset($provider->provider_id) ? $provider->provider_id : null,
                            'providerName' => isset($provider->provider_name) ? $provider->provider_name : null,
                            'imgLogoPath' => isset($provider->logo_path) ? $this->config->baseImageUrl . '/' .
                                                                           $this->config->logoImageSize .
                                                                           $provider->logo_path : null
                        );
                    }
                }
                //rent
                if (isset($providerItems->rent) &&
                    is_array($providerItems->rent) &&
                    count($providerItems->rent) > 0
                   )
                {
                    foreach ($providerItems->rent as $providerRent) {
                        $rent[] = array(
                            'providerId' => isset($providerRent->provider_id) ? $providerRent->provider_id : null,
                            'providerName' => isset($providerRent->provider_name) ? $providerRent->provider_name : null,
                            'imgLogoPath' => isset($providerRent->logo_path) ? $this->config->baseImageUrl . '/' .
                                                                               $this->config->logoImageSize .
                                                                               $providerRent->logo_path : null
                        );
                    }
                }
                // flatrate (stream)
                if (isset($providerItems->flatrate) &&
                    is_array($providerItems->flatrate) &&
                    count($providerItems->flatrate) > 0
                   )
                {
                    foreach ($providerItems->flatrate as $providerFlatrate) {
                        $flatrate[] = array(
                            'providerId' => isset($providerFlatrate->provider_id) ? $providerFlatrate->provider_id : null,
                            'providerName' => isset($providerFlatrate->provider_name) ? $providerFlatrate->provider_name : null,
                            'imgLogoPath' => isset($providerFlatrate->logo_path) ? $this->config->baseImageUrl . '/' .
                                                                                   $this->config->logoImageSize .
                                                                                   $providerFlatrate->logo_path : null
                        );
                    }
                }
                $this->watchProviders[$country] = array(
                    'link' => isset($providerItems->link) ? $providerItems->link : null,
                    'buy' => $buy,
                    'rent' => $rent,
                    'flatrate' => $flatrate
                );
            }
        }
        // reviews
        if (isset($data->reviews->results) &&
            is_array($data->reviews->results) &&
            count($data->reviews->results) > 0
           )
        {
            foreach ($data->reviews->results as $reviewObject) {
                $this->reviews[] = array(
                    'id' => isset($reviewObject->id) ? $reviewObject->id : null,
                    'author' => isset($reviewObject->author) ? $reviewObject->author : null,
                    'content' => isset($reviewObject->content) ? $reviewObject->content : null
                );
            }
        }
        // content ratings
        if (isset($data->content_ratings->results) &&
            is_array($data->content_ratings->results) &&
            count($data->content_ratings->results) > 0
           )
        {
            foreach ($data->content_ratings->results as $contentRatingsObject) {
                $descriptionResults = array();
                if (isset($contentRatingsObject->descriptors) &&
                    is_array($contentRatingsObject->descriptors) &&
                    count($contentRatingsObject->descriptors) > 0
                   )
                {
                    foreach ($contentRatingsObject->descriptors as $description) {
                        if (!empty($description)) {
                            $descriptionResults[] = $description;
                        }
                    }
                }
                $this->contentRatings[] = array(
                    'iso3166' => isset($contentRatingsObject->iso_3166_1) ?
                                       $contentRatingsObject->iso_3166_1 : null,
                    'rating' => isset($contentRatingsObject->rating) ?
                                      $contentRatingsObject->rating : null,
                    'descriptors' => $descriptionResults
                );
            }
        }
        // results array
        $this->results = array(
            'id' => $this->tmdbId,
            'imdbId' => $this->imdbId,
            'wikidataId' => $this->wikidataId,
            'tvdbId' => $this->tvdbId,
            'name' => $this->name,
            'originalName' => $this->originalName,
            'overview' => $this->overview,
            'originalLanguage' => $this->originalLanguage,
            'firstAirDate' => $this->firstAirDate,
            'lastAirDate' => $this->lastAirDate,
            'totalSeasons' => $this->totalSeasons,
            'totalEpisodes' => $this->totalEpisodes,
            'tagline' => $this->tagline,
            'originCountry' => $this->originCountry,
            'createdBy' => $this->createdBy,
            'spokenLanguages' => $this->spokenLanguages,
            'genres' => $this->genres,
            'type' => $this->type,
            'status' => $this->status,
            'adult' => $this->adult,
            'inProduction' => $this->inProduction,
            'homepage' => $this->homepage,
            'popularity' => $this->popularity,
            'voteCount' => $this->voteCount,
            'voteAverage' => $this->voteAverage,
            'contentRatings' => $this->contentRatings,
            'posterImgPath' => $this->posterImgPath,
            'productionCompanies' => $this->productionCompanies,
            'productionCountries' => $this->productionCountries,
            'alternativeTitles' => $this->alternativeTitles,
            'images' => $this->images,
            'videos' => $this->videos,
            'keywords' => $this->keywords,
            'recommendations' => $this->recommendations,
            'cast' => $this->cast,
            'crew' => $this->crew,
            'networks' => $this->networks,
            'seasons' => $this->seasonsData,
            'seasonsEpisodes' => $this->seasonsEpisodes,
            'watchProviders' => $this->watchProviders,
            'reviews' => $this->reviews
        );
        return $this->results;
    }
}
