<?php
defined('MOODLE_INTERNAL') || die();

class qtype_audioresponse_question extends question_with_responses {

    public $delaybefore;
    public $preparationcountdown;
    public $recordingduration;

    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        return question_engine::make_behaviour('manualgraded', $qa, $preferredbehaviour);
    }

    public function get_expected_data(): array {
        return ['recording' => question_attempt::PARAM_FILES];
    }

    public function summarise_response(array $response) {
        if (!isset($response['recording']) || $response['recording'] === '') {
            return get_string('norecording', 'qtype_audioresponse');
        }
        $files = $response['recording']->get_files();
        foreach ($files as $file) {
            if (!$file->is_directory()) {
                return $file->get_filename();
            }
        }
        return get_string('norecording', 'qtype_audioresponse');
    }

    public function is_complete_response(array $response): bool {
        return $this->is_gradable_response($response);
    }

    public function is_gradable_response(array $response): bool {
        if (!isset($response['recording']) || $response['recording'] === '') {
            return false;
        }
        foreach ($response['recording']->get_files() as $file) {
            if (!$file->is_directory()) {
                return true;
            }
        }
        return false;
    }

    public function get_validation_error(array $response): string {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return get_string('pleaserecord', 'qtype_audioresponse');
    }

    public function is_same_response(array $prevresponse, array $newresponse): bool {
        return question_utils::arrays_same_at_key_missing_is_blank(
            $prevresponse, $newresponse, 'recording');
    }

    public function get_correct_response(): ?array {
        return null;
    }

    public function check_file_access($qa, $options, $component, $filearea,
            $args, $forcedownload): bool {
        if ($component === 'question' && $filearea === 'recording') {
            return true;
        }
        return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
    }
}
