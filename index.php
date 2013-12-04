<?php
namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';

// TODO コースid 0のとき？フロントページ？
$id = optional_param('id', 0, PARAM_INT);

if ($id) {
	if (!($course = $DB->get_record('course', array('id' => $id)))) {
		print_error('invalidcourseid');
	}
} else {
	$course = get_site();
}

require_course_login($course);

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/kakiemon/index.php', array('id' => $id));
$PAGE->set_title($course->shortname.': '.ke::str('modulenameplural'));
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(ke::str('modulenameplural'));

$coursecontext = \context_course::instance($course->id);

add_to_log($course->id, 'kakiemon', 'view kakiemons', "index.php?id=$course->id");

echo $OUTPUT->header();

if (!($kakiemons = get_all_instances_in_course('kakiemon', $course))) {
	notice(get_string('thereareno', '', ke::str('modulenameplural')), "$CFG->wwwroot/course/view.php?id=$course->id");
	exit();
}

$table = new \html_table();
$table->attributes['class'] = 'generaltable mod_index';
$table->head = array(
		'',
		ke::str('modulename'),
		'説明'
);

$modinfo = get_fast_modinfo($course);
foreach ($kakiemons as $kakiemon) {
	$cm = $modinfo->cms[$kakiemon->coursemodule];
	$table->data[] = array(
			'',
			$OUTPUT->action_link('/mod/kakiemon/view.php', $kakiemon->name),
			format_module_intro('kakiemon', $kakiemon, $cm->id)
	);
}

echo \html_writer::table($table);

echo $OUTPUT->footer();
