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

    if ($oldversion < 2013112000) {

        // Define table kakiemon_accesses to be created.
        $table = new xmldb_table('kakiemon_accesses');

        // Adding fields to table kakiemon_accesses.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('kakiemon', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('page', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timeaccessed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table kakiemon_accesses.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('kakiemon', XMLDB_KEY_FOREIGN, array('kakiemon'), 'kakiemon', array('id'));
        $table->add_key('page', XMLDB_KEY_FOREIGN, array('page'), 'kakiemon_pages', array('id'));

        // Conditionally launch create table for kakiemon_accesses.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Kakiemon savepoint reached.
        upgrade_mod_savepoint(true, 2013112000, 'kakiemon');
    }

    return true;
}
