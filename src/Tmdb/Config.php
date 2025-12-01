<?php

#############################################################################
# TMDbAPIPHP                                    ed (github user: duck7000)  #
# written by ed (github user: duck7000)                                     #
# ------------------------------------------------------------------------- #
# This program is free software; you can redistribute and/or modify it      #
# under the terms of the GNU General Public License (see doc/LICENSE)       #
#############################################################################

namespace Tmdb;

/**
 * Configuration class for musicBrainzPHP
 * @author ed (github user: duck7000)
 */
class Config
{

    #========================================================[ Cache options]===
    /**
     * Directory to store cached pages. This must be writable by the web
     * server. It doesn't need to be under documentroot.
     * @var string
     */
    public $cacheDir = './cache/';

    /**
     * Use cached pages if available?
     * @var boolean
     */
    public $cacheUse = false;

    /**
     * Store the pages retrieved for later use?
     * @var boolean
     */
    public $cacheStore = false;

    /**
     * Use zip compression for caching the retrieved html-files?
     * @see $converttozip if you're changing from false to true
     * @var boolean
     */
    public $cacheUseZip = false;

    /**
     * Convert non-zip cache-files to zip
     * You might want to use this if you weren't gzipping your cache files, but now are. They will be rewritten when they're used
     * @var boolean
     */
    public $cacheConvertZip = false;

    /**
     * Cache expiration time - cached pages older than this value (in seconds) will
     * be automatically deleted.
     * If 0 cached pages will never expire
     * @var integer
     */
    public $cacheExpire = 604800;
    
    /**
     * TMDb API base url
     * @var string
     */
    public $apiUrl = 'https://api.themoviedb.org';

    /**
     * TMDb API version
     * @var string
     */
    public $apiVersion = '3';

    /**
     * TMDb API key (not bearer token!)
     * @var string
     */
    public $apiKey = '';

    /**
     * TMDb API base image url
     * @var string
     */
    public $baseImageUrl = 'https://image.tmdb.org/t/p';

    /**
     * TMDb API poster image size, default: w185
     * Possible values:
     *      original (orginal size, can be huge!)
     *      w92, w154, w185, w342, w500, w780 (w = width)
     * @var string
     */
    public $posterImageSize = 'w185';

    /**
     * TMDb API person profile image size, default: w185
     * Possible values:
     *      original (orginal size, can be huge!)
     *      w45, w185, h632 (w = width, h= height)
     * @var string
     */
    public $profileImageSize = 'w185';

    /**
     * TMDb API company logo image size, default: w92
     * Possible values:
     *      original (orginal size, can be huge!)
     *      w45, w92, w154, w185, w300, w500 (w = width)
     * @var string
     */
    public $logoImageSize = 'w92';

    /**
     * TMDb API backdrop image size, default: w300
     * Possible values:
     *      original (orginal size, can be huge!)
     *      w300, w780, w1280 (w = width)
     * @var string
     */
    public $backdropImageSize = 'w300';

    /**
     * TMDb API still image size, default: w185
     * Possible values:
     *      original (orginal size, can be huge!)
     *      w92, w185, w300 (w = width)
     * @var string
     */
    public $stillImageSize = 'w185';

    // Debug config
    /**
     * Debug mode true or false
     * @var boolean
     */
    public $debug = false;

    /**
     * Throw Exception if something goes wrong with the api call
     * True: throws Exception, false: returns empty object
     * @var boolean
     */
    public $throwHttpExceptions = false;

}
