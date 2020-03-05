<?php

#
# Uses this PHP Rest Client: https://github.com/tcdent/php-restclient
#

include '../php-restclient/restclient.php';
# Sandbox:
$key = 'sandboxapikey';
$api = new RestClient([
	'base_url' => "https://api-na.hosted.exlibrisgroup.com/",
	'headers' => ['Authorization' => 'apikey '."$key",
				 'Accept' => 'application/json',
				],
]);


/*
* @param $vendor_code identifies a vendor in the aquisitions system
* returns json object describing the requested vendor's info in the system
*/

function getVendor($vendor_code){
	$vendor = $api->get("almaws/v1/acq/vendors/$vendor_code");
	
	//SUCCESS: GOT INDIVIDUAL VENDOR OBJECT
	if($vendor->info->http_code == 200) {
		$output = $vendor->response;
		$json = json_decode($output);
       		return $json;
	} 
	//COULDN'T GET INDIVIDUAL VENDOR OBJECT
        else {
		return false;
	}
}

/*
* @param $limit how many vendors to return
* returns an object listing the names and codes for vendors in aquisitions system
*/

function getVendors($limit){
	$vendors = $api->get("almaws/v1/acq/vendors?limit=$limit");
	//Success: got list of vendors
	if ($vendors->info->http_code == 200) {
		$output = $vendors->response;
		$json = json_decode($output);
		return $json;
	}
	//Couldn't get list of vendors
	else { 
		return false;
	}
}

//print each vendor note
if ($vendors = getVendors(1)) {
	foreach ($vendors as $record) {
		//get and modify its vendor object
                $vendor_code = $record->code;
		if ($vendor = getVendor($record)) {
			$vendor_note = $vendor->financial_sys_code;
			print $vendor_note . PHP_EOL;
		}
		else {
			print $vendor_code . " not found" . PHP_EOL;
		}
	}
}

?>
