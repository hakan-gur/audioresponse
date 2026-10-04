<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/edit_question_form.php');

class qtype_audioresponse_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        // definition_inner() is called by Moodle immediately after ID Number and before Tags.
        $mform->addElement('duration', 'delaybefore',
            get_string('delaybefore', 'qtype_audioresponse'),
            ['units' => [1], 'optional' => false]);
        $mform->addHelpButton('delaybefore', 'delaybefore', 'qtype_audioresponse');
        $mform->setDefault('delaybefore', $this->get_default_value('delaybefore', 0));

        $mform->addElement('duration', 'preparationcountdown',
            get_string('preparationcountdown', 'qtype_audioresponse'),
            ['units' => [1], 'optional' => false]);
        $mform->addHelpButton('preparationcountdown', 'preparationcountdown', 'qtype_audioresponse');
        $mform->setDefault('preparationcountdown',
            $this->get_default_value('preparationcountdown', 0));

        $mform->addElement('duration', 'recordingduration',
            get_string('recordingduration', 'qtype_audioresponse'),
            ['units' => [1, 60], 'defaultunit' => 1, 'optional' => false]);
        $mform->addHelpButton('recordingduration', 'recordingduration', 'qtype_audioresponse');
        $mform->setDefault('recordingduration',
            $this->get_default_value('recordingduration',
                qtype_audioresponse::DEFAULT_RECORDING_DURATION));

        // Audio Response uses 10 marks as its new-question default.
        $mform->setDefault('defaultmark', 10);

    }

    public function validation($fromform, $files): array {
        $errors = parent::validation($fromform, $files);

        $duration = (int)($fromform['recordingduration'] ?? 0);
        if ($duration < qtype_audioresponse::MIN_RECORDING_DURATION) {
            $errors['recordingduration'] = get_string(
                'err_recordingdurationmin', 'qtype_audioresponse',
                qtype_audioresponse::MIN_RECORDING_DURATION);
        } else if ($duration > qtype_audioresponse::MAX_RECORDING_DURATION) {
            $errors['recordingduration'] = get_string(
                'err_recordingdurationmax', 'qtype_audioresponse',
                format_time(qtype_audioresponse::MAX_RECORDING_DURATION));
        }

        if ((int)($fromform['delaybefore'] ?? 0) < 0) {
            $errors['delaybefore'] = get_string('err_nonnegative', 'qtype_audioresponse');
        }
        if ((int)($fromform['preparationcountdown'] ?? 0) < 0) {
            $errors['preparationcountdown'] = get_string('err_nonnegative', 'qtype_audioresponse');
        }

        return $errors;
    }

    public function qtype(): string {
        return 'audioresponse';
    }
}
