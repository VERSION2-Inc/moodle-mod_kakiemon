<?php
use ver2\kakiemon\ke;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/course/moodleform_mod.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';

class mod_kakiemon_mod_form extends moodleform_mod {
	protected function definition() {
		$f = $this->_form;

		$f->addElement('header', 'general', get_string('general', 'form'));

		$f->addElement('text', 'name', ke::str('kakiemonname'), array('size' => 64));
		$f->setType('name', PARAM_TEXT);
		$f->addRule('name', null, 'required', null, 'client');

		$this->add_intro_editor();

		$f->addElement('header', 'timelimit', ke::str('timelimit'));
		$f->addElement('date_time_selector', 'createstartdate', ke::str('createstartdate'),
				array('optional' => true));
		$f->addElement('date_time_selector', 'createenddate', ke::str('createenddate'),
				array('optional' => true));
		$f->addElement('date_time_selector', 'viewstartdate', ke::str('viewstartdate'),
				array('optional' => true));
		$f->addElement('date_time_selector', 'viewenddate', ke::str('viewenddate'),
				array('optional' => true));

		//編集権限
		//公開範囲

		$f->addElement('header', 'features', ke::str('features'));
		$f->addElement('selectyesno', 'showtracks', ke::str('usefootmark'));
		$f->addElement('selectyesno', 'uselike', ke::str('uselike'));
		$f->addElement('selectyesno', 'usedislike', ke::str('usedislike'));

		$this->standard_coursemodule_elements();

		$this->add_action_buttons();
	}
}
