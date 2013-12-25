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

    if ($oldversion < 2013121900) {

        // Define field id to be added to kakiemon_pages.
        $table = new xmldb_table('kakiemon_pages');
        $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define table kakiemon_grades to be created.
        $table = new xmldb_table('kakiemon_grades');

        // Adding fields to table kakiemon_grades.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('kakiemon', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('page', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('gradeitem', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemarked', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('grade', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table kakiemon_grades.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('kakiemon', XMLDB_KEY_FOREIGN, array('kakiemon'), 'kakiemon', array('id'));
        $table->add_key('page', XMLDB_KEY_FOREIGN, array('page'), 'kakiemon_pages', array('id'));

        // Adding indexes to table kakiemon_grades.
        $table->add_index('gradeitem', XMLDB_INDEX_UNIQUE, array('gradeitem'));

        // Conditionally launch create table for kakiemon_grades.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table kakiemon_ratings to be created.
        $table = new xmldb_table('kakiemon_ratings');

        // Adding fields to table kakiemon_ratings.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('kakiemon', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('page', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('rating', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table kakiemon_ratings.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('kakiemon', XMLDB_KEY_FOREIGN, array('kakiemon'), 'kakiemon', array('id'));
        $table->add_key('page', XMLDB_KEY_FOREIGN, array('page'), 'kakiemon_pages', array('id'));

        // Adding indexes to table kakiemon_ratings.
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Conditionally launch create table for kakiemon_ratings.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Kakiemon savepoint reached.
        upgrade_mod_savepoint(true, 2013121900, 'kakiemon');
    }

    return true;
}
