<?php
defined('MOODLE_INTERNAL') || die();

function qtype_audioresponse_pluginfile($course, $cm, $context, $filearea,
        $args, $forcedownload, array $options = []) {
    require_once($GLOBALS['CFG']->libdir . '/questionlib.php');
    question_pluginfile($course, $context, 'qtype_audioresponse',
        $filearea, $args, $forcedownload, $options);
}
