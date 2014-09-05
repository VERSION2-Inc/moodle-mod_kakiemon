<?php
namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';
require_once $CFG->dirroot . '/mod/kakiemon/lib/phpqrcode/qrlib.php';

switch (required_param('page', PARAM_ALPHA)) {
    case 'videoupload':
        $instance = required_param('instance', PARAM_INT);
        $blockid = required_param('block', PARAM_INT);
        $key = mobile_key::create($instance, 'videoupload', $blockid);

        $uploadurl = new \moodle_url('/mod/kakiemon/mobile/videoupload.php', array(
            'key' => $key->keystring
        ));
        $text = $uploadurl->out(false);
        break;

    default:
        die;
}

\QRcode::png($text);
