<?php
namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';

class page_page_edit extends page {
	public function execute() {
		switch (optional_param('action', null, PARAM_ALPHA)) {
			default:
				$this->view();
		}
	}

	private function view() {
		global $DB;

		$form = new form_page_edit();

		echo $this->output->header();

		$form->display();

		echo $this->output->footer();
	}
}

class form_page_edit extends \moodleform {
	protected function definition() {
		$f = $this->_form;


		$this->add_action_buttons();
	}
}

$page = new page_page_edit('/mod/kakiemon/page_edit.php');
$page->execute();
