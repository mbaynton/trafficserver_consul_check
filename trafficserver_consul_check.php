<?php
/**
 * Just checks if valid JSON coming back from trafficserver stats plugin for
 * now.
 */
require __DIR__ . '/vendor/autoload.php';

function report($code, $msg) {
  fwrite(STDERR, $msg . "\n");
  exit($code);
}

// Stats are retrievable on a secret path set in the stats plugin's
// configuration. We'll read it from a file adjacent to us in the filesystem.
$secret_path = file_get_contents(__DIR__ . '/statspath_secret');
if (empty($secret_path)) {
  report (1, 'Check script needs to know the secret stats path.');
}

$client = new GuzzleHttp\Client();
try {
  $res = $client->get("http://127.0.0.1/$secret_path",
    [
      'timeout' => 6,
    ]
  );
} catch (Guzzle\Http\Exception\BadResponseException $e) {
  report (2, "HTTP transactions with Trafficserver are failing: " . $e->getMessage());
}

try {
  $res->json();
} catch (\Exception $e) {
  report (2, "Did not receive expected JSON from Trafficserver.");
}
