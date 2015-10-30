<?php namespace Adjuvant\Sitemap;

use SitemapPHP\Sitemap as Sitemap;

class Generator extends Sitemap {

	public function __construct($host = null){
		if( is_null($host) ) $host = 'http://' . $_SERVER['SERVER_NAME'];
		parent::__construct($host);
	}
	
	
}