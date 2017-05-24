<?php

class VigiloHlservice extends VigiloXml implements ArrayAccess
{
    protected $name;
    protected $message;
    protected $priorities;
    protected $dependencies;

    const OPERATOR_AND  = "and";
    const OPERATOR_OR   = "or";
    const OPERATOR_PLUS = "plus";

    const STATE_WARNING     = 1;
    const STATE_CRITICAL    = 2;
    const STATE_UNKNOWN     = 3;

    public function __construct($name, $operator, $message, $warnThreshold = 0, $critThreshold = 0)
    {
        if (!in_array($operator, array(self::OP_AND, self::OP_OR, self::OP_PLUS))) {
            throw new Exception('Invalid operator');
        }

        $this->name             = $name;
        $this->message          = $message;
        $this->operator         = $operator;
        $this->warnThreshold    = (int) $warnThreshold;
        $this->critThreshold    = (int) $critThreshold;
        $this->priorities       = array();
        $this->dependencies     = array();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPriority($state, $priority)
    {
        if (!in_array($priority, array(self::STATE_WARNING, self::STATE_CRITICAL, self::STATE_UNKNOWN))) {
            throw new Exception('Invalid state');
        }

        $this->priorities[$state] = (int) $priority;
    }

    public function setWarningThreshold($threshold)
    {
        $this->warnThreshold = (int) $threshold;
    }

    public function setCriticalThreshold($threshold)
    {
        $this->critThreshold = (int) $threshold;
    }

    public function offsetExists($offset)
    {
        return isset($this->dependencies[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->dependencies[$offset]) ? $this->dependencies[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (!is_object($value) || !($value instanceof VigiloDepends)) {
            throw new \RuntimeException("Invalid dependency");
        }
        $this->dependencies[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->dependencies[$offset]);
    }

    public function __toString()
    {
        $priorities = array();
        $map        = array(
            "unknown"   => self::STATE_UNKNOWN,
            "warning"   => self::STATE_WARNING,
            "critical"  => self::STATE_CRITICAL,
        );

        foreach ($map as $name => $id) {
            if (isset($this->priorities[$id])) {
                $value          = $this->priorities[$id];
                $priorities[]   = "<${name}_priority>$value</${name}_priority>";
            }
        }

        $xml = <<<HLS
<hlservice>
    <message>%s</message>
    <warning_threshold>%d</warning_threshold>
    <critical_threshold>%d</critical_threshold>
    %s
    <operator>%s</operator>
    %s
</hlservice>
HLS;
        return self::sprintf(
            $xml,
            $this->message,
            $this->warnThreshold,
            $this->critThreshold,
            $priorities,
            $this->operator,
            $this->dependencies
        );
    }
}
