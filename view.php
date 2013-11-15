<?php

namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';
require_once $CFG->libdir . '/tablelib.php';

class page_view extends page {

	public function execute() {
		switch (optional_param('action', null, PARAM_ALPHA)) {
			default:
				$this->view();
		}
	}

	private function view() {
		global $DB, $USER;

		$userid = $USER->id;

		echo $this->output->header();

		echo $this->output->action_link(
				new \moodle_url($this->ke->url('page_edit', array(
						'add' => 1
				)), array(
						'action' => 'addpage'
				)), ke::str('addpage'));

		echo $this->output->heading(ke::str('mypages'));

		$pages = $DB->get_records(ke::TABLE_PAGES,
				array(
						'kakiemon' => $this->ke->instance,
						'userid' => $userid
				), 'timecreated DESC');
		$table = new \flexible_table('pages');
		$table->define_baseurl($this->url);
		$columns = array(
				'name',
				'timecreated',
				'buttons'
		);
		$headers = array(
				ke::str('pagename'),
				ke::str('timecreated'),
				''
		);
		$table->define_columns($columns);
		$table->define_headers($headers);
// 		$table->sortable(true, 'timecreated', SORT_DESC);
		$table->setup();
		$editicon = new \pix_icon('t/edit', get_string('edit'));
		$deleteicon = new \pix_icon('t/delete', get_string('delete'));
		foreach ($pages as $page) {
			$url = new \moodle_url($this->ke->url('page_view'), array(
					'page' => $page->id
			));
			$name = $this->output->action_link($url, $page->name);
			$params = array(
					'page' => $page->id
			);
			$buttons = $this->output->action_icon($this->ke->url('page_edit', $params), $editicon) . $this->output->action_icon(
					$this->ke->url('page_edit', $params), $deleteicon,
					new \confirm_action(ke::str('reallydeletepage')));
			$row = array(
					$name,
					userdate($page->timecreated),
					$buttons
			);
			$table->add_data($row);
		}
		$table->finish_output();

		echo $this->output->heading(ke::str('allpages'));

		$pages = $DB->get_records_sql(
				'
				SELECT p.id, p.name, p.timecreated,
					u.lastname, u.firstname
				FROM {' . ke::TABLE_PAGES . '} p
					JOIN {user} u ON p.userid = u.id
				WHERE p.kakiemon = :ke
					AND p.userid <> :userid
				', array(
						'ke' => $this->ke->instance,
						'userid' => $userid
				));
		$table = new \flexible_table('pages');
		$table->define_baseurl($this->url);
		$columns = array(
				'name',
				'user',
				'timecreated'
		);
		$headers = array(
				ke::str('pagename'),
				ke::str('author'),
				ke::str('timecreated')
		);
		$table->define_columns($columns);
		$table->define_headers($headers);
		$table->sortable(true, 'timecreated', SORT_DESC);
		$table->setup();
		foreach ($pages as $page) {
			$name = $this->output->action_link(
					new \moodle_url($this->url, array(
							'page' => $page->id
					)), $page->name);
			$row = array(
					$name,
					fullname($page),
					userdate($page->timecreated)
			);
			$table->add_data($row);
		}
		$table->finish_output();

		echo $this->output->footer();
	}
}

$page = new page_view('/mod/kakiemon/view.php');
$page->execute();
