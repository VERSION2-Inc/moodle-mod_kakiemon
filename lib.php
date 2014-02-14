<?php
defined('MOODLE_INTERNAL') || die();

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

    send_stored_file($file, DAYSECS, 0, $forcedownload, $options);
}
