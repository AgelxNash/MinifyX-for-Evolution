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

if($modx->Event->name == 'OnDocFormSave'){
  $param = $modx->event->params;
  $param['API'] = '1';

  $mode = false;
  $param['CSSdoc'] = isset($param['CSSdoc']) ? (int)$param['CSSdoc'] : 0;
  if($param['id'] == $param['CSSdoc'] && (int)$param['CSSdoc'] > 0){
    $param['JSdoc'] = '';
    $param['JSfile'] = '';
    $mode = 'css';
  }else{
    $param['JSdoc'] = isset($param['JSdoc']) ? (int)$param['JSdoc'] : 0;
    if($param['id'] == $param['JSdoc'] && (int)$param['JSdoc'] > 0){
      $param['CSSdoc'] = '';
      $param['CSSfile'] = '';
      $mode = 'js';
    }
  }
  switch($mode){
    case 'css':
    case 'js':{
      $flag = $modx->runSnippet("MinifyX", $param);
      break;
    }
    default:{
      break;
    }
  }
}