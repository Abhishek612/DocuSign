<?php
// 
// DocuSign API Quickstart - Embedded Signing 
// 
// Download PHP client:  https://github.com/docusign/DocuSign-PHP-Client
require_once './src/DocuSign_Client.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/DocuSign-PHP-Client/src/service/DocuSign_RequestSignatureService.php';
require_once './src/service/DocuSign_ViewsService.php';
//=======================================================================================================================
// STEP 1: Login API 
//=======================================================================================================================
// client configuration
session_start();
if(isset($_FILES['file'])){
      $errors= array();
      $file_name = $_FILES['file']['name'];
      $file_size =$_FILES['file']['size'];
      $file_tmp =$_FILES['file']['tmp_name'];
      $file_type=$_FILES['file']['type'];
      $file_ext=strtolower(end(explode('.',$_FILES['file']['name'])));
      
      $expensions= array("doc","pdf","docx");
      
      if(in_array($file_ext,$expensions)=== false){
         $errors[]="extension not allowed, please choose a pdf or doc file.";
      }
      
      if($file_size > 2097152){
         $errors[]='File size must be excately 2 MB';
      }
      
      if(empty($errors)==true){
         move_uploaded_file($file_tmp,"docs/".$file_name);
         echo "Success";
      }else{
         print_r($errors);
      }
   }



$testConfig = array(
	// Enter your Integrator Key, Email, and Password
	'integrator_key' => "Write_your_developer_acocunt_Integrator_Key", 'email' => "Write_your_developer_acocunt_Email", 'password' => "Write_your_developer_acocunt_Password",
	// API version and environment (demo, www, etc)
	'version' => 'v2', 'environment' => 'demo'
);
// instantiate client object and call Login API
$client = new DocuSign_Client($testConfig);
if( $client->hasError() )
{
	echo "\nError encountered in client, error is: " . $client->getErrorMessage() . "\n";
	return;
}
//=======================================================================================================================
// STEP 2: Create and Send Envelope API (with embedded recipient)
//=======================================================================================================================

/*
"recipients": {
    "signers": [
        {
            "name": "Abhishek",
            "email": "abhishekmail@outlook.com",
            "recipientId": "1",
            "routingOrder": "1",
        },
        {
            "name": "Ankit",
            "email": "ankitmail@outlook.com",
            "recipientId": "2",
            "routingOrder": "2",
        }
    ]
}
*/


$service = new DocuSign_RequestSignatureService($client);
// Configure envelope settings, document(s), and recipient(s)
$emailSubject = "Please sign my document";
$emailBlurb = "This goes in the email body";	
// create one signHere tab for the recipient
$tabs = array( "signHereTabs" => array( 
	array( "documentId"=>"1","pageNumber" => "1","xPosition" => "460","yPosition" => "500" )));
$recipients = array( new DocuSign_Recipient( "1", "1", $_POST['recipientName'], $_POST['recipientEmail'], "101", 'signers', $tabs));
$documents = array( new DocuSign_Document($file_name, "1", file_get_contents("docs/".$file_name) ));
// "sent" to send immediately, "created" to save as draft in your account	
$status = 'sent'; 
//*** Send the signature request!
$response = $service->signature->createEnvelopeFromDocument( 
	$emailSubject, $emailBlurb, $status, $documents, $recipients, array() );
echo "\n-- Results --\n\n";
print_r($response);	
//=======================================================================================================================
// STEP 3: Request Recipient View API (aka Signing URL)
//=======================================================================================================================
// Now get the recipient view
$service = new DocuSign_ViewsService($client);
$returnUrl = "http://www.docusign.com/developer-center";
$authMethod = "email";
$envelopeId = $response->envelopeId;	
$response = $service->views->getRecipientView( 	$returnUrl, 
						$envelopeId, 
						$_POST['recipientName'], 
						$_POST['recipientEmail'],
						"101",
						$authMethod );	
//echo "\nOpen the following URL to sign the document:\n\n";
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
// More headers
$headers .= 'From: <austnews>' . "\r\n";
mail($_POST['recipientEmail'],"DocuSign",$_POST['docMessage']."<br>Open the following URL to sign the document: ".$response->url,$headers);
$_SESSION['succMsg'] = "An url has been sent to the recipient successfully.";
header('Location:http://projects.olive.co.in/DocuSign-PHP-Client/');
print_r($response);
?>