<?php
use ver2\kakiemon\ke;
use ver2\kakiemon\mobile_key;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/mod/kakiemon/class/autoload.php';

/**
 *
 * @param \stdClass $kakiemon
 * @param mod_form_mod_form $form
 * @return int
 */
function kakiemon_add_instance($kakiemon, $form) {
    global $DB;

    return $DB->insert_record('kakiemon', $kakiemon);
}

/**
 *
 * @param \stdClass $kakiemon
 * @param mod_form_mod_form $form
 * @return boolean
 */
function kakiemon_update_instance($kakiemon, $form) {
    global $DB;

    $kakiemon->id = $kakiemon->instance;
    $DB->update_record('kakiemon', $kakiemon);

    return true;
}

/**
 *
 * @param int $id
 * @return boolean
 */
function kakiemon_delete_instance($id) {
    global $DB;

    $DB->delete_records('kakiemon', array('id' => $id));
    $DB->delete_records('kakiemon_pages', array('kakiemon' => $id));
    $DB->delete_records('kakiemon_blocks', array('kakiemon' => $id));
    $DB->delete_records('kakiemon_likes', array('kakiemon' => $id));
    $DB->delete_records('kakiemon_accesses', array('kakiemon' => $id));

    return true;
}

/**
 *
 * @param string $feature
 * @return boolean
 */
function kakiemon_supports($feature) {
    return in_array($feature, array(
        FEATURE_MOD_INTRO,
        FEATURE_GRADE_HAS_GRADE,
        FEATURE_ADVANCED_GRADING
    ));
}

/**
 * @return string[]
 */
function kakiemon_grading_areas_list() {
    return array(
            'page' => get_string('page', 'mod_kakiemon')
    );
}

function kakiemon_grade_item_update($ke, $grades = null) {
//     $params = array(
//             'itemname' => $ke->name,
//             'idnumber' => $ke->cmidnumber
//     );

//     if ($ke->scale == 0) {
//     	$params['gradetype'] = GRADE_TYPE_NONE;
//     } else if ($ke->scale > 0) {
//         $params['gradetype'] = GRADE_TYPE_VALUE;
//         $params['grademax'] = $ke->scale;
//         $params['grademin'] = 0;
//     } else if ($ke->scale < 0) {
//         $params['gradetype'] = GRADE_TYPE_SCALE;
//         $params['scaleid'] = -$ke->scale;
//     }

//     if ($grades === 'reset') {
//         $params['reset'] = true;
//         $grades = null;
//     }

//     return grade_update('mod/kakiemon', $ke->course, 'mod', 'kakiemon', $ke->id, 0, $grades, $params);
}

function kakiemon_update_grades($ke, $userid = 0, $nullifnone = true) {
}

function kakiemon_cron() {
    mtrace(ke::str('deletingoldkeys'));
    mobile_key::delete_expired();
}

/**
 *
 * @param \stdClass $course
 * @param \stdClass $cm
 * @param context_module $context
 * @param string $filearea
 * @param array $args
 * @param boolean $forcedownload
 * @param array $options
 */
function mod_kakiemon_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'wkhtmltopdf') === false) {
        if ($context->contextlevel != CONTEXT_MODULE) {
            return false;
        }

        if ($filearea !== 'blockfile') {
            return false;
        }

        require_login($course, true, $cm);

        if (!has_capability('mod/kakiemon:view', $context)) {
            return false;
        }
    }

    $itemid = array_shift($args);

    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_kakiemon', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}
