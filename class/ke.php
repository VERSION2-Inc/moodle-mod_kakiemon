<?php
namespace ver2\kakiemon;

defined('MOODLE_INTERNAL') || die();

class ke {
    const COMPONENT = 'mod_kakiemon';
    const MODULE_DIR = '/mod/kakiemon/';

    const KEY = 'kakiemon';
    const TABLE_MOD = 'kakiemon';
    const TABLE_PAGES = 'kakiemon_pages';
    const TABLE_BLOCKS = 'kakiemon_blocks';
    const TABLE_LIKES = 'kakiemon_likes';
    const TABLE_ACCESSES = 'kakiemon_accesses';
    const TABLE_RATINGS = 'kakiemon_ratings';
    const TABLE_GRADES = 'kakiemon_grades';
    const TABLE_FEEDBACKS = 'kakiemon_feedbacks';
    const TABLE_MOBILE_KEYS = 'kakiemon_mobile_keys';
    const TABLE_PAGE_KEYS = 'kakiemon_page_keys';

    const CAP_VIEW = 'mod/kakiemon:view';
    const CAP_CREATE_TEMPLATE = 'mod/kakiemon:createtemplate';

    const FILE_ICON_SIZE = 24;

    const GRADING_AREA_PAGE = 'page';

    const SHARE_COURSE = 'course';
    const SHARE_LOGIN = 'login';
    const SHARE_PUBLIC = 'public';

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
    public $blocks = array();
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
     * @var \moodle_database
     */
    private $db;
    /**
     *
     * @var \stdClass
     * @deprecated
     */
    public $options;
    /**
     *
     * @var \stdClass
     */
    public $data;
    /**
     *
     * @var \stdClass
     */
    public $course;
    public $config;

    /**
     *
     * @param int $cmid
     */
    public function __construct($cmid) {
        global $DB;

        $this->db = $DB;
        $this->cm = get_coursemodule_from_id(self::TABLE_MOD, $cmid);
        $this->course = $this->db->get_record('course', array('id' => $this->cm->course));
        $this->cmid = $this->cm->id;
        $this->instance = $this->cm->instance;
        $this->context = \context_module::instance($this->cm->id);
        $this->data = $this->db->get_record(self::TABLE_MOD, array('id' => $this->instance));
        $this->options = $this->data;
        $this->config = get_config('kakiemon');

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
     * @param string $capability
     * @return boolean
     */
    public function has_capability($capability) {
        return has_capability($capability, $this->context);
    }

    /**
     *
     * @param int $pageid
     * @return boolean
     */
    public static function is_page_editable($pageid) {
        global $DB, $USER;

        $page = $DB->get_record(self::TABLE_PAGES, array('id' => $pageid));

        return $page->userid == $USER->id;
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
     * @param string|\moodle_url $url
     * @param array $params
     * @param string $anchor
     * @return \moodle_url
     */
    public function url($url, array $params = array(), $anchor = null) {
        if ($url instanceof \moodle_url) {
            return new \moodle_url($url, $params, $anchor);
        }

        if (!preg_match(',/$,', $url) && strpos(basename($url), '.') === false) {
            $url .= '.php';
        }
        if ($url[0] != '/') {
            $url = self::MODULE_DIR.$url;
        }

        $params = array('id' => $this->cm->id) + $params;

        return new \moodle_url($url, $params, $anchor);
    }

    public function get_template_page() {
        return $this->db->get_record(self::TABLE_PAGES, array(
                'kakiemon' => $this->instance,
                'template' => 1
        ));
    }

    public function copy_page($srcpage, $dstpage) {
        global $USER;

        $blocks = $this->db->get_records(self::TABLE_BLOCKS, array(
                'page' => $srcpage->id
        ), 'blockcolumn, blockorder');
        $userid = $USER->id;
        $fs = get_file_storage();

        foreach ($blocks as $block) {
            $oldid = $block->id;
            unset($block->id);
            $block->page = $dstpage->id;
            $newid = $this->db->insert_record(self::TABLE_BLOCKS, $block);

            if ($files = $fs->get_area_files($this->context->id, self::COMPONENT, 'blockfile', $oldid,
                    'filepath, filename', false)) {
                foreach ($files as $file) {
                    $fs->create_file_from_storedfile(array(
                            'itemid' => $newid
                    ), $file);
                }
            }
        }
    }

    public function update_access($page) {
        global $USER;

        $userid = $USER->id;

//         if (!$this->options->showtracks || $page->userid == $userid) {
//             return;
//         }

        $pageid = $page->id;
//         $daystart = strtotime('00:00');
//         if ($access = $this->db->get_record_select(self::TABLE_ACCESSES,
//                     'page = :page AND userid = :userid AND timeaccessed >= :daystart',
//                     array(
//                             'page' => $pageid,
//                             'userid' => $userid,
//                             'daystart' => $daystart
//                     )
//         )) {
//             $access->timeaccessed = time();
//             $this->db->update_record(self::TABLE_ACCESSES, $access);
//         } else {
            $access = (object)array(
                    'kakiemon' => $this->instance,
                    'page' => $pageid,
                    'userid' => $userid,
                    'timeaccessed' => time()
            );
            $this->db->insert_record(self::TABLE_ACCESSES, $access);
//         }
    }

    /**
     *
     * @param string $action
     * @param string $url
     * @param string $info
     * @param string $cm
     * @param string $user
     */
    public function log($action, $url = '', $info = '', $cm = '', $user = '') {
        if ($url instanceof \moodle_url) {
            $url = $url->out_as_local_url(false);
        }
        $url = preg_replace(',^/mod/kakiemon/,', '', $url);

        add_to_log($this->cm->course, 'kakiemon', $action, $url, $info, $cm, $user);
    }

    /**
     *
     * @return boolean
     */
    public function can_create_page() {
        return $this->is_in_period($this->options->createstartdate, $this->options->createenddate);
    }

    /**
     *
     * @return boolean
     */
    public function can_view_page() {
        return $this->is_in_period($this->options->viewstartdate, $this->options->viewenddate);
    }

    /**
     *
     * @param int $start
     * @param int $end
     * @return boolean
     */
    private function is_in_period($start, $end) {
        $now = time();

        return (!$start || $now >= $start) && (!$end || $now < $end);
    }

    /**
     *
     * @return array
     */
    public function get_grading_info() {
        global $CFG;
        require_once $CFG->libdir . '/gradelib.php';

        return grade_get_grades($this->course->id, 'mod', 'kakiemon', $this->instance);
    }

    public function create_page_key($pageid, $userid) {
        global $DB;

        $key = (object)array(
            'kakiemon' => $this->instance,
            'page' => $pageid,
            'userid' => $userid,
            'keystring' => random_string(10),
            'expires' => time() + MINSECS
        );
        $DB->insert_record(self::TABLE_PAGE_KEYS, $key);

        return $key->keystring;
    }

    public function is_valid_page_key($pageid, $userid, $keystr) {
        global $DB;

        return $DB->record_exists_select(
            self::TABLE_PAGE_KEYS,
            'kakiemon = :kakiemon
            AND page = :page
            AND userid = :userid
            AND keystring = :keystr
            AND expires > :now',
            array(
                'kakiemon' => $this->instance,
                'page' => $pageid,
                'userid' => $userid,
                'keystr' => $keystr,
                'now' => time()
            ));
    }
}
