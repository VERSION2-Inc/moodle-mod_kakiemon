<?php
namespace ver2\kakiemon;

class block_image extends block {
	/**
	 *
	 * @param \MoodleQuickForm $f
	 */
	public function add_form_elements(\MoodleQuickForm $f) {
		$f->addElement('filemanager', 'file', '画像ファイル');
	}

	/**
	 *
	 * @param \moodleform $form
	 * @return string
	 */
	public function update_data($form, $block) {
		$data = $form->get_data();
		file_save_draft_area_files($data->file, $this->kakiemon->context->id, kakiemon::COMPONENT,
			'blockfile', $block->id);

		$data = (object)[
		];

		return serialize($data);
	}

	/**
	 *
	 * @param string $data
	 * @return string
	 */
	public function get_content($block) {
		util::load_lightbox();

		$data = unserialize($block->data);

		$o = '';

		$fs = get_file_storage();
		$files = $fs->get_area_files($this->kakiemon->context->id, kakiemon::COMPONENT, 'blockfile',
				$block->id, 'itemid, filepath, filename', false);
		if (!$files) {
			return '';
		}
		/* @var $file \stored_file */
		$file = reset($files);

		$path = '/'.$this->kakiemon->context->id.'/mod_kakiemon/blockfile/'.$block->id
				.$file->get_filepath().$file->get_filename();
		$fileurl = \moodle_url::make_file_url('/pluginfile.php', $path);

		$o .= \html_writer::empty_tag('img', array('src' => $fileurl, 'style'=>'width:100%'));

		return $o;
	}
}
