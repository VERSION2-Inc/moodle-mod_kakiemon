<?php
namespace ver2\kakiemon;

defined('MOODLE_INTERNAL') || die();

class kakiemon {
	const COMPONENT = 'mod_kakiemon';

	const TABLE_MOD = 'kakiemon';
	const TABLE_PAGES = 'kakiemon_pages';
	const TABLE_BLOCKS = 'kakiemon_blocks';
	const TABLE_LIKES = 'kakiemon_likes';

	/**
	 *
	 * @var \stdClass
	 */
	public $cm;
	/**
	 *
	 * @var int
	 */
	public $instance;
	/**
	 *
	 * @var string[]
	 */
	public $blocks = [];
	/**
	 *
	 * @var \context_module
	 */
	public $context;

	/**
	 *
	 * @param int $cmid
	 */
	public function __construct($cmid) {
		global $DB;

		$this->cm = get_coursemodule_from_id(self::TABLE_MOD, $cmid);
		$this->instance = $this->cm->instance;
		$this->context = \context_module::instance($this->cm->id);

		$this->load_block_plugins();
	}

	public function load_block_plugins() {
		global $CFG;

		$files = glob($CFG->dirroot . '/mod/kakiemon/blocks/*');
		foreach ($files as $file) {
			include_once $file;

			$blockname = pathinfo($file, PATHINFO_FILENAME);
			$this->blocks[$blockname] = self::str($blockname);
		}
		self::asort($this->blocks);
	}

	/**
	 *
	 * @param string $identifier
	 * @param string|\stdClass $a
	 * @return string
	 */
	public static function str($identifier, $a = null) {
		return get_string($identifier, self::COMPONENT, $a);
	}

	/**
	 *
	 * @param string $type
	 * @return block
	 */
	public function get_block($type) {
		$classname = __NAMESPACE__.'\block_'.$type;

		return new $classname($this);
	}

	/**
	 *
	 * @param string[] $arr
	 */
	public static function asort(array &$arr) {
		if (class_exists('core_collator')) {
			\core_collator::asort($arr);
		} else if (class_exists('collatorlib')) {
			\collatorlib::asort($arr);
		} else {
			asort($arr);
		}
	}
}
