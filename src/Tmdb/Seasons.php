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
 * Get all seasons and episodes of tv serie
 * @author ed (github user: duck7000)
 */
class Seasons extends MdbBase
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
     * Fetch seasons and episodes for Tv Class
     * @param int $id id from tv show
     * @param int $totalSeasons number of seasons
     * @return array
     */
    public function fetchSeasonsEpisodes($id, $totalSeasons)
    {
        $seasonResults = array();
        // Data request
        $seasonsData = $this->api->doTvSeasonsLookup($id, $totalSeasons);
            if (!empty($seasonsData) || !empty((array) $seasonsData)) {
                $seasonCounter = 1;
                while ($seasonCounter <= $totalSeasons) {
                    if (isset($seasonsData->{"season/$seasonCounter"}->episodes) &&
                        is_array($seasonsData->{"season/$seasonCounter"}->episodes) &&
                        count($seasonsData->{"season/$seasonCounter"}->episodes) > 0
                       )
                    {
                        foreach ($seasonsData->{"season/$seasonCounter"}->episodes as $episode) {
                            $seasonResults[$seasonCounter][$episode->episode_number] = array(
                                'id' => isset($episode->id) ?
                                              $episode->id : null,
                                'name' => isset($episode->name) ?
                                                $episode->name : null,
                                'airdate' => isset($episode->air_date) ?
                                                   $episode->air_date : null,
                                'overview' => isset($episode->overview) ?
                                                    $episode->overview : null,
                                'runtime' => isset($episode->runtime) ?
                                                   $episode->runtime : null,
                                'seasonNumber' => isset($episode->season_number) ?
                                                        $episode->season_number : null,
                                'episodeNumber' => isset($episode->episode_number) ?
                                                         $episode->episode_number : null,
                                'imgStillPath' => isset($episode->still_path) ? $this->config->baseImageUrl . '/' .
                                                                                $this->config->stillImageSize .
                                                                                $episode->still_path : null
                            );
                        }
                    }
                    $seasonCounter++;
                }
            }
        return $seasonResults;
    }
}
