<?php
namespace ver2\kakiemon;

defined('MOODLE_INTERNAL') || die();

class model {
    /**
     *
     * @var \moodle_database
     */
    protected $db;

    public function __construct() {
        global $DB;

        $this->db = $DB;
    }

    /**
     *
     * @param string[] $conditions
     * @return \stdClass[]
     */
    public function many($conditions = null) {
        return $this->db->get_records(self::TABLE, $conditions);
    }
}
