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
     * Fetch movie ids that have been changed
     *      * @param int $days number of days in the past to return, max 14 days
     * @note if parameter $days is omitted 1 day is returned
     * @return array
     */
    public function fetchMovieChanges($days = 1)
    {
        return $this->fetchChanges("movie", $days);
    }

    /**
     * Fetch tv ids that have been changed
     * @param int $days number of days in the past to return, max 14 days
     * @note if parameter $days is omitted 1 day is returned
     * @return array
     */
    public function fetchTvChanges($days = 1)
    {
        return $this->fetchChanges("tv", $days);
    }

    /**
     * Fetch person ids that have been changed
     * @param int $days number of days in the past to return, max 14 days
     * @note if parameter $days is omitted 1 day is returned
     * @return array
     */
    public function fetchPersonChanges($days = 1)
    {
        return $this->fetchChanges("person", $days);
    }

    /**
     * Fetch ids that have been changed
     * @param string $mediaType movie, tv, person
     * @param int $days number of days in the past to return, max 14 days
     * @note if parameter $days is omitted 1 day is returned
     * @return array
     */
    private function fetchChanges($mediaType, $days)
    {
        $results = array();
        $resultData = $this->api->doChangesLookup($mediaType, $days);
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
