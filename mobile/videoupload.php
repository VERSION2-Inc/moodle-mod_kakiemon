<?php
namespace ver2\kakiemon;

require_once '../../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';

try {
    $keystr = required_param('key', PARAM_ALPHANUM);
    $key = mobile_key::get($keystr);
    if (!$key)
        throw new \moodle_exception('qrcodeexpired', ke::COMPONENT);

    if (!empty($_FILES['video'])) {
        $cm = get_coursemodule_from_instance('kakiemon', $key->kakiemon, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);

        $fs = get_file_storage();

        $finfo = array(
            'contextid' => $context->id,
            'component' => ke::COMPONENT,
            'filearea' => 'blockfile',
            'itemid' => $key->block,
            'filepath' => '/',
            'filename' => $_FILES['video']['name']
        );

        $fs->delete_area_files($finfo['contextid'], $finfo['component'], $finfo['filearea'],
            $finfo['itemid']);

        $fs->create_file_from_pathname($finfo, $_FILES['video']['tmp_name']);

        $title = 'ok';
        $message = 'ビデオをアップロードしました';
        include 'message.php';
    } else
        include 'videoupload_form.php';

} catch (\moodle_exception $e) {
    $error = $e->getMessage();
    include 'error.php';
}
