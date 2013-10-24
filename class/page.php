<?php
namespace ver2\kakiemon;

defined('MOODLE_INTERNAL') || die();

abstract class page {
	/**
	 *
	 * @var \core_renderer
	 */
	protected $output;
	/**
	 *
	 * @var int
	 */
	public $cmid;
	/**
	 *
	 * @var kakiemon
	 */
	public $kakiemon;
	/**
	 *
	 * @var kakiemon
	 */
	public $ke;

	public function __construct($url) {
		global $PAGE, $OUTPUT;

		$id = required_param('id', PARAM_INT);
		$this->url = new \moodle_url($url, array('id' => $id));
		$PAGE->set_url($this->url);

		$this->cmid = $id;

		$kakiemon = new kakiemon($id);
		$this->kakiemon = $kakiemon;
		$this->ke = $kakiemon;

		require_login($kakiemon->cm->course, true, $kakiemon->cm);

		$PAGE->set_title('title');
		$PAGE->set_heading('heading');

		$this->output = $OUTPUT;
	}

	public abstract function execute();

	public function add_navbar($text) {
		global $PAGE;

		$PAGE->navbar->add($text);
	}

	public static function execute_new($file) {
		global $CFG;
	}
}
