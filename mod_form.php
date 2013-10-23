<?php
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/course/moodleform_mod.php';

class mod_kakiemon_mod_form extends moodleform_mod {
	protected function definition() {
		$f = $this->_form;

		$f->addElement('header', 'general', get_string('general', 'form'));

		$f->addElement('text', 'name', get_string('kakiemonname', 'kakiemon'), array('size' => 64));
		$f->setType('name', PARAM_TEXT);
		$f->addRule('name', null, 'required', null, 'client');

		$this->add_intro_editor();

		$this->standard_coursemodule_elements();

		$this->add_action_buttons();
	}
}
