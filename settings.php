<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';

    $settings->add(new admin_setting_configexecutable('kakiemon_wkhtmltopdf', 'wkhtmltopdf へのpath',
        'wkhtmltopdf', '/usr/local/bin/wkhtmltopdf'));
    $settings->add(new admin_setting_configexecutable('kakiemon_wkhtmltoimage', 'wkhtmltoimage へのpath',
        'wkhtmltopdf', '/usr/local/bin/wkhtmltoimage'));
}
