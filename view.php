<?php
namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';

class page_view extends page {
	public function execute() {
		switch (optional_param('action', null, PARAM_ALPHA)) {
			default:
				$this->view();
		}
	}

	private function view() {
		global $DB;

		echo $this->output->header();



		echo $this->output->container('新規ブロックの追加');
		echo $this->output->single_select(
				new \moodle_url('/mod/kakiemon/blockedit.php', array('id' => $this->cmid)),
				'type', $this->kakiemon->blocks);

		echo '<div style=margin:1em;text-align:center>
				<input type=radio checked>イイネ！
				<input type=radio>ワルイネ！
				</div>';

		$blocks = $DB->get_records(kakiemon::TABLE_BLOCK, array('kakiemon' => $this->kakiemon->instance));
		foreach ($blocks as $block) {
			$ob = '';

			$ob .= \html_writer::tag('h3', $block->title);

			$oblock = $this->kakiemon->get_block($block->type);
			$ob .= $oblock->get_content($block);

			echo $this->output->box($ob, 'kaki-block');
		}
// 		var_dump($blocks);

		echo $this->output->footer();
	}
}

$page = new page_view('/mod/kakiemon/view.php');
$page->execute();
