<?php
namespace ver2\kakiemon;

class block_file extends block {
	public function add_form_elements(\MoodleQuickForm $f) {
		$f->addElement('filemanager', 'file', 'ファイル');
	}

	public function update_data(form_block_edit $form,\stdClass $block) {
		$data = $form->get_data();
		file_save_draft_area_files($data->file, $this->ke->context->id, ke::COMPONENT,
				'blockfile', $block->id);

		$data = (object)[];

		return serialize($data);
	}

	public function get_content(\stdClass $block) {
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
		$fileurl = \moodle_url::make_file_url('/pluginfile.php', $path, true);

		$icon = $this->output->pix_icon(file_file_icon($file, ke::FILE_ICON_SIZE),
				get_mimetype_description($file));
		$o .= $this->output->action_link($fileurl, $icon . ' ' . $file->get_filename());

		return $o;
	}
}
