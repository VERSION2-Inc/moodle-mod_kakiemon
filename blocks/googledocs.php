<?php
namespace ver2\kakiemon;

class block_googledocs extends block {
    /**
     *
     * @param \MoodleQuickForm $f
     */
    public function add_form_elements(\MoodleQuickForm $f) {
        $f->addElement('textarea', 'content', ke::str('embedcode'), $this->codeareaattrs);

        $f->addElement('static', 'embedhelp', ke::str('howtogetembedcode'), ke::str('googledriveembedhelp'));
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

        $o = preg_replace('/(width)=["\']\d+["\']/', '$1="100%"', $data->content);

        $o = \html_writer::tag('div', $o, array(
            'class' => 'iframewrap'
        ));

        return $o;
    }
}
