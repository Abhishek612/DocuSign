<?php
/*
 * Copyright 2013 DocuSign Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once 'testConfig.php';
require_once '../../src/DocuSign_Client.php';
require_once '../../src/service/DocuSign_ConnectService.php';

$client = new DocuSign_Client($testConfig);
$service = new DocuSign_ConnectService($client);
$accountId = $testConfig['account_id'];

$connects = $service->connect->getConnectConfiguration($accountId);
# echo "Connections: "; print_r ($connects);

echo '.'; assert (is_array($connects->configurations));
echo '.'; assert (count($connects->configurations) == $connects->totalRecords);
$initial_connect_records = $connects->totalRecords;

$urlToPublishTo = "http:foo.com";
$urlToPublishTo2 = "http:foo.com";
$connectName = "Test Connect"; 
$params = array(
	'urlToPublishTo' => $urlToPublishTo,
	'name' => $connectName,
	'envelopeEvents' => array('Sent', 'Delivered'),
	'recipientEvents' => array('Delivered', 'Completed')
	);

$connect = $service->connect->createConnectConfiguration(	
		$accountId, # string	Account Id
		$params);
		# params is an associative array holding the parameters. Most are optional.
		# Valid keys:
		# urlToPublishTo, # Required. string	Client's incoming webhook url
		# allUsers,	# boolean	Track events initiated by all users.
		# allowEnvelopePublish, # boolean	Enables users to publish processed events.
		# enableLog, # boolean	Enables logging on prcoessed events. Log only maintains the last 100 events.
		# envelopeEvents, # array list of 'Envelope' related events to track. Events: Sent, Delivered, Signed, Completed, Declined, Voided
		# includeDocuments, # boolean	Include envelope documents
		# includeSenderAccountasCustomField, # boolean	Include sender account as Custom Field.
		# includeTimeZoneInformation, # boolean	Include time zone information.
		# name, # string	name of the connection
		# recipientEvents, # array list of 'Recipient' related events to track. Events: Sent, AutoResponsed(Delivery Failed), Delivered, Completed, Declined, AuthenticationFailure
		# requiresAcknowledgement, # boolean	true or false
		# signMessagewithX509Certificate,	# boolean	Signs message with an X509 certificate.
		# soapNamespace, # string	Soap method namespace. Required if useSoapInterface is true.
		# useSoapInterface, # boolean	Set to true if the urlToPublishTo is a SOAP endpoint
		# userIds # array list of user Id's. Required if allUsers is false

# echo "Create a connection: "; print_r ($connect);
$connectId = $connect->connectId;
echo '.'; assert (is_array($connect->envelopeEvents));
echo '.'; assert (is_array($connect->recipientEvents));
echo '.'; assert (is_array($connect->userIds));

echo '.'; assert (count($connect->envelopeEvents) === 2);
echo '.'; assert (count($connect->recipientEvents) === 2);
echo '.'; assert (count($connect->userIds) === 0);

echo '.'; assert ($connect->urlToPublishTo === $urlToPublishTo);
echo '.'; assert ($connect->name === $connectName);
		#		[connectId] => 123
		#		[configurationType] => false
		#		[urlToPublishTo] => http:foo.com
		#		[name] => Test Connect

# check that we now have 1 more connection
$connects = $service->connect->getConnectConfiguration($accountId);
echo '.'; assert (($initial_connect_records + 1) === intval($connects->totalRecords));

# get config by ID
$connect = $service->connect->getConnectConfigurationByID($accountId, $connectId);
# echo "Connection: "; print_r ($connect);
echo '.'; assert ($connect->configurations[0]->connectId === $connectId);
$c = $connect->configurations[0];
echo '.'; assert (is_array($c->envelopeEvents));
echo '.'; assert (is_array($c->recipientEvents));
echo '.'; assert (is_array($c->userIds));

echo '.'; assert (count($c->envelopeEvents) === 2);
echo '.'; assert (count($c->recipientEvents) === 2);
echo '.'; assert (count($c->userIds) === 0);


$params = array(
	'urlToPublishTo' => $urlToPublishTo2,
	'name' => $connectName);	
$connects = $service->connect->updateConnectConfiguration(	
		$accountId, # string	Account Id
		$connectId, # string	Connection Id
		$params);

# Confirm that the config was updated.
$connect = $service->connect->getConnectConfigurationByID($accountId, $connectId);
echo '.'; assert ($connect->configurations[0]->urlToPublishTo === $urlToPublishTo2);

# delete
$service->connect->deleteConnectConfiguration(	
		$accountId, # string	Account Id
		$connectId	# string	Connection Id
		);

# check that we are now back to the starting number of configurations
$connects = $service->connect->getConnectConfiguration($accountId);
echo '.'; assert ($initial_connect_records === $connects->totalRecords);

echo "\nDone.\n";
		