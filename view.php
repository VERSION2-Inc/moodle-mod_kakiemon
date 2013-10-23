<?php
namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';
require_once $CFG->libdir . '/tablelib.php';

class page_view extends page {
	public function execute() {
		switch (optional_param('action', null, PARAM_ALPHA)) {
			case 'addpage':
				$this->stub_add_page();
				break;
			default:
				$this->view();
		}
	}

	private function view() {
		global $DB;

		echo $this->output->header();

		echo $this->output->action_link(new \moodle_url($this->url, array('action' => 'addpage')), 'Add page');

		$pages = $DB->get_records(kakiemon::TABLE_PAGES, array('kakiemon' => $this->kakiemon->instance));
		$table = new \flexible_table('pages');
		$table->define_baseurl($this->url);
		$columns = array('name', 'user', 'timecreated');
		$headers = array('Name', 'user', kakiemon::str('timecreated'));
		$table->define_columns($columns);
		$table->define_headers($headers);
		$table->setup();
		foreach ($pages as $page) {
			$row = array(
					$page->name,
					$page->userid,
					userdate($page->timecreated)
			);
			$table->add_data($row);
		}
		$table->finish_output();

		echo $this->output->container('新規ブロックの追加');
		echo $this->output->single_select(
				new \moodle_url('/mod/kakiemon/blockedit.php', array('id' => $this->cmid)),
				'type', $this->kakiemon->blocks);

		echo '<div style=margin:1em;text-align:center>
				<input type=radio checked>イイネ！
				<input type=radio>ワルイネ！
				</div>';

		$blocks = $DB->get_records(kakiemon::TABLE_BLOCKS, array('kakiemon' => $this->kakiemon->instance));
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

	private function stub_add_page() {
		global $DB, $USER;

		$userid = $USER->id;
		$page = (object)array(
				'kakiemon' => $this->kakiemon->instance,
				'userid' => $userid,
				'name' => 'ページ',
				'timecreated' => time()
		);
		$DB->insert_record(kakiemon::TABLE_PAGES, $page);

		redirect($this->url);
	}
}

$page = new page_view('/mod/kakiemon/view.php');
$page->execute();
