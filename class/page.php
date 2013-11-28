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
	 * @var ke
	 */
	public $ke;
	/**
	 *
	 * @var ke
	 * @deprecated
	 */
	public $kakiemon;
	/**
	 *
	 * @var string
	 */
	public $action;
	/**
	 *
	 * @var \moodle_database
	 */
	protected $db;

	public function __construct($url) {
		global $DB, $PAGE, $OUTPUT;

		$id = required_param('id', PARAM_INT);
		$this->url = new \moodle_url($url, array('id' => $id));
		$PAGE->set_url($this->url);

		$this->cmid = $id;

		$this->ke = new ke($id);
		$this->kakiemon = $this->ke;
		$this->action = optional_param('action', null, PARAM_ALPHA);

		require_login($this->ke->cm->course, true, $this->ke->cm);

		$PAGE->set_title('title');
		$PAGE->set_heading('heading');

		$this->output = $OUTPUT;
		$this->db = $DB;
	}

	public abstract function execute();

	public function add_navbar($text, $action = null) {
		global $PAGE;

		$PAGE->navbar->add($text, $action);
	}

	public static function execute_new($file) {
		global $CFG;
	}
}
