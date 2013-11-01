<?php
namespace ver2\kakiemon;

class block_video extends block {
	public function add_form_elements(\MoodleQuickForm $f) {
		$f->addElement('textarea', 'content', '埋め込みコード');
	}

	public function update_data(form_block_edit $form, \stdClass $block) {
		global $DB;

		$formdata = $form->get_data();

		$data = (object)array(
			'content' => $formdata->content
		);

		$DB->set_field(kakiemon::TABLE_BLOCKS, 'data', serialize($data), array('id' => $block->id));
	}

	public function get_content(\stdClass $block) {
		$data = unserialize($block->data);

		$o = $data->content;

		return $o;
	}
}
