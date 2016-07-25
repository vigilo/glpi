<?php

class VigiloArg extends VigiloXml
{
    protected $name;
    protected $values;

    public function __construct($name, $values)
    {
        if (is_array($values)) {
            $new_values = array();
            foreach ($values as $value) {
                if (is_string($value)) {
                    $new_values[] = new VigiloItem($value);
                } elseif (!is_a($value, 'VigiloItem')) {
                    throw new \RuntimeException();
                } else {
                    $new_values[] = $value;
                }
            }
            $values = $new_values;
        } elseif (!is_string($values) && !is_int($values)
            && !is_bool($values) && !is_float($values)
        ) {
            throw new \RuntimeException();
        } else {
            $values = (string) $values;
        }

        $this->name = $name;
        $this->values = $values;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        if (is_string($this->values)) {
            return $this->values;
        }
        return array_map('getValue', $this->values);
    }

    public function __toString()
    {
        return self::sprintf(
            '<arg name="%s">%s</arg>',
            $this->name,
            $this->values
        );
    }
}
