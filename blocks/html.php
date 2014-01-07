<?php
namespace ver2\kakiemon;

class block_html extends block {
    /**
     *
     * @param \MoodleQuickForm $f
     */
    public function add_form_elements(\MoodleQuickForm $f) {
        $f->addElement('editor', 'content', 'コンテンツ');
    }

    /**
     *
     * @param form_block_edit $form
     * @param \stdClass $block
     */
    public function set_form_data(form_block_edit $form, \stdClass $block) {
        $data = unserialize($block->data);

        $data->content = array(
                'text' => $data->content,
                'format' => FORMAT_HTML
        );

        $form->set_data($data);
    }

    /**
     *
     * @param form_block_edit $form
     * @param \stdClass $block
     */
    public function update_data(form_block_edit $form, \stdClass $block) {
        global $DB;

        $formdata = $form->get_data();

        $data = (object)[
            'content' => $formdata->content['text']
        ];

        $DB->set_field(kakiemon::TABLE_BLOCKS, 'data', serialize($data), array('id' => $block->id));
    }

    /**
     *
     * @param \stdClass $block
     * @return string
     */
    public function get_content(\stdClass $block) {
        $data = unserialize($block->data);

        $o = $data->content;

        return $o;
    }
}
