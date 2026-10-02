<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Audio Response';
$string['pluginnameadding'] = 'Adding an Audio Response question';
$string['pluginnameediting'] = 'Editing an Audio Response question';
$string['pluginnamesummary'] = 'Students respond by recording an audio response automatically. The recording starts after the configured delay and preparation countdown and stops automatically at the configured duration.';
$string['pluginname_help'] = 'Students respond by recording an audio response automatically. There is no pause, playback or re-record control during the attempt. The response is graded manually.';

$string['delaybefore'] = 'Delay before recording';
$string['delaybefore_help'] = 'This is the number of seconds after page load, before the preparation countdown begins. When you calculate this delay, include page loading time + instruction audio length (if there is one).';
$string['preparationcountdown'] = 'Preparation Countdown';
$string['preparationcountdown_help'] = 'This is the number of seconds before recording starts.';
$string['recordingduration'] = 'Recording Duration';
$string['recordingduration_help'] = 'This is the longest duration of a recording that the student is allowed to make. If they reach this time, the recording will automatically stop and it will be saved. The upper limit is 10 minutes.';
$string['err_recordingdurationmin'] = 'Recording duration must be at least {$a} seconds.';
$string['err_recordingdurationmax'] = 'Recording duration cannot be longer than {$a}.';
$string['err_nonnegative'] = 'This value cannot be negative.';

$string['preparing'] = 'Instruction';
$string['recordingstarting'] = 'Get Ready';
$string['recordingnow'] = 'Recording';
$string['recordingfinished'] = 'Recording finished. Saving ...';
$string['recordingfailed'] = 'Recording failed.';
$string['nowebrtc'] = 'Your browser or connection does not support secure audio recording.';
$string['microphonepermission'] = 'Microphone access is required to record your response.';
$string['uploadcomplete'] = 'Recording saved.';
$string['uploadfailed'] = 'The recording could not be saved. Please contact your teacher.';
$string['norecording'] = 'No recording';
$string['pleaserecord'] = 'Please complete your audio response.';
$string['startsignal'] = 'Recording start signal';

$string['privacy:metadata'] = 'The Audio Response question type stores audio responses as part of Moodle question attempts.';
