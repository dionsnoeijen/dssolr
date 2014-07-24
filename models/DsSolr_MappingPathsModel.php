<?php
namespace Craft;

class DsSolr_MappingPathsModel extends BaseModel
{
    public function getTableName()
    {
        return 'ds_solr_mapping_paths';
    }

    protected function defineAttributes()
    {
        return array(
            'sectionId'     => AttributeType::Number,
            'mappingPath'   => AttributeType::String,
            'locale' => AttributeType::String,
        );
    }
}