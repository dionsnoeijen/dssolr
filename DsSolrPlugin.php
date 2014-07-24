<?php
namespace Craft;

class DsSolrPlugin extends BasePlugin
{

	// ======================================================================= //
	// ! Plugin settings                                                       //
	// ======================================================================= //

    public function getName() 		  { return Craft::t('DS Solr'); }
    public function getVersion() 	  { return '0.1'; }
    public function getDeveloper() 	  { return 'Dion Snoeijen (Diovisuals)'; }
    public function getDeveloperUrl() { return 'http://www.diovisuals.com'; }
    public function hasCpSection() 	  { return true; }

    // ======================================================================= //
	// ! Register events                                                       //
	// ======================================================================= //

    public function init()
    {
        if (craft()->userSession->isLoggedIn())
        {
    	   craft()->runController('DsSolr/saveEntry/registerEvents');
        }
    }

	// ======================================================================= // 
	// ! Routing -> Route paths to controller actions                          //        
	// ======================================================================= //

	public function registerCpRoutes()
    {
        return array(
            
            // -------------------------
            // Cp pages
            // -------------------------

            'dssolr' => array(
                'action' => 'DsSolr/cp/indexMapping',
            ),
            'dssolr/settings' => array(
            	'action' => 'DsSolr/cp/settings',
            ),
            'dssolr/status' => array(
            	'action' => 'DsSolr/cp/status',
            ),

            // -------------------------
            // Ajax calls
            // -------------------------
            
            'dssolr/total/(?P<section>\d+)/(?P<locale>\w+)' => array(
                'action' => 'DsSolr/cp/totalEntries',
            ),
        );
    }
}
