<?php
namespace ver2\kakiemon;

use ver2\kakiemon\kakiemon as ke;

class block_page extends block {
	const THUMBNAIL_WIDTH = 250;
	const THUMBNAIL_NAME = 'thumb.jpg';
	const THUMBNAIL_API_THUMBALIZR = 'http://api1.thumbalizr.com/';

	/**
	 *
	 * @param \MoodleQuickForm $f
	 */
	public function add_form_elements(\MoodleQuickForm $f) {
		$f->addElement('text', 'url', 'URL', array('size' => 50));
		$f->setType('url', PARAM_TEXT);
		$f->addRule('url', null, 'required', null, 'client');
	}

	/**
	 *
	 * @param \stdClass $form
	 * @return string
	 */
	public function update_data(form_block_edit $form, \stdClass $block) {
		global $DB;

		$formdata = $form->get_data();

		$data = (object)[
				'url' => $formdata->url
		];

// 		$thumb = $this->make_thumbnail($formdata->url);
// 		if ($thumb) {
// 			$fs = get_file_storage();
// 			$file = $fs->create_file_from_string(array(
// 					'contextid' => $this->ke->context->id,
// 					'component' => ke::COMPONENT,
// 					'filearea' => self::FILE_AREA,
// 					'itemid' => $block->id,
// 					'filepath' => '/',
// 					'filename' => self::THUMBNAIL_NAME
// 			), $thumb);
// 		}

		$DB->set_field(ke::TABLE_BLOCKS, 'data', serialize($data), array('id' => $block->id));
	}

	/**
	 *
	 * @param string $data
	 * @return string
	 */
	public function get_content(\stdClass $block) {
		$data = unserialize($block->data);

		$o = '';

// 		$fs = get_file_storage();
// 		$contextid = $this->ke->context->id;
// 		$files = $fs->get_area_files($contextid, ke::COMPONENT, self::FILE_AREA, false,
// 				'itemid, filepath, filename', false);
// 		if ($files) {
// 			$file = reset($files);
// 			$o .= \html_writer::empty_tag('img', array(
// 					'src' => \moodle_url::make_pluginfile_url($contextid, ke::COMPONENT,
// 							self::FILE_AREA, $block->id, '/', self::THUMBNAIL_NAME)
// 			));
// 		}
		$o .= $this->get_thumbnail_code($data->url);
		$o .= \html_writer::tag('a', $data->url, array(
				'href' => $data->url,
				'target' => '_blank'
		));

		return $o;
	}

	private function get_thumbnail_code($url) {
		return $this->get_thumbnail_code_thumbnail_web($url);
// 		return $this->get_thumbnail_code_thumbalizr($url);
	}

	private function get_thumbnail_code_thumbnail_web($url) {
		$params = array(
				'320x240',
				'guest',
				$url
		);
		$jsurl = 'http://free.thumbnail-web.com/p2?'.implode('&', $params);

		return \html_writer::script('', $jsurl);
	}

	private function get_thumbnail_code_thumbalizr($url) {
		$url = rtrim($url, '/');

		$imgurl = 'http://api1.thumbalizr.com/?url='.$url.'&width=250';

		return \html_writer::empty_tag('img', array(
				'src' => $imgurl
		));
	}

	private function make_thumbnail($url) {
		return $this->make_thumbnail_thumbalizr($url);
	}

	private function make_thumbnail_thumbalizr($url) {
		$api = new \moodle_url(self::THUMBNAIL_API_THUMBALIZR, array(
				'url' => $url,
				'width' => self::THUMBNAIL_WIDTH
		));
		$ch = curl_init($api->out(false));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$data = curl_exec($ch);

		if (curl_errno($ch)) {
			return false;
		}

		curl_close($ch);

		return $data;
	}
}
