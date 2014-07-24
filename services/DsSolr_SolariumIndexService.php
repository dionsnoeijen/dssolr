<?php

namespace Craft;

class DsSolr_SolariumIndexService extends BaseApplicationComponent 
{
	private $client;
	private $update;

	/**
	 * Constructor, get SOLR Client
	 * 
	 * @return void
	 */
	public function __construct()
	{
		// Get the SOLR client
		$this->client = craft()->dsSolr->getSOLRClient();

		// Create a new update
		$this->update = $this->client->createUpdate();
	}

	// =======================================================================// 
	// ! Public functions                                                     //
	// =======================================================================//

	/**
	 * Delete all documents in a section by it's sectionId and locale
	 * Won't execute immediately by default
	 * 
	 * @param int sectionId
	 * @param str locale
	 * @param bool execute, default false
	 * @return void
	 */
	public function clearDocumentsBySectionAndLocale($sectionId, $locale, $execute = false)
	{
		$sectionIdField = craft()->config->get('dssolrSectionIdField');
		$localeField 	= craft()->config->get('dssolrLocaleField');

		$sectionIdField = (isset($sectionIdField) ? $sectionIdField : 'section_id_i') . ':';
		$localeField 	= (isset($localeField) ? $localeField : 'locale_s') . ':';

		$this->update->addDeleteQuery($sectionIdField . $sectionId . ' AND ' . $localeField . $locale);

		if($execute) {
			$this->update->addCommit();
			$this->execute();
		}
	}

	/**
	 * Delete specific document by entry id and locale to be combined in the document id 
	 * It will execute immediately by default
	 * 
	 * @param int entryId
	 * @param str locale, default value is null, if null just delete everything by entryId and ignore it's locale.
	 * @param bool execute, default true
	 * @return void
	 */
	public function clearDocumentByEntryIdAndLocale($entryId, $locale = null, $execute = true)
	{

		if(!isset($locale))
		{
			$query = 'id:' . $entryId . '-*';
		}
		else
		{
			$query = 'id:' . $entryId . '-' . $locale;
		}

		$this->update->addDeleteQuery($query);

		if($execute)
		{
			$this->update->addCommit();
			$this->execute();
		}
	}

	/**
	 * Final word. Separate allows for more flexibility.
	 *
	 * @return void
	 */
	public function execute()
	{
		// Send to SOLR!
        $this->client->update($this->update);
	}

	/**
	 * The actual indexing operation.
	 * It will execute immediately.
	 * 
	 * @todo Make a check if everything went allright, if not, return false or throw error.
	 * @return bool
	 */
	public function runIndexSolr($path, $sectionId, $locale, $id = null, $limit = 10, $offset = null, $fullUpdate = false)
	{
		// Get the mapping template data
		$fileContents =	$this->getMappingTemplateContents($path, $id, $locale, $limit, $offset);

		// If it's a full update, delete everything belonging to the current mapping
		if ($fullUpdate)
		{
			$this->clearDocumentsBySectionAndLocale($sectionId, $locale);
		}

		// Create the documents
		foreach ($fileContents as $data)
		{
			$this->createDocument($data);
		}

		// Add the commit to the update object
	    $this->update->addCommit();

	    // Go go go!
	    $this->execute();

        return true;
	}

	// =======================================================================// 
	// ! Private functions                                                    //
	// =======================================================================//

	/**
	 * Loop through the mapping data and build the document
	 *
	 * @return void
	 * @todo integrate recursion for multiple depth levels
	 */
	private function createDocument($data)
	{
		$doc = $this->update->createDocument();

		foreach($data as $key=>$row)
		{	
			$doc->$key = $row;
		}

		$this->update->addDocument($doc);
	}

	/**
	 * Fetch the mapping template and decode it
	 *
	 * @return array
	 */
	private function getMappingTemplateContents($path, $id, $locale, $limit, $offset)
	{
		$data = craft()->templates->render('dssolr/mappings/' . $path, array(
			'id' => $id,
			'locale' => $locale,
			'limit' => $limit,
			'offset' => $offset,
		));
		return json_decode($data);
	}

}
