<?php
namespace ver2\kakiemon;

defined('MOODLE_INTERNAL') || die();

class util {
	public static function load_lightbox() {
		global $PAGE;

		$PAGE->requires->js(new \moodle_url('/mod/kakiemon/lib/lightbox/js/jquery-1.10.2.min.js'));
		$PAGE->requires->js(new \moodle_url('/mod/kakiemon/lib/lightbox/js/lightbox-2.6.min.js'));

// 		$PAGE->requires->css(new \moodle_url('/mod/kakiemon/lib/lightbox/css/lightbox.css'));
	}
}
