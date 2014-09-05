<?php
namespace ver2\kakiemon;

class mobile_key {
    public static function create($instance, $keytype, $blockid = null) {
        global $DB, $USER;

        $key = (object)array(
            'kakiemon' => $instance,
            'block' => $blockid,
            'userid' => $USER->id,
            'keytype' => $keytype,
            'keystring' => sha1(mt_rand(0, 99999999)),
            'expires' => time() + HOURSECS
        );
        $key->id = $DB->insert_record(ke::TABLE_MOBILE_KEYS, $key);

        return $key;
    }

    public static function get($keystr) {
        global $DB;

        return $DB->get_record_select(ke::TABLE_MOBILE_KEYS,
            'keystring = :key AND expires > :now',
            array('key' => $keystr, 'now' => time())
        );
    }

    public static function delete_expired() {
        global $DB;

        $DB->delete_records_select(ke::TABLE_MOBILE_KEYS,
            'expires <= :now', array('now' => time()));
    }
}
