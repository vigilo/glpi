<?php

class VigiloDepends extends VigiloXml
{
    protected $host;
    protected $service;

    public function __construct($host = null, $service = null)
    {
        if (null === $host && null === $service) {
            throw new Exception('Invalid dependency');
        }

        if ('' === $host || '' === $service || !is_string($host) || !is_string($service)) {
            throw new Exception('Invalid dependency');
        }

        $this->host     = $host;
        $this->service  = $service;
    }

    public function __toString()
    {
        if (null === $this->host) {
            return "<depends service=\"{$this->service}\"/>";
        }

        if (null === $this->service) {
            return "<depends host=\"{$this->host}\"/>";
        }

        return "<depends host=\"{$this->host}\" service=\"{$this->service}\"/>";
    }
}
