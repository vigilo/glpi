<?php

include 'VigiloSoftwareList.php';

class VigiloTestSoftware
{
    protected $testTable;
    protected $softwareBase;
    protected $computer;
    protected $addedTests;

    public function __construct($computer)
    {
        $this->computer=$computer;
        $this->softwareBase = getSoftwareList($this->computer);
        $this->testTable=array();
        $this->addedTests=array();
    }

    public function getTable()
    {
        return $this->testTable;
    }

    public function addRelevantTestWith($softwareName)
    {
        $functionArray=$this->softwareBase[$softwareName];
        $this->testTable[]=call_user_func_array(array($this,$functionArray[0]), $functionArray[1]);
    }

    protected function addNTPTest()
    {
        //TODO : set up arguments
        $args=array();
        //$address=0;
        //$args[]=new VigiloArg('address',$address);
        $args[]=new VigiloArg('crit', 0);
        $args[]=new VigiloArg('warn', 0);
        return new VigiloTest('NTP', $args);
    }

    protected function addNTPqTest()
    {
        //TODO: set up arguments
        $args=array();
        $args[]=new VigiloArg('crit', 0);
        $args[]=new VigiloArg('warn', 0);
        return new VigiloTest('NTPq', $args);
    }

    protected function addNTPSyncTest()
    {
        return new VigiloTest('NTPSync');
    }

    protected function addHTTPTest()
    {
        return new VigiloTest('HTTP');
    }

    protected function addMemcachedTest($computer)
    {
        $args=array();
        $args[]=new VigiloArg('port', 11211);//TODO: set up arguments
        return new VigiloTest('Memcached', $args);
    }

    protected function addNagiosTest()
    {
        return new VigiloTest('Nagios');
    }

    protected function addPGSQLTest($computer)
    {
        //TODO: set up arguments
        //$args=array();
        //$args[]=new VigiloArg('database',NULL);
        //$args[]=new VigiloArg('port',NULL);
        //$args[]=new VigiloArg('user',NULL);
        //return new VigiloTest('PostgreSQLConnection',$args);
    }

    protected function addProxyTest()
    {
        //TODO: set up arguments
        //$args=array();
        /*$args[]=new VigiloArg('auth',NULL);
        $args[]=new VigiloArg('port',NULL);
        $args[]=new VigiloArg('url',NULL);*/
        //return new VigiloTest('Proxy',$args);
    }

    protected function addRRDcachedTest($computer)
    {
        //TODO: set up arguments
        //$path=
        //$args=array();
        //$args[]=new VigiloArg('crit',0);
        //$args[]=new VigiloArg('path',$path);
        //$args[]=new VigiloArg('warn',0);
        //return new VigiloTest('RRDcached',$args);
    }

    protected function addSSHTest()
    {
        return new VigiloTest('SSH');
    }

    protected function addVigiloConnectorTest($type)
    {
        $args=array();
        $args[]=new VigiloArg('type', $type);
        return new VigiloTest('VigiloConnector', $args);
    }

    protected function addVigiloCorrelatorTest()
    {
        //TODO :set up arguments
        //$args=array();
        //$args[]=new VigiloArg('rules',NULL);
        //$args[]=new VigiloArg('servicename',NULL);
        //return new VigiloTest('VigiloCorrelator',$args);
    }

    protected function TestService($computer)
    {
        //TODO: set up arguments
        //$args=array();
        //$args[]=new VigiloArg('svcname',NULL);
        //return new VigiloTest('Service',$args);
    }

    public function __toString()
    {
        return $this->child;
    }
}
