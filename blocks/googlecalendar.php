<?php
namespace ver2\kakiemon;

class block_googlecalendar extends block {
    public function add_form_elements(\MoodleQuickForm $f) {
        $f->addElement('textarea', 'content', ke::str('embedcode'),
                array('cols' => 40, 'rows' => 5));
    }

    public function update_data(form_block_edit $form, \stdClass $block) {
        global $DB;

        $formdata = $form->get_data();

        $data = (object)array(
            'content' => $formdata->content
        );

        $DB->set_field(ke::TABLE_BLOCKS, 'data', serialize($data), array('id' => $block->id));
    }

    public function get_content(\stdClass $block) {
        $data = unserialize($block->data);

        $o = preg_replace('/(width|height)="\d+"/', '$1="100%"', $data->content);

        return $o;
    }
        }
