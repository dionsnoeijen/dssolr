<?php
namespace Craft;

class DsSolr_MappingPathsRecord extends BaseRecord
{
    /**
     * @return string
     */
    public function getTableName()
    {
        return 'ds_solr_mapping_paths';
    }

    /**
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'sectionId'     => AttributeType::Number,
            'mappingPath'   => AttributeType::String,
            'locale' => AttributeType::String,
        );
    }

    /**
     * @return array
     */
    public function defineRelations()
    {
        return array(
            'section' => array(static::BELONGS_TO, 'SectionRecord', 'required' => true),
        );
    }
}