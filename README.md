# Moodle Audio Response question type

A manually graded Moodle question type for standardized-test-style speaking responses.

## Target

- Moodle 4.1.22 initially.
- Designed for later compatibility testing through Moodle 5.0.
- No administrator settings.
- Fully translation-ready.
- Audio only.

## Student recording sequence

1. Page loads.
2. Optional delay.
3. Optional preparation countdown.
4. A short recording-start signal plays.
5. After one second, microphone recording starts automatically.
6. Recording continues without pause, playback, or re-record controls.
7. Recording stops automatically at the configured duration.
8. The audio is uploaded to Moodle's question-attempt response file area.

Students do not choose when recording starts and cannot create another take.

## Author settings

- Delay before recording: 0 seconds by default.
- Preparation countdown: 0 seconds by default.
- Recording duration: 10 minutes by default.
- Recording duration minimum: 10 seconds.
- Recording duration maximum: 10 minutes (600 seconds).
- Default mark: 10.

The 10-second minimum is the minimum configurable recording window. It is not a requirement that the student must speak for ten seconds.

## Question bank integration

The plugin uses Moodle's standard question type APIs, including question categories, question versions/status, tags, question text editors, general feedback, ID Number, attempt response storage, manual grading and question file handling.

Audio responses are stored as part of Moodle question attempts rather than in a plugin-specific response table.

## Browser requirements

Recording requires a secure context (HTTPS, or localhost for development) and browser support for MediaRecorder and getUserMedia.

## License

GPL v2 or later.
