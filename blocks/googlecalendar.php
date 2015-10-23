<?php
namespace ver2\kakiemon;

class block_googlecalendar extends block {
    /**
     *
     * @param \MoodleQuickForm $f
     */
    public function add_form_elements(\MoodleQuickForm $f) {
        $f->addElement('textarea', 'content', ke::str('embedcode'), $this->codeareaattrs);

        $this->add_embed_help($f, 'googlecalendarembedhelp');
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

        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($data->content);

        /* @var $iframe \DOMElement */
        $iframe = $dom->getElementsByTagName('iframe')->item(0);

        if (ke::is_output_pdf()) {
            $url = $iframe->getAttribute('src');

            return \html_writer::link($url, ke::str('opencalendar'), array('target' => '_blank'));
        } else {
            $iframe->setAttribute('width', '100%');

            return $dom->saveHTML($iframe);
        }
    }
}
