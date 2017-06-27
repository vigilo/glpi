<?php

class PluginVigiloHls extends VigiloXml
{
    protected $name;
    protected $hlsHost;
    protected $hlsServices;

    const MESSAGE = "%(state)s: %(active_deps)r/%(total_deps)r active deps";

    public function __construct(PluginVigiloAbstractMonitoredItem $host)
    {
        $name = $host->getName();

        // Création d'un HLS qui dépend de tous les services de l'hôte.
        $this->hlsServices  = new VigiloHlservice(
            "services:$name",
            VigiloHlservice::OPERATOR_AND,
            self::MESSAGE,
            1,  // Seuil warning
            0   // Seuil critical
        );
        $nbServices = 0;
        foreach ($host->getTests() as $test) {
            foreach ($test->getNagiosNames() as $service) {
                $this->hlsServices[] = new VigiloDepends($name, $service, 2, 1);
                $nbServices++;
            }
        }

        // Création d'un HLS qui dépend de l'hôte et du HLS précédent.
        $this->hlsHost      = new VigiloHlservice(
            "machine:$name",
            VigiloHlservice::OPERATOR_AND,
            self::MESSAGE,
            1,  // Seuil warning
            0   // Seuil critical
        );
        $this->hlsHost[]    = new VigiloDepends($name, null, 2);
        $this->hlsHost[]    = new VigiloDepends(null, "services:$name", 2, 1);
        $this->name         = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return "<" . "?xml version=\"1.0\"?" .
            "><hlservices>{$this->hlsServices}{$this->hlsHost}</hlservices>";
    }
}
