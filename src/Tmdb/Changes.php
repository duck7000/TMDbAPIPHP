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
 * Get a list of all of the movie, people or tv ids that have been changed
 * @author ed (github user: duck7000)
 */
class Changes extends MdbBase
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
     * Fetch movie ids that have been changed in the past 24 hours
     * @return array
     */
    public function fetchMovieChanges()
    {
        return $this->fetchChanges("movie");
    }

    /**
     * Fetch tv ids that have been changed in the past 24 hours
     * @return array
     */
    public function fetchTvChanges()
    {
        return $this->fetchChanges("tv");
    }

    /**
     * Fetch person ids that have been changed in the past 24 hours
     * @return array
     */
    public function fetchPersonChanges()
    {
        return $this->fetchChanges("person");
    }

    /**
     * Fetch ids that have been changed
     * @param string $mediaType movie, tv, person
     * @return array
     */
    private function fetchChanges($mediaType)
    {
        $results = array();
        $resultData = $this->api->doChangesLookup($mediaType);
        if (empty($resultData) || empty((array) $resultData)) {
            return $this->movieResults;
        }
        foreach ($resultData as $data) {
            $results[] = array(
                'id' => isset($data->id) ? $data->id : null,
                'adult' => isset($data->adult) ? $data->adult : null
            );
        }
        return $results;
    }
}
