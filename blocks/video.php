<?php
namespace ver2\kakiemon;

class block_video extends block {
    /**
     *
     * @param \MoodleQuickForm $f
     */
    public function add_form_elements(\MoodleQuickForm $f) {
        $f->addElement('textarea', 'content', ke::str('embedcode'), $this->codeareaattrs);
        $f->addHelpButton('content', 'embedcode', ke::COMPONENT);
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
            'content' => $formdata->content
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

        if (stripos($content, '<iframe') !== false)
            return $this->tweak_iframe_attrs($content);
        elseif (preg_match('/^[[:alnum:]_-]{11}$/', $content))
            return '<iframe width="100%" height="100%" src="//www.youtube.com/embed/' . $content
                . '" frameborder="0" allowfullscreen></iframe>';
        else
            return \html_writer::tag('span', ke::str('invalidvideoparam'), array('class' => 'error'));
    }

    private function tweak_iframe_attrs($html) {
        $dom = new \DOMDocument;
        if (!$dom->loadHTML($html))
            return $html;

        $nodelist = $dom->getElementsByTagName('iframe');
        if ($nodelist->length) {
            $iframe = $nodelist->item(0);
            $iframe->setAttribute('width', '100%');
            $iframe->setAttribute('height', '100%');
            return $dom->saveHTML($iframe);
        }

        return $html;
    }
}
