<?php
namespace Craft;

class DsSolr_SettingsService extends BaseApplicationComponent
{
    /**
     * Saves, updates or deletes mapping paths.
     * 
     * @return void
     */
    public function saveMappingBySectionId(DsSolr_MappingPathsRecord $mappingPath)
    {
    	$mappingPathsRecord = new DsSolr_MappingPathsRecord();
    	$record = $mappingPathsRecord->findByAttributes(array(
            'sectionId' => $mappingPath->sectionId,
            'locale' => $mappingPath->locale
        ));

        // No mapping path found for this section, create a new one.
    	if (!isset($record))
    	{
            // But only when the mapping path is not empty.
            if (!empty($mappingPath->mappingPath))
            {
    		    $mappingPath->save();
            }
    	}
        // Mapping path found for this section.
    	else
    	{
            // Delete it if value is empty.
            if (empty($mappingPath->mappingPath))
            {
                $record->delete();
            }
            // Else update the record with a new mapping path.
            else
            {
    		    $record->mappingPath = $mappingPath->mappingPath;
    		    $record->save();
            }
    	}
    }

    /**
     * Check the templates/mappings directory for available mapping jsons
     * 
     * @return array
     */
    public function getMappingTemplates()
    {
        $path = CRAFT_PLUGINS_PATH . 'dssolr/templates/mappings/';
        $files = scandir($path);

        foreach ($files as $key=>$file)
        {
            $fileParts = pathinfo($file);
            if($fileParts['extension'] !== 'json')
            {
                unset($files[$key]);
            }
        }

        return $files;
    }

    /**
     * Fetch the mapping settings per section with it's locales
     *
     * @param withPath (Just return settigns of sections that have a path assigned)
     * @return array
     */
    public function getMappingSettings($withPath = false)
    {
    	$sections = craft()->sections->getAllSections();

    	$mappingSettings = array();

    	foreach ($sections as $key=>$section)
    	{
            $sectionLocales = craft()->sections->getSectionLocales($section->id);

            foreach ($sectionLocales as $locale)
            {
        		$setting                = new \stdClass();
        		$setting->sectionId     = $section->id;
        		$setting->sectionName   = $section->name;
                $setting->sectionHandle = $section->handle;
                $setting->sectionLocale = $locale->locale;

        		$mappingPath = $this->getMappingBySectionIdAndLocale($section->id, $locale->locale);
        		$setting->mappingPath = isset($mappingPath) ? $mappingPath->mappingPath : '';
        		
                if ($withPath === false)
                {
                    $mappingSettings[] = $setting;
                }
                elseif (isset($mappingPath))
                {
                    $mappingSettings[] = $setting;
                }
            }
    	}

    	return $mappingSettings;
    }

    /**
     * Get a mapping path record
     */
    public function getMappingBySectionIdAndLocale($sectionId, $locale)
    {	
    	$mappingPathsRecord = new DsSolr_MappingPathsRecord();
    	return $mappingPathsRecord->findByAttributes(array('sectionId' => $sectionId, 'locale' => $locale));
    }

    /**
     *  Get all mappings
     */
    public function getMappings()
    {
    	$mappingPathsRecord = new DsSolr_MappingPathsRecord();
    	return $mappingPathsRecord->findAll();
    }
}