<?php
namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';
require_once $CFG->libdir . '/formslib.php';
require_once $CFG->libdir . '/pear/HTML/QuickForm/input.php';
require_once $CFG->dirroot . '/grade/grading/lib.php';

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

        $formdata = (object)array(
                'ke' => $this->ke,
                'pageid' => $pageid
        );

        $manager = get_grading_manager($this->ke->context, ke::COMPONENT, ke::GRADING_AREA_PAGE);
        $method = $manager->get_active_method();
        if ($method) {
            /* @var $controller \gradingform_rubric_controller */
        	$controller = $manager->get_controller($method);
        }
        if (!isset($formdata->advancedgradinginstance)) {
        	$formdata->advancedgradinginstance = new \stdClass();
        }
        if ($formdata->advancedgradinginstance) {
        	$gradinginstance = $controller->get_or_create_instance($advancedgradinginstanceid, $USER->id, $pageid);
        	$formdata->advancedgradinginstance = $gradinginstance;
        }
        $form = new form_grade(null, $formdata);

        echo $this->output->header();

        $form->display();

        echo $this->output->footer();
    }
}

class form_grade extends \moodleform {
    public function definition() {
        /* @var $ke ke */
        $ke = $this->_customdata->ke;
        if ($this->_customdata->advancedgradinginstance) {
        	$isntanceid = $this->_customdata->advancedgradinginstance;
        }

        $f = $this->_form;

        $f->addElement('hidden', 'id', $ke->cmid);
        $f->setType('id', PARAM_INT);
        $f->addElement('hidden', 'page', $this->_customdata->pageid);
        $f->setType('page', PARAM_INT);

        $f->addElement('header', 'gradehdr', 'Grade');


        $f->addElement('grading', 'advancedgrading', 'Adv Grade', array(
                'gradinginstance' => $this->_customdata->advancedgradinginstance
        ));
//         $f->addElement('hidden', 'advancedgradinginstanceid', '0');

        $this->add_action_buttons();
    }
}

page_grade::execute_new(__FILE__);
