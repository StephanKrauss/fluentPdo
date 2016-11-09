<?php
/**
 * Dient zum Verwaltung von File-Cache
 * 
 * Example:
 * $objCache = new TCache( DIR_FS_TEMP.get_class($this).__FUNCTION__,
 *												 array_merge($arrSearchString, $arrParameters) );
 * if(!$objCache->isCached()){
 * 	$objCache->putContent(array('resultSets' => $this->getResultSets(),
 *															'fieldPrefix2classNames' => $this->getFieldPrefix2classNames()));
 * }
 * $cachContent = $objCache->getContent();
 * $this->setResultSets($cachContent['resultSets']);
 * $this->setFieldPrefix2classNames($cachContent['fieldPrefix2classNames']);
 */
class cache{
	/**
	 * expire time in seconds
	 *
	 * @var int
	 */
	private $expireTime = 86400;
	/**
	 * full file name of cache
	 *
	 * @var string
	 */
	private $filename;
	
	/**
	 * Constructor
	 *
	 * @param string $filename
	 * @param array $params
	 * @param int $expireTime
	 */
	public function __construct( $filename, $params = array(), $expireTime = 0 )
    {
		if($expireTime > 0){
			$this->expireTime = $expireTime;
		}

		//$this->filename = $filename;
		$this->filename = '';
		foreach($params as $key => $val){
			$this->filename.= $key.serialize($val);
		}

		$this->filename = $filename.'_'.sha1($this->filename);
	}
	
	/**
	 * checks creation time of cache file and exists file
	 *
	 */
	public function isCached(){
		$result = false;
		
		$ftime = 0;
		if(file_exists($this->filename) && is_readable($this->filename)){
			$ftime = @filectime($this->filename);

			$result = true;
		}

		if((time()-$ftime) >= $this->expireTime){
			$this->fDelete();

			$result = false;
		}

		// return $result;
        return false;
	}
	
	/**
	 * put cached content
	 *
	 * @param mixed $content
	 */
	public function putContent($content){
		$this->fPutContent($content);
	}

	/**
	 * get cached content
	 *
	 * @return unknown
	 */
	public function getContent(){
		return $this->fGetContent();
	}
	
	/**
	 * delete cache file
	 *
	 */
	public function fDelete(){
		$result = false;
		
		if(file_exists($this->filename)){
			$result = @unlink($this->filename);
		}
		
		return $result;
	}
	
	/**
	 * puts content into cache file
	 *
	 * @param mixed $content
	 */
	private function fPutContent($content){
		if(file_exists($this->filename) && !is_writable($this->filename)){
			throw new WWSException( 'Cache konnte nicht gespeichert werden: ('.$this->filename.')', WWS_EXCEPTION_ERROR_LEVEL);
		}

		file_put_contents ($this->filename, serialize($content));
	}
	
	/**
	 * gets content from cache file
	 *
	 * @return mixed
	 */
	private function fGetContent(){
		if(file_exists($this->filename) && !is_readable($this->filename)){
			throw new WWSException( 'Cache konnte nicht ausgelesen werden: ('.$this->filename.')', WWS_EXCEPTION_ERROR_LEVEL);
		}

		return @unserialize(@file_get_contents($this->filename));
	}

	/**
	 * Löscht alle Dateien, die mit dem Pattern übereinstimmen
	 *
	 * @param string $dir
	 * @param string $patern
	 */
	static public function clean ( $dir, $pattern ) {
		$result = true;
		
		$files = scandir ( $dir );
		
		foreach( $files as $pos => $file ) {
		
			if( !is_dir( $file ) && is_file( $dir.$file ) && true == preg_match('/'.$pattern.'/i', $file) ) {
				$result = unlink( $dir.$file );
			}
		}
		
		return $result;
	}
}
