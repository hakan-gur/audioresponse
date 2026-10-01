<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/audioresponse/question.php');

class qtype_audioresponse extends question_type {

    public const MIN_RECORDING_DURATION = 10;
    public const MAX_RECORDING_DURATION = 600;
    public const DEFAULT_RECORDING_DURATION = 600;

    public function is_manual_graded(): bool {
        return true;
    }

    public function response_file_areas(): array {
        return ['recording'];
    }

    public function extra_question_fields(): array {
        return [
            'qtype_audioresponse_options',
            'delaybefore',
            'preparationcountdown',
            'recordingduration',
        ];
    }

    public function save_defaults_for_new_questions(stdClass $fromform): void {
        parent::save_defaults_for_new_questions($fromform);
        $this->set_default_value('defaultmark', 10);
        $this->set_default_value('delaybefore', 0);
        $this->set_default_value('preparationcountdown', 0);
        $this->set_default_value('recordingduration', self::DEFAULT_RECORDING_DURATION);
    }

    public function save_question_options($fromform) {
        global $DB;

        parent::save_question_options($fromform);

        $record = $DB->get_record('qtype_audioresponse_options',
            ['questionid' => $fromform->id]);
        if (!$record) {
            $record = new stdClass();
            $record->questionid = $fromform->id;
            $record->id = $DB->insert_record('qtype_audioresponse_options', $record);
        }

        $record->delaybefore = (int)$fromform->delaybefore;
        $record->preparationcountdown = (int)$fromform->preparationcountdown;
        $record->recordingduration = (int)$fromform->recordingduration;
        $DB->update_record('qtype_audioresponse_options', $record);
    }

    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('qtype_audioresponse_options', ['questionid' => $questionid]);
        parent::delete_question($questionid, $contextid);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->delaybefore = (int)$questiondata->options->delaybefore;
        $question->preparationcountdown = (int)$questiondata->options->preparationcountdown;
        $question->recordingduration = (int)$questiondata->options->recordingduration;
    }

    public function export_to_xml($question, qformat_xml $format, $extra = null): string {
        $output  = "    <delaybefore>" . (int)$question->delaybefore . "</delaybefore>\n";
        $output .= "    <preparationcountdown>" . (int)$question->preparationcountdown .
            "</preparationcountdown>\n";
        $output .= "    <recordingduration>" . (int)$question->recordingduration .
            "</recordingduration>\n";
        return $output;
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        $questiontype = $data['@']['type'];
        if ($questiontype !== $this->name()) {
            return false;
        }

        $qo = $format->import_headers($data);
        $qo->qtype = $questiontype;
        $qo->delaybefore = $format->getpath($data, ['#', 'delaybefore', 0, '#'], 0);
        $qo->preparationcountdown = $format->getpath(
            $data, ['#', 'preparationcountdown', 0, '#'], 0);
        $qo->recordingduration = $format->getpath(
            $data, ['#', 'recordingduration', 0, '#'], self::DEFAULT_RECORDING_DURATION);
        return $qo;
    }
}
