<?php
namespace Craft;

class DsSolr_SaveEntryController extends BaseController
{

	// =======================================================================// 
	// ! Save Entry Register Events                                           //        
	// =======================================================================//

	public function actionRegisterEvents()
	{
		craft()->on('entries.saveEntry', array($this, 'onSaveEntry'));
		craft()->on('entries.onDeleteEntry', array($this, 'onDeleteEntry'));
	}

	// =======================================================================// 
	// ! Save Entry Event Handlers                                            //
	// =======================================================================//

	protected function onSaveEntry(Event $event)
	{
		$entryModel = $event->params['entry'];
		$mappingPath = craft()->dsSolr_settings->getMappingBySectionIdAndLocale($entryModel->sectionId, $entryModel->locale);

		// Only execute if this is a mapped section
		if (isset($mappingPath))
		{
			// Check entry in available locales
			$sectionLocales = craft()->sections->getSectionLocales($entryModel->sectionId);
			foreach ($sectionLocales as $locale)
			{
				// Get the specific entry by id and locale
				$criteria 		     = craft()->elements->getCriteria(ElementType::Entry);
				$criteria->sectionId = $entryModel->sectionId;
				$criteria->locale  	 = $locale->locale;
				$criteria->id      	 = $entryModel->id;

				// Only store when live, in all other cases remove from SOLR
				$store = false;
				foreach ($criteria as $entry)
				{
					if (isset($entry->status) && $entry->status === 'live')
					{
						$store = true;
					}
				}

				if ($store)
				{
					craft()->dsSolr_solariumIndex->runIndexSolr($mappingPath->mappingPath, $entryModel->sectionId, $locale->locale, $entryModel->id);
				}
				else
				{
					craft()->dsSolr_solariumIndex->clearDocumentByEntryIdAndLocale($entryModel->id, $locale->locale);
				}
			}
		}
	}

	protected function onDeleteEntry(Event $event)
	{
		$entryModel = $event->params['entry'];
		$mappingPath = craft()->dsSolr_settings->getMappingBySectionIdAndLocale($entryModel->sectionId, $entryModel->locale);

		// Only execute if this is a mapped section
		if(isset($mappingPath))
		{
			craft()->dsSolr_solariumIndex->clearDocumentByEntryIdAndLocale($entryModel->id);
		}
	}
}