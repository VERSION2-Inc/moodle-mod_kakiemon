<?php
namespace ver2\kakiemon;

class block_html extends block {
	public function add_form_elements(\MoodleQuickForm $f) {
		$f->addElement('editor', 'content', 'コンテンツ');
	}

	public function update_data($form, $block) {
		global $DB;

		$formdata = $form->get_data();

		$data = (object)[
			'content' => $formdata->content['text']
		];

		$DB->set_field(kakiemon::TABLE_BLOCK, 'data', serialize($data), array('id' => $block->id));
	}

	public function get_content($block) {
		$data = unserialize($block->data);

		$o = $data->content;

		return $o;
	}
}
