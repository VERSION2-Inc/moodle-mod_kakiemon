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

    if ($oldversion < 2013122600) {

        // Rename field sharewith on table kakiemon to NEWNAMEGOESHERE.
        $table = new xmldb_table('kakiemon');
        $field = new xmldb_field('publicarea', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'editcapabilities');

        // Launch rename field sharewith.
        $dbman->rename_field($table, $field, 'sharewith');

        $field = new xmldb_field('sharewith', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null, 'editcapabilities');

        // Launch change of type for field sharewith.
        $dbman->change_field_type($table, $field);

        // Launch change of default for field sharewith.
        $dbman->change_field_default($table, $field);

        // Kakiemon savepoint reached.
        upgrade_mod_savepoint(true, 2013122600, 'kakiemon');
    }

    if ($oldversion < 2013122701) {

        // Define field userating to be added to kakiemon.
        $table = new xmldb_table('kakiemon');
        $field = new xmldb_field('userating', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'usedislike');

        // Conditionally launch add field userating.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Kakiemon savepoint reached.
        upgrade_mod_savepoint(true, 2013122701, 'kakiemon');
    }

    if ($oldversion < 2013122702) {

        // Changing precision of field type on table kakiemon_blocks to (20).
        $table = new xmldb_table('kakiemon_blocks');
        $field = new xmldb_field('type', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'page');

        // Launch change of precision for field type.
        $dbman->change_field_precision($table, $field);

        // Kakiemon savepoint reached.
        upgrade_mod_savepoint(true, 2013122702, 'kakiemon');
    }

    if ($oldversion < 2013122703) {

        // Define field grade to be added to kakiemon.
        $table = new xmldb_table('kakiemon');
        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'introformat');

        // Conditionally launch add field grade.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Kakiemon savepoint reached.
        upgrade_mod_savepoint(true, 2013122703, 'kakiemon');
    }

    if ($oldversion < 2013122704) {

        // Define field feedback to be added to kakiemon_grades.
        $table = new xmldb_table('kakiemon_grades');
        $field = new xmldb_field('feedback', XMLDB_TYPE_TEXT, null, null, null, null, null, 'grade');

        // Conditionally launch add field feedback.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('feedbackformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'feedback');

        // Conditionally launch add field feedbackformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Kakiemon savepoint reached.
        upgrade_mod_savepoint(true, 2013122704, 'kakiemon');
    }

    if ($oldversion < 2013122705) {

        // Define index gradeitem (unique) to be dropped form kakiemon_grades.
        $table = new xmldb_table('kakiemon_grades');
        $index = new xmldb_index('gradeitem', XMLDB_INDEX_UNIQUE, array('gradeitem'));

        // Conditionally launch drop index gradeitem.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Kakiemon savepoint reached.
        upgrade_mod_savepoint(true, 2013122705, 'kakiemon');
    }

    return true;
}
