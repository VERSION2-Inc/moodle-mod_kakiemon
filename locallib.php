<?php
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/mod/kakiemon/class/autoload.php';

require_once $CFG->libdir . '/formslib.php';
MoodleQuickForm::registerElementType('kakiemonfile',
    $CFG->dirroot.'/mod/kakiemon/class/quickform/file.php', 'HTML_QuickForm_kakiemonfile');
