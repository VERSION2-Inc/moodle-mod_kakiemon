<?php
namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';
require_once $CFG->libdir . '/formslib.php';
require_once $CFG->libdir . '/pear/HTML/QuickForm/input.php';
require_once $CFG->dirroot . '/grade/grading/lib.php';
require_once $CFG->libdir . '/gradelib.php';

class page_grade extends page {
    private $advancedgradinginstance;

    public function execute() {
        $this->view();
    }

    private function view() {
        global $USER;

        // TODO 評定フォーム定義できてないとエラーになるから定義済んでるかチェック
        $advancedgradinginstanceid = optional_param('advancedgradinginstanceid', 0, PARAM_INT);
        $pageid = required_param('page', PARAM_INT);
        $page = $this->db->get_record(ke::TABLE_PAGES, array('id' => $pageid));

        $customdata = (object)array(
                'ke' => $this->ke,
                'pageid' => $pageid,
                'page' => $page
        );

        $manager = get_grading_manager($this->ke->context, ke::COMPONENT, ke::GRADING_AREA_PAGE);
        $method = $manager->get_active_method();
        if ($method) {
            /* @var $controller \gradingform_rubric_controller */
        	$controller = $manager->get_controller($method);
        }
        if (!isset($customdata->advancedgradinginstance)) {
        	$customdata->advancedgradinginstance = new \stdClass();
        }
        if ($customdata->advancedgradinginstance) {
        	$gradinginstance = $controller->get_or_create_instance($advancedgradinginstanceid, $USER->id, $pageid);
        	$customdata->advancedgradinginstance = $gradinginstance;
        }
        $form = new form_grade(null, $customdata);

        if ($form->is_cancelled()) {
            redirect($this->ke->url('page_view', array('page' => $data->page)));
        } else if ($data = $form->get_data()) {
            if (isset($gradinginstance)) {
            	$_POST['xgrade'] = $gradinginstance->submit_and_get_grade($data->advancedgrading, $pageid);
            }



//             grade_update('mod/kakiemon', $this->ke->course, 'mod', 'kakiemon', $this->ke->instance, 0);

            redirect($this->ke->url('page_view', array('page' => $data->page)));
        }

        echo $this->output->header();

        $form->display();

        echo $this->output->footer();
    }
}

class form_grade extends \moodleform {
    public function definition() {


        /* @var $ke ke */
        $ke = $this->_customdata->ke;
        $page = $this->_customdata->page;

        if ($this->_customdata->advancedgradinginstance) {
        	$instanceid = $this->_customdata->advancedgradinginstance->get_id();
        }

        $f = $this->_form;

        $f->addElement('hidden', 'id', $ke->cmid);
        $f->setType('id', PARAM_INT);
        $f->addElement('hidden', 'page', $this->_customdata->pageid);
        $f->setType('page', PARAM_INT);

        $f->addElement('header', 'gradehdr', 'Grade');

//         $gradinginfo = grade_get_grades($ke->course->id, 'mod', 'kakiemon', $ke->instance, $page->userid);
//         var_dump($gradinginfo);

        if ($instanceid) {
            $f->addElement('hidden', 'advancedgradinginstanceid', $instanceid);
            $f->setType('advancedgradinginstanceid', PARAM_INT);

            $f->addElement('grading', 'advancedgrading', get_string('grade'), array(
                    'gradinginstance' => $this->_customdata->advancedgradinginstance
            ));

//             $f->addElement('static', 'currentgrade', ke::str('currentgrade'));
            $f->addElement('editor', 'feedback', get_string('feedback'));
        } else {
            echo '申し訳ありません。シンプル評定は利用できません。';
        }

        $this->add_action_buttons();
    }
}

page_grade::execute_new(__FILE__);
