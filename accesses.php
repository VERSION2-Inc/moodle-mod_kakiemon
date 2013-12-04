<?php
namespace ver2\kakiemon;

use assignfeedback_editpdf\annotation;
require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';

class page_accesses extends page {
	const ACCESS_LIMIT = 40;

	public function execute() {
		$this->view();
	}

	private function view() {
		global $USER;

		$pageid = required_param('page', PARAM_INT);
		$page = $this->db->get_record(ke::TABLE_PAGES, array('id' => $pageid), '*', MUST_EXIST);
		$this->add_navbar($page->name, $this->ke->url('page_view', array('page' => $pageid)));
		$this->add_navbar(ke::str('accesses'));

		$self = optional_param('self', 0, PARAM_BOOL);

		echo $this->output->header();
		echo $this->output->heading(ke::str('accesses'));

		echo $this->output->container_start('menulinks');
		$url = $this->ke->url('accesses', array('page' => $pageid));
		if ($self) {
			echo $this->output->action_link($url, ke::str('viewotheraccesses'));
		} else {
			$url->param('self', 1);
			echo $this->output->action_link($url, ke::str('viewselfaccesses'));
		}
		echo $this->output->container_end();

		if ($self) {
			$where = ' AND userid = :user';
		} else {
			$where = ' AND userid <> :user';
		}
		$accesses = $this->db->get_records_sql(
				'
				SELECT
					a.id, a.timeaccessed,
					'.\user_picture::fields('u', null, 'userid').'
				FROM {'.ke::TABLE_ACCESSES.'} a
					JOIN {user} u ON a.userid = u.id
				WHERE a.page = :page
					'.$where.'
				ORDER BY a.timeaccessed DESC
				',
				$params = array(
						'page' => $pageid,
						'user' => $USER->id
				),
				0, self::ACCESS_LIMIT
		);

		$table = new \html_table();
		$table->attributes = array(
				'class' => 'generaltable accesses'
		);
		$table->head = array(
				get_string('time'),
				'',
				get_string('fullnameuser')
		);
		foreach ($accesses as $access) {
			$row = array(
					userdate($access->timeaccessed),
					$this->output->user_picture($access),
					fullname($access)
			);
			$table->data[] = $row;
		}
		echo \html_writer::table($table);

		echo $this->output->footer();
	}
}

page_accesses::execute_new(__FILE__);
