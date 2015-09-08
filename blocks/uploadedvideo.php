<?php
namespace ver2\kakiemon;

class block_uploadedvideo extends block {
    /**
     *
     * @param \MoodleQuickForm $f
     */
    public function add_form_elements(\MoodleQuickForm $f) {
        $f->addElement('filemanager', 'file', ke::str('videofile'), array(
            'maxfiles' => 1
        ));
    }

    public function prepare_file(form_block_edit $form, \stdClass $block) {
        $draftitemid = file_get_submitted_draft_itemid('file');
        file_prepare_draft_area($draftitemid, $this->ke->context->id, ke::COMPONENT, 'blockfile',
            $block->id);
        $data = new \stdClass();
        $data->file = $draftitemid;
        $form->set_data($data);
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

        $data = (object)array();

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
        if ($files) {
            /* @var $file \stored_file */
            $file = reset($files);

            $path = '/' . $this->ke->context->id . '/mod_kakiemon/blockfile/' . $block->id .
                     $file->get_filepath() . $file->get_filename();

            $fileurl = \moodle_url::make_file_url('/pluginfile.php', $path);

            $o .= \html_writer::link($fileurl, 'Video');

            $o = format_text($o);
        }

        if ($this->editing) {
            $o .= \html_writer::tag('div',
                \html_writer::tag('span', ke::str('uploadwithphone'), array(
                    'class' => 'textbutton showqrcode',
                    'data-block' => $block->id
                )));
            $o .= \html_writer::tag('div', '', array(
                'class' => 'qrcodewrap'
            ));
        }

        return $o;
    }
}
