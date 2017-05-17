<?php

class VigiloHostTemplate extends VigiloXml
{
    protected $name;

    public function __construct($tpl)
    {
        $this->name = $tpl;
    }

    public function __toString()
    {
        return self::sprintf('<template>%s</template>', $this->name);
    }
}
