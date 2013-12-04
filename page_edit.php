<?php
namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';
require_once $CFG->libdir . '/formslib.php';

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
			case 'delete':
				$this->delete();
				break;
			default:
				$this->view();
		}
	}

	private function view() {
		global $DB;

		$pageid = optional_param('page', 0, PARAM_INT);

		if ($pageid) {
			$this->check_user($pageid);
		}

		if ($this->form->is_submitted()) {
			$this->update();
			return;
		}

		// if (optional_param('add', 0, PARAM_BOOL)) {

		// } else {
		// $pageid = required_param('page', PARAM_INT);
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

		$now = time();

		if ($data->template) {
			if ($template = $this->ke->get_template_page()) {
				if (!$data->page || $data->page && $template->id != $data->page) {
					$template->template = 0;
					$this->db->update_record(ke::TABLE_PAGES, $template);
				}
			}
		}

		$page = new \stdClass();
		$page->name = $data->name;
		$page->template = $data->template;
		$page->timemodified = $now;

		if ($data->page) {
			$this->check_user($data->page);

			$page->id = $data->page;

			$DB->update_record(ke::TABLE_PAGES, $page);
		} else {
			$page->kakiemon = $this->ke->instance;
			$page->userid = $userid;
			$page->timecreated = $now;

			$page->id = $DB->insert_record(ke::TABLE_PAGES, $page);

			if ($template = $this->ke->get_template_page()) {
				$this->ke->copy_page($template, $page);
			}
		}

		$this->ke->log('edit page', $this->ke->url('page_view', array('page' => $page->id)), $page->name);

		redirect($this->ke->url('view'));
	}

	private function delete() {
		global $USER;

		$pageid = required_param('page', PARAM_INT);
		$this->check_user($pageid);

		$this->db->delete_records(ke::TABLE_PAGES, array('id' => $pageid));

		$url = $this->ke->url('view');
		$this->ke->log('delete page', $url, $pageid);
		redirect($url);
	}

	/**
	 *
	 * @param int $page
	 */
	private function check_user($pageid) {
		global $USER;

		$page = $this->db->get_record(ke::TABLE_PAGES, array('id' => $pageid), '*', MUST_EXIST);

		if ($page->userid != $USER->id) {
			$this->ke->print_error('thisisnotyourpage');
		}
	}
}

class form_page_edit extends \moodleform {
	protected function definition() {
		$f = $this->_form;

		/* @var $ke ke */
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

		if ($ke->has_capability(ke::CAP_CREATE_TEMPLATE)) {
			$f->addElement('selectyesno', 'template', ke::str('useastemplate'));
		} else {
			$f->addElement('hidden', 'template', 0);
			$f->setType('template', PARAM_BOOL);
		}

		$this->add_action_buttons(false);
	}
}

$page = new page_page_edit('/mod/kakiemon/page_edit.php');
$page->execute();
