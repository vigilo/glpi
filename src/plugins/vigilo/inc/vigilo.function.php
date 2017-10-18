<?php

function plugin_vigilo_getSoftwareMapping()
{
    return array(
        /**
         * Serveurs web
         */
        // Apache HTTP Server (RHEL/CentOS)
        'httpd'         => array("HTTP"),

        // Apache HTTP Server (Debian)
        'apache2'       => array("HTTP"),

        // AOL web server
        'aolserver-4'   => array("HTTP"),

        // Event-based HTTP/WSGI server
        'gunicorn'      => array("HTTP"),

        // Specialized HTTP server to access CD-ROM books
        'ebhttpd'       => array("HTTP"),

        // Fast webserver with minimal memory footprint
        'lighttpd'      => array("HTTP"),

        // Really small HTTP server
        'micro-httpd'   => array("HTTP"),

        // nghttp HTTP 2.0 servers
        'nghttp2'       => array("HTTP"),

        // Small, powerful, scalable web/proxy server
        'nginx'         => array("HTTP"),

        // Lightweight HTTP server for static content
        'webfs'         => array("HTTP"),

        // High performance HTTP 1.1 webserver written by Erlang
        'yaws'          => array("HTTP"),

        // HTTP server that runs on Emacsen
        'elserv'        => array("HTTP"),

        // Fast and very simple Ruby web server
        'thin'          => array("HTTP"),

        // Web server providing an HTTP interface to Redis
        'webdis'        => array("HTTP"),


        /**
         * Connecteurs de Vigilo
         */
        'vigilo-connector-metro'        => array("VigiloConnector", array("type" => "metro")),
        'vigilo-connector-nagios'       => array("VigiloConnector", array("type" => "nagios")),
        'vigilo-connector-syncevents'   => array("VigiloConnector", array("type" => "syncevents")),
        'vigilo-connector-vigiconf'     => array("VigiloConnector", array("type" => "vigiconf")),


        /**
         * Nagios
         */
        // Host/service/network monitoring and management system (RHEL/CentOS)
        'nagios'    => array("Nagios"),

        // Host/service/network monitoring and management system (Debian)
        'nagios3'   => array("Nagios"),


        /**
         * Caches mémoires
         */
        // High-performance memory object caching system
        'memcached'  => array("Memcached", array("port" => 11211)),

        // Data caching daemon for RRDtool
        'rrdcached'  => array(
            "RRDcached",
            array(
                'warn' => 0,
                'crit' => 0,
                'path' => '/var/lib/vigilo/connector-metro/rrdcached.sock',
            )
        ),


        /**
         * Serveurs SSH
         */
        // Secure shell (SSH) server, for secure access from remote machines
        'openssh-server'    => array("SSH"),

        // Secure shell client and server (metapackage)
        'ssh'               => array("SSH"),


        /**
         * Bases de données
         */
        // PostgreSQL
        // FIXME : on devrait utiliser un test plus ciblé
        'postgresql-server' => array('TCP', array('port' => 5432, 'label' => 'PostgreSQL')),

        // MariaDB
        'mariadb-server'    => array('TCP', array('port' => 3306, 'label' => 'MariaDB')),

        // MySQL
        'mysql-server'      => array('TCP', array('port' => 3306, 'label' => 'MySQL')),

        // Oracle SQL
        'oracle'            => array('TCP', array('port' => 1521, 'label' => 'Oracle SQL')),

        // MSSQL (aka. "SQL Server")
        'mssql'             => array('TCP', array('port' => 1433, 'label' => 'SQL Server')),

        // MSSQL (aka. "SQL Server")
        'sql server'        => array('TCP', array('port' => 1433, 'label' => 'SQL Server')),


        /**
         * Serveurs mandataires
         */
        // Really small HTTP/HTTPS proxy
        'micro-proxy'   => array("Proxy"),

        // NTLM Authorization Proxy Server
        'ntlmaps'       => array("Proxy"),

        // Privacy enhancing HTTP Proxy
        'privoxy'       => array("Proxy"),

        // Full featured Web Proxy cache (HTTP proxy)
        'squid3'        => array("Proxy"),

        // A lightweight, non-caching, optionally anonymizing HTTP proxy
        'tinyproxy'     => array("Proxy"),
    );
}

function plugin_vigilo_MassiveActionsFieldsDisplay($params)
{
    global $CFG_GLPI;

    $opts = array(
        "name" => "vigilo_template",
        "value" => 0,
        "url" => $CFG_GLPI["root_doc"] . "/plugins/vigilo/ajax/getTemplates.php"
    );

    Dropdown::show('PluginVigiloTemplate', $opts);
    return true;
}
