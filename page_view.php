<?php
namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';
require_once $CFG->libdir . '/formslib.php';

class page_page_view extends page {
    const LIKE = 'like';
    const DISLIKE = 'dislike';
    const BLOCK_COLUMNS = 3;

    /**
     *
     * @var form_page_edit
     */
    private $form;
    /**
     *
     * @var int
     */
    private $pageid;
    /**
     *
     * @var ke_page_cls
     */
    private $page;

    /**
     *
     * @param string $url
     */
    public function __construct($url) {
        $this->ispublic = true;

        parent::__construct($url);

        $this->page = new ke_page_cls($this->ke, required_param('page', PARAM_INT));
    }

    public function execute() {
        switch (optional_param('action', null, PARAM_ALPHA)) {
            case 'setediting':
                $this->set_editing();
                break;
            case 'like':
                $this->like();
                break;
            case 'rate':
                $this->rate();
                break;
            case 'comment':
                $this->comment();
                break;
            case 'feedbackdelete':
                $this->feedback_delete();
                break;
            default:
                $this->view();
        }
    }

    private function view() {
        global $DB, $PAGE, $SESSION, $USER;

        $download = optional_param('download', 0, PARAM_BOOL);
        $pdf = optional_param('pdf', 0, PARAM_BOOL);

        $userid = $USER->id;

        $PAGE->set_pagelayout('report');
        $PAGE->blocks->show_only_fake_blocks();

        if (ke::is_output_pdf()) {
            $PAGE->add_body_class('pdf');
        }

        $pageid = required_param('page', PARAM_INT);

        $this->url->param('page', $pageid);

        $this->page = new ke_page_cls($this->ke, $pageid);
        $opage = $this->page;

        if (!$this->ke->can_view_page() || !$opage->is_viewable())
            throw new \moodle_exception('cantviewpage', ke::COMPONENT);

        if (!ke::is_page_editable($pageid)) {
            $SESSION->kakiemon_editing = false;
        }
        $editing = false;
        if (!empty($SESSION->kakiemon_editing)) {
            $editing = true;
        }

        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        $PAGE->requires->jquery_plugin('ui-css');
        $jqueryinit = 'var cmid = '.$this->cmid.';'
            .'var editing = '.($editing ? 'true' : 'false').';';
//         $PAGE->requires->js('/mod/kakiemon/js/page_view.js');
        $jsparams = (object)array(
            'cmid' => $this->cmid,
            'instance' => $this->ke->instance,
            'editing' => $editing
        );
        $PAGE->requires->js_init_call('M.mod_kakiemon.page_view_init', array($jsparams), false,
            array(
                'name' => 'mod_kakiemon',
                'fullpath' => '/mod/kakiemon/module.js',
                'requires' => array(
                    'dd',
                    'io',
                    'dd-drag',
                    'dd-proxy',
                    'panel',
                    'resize',
                    'resize-plugin',
                    'widget'
                )
            ));

        $PAGE->requires->css('/mod/kakiemon/lib/lightbox/css/lightbox.css');

        $page = $DB->get_record(ke::TABLE_PAGES, array(
            'id' => $pageid
        ));

        $this->ke->update_access($page);

        $title = $page->name;

        $this->add_navbar($title);

        if ($download) {
            // HTML ダウンロード
            $filename = $page->name.'.html';
            if (check_browser_version('MSIE')) {
                $filename = rawurlencode($filename);
            }
            header('Content-Disposition: attachment; filename="'.$filename.'"');

        } else if ($pdf) {
            // PDF ダウンロード
            $this->output_pdf($title);
            exit;
        }

        echo $this->output->header();
        echo $this->output->heading($title);

        echo \html_writer::script($jqueryinit);

        $user = $DB->get_record('user', array('id' => $page->userid));
        echo $this->output->container($user->idnumber.' '.fullname($user),
                'page-author');

        echo \html_writer::start_tag('div', array('class' => 'page-infolinks'));
        if ($this->ke->options->showtracks && $page->userid == $userid) {
            echo \html_writer::start_tag('div');
            echo $this->output->action_link($this->ke->url('accesses', array('page' => $pageid)),
                    ke::str('viewaccesses'));
            echo \html_writer::end_tag('div');
        }
        if (has_capability('mod/kakiemon:grade', $this->ke->context)) {
            echo \html_writer::start_tag('div');
            echo $this->output->action_link($this->ke->url('page_grade',
                    array('page' => $pageid)), ke::str('gradepage'));
            echo \html_writer::end_tag('div');
        }
        echo \html_writer::start_tag('div');
        echo $this->output->action_link($this->ke->url('page_view',
                array('page' => $pageid, 'download' => 1)), ke::str('downloadhtml'));
        echo \html_writer::end_tag('div');
        echo \html_writer::start_tag('div');
        echo $this->output->action_link($this->ke->url('page_view',
            array('page' => $pageid, 'pdf' => 1)), ke::str('downloadpdf'),
            null, array('target' => '_blank'));
        echo \html_writer::end_tag('div');
        echo \html_writer::end_tag('div');

        echo $this->output->container_start('likebuttons');
        $likeelement = 'span';
        if ($this->ke->options->uselike) {
            $likecount = $DB->count_records(ke::TABLE_LIKES, array(
                'page' => $pageid,
                'type' => self::LIKE
            ));
            echo $this->output->action_link(new \moodle_url($this->url, array(
                'page' => $pageid,
                'action' => 'like',
                'type' => self::LIKE
            )), ke::str('like'), null, array('class' => 'likebutton'));
            echo \html_writer::tag($likeelement, $likecount, array('class' => 'likecount'));
        }
        if ($this->ke->options->usedislike) {
            $dislikecount = $DB->count_records(ke::TABLE_LIKES, array(
                'page' => $pageid,
                'type' => self::DISLIKE
            ));
            echo $this->output->action_link(new \moodle_url($this->url, array(
                'page' => $pageid,
                'action' => 'like',
                'type' => self::DISLIKE
            )), ke::str('dislike'), null, array('class' => 'likebutton'));
            echo \html_writer::tag($likeelement, $dislikecount, array('class' => 'likecount'));
        }
        echo $this->output->container_end();

        if ($this->ke->data->userating) {
            echo \html_writer::start_tag('div', array('id' => 'rating'));
            echo ke::str('rating').': ';
            $averagerating = $opage->get_average_rating();
            echo $this->print_rating($averagerating);
            echo sprintf('%g', $opage->get_average_rating());
            echo ' ('.ke::str('ratedbyusers', $opage->get_rated_users()).')';
            $ratingselect = new \single_select(
                $this->ke->url('page_view', array('action' => 'rate', 'page' => $pageid)), 'rating',
                array(
                    5 => '5',
                    4 => '4',
                    3 => '3',
                    2 => '2',
                    1 => '1',
                    0 => ke::str('norating')
                ),
                $opage->get_my_rating()
            );
            $ratingselect->label = ke::str('yourrating').': ';
            echo $this->output->render($ratingselect);
            echo \html_writer::end_tag('div');
        }

        if ($editing) {
            echo $this->output->container(
                $this->output->single_button(
                    new \moodle_url($this->url, array(
                        'page' => $pageid,
                        'action' => 'setediting',
                        'editing' => 'off'
                    )), ke::str('finisheditingthispage')),
                'editbutton');

//             echo \html_writer::start_tag('ul', array('id' => 'newblocks'));
//             foreach ($this->ke->blocks as $type => $name) {
//                 echo \html_writer::tag('li', $name);
//             }
//             echo \html_writer::end_tag('ul');
        } else {
            if (ke::is_page_editable($pageid)) {
                echo $this->output->container(
                    $this->output->single_button(
                        new \moodle_url($this->url, array(
                            'page' => $pageid,
                            'action' => 'setediting',
                            'editing' => 'on'
                        )), ke::str('editthispage')),
                    'editbutton');
            }
        }
        echo $this->output->container('', 'clearer');

        echo $this->output->container_start('block-columns');

        for ($column = 1; $column <= 3; $column++) {
            echo $this->output->container_start('block-column', 'column'.$column);
            echo \html_writer::start_tag('div', array(
                'class' => 'block-column-blocks',
                'data-column' => $column
            ));

            $blocks = $DB->get_records(ke::TABLE_BLOCKS, array(
                'page' => $pageid,
                'blockcolumn' => $column
            ), 'blockorder');
            $row = 0;
            foreach ($blocks as $block) {
                $ob = '';

                if ($editing) {
                    $buttons = '';

                    $buttons .= util::button(util::BUTTON_EDIT,
                        new \moodle_url($this->ke->url('block_edit', array(
                            'block' => $block->id,
                            'action' => 'edit',
                            'editmode' => 'update'
                        ))));
                    $buttons .= util::button(util::BUTTON_DELETE,
                        new \moodle_url($this->ke->url('block_edit', array(
                            'action' => 'delete',
                            'block' => $block->id
                        ))),
                        new \confirm_action(ke::str('reallydeleteblock')));

                    if ($row > 0) {
                        $buttons .= util::button(util::BUTTON_UP,
                            new \moodle_url($this->ke->url('block_edit', array(
                                'action' => 'changeorder',
                                'value' => -1,
                                'block' => $block->id
                            ))));
                    }
                    if ($row < count($blocks) - 1) {
                        $buttons .= util::button(util::BUTTON_DOWN,
                            new \moodle_url($this->ke->url('block_edit', array(
                                'action' => 'changeorder',
                                'value' => 1,
                                'block' => $block->id
                            ))));
                    }
                    if ($column > 0) {
                        $buttons .= util::button(util::BUTTON_LEFT,
                            new \moodle_url($this->ke->url('block_edit', array(
                                'action' => 'changecolumn',
                                'value' => -1,
                                'block' => $block->id
                            ))));
                    }
                    if ($column < self::BLOCK_COLUMNS) {
                        $buttons .= util::button(util::BUTTON_RIGHT,
                            new \moodle_url($this->ke->url('block_edit', array(
                                'action' => 'changecolumn',
                                'value' => 1,
                                'block' => $block->id
                            ))));
                    }
                    $ob .= $this->output->container($buttons, 'blockeditbuttons');
                }

                if ($block->displaytitle)
                    $ob .= $this->output->heading($block->title, 3);

                $oblock = $this->ke->get_block_type($block->type);
                $oblock->editing = $editing;
                $ob .= $oblock->get_content($block);

//                 echo '<div class="kaki-block-wrap" style="width:300px;height:300px;">';
                $style = '';
                if ($block->width)
                    $style .= 'width:'.$block->width.'px;';
                if ($block->height)
                    $style .= 'height:'.$block->height.'px;';
                echo \html_writer::tag('div', $ob, array(
                    'class' => 'kaki-block kaki-block-'.$block->type,
                    'style' => $style,
                    'data-id' => $block->id,
                    'data-column' => $block->blockcolumn,
                    'data-order' => $block->blockorder
                ));
//                 echo '</div>';

                $row++;
            }
            echo \html_writer::end_tag('div');

            if ($editing) {
                echo $this->output->container(ke::str('addblock'));
                echo $this->output->single_select(
                    $this->ke->url('block_edit',
                        array(
                            'action' => 'edit',
                            'editmode' => 'add',
                            'page' => $pageid,
                            'blockcolumn' => $column
                )), 'type', $this->ke->blocks);
            }

            echo $this->output->container_end();
        }

        echo $this->output->container_end();

        if ($page->userid == $USER->id || has_capability('mod/kakiemon:grade', $this->ke->context)) {
            echo '<div style="clear: both"></div>';
            $gradinginfo = $this->ke->get_grading_info();
            $manager = get_grading_manager($this->ke->context, ke::COMPONENT, ke::GRADING_AREA_PAGE);
            $method = $manager->get_active_method();
            if ($method) {
                /* @var $controller \gradingform_rubric_controller */
                $controller = $manager->get_controller($method);
                if ($controller->is_form_available()) {
                    echo $controller->render_grade($PAGE, $pageid, $gradinginfo, '', false);
                }
            }
            if ($grade = $opage->get_grade()) {
                echo \html_writer::start_tag('div', array('class' => 'page-grade'));
                echo \html_writer::start_tag('div');
                echo \html_writer::tag('strong', get_string('grade'));
                echo ': ';
                echo sprintf('%g', $grade->grade);
                echo \html_writer::end_tag('div');
                echo \html_writer::start_tag('div');
                echo format_text($grade->feedback);
                echo \html_writer::end_tag('div');
                echo \html_writer::end_tag('div');
            }
        }

        $this->pageid = $pageid;
        $this->view_feedback();

        echo \html_writer::tag('div', '', array('id' => 'blockedit'));

        echo $this->output->footer();

        if ($pdf) {
            $htmlforpdf = ob_get_contents();
            ob_end_clean();
            $this->output_pdf($htmlforpdf, $title);
        }
        $this->ke->log('view page', $this->ke->url('page_view', array('page' => $page->id)), $page->name);
    }

