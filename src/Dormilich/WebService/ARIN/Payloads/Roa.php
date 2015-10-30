<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;

/**
 * The ROA Payload allows for Route Origin Authorization (ROA) request 
 * submissions.
 * 
 * Please complete the roaData field using the following format:
 * 
 * versionNumber|signingTime|name|originAS|validityStartDate|validityEndDate|startAddress|cidrLength|maxLength|
 * 
 * Note that startAddress|cidrLength|maxLength| are repeated for each resource. 
 * The versionNumber field must be set to 1, as it is currently the only 
 * supported version. The signingTime is a timestamp specifying when the ROA 
 * data was signed, specified in seconds since the unix epoch (January 1, 1970). 
 * The name field may contain any name of your choosing, and is for your own 
 * identification purposes. The originAs field is the AS that will be 
 * authorized to announce the resources present in the roaData. The 
 * validityStartDate and validityEndDate specifies the date range during which 
 * your ROA must be considered valid, and must be within the range of your 
 * resource certificate. These dates must be specified in the mm-dd-yyyy format.
 * 
 * The signature field of the RoaPayload is the signed base64 encoding of the 
 * roaData field. More information about ROA signing may be found on ARIN’s 
 * RPKI FAQ. 
 */
class Roa extends Payload
{
	public function __construct()
	{
		$this->name = 'roa';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('roaData'));
		$this->create(new Element('signature'));
	}
}
