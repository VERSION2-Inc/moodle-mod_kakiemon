<?php
namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';

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
     * @param string $url
     */
    public function __construct($url) {
        $this->ispublic = true;

        parent::__construct($url);
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
            default:
                $this->view();
        }
    }

    private function view() {
        global $DB, $PAGE, $SESSION, $USER;

        $userid = $USER->id;

        $PAGE->blocks->show_only_fake_blocks();

        $pageid = required_param('page', PARAM_INT);

        $opage = new ke_page_cls($this->ke, $pageid);

        if (!$opage->is_viewable()) {
            print_error('cantviewpage', ke::COMPONENT);
        }

        if (!ke::is_page_editable($pageid)) {
            $SESSION->kakiemon_editing = false;
        }
        $editing = false;
        if (!empty($SESSION->kakiemon_editing)) {
            $editing = true;
        }

        $jsparams = (object)array(
                'cmid' => $this->cmid,
                'editing' => $editing
        );
        $PAGE->requires->js_init_call('M.mod_kakiemon.page_view_init', array($jsparams), false,
                array(
                        'name' => 'mod_kakiemon',
                        'fullpath' => '/mod/kakiemon/module.js',
                        'requires' => array('dd', 'io')
                ));

        $PAGE->requires->css('/mod/kakiemon/lib/lightbox/css/lightbox.css');

        $page = $DB->get_record(ke::TABLE_PAGES, array(
                'id' => $pageid
        ));

        $this->ke->update_access($page);

        $title = $page->name;

        $this->add_navbar($title);

        echo $this->output->header();
        echo $this->output->heading($title);

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
            echo $this->output->action_link($this->ke->url('grade', array('page' => $pageid)), ke::str('gradepage'));
            echo \html_writer::end_tag('div');
        }
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

                $oblock = $this->ke->get_block_type($block->type);
                $ob .= $oblock->get_content($block);

                echo \html_writer::tag('div', $ob, array(
                        'class' => 'kaki-block',
                        'data-id' => $block->id,
                        'data-column' => $block->blockcolumn,
                        'data-order' => $block->blockorder
                ));

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

        echo $this->output->footer();

        $this->ke->log('view page', $this->ke->url('page_view', array('page' => $page->id)), $page->name);
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
            if ($like->type != $type) {
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
}

page_page_view::execute_new(__FILE__);
