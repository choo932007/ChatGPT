<?php
require_once(__DIR__."/vendor/autoload.php");

use Orhanerday\OpenAi\OpenAi;
use League\CommonMark\CommonMarkConverter;

header( "Content-Type: application/json" );

$context = json_decode( $_POST['context'] ?? "[]" ) ?: [];


//Enter your ChatGPT api token
$open_ai = new OpenAi('Your API Token');


$prompt = "\n\n";

if( empty( $context ) ) {
    $prompt .= "
    Question:\n'Can I ask you a question?
    \n\nAnswer:\nNo
    \n\n Question:\n'HI
    \n\nAnswer:\n How are we
    ";
    
    $please_use_above = "";
} else {
    
    $prompt .= "";
    $context = array_slice( $context, -5 );
    foreach( $context as $message ) {
        $prompt .= "Question:\n" . $message[0] . "\n\nAnswer:\n" . $message[1] . "\n\n";
    }
    $please_use_above = ". Please use the questions and answers above as context for the answer.";
}

$prompt = $prompt . "Question:\n" . $_POST['message'] . $please_use_above . "\n\nAnswer:\n\n";

$complete = json_decode( $open_ai->completion( [
    'model' => 'gpt-3.5-turbo',
    'prompt' => $prompt,
    'temperature' => 0.9,
    'max_tokens' => 2000, 
    'top_p' => 1,
    'frequency_penalty' => 0,
    'presence_penalty' => 0,
    'stop' => [
        "\nNote:",
        "\nQuestion:"
    ]
] ) );


if( isset( $complete->choices[0]->text ) ) {
    $text = str_replace( "\\n", "\n", $complete->choices[0]->text );
} elseif( isset( $complete->error->message ) ) {
    $text = $complete->error->message;
} else {
    $text = "Sorry, but I don't know how to answer that.";
}


$converter = new CommonMarkConverter();
$styled = $converter->convert( $text );

echo json_encode( [
    "message" => (string)$styled,
    "raw_message" => $text,
    "status" => "success",
] );
