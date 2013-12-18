<?php
namespace ver2\kakiemon;

class block_html extends block {
    public function add_form_elements(\MoodleQuickForm $f) {
        $f->addElement('editor', 'content', 'コンテンツ');
    }

    public function set_form_data(form_block_edit $form, \stdClass $block) {
        $data = unserialize($block->data);

        $data->content = array(
                'text' => $data->content,
                'format' => FORMAT_HTML
        );

        $form->set_data($data);
    }

    public function update_data(form_block_edit $form, \stdClass $block) {
        global $DB;

        $formdata = $form->get_data();

        $data = (object)[
            'content' => $formdata->content['text']
        ];

        $DB->set_field(kakiemon::TABLE_BLOCKS, 'data', serialize($data), array('id' => $block->id));
    }

    public function get_content(\stdClass $block) {
        $data = unserialize($block->data);

        $o = $data->content;

        return $o;
    }
}
