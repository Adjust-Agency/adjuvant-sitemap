<?php namespace Adjuvant\Sitemap;

use SitemapPHP\Sitemap as Sitemap;

class Generator extends Sitemap {
	
	public $record 		= false;
	public $frequency 	= 'monthly';
	public $priority 	= '1.0';
	
	protected $language 		= 'neutral';
	protected $trackPath 		= null;
	protected $trackFile 		= '.sitemap.json';
	protected $host				= null;

	public function __construct($host = null)
	{
		$this->host = !is_null($host) ? $host : 'http://' . $_SERVER['SERVER_NAME'];		
		if( is_null($this->trackPath)) $this->trackPath = getcwd();
		
		parent::__construct($this->host);
	}
	
	protected function getTrackFileName(){
		return preg_replace("`\/{2,}`", '/', $this->trackPath . '/' . $this->trackFile);
	}
	
	protected function getTracker(){
	
		$trackFile = $this->getTrackFileName();
		if(!is_file($trackFile)) {
			file_put_contents($trackFile, json_encode(array()));	
		}
		
		return json_decode( file_get_contents( $trackFile )); 
	}
	
	public function setTrackerPath($path)
	{
		$this->trackPath = $path;
	}
	
	public function saveTracker($tree)
	{
		file_put_contents($this->getTrackFileName(), json_encode($tree, JSON_PRETTY_PRINT));
	}
	
	public function track($language = 'neutral', $url = null)
	{
		
		if($this->record){
			
			if( is_null($url) ) $url = $_SERVER['REQUEST_URI'];
			$url = preg_replace("`\/{2,}`", '/',  '/' . $url);
			
			$tree 		= $this->getTracker();
			$oldHash	= md5( json_encode($tree) );
						
			if(empty($tree->$language)) $tree->$language = array();
			if( !in_array($url, $tree->$language) ) {
				 array_push($tree->$language, $url);
			}
			
			$newHash = md5( json_encode($tree) );
			
			if( $oldHash !== $newHash ) {
				$this->saveTracker( $tree );
			}
		}
	}
	
	public function output($language = 'neutral')
	{
		
		$tree = $this->getTracker();
		$hash = md5( json_encode($tree) . $this->host );
		$filename = '.sitemap-' . $language. '-' . $hash . ".xml";
		$trackpath  = preg_replace("`\/{2,}`", '/', $this->trackPath . '/');		
		$xml = $trackpath . $filename;
				
		if( ! is_file( $xml )) {
			
			foreach (glob(".sitemap-" . $language. "-*") as $oldxmlfile) {
				unlink($trackpath . $oldxmlfile);
			}
			
			$this->setPath( $trackpath);
			$this->setFilename( basename($filename, '.xml') );
			
			foreach( $tree->$language as $leaf ){
				$this->addItem($leaf, $this->priority, $this->frequency);
			}
			
			$lang = strlen($language) == 2 ? $language : '';			
			$this->createSitemapIndex( $this->host . preg_replace("`\/{2,}`", '/', '/' . $lang . '/sitemap.xml') , 'Today');
			
		}
		
		header("Content-Type: application/xml; charset=utf-8");
		echo file_get_contents($xml);
		exit();
	}
	
}