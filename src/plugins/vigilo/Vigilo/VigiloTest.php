<?php

class VigiloTest extends VigiloXml implements ArrayAccess
{
    protected $name;
    protected $args;

    public function __construct($name, array $args = array())
    {
        $this->name = $name;
        $this->args = array();

        foreach ($args as $arg => $value) {
            $this[$arg] = $value;
        }
    }

    public function offsetExists($offset)
    {
        $name = is_a($offset, 'VigiloArg') ? $offset->getName() : $offset;
        return isset($this->args[$name]);
    }

    public function offsetGet($offset)
    {
        $name = is_a($offset, 'VigiloArg') ? $offset->getName() : $offset;
        return isset($this->args[$name]) ? $this->args[$name] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_string($offset)) {
            $value = new VigiloArg($offset, $value);
        }

        if (!is_a($value, 'VigiloArg')) {
            throw new \RuntimeException();
        }

        return $this->args[$value->getName()] = $value;
    }

    public function offsetUnset($offset)
    {
        $name = is_a($offset, 'VigiloArg') ? $offset->getName() : $offset;
        unset($this->args[$name]);
    }

    public function __toString()
    {
        return self::sprintf(
            '<test name="%s">%s</test>',
            $this->name,
            $this->args
        );
    }
}
