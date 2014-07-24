# DS Solr for Craft CMS (0.1 Beta)

![Solr Logo](http://lucene.apache.org/images/solr.png)

## Introduction

This plugin provides a flexible and powerful manner to integrate SOLR with your Craft projects. It takes the hard work out of your hands by providing simple means to store and retrieve data from SOLR.

## Prerequisites

You need SOLR to be installed on your server. This plugin aims to be as flexible as possible, sacrificing oversimplification of certain features. To get most out of this plugin it would be helpful if you have at least basic knowledge about Php, Json and of course SOLR. The web is full of information about these topics, therefore it's outside the scope of this readme.

## Installation

1. Add the dssolr folder to your Plugins directory.
2. Install it on the plugins page.
3. Add configuration settings.

## Configuration

Go to /craft/config/general.php and add the following configurations:

`'dssolrHost' => 'your-host'`
Usually this is 'localhost', this is a **required** setting.

`'dssolrPort' => 8080`
Usually this is 8080, this is a **required** setting.

`'dssolrPath' => '/solr/your_collection/'`
With the default SOLR setup this is '/solr/collection1/', this is a **required** setting.

`'dssolrSectionIdField' => 'your_section_id_field'`
**Optional**, defaults to: 'section_id_i'.

`'dssolrLocaleField' => 'your_locale_field'`
**Optional**, defaults to 'locale_s'.

`'dssolrIndexStepSize' => 5`
**Optional**, defaults to 5.

## Documentation

Visit [the wiki](https://github.com/Sanity11/dssolr/wiki/)

## Special thanks to
[Lucene](http://lucene.apache.org/)
[Solr](http://lucene.apache.org/solr/)
[Solarium](http://www.solarium-project.org/)