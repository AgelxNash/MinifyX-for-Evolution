<?php
if(!defined('MODX_BASE_PATH')) {die('What are you doing? Get out of here!');}

class MinifyX{
	public $outFolder='assets/templates/';
	public $cssFile='style.css';
	public $jsFile='script.js';
	public $parse=true;
  
	private $dir,$modx;
	
	function __construct($modx){
		try{
			if($modx instanceof DocumentParser){
				$this->modx=$modx;
			}else{
				throw new Exception('MODX value is not DocumentParser class');
			}
		}catch(Exception $e){
			$this->debug($e->getMessage(),$e->getFile(),$e->getLine(),$e->getTrace(),3);
		}
		$this->dir=dirname(__FILE__)."/";
	}
  public function minCSS($css='',$doc=0){
		$flag=false;
		try{
		if(file_exists($this->dir."cssmin.class.php")){
				include_once($this->dir."cssmin.class.php"); 
			}else{
				throw new Exception("No file ".$this->dir."cssmin.class.php");
			}
		}catch(Exception $e){
			$this->debug($e->getMessage(),$e->getFile(),$e->getLine(),$e->getTrace(),3);
		}
		try{
			if(($css=$this->collectFile($css,'css'))!==false || (int)$doc>0){
				if(($data=$this->collectContents($css,$doc,'css'))!=''){
					if($this->writeFile($this->cssFile,$data)){
						$flag=true;
					}else{
						$die=3;
						throw new Exception('Error write data CSS');
					}
				}else{
					$die=1;
					throw new Exception('No data CSS');
				}
				
			}else{
				$die=1;
				throw new Exception('No file CSS to compress');
			}
			
		}catch(Exception $e){
			$this->debug($e->getMessage(),$e->getFile(),$e->getLine(),$e->getTrace(),$die);
		}
		return $flag;
	}
	
	public function minJS($js='',$doc=0){
		$flag=false;
		try{
		if(file_exists($this->dir."jsmin.class.php")){
				include_once($this->dir."jsmin.class.php"); 
			}else{
				throw new Exception("No file ".$this->dir."jsmin.class.php");
			}
		}catch(Exception $e){
			$this->debug($e->getMessage(),$e->getFile(),$e->getLine(),$e->getTrace(),3);
		}
		try{
			if(($js=$this->collectFile($js,'js'))!==false || (int)$doc>0){
				if(($data=$this->collectContents($js,$doc,'js'))!=''){
					if($this->writeFile($this->jsFile,$data)){
						$flag=true;
					}else{
						$die=3;
						throw new Exception('Error write data JS');
					}
				}else{
					$die=1;
					throw new Exception('No data JS');
				}
				
			}else{
				$die=1;
				throw new Exception('No file JS to compress');
			}
			
		}catch(Exception $e){
			$this->debug($e->getMessage(),$e->getFile(),$e->getLine(),$e->getTrace(),$die);
		}
		return $flag;
	}
  private function getPath($file){
	$file=dirname($file)."/";
    $file=str_replace($this->modx->config['base_path'],'/',$file)."/";
	$file=str_replace('//','/',$file);
    return $file;
  }

	private function collectContents($data,$id,$type){
		$out='';
		
		foreach($data as $f){
			$f = trim($f);
			$fp = $this->modx->config['base_path'].$f;
			if (is_readable($fp)){
				$out .= "\r\n\r\n/* " . $f . " */ \r\n";
				$content = file_get_contents($fp);  
        switch($type){
			case 'css':{
				//@see: http://stackoverflow.com/questions/9798378/preg-replace-regex-to-match-relative-url-paths-in-css-files
				$content=preg_replace('#url\((?!\s*[\'"]?(?:https?:)?/)\s*([\'"])?#i', "url($1{$this->getPath($fp)}", $content);
				$out .= Minify_CSS_Compressor::process($content);
				break;
			  }case 'js':{
				$out .= JSMin::minify($content);
				break;
			  }
        }
			}
		}
    if((int)$id>0){
    	$sql=$this->modx->db->query("SELECT content FROM ".$this->modx->getFullTableName("site_content")." WHERE id=".(int)$id);
      $out .= "\r\n\r\n/* DB */ \r\n";
			$content = $this->modx->db->getValue($sql);
      
      $minParserPasses= empty ($this->modx->minParserPasses) ? 2 : $this->modx->minParserPasses;
      $maxParserPasses= empty ($this->modx->maxParserPasses) ? 10 : $this->modx->maxParserPasses;
      $passes = $minParserPasses;
      
      if($this->modx->parse){
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
      $out .= $content;
    }
		return $out;
	}
  private function sanitar($file,$type){
  	$file=preg_replace(array('/\.*[\/|\\\]/i', '/[\/|\\\]+/i'), array('/', '/'),$file);
    $tmp=explode(".",$file);
    return (end($tmp)==$type) ? $file : '';
  }
	private function collectFile($str,$type){
		$out=array();
    $str=explode(",",$str);
    foreach($str as $f){
      if(($file=$this->sanitar($f,$type))!=''){
      	$out[]=$file;
      }
    }
		return (count($out)>0) ? $out : false;
	}
	private function writeFile($file,$data){
		$flag=false;
		$handle = fopen($this->modx->config['base_path'].$this->outFolder.'/'.$file,"w+");
		if ($handle && flock($handle, LOCK_EX)) {
			fputs($handle, $data);
			flock($handle, LOCK_UN);
			$flag=true;
		fclose($handle);
		}
		return $flag;
	}
	private function debug($message,$file,$line,$trace,$die){
		$msg='';
		$msg="<h3>".$message."</h3>";
		$msg.="<p>".$file.":<strong>".$line."</strong></p>";
		$msg.="<h5>Trace <i>serialize</i></h5>";
		$msg.="<textarea>";
			$msg.=serialize($trace);
		$msg.="</textarea>";
		$this->modx->logEvent(46,(int)$die,$msg,"MinifyX");
		if((int)$die==3){
			die("ERROR IN MinifyX. See log");
		}
	}
}
