<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_qtype_audioresponse_upgrade($oldversion): bool {
    global $DB;

    if ($oldversion < 2026100400) {
        $preferences = $DB->get_records('user_preferences', [
            'name' => 'qtype_audioresponse_recordingduration',
        ], '', 'id, value');

        foreach ($preferences as $preference) {
            if ($preference->value === '600') {
                $DB->set_field('user_preferences', 'value', '60', ['id' => $preference->id]);
            }
        }

        upgrade_plugin_savepoint(true, 2026100400, 'qtype', 'audioresponse');
    }

    if ($oldversion < 2026100410) {
        $preferences = $DB->get_records('user_preferences', [
            'name' => 'qtype_audioresponse_recordingduration',
        ], '', 'id, value');

        foreach ($preferences as $preference) {
            if ($preference->value === '600') {
                $DB->set_field('user_preferences', 'value', '60', ['id' => $preference->id]);
            }
        }

        upgrade_plugin_savepoint(true, 2026100410, 'qtype', 'audioresponse');
    }

    return true;
}
