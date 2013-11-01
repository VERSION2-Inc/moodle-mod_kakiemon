<?php

namespace ver2\kakiemon;

use ver2\kakiemon\kakiemon as ke;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';

class page_page_view extends page {
	/**
	 *
	 * @var form_page_edit
	 */
	private $form;

	public function execute() {
		switch (optional_param('action', null, PARAM_ALPHA)) {
			default:
				$this->view();
		}
	}

	private function view() {
		global $DB, $PAGE;

		// $PAGE->set_pagelayout('embedded');

		$PAGE->requires->css('/mod/kakiemon/lib/lightbox/css/lightbox.css');

		$pageid = required_param('page', PARAM_INT);
		$page = $DB->get_record(ke::TABLE_PAGES, array(
				'id' => $pageid
		));

		$title = $page->name;

		$this->add_navbar($title);

		echo $this->output->header();
		echo $this->output->heading($title);

		echo $this->output->container(
				$this->output->single_button(
						new \moodle_url($this->url, array('edit' => 'on')), ke::str('editthispage')),
				'editbutton');
		echo $this->output->container('', 'clearer');

		$blocks = $DB->get_records(ke::TABLE_BLOCKS, array(
				'page' => $pageid
		));
		foreach ($blocks as $block) {
			$ob = '';

			$ob .= \html_writer::tag('h3', $block->title);

			$buttons = '';
			$buttons .= util::button(util::BUTTON_EDIT,
					new \moodle_url($this->ke->url('block_edit', array(
							'block' => $block->id,
							'action' => 'edit',
							'editmode' => 'update'
					))));
			$buttons .= util::button(util::BUTTON_DELETE,
					new \moodle_url($this->ke->url('block_edit', array(
							'action' => 'delete',
							'block' => $block->id
					))),
					new \confirm_action(ke::str('reallydeleteblock')));
			$ob .= $this->output->container($buttons, 'blockeditbuttons');

			$oblock = $this->kakiemon->get_block_type($block->type);
			$ob .= $oblock->get_content($block);

			echo $this->output->box($ob, 'kaki-block');
		}

		echo $this->output->container(ke::str('addblock'));
		echo $this->output->single_select(
				$this->ke->url('block_edit',
						array(
								'action' => 'edit',
								'editmode' => 'add',
								'page' => $pageid
						)), 'type', $this->kakiemon->blocks);

		echo $this->output->footer();
	}
}

$page = new page_page_view('/mod/kakiemon/page_view.php');
$page->execute();
