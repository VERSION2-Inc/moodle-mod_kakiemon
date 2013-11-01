<?php
namespace ver2\kakiemon;

class block_link extends block {
	/**
	 *
	 * @param \MoodleQuickForm $f
	 */
	public function add_form_elements(\MoodleQuickForm $f) {
		$f->addElement('text', 'url', 'URL');
		$f->setType('url', PARAM_TEXT);
	}

	/**
	 *
	 * @param \stdClass $form
	 * @return string
	 */
	public function update_data(form_block_edit $form, \stdClass $block) {
		global $DB;

		$formdata = $form->get_data();

		$data = (object)[
			'url' => $formdata->url
		];

		$DB->set_field(kakiemon::TABLE_BLOCKS, 'data', serialize($data), array('id' => $block->id));
	}

	/**
	 *
	 * @param string $data
	 * @return string
	 */
	public function get_content(\stdClass $block) {
		$data = unserialize($block->data);

		$o = '';
		$o .= \html_writer::tag('a', 'リンクを開く', array(
				'href' => $data->url,
				'target' => '_blank'
		));

		return $o;
	}
}
