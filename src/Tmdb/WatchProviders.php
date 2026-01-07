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
 * Get watch providers for specific movie or tv serie
 * It is possible to use this class on its own if you provide id and type
 * @author ed (github user: duck7000)
 */
class WatchProviders extends MdbBase
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
     * Fetch watch providers for Tv or Movie Class
     * @param int $id id from movie or tv show
     * @param string $type "tv" or "movie" is allowed
     * @return array
     */
    public function fetchWatchProviders($id, $type)
    {
        $watchProvidersResults = array();
        // Data request
        $watchProvidersData = $this->api->doWatchProviderLookup($id, $type);
        if (empty($watchProvidersData) || empty((array) $watchProvidersData)) {
            return $watchProvidersResults;
        }
        $watchProviderResults = (array) $watchProvidersData->results;
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
            $watchProvidersResults[$country] = array(
                'link' => isset($providerItems->link) ? $providerItems->link : null,
                'buy' => $buy,
                'rent' => $rent,
                'flatrate' => $flatrate
            );
        }
        return $watchProvidersResults;
    }
}
