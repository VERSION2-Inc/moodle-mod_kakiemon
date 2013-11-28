<?php

namespace ver2\kakiemon;

class block_image extends block {

	/**
	 *
	 * @param \MoodleQuickForm $f
	 */
	public function add_form_elements(\MoodleQuickForm $f) {
		$f->addElement('filemanager', 'file', '画像ファイル', array(
				'maxfiles' => 1
		));
	}

	/**
	 *
	 * @param \moodleform $form
	 * @return string
	 */
	public function update_data(form_block_edit $form, \stdClass $block) {
		$data = $form->get_data();
		file_save_draft_area_files($data->file, $this->ke->context->id, ke::COMPONENT,
				'blockfile', $block->id);

		$data = (object)[];

		return serialize($data);
	}

	/**
	 *
	 * @param string $data
	 * @return string
	 */
	public function get_content(\stdClass $block) {
		util::load_lightbox();

		$data = unserialize($block->data);

		$o = '';

		$fs = get_file_storage();
		$files = $fs->get_area_files($this->ke->context->id, ke::COMPONENT, 'blockfile',
				$block->id, 'itemid, filepath, filename', false);
		if (!$files) {
			return '';
		}
		/* @var $file \stored_file */
		$file = reset($files);

		$path = '/' . $this->ke->context->id . '/mod_kakiemon/blockfile/' . $block->id .
				 $file->get_filepath() . $file->get_filename();
		$fileurl = \moodle_url::make_file_url('/pluginfile.php', $path);

		$o .= \html_writer::link($fileurl,
		\html_writer::empty_tag('img', array(
				'src' => $fileurl,
				'class' => 'block-image'
		)), array('data-lightbox' => 'roadtrip'));

		return $o;
	}
}
