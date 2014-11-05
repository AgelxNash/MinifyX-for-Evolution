<?php
/**
 * MinifyX for MODX Evolution 
 * created by Agel_Nash
 *
 * @category  module
 * @version   v 1.0
 * @internal  @legacy_names MinifyX
 * @internal  @installset base, sample
 * @internal  @properties  &CSSfile=CSS файлы;textarea; &JSfile=JS файлы;textarea; &CSSdoc=ID CSS документа;int; &JSdoc=ID JS документа;int;
 */


$param=$modx->event->params;
$param['API']='1';

$flag=$modx->runSnippet("MinifyX",$param);
if(isset($flag['js']) && $flag['js']){
  echo "JS ";
}
if(isset($flag['css']) && $flag['css']){
	echo "CSS ";
}