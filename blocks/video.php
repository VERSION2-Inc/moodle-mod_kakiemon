<?php
namespace ver2\kakiemon;

class block_video extends block {
    const DEF_WIDTH = 500;
    const DEF_HEIGHT = 280;

    /**
     *
     * @param \MoodleQuickForm $f
     */
    public function add_form_elements(\MoodleQuickForm $f) {
        $f->addElement('textarea', 'content', ke::str('embedcode'), $this->codeareaattrs);
        $f->addHelpButton('content', 'embedcode', ke::COMPONENT);

        $f->addElement('text', 'videowidth', ke::str('videowidth'), array('size' => 5));
        $f->setType('videowidth', PARAM_INT);
        $f->setDefault('videowidth', self::DEF_WIDTH);
        $f->addElement('text', 'videoheight', ke::str('videoheight'), array('size' => 5));
        $f->setType('videoheight', PARAM_INT);
        $f->setDefault('videoheight', self::DEF_HEIGHT);
    }

    /**
     *
     * @param form_block_edit $form
     * @param \stdClass $block
     */
    public function update_data(form_block_edit $form, \stdClass $block) {
        global $DB;

        $formdata = $form->get_data();

        $data = (object)array(
            'content' => $formdata->content,
            'videowidth' => $formdata->videowidth,
            'videoheight' => $formdata->videoheight
        );

        $DB->set_field(ke::TABLE_BLOCKS, 'data', serialize($data), array('id' => $block->id));
    }

    /**
     *
     * @param \stdClass $block
     * @return string
     */
    public function get_content(\stdClass $block) {
        $data = unserialize($block->data);
        $content = trim($data->content);

        if (!isset($data->videowidth))
            $data->videowidth = self::DEF_WIDTH;
        if (!isset($data->videoheight))
            $data->videoheight = self::DEF_HEIGHT;

        if (stripos($content, '<iframe') !== false)
            return $this->tweak_iframe_attrs($content, $data);

        elseif (preg_match('/^([[:alnum:]_-]{11})$/', $content, $m)
            || preg_match('!youtube\.com/watch\?v=([[:alnum:]_-]{11})!', $content, $m)) {
            $videoid = $m[1];
            return \html_writer::tag('iframe', '', array(
                'src' => '//www.youtube.com/embed/' . $videoid,
                'width' => $data->videowidth,
                'height' => $data->videoheight,
                'frameborder' => 0,
                'allowfullscreen' => 'allowfullscreen'
            ));

        } else
            return \html_writer::tag('span', ke::str('invalidvideoparam'), array('class' => 'error'));
    }

    private function tweak_iframe_attrs($html, \stdClass $blockdata) {
        $dom = new \DOMDocument;
        if (!$dom->loadHTML($html))
            return $html;

        $nodelist = $dom->getElementsByTagName('iframe');
        if ($nodelist->length) {
            $iframe = $nodelist->item(0);
            $iframe->setAttribute('width', $blockdata->videowidth);
            $iframe->setAttribute('height', $blockdata->videoheight);
            return $dom->saveHTML($iframe);
        }

        return $html;
    }
}
