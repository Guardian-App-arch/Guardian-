$(document).ready(function () {
    let recorder; // Recorder.js instance
    let audioContext; // AudioContext for Recorder.js
    let stream; // MediaStream from getUserMedia
    let recordingInterval; // Interval for sending data every 15 seconds


    $('#toggle').click(async function () {
        const button = $(this);

        // Stop recording
        if (button.hasClass('btn-success')) {
            recorder.stop(); // Stop the Recorder.js instance
            clearInterval(recordingInterval); // Clear the interval
            stream.getTracks().forEach((track) => track.stop()); // Stop audio stream
            console.log('Recording stopped.');

            // Update button UI
            button.removeClass('btn-success').addClass('btn-off').html('Activate');
            $('#status-spinner').removeClass('spinner').removeClass('spinner-grow');
            $('img').attr('src', 'assets/img/Lo1.png');
            return;
        }

        // Start recording
        if (button.hasClass('btn-off')) {
            try {
                // Get audio input stream
                stream = await navigator.mediaDevices.getUserMedia({ audio: true });

                // Create an AudioContext and attach the stream
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const input = audioContext.createMediaStreamSource(stream);

                // Initialize Recorder.js
                recorder = new Recorder(input, { numChannels: 1 });
                recorder.record();
                console.log('Recording started.');

                // Send audio to the server every 15 seconds
                recordingInterval = setInterval(() => {
                    recorder.exportWAV((audioBlob) => {
                        const formData = new FormData();
                        formData.append('audio', audioBlob, `recording_${Date.now()}.wav`);

                        console.log('Sending audio to process.php ...');

                        // send the audio file to the server (process.php file)
                        $.ajax({
                            url: 'process.php',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                console.log(response);
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                console.error('Error uploading audio:', textStatus, errorThrown);
                            },
                        });
                    });
                }, 15000); // Send data every 15 seconds

                // Update the UI
                button.addClass('btn-success').removeClass('btn-off').html('Deactivate');
                $('#status-spinner').addClass('spinner').addClass('spinner-grow');
                $('img').attr('src', 'assets/img/Lo.png');
            } catch (err) {
                console.error('Error accessing audio stream:', err);
                alert('Failed to start recording.');
            }
        }
    });
});

//tool tip
(function ($) {
    "use strict";

    if ($('[data-bs-toggle="tooltip"]').length > 0) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    }
})(jQuery);