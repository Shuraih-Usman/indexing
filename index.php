<?php
require 'vendor/autoload.php';

use Google\Client;
use GuzzleHttp\Client as GuzzleClient;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $urls = explode("\n", trim($_POST['urls']));
    $urls = array_filter($urls, function($url) {
        return filter_var(trim($url), FILTER_VALIDATE_URL);
    });

    $errors = [];
    $success = false;
    $message = "";

    if (empty($urls)) {

        $errors[] = 'No valid URLs provided';
    }

      $responses = [];
      $client = new GuzzleClient();
      foreach ($urls as $url) {
          $response = indexUrl($client, $url);
          
          $parts = explode('--batch_', $response);
          foreach ($parts as $part) {
              if (trim($part) === '') {
                  continue;
              }
              preg_match('/\{.*\}/s', $part, $matches);
              if (!empty($matches)) {
                  $decodedResponse = json_decode($matches[0], true);
                  if ($decodedResponse === null) {

                    $errors[] =  'Failed to decode API response';
                  }


                  if (isset($decodedResponse['error']) && $decodedResponse['error']['code'] == 429) {
                      $errors[] = 'Quota limit exceeded for Google indexing API pls try again after 24 hours';
                  }
                  $responses[] = [
                      'url' => $url,
                      'response' => $decodedResponse
                  ];
              }
          }
      }
  
      $logsDir = __DIR__ . '/logs';
      if (!is_dir($logsDir)) {
          if (!mkdir($logsDir, 0777, true) && !is_dir($logsDir)) {
              $errors[] = 'Failed to create logs directory';
          }
      }
  
      $logFile = $logsDir . '/' . date('Y-m-d_H-i-s') . '.json';
      if (file_put_contents($logFile, json_encode($responses, JSON_PRETTY_PRINT)) === false) {
          $errors[] = 'Failed to write to log file';
      }

      if(count($errors) > 0) {
        $message = $errors[0];
      } else {
        $success =  true;
        $message = "URLs indexed successfully";
      }
  
      echo json_encode(['success' => $success, 'message' => $message, 'responses' => $responses]);
  }

function indexUrl($client, $url) {
    $keyFilePath = 'key.json';
    $googleClient = new Client();
    $googleClient->setAuthConfig($keyFilePath);
    $googleClient->addScope('https://www.googleapis.com/auth/indexing');
    $googleClient->fetchAccessTokenWithAssertion();
    $accessToken = $googleClient->getAccessToken()['access_token'];

    $content = json_encode(['url' => $url, 'type' => 'URL_UPDATED']);
    $boundary = '-------314159265358979323846';
    $body = "--$boundary\n";
    $body .= "Content-Type: application/http\n\n";
    $body .= "POST /v3/urlNotifications:publish HTTP/1.1\n";
    $body .= "Content-Type: application/json\n\n";
    $body .= "$content\n";
    $body .= "--$boundary--";

    $response = $client->request('POST', 'https://indexing.googleapis.com/batch', [
        'headers' => [
            'Content-Type' => 'multipart/mixed; boundary=' . $boundary,
            'Authorization' => 'Bearer ' . $accessToken
        ],
        'body' => $body
    ]);

    return $response->getBody()->getContents();
}
?>
