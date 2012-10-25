<?php

// Create cookie file if it doesn't exists
$cookieFile = "myCookiefile.txt";
if (!file_exists($cookieFile)) {
    $fh = fopen($cookieFile, "w");
    fwrite($fh, "");
    fclose($fh);
}

// Initialize the cUrl connection
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://www.google.com/finance?q=spy');

// Set the POST data
//$data = array('UserName' => 'Foo');
//curl_setopt($ch, CURLOPT_POST, 1);
//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);

// Set the COOKIE files
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);

// Send request and print results
echo curl_exec($ch);

// Close the connection
curl_close($ch);
?>
 