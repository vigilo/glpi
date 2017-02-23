<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginVigiloComputer extends Computer {
    static function showComputerInfo($item) {
        global $CFG_GLPI;
        $templates = PluginVigiloVigiloTemplate::getAllTemplates();
        $value = array_search($item->getField('vigilo_template'), $templates);
        if (empty($value)) {
           $value = 0;
        }
        echo '<table class="tab_cadre_fixe tab_glpi" width="100%">';
        echo '<tr class="tab_bg_1"><th colspan="4">Vigilo Template</th></tr>';
        echo '<tr class="tab_bg_1">';
        echo '<td>Vigilo Template</td>';
        echo '<td>';
        Dropdown::showFromArray('vigilo_template', $templates, array('value' => $value));
        echo '</td></tr>';
        echo '</table>';
        return TRUE;
    }

    function getSearchOptions() {
        global $CFG_GLPI;

        $computer = new Computer();
        $options  = $computer->getSearchOptions();

        $options['vigilo']             = 'Vigilo Template';

        $options['7007']['table']      = 'glpi_computers';
        $options['7007']['field']      = 'vigilo_template';
        $options['7007']['name']       = 'vigilo_template';
        //$options['7007']['searchtype'] = 'equals';
        $options['7007']['datatype']   = 'dropdown';

        return $options;
    }
}
