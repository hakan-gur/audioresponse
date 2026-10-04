<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_qtype_audioresponse_upgrade($oldversion): bool {
    global $DB;

    if ($oldversion < 2026100400) {
        $DB->set_field('user_preferences', 'value', '60', [
            'name' => 'qtype_audioresponse_recordingduration',
            'value' => '600',
        ]);
        upgrade_plugin_savepoint(true, 2026100400, 'qtype', 'audioresponse');
    }

    return true;
}
