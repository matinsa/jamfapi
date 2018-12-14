<?php

#Version 1
#Used external database to track check in and out
#Version 2
#Used EAs within the JSS to track check in and out. Smart group, API call for the full group and then API call for every machine 
#Version 2.5
#Used EAs within the JSS. Advanced Computer Search with certain Display fields


#Requirements
#----------------------------------------------------------------------------------------
#Advanaced Computer Search to find all Loaner computer
#Fields need to be shown in the Search - Name, DateReturned, DateOut, Availability, Username, Department, Serial Number, JSS ID
#Extension Attributes Needed to be added into the JSS - DateReturned, DateOut, Availability
#EA - DateReturned and DateOut set as Data Type date
#EA - Availability set as Input Type Pop Up Menu, choices Yes and No

#global settings
$jssAPI = 'myjamfpro/JSSResource/';
$username = 'USER';
$password = 'PASS';

function PUTintoCasper($JSSResource, $Avail) {
#PUT info into the JSS using API

	$userpass = $GLOBALS['username'].":".$GLOBALS['password'];
	$remote_url = $GLOBALS['jssAPI'] . $JSSResource;
	$xml = "";
	
	
	if ($Avail == "Yes"){
#checking out a laptop	
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?><computer><location><username></username><real_name></real_name><email_address></email_address><department></department></location><extension_attributes><extension_attribute><id>69</id><name>Availability</name><type>String</type><value>".$Avail."</value></extension_attribute><extension_attribute><id>68</id><name>DateReturned</name><type>Date</type><value>".$_POST['DateReturned']."</value></extension_attribute></extension_attributes></computer>";

	} elseif ($Avail == "No"){
#checking in a laptop	
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?><computer><location><username>".$_POST['User']."</username></location><extension_attributes><extension_attribute><id>69</id><name>Availability</name><type>String</type><value>".$Avail."</value></extension_attribute><extension_attribute><id>67</id><name>DateOut</name><type>Date</type><value>".$_POST['DateOut']."</value></extension_attribute></extension_attributes></computer>";

	}

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $remote_url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml', 'Accept: text/xml', 'Content-Length: ' . strlen($xml),'X-HTTP-Method-Override: PUT'));
	curl_setopt($ch, CURLOPT_FAILONERROR, true);  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_USERPWD, $GLOBALS['username'].":".$GLOBALS['password']);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); 
	curl_setopt($ch, CURLOPT_STDERR, $out); 
    
	$response = curl_exec($ch);
	curl_close($ch);	
    
	fclose($out);  


	
}

function ReadJSS($JSSResource) {
#GET info from JSS using the API

	$remote_url = $GLOBALS['jssAPI'] . $JSSResource;
    
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $remote_url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json'));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	curl_setopt($curl, CURLOPT_USERPWD, $GLOBALS['username'].":".$GLOBALS['password']);
	
	$result = curl_exec($curl);
	curl_close($curl);	
	
	return $result;

}

If ($_POST){
#If there's a POST available

	if ($_POST["avail"] == "checkout"){
		
		PUTintoCasper("computers/serialnumber/".$_POST['laptop'], "No");

	}
	if ($_POST["avail"] == "checkin"){
		
		PUTintoCasper("computers/serialnumber/".$_POST['laptop'], "Yes");
	
	}

}
   
?>   

<HTML>
<HEAD>
<style type="text/css">
<!--
body {
    background-image: url("YellowLinedPaper.jpg");
    background-color: #fcf5ad;
    background-size: 100% 30%; 

}

.TheBody {
	margin-left: 15%;
	margin-right: 12%;
	
}

table {
	width: 100%;
}

table, tr, td {


    border-collapse: collapse;
    text-align: center;
}

a:link {
    text-decoration: none;
    color: black;
}

a:visited {
    text-decoration: none;
    color: black;
}

a:hover {
    text-decoration: underline;
    color: gray;
}

a:active {
    text-decoration: underline;
}
</style>
<script>
function DoubleCheck(LaptopConfirm){


	var r = confirm("Confirm update to " + LaptopConfirm);
	
	if (r == true) {
	    document.getElementById('laptopsform').submit();
	    return true;
	
	} else {
	    x = "You pressed Cancel!";
	    document.getElementById('laptopsform').reset();
	    return false;
	  

	}
}
</script>
<TITLE>Loaner Checkout</TITLE>
</HEAD>
<BODY>
<div class="TheBody">
<BR>
<table>
	<tr style="font-size: 15pt; text-decoration: underline;">	
		<td>Laptop</td>
		<td>User</td>
		<td>Date Out</td>
		<td>Date Returned</td>
		<td></td>
	</tr>

<?php
#Advanced Saved Computer Search ID needed here
#Fields need to be shown in the Search - Name, DateReturned, DateOut, Availability, Username, Department, Serial Number, JSS ID
	$Read_result=ReadJSS("computerreports/id/ENTER-REPORT-ID-HERE");

	$TodayDate = date('Y/m/d');

	$jss_array = json_decode($Read_result, true);
	$device_array = $jss_array['computer_reports'];
	foreach($device_array as $device){
		echo "<form action=\"\" method=\"Post\" name=\"laptopsform\" id=\"laptopsform\" onSubmit=\"return DoubleCheck('".$device['Computer_Name']."');\">";
		echo "<tr style=\"font-size: 12pt;\">";
		echo "<input type=\"hidden\" name=\"laptop\" value=\"".$device['Serial_Number']."\">";
		echo "<td><a href=\"https://casper.saes.org:8443/computers.html?id=".$device['JSS_Computer_ID']."\" target=\"_blank\">".$device['Computer_Name']."</a></td>";
		if($device['Availability'] == "No"){
			echo "<td><a href=\"mailto:".$device['Username']."@saes.org\" target=\"_blank\">".$device['Username']."</a></td>";
			echo "<td>".$device['DateOut']."</td>";
			echo "<td><input type=\"date\" name=\"DateReturned\" value=\"".$TodayDate."\"></td>";
			echo "<input type=\"hidden\"  name=\"avail\" value=\"checkin\">";

			echo "<td><input type=\"submit\" value=\"Submit\" id=\"submit\" name=\"submit\"></td>";
			echo "</form>";
		} else if ($device['Availability'] == "Yes"){
			echo "<td><input type=\"text\" name=\"User\"></td>";			
				
			echo "<td><input type=\"date\" name=\"DateOut\" value=\"".$TodayDate."\"></td>";
			echo "<td>".$device['DateReturned']."</td>";
						
			echo "<input type=\"hidden\"  name=\"avail\" value=\"checkout\">";
			echo "<td><input type=\"submit\" value=\"Submit\" id=\"submit\" name=\"submit\"></td>";
			echo "</form>";
		}
		
	}
	
?>	
</div>
</BODY>
</HTML> 

