<?php

#
# Uses this PHP Rest Client: https://github.com/tcdent/php-restclient
#

include '../php-restclient/restclient.php';
include 'config.php';

# Sandbox:
$key = '$sandboxkey';
# Production:
#$key = '$productionkey';

$api = new RestClient([
	'base_url' => "https://api-na.hosted.exlibrisgroup.com/",
	'headers' => ['Authorization' => 'apikey '."$key",
				 'Accept' => 'application/json',
				],
]);

$vendors = $api->get("almaws/v1/acq/vendors");

//GET ALL VENDORS
if($vendor->info->http_code == 200) {
        $output = $vendors->response;
        $json = json_decode($output);
	$vendors = $json->{'vendor'};
        //{"vendor":[{"code":"BRILL","name":"BRILL","status":null,"language":null,"licensor":null,"governmen
	foreach ($vendors as $record){
		//get and modify vendor object
                $vendor_code=$record->code;
		$vendor = $api->get("almaws/v1/acq/vendors/$vendor_code");
			//SUCCESS: GOT INDIVIDUAL VENDOR OBJECT
			if($vendor->info->http_code == 200) {
        			$output = $vendor->response;
        			$json = json_decode($output);
        			$vendor_note = $json->{'note'}['0']->{'content'};
				//IF VENDOR NOTE LOOKS LIKE WHAT WE WANT..
        			if (($vendor_note)&&(preg_match('/: APSN=/', $vendor_note))){
					//ADD IT TO FIS FIELD
        				$json->{'financial_sys_code'}=$vendor_note;
					//UPLOAD NEW VENDOR RECORD with $json as newly modified vendor record
					$vendor = $api->put("/almaws/v1/acq/vendors/$vendor_code",$json);
                                        if($vendor->info->http_code == 200) {
                                            print $vendor_code . " updated \n";
                                        }
                                        else{
                                            print $vendor_code . " failed \n" ;
                                        }
        			}
				//OTHERWISE NO FIS DATA IN NOTE
        			else{
					print $vendor_code . ': No match in Notes field' . "\n";
				}			
			} 
                        //COULDN'T GET INDIVIDUAL VENDOR OBJECT
                        else {
        			print "Error: " . $vendor->info->http_code . "\n";
        			print "Failed to find: " . $vendor_code . "\n";
			}	       
        }
} 
//ERROR GETTING VENDOR LIST
else {
    print "Error getting vendor list";
}

?>
?>
