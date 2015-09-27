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

        // PDF 生成用に自動ログイン
        $keystr = optional_param('key', '', PARAM_RAW);
        if ($keystr !== '') {
            $pageid = required_param('page', PARAM_INT);
            $userid = required_param('userid', PARAM_INT);
            if (!$this->ke->is_valid_page_key($pageid, $userid, $keystr))
                throw new \moodle_exception('error', ke::COMPONENT);

            $user = $DB->get_record('user', array('id' => $userid));
            complete_user_login($user);
        }

        if ($this->ispublic) {
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
     * @param string $html
     * @param string $format
     */
//     protected function output_pdf($html, $title) {
//         global $CFG;

//         $tmpdir = $CFG->tempdir.'/kakiemon/pdf';
//         if (!file_exists($tmpdir))
//             mkdir($tmpdir, 0777, true);
//         do
//             $htmlpath = $tmpdir.'/'.mt_rand(0, 999999).'.html';
//         while (file_exists($htmlpath));
//         file_put_contents($htmlpath, $html);
//         $pdfpath = preg_replace('/\.html$/', '.pdf', $htmlpath);

//         $cmd = escapeshellarg($this->ke->config->wkhtmltopdf)
//             . ' ' . $this->ke->config->wkhtmltopdfoptions
//             . ' ' . escapeshellarg($htmlpath)
//             . ' ' . escapeshellarg($pdfpath);

//         exec($cmd);

//         @unlink($htmlpath);

//         if (!file_exists($pdfpath)) {
//             echo ke::str('cantoutputpdf');
//             exit;
//         }

//         send_file($pdfpath, $title.'.pdf', 0);
//     }

    protected function output_pdf($title) {
        global $CFG, $USER;

        $tmpdir = $CFG->tempdir.'/kakiemon/pdf';
        if (!file_exists($tmpdir))
            mkdir($tmpdir, 0777, true);

        do {
            $htmlpath = $tmpdir.'/'.mt_rand(0, 999999).'.html';
        } while (file_exists($htmlpath));
        $pdfpath = preg_replace('/\.html$/', '.pdf', $htmlpath);

        $url = new \moodle_url($this->url, array(
            'userid' => $USER->id,
            'key' => $this->ke->create_page_key($this->url->get_param('page'), $USER->id)
        ));

        $cmd = escapeshellarg($this->ke->config->wkhtmltopdf)
            . ' ' . $this->ke->config->wkhtmltopdfoptions
            . ' ' . escapeshellarg($url->out(false))
            . ' ' . escapeshellarg($pdfpath);

//         echo $cmd;die;

        exec($cmd);

        if (!file_exists($pdfpath)) {
            echo ke::str('cantoutputpdf');
            exit;
        }

        send_file($pdfpath, $title.'.pdf', 0);
    }

    /**
     *
     * @param string $file
     */
    public static function execute_new($file) {
        global $CFG;

        if (strpos($file, $CFG->dirroot) !== 0)
            throw new \moodle_exception('cantexecutepage', ke::COMPONENT);

        $url = substr($file, strlen($CFG->dirroot));
        if ($CFG->ostype == 'WINDOWS') {
            $url = str_replace('\\', '/', $url);
        }
        $page = new static($url);
        $page->execute();
    }
}
