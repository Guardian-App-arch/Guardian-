<?php
require __DIR__ . '/vendor/autoload.php';

use Codewithkyrian\Whisper\Whisper;
use Codewithkyrian\Whisper\ModelLoader;
use function Codewithkyrian\Whisper\readAudio;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audio'])) {

    $audioDir = 'audio/';
    $audioFile = $audioDir . basename($_FILES['audio']['name']);
    $prompt = "answer with 'yes' or 'no'. is this text sign of a dangerous situation: ";

    if (!is_dir($audioDir)) {
        mkdir($audioDir, '0777', true);
    }

    if (move_uploaded_file($_FILES['audio']['tmp_name'], $audioFile)) {

        $modelPath = ModelLoader::loadModel('base.en', __DIR__ . '/models');
        $whisper = Whisper::fromPretrained('base.en', baseDir: __DIR__ . '/models');

        $audio = readAudio(__DIR__ . '/' . $audioFile);
        
        $segments = $whisper->transcribe($audio, 8);

        foreach ($segments as $segment) {
            echo $segment->startTimestamp . ': ' . $segment->text;
        }

    }
}
