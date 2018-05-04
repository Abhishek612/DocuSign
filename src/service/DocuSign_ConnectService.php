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

 
 /*
  *
  * The connection part of the DocuSign API includes comma separated
  * values.
  *
  * This library changes the comma separated value strings into arrays when 
  * data is received and changes arrays back to strings for data being sent to
  * DocuSign
  */
 
require_once 'DocuSign_Service.php';
require_once 'DocuSign_Resource.php';

class DocuSign_ConnectService extends DocuSign_Service {

	public $connect;

	/**
	* Constructs the internal representation of the DocuSign Connect service.
	*
	* @param DocuSign_Client $client
	*/
	public function __construct(DocuSign_Client $client) {
		parent::__construct($client);
		$this->connect = new DocuSign_ConnectResource($this);
	}
}

class DocuSign_ConnectResource extends DocuSign_Resource {

	public function __construct(DocuSign_Service $service) {
		parent::__construct($service);
	}
	
	private $csvElements = array(
			'envelopeEvents',
			'recipientEvents',
			'userIds'
			);
	
	private $booleanElements = array(
			'allUsers',
			'allowEnvelopePublish', 
			'enableLog',
			'includeDocuments', 
			'includeSenderAccountasCustomField', 
			'includeTimeZoneInformation', 
			'requiresAcknowledgement',
			'signMessagewithX509Certificate',
			'useSoapInterface'
			);
	
	private function setURL($tail) {
		$this->url = 'https://' . $this->client->getEnvironment() . '.docusign.net/restapi/' . $this->client->getVersion() . $tail;
	}

	private function setBooleans($data, $elements) {
		foreach ($data as $key => &$value) {
			if (in_array ($key, $elements)) {
				$value = (bool)$value;
			}
		}
		return $data;
	}
	
	private function setCSVs($data, $elements) {
		# Set specific elements to be comma separated text instead of arrays
		foreach ($data as $key => &$value) {
			if (in_array ($key, $elements)) {
				# $value is currently an array
				$value = implode(",", $value);
			}
		}
		return $data;
	}
	
	private function createArrays($data, $elements) {
		# Set specific elements to be arrays instead of comma separated text
		foreach ($data as $key => &$value) {
			if (in_array ($key, $elements)) {
				# $value is currently a string
				$value = $value === "" ? array() : explode(",", $value);
			}
		}
		return $data;
	}

	public function getConnectConfiguration($accountId) {
		$this->setURL('/accounts/' . $accountId . '/connect');
		$data = $this->curl->makeRequest($this->url, 'GET', $this->client->getHeaders(), array(), null);	
		foreach ($data->configurations as &$configuration) {
			$configuration = $this->createArrays($configuration, $this->csvElements);
		}
		return $data; 
	}

	# Get connection by ID
	#
	# Returns same structure as getConnectionConfiguration--an array even though only one element!
	# stdClass Object
	#	(
	#		[configurations] => Array
	#			(
	#				[0] => stdClass Object
	#					(
	#						[connectId] => 26858
	#						[configurationType] => false
	#						[urlToPublishTo] => http:foo.com
	#						[name] => Test Connect
	#						[allowEnvelopePublish] => false
	#						[enableLog] => false
	#						[includeDocuments] => false
	#						[includeCertificateOfCompletion] => false
	#						[requiresAcknowledgement] => false
	#						[signMessageWithX509Certificate] => false
	#						[useSoapInterface] => false
	#						[includeTimeZoneInformation] => false
	#						[includeEnvelopeVoidReason] => false
	#						[includeSenderAccountasCustomField] => false
	#						[envelopeEvents] =>
	#						[recipientEvents] =>
	#						[userIds] =>
	#						[soapNamespace] =>
	#						[allUsers] => false
	#						[includeCertSoapHeader] => false
	#						[includeDocumentFields] => false
	#					)
    #
	#			)
    #
	#		[totalRecords] => 1
	#	)
	public function getConnectConfigurationByID($accountId, $connectID) {
		$this->setURL('/accounts/' . $accountId . '/connect/' . $connectID);
		$result = array();
		$data = $this->curl->makeRequest($this->url, 'GET', $this->client->getHeaders(), array(), null);	
		foreach ($data->configurations as &$configuration) {
			$configuration = $this->createArrays($configuration, $this->csvElements);
		}
		return $data; 
	}

	public function createConnectConfiguration(	
		$accountId, # string	Account Id
		$params = array()){
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
		#
		# RETURNS
		# stdClass Object
		#	(
		#		[connectId] => 
		#		[configurationType] => false
		#		[urlToPublishTo] => http:foo.com
		#		[name] => Test Connect
		#		[allowEnvelopePublish] => false
		#		[enableLog] => false
		#		[includeDocuments] => false
		#		[includeCertificateOfCompletion] => false
		#		[requiresAcknowledgement] => false
		#		[signMessageWithX509Certificate] => false
		#		[useSoapInterface] => false
		#		[includeTimeZoneInformation] => false
		#		[includeEnvelopeVoidReason] => false
		#		[includeSenderAccountasCustomField] => false
		#		[envelopeEvents] =>
		#		[recipientEvents] =>
		#		[userIds] =>
		#		[soapNamespace] =>
		#		[allUsers] => false
		#		[includeCertSoapHeader] => false
		#		[includeDocumentFields] => false
		#	)
		$this->setURL('/accounts/' . $accountId . '/connect');
		$data = $params;
		$data = $this->setBooleans($data, $this->booleanElements);
		$data = $this->setCSVs($data, $this->csvElements);
		$result = $this->curl->makeRequest($this->url, 'POST', $this->client->getHeaders(), array(), json_encode($data));
		$result = $this->createArrays($result, $this->csvElements);
		return $result;
	}
	
	public function updateConnectConfiguration(	
		$accountId, # string	Account Id
		$connectId, # string	Connection Id
		$params = array()){
		# params is an associative array holding the parameters. All are optional.
		# Valid keys:
		# urlToPublishTo, # string	Client's incoming webhook url
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
		$this->setURL('/accounts/' . $accountId . '/connect');
		$data = $params;
		$data['connectId'] = $connectId;
		$data = $this->setBooleans($data, $this->booleanElements);
		$data = $this->setCSVs($data, $this->csvElements);
		$result = $this->curl->makeRequest($this->url, 'PUT', $this->client->getHeaders(), array(), json_encode($data));
		$result = $this->createArrays($result, $this->csvElements);
		return $result;
	}

	public function deleteConnectConfiguration(	
		$accountId, # string	Account Id
		$connectId	# string	Connection Id
		){
		$this->setURL('/accounts/' . $accountId . '/connect/' . $connectId);
		return $this->curl->makeRequest($this->url, 'DELETE', $this->client->getHeaders());
	}
}
