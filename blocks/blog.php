<?php
namespace ver2\kakiemon;

class block_blog extends block {
	public function add_form_elements(\MoodleQuickForm $f) {
		$f->addElement('static', 'desc', 'コース上の「ブログメニュー」ブロック＞「このコースのエントリを追加する」から追加されたブログ記事を一覧表示します。');
	}

	public function get_content(\stdClass $block) {
		global $DB;

		$o = '';

		$posts = $DB->get_records('post');
		foreach ($posts as $post) {
			$o .= $this->output->heading($post->subject);
			$o .= $this->output->container($post->summary);
		}

		return $o;
	}
}
