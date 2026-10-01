<?php
namespace qtype_audioresponse\privacy;

defined('MOODLE_INTERNAL') || die();

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    public static function get_metadata(
        \core_privacy\local\metadata\collection $items): \core_privacy\local\metadata\collection {
        $items->add_database_table(
            'question_attempts',
            [
                'id' => 'privacy:metadata:questionattemptsid',
            ],
            'privacy:metadata:questionattempts'
        );
        $items->add_subsystem_link(
            'core',
            'question',
            'privacy:metadata:question',
            ['question' => 'privacy:metadata:question']
        );
        return $items;
    }

    public static function get_contexts_for_userid(int $userid): \core_privacy\local\request\contextlist {
        return new \core_privacy\local\request\contextlist();
    }

    public static function export_user_data(
        \core_privacy\local\request\approved_contextlist $contextlist) {
    }

    public static function delete_data_for_all_users_in_context(
        \context $context) {
    }

    public static function delete_data_for_user(
        \core_privacy\local\request\approved_contextlist $contextlist) {
    }
}
