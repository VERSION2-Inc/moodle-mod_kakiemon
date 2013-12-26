<?php
namespace ver2\kakiemon;

class ke_page_cls {
    /**
     *
     * @var \stdClass
     */
    public $data;

    /**
     *
     * @var ke
     */
    private $ke;
    /**
     *
     * @var \moodle_database
     */
    private $db;

    /**
     *
     * @param int $id
     */
    public function __construct($ke, $id) {
        global $DB;

        $this->ke = $ke;
        $this->db = $DB;

        $this->data = $this->db->get_record(ke::TABLE_PAGES, array('id' => $id), '*', MUST_EXIST);
    }

    /**
     *
     * @return boolean
     */
    public function is_viewable() {
        global $USER;

        $sharewith = $this->ke->options->sharewith;

        if ($sharewith == ke::SHARE_PUBLIC
            || $sharewith == ke::SHARE_LOGIN && isloggedin()
            || $sharewith == ke::SHARE_COURSE && can_access_course($this->ke->course)
        ) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return float
     */
    public function get_average_rating() {
        return $this->db->get_field_sql('
                SELECT AVG(rating) FROM {'.ke::TABLE_RATINGS.'}
                WHERE kakiemon = :instance AND page = :page',
                array('instance' => $this->data->kakiemon, 'page' => $this->data->id));
    }

    /**
     *
     * @return int
     */
    public function get_rated_users() {
        return $this->db->get_field_sql('
                SELECT COUNT(*) FROM {'.ke::TABLE_RATINGS.'}
                WHERE kakiemon = :instance AND page = :page',
                array('instance' => $this->data->kakiemon, 'page' => $this->data->id));
    }

    /**
     *
     * @return int
     */
    public function get_my_rating() {
        global $USER;

        return $this->db->get_field(ke::TABLE_RATINGS, 'rating', array(
                'kakiemon' => $this->data->kakiemon,
                'page' => $this->data->id,
                'userid' => $USER->id
        ));
    }
}
