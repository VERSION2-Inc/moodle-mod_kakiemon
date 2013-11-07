<?php
namespace ver2\kakiemon;

use ver2\kakiemon\kakiemon as ke;

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
		$targetblock = $DB->get_record(ke::TABLE_BLOCKS, array('id' => required_param('target', PARAM_INT)));

		$columnblocks = $DB->get_records(ke::TABLE_BLOCKS, array(
				'page' => $block->page,
				'blockcolumn' => $block->blockcolumn
		), 'blockorder');

		$ids = array_keys($columnblocks);
		array_splice($ids, array_search($block->id, $ids), 1, array());
		array_splice($ids, array_search($targetblock->id, $ids), 1, array($block->id, $targetblock->id));

		$order = 1;
		foreach ($ids as $id) {
			$DB->set_field(ke::TABLE_BLOCKS, 'blockorder', $order++, array('id' => $id));
		}
	}
}

$page = new page_ajax('/mod/kakiemon/ajax.php');
$page->execute();
