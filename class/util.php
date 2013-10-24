<?php
namespace ver2\kakiemon;

use editor_tinymce;
defined('MOODLE_INTERNAL') || die();

class util {
	const ICON_EDIT = 0;
	const ICON_DELETE = 1;
	const BUTTON_EDIT = 0;
	const BUTTON_DELETE = 1;

	private static $icons = array();

	public static function load_lightbox() {
		global $PAGE;

		$PAGE->requires->js(new \moodle_url('/mod/kakiemon/lib/lightbox/js/jquery-1.10.2.min.js'));
		$PAGE->requires->js(new \moodle_url('/mod/kakiemon/lib/lightbox/js/lightbox-2.6.min.js'));

// 		$PAGE->requires->css(new \moodle_url('/mod/kakiemon/lib/lightbox/css/lightbox.css'));
	}

	public static function icon($type) {
		switch ($type) {
			case self::ICON_EDIT:
				break;
			case self::ICON_DELETE:
				break;
		}
	}

	public static function button($type, $url) {
		switch ($type) {
			case self::BUTTON_EDIT:
				$icon = new \pix_icon('t/delete', get_st);
				break;
		}
	}
}
