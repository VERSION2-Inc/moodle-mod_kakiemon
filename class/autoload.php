<?php
defined('MOODLE_INTERNAL') || die();

function mod_kakiemon_autoload($classname) {
    global $CFG;

    if (strpos($classname, 'ver2\\kakiemon') === 0) {
        $classname = preg_replace('/^ver2\\\\kakiemon\\\\/', '', $classname);

        $classdir = $CFG->dirroot . '/mod/kakiemon/class/';
        $path = $classdir . str_replace('\\', DIRECTORY_SEPARATOR, $classname) . '.php';
        if (is_readable($path)) {
            require $path;
        }
    }
}

spl_autoload_register('mod_kakiemon_autoload');
