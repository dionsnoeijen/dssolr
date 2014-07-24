<?php

namespace Craft;

class DsSolrVariable
{
	private $custom;

	public function __construct()
	{
		require_once CRAFT_PLUGINS_PATH . 'dssolr/variables/DsSolr_CustomVariable.php';

		$this->custom = new DsSolr_CustomVariable();
	}

	/**
	 * Get the query constant from the client
	 *
	 * @return string
	 */
	public function getConst($const)
	{
		return constant("Solarium\Core\Client\Client::{$const}");
	}

	/**
	 * Get the solr client
	 *
	 * @return object SOLR Client
	 */
	public function getClient()
	{
		return craft()->dsSolr->getSOLRClient();
	}

	/**
	 * Pass functions to custom variable
	 *
	 * @return object SOLR Client
	 */
	public function __call($method, $arguments)
	{
		return call_user_func_array(array($this->custom, $method), $arguments);
	}
}