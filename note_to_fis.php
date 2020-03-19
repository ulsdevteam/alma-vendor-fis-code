<?php

#
# Uses this PHP Rest Client: https://github.com/tcdent/php-restclient
#

include '../php-restclient/restclient.php';

$key = 'yourkeygoeshere';


$api = new RestClient([
	'base_url' => "https://api-na.hosted.exlibrisgroup.com/",
	'headers' => ['Authorization' => 'apikey '."$key",
				 'Accept' => 'application/json',
				 'Content-Type'=>' application/json',
				],
]);


/*
* @param $vendor_object modified vendor object_
* replaces existing acquisitions vendor object with supplied
*/

function updateVendor($vendor_object, $vendor_code){
	global $api;
	$vendor = $api->put("almaws/v1/acq/vendors/$vendor_code",$vendor_object);
	if($vendor->info->http_code == 200) {
		return TRUE;	
	}
	else{
		print "ERROR: " . PHP_EOL;
		var_dump($vendor) . PHP_EOL;
		exit; 
	}
}

/*
* @param $vendor_code identifies a vendor in the aquisitions system
* returns php object describing the requested vendor's info in the system
*/

function getVendor($vendor_code){
	global $api;
	$vendor = $api->get("almaws/v1/acq/vendors/$vendor_code");
	
	//SUCCESS: GOT INDIVIDUAL VENDOR OBJECT
	if($vendor->info->http_code == 200) {
		$output = $vendor->response;
		$output = json_decode($output);
       		return $output;
	} 
	//COULDN'T GET INDIVIDUAL VENDOR OBJECT
        else {
		return false;
	}
}

/*
* @param $limit how many vendors to return
* @param $status active or inactive vendors according to Alma settings
* @param $offset which record to start with.  begins at 0.  helpful in looping through lots of vendors
* returns an object describing vendors in the Alma aquisitions module
*/

function getVendors($limit,$status,$offset){
	global $api;
	$vendors = $api->get("almaws/v1/acq/vendors?limit=$limit&status=$status&offset=$offset");
	//Success: got list of vendors
	if ($vendors->info->http_code == 200) {
		$output = $vendors->response;
		$output = json_decode($output);
		return $output;
	}
	//Couldn't get list of vendors
	else { 
		return false;
	}
}

//simulate actions for targeting vendor record updates and print info about irregularities
//first param is limit. API max 100.
//last param is offset.  loop through total number of records and get them 100 at a time.
$myfile = fopen("vendorFisCodeInserts.txt", "w");
for ($i=0;$i<=2;$i+=1){
if ($vendors = getVendors(1,'active',$i)) {
	foreach ($vendors->vendor as $record) {
		//get and modify its vendor object
               	//code is a required field for a record so assuming it's there
		$vendor_code = $record->code;
		if ($vendor = getVendor($vendor_code)) {
			if (property_exists($vendor,'note')) {
				if (!empty($vendor->note)){
					$vendor_note = $vendor->note['0']->content;
					if (preg_match('/(APVN=.*?\*)/',$vendor_note, $matches)) {
						$vendor_note = $matches[0];
						$vendor_note = str_replace("APVN=","","$vendor_note");
						$vendor_note = str_replace(" : APSN=","|",$vendor_note);
						$vendor_note = str_replace("*","",$vendor_note);	
						if (property_exists($vendor,'financial_sys_code')) {
							$vendor->financial_sys_code = $vendor_note;	
							$jsonvendor = json_encode((array) $vendor);
//for testing
							//PRINT INSTEAD OF UPDATE
							fwrite($myfile, $vendor_code . '       ' . $vendor->financial_sys_code . PHP_EOL);
							/*
							if (updateVendor($jsonvendor, $vendor_code)){
								print $vendor_code . " Replaced existing financial system code: ". $vendor->financial_sys_code . PHP_EOL;
							}
							else{
								print "Error updating " . $vendor_code . PHP_EOL;
							}
							*/					
						}
						else{
							$vendor = (array)$vendor;
							$vendor['financial_sys_code'] = $vendor_note;
//for testing
							//PRINT INSTEAD OF UPDATE
							fwrite($myfile, $vendor_code . '       ' . $vendor['financial_sys_code'] . PHP_EOL);
							/*
							$vendor = (object)$vendor;
							$jsonvendor = json_encode((array) $vendor);	
							if (updateVendor($jsonvendor, $vendor_code)){
								print $vendor_code . " Created new financial system code: ". $vendor->financial_sys_code . PHP_EOL;
							}
							else{
								print "Error updating " . $vendor_code . PHP_EOL;
							}*/

						}
					}
					else {
						print $vendor_code . "       " . "Note does not match pattern" . "       " . $vendor_note . PHP_EOL;
					}
				}
				else { 
					 print $vendor_code . "       " . "Empty note field" . PHP_EOL;
				}
			}
			else {
				print $vendor_code ."       " . "No Notes field" . PHP_EOL;
			}
		}
		else {
			print $vendor_code . "       " . " not found" . PHP_EOL;
		}
	}
}
}
 fclose($myfile);
?>
