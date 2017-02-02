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
	if (strstr($softwareName, "vigilo-test"))
	{
	    $functionArray=array("addCustomTest", array($softwareName));
	}
	else 
	{
            $functionArray=$this->softwareBase[$softwareName];
	}
        $this->testTable[]=call_user_func_array(array($this,$functionArray[0]), $functionArray[1]);
    }

    protected function addCustomTest($softwareName)
    {
        $software_name = str_replace('vigilo-test-', '', $softwareName);
        $explode_software_name = explode('-', $software_name, 2);
        $args=array();
        switch($explode_software_name[0])
        {
            case "process": $args[]=new VigiloArg('processname', $explode_software_name[1]); break;
            case "service": $args[]=new VigiloArg('svcname', $explode_software_name[1]); break;
            default: return;
        }

        return new VigiloTest(ucfirst($explode_software_name[0]), $args);
    }

    protected function addNTPTest()
    {
	$args=array();
        //$address=0;
        //$args[]=new VigiloArg('address',$address);
        $args[]=new VigiloArg('crit', 0);
        $args[]=new VigiloArg('warn', 0);
        return new VigiloTest('NTP', $args);
    }

    protected function addNTPqTest()
    {
        $args=array();
        $args[]=new VigiloArg('crit', 2000);
        $args[]=new VigiloArg('warn', 5000);
        return new VigiloTest('NTPq', $args);
    }

    protected function addNTPSyncTest() // OK
    {
        return new VigiloTest('NTPSync');
    }

    protected function addHTTPTest() // OK
    {
        return new VigiloTest('HTTP');
    }

    protected function addMemcachedTest($computer)
    {
        $args=array();
        $args[]=new VigiloArg('port', 11211);
        return new VigiloTest('Memcached', $args);
    }

    protected function addNagiosTest()
    {
        return new VigiloTest('Nagios');
    }

    protected function addPGSQLTest($computer)
    {
        $args=array();
        $args[]=new VigiloArg('database',"postgres");
        $args[]=new VigiloArg('port',5432);
        $args[]=new VigiloArg('user',"postgres");
        return new VigiloTest('PostgreSQLConnection',$args);
    }

    protected function addProxyTest()
    {
        $args=array();
        $args[]=new VigiloArg('auth',"False");
        $args[]=new VigiloArg('port',8080);
        $args[]=new VigiloArg('url',"http://www.google.fr");
        return new VigiloTest('Proxy',$args);
    }

    protected function addRRDcachedTest($computer)
    {
        $path="/var/lib/vigilo/connector-metro/rrdcached.sock";
        $args=array();
        $args[]=new VigiloArg('crit',0);
        $args[]=new VigiloArg('path',$path);
        $args[]=new VigiloArg('warn',0);
        return new VigiloTest('RRDcached',$args);
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
        $args=array();
        //$args[]=new VigiloArg('rules','');
        $args[]=new VigiloArg('servicename','vigilo-correlator');
        return new VigiloTest('VigiloCorrelator',$args);
    }

    protected function addTestService($computer, $service)
    {
        $args=array();
        $args[]=new VigiloArg('svcname',$service);
        return new VigiloTest('Service',$args);
    }

    public function __toString()
    {
        return $this->child;
    }
}
