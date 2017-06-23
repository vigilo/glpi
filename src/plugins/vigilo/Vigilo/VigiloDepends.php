<?php

class VigiloDepends extends VigiloXml
{
    protected $host;
    protected $service;
    protected $weight;
    protected $warningWeight;

    public function __construct($host = null, $service = null, $weight = 1, $warningWeight = null)
    {
        /* GLPI signale parfois l'absence de valeur via des chaînes vides,
           plutôt qu'en utilisant la constante null. */
        if ('' === $host) {
            $host = null;
        }
        if ('' === $service) {
            $service = null;
        }

        if (null === $host && null === $service) {
            throw new Exception('Invalid dependency');
        }

        if ((null !== $host && !is_string($host)) ||
            (null !== $service && !is_string($service))) {
            throw new Exception('Invalid dependency');
        }

        if (null === $service && null !== $warningWeight) {
            // L'état "warning" n'existe pas pour les hôtes.
            throw new Exception('Invalid dependency');
        }

        if (null === $warningWeight) {
            $warningWeight = $weight;
        }

        $this->host             = $host;
        $this->service          = $service;
        $this->weight           = (int) $weight;
        $this->warningWeight    = (int) $warningWeight;
    }

    public function __toString()
    {
        if (null === $this->host) {
            return  "<depends service=\"{$this->service}\" " .
                    "weight=\"{$this->weight}\" " .
                    "warning_weight=\"{$this->warningWeight}\"/>";
        }

        if (null === $this->service) {
            return "<depends host=\"{$this->host}\" weight=\"{$this->weight}\"/>";
        }

        return  "<depends host=\"{$this->host}\" " .
                "service=\"{$this->service}\" " .
                "weight=\"{$this->weight}\" " .
                "warning_weight=\"{$this->warningWeight}\"/>";
    }
}