    private function view_feedback() {
        global $USER;

        $feedbackpage = optional_param('feedbackpage', 0, PARAM_INT);
        $perpage = 10;

        echo $this->output->heading(ke::str('feedback'), 5);
        echo \html_writer::start_div('feedbacks', array('id' => 'feedbacks'));

        $feedbacks = $this->db->get_records_sql('
            SELECT c.*
            FROM {'.ke::TABLE_FEEDBACKS.'} c
            WHERE c.kakiemon = :kakiemon AND c.page = :page
            ORDER BY timemodified DESC
            ',
            array(
                'kakiemon' => $this->ke->instance,
                'page' => $this->pageid
            ),
            $feedbackpage * $perpage, $perpage
        );
        $delicon = new \pix_icon('t/delete', get_string('delete'));
        foreach ($feedbacks as $feedback) {
            echo '<table class="feedback"><tr><td class="userpiccell">';

            $user = $this->db->get_record('user', array('id' => $feedback->userid),
                    \user_picture::fields(), MUST_EXIST);
            echo $this->output->user_picture($user);

            echo '</td><td class="namecomment">';

            if ($this->page->can_delete_feedback($feedback)) {
                echo \html_writer::start_div('actionicons');
                echo $this->output->action_icon(
                    $this->ke->url('page_view',
                        array(
                            'action' => 'feedbackdelete',
                            'page' => $this->pageid,
                            'feedback' => $feedback->id
                        )
                    ),
                    $delicon,
                    new \confirm_action(ke::str('reallydeletefeedback'))
                );
                echo \html_writer::end_div();
            }

            echo \html_writer::div(fullname($user).' - '.userdate($feedback->timemodified), 'namedate');
            echo \html_writer::div(format_text($feedback->comments, $feedback->commentsformat), 'comment');
            echo '</td></tr></table>';
        }

        $numfeedbacks = $this->db->count_records(ke::TABLE_FEEDBACKS,
            array(
                'kakiemon' => $this->ke->instance,
                'page' => $this->pageid
            ));
        $baseurl = $this->ke->url('page_view',
            array(
                'page' => $this->pageid
            ),
            'feedbacks');
        echo $this->output->paging_bar($numfeedbacks, $feedbackpage, $perpage, $baseurl, 'feedbackpage');

        echo \html_writer::div(
            \html_writer::link('#feedbackform', ke::str('postfeedback')),
            '', array('id' => 'showfeedbackform'));
        $customdata = (object)array(
            'cmid' => $this->cmid,
            'pageid' => $this->pageid
        );
        $commentform = new form_page_comment(null, $customdata, 'post', '', array('id' => 'feedbackform'));
        $commentform->display();

        echo \html_writer::end_div();
    }

