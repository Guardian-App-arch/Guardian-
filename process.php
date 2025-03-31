<?php
require __DIR__ . '/vendor/autoload.php';

use Codewithkyrian\Whisper\Whisper; //include necessary whisper libs for loading base.en and transcribing the audio
use Codewithkyrian\Whisper\ModelLoader; // 
use function Codewithkyrian\Whisper\readAudio;

$client = new \Ollama\Client\OllamaClient(); // a new client for ollama
$completionApi = new \Ollama\Api\Completion($client); // creating a new completionApi for text completion
$transcribedText = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audio'])) { //check if the audio is received

    $audioDir = 'audio/'; // where the audio is stored
    $audioFile = $audioDir . basename($_FILES['audio']['name']);

    if (!is_dir($audioDir)) { // creates the audio if doesn't exist
        mkdir($audioDir, '0777', true);
    }

    if (move_uploaded_file($_FILES['audio']['tmp_name'], $audioFile)) { //uploads the audio to the server

        //loads the model
        $modelPath = ModelLoader::loadModel('base.en', __DIR__ . '/models');
        $whisper = Whisper::fromPretrained('base.en', baseDir: __DIR__ . '/models');

        //reads the audio
        $audio = readAudio(__DIR__ . '/' . $audioFile);

        //transcribe the audio and store the text in an associative array 
        $segments = $whisper->transcribe($audio, 8);

        // concatenate the text (the transcribed text)
        foreach ($segments as $segment) {
            $transcribedText = $transcribedText . $segment->startTimestamp . ': ' . $segment->text;
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

        echo $jsonResponse['response'];
    }
}
