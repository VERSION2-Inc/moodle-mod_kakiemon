<?php
namespace ver2\kakiemon;

class block {
	const FILE_AREA = 'blockfile';

	protected $ke;
	/**
	 *
	 * @var \core_renderer
	 */
	protected $output;

	/**
	 *
	 * @param kakiemon $kakiemon
	 */
	public function __construct(ke $kakiemon) {
		global $OUTPUT;

		$this->ke = $kakiemon;
		$this->output = $OUTPUT;
	}

	/**
	 *
	 * @param \MoodleQuickForm $f
	 */
	public function add_form_elements(\MoodleQuickForm $f) {
	}

	public function set_form_data(form_block_edit $form, \stdClass $block) {
		$data = (array)unserialize($block->data);

		$form->set_data($data);
	}

	/**
	 *
	 * @param form_block_edit $form
	 * @return string
	 */
	public function update_data(form_block_edit $form, \stdClass $block) {
		$data = (object)array(
		);

		return serialize($data);
	}

	/**
	 *
	 * @param string $data
	 * @return string
	 */
	public function get_content(\stdClass $block) {
		$data = unserialize($block->data);

		$o = '';

		return $o;
	}
}
