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
 * A title on TMDb API
 * @author ed (github user: duck7000)
 */
class MdbBase extends Config
{
    public $version = '0.1.1';

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var GraphQL
     */
    protected $api;

    /**
     * @var string identifier for this person or title
     */
    protected $tmdbID;

    /**
     * @param Config $config OPTIONAL override default config
     * @param LoggerInterface $logger OPTIONAL override default logger `\Imdb\Logger` with a custom one
     * @param CacheInterface $cache OPTIONAL override the default cache with any PSR-16 cache.
     */
    public function __construct(?Config $config = null, ?LoggerInterface $logger = null, ?CacheInterface $cache = null)
    {
        $this->config = $config ?: $this;
        $this->logger = empty($logger) ? new Logger($this->debug) : $logger;
        $this->cache = empty($cache) ? new Cache($this->config, $this->logger) : $cache;
        $this->api = new Api($this->cache, $this->logger, $this->config);
    }

    /**
     * Retrieve the TMDb ID
     * @return string id TMDbID currently used
     */
    public function tmdbid()
    {
        return $this->tmdbID;
    }

    /**
     * Set and validate the TMDb ID
     * @param string id TMDb ID
     */
    protected function setid($id)
    {
        if (is_numeric($id)) {
            $this->tmdbID = $id;
        } else {
            $this->debug_scalar("<BR>setid: Invalid TMDB ID '$id'!<BR>");
        }
    }

    #---------------------------------------------------------[ Debug helpers ]---
    protected function debug_scalar($scalar)
    {
        $this->logger->error($scalar);
    }

    protected function debug_object($object)
    {
        $this->logger->error('{object}', array('object' => $object));
    }

    protected function debug_html($html)
    {
        $this->logger->error(htmlentities($html));
    }
}
