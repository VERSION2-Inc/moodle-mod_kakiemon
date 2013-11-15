<?php
namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';

class page_page_view extends page {
	const LIKE = 'like';
	const DISLIKE = 'dislike';
	const BLOCK_COLUMNS = 3;

	/**
	 *
	 * @var form_page_edit
	 */
	private $form;

	public function execute() {
		switch (optional_param('action', null, PARAM_ALPHA)) {
			case 'setediting':
				$this->set_editing();
				break;
			case 'like':
				$this->like();
				break;
			default:
				$this->view();
		}
	}

	private function view() {
		global $DB, $PAGE, $SESSION, $USER;

		$PAGE->blocks->show_only_fake_blocks();

		$pageid = required_param('page', PARAM_INT);

		if (!ke::is_page_editable($pageid)) {
			$SESSION->kakiemon_editing = false;
		}
		$editing = false;
		if (!empty($SESSION->kakiemon_editing)) {
			$editing = true;
		}

		$jsparams = (object)array(
				'cmid' => $this->cmid,
				'editing' => $editing
		);
		$PAGE->requires->js_init_call('M.mod_kakiemon.page_view_init', array($jsparams), false,
				array(
						'name' => 'mod_kakiemon',
						'fullpath' => '/mod/kakiemon/module.js',
						'requires' => array('dd', 'io')
				));
		$PAGE->requires->yui_module('moodle-mod_kakiemon-dragdrop', 'M.mod_kakiemon.init_dragdrop',
				array(array('cmid' => $this->cmid)));

		$PAGE->requires->css('/mod/kakiemon/lib/lightbox/css/lightbox.css');

		$page = $DB->get_record(ke::TABLE_PAGES, array(
				'id' => $pageid
		));

		$title = $page->name;

		$this->add_navbar($title);

		echo $this->output->header();
		echo $this->output->heading($title);

		$user = $DB->get_record('user', array('id' => $page->userid));
		echo \html_writer::tag('div',
				$user->idnumber.' '.fullname($user), array('style'=>'text-align:right'));

		echo $this->output->container_start('likebuttons');
		$likeelement = 'span';
		$likecount = $DB->count_records(ke::TABLE_LIKES, array(
				'page' => $pageid,
				'type' => self::LIKE
		));
		echo $this->output->action_link(new \moodle_url($this->url, array(
				'page' => $pageid,
				'action' => 'like',
				'type' => self::LIKE
		)), ke::str('like'), null, array('class' => 'likebutton'));
		echo \html_writer::tag($likeelement, $likecount, array('class' => 'likecount'));
		$dislikecount = $DB->count_records(ke::TABLE_LIKES, array(
				'page' => $pageid,
				'type' => self::DISLIKE
		));
		echo $this->output->action_link(new \moodle_url($this->url, array(
				'page' => $pageid,
				'action' => 'like',
				'type' => self::DISLIKE
		)), ke::str('dislike'), null, array('class' => 'likebutton'));
		echo \html_writer::tag($likeelement, $dislikecount, array('class' => 'likecount'));
		echo $this->output->container_end();

		if ($editing) {
			echo $this->output->container(
					$this->output->single_button(
							new \moodle_url($this->url, array(
									'page' => $pageid,
									'action' => 'setediting',
									'editing' => 'off'
							)), ke::str('finisheditingthispage')),
					'editbutton');
		} else {
			if (ke::is_page_editable($pageid)) {
				echo $this->output->container(
						$this->output->single_button(
								new \moodle_url($this->url, array(
										'page' => $pageid,
										'action' => 'setediting',
										'editing' => 'on'
								)), ke::str('editthispage')),
						'editbutton');
			}
		}
		echo $this->output->container('', 'clearer');

		for ($column = 0; $column < 3; $column++) {
			echo $this->output->container_start('block-column', 'column'.$column);

			$blocks = $DB->get_records(ke::TABLE_BLOCKS, array(
					'page' => $pageid,
					'blockcolumn' => $column
			), 'blockorder');
			$row = 0;
			foreach ($blocks as $block) {
				$ob = '';

// 				$ob .= \html_writer::tag('h3', $block->title);

				if ($editing) {
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

					if ($row > 0) {
						$buttons .= util::button(util::BUTTON_UP,
								new \moodle_url($this->ke->url('block_edit', array(
										'action' => 'changeorder',
										'value' => -1,
										'block' => $block->id
								))));
					}
					if ($row < count($blocks) - 1) {
						$buttons .= util::button(util::BUTTON_DOWN,
								new \moodle_url($this->ke->url('block_edit', array(
										'action' => 'changeorder',
										'value' => 1,
										'block' => $block->id
								))));
					}
					if ($column > 0) {
						$buttons .= util::button(util::BUTTON_LEFT,
								new \moodle_url($this->ke->url('block_edit', array(
										'action' => 'changecolumn',
										'value' => -1,
										'block' => $block->id
								))));
					}
					if ($column < self::BLOCK_COLUMNS) {
						$buttons .= util::button(util::BUTTON_RIGHT,
								new \moodle_url($this->ke->url('block_edit', array(
										'action' => 'changecolumn',
										'value' => 1,
										'block' => $block->id
								))));
					}
					$ob .= $this->output->container($buttons, 'blockeditbuttons');
				}

				$oblock = $this->ke->get_block_type($block->type);
				$ob .= $oblock->get_content($block);

				echo \html_writer::tag('div', $ob, array(
						'class' => 'kaki-block',
						'data-id' => $block->id
				));

				$row++;
			}

			if ($editing) {
				echo $this->output->container(ke::str('addblock'));
				echo $this->output->single_select(
						$this->ke->url('block_edit',
								array(
										'action' => 'edit',
										'editmode' => 'add',
										'page' => $pageid,
										'blockcolumn' => $column
								)), 'type', $this->ke->blocks);
			}

			echo $this->output->container_end();
		}


		echo $this->output->footer();
	}

	private function set_editing() {
		global $SESSION;

		if (required_param('editing', PARAM_ALPHA) == 'on') {
			$SESSION->kakiemon_editing = true;
		} else {
			$SESSION->kakiemon_editing = false;
		}

		redirect($this->ke->url('page_view.php', array(
				'page' => required_param('page', PARAM_INT)
		)));
	}

	private function like() {
		global $DB, $USER;

		$userid = $USER->id;
		$pageid = required_param('page', PARAM_INT);
		$type = required_param('type', PARAM_ALPHA);

		$like = $DB->get_record(ke::TABLE_LIKES, array(
				'page' => $pageid,
				'userid' => $userid
		));
		if ($like) {
			if ($like->type != $type) {
				$like->type = $type;
				$DB->update_record(ke::TABLE_LIKES, $like);
			}
		} else {
			$like = (object)array(
					'kakiemon' => $this->ke->instance,
					'page' => $pageid,
					'userid' => $userid,
					'type' => $type
			);
			$DB->insert_record(ke::TABLE_LIKES, $like);
		}

		redirect(new \moodle_url($this->url, array('page' => $pageid)));
	}
}

$page = new page_page_view('/mod/kakiemon/page_view.php');
$page->execute();
