<?php

namespace Craft;

class DsSolrService extends BaseApplicationComponent
{

	private $client;

	/**
	 * Constructor, get SOLR Client
	 */
	public function __construct()
	{
		$this->client = $this->getSOLRClient();
	}

	/**
	 * Connect to solr server and create a client
	 *
	 * @return object SOLR Client
	 */
	public function getSOLRClient()
	{
		require_once CRAFT_PLUGINS_PATH . 'dssolr/vendor/autoload.php';
		\Solarium\Autoloader::register();

		$host = craft()->config->get('dssolrHost');
		$port = craft()->config->get('dssolrPort');
		$path = craft()->config->get('dssolrPath');

		if(isset($host) && isset($port) && isset($path))
		{
			$config = array(
	            'endpoint' => array(
	                'localhost' => array(
	                    'host' => $host,
	                    'port' => $port,
	                    'path' => $path
	                )
	            )
	        );

	        return new \Solarium\Client($config);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Incorrect SOLR config data, check your general config file.'));
		}

		return false;
	}

	/**
	 * Is the SOLR server available?
	 *
	 * @return string
	 */
	public function getPing()
	{
		$obj = new \stdClass();
		if (isset($this->client) && $this->client)
		{
			$ping = $this->client->createPing();
	        try 
	        {
	            $result = $this->client->ping($ping);
	            return $result->getData();
	        } 
	        catch (Solarium\Exception $e) 
	        {	
	        	$obj->status = Craft::t('Connection failed') . ' ' . $e;
	            return $obj;
	        }
	    }
        else
        {
        	$obj->status = Craft::t('No SOLR Client available');
        	return $obj;
        }
	}
}