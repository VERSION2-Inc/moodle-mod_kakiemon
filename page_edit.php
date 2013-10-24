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

		$pageid = required_param('page', PARAM_INT);

		$page = $DB->get_record(ke::TABLE_PAGES, array('id' => $pageid));
		$page->page = $page->id;
		unset($page->id);

		$this->form->set_data($page);

		echo $this->output->header();

		$this->form->display();

		echo $this->output->footer();
	}

	private function update() {

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

		$f->addElement('text', 'name', ke::str('pagename'), array('size' => 40));
		$f->setType('name', PARAM_TEXT);

		$this->add_action_buttons();
	}
}

$page = new page_page_edit('/mod/kakiemon/page_edit.php');
$page->execute();
