<?php
namespace ver2\kakiemon;

defined('MOODLE_INTERNAL') || die();

class ke_page extends model {
	const TABLE = ke::TABLE_PAGES;

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
	 * @param int $limitnum
	 * @return \stdClass[]
	 */
	public function others($limitnum = 0) {
		global $USER;

		return $this->db->get_records_select(self::TABLE,
				'userid <> :userid', array('userid' => $USER->id),
				'timemodified DESC', '*', 0, $limitnum);
	}

	public function most_liked($limitnum = 5) {
// 		return $this->db->get_records_select(self::TABLE,
// 				'likes > 0', null,
// 				'likes DESC, timemodified DESC');
		$sql = '
				SELECT p.*,
					(
						SELECT COUNT(*)  FROM {'.ke::TABLE_LIKES.'} l
						WHERE l.page = p.id AND l.type = :type
					) cnt
				FROM {'.self::TABLE.'} p
				HAVING cnt > 0
				';
		$params = array(
				'type' => 'like'
		);
		return $this->db->get_records_sql($sql, $params);
	}

	public function most_disliked($limitnum = 5) {
// 		return $this->db->get_records_select(self::TABLE,
// 				'dislikes > 0', null,
// 				'dislikes DESC, timemodified DESC');
		$sql = '
				SELECT p.*,
					(
						SELECT COUNT(*)  FROM {'.ke::TABLE_LIKES.'} l
						WHERE l.page = p.id AND l.type = :type
					) cnt
				FROM {'.self::TABLE.'} p
				HAVING cnt > 0
				';
		$params = array(
				'type' => 'dislike'
		);
		return $this->db->get_records_sql($sql, $params);
	}
}
