<?php
header('Content-type: text/xml');

//via http://webtutsdepot.com/2009/10/13/amazon-signed-request-php/
class Amazon
{

	// public key
	var $publicKey = "AKIAJ6R3SJQHJKKVTTGA";
	require('amazonconfig.php');
	// affiliate tag
	var $affiliateTag='YourAssociateTagHere';
		
		/**
		*Get a signed URL
		*@param string $region used to define country
		*@param array $param used to build url
		*@return array $signature returns the signed string and its components
		*/
	public function generateSignature($param)
	{
		// url basics
		$signature['method']='GET';
		$signature['host']='webservices.amazon.com'.$param['region'];
		$signature['uri']='/onca/xml';

	    // necessary parameters
		$param['Service'] = "AWSECommerceService";
	    $param['AWSAccessKeyId'] = $this->publicKey;
	    $param['Timestamp'] = gmdate("Y-m-d\TH:i:s\Z");
	    $param['Version'] = '2009-10-01';
		ksort($param);
	    foreach ($param as $key=>$value)
	    {
	        $key = str_replace("%7E", "~", rawurlencode($key));
	        $value = str_replace("%7E", "~", rawurlencode($value));
	        $queryParamsUrl[] = $key."=".$value;
	    }
		// glue all the  "params=value"'s with an ampersand
	    $signature['queryUrl']= implode("&", $queryParamsUrl);
		
	    // we'll use this string to make the signature
		$StringToSign = $signature['method']."\n".$signature['host']."\n".$signature['uri']."\n".$signature['queryUrl'];
	    // make signature
	    $signature['string'] = str_replace("%7E", "~", 
			rawurlencode(
				base64_encode(
					hash_hmac("sha256",$StringToSign,$this->privateKey,True
					)
				)
			)
		);
	    return $signature;
	}
		/**
		* Get signed url response
		* @param string $region
		* @param array $params
		* @return string $signedUrl a query url with signature
		*/
	public function getSignedUrl($params)
	{
		$signature=$this->generateSignature($params);

		return $signedUrl= "http://".$signature['host'].$signature['uri'].'?'.$signature['queryUrl'].'&Signature='.$signature['string'];
	}
}

$Amazon=new Amazon();
/*
$parameters=array(
//"region"=>"com",
"AssociateTag"=>'YourAssociateTagHere',
//'ResponseGroup'=>'Images',
"Operation"=>"ItemSearch",
"SearchIndex"=>"Books",
"Keywords"=>"0321776402"
); */
$parameters=array(
"AssociateTag"=>'YourAssociateTagHere',
"Operation"=>"ItemLookup",
//"SearchIndex"=>"Books",
"ItemId"=>"0321776402",
"ResponseGroup"=>"OfferFull"
);

$queryUrl=$Amazon->getSignedUrl($parameters);
$response = file_get_contents($queryUrl);
echo $response;
	$parsed_xml = simplexml_load_string($response);
	//printSearchResults($parsed_xml, $SearchIndex);

function printSearchResults($parsed_xml, $SearchIndex){
	$numOfItems = $parsed_xml->Items->TotalResults;
	if($numOfItems>0){
		print("<table>");
		foreach($parsed_xml->Items->Item as $current){
			print("<tr><td><b>".$current->ItemAttributes->Title."</b>");
			if (isset($current->ItemAttributes->Title)) {
				print("<br>Title: ".$current->ItemAttributes->Title);
			} elseif (isset($current->ItemAttributes->Author)) {
				print("<br>Author: ".$current->ItemAttributes->Author);
			} elseif (isset($current->Offers->Offer->Price->FormattedPrice)) {
				print("<br>Price:".$current->Offers->Offer->Price->FormattedPrice);
			}
			echo "</td></tr>";
		}
		print("</table>");
	} else {
		print("<center>No matches found.</center>");
	}
}


/*
//Enter your IDs
define("Access_Key_ID", "AKIAJ6R3SJQHJKKVTTGA");
define("Associate_tag", "compar03-20");

//Set up the operation in the request
function ItemSearch($SearchIndex, $Keywords) {
	//Set the values for some of the parameters
	$Operation = "ItemSearch";
	$Version = "2011-08-01";
	$ResponseGroup = "ItemAttributes,Offers";
	//User interface provides values
	//for $SearchIndex and $Keywords
	
	//Define the request
	$request=
	     "http://webservices.amazon.com/onca/xml"
	   . "?Service=AWSECommerceService"
	//   . "&AssociateTag=" . Associate_tag
	   . "&AWSAccessKeyId=" . Access_Key_ID
	   . "&Operation=" . $Operation
	   . "&Version=" . $Version
	   . "&Service=AWSECommerceService"
	   . "&SearchIndex=" . $SearchIndex
	   . "&Keywords=" . $Keywords;
	//   . "&Signature=" . [Request Signature]
	//   . "&ResponseGroup=" . $ResponseGroup;
	
	//Catch the response in the $response object
	$response = file_get_contents($request);
	$parsed_xml = simplexml_load_string($response);
	printSearchResults($parsed_xml, $SearchIndex);
}
function printSearchResults($parsed_xml, $SearchIndex){
	$numOfItems = $parsed_xml->Items->TotalResults;
	if($numOfItems>0){
		print("<table>");
		foreach($parsed_xml->Items->Item as $current){
			print("<tr><td><b>".$current->ItemAttributes->Title."</b>");
			if (isset($current->ItemAttributes->Title)) {
				print("<br>Title: ".$current->ItemAttributes->Title);
			} elseif (isset($current->ItemAttributes->Author)) {
				print("<br>Author: ".$current->ItemAttributes->Author);
			} elseif (isset($current->Offers->Offer->Price->FormattedPrice)) {
				print("<br>Price:".$current->Offers->Offer->Price->FormattedPrice);
			}
			echo "</td></tr>";
		}
		print("</table>");
	} else {
		print("<center>No matches found.</center>");
	}
}
echo "fds";
ItemSearch("Books", "0321776402");
*/
?>