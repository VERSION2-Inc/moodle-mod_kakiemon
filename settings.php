<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configexecutable('kakiemon/wkhtmltopdf',
        get_string('wkhtmltopdfpath', 'kakiemon'), get_string('wkhtmltopdfpathdesc', 'kakiemon'),
        '/usr/local/bin/wkhtmltopdf'));
    $settings->add(new admin_setting_configexecutable('kakiemon/wkhtmltoimage',
        get_string('wkhtmltoimagepath', 'kakiemon'), get_string('wkhtmltoimagepathdesc', 'kakiemon'),
        '/usr/local/bin/wkhtmltoimage'));
}
