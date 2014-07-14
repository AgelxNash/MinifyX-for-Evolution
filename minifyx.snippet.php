<?php
/**
 * MinifyX for MODX Evolution 
 * created by Agel_Nash
 *
 * @category  snippet
 * @version   v 1.0
 * @internal  @legacy_names MinifyX
 */
include($modx->config['base_path']."assets/snippets/MinifyX/MinifyX.core.php");
$CSSfile=isset($CSSfile) ? $CSSfile : '';
$JSfile=isset($JSfile) ? $JSfile : '';
$CSSdoc=isset($CSSdoc) ? (int)$CSSdoc : 0;
$JSdoc=isset($JSdoc) ? (int)$JSdoc : 0;


$MinifyX=new MinifyX($modx);
if(!empty($outFolder)){
	$MinifyX->outFolder = $outFolder;
}
if(!empty($jsFile)){
    $MinifyX->jsFile= $jsFile;
}
if(!empty($cssFile)){
    $MinifyX->cssFile= $cssFile;
}
if($CSSfile!='' || (int)$CSSdoc>0){
  $flagCSS=$MinifyX->minCSS($CSSfile,$CSSdoc);
  $modx->logEvent(46,1,"CSSFILE: ".$CSSfile."<br />CSSDOC: ".$CSSdoc,"MinifyX new CSS");
}

if($JSfile!='' || (int)$JSdoc>0){
  $flagJS=$MinifyX->minJS($JSfile,$JSdoc);
  $modx->logEvent(46,1,"JSFILE: ".$JSfile."<br />JSDOC: ".$JSdoc,"MinifyX newJS");
}

if(isset($API)){
  $data=array('js'=>'','css'=>'');
  if($flagJS){
  	$data['js']="/".$MinifyX->outFolder.$MinifyX->jsFile;
  }
  if($flagCSS){
  	$data['css']="/".$MinifyX->outFolder.$MinifyX->cssFile;
  }	
}else{
  $data='';
  $modx->logEvent(46,2,"slow MODE","MinifyX");
	if($flagJS){
  	$modx->regClientCSS("/".$MinifyX->outFolder.$MinifyX->jsFile);
  }
  if($flagCSS){
  	$modx->regClientCSS("/".$MinifyX->outFolder.$MinifyX->cssFile);
  }
}

return $data;
