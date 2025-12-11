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
 * Person lists on TMDb API
 * @author ed (github user: duck7000)
 */
class PersonList extends MdbBase
{

    protected $popularResults = array();

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
     * Fetch popular persons
     * @return array
     */
    public function popular()
    {
        // Data request
        $resultData = $this->api->doListLookup("person", "popular", 25);
        if (empty($resultData)) {
            return $this->popularResults;
        }
        foreach ($resultData as $data) {
            // known for
            $knownFor = array();
            if (isset($data->known_for) &&
                is_array($data->known_for) &&
                count($data->known_for) > 0
               )
            {
                foreach ($data->known_for as $knownForObject) {
                    $knownFor[] = array(
                        'id' => isset($knownForObject->id) ? $knownForObject->id : null,
                        'name' => isset($knownForObject->name) ? $knownForObject->name : null
                    );
                }
            }
            // results array
            $this->popularResults[] = array(
                'id' => isset($data->id) ? $data->id : null,
                'name' => isset($data->name) ? $data->name : null,
                'originalName' => isset($data->original_name) ? $data->original_name : null,
                'popularity' => isset($data->popularity) ? $data->popularity : null,
                'knownFor' => $knownFor,
                'profileImgPath' => isset($data->profile_path) ? $this->config->baseImageUrl . '/' .
                                                               $this->config->profileImageSize .
                                                               $data->profile_path : null
            );
        }
        return $this->popularResults;
    }
}
