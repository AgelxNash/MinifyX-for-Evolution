<?php
if(!defined('MODX_BASE_PATH')) {die('What are you doing? Get out of here!');}

class MinifyX{
	public $outFolder = 'assets/templates/';
	public $cssFile = 'style.css';
	public $jsFile = 'script.js';
	public $parse = true;

	private $dir = null;
	private $modx = null;

	public function __construct(DocumentParser $modx){
		$this->modx = $modx;
		$this->dir = dirname(__FILE__) . "/";
	}

	public function checkHashFile(&$files, $type){
		$hash = array();
		$files = $this->collectFile($files, $type);
		foreach($files as $f){
			$hash[] = sha1_file(MODX_BASE_PATH . $f);
		}
		$files = implode(",", $files);
		return $hash;
	}

  	public function minCSS($css = '', $doc = 0, $compress = true){
		$flag = false;
		if(file_exists($this->dir . "cssmin.class.php")){
			include_once($this->dir . "cssmin.class.php");
		}
		if( ($css = $this->collectFile($css, 'css')) !== false || (int)$doc>0){
			if( ($data = $this->collectContents($css, $doc, 'css', $compress)) != ''){
				if($this->writeFile($this->cssFile, $data)){
					$flag = $this->outFolder . $this->cssFile;
				}
			}
		}
		return $flag;
	}

	public function minJS($js='',$doc=0, $compress = true){
		$flag = false;
		if(file_exists($this->dir . "jsmin.class.php")){
			include_once($this->dir . "jsmin.class.php");
		}

		if( ($js = $this->collectFile($js, 'js')) !== false || (int)$doc>0){
			if( ($data = $this->collectContents($js, $doc, 'js', $compress)) != ''){
				if($this->writeFile($this->jsFile, $data)){
					$flag = $this->outFolder . $this->jsFile;
				}
			}
		}
		return $flag;
	}

  	private function getPath($file){
  		if(empty($file)) return '';

		$outFile = dirname($file) . "/";
    	$outFile = str_replace($this->modx->getConfig('base_path'), '/', $outFile) . "/";
		$outFile = str_replace('//', '/', $outFile);
    	return $outFile;
  	}

	private function collectContents($data, $id, $type, $compress = true){
		$out='';

		foreach($data as $f){
			$f = trim($f);
			$fp = $this->modx->getConfig('base_path').$f;
			if (is_readable($fp)){
				$out .= "/* " . $f . " */\r\n";
        		$out .= $this->process(file_get_contents($fp), $type, $compress, $fp);
			}
		}
    	if( (int)$id > 0 ){
    		$out .= "/* DB */\r\n";
    		$out .= $this->process($this->renderDoc($id), $type, $compress);
    	}
		return $out;
	}

	private function process($content, $type, $compress, $file = null){
		switch($type){
			case 'css':{
				if( ! empty($file)){
					$content = preg_replace('#url\((?!\s*[\'"]?(data\:image|/|http([s]*)\:\/\/))\s*([\'"])?#i', "url($3{$this->getPath($file)}", $content);
				}
				$content = trim( $compress ? Minify_CSS_Compressor::process($content) : $content );
				break;
			}
			case 'js':{
				$content = trim( ($compress ? JSMin::minify($content) : $content), ';').';';
				break;
			}
		}
		return $content;
	}

	private function renderDoc($id){
		$this->modx->loadExtension('tpl', false);
		$content = $this->modx->db->getValue("SELECT content FROM ".$this->modx->getFullTableName("site_content")." WHERE id=".(int)$id);
		if($this->modx->tpl instanceof DLTemplate && $this->parse){
			$content = $this->modx->tpl->parseDocumentSource($content, $this->modx);
		}else{
			$minParserPasses = empty ($this->modx->minParserPasses) ? 2 : $this->modx->minParserPasses;
      		$maxParserPasses = empty ($this->modx->maxParserPasses) ? 10 : $this->modx->maxParserPasses;
      		$passes = $minParserPasses;
      		if($this->parse && !empty($content)){
        		for ($i= 0; $i < $passes; $i++) {
          			if ($i == ($passes -1)) $st = strlen($content);

          			$content=$this->modx->mergeDocumentContent($content);
        			$content=$this->modx->mergeSettingsContent($content);
      				$content=$this->modx->mergeChunkContent($content);
					$content=$this->modx->evalSnippets($content);
					$content=$this->modx->mergePlaceholderContent($content);

          			if ($i == ($passes -1) && $i < ($maxParserPasses - 1)) {
         	 			$et = strlen($content);
          				if ($st != $et) $passes++;
       	 			}
        		}
      		}
		}

      	return trim($content);
	}

  	private function sanitar($file,$type){
  		$file = preg_replace(array('/\.*[\/|\\\]/i', '/[\/|\\\]+/i'), array('/', '/'), $file);
    	$tmp = explode(".", $file);
    	return (strtolower(end($tmp)) == strtolower($type)) ? $file : '';
  	}

	private function collectFile($str, $type){
		$out=array();
    	$str=explode(",", $str);
    	foreach($str as $f){
      		if( ($file = $this->sanitar(trim($f), $type)) != ''){
      			$path = MODX_BASE_PATH.$file;
				if(is_file($path) && is_readable($path)){
      				$out[] = $file;
      			}
      		}
    	}
		return (count($out) > 0) ? $out : false;
	}

	private function writeFile($file,$data){
		$flag = false;
		$handle = fopen($this->modx->getConfig('base_path').$this->outFolder.'/'.$file, "w+");
		if ($handle && flock($handle, LOCK_EX)) {
			fputs($handle, $data);
			flock($handle, LOCK_UN);
			$flag = true;
			fclose($handle);
		}
		return $flag;
	}
}
