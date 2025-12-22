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
 * Get Company info based on company ID
 * @author ed (github user: duck7000)
 */
class Company extends MdbBase
{

    protected $companyResults = array();

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
     * Fetch company info based on a company Id found by Search class
     * @param int $tmdbId input TMDb company ID
     * @return array
     */
    public function fetchCompany($tmdbId)
    {
        // Data request
        $resultData = $this->api->doTypeLookup($tmdbId, "company");
        if (empty($resultData) || empty((array) $resultData)) {
            return $this->companyResults;
        }
        $this->companyResults = array(
            'id' => isset($resultData->id) ? $resultData->id : null,
            'name' => isset($resultData->name) ? $resultData->name : null,
            'description' => isset($resultData->description) ? $resultData->description : null,
            'headquarters' => isset($resultData->headquarters) ? $resultData->headquarters : null,
            'homepage' => isset($resultData->homepage) ? $resultData->homepage : null,
            'originCountry' => isset($resultData->origin_country) ? $resultData->origin_country : null,
            'parentCompany' => isset($resultData->parent_company) ? $resultData->parent_company : null,
            'logoImgUrl' => isset($resultData->logo_path) ? $this->config->baseImageUrl . '/' .
                                                            $this->config->logoImageSize .
                                                            $resultData->logo_path : null
        );
        return $this->companyResults;
    }
}
