<?php

namespace ver2\kakiemon;

use ver2\kakiemon\kakiemon as ke;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';

class page_page_edit extends page {
	/**
	 *
	 * @var form_page_edit
	 */
	private $form;

	public function execute() {
		$this->form = new form_page_edit(null, (object)array(
				'ke' => $this->ke
		));

		switch (optional_param('action', null, PARAM_ALPHA)) {
			default:
				$this->view();
		}
	}

	private function view() {
		global $DB;

		if ($this->form->is_submitted()) {
			$this->update();
			return;
		}

		// if (optional_param('add', 0, PARAM_BOOL)) {

		// } else {
		// $pageid = required_param('page', PARAM_INT);
		$pageid = optional_param('page', 0, PARAM_INT);
		if ($pageid) {
			$page = $DB->get_record(ke::TABLE_PAGES, array(
					'id' => $pageid
			));
			$page->page = $page->id;
			unset($page->id);
			$this->form->set_data($page);
		}
		// }

		$title = ke::str('editpage');

		$this->add_navbar($title);

		echo $this->output->header();
		echo $this->output->heading($title);

		$this->form->display();

		echo $this->output->footer();
	}

	private function update() {
		global $DB, $USER;

		$userid = $USER->id;

		$data = $this->form->get_data();
		// var_dump($data);

		$now = time();

		$page = new \stdClass();
		$page->name = $data->name;
		$page->timemodified = $now;

		if ($data->page) {
			$page->id = $data->page;

			$DB->update_record(ke::TABLE_PAGES, $page);
		} else {
			$page->kakiemon = $this->ke->instance;
			$page->userid = $userid;
			$page->timecreated = $now;

			$DB->insert_record(ke::TABLE_PAGES, $page);
		}

		redirect($this->ke->url('view'));
	}
}

class form_page_edit extends \moodleform {

	protected function definition() {
		$f = $this->_form;

		/* @var $ke kakiemon */
		$ke = $this->_customdata->ke;

		$f->addElement('hidden', 'id', $ke->cmid);
		$f->setType('id', PARAM_INT);
		$f->addElement('hidden', 'page', null);
		$f->setType('page', PARAM_INT);

		$f->addElement('header', 'pagesettings', ke::str('pagesettings'));

		$f->addElement('text', 'name', ke::str('pagename'), array(
				'size' => 40,
				'maxlength' => 255
		));
		$f->setType('name', PARAM_TEXT);
		$f->addRule('name', null, 'required', null, 'client');

		$this->add_action_buttons(false);
	}
}

$page = new page_page_edit('/mod/kakiemon/page_edit.php');
$page->execute();