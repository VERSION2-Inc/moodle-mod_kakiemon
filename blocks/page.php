<?php
namespace ver2\kakiemon;

class block_page extends block {
    const THUMBNAIL_WIDTH = 250;
    const THUMBNAIL_HEIGHT = 250;
    const THUMBNAIL_NAME = 'thumb.jpg';
    const THUMBNAIL_API_THUMBALIZR = 'http://api1.thumbalizr.com/';

    const OUTPUT_FILE = 'out.jpg';
    const THUMBNAIL_FILE = 'thumb.jpg';
    const CROP_HEIGHT = 1024;

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

        $data = (object)array(
            'url' => $formdata->url
        );

//         $thumb = $this->make_thumbnail($formdata->url);
//         if ($thumb) {
//             $fs = get_file_storage();
//             $file = $fs->create_file_from_string(array(
//                     'contextid' => $this->ke->context->id,
//                     'component' => ke::COMPONENT,
//                     'filearea' => self::FILE_AREA,
//                     'itemid' => $block->id,
//                     'filepath' => '/',
//                     'filename' => self::THUMBNAIL_NAME
//             ), $thumb);
//         }

        $DB->set_field(ke::TABLE_BLOCKS, 'data', serialize($data), array('id' => $block->id));

        $fr = (object)array(
            'contextid' => $this->ke->context->id,
            'component' => ke::COMPONENT,
            'filearea' => block::FILE_AREA,
            'itemid' => $block->id,
            'filepath' => '/',
            'filename' => self::THUMBNAIL_FILE
        );

        $fs = get_file_storage();
        $fs->delete_area_files($fr->contextid, $fr->component, $fr->filearea, $fr->itemid);
    }

    /**
     *
     * @param string $data
     * @return string
     */
    public function get_content(\stdClass $block) {
        $this->block = $block;

        $data = unserialize($block->data);

        $o = '';

//         $fs = get_file_storage();
//         $contextid = $this->ke->context->id;
//         $files = $fs->get_area_files($contextid, ke::COMPONENT, self::FILE_AREA, false,
//                 'itemid, filepath, filename', false);
//         if ($files) {
//             $file = reset($files);
//             $o .= \html_writer::empty_tag('img', array(
//                     'src' => \moodle_url::make_pluginfile_url($contextid, ke::COMPONENT,
//                             self::FILE_AREA, $block->id, '/', self::THUMBNAIL_NAME)
//             ));
//         }
// $o .= $this->make_thumbnail($data->url);
        $o .= $this->get_thumbnail_code($data->url);
        $o .= '<br>';
        $o .= \html_writer::tag('a', $data->url, array(
                'href' => $data->url,
                'target' => '_blank'
        ));

        return $o;
    }

    private function get_thumbnail_code($url) {
        global $CFG;

        $fr = (object)array(
            'contextid' => $this->ke->context->id,
            'component' => ke::COMPONENT,
            'filearea' => block::FILE_AREA,
            'itemid' => $this->block->id,
            'filepath' => '/',
            'filename' => self::THUMBNAIL_FILE
        );

        $fs = get_file_storage();
        if (!$fs->file_exists($fr->contextid, $fr->component, $fr->filearea, $fr->itemid,
            $fr->filepath, $fr->filename)) {
            $tmpdir = $CFG->tempdir.'/kakiemon/page/'.$this->block->id;

            if (!file_exists($tmpdir))
                mkdir($tmpdir, 0777, true);

            chdir($tmpdir);

            $cmd = implode(' ', array(
                escapeshellarg($this->ke->config->wkhtmltoimage),
                '--crop-h',
                self::CROP_HEIGHT,
                escapeshellarg($url),
                self::OUTPUT_FILE
            ));
//             echo $cmd;die;

            exec($cmd, $out, $ret);

            error_log(print_r($out,1));
            error_log($ret);

            if (file_exists(self::OUTPUT_FILE))
                $this->resize_image(self::OUTPUT_FILE, self::THUMBNAIL_FILE,
                    self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
            else
                $this->create_dummy_image(self::THUMBNAIL_FILE,
                    self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);

            $fs->create_file_from_pathname($fr, self::THUMBNAIL_FILE);

//             fulldelete($tmpdir);
        }

        $thumburl = \moodle_url::make_pluginfile_url($fr->contextid, $fr->component, $fr->filearea,
            $fr->itemid, $fr->filepath, $fr->filename);

        return \html_writer::empty_tag('img', array(
            'src' => $thumburl->out(false)
        ));
    }

    private function resize_image($src, $dst, $width, $height) {
        $imsrc = imagecreatefromjpeg($src);
        list($orgwidth, $orgheight) = getimagesize($src);

        $imdst = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($imdst, 255, 255, 255);
        imagefill($imdst, 0, 0, $bg);

        imagecopyresampled($imdst, $imsrc, 0, 0, 0, 0, $width,
            $orgheight * self::THUMBNAIL_HEIGHT / self::CROP_HEIGHT, $orgwidth, $orgheight);
        imagejpeg($imdst, $dst);
    }

    private function create_dummy_image($path, $width, $height) {
        $imdst = imagecreatetruecolor($width, $height);
        imagejpeg($imdst, $path);
    }
}
