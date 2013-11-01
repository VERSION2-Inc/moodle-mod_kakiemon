<?php
namespace ver2\kakiemon;

use ver2\kakiemon\kakiemon as ke;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';
require_once $CFG->libdir . '/formslib.php';

class page_block_edit extends page {
	/**
	 *
	 * @var form_page_edit
	 */
	private $form;
	private $editmode;

	public function execute() {
		global $DB;

		$this->editmode = optional_param('editmode', '', PARAM_ALPHA);

		switch ($this->action) {
			case 'edit':
				$this->edit();
				break;
		}

	}

	private function edit() {
		global $DB;

		$customdata = (object)array(
				'kakiemon' => $this->kakiemon
		);
		$editmode = required_param('editmode', PARAM_ALPHA);
		if ($editmode == 'update') {
			$block = util::get_record_for_form(ke::TABLE_BLOCKS, required_param('block', PARAM_INT), 'block');
			$customdata->blocktype = $block->type;
			$this->form = new form_block_edit(null, $customdata);
			$this->form->set_data($block);

			$blocktype = $this->ke->get_block_type($block->type);
			$blocktype->set_form_data($this->form, $block);
		} else {
			$customdata->blocktype = required_param('type', PARAM_ALPHA);
			$this->form = new form_block_edit(null, $customdata);
		}

		if ($this->form->is_submitted()) {
			$this->edit_update();
		}
		$this->edit_form();
	}

	private function edit_form() {
		echo $this->output->header();

		$this->form->display();

		echo $this->output->footer();
	}

	private function edit_update() {
		global $DB;

		$data = $this->form->get_data();

		$oblock = $this->kakiemon->get_block_type($data->type);

		if ($this->editmode == 'update') {
			$block = $DB->get_record(ke::TABLE_BLOCKS, array('id' => $data->block));
			$block->title = $data->title;
			$DB->update_record(ke::TABLE_BLOCKS, $block);
		} else {
			$pageid=required_param('page', PARAM_INT);
			$block = (object)array(
					'kakiemon' => $this->kakiemon->instance,
					'page'=>$pageid,
					'type' => $data->type,
					'title' => $data->title,
					// 				'data' => $oblock->get_data($this->form)
			);
			$block->id = $DB->insert_record(kakiemon::TABLE_BLOCKS, $block);
		}

		$oblock->update_data($this->form, $block);

		redirect($this->ke->url('page_view', array('page'=>$block->page)));
	}

	private function view() {
		global $DB;

		echo $this->output->header();

		$this->form->display();

		echo $this->output->footer();
	}

}

class form_block_edit extends \moodleform {
	protected function definition() {
		$f = $this->_form;
		/* @var $kakiemon kakiemon */
		$kakiemon = $this->_customdata->kakiemon;
		$blocktype = $this->_customdata->blocktype;

		$f->addElement('hidden', 'action', required_param('action', PARAM_ALPHA));
		$f->setType('action', PARAM_ALPHA);
		$f->addElement('hidden', 'editmode', required_param('editmode', PARAM_ALPHA));
		$f->setType('editmode', PARAM_ALPHA);
		$f->addElement('hidden', 'block' );
		$f->setType('block', PARAM_INT);

		$oblock = $kakiemon->get_block_type($blocktype);

		$f->addElement('hidden', 'id', $kakiemon->cm->id);
		$f->setType('id', PARAM_INT);
		$f->addElement('hidden', 'page', optional_param('page', 0,PARAM_INT));
		$f->setType('page', PARAM_INT);
		$f->addElement('hidden', 'type', $blocktype);
		$f->setType('type', PARAM_ALPHA);

		$f->addElement('text', 'title', 'ブロックタイトル');
		$f->setType('title', PARAM_TEXT);
		$f->addRule('title', null, 'required', null, 'client');
		$f->setDefault('title', kakiemon::str($blocktype));

		//格納、自動格納
		//ファイル

		$oblock->add_form_elements($f);

		$this->add_action_buttons();
	}
}

$page = new page_block_edit('/mod/kakiemon/block_edit.php');
$page->execute();
