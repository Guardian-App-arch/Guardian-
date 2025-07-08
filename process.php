<?php
#########################
#########################
############make sude to create a twilio account and fill in your credentials
#########################
#########################


require __DIR__ . '/vendor/autoload.php';

class uploadingErrorException extends Exception {}
class dbConnectionErrorException extends Exception {}
class executionErrorException extends Exception {}
class noContactsException extends Exception {}
class mailingErrorException extends Exception {}


use Codewithkyrian\Whisper\Whisper; //include necessary whisper libs for loading base.en and transcribing the audio
use Codewithkyrian\Whisper\ModelLoader; // 
use function Codewithkyrian\Whisper\readAudio;
use Codewithkyrian\Whisper\WhisperException;

// phpmailer libs for mailing
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// twillio libs
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;


$client = new \Ollama\Client\OllamaClient(); // a new client for ollama
$completionApi = new \Ollama\Api\Completion($client); // creating a new completionApi for text completion
$transcribedText = '';


try {
    // setting response array to send back to index.html as the response
    $progressResponse = [
        'error' => '',
        'status' => '',
        'message' => ''
    ];
    $progressMessages = 'started';

    $transcribedText = '';

    $audioDir = 'audio/'; // where the audio files are stored
    $audioFile = $audioDir . basename($_FILES['audio']['name']);

    if (!is_dir($audioDir)) { // creates the audio directory if doesn't exist
        mkdir($audioDir, '0777', true);
    }

    //uploads the audio to the server
    if (!move_uploaded_file($_FILES['audio']['tmp_name'], $audioFile)) {
        throw new uploadingErrorException();
    }

    //loads the model
    $modelPath = ModelLoader::loadModel('base.en', __DIR__ . '/models');
    $whisper = Whisper::fromPretrained('base.en', baseDir: __DIR__ . '/models');

    //reads the audio
    $audio = readAudio(__DIR__ . '/' . $audioFile);

    //transcribe the audio and store the text in an associative array 
    $segments = $whisper->transcribe($audio, 8);

    // concatenate the text (the transcribed text)
    foreach ($segments as $segment) {

        $transcribedText = "$transcribedText $segment->startTimestamp : $segment->text";
    }

    // optimized prompt
    $prompt = <<< EOT
    you are a machine that can only respond with "yes" or "no" in a json format after analyzing a given text. If the text is a transcription of a dangrous situation or a sign of danger, you respond with "yes" and "no" if it is not
    the json should look like this

    ```
    {
        "response": "your_response"
    }
    ```

    The text that needs analysis is: $transcribedText
    EOT;

    // create a new completion request to analyze the transcribed text
    $request = new \Ollama\Requests\CompletionRequest(
        model: "phi3.5", // the model name
        prompt: $prompt, // the prompt to be sent to ollama
        format: "json" // the response format from ollama
    );

    $response = $completionApi->getCompletion($request);

    $jsonResponse = json_decode($response->response, true);

    if ($jsonResponse['response'] === 'yes') {
        // send emails to the registered contacts 
        $conn = new mysqli('localhost', 'root', '', 'guardian');

        if ($conn->connect_error) {
            throw new dbConnectionErrorException($conn->connect_error);
        }

        $sql = "SELECT * FROM emergency_contacts LIMIT 3";
        $stmt = $conn->prepare($sql);

        if (!$stmt->execute()) {
            throw new executionErrorException($stmt->error);
        }

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new noContactsException();
        }

        while ($row = $result->fetch_assoc()) {

            if ($row['emergency_contact'] !== null && $row['emergency_contact'] !== '' && $row['emergency_contact'] !== ' ') {

                // twilio set up // fill in your credentials
                $phone = '';
                $account_sid = '';
                $auth_token = '';
                $fromPhone = '';
                $toPhone = $row['emergency_contact'];

                $client2 = new Client($account_sid, $auth_token);

                $message = $client2->messages->create(
                    $toPhone,
                    [
                        "from" => $fromPhone,
                        "body" => 'guardian detected a sign of danger around'
                    ]
                );

                $progressMessages .= "twilio sent the message $toPhone with the sid $message->sid";
            }
        }
    } else if ($jsonResponse['response'] === 'no') {

        // send emails to the registered contacts 
        $conn = new mysqli('localhost', 'root', '', 'guardian');

        if ($conn->connect_error) {
            throw new dbConnectionErrorException($conn->connect_error);
        }

        $sql = "SELECT * FROM emergency_contacts LIMIT 3";
        $stmt = $conn->prepare($sql);

        if (!$stmt->execute()) {
            throw new executionErrorException($stmt->error);
        }

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new noContactsException();
        }

        // sending alerts to all registered contacts
        while ($row = $result->fetch_assoc()) {

            if ($row['emergency_contact'] !== null && $row['emergency_contact'] !== '' && $row['emergency_contact'] !== ' ') {

                // twilio set up
                $phone = '';
                $account_sid = '';
                $auth_token = '';
                $fromPhone = '';
                $toPhone = $row['emergency_contact'];

                $client2 = new Client($account_sid, $auth_token);

                $message = $client2->messages->create(
                    $toPhone,
                    [
                        "from" => $fromPhone,
                        "body" => 'there is no danger'
                    ]
                );

                $progressMessages .= "twilio sent the message $toPhone with the sid: $message->sid";
            }
        }
    }

    $progressResponse = [
        'error' => 'okay',
        'status' => 'success',
        'message' => 'everything went well',
        'progressMessages' => $progressMessages
    ];
} catch (dbConnectionErrorException $e) {
    $progressResponse = [
        'error' => htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
        'status' => 'dbConnectionError',
        'message' => 'something went wrong during connection to the database. Please, check the database credentials',
        'progressMessages' => $progressMessages
    ];
} catch (uploadingErrorException $e) {
    $progressResponse = [
        'error' => htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
        'status' => 'uploadError',
        'message' => 'something went wrong during uploading of the audio file. Maybe you should try again.',
        'progressMessages' => $progressMessages
    ];
}  catch (WhisperException $e) {
    $progressResponse = [
        'error' => htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
        'status' => 'whisperError',
        'message' => 'something went wrong when using whisper.',
        'progressMessages' => $progressMessages
    ];
} catch (executionErrorException $e) {
    $progressResponse = [
        'error' => htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
        'status' => 'executionError',
        'message' => 'something went wrong during query execution. double check the queries.',
        'progressMessages' => $progressMessages
    ];
} catch (mailingErrorException $e) {
    $progressResponse = [
        'error' => htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
        'status' => 'mailingError',
        'message' => 'something went wrong during mailing. Maybe check the internet connection.',
        'progressMessages' => $progressMessages
    ];
} catch (TwilioException $e){
    $progressResponse = [
        'error' => htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
        'status' => 'messagingError',
        'message' => 'something went wrong while sending alert messages.',
        'progressMessages' => $progressMessages
    ];
} catch (Exception $e) {
    $progressResponse = [
        'error' => htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
        'status' => 'unknownError',
        'message' => 'something went wrong, please contact the developers.',
        'progressMessages' => $progressMessages
    ];
} finally {
    $conn->close();
    $progressJsonResponse = json_encode($progressResponse, JSON_UNESCAPED_UNICODE);
    echo $progressJsonResponse;
}
