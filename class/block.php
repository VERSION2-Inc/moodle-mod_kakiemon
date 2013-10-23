<?php
namespace ver2\kakiemon;

class block {
	/**
	 *
	 * @var kakiemon
	 */
	protected $kakiemon;

	/**
	 *
	 * @param kakiemon $kakiemon
	 */
	public function __construct(kakiemon $kakiemon) {
		$this->kakiemon = $kakiemon;
	}

	/**
	 *
	 * @param \MoodleQuickForm $f
	 */
	public function add_form_elements(\MoodleQuickForm $f) {
	}

	/**
	 *
	 * @param form_block_edit $form
	 * @return string
	 */
	public function update_data($form, $block) {
		$data = (object)array(
		);

		return serialize($data);
	}

	/**
	 *
	 * @param string $data
	 * @return string
	 */
	public function get_content($block) {
		$data = unserialize($block->data);

		$o = '';

		return $o;
	}
}
