<?php
namespace ver2\kakiemon;

class block {
    const FILE_AREA = 'blockfile';

    public $editing;
    /**
     * @var array
     */
    protected $codeareaattrs = array(
        'class' => 'code',
        'cols' => 60,
        'rows' => 10,
        'spellcheck' => 'false'
    );

    /**
     *
     * @var ke
     */
    protected $ke;
    /**
     *
     * @var \core_renderer
     */
    protected $output;
    protected $block;

    /**
     *
     * @param kakiemon $kakiemon
     */
    public function __construct(ke $kakiemon) {
        global $OUTPUT;

        $this->ke = $kakiemon;
        $this->output = $OUTPUT;
    }

    /**
     *
     * @param \MoodleQuickForm $f
     */
    public function add_form_elements(\MoodleQuickForm $f) {
    }

    public function set_form_data(form_block_edit $form, \stdClass $block) {
        $data = (array)unserialize($block->data);

        $form->set_data($data);
    }

    public function prepare_file(form_block_edit $form, \stdClass $block) {
    }

    /**
     *
     * @param form_block_edit $form
     * @return string
     */
    public function update_data(form_block_edit $form, \stdClass $block) {
        $data = (object)array(
        );

        return serialize($data);
    }

    /**
     *
     * @param string $data
     * @return string
     */
    public function get_content(\stdClass $block) {
        $data = unserialize($block->data);

        $o = '';

        return $o;
    }
}
