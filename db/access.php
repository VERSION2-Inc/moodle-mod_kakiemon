<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
		'mod/kakiemon:addinstance' => array(
				'riskbitmask' => RISK_XSS,

				'captype' => 'write',
				'contextlevel' => CONTEXT_COURSE,
				'archetypes' => array(
						'editingteacher' => CAP_ALLOW,
						'manager' => CAP_ALLOW
				),
				'clonepermissionsfrom' => 'moodle/course:manageactivities'
		),

		'mod/kakiemon:view' => array(
				'captype' => 'read',
				'contextlevel' => CONTEXT_MODULE,
				'archetypes' => array(
						'guest' => CAP_ALLOW,
						'user' => CAP_ALLOW
				)
		)
);
