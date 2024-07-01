<?
header("Content-Typpe: application/json");

// Check if name query parameter is set
if(!isset($_GET["name"]) || empty($_GET["name"])) {
	http_response_code(400);

	$response = array(
		"status" => "error",
		"details" => [
			"code" => "INVALID REQUEST",
			"message" => "Parameter 'name' not found"
		]
	);

	echo json_encode($response);

	exit();
}


function getClientIp() {
	$ipAddress = '';

	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		// Check if IP is from shared internet
		$ipAddress = $_SERVER['HTTP_CLIENT_IP'];
	 } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
 		// Check if IP is passed from proxy
	 $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		// Check if IP is from remote address
		 $ipAddress = $_SERVER['REMOTE_ADDR'];
  	}
	
	return $ipAddress;
}



function sanitiseAndFilterText($input) {
	return preg_replace( // remove  any non-printable character
		'/[\x00-\x1F\x7F]/', '',
		filter_var( // filter out malicious characters
			htmlspecialchars(
				trim($input)
			),
			FILTER_SANITIZE_FULL_SPECIAL_CHARS
		)
	);
}


function getIpGeolocationInfo($ipAddress) {
	// Gets the geolocation info using the given IP Address
	$url = "http://ip-api.com/php/{$ipAddress}";
	$response = file_get_contents($url);
	$data = json_decode($response, TRUE);

	if($data && $data["status"] === "success") {
		return $data;
	}

	return NULL;
}

$name = sanitiseAndFilterText($_GET["name"]);
$ipAddress = getClientIp();
$locationInfo = getIpGeoLocationInfo($ipAddress);
$country = !is_null($locationInfo) ? $locationInfo["country"] : "Unknown country";
$city = !is_null($locationInfo) ? $locationInfo["city"] : "Unknown City";

$greeting = "Hello, {$name}!, the temperature is 11 degrees in {$city}, {$country}";

$response = array(
	"client_ip" => $ipAddress,
	"location" => $city,
	"greeting" => $greeting
);

// Send response
http_response_code(200);
echo json_encode($response);
