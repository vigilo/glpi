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

    public function getNagiosNames()
    {
        $res = array();
        switch ($this->name) {
            case 'Interface':
                // Pour une interface réseau, le service est construit
                // à partir du label donné.
                $label = $this->args['label']->getValue();
                $res[] = "Interface $label";

                // On regarde si des seuils ont été définis ou non.
                // Si c'est le cas, cela génère des services supplémentaires.
                $warn   = isset($this->args['warn']) ? $this->args['warn'] : array();
                $crit   = isset($this->args['crit']) ? $this->args['crit'] : array();
                $tests  = array(
                    "Traffic in",
                    "Traffic out",
                    "Discards in",
                    "Discards out",
                    "Errors in",
                    "Errors out",
                );
                if (is_array($warn) && is_array($crit)) {
                    foreach ($tests as $i => $test) {
                        if (isset($warn[$i], $crit[$i])) {
                            $res[] = "$test $label";
                        }
                    }
                }
                break;

            case 'Partition':
                // Le label est utilisé pour construire le nom du service.
                $label = $this->args['label']->getValue();
                $res[] = "Partition $label";
                break;

            case 'Ping':
                $res[] = 'Ping';
                break;

            case 'Process':
                $label = $this['label'];
                if (null === $label) {
                    $label = $this->args['processname'];
                }
                $label = $label->getValue();
                $res[] = "Process $label";
                break;

            case 'Service':
                $label = $this['label'];
                if (null === $label) {
                    $label = $this->args['svcname'];
                }
                $res[] = $label->getValue();
                break;

            case 'TCP':
                $label = $this['label'];
                if (null === $label) {
                    $label = 'TCP ' . $this->args['port']->getValue();
                } else {
                    $label = $label->getValue();
                }
                $res[] = $label;
                break;
        }

        return $res;
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
