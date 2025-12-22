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
 * Get Collection content based on collection ID found by Search class
 * @author ed (github user: duck7000)
 */
class Collection extends MdbBase
{

    protected $collectionResults = array();

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
     * Fetch collections based on a collection Id found by Search class
     * @param int $tmdbId input TMDb collection ID
     * @return array
     */
    public function fetchCollection($tmdbId)
    {
        // Data request
        $resultData = $this->api->doTypeLookup($tmdbId, "collection");
        if (empty($resultData) || empty((array) $resultData)) {
            return $this->collectionResults;
        }
        // collection parts
        $parts = array();
        if (isset($resultData->parts) &&
            is_array($resultData->parts) &&
            count($resultData->parts) > 0
           )
        {
            foreach ($resultData->parts as $item) {
                $parts[] = array(
                    'id' => isset($item->id) ? $item->id : null,
                    'title' => isset($item->title) ? $item->title : null,
                    'originalTitle' => isset($item->original_title) ? $item->original_title : null,
                    'overview' => isset($item->overview) ? $item->overview : null,
                    'imgUrl' => isset($item->poster_path) ? $this->config->baseImageUrl . '/' .
                                                            $this->config->posterImageSize .
                                                            $item->poster_path : null,
                    'releaseDate' => isset($item->release_date) ? $item->release_date : null,
                    'mediaType' => isset($item->media_type) ? $item->media_type : null
                );
            }
        }
        $this->collectionResults = array(
            'collectionId' => isset($resultData->id) ? $resultData->id : null,
            'collectionName' => isset($resultData->name) ? $resultData->name : null,
            'collectionOverview' => isset($resultData->overview) ? $resultData->overview : null,
            'collectionImgUrl' => isset($resultData->poster_path) ? $this->config->baseImageUrl . '/' .
                                                                    $this->config->posterImageSize .
                                                                    $resultData->poster_path : null,
            'collectionParts' => $parts
        );
        return $this->collectionResults;
    }
}
