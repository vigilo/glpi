<?php

class VigiloTest extends VigiloXml
{
    protected $name;
    protected $args;

    public function __construct($name, array $args = array())
    {
        $new_args = array();
        foreach ($args as $arg) {
            if (!is_a($arg, 'VigiloArg')) {
                    throw new \RuntimeException();
            }
            $new_args[$arg->getName()] = $arg;
        }

        $this->name = $name;
        $this->args = $new_args;
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
