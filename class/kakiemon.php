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
	 * @var int
	 */
	public $cmid;

	/**
	 *
	 * @param int $cmid
	 */
	public function __construct($cmid) {
		global $DB;

		$this->cm = get_coursemodule_from_id(self::TABLE_MOD, $cmid);
		$this->cmid = $this->cm->id;
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
	public function get_block_type($type) {
		$classname = __NAMESPACE__.'\block_'.$type;

		return new $classname($this);
	}

	/**
	 *
	 * @param string[] $arr
	 * @return boolean
	 */
	public static function asort(array &$arr) {
		if (class_exists('core_collator')) {
			return \core_collator::asort($arr);
		} else if (class_exists('collatorlib')) {
			return \collatorlib::asort($arr);
		} else {
			return asort($arr);
		}
	}

	/**
	 *
	 * @param string $url
	 * @param array $params
	 * @return \moodle_url
	 */
	public function url($url, array $params = null) {
		if (!preg_match(',/$,', $url) && strpos(basename($url), '.') === false) {
			$url .= '.php';
		}

		$params['id'] = $this->cm->id;

		return new \moodle_url('/mod/kakiemon/'.$url, $params);
	}
}
