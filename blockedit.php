<?php
namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';
require_once $CFG->libdir . '/formslib.php';

class page_block_edit extends page {
	public function execute() {
		$this->form = new form_block_edit(null, (object)array(
				'kakiemon' => $this->kakiemon
		));

		if ($this->form->is_submitted()) {
			$this->update_block();
		}
		$this->view();
	}

	private function view() {
		echo $this->output->header();

		$this->form->display();

		echo $this->output->footer();
	}

	private function update_block() {
		global $DB;

		$data = $this->form->get_data();

		$oblock = $this->kakiemon->get_block($data->type);

		$block = (object)array(
			'kakiemon' => $this->kakiemon->instance,
			'type' => $data->type,
			'title' => $data->title,
// 			'data' => $oblock->get_data($this->form)
		);
		$block->id = $DB->insert_record(kakiemon::TABLE_BLOCK, $block);

		$oblock->update_data($this->form, $block);

		$url = new \moodle_url('/mod/kakiemon/view.php', array('id' => $this->kakiemon->cm->id));
		redirect($url);
	}
}

class form_block_edit extends \moodleform {
	protected function definition() {
		$f = $this->_form;
		/* @var $kakiemon kakiemon */
		$kakiemon = $this->_customdata->kakiemon;

		$blocktype = required_param('type', PARAM_ALPHA);
		$oblock = $kakiemon->get_block($blocktype);

		$f->addElement('hidden', 'id', $kakiemon->cm->id);
		$f->setType('id', PARAM_INT);
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

$page = new page_block_edit('/mod/kakiemon/blockedit.php');
$page->execute();
