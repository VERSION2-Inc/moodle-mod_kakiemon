<?php
namespace ver2\kakiemon;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/kakiemon/locallib.php';

class page_ajax extends page {
    public function execute() {
        global $DB;

        switch ($this->action) {
            case 'blockmove':
                $this->block_move();
                break;
        }
    }

    private function block_move() {
        global $DB;

        $block = $DB->get_record(ke::TABLE_BLOCKS, array('id' => required_param('block', PARAM_INT)));
        $block->blockcolumn = required_param('column', PARAM_INT);
        $block->blockorder = required_param('order', PARAM_INT);
        $DB->update_record(ke::TABLE_BLOCKS, $block);

        $columnblocks = $DB->get_records(ke::TABLE_BLOCKS, array(
                'page' => $block->page,
                'blockcolumn' => $block->blockcolumn
        ), 'blockorder');

        $ids = array_keys($columnblocks);

        $order = 1;
        foreach ($ids as $id) {
            $DB->set_field(ke::TABLE_BLOCKS, 'blockorder', $order++, array('id' => $id));
        }
    }
}

$page = new page_ajax('/mod/kakiemon/ajax.php');
$page->execute();