    private function set_editing() {
        global $SESSION;

        if (required_param('editing', PARAM_ALPHA) == 'on') {
            $SESSION->kakiemon_editing = true;
        } else {
            $SESSION->kakiemon_editing = false;
        }

        redirect($this->ke->url('page_view.php', array(
            'page' => required_param('page', PARAM_INT)
        )));
    }

    private function like() {
        global $DB, $USER;

        $userid = $USER->id;
        $pageid = required_param('page', PARAM_INT);
        $type = required_param('type', PARAM_ALPHA);

        $url = new \moodle_url($this->url, array('page' => $pageid));

        $like = $DB->get_record(ke::TABLE_LIKES, array(
            'page' => $pageid,
            'userid' => $userid
        ));
        $updated = false;
        if ($like) {
            if ($like->type == $type) {
                $this->db->delete_records(ke::TABLE_LIKES, array('id' => $like->id));
                $updated = true;
            } else {
                $like->type = $type;
                $DB->update_record(ke::TABLE_LIKES, $like);
                $updated = true;
            }
        } else {
            $like = (object)array(
                'kakiemon' => $this->ke->instance,
                'page' => $pageid,
                'userid' => $userid,
                'type' => $type
            );
            $DB->insert_record(ke::TABLE_LIKES, $like);
            $updated = true;
        }

        if ($updated) {
            $this->ke->log('like page', $url, $type);
        }

        redirect($url);
    }

