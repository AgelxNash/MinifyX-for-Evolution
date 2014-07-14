<?php
/**
 * MinifyX for MODX Evolution 
 * created by Agel_Nash
 *
 * @category  plugin
 * @version   v 1.0
 * @internal  @legacy_names MinifyX
 * @internal  @installset base, sample
 * @internal  @event OnDocFormSave
 */
 
if($modx->Event->name=='OnDocFormSave'){

  //$modx->db->update(array('content'=>$data),$modx->getFullTableName("site_content"),"id='58'");
  	$param=$modx->event->params;
	  $param['API']='1';

  $mode=false;
  	if($param['id']==$param['CSSdoc'] && (int)$param['CSSdoc']>0){
  		$param['JSdoc']='';
      $param['JSfile']='';
      $mode='css';
  	}else{
    	if($param['id']==$param['JSdoc'] && (int)$param['JSdoc']>0){
  			$param['CSSdoc']='';
      	$param['CSSfile']='';
        $mode='js';
  		} 
    }
  switch($mode){
    case 'css':
    case 'js':{
  		$flag=$modx->runSnippet("MinifyX",$param);
      break;
    }
    default:{
    	break;
    }
  }
}