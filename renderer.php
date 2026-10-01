<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/repository/lib.php');

class qtype_audioresponse_renderer extends qtype_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options): string {
        global $CFG;

        /** @var qtype_audioresponse_question $question */
        $question = $qa->get_question();
        $output = '';

        $existingfiles = $qa->get_last_qt_files('recording', $options->context->id);
        $draftitemid = null;

        if (!$options->readonly) {
            $draftitemid = $qa->prepare_response_files_draft_itemid(
                'recording', $options->context->id);
            $output .= html_writer::empty_tag('input', [
                'type' => 'hidden',
                'name' => $qa->get_qt_field_name('recording'),
                'value' => $draftitemid,
            ]);
        }

        if ($qa->get_state() == question_state::$invalid) {
            $output .= html_writer::div(
                $question->get_validation_error([]), 'validationerror');
        }

        $questiontext = $question->format_text(
            $question->questiontext,
            $question->questiontextformat,
            $qa,
            'question',
            'questiontext',
            $question->id
        );
        $output .= html_writer::tag('div', $questiontext, ['class' => 'qtext']);

        $filename = 'audioresponse.webm';
        $existingfile = null;
        foreach ($existingfiles as $file) {
            if (!$file->is_directory()) {
                $existingfile = $file;
                $filename = $file->get_filename();
                break;
            }
        }

        if ($options->readonly) {
            if ($existingfile) {
                $url = $qa->get_response_file_url($existingfile);
                $output .= html_writer::tag('div',
                    html_writer::tag('audio', '', [
                        'controls' => 'controls',
                        'preload' => 'metadata',
                        'src' => $url,
                    ]),
                    ['class' => 'qtype-audioresponse-playback']);
            } else {
                $output .= html_writer::div(
                    get_string('norecording', 'qtype_audioresponse'),
                    'qtype-audioresponse-no-recording');
            }
            return $output;
        }

        $recordingurl = $existingfile
            ? moodle_url::make_draftfile_url($draftitemid, '/', $filename)
            : '';

        $output .= html_writer::start_div('qtype-audioresponse-recorder', [
            'id' => $qa->get_outer_question_div_unique_id() . '_audioresponse',
            'data-delay' => $question->delaybefore,
            'data-preparation' => $question->preparationcountdown,
            'data-duration' => $question->recordingduration,
            'data-draftitemid' => $draftitemid,
            'data-contextid' => $options->context->id,
            'data-recording-filename' => $filename,
            'data-recording-url' => $recordingurl,
        ]);
        $output .= html_writer::div('', 'qtype-audioresponse-status', ['aria-live' => 'polite']);
        $output .= html_writer::div('', 'qtype-audioresponse-countdown', ['aria-live' => 'assertive']);
        $output .= html_writer::div('', 'qtype-audioresponse-timer', ['aria-live' => 'polite']);
        $output .= html_writer::end_div();

        $repositories = repository::get_instances([
            'type' => 'upload',
            'currentcontext' => $options->context,
        ]);
        if (empty($repositories)) {
            throw new moodle_exception('errornouploadrepo', 'moodle');
        }
        $uploadrepository = reset($repositories);

        $settings = [
            'uploadRepositoryId' => (int)$uploadrepository->id,
            'contextId' => (int)$options->context->id,
            'draftItemId' => (int)$draftitemid,
            'maxUploadSize' => $this->get_upload_size_limit($options->context),
            'recordingFilename' => $filename,
        ];

        $this->page->requires->strings_for_js([
            'recordingfailed', 'recordingstarting', 'recordingnow',
            'uploadcomplete', 'uploadfailed', 'microphonepermission',
            'preparing', 'recordingfinished', 'norecording',
        ], 'qtype_audioresponse');

        $this->page->requires->js_call_amd(
            'qtype_audioresponse/recorder',
            'init',
            [$qa->get_outer_question_div_unique_id() . '_audioresponse', $settings]
        );

        return $output;
    }

    protected function get_upload_size_limit(context $context): int {
        global $CFG;

        $coursebytes = 0;
        [$ctx, $course] = get_context_info_array($context->id);
        if (is_object($course)) {
            $coursebytes = $course->maxbytes;
        }

        return get_user_max_upload_file_size($context, $CFG->maxbytes, $coursebytes);
    }
}
