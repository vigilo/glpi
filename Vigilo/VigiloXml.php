<?php

abstract class VigiloXml
{
    abstract public function __toString();

    public static function sprintf($s)
    {
        $args = func_get_args();
        array_shift($args); // pop $s

        $new_args = array();
        foreach ($args as $arg) {
            if (is_string($arg)) {
                $new_args[] = htmlspecialchars($arg, ENT_XML1 | ENT_QUOTES, "utf-8");
            } else if (is_array($arg)) {
                $acc = '';
                foreach ($arg as $sub) {
                    if (is_object($sub))
                        $acc .= (string) $sub;
                }
                $new_args[] = $acc;
            } else {
                $new_args[] = (string) $arg;
            }
        }

        return vsprintf($s, $new_args);
    }
}

