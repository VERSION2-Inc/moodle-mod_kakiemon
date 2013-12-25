<?php
namespace ver2\kakiemon;

defined('MOODLE_INTERNAL') || die();

class ke_page extends model {
    const TABLE = ke::TABLE_PAGES;

    const SHARE_COURSE = 'course';
    const SHARE_LOGIN = 'login';
    const SHARE_PUBLIC = 'public';

    /**
     *
     * @param int $limitnum
     * @return \stdClass[]
     */
    public function mine($limitnum = 0) {
        global $USER;

        return $this->db->get_records(self::TABLE, array('userid' => $USER->id),
                'timemodified DESC', '*', 0, $limitnum);
    }

    /**
     *
     * @return int
     */
    public function count_mine() {
        global $USER;

        return $this->db->count_records(self::TABLE, array('userid' => $USER->id));
    }

    /**
     *
     * @param int $limitnum
     * @return \stdClass[]
     */
    public function others($limitnum = 0) {
        global $USER, $COURSE, $SITE;

        $where = '
                WHERE userid <> :userid
                    AND (k.viewstartdate = 0 OR k.viewstartdate <= :startdatetest)
                    AND (k.viewenddate = 0 OR k.viewenddate > :enddatetest)';
        $now = time();
        $params = array(
                'userid' => $USER->id,
                'startdatetest' => $now,
                'enddatetest' => $now
        );

        if ($COURSE->id != $SITE->id) {
            $where .= ' AND c.id = :course';
            $params['course'] = $COURSE->id;
        }

        $sql = '
                SELECT p.*
                FROM {kakiemon_pages} p
                    JOIN {kakiemon} k ON p.kakiemon = k.id
                    JOIN {course} c ON k.course = c.id
                '.$where.'
                ORDER BY p.timemodified DESC
                ';

        return $this->db->get_records_sql($sql, $params, 0, $limitnum);
    }

    /**
     *
     * @return int
     */
    public function count_others() {
        global $USER;

        return $this->db->count_records_select(self::TABLE, 'userid <> :userid',
                array('userid' => $USER->id));
    }

    /**
     *
     * @param int $limitnum
     * @return \stdClass[]
     */
    public function most_liked($limitnum = 5) {
        $sql = '
                SELECT p.*,
                    (
                        SELECT COUNT(*)  FROM {'.ke::TABLE_LIKES.'} l
                        WHERE l.page = p.id AND l.type = :type
                    ) cnt
                FROM {'.self::TABLE.'} p
                HAVING cnt > 0
                ORDER BY cnt DESC
                ';
        $params = array(
                'type' => 'like'
        );
        return $this->db->get_records_sql($sql, $params, 0, $limitnum);
    }

    /**
     *
     * @param int $limitnum
     * @return \stdClass[]
     */
    public function most_disliked($limitnum = 5) {
        $sql = '
                SELECT p.*,
                    (
                        SELECT COUNT(*)  FROM {'.ke::TABLE_LIKES.'} l
                        WHERE l.page = p.id AND l.type = :type
                    ) cnt
                FROM {'.self::TABLE.'} p
                HAVING cnt > 0
                ORDER BY cnt DESC
                ';
        $params = array(
                'type' => 'dislike'
        );
        return $this->db->get_records_sql($sql, $params, 0, $limitnum);
    }
    
    public function is_viewable() {
        global $USER;
        
        
    }

    /**
     *
     * @return string[]
     */
    public static function get_share_options() {
        return array(
                self::SHARE_COURSE => ke::str('sharecourse'),
                self::SHARE_LOGIN => ke::str('sharelogin'),
                self::SHARE_PUBLIC => ke::str('sharepublic')
        );
    }
}
