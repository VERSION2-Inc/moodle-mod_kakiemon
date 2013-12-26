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
    /**
     *
     * @var \moodle_url
     */
    protected $url;
    public $ispublic;

    /**
     *
     * @param string $url
     */
    public function __construct($url) {
        global $DB, $PAGE, $OUTPUT;

        $id = required_param('id', PARAM_INT);
        $this->url = new \moodle_url($url, array('id' => $id));
        $PAGE->set_url($this->url);

        $this->cmid = $id;

        $this->ke = new ke($id);
        $this->kakiemon = $this->ke;
        $this->action = optional_param('action', null, PARAM_ALPHA);

        if ($this->ispublic) {
//             var_dump($this->ke->cm);
//             var_dump($this->ke->course);
//             $PAGE->set_context(\context_module::instance($this->cmid));
            $PAGE->set_cm($this->ke->cm, $this->ke->course);
        } else {
            require_login($this->ke->cm->course, true, $this->ke->cm);
        }

        $strkakiemon = ke::str('modulename');
        $PAGE->set_title($strkakiemon);
        $PAGE->set_heading($strkakiemon);

        $this->output = $OUTPUT;
        $this->db = $DB;
    }

    public abstract function execute();

    /**
     *
     * @param string $text
     * @param string $action
     */
    public function add_navbar($text, $action = null) {
        global $PAGE;

        $PAGE->navbar->add($text, $action);
    }

    /**
     *
     * @param string $file
     */
    public static function execute_new($file) {
        global $CFG;

        if (strpos($file, $CFG->dirroot) !== 0) {
            print_error('cantexecutepage', ke::COMPONENT);
        }

        $url = substr($file, strlen($CFG->dirroot));
        if ($CFG->ostype == 'WINDOWS') {
            $url = str_replace('\\', '/', $url);
        }
        $page = new static($url);
        $page->execute();
    }
}