    private function rate() {
        global $DB, $USER;

        $pageid = required_param('page', PARAM_INT);
        $userid = $USER->id;
        $rating = required_param('rating', PARAM_INT);

        if ($row = $DB->get_record(ke::TABLE_RATINGS, array(
            'kakiemon' => $this->ke->instance,
            'page' => $pageid,
            'userid' => $userid))) {
            $row->rating = $rating;
            $row->timemodified = time();
            $DB->update_record(ke::TABLE_RATINGS, $row);
        } else {
            $row = (object)array(
                'kakiemon' => $this->ke->instance,
                'page' => $pageid,
                'userid' => $userid,
                'rating' => $rating,
                'timemodified' => time()
            );
            $DB->insert_record(ke::TABLE_RATINGS, $row);
        }

        redirect($this->ke->url('page_view', array('page' => $pageid)));
    }

    /**
     *
     * @param float $rating
     * @return string
     */
    private function print_rating($rating) {
        $rating = round($rating);

        $o = '';
        $pix = new \pix_icon('star', '★', ke::COMPONENT);
        for ($i = 0; $i < $rating; $i++) {
            $o .= $this->output->render($pix);
        }

        return $o;
    }

    private function comment() {
        global $USER;

        $customdata = (object)array(
            'cmid' => $this->cmid,
            'pageid' => required_param('page', PARAM_INT)
        );
        $form = new form_page_comment(null, $customdata);
        $data = $form->get_data();

        $feedback = (object)array(
            'kakiemon' => $this->ke->instance,
            'page' => $data->page,
            'userid' => $USER->id,
            'comments' => $data->comment['text'],
            'commentsformat' => $data->comment['format'],
            'timemodified' => time()
        );
        $this->db->insert_record(ke::TABLE_FEEDBACKS, $feedback);

        redirect($this->ke->url('page_view', array('page' => $data->page), 'feedbacks'));
    }

    private function feedback_delete() {
        $feedbackid = required_param('feedback', PARAM_INT);
        if ($this->page->can_delete_feedback($feedbackid)) {
            $this->db->delete_records(ke::TABLE_FEEDBACKS, array('id' => $feedbackid));
        }
        redirect($this->ke->url('page_view', array('page' => $this->page->id), 'feedbacks'));
    }
}

class form_page_comment extends \moodleform {
    protected function definition() {
        $f = $this->_form;

        $f->addElement('hidden', 'id', $this->_customdata->cmid);
        $f->setType('id', PARAM_INT);
        $f->addElement('hidden', 'action', 'comment');
        $f->setType('action', PARAM_ALPHA);
        $f->addElement('hidden', 'page', $this->_customdata->pageid);
        $f->setType('page', PARAM_INT);

        $f->addElement('editor', 'comment', ke::str('message'),
            array(
                'cols' => 40,
                'rows' => 10
            )
        );

        $this->add_action_buttons(false, ke::str('postfeedback'));
    }
}

page_page_view::execute_new(__FILE__);
