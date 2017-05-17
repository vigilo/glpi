<?php

function getSoftwareList($computer)
{
    return array(
        /**
         * Serveurs web
         */
        // Apache HTTP Server (RHEL/CentOS)
        'httpd'         => array("addHTTPTest", array()),

        // Apache HTTP Server (Debian)
        'apache2'       => array("addHTTPTest", array()),

        // AOL web server
        'aolserver-4'   => array("addHTTPTest", array()),

        // Event-based HTTP/WSGI server
        'gunicorn'      => array("addHTTPTest", array()),

        // Specialized HTTP server to access CD-ROM books
        'ebhttpd'       => array("addHTTPTest", array()),

        // Fast webserver with minimal memory footprint
        'lighttpd'      => array("addHTTPTest", array()),

        // Really small HTTP server
        'micro-httpd'   => array("addHTTPTest", array()),

        // nghttp HTTP 2.0 servers
        'nghttp2'       => array("addHTTPTest", array()),

        // Small, powerful, scalable web/proxy server
        'nginx'         => array("addHTTPTest", array()),

        // Lightweight HTTP server for static content
        'webfs'         => array("addHTTPTest", array()),

        // High performance HTTP 1.1 webserver written by Erlang
        'yaws'          => array("addHTTPTest", array()),

        // HTTP server that runs on Emacsen
        'elserv'        => array("addHTTPTest", array()),

        // Fast and very simple Ruby web server
        'thin'          => array("addHTTPTest", array()),

        // Web server providing an HTTP interface to Redis
        'webdis'        => array("addHTTPTest", array()),


        //'ntp'=> array("addNTPTest", array()), //Network Time Protocol deamon and utility programs


        /**
         * Connecteurs de Vigilo
         */
        // Vigilo module receiving and stocking metrology data
        'vigilo-connector-metro'        => array("addVigiloConnectorTest", array("metro")),

        // Vigilo module sharing data with Nagios
        'vigilo-connector-nagios'       => array("addVigiloConnectorTest", array("nagios")),

        'vigilo-connector-syncevent'    => array("addVigiloConnectorTest", array("syncevent")),

        'vigilo-connector-vigiconf'     => array("addVigiloConnectorTest", array("vigiconf")),


        /**
         * Nagios
         */
        // Host/service/network monitoring and management system (RHEL/CentOS)
        'nagios'    => array("addNagiosTest", array()),

        // Host/service/network monitoring and management system (Debian)
        'nagios3'   => array("addNagiosTest", array()),


        /**
         * Caches mémoires
         */
        // High-performance memory object caching system
        'memcached'  => array("addMemcachedTest", array($computer)),

        //Memory object caching system with master/slave replication and server-side locking
        'yrmcds'     => array("addMemcachedTest", array($computer)),

        //Data caching daemon for RRDtool
        'rrdcached'  => array("addRRDcachedTest", array($computer)),


        /**
         * Serveurs SSH
         */
        // Secure shell (SSH) server, for secure access from remote machines
        'openssh-server'    => array("addSSHTest", array()),

        // Secure shell client and server (metapackage)
        'ssh'               => array("addSSHTest", array()),


        /**
         * Bases de données
         */
        // MariaDB database server
        'mariadb-server'    => array("addPGSQLTest", array($computer)),

        // MySQL database server
        'mysql-server'      => array("addPGSQLTest", array($computer)),


        /**
         * Serveurs mandataires
         */
        // Really small HTTP/HTTPS proxy
        'micro-proxy'   => array("addProxyTest", array($computer)),

        // NTLM Authorization Proxy Server
        'ntlmaps'       => array("addProxyTest", array($computer)),

        // Privacy enhancing HTTP Proxy
        'privoxy'       => array("addProxyTest", array($computer)),

        // Full featured Web Proxy cache (HTTP proxy)
        'squid3'        => array("addProxyTest", array($computer)),

        // A lightweight, non-caching, optionally anonymizing HTTP proxy
        'tinyproxy'     => array("addProxyTest", array($computer)),
    );
}
