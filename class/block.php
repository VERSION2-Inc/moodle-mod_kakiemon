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

	public function set_form_data(\MoodleQuickForm $f, \stdClass $block) {
		$data = (array)unserialize($block->data);
var_dump($data);
		$f->setDefaults($data);
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
