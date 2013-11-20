<?php
namespace ver2\kakiemon;

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
	private $pageid;

	public function execute() {
		global $DB;

		$this->editmode = optional_param('editmode', '', PARAM_ALPHA);

		switch ($this->action) {
			case 'edit':
				$this->edit();
				break;
			case 'delete':
				$this->delete();
				break;
			case 'changecolumn':
				$this->change_column();
				break;
			case 'changeorder':
				$this->change_order();
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

			$this->pageid = $block->page;
		} else {
			$this->pageid = required_param('page', PARAM_INT);

			$customdata->blocktype = required_param('type', PARAM_ALPHA);
			if (!$customdata->blocktype) {
				redirect($this->ke->url('page_view', array('page' => $this->pageid)));
			}
			$this->form = new form_block_edit(null, $customdata);
		}

		if ($this->form->is_cancelled()) {
			//FIXME pageid
			redirect($this->ke->url('page_view', array('page' => required_param('page', PARAM_INT))));
		}
		if ($this->form->is_submitted()) {
			$this->edit_update();
		}
		$this->edit_form();
	}

	private function edit_form() {
		global $DB;

		$page = $DB->get_record(ke::TABLE_PAGES, array('id' => $this->pageid));
		$this->add_navbar($page->name, $this->ke->url('page_view', array('page' => $this->pageid)));
		$this->add_navbar(ke::str('editblock'));

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
					'page' => $pageid,
					'blockcolumn' => $data->blockcolumn,
					'type' => $data->type,
					'title' => $data->title,
			);
			$block->id = $DB->insert_record(kakiemon::TABLE_BLOCKS, $block);
		}

		$oblock->update_data($this->form, $block);

		redirect($this->ke->url('page_view', array('page'=>$block->page)));
	}

	private function delete() {
		global $DB;

		$blockid = required_param('block', PARAM_INT);
		$block = $DB->get_record(ke::TABLE_BLOCKS, array('id' => $blockid), '*', MUST_EXIST);

		$DB->delete_records(ke::TABLE_BLOCKS, array('id' => $blockid));

		redirect($this->ke->url('page_view', array('page' => $block->page)));
	}

	private function change_column() {
		global $DB;

		$block = $DB->get_record(ke::TABLE_BLOCKS, array('id' => required_param('block', PARAM_INT)),
				'*', MUST_EXIST);
		$value = required_param('value', PARAM_INT);

		$block->blockcolumn += $value;
		$block->blockorder = 0;
		$DB->update_record(ke::TABLE_BLOCKS, $block);

		$this->reorder_blocks($block);

		redirect($this->ke->url('page_view', array('page' => $block->page)));
	}

	private function change_order() {
		global $DB;

		$block = $DB->get_record(ke::TABLE_BLOCKS, array('id' => required_param('block', PARAM_INT)),
				'*', MUST_EXIST);
		$value = required_param('value', PARAM_INT);

		if ($nextblock = $DB->get_record(ke::TABLE_BLOCKS,
				array('blockorder' => $block->blockorder + $value))) {
			$nextblock->blockorder -= $value;
			$DB->update_record(ke::TABLE_BLOCKS, $nextblock);
		}

		$block->blockorder += $value;
		$DB->update_record(ke::TABLE_BLOCKS, $block);

		$this->reorder_blocks($block);

		redirect($this->ke->url('page_view', array('page' => $block->page)));
	}

	private function reorder_blocks(\stdClass $block) {
		global $DB;

		$columnblocks = $DB->get_records(ke::TABLE_BLOCKS, array(
				'page' => $block->page,
				'blockcolumn' => $block->blockcolumn
		), 'blockorder');

		$ids = array_keys($columnblocks);

		$order = 1;
		foreach ($ids as $id) {
			$DB->set_field(ke::TABLE_BLOCKS, 'blockorder', $order++, array('id' => $id));
		}
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
		$f->addElement('hidden', 'block');
		$f->setType('block', PARAM_INT);

		$oblock = $kakiemon->get_block_type($blocktype);

		$f->addElement('hidden', 'id', $kakiemon->cm->id);
		$f->setType('id', PARAM_INT);
		$f->addElement('hidden', 'page', optional_param('page', 0,PARAM_INT));
		$f->setType('page', PARAM_INT);
		if (required_param('editmode', PARAM_ALPHA)=='add'){
			$f->addElement('hidden', 'blockcolumn', required_param('blockcolumn', PARAM_INT));
			$f->setType('blockcolumn', PARAM_INT);
		}
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
