<?php
namespace Craft;

class DsSolr_CpController extends BaseController
{

	// =======================================================================// 
	// ! CP Tab Route Actions                                                 //
	// =======================================================================//

	/**
	 * Render index mapping page 
	 *
     * @return string
     */
	public function actionIndexMapping()
	{
		$stepSize = craft()->config->get('dssolrIndexStepSize');
		
		return $this->renderTemplate(
			'dssolr/cp/index',
			array(
				'mappingSettings' => craft()->dsSolr_settings->getMappingSettings(true),
				'stepSize' 		  => isset($stepSize) ? $stepSize : 5,
			)
		);
	}

	/**
	 * Render settings page
	 *
     * @return string
     */
	public function actionSettings()
	{
		return $this->renderTemplate(
			'dssolr/cp/settings', 
			array(
				'mappingSettings' => craft()->dsSolr_settings->getMappingSettings(),
				'mappings'		  => craft()->dsSolr_settings->getMappingTemplates()
			)
		);
	}

	/**
	 * Render status page 
	 *
     * @return string
     */
	public function actionStatus()
	{
		$ping 	  = craft()->dsSolr->getPing();
		$stepSize = craft()->config->get('dssolrIndexStepSize');

		if (!$ping)
		{
			$ping['status'] = Craft::t('NOT OK!');
		}

		return $this->renderTemplate('dssolr/cp/status', array(
			'ping' 			  => $ping,
			'stepSize' 		  => isset($stepSize) ? $stepSize : 5,
			'mappingSettings' => craft()->dsSolr_settings->getMappingSettings(),
			'mappings' 		  => craft()->dsSolr_settings->getMappingTemplates(),
			'locales'		  => craft()->i18n->getSiteLocales(),
		));
	}

	/**
	 * Render mapping preview
	 * 
	 * @return string
	 */
	public function actionRenderMappingPreview()
	{
		$id      = craft()->request->getPost('id');
		$limit   = craft()->request->getPost('limit');
		$offset  = craft()->request->getPost('offset');
		$locale  = craft()->request->getPost('locale');
		$mapping = craft()->request->getPost('mapping');

		return $this->renderTemplate('dssolr/mappings/' . $mapping, array(
			'id' 	 => !empty($id) 	? $id 	  : null,
			'limit'  => !empty($limit)  ? $limit  : null,
			'offset' => !empty($offset) ? $offset : null,
			'locale' => !empty($locale) ? $locale : null,
		));
	}

	// =======================================================================// 
	// ! CP Form Submissons                                                   //
	// =======================================================================//

	/**
	 * Form Save Mappings (Settings page)
	 *
     * @return void
     */
	public function actionSaveMappings()
	{
		$mappingPaths = craft()->request->getPost('mappingPaths');

		foreach($mappingPaths as $key=>$data)
		{
			$mappingPath 			  = new DsSolr_MappingPathsRecord();
			$mappingPath->sectionId   = $data['sectionId'];
			$mappingPath->mappingPath = $data['mapping'];
			$mappingPath->locale 	  = $data['locale'];

			if($mappingPath->validate())
			{
				craft()->dsSolr_settings->saveMappingBySectionId($mappingPath);
				craft()->userSession->setNotice(Craft::t('Mapping paths saved'));
			}
			else
			{
				craft()->userSession->setError(Craft::t('Couldn\'t save mapping paths'));
			}
		}
	}

	// =======================================================================// 
	// ! Ajax calls                                                           //
	// =======================================================================//

	/**
	 * Run the indexing action (Index page)
	 *
     * @return void
     */
	public function actionRunIndex()
	{	
		$data = array();

		$data['limit'] 		 = craft()->request->getPost('limit');
		$data['offset']    	 = craft()->request->getPost('offset');
		$data['sectionId'] 	 = craft()->request->getPost('sectionId');
		$data['mappingPath'] = craft()->request->getPost('mappingPath');
		$data['locale'] 	 = craft()->request->getPost('locale');
		$data['fullUpdate']  = craft()->request->getPost('fullUpdate');

		// Make sure we have the section data before we can continue to index
		if (isset($data['limit']) && 
			isset($data['offset']) && 
			isset($data['sectionId']) && 
			isset($data['mappingPath']) && 
			isset($data['locale']))
		{
			if (craft()->dsSolr_solariumIndex->runIndexSolr(
				$data['mappingPath'],
				$data['sectionId'], 
				$data['locale'], 
				null, 
				$data['limit'], 
				$data['offset'],
				(isset($data['fullUpdate']) && $data['offset'] == 0) ? true : false))
			{
				$data['status'] = 1;
				return $this->returnJson($data);
			}
			else
			{
				$data['status'] = 0;
				return $this->returnJson($data);
			}
		}
		$data['status'] = 0;
		return $this->returnJson($data);
	}

	/**
	 * Create json for total amount of entries in a section.
	 * 
	 * @param $variables (should countain: section and locale)
	 * @return string (rendered json)
	 */
	public function actionTotalEntries(array $variables = array())
	{
		$sectionId = $variables['section'];
		$locale    = $variables['locale'];

		$criteria 		   = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->sectionId = $sectionId;
		$criteria->limit   = null;
		$criteria->locale  = $locale;
		$total 			   = $criteria->total();
		$mappingPath       = craft()->dsSolr_settings->getMappingBySectionIdAndLocale($sectionId, $locale);

		return $this->renderTemplate(
			'dssolr/cp/totalentries.json', 
			array(
				'total' 	  => $total,
				'sectionId'   => $sectionId,
				'mappingPath' => $mappingPath->mappingPath,
				'locale'	  => $locale,
			)
		);
	}
}