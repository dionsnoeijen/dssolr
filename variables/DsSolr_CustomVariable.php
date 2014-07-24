<?php

namespace Craft;

class DsSolr_CustomVariable
{

	private $solrClient;

	public function __construct()
	{
		$this->solrClient = craft()->dsSolr->getSOLRClient();
	}

	// =======================================================================// 
	// ! Custom variables below this line                                     //
	// =======================================================================//

	public function example()
	{
		return 'datacheck';
	}
}