<?php
function getSoftwareList($computer)
{
    return array('httpd'       =>array("addHTTPTest",array()), //http Apache server of CentOS
           'apache2'     =>array("addHTTPTest",array()), //http Apache server
           'aolserver-4' =>array("addHTTPTest",array()), //AOL web server
           'gunicorn'    =>array("addHTTPTest",array()), //Event-based HTTP/WSGI server
           'ebhttpd'     =>array("addHTTPTest",array()), //Specialized HTTP server to access CD-ROM books
           'lighttpd'    =>array("addHTTPTest",array()), //Fast webserver with minimal memory footprint
           'micro-httpd' =>array("addHTTPTest",array()), //Really small HTTP server
           'nghttp2'     =>array("addHTTPTest",array()), //nghttp HTTP 2.0 servers
           'nginx'       =>array("addHTTPTest",array()), //Small, powerful, scalable web/proxy server
           'webfs'       =>array("addHTTPTest",array()), //Lightweight HTTP server for static content
           'yaws'        =>array("addHTTPTest",array()), //High performance HTTP 1.1 webserver written by Erlang
           'elserv'      =>array("addHTTPTest",array()), //HTTP server that runs on Emacsen
           'thin'        =>array("addHTTPTest",array()), //Fast and very simple Ruby web server
           'webdis'      =>array("addHTTPTest",array()), //Web server providing an HTTP interface to Redis
           //'ntp'=>array("addNTPTest",array()), //Network Time Protocol deamon and utility programs

           'vigilo-connector-metro'     =>array("addVigiloConnectorTest",array("metro")), //Vigilo module receiving and stocking metrology data
           'vigilo-connector-nagios'    =>array("addVigiloConnectorTest",array("nagios")), //Vigilo module sharing data with Nagios
           'vigilo-connector-syncevent' =>array("addVigiloConnectorTest",array("syncevent")),
           'vigilo-connector-vigiconf'  =>array("addVigiloConnectorTest",array("vigiconf")),

           'nagios'   =>array("addNagiosTest",array()), //Host/service/network monitoring and management system for CentOS
           'nagios3'  =>array("addNagiosTest",array()), //Host/service/network monitoring and management system for Debian

           'memcached' =>array("addMemcachedTest",array($computer)), //High-performance memory object caching system
           'yrmcds'    =>array("addMemcachedTest",array($computer)), //Memory object caching system with master/slave replication and server-side locking.

           'openssh-server'    =>array("addSSHTest",array()), //Secure shell (SSH) server, for secure access from remote machines
           'ssh'               =>array("addSSHTest",array()), //Secure shell client and server (metapackage)

           'mariadb-server'  =>array("addPGSQLTest",array($computer)), //MariaDB database server
           'mysql-server'    =>array("addPGSQLTest",array($computer)), //MySQL database server

           'micro-proxy'   =>array("addProxyTest",array($computer)), //Really small HTTP/HTTPS proxy
           'ntlmaps'       =>array("addProxyTest",array($computer)), //NTLM Authorization Proxy Server
           'privoxy'       =>array("addProxyTest",array($computer)), //Privacy enhancing HTTP Proxy
           'squid3'        =>array("addProxyTest",array($computer)), //Full featured Web Proxy cache (HTTP proxy)
           'tinyproxy'     =>array("addProxyTest",array($computer)), //A lightweight, non-caching, optionally anonymizing HTTP proxy

           'rrdcached'     =>array("addRRDcachedTest",array($computer))); //Data caching daemon for RRDtool
}
