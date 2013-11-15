<?php
defined('MOODLE_INTERNAL') || die();

/**
 *
 * @param int $oldversion
 * @return boolean
 */
function xmldb_kakiemon_upgrade($oldversion = 0) {
	global $DB;

	$dbman = $DB->get_manager();

	if ($oldversion < 2013111500) {

		// Define field template to be added to kakiemon_pages.
		$table = new xmldb_table('kakiemon_pages');
		$field = new xmldb_field('template', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'dislikes');

		// Conditionally launch add field template.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		// Kakiemon savepoint reached.
		upgrade_mod_savepoint(true, 2013111500, 'kakiemon');
	}

	return true;
}
