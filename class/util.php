<?php
namespace ver2\kakiemon;

defined('MOODLE_INTERNAL') || die();

class util {
    const BUTTON_EDIT = 0;
    const BUTTON_DELETE = 1;
    const BUTTON_UP = 2;
    const BUTTON_DOWN = 3;
    const BUTTON_LEFT = 4;
    const BUTTON_RIGHT = 5;

    private static $icons = array();

    /**
     *
     * @param string $table
     * @param int $id
     * @param string $keyfield
     * @return \stdClass
     */
    public static function get_record_for_form($table, $id, $keyfield) {
        global $DB;

        $row = $DB->get_record($table, array('id' => $id), '*', MUST_EXIST);

        $row->$keyfield = $row->id;
        unset($row->id);

        return $row;
    }

    public static function load_lightbox() {
        global $PAGE;

        $PAGE->requires->js(new \moodle_url('/mod/kakiemon/lib/lightbox/js/jquery-1.10.2.min.js'));
        $PAGE->requires->js(new \moodle_url('/mod/kakiemon/lib/lightbox/js/lightbox-2.6.min.js'));
    }

    /**
     *
     * @param int $type
     * @param string|\moodle_url $url
     * @param \component_action $action
     * @return string
     */
    public static function button($type, $url, \component_action $action = null) {
        global $OUTPUT;

        $icon = null;
        switch ($type) {
            case self::BUTTON_EDIT:
                $icon = new \pix_icon('t/edit', get_string('edit'));
                break;
            case self::BUTTON_DELETE:
                $icon = new \pix_icon('t/delete', get_string('delete'));
                break;
            case self::BUTTON_UP:
                $icon = new \pix_icon('t/up', get_string('up'));
                break;
            case self::BUTTON_DOWN:
                $icon = new \pix_icon('t/down', get_string('down'));
                break;
            case self::BUTTON_LEFT:
                $icon = new \pix_icon('t/left', get_string('moveleft'));
                break;
            case self::BUTTON_RIGHT:
                $icon = new \pix_icon('t/right', get_string('moveright'));
                break;
        }

        return $OUTPUT->action_icon($url, $icon, $action);
    }

    public static function fix_jpeg_orientation($path) {
        if (!class_exists('Imagick')) {
            return false;
        }

        $im = new \Imagick;

        $im->readImage($path);

        if (strtolower($im->getImageFormat()) != 'jpeg') {
            return false;
        }

        $orientation = $im->getImageOrientation();
        $rotated = false;
        if ($orientation == \Imagick::ORIENTATION_RIGHTTOP) {
            $im->rotateImage('none', 90);
            $rotated = true;
        } elseif ($orientation == \Imagick::ORIENTATION_BOTTOMRIGHT) {
            $im->rotateImage('none', 180);
            $rotated = true;
        } elseif ($orientation == \Imagick::ORIENTATION_LEFTBOTTOM) {
            $im->rotateImage('none', 270);
            $rotated = true;
        }
        if ($rotated) {
            $im->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
            $im->writeImage();
            return true;
        }
        return false;
    }
}
