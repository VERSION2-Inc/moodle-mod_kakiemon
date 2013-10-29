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

		$blocks = $DB->get_records(ke::TABLE_BLOCKS, array(
				'page' => $pageid
		));
		foreach ($blocks as $block) {
			$ob = '';

			$ob .= \html_writer::tag('h3', $block->title);

			$oblock = $this->kakiemon->get_block($block->type);
			$ob .= $oblock->get_content($block);

			echo $this->output->box($ob, 'kaki-block');
		}

		echo $this->output->container(ke::str('addblock'));
		echo $this->output->single_select(
				$this->ke->url('block_edit',
						array(
								'id' => $this->cmid,
								'page' => $pageid
						)), 'type', $this->kakiemon->blocks);

		echo $this->output->footer();
	}
}

$page = new page_page_view('/mod/kakiemon/page_view.php');
$page->execute();
