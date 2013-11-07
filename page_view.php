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
			case 'setediting':
				$this->set_editing();
				break;
			default:
				$this->view();
		}
	}

	private function view() {
		global $DB, $PAGE, $SESSION;

		$PAGE->set_pagelayout('embedded');

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

		$pageid = required_param('page', PARAM_INT);
		$page = $DB->get_record(ke::TABLE_PAGES, array(
				'id' => $pageid
		));

		$title = $page->name;

		$this->add_navbar($title);

		echo $this->output->header();
		echo $this->output->heading($title);

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
			echo $this->output->container(
					$this->output->single_button(
							new \moodle_url($this->url, array(
									'page' => $pageid,
									'action' => 'setediting',
									'editing' => 'on'
							)), ke::str('editthispage')),
					'editbutton');
		}
		echo $this->output->container('', 'clearer');

		for ($column = 0; $column < 3; $column++) {
			echo $this->output->container_start('block-column', 'column'.$column);

			$blocks = $DB->get_records(ke::TABLE_BLOCKS, array(
					'page' => $pageid,
					'blockcolumn' => $column
			), 'blockorder');
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

// 				$buttons .= util::button(util::BUTTON_UP,
// 						new \moodle_url($this->ke->url('block_edit', array(
// 								'action' => 'changeorder',
// 								'value' => -1,
// 								'block' => $block->id
// 						))));
// 				$buttons .= util::button(util::BUTTON_DOWN,
// 						new \moodle_url($this->ke->url('block_edit', array(
// 								'action' => 'changeorder',
// 								'value' => 1,
// 								'block' => $block->id
// 						))));
// 				$buttons .= util::button(util::BUTTON_LEFT,
// 						new \moodle_url($this->ke->url('block_edit', array(
// 								'action' => 'changecolumn',
// 								'value' => -1,
// 								'block' => $block->id
// 						))));
// 				$buttons .= util::button(util::BUTTON_RIGHT,
// 						new \moodle_url($this->ke->url('block_edit', array(
// 								'action' => 'changecolumn',
// 								'value' => 1,
// 								'block' => $block->id
// 						))));
				$ob .= $this->output->container($buttons, 'blockeditbuttons');

				$oblock = $this->kakiemon->get_block_type($block->type);
				$ob .= $oblock->get_content($block);

				echo \html_writer::tag('div', $ob, array(
						'class' => 'kaki-block',
						'data-id' => $block->id
				));
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

		$u=$this->ke->url($this->url, array(
				'page' => required_param('page', PARAM_INT)
		));
		redirect($this->ke->url('page_view.php', array(
				'page' => required_param('page', PARAM_INT)
		)));
	}
}

$page = new page_page_view('/mod/kakiemon/page_view.php');
$page->execute();
