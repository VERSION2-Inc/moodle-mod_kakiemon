<?php
namespace ver2\kakiemon;

defined('MOODLE_INTERNAL') || die();

class feedback {
    public $data;

    public function __construct(\stdClass $data) {
        $this->data = $data;
    }

    public function is_deletable() {
        global $USER;


    }
}
