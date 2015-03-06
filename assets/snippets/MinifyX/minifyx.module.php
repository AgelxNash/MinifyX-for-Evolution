<?php
/**
 * MinifyX for MODX Evolution
 * created by Agel_Nash
 *
 * @category  module
 * @version   v 2.0
 * @internal  @legacy_names MinifyX
 * @internal  @installset base, sample
 * @internal  @properties  &CSSfile=CSS файлы;textarea; &JSfile=JS файлы;textarea; &CSSdoc=ID CSS документа;int; &JSdoc=ID JS документа;int;
 */

$vars = array(
	'modx_lang_attribute', 'modx_textdir', 'manager_theme', 'modx_manager_charset',
	'_lang', '_style', 'e', 'SystemAlertMsgQueque', 'incPath', 'content'
);
foreach($vars as $var) global $$var;

$param = is_array($modx->event->params) ? $modx->event->params : array();
$param['API']='1';

include($incPath . 'header.inc.php');

$flag = $modx->runSnippet("MinifyX", $param);

function checkCompressRate($in, $doc, $out){
	global $modx;
	$in = explode(",", $in);

	$inSize = (int)$modx->db->getValue("SELECT LENGTH(`content`) FROM ".$modx->getFullTableName("site_content")." WHERE id=".(int)$doc);
	foreach($in as $f){
		if(is_file($modx->getConfig('base_path').$f)){
			$inSize += filesize($modx->getConfig('base_path').$f);
		}
	}

	if(is_file($modx->getConfig('base_path').$out)){
		$outSize = filesize($modx->getConfig('base_path').$out);
	}

	return number_format(100 - 100*$outSize/$inSize, 2, '.', '');
}
?>

<H1>MinifyX</h1>
<div class="section">
	<div class="sectionBody" style="padding:10px 20px;">
		<? if(!empty($flag['js']) && file_exists($modx->getConfig('base_path').$flag['js'])): ?>
			<h2>JS Done</h2>
			<ul>
				<li>
					<strong>Out file:</strong>
					<a href="<?=$modx->getConfig('site_url').$flag['js'];?>" target="_blank"><?=$flag['js'];?></a>
					(<?=$modx->nicesize(filesize($modx->getConfig('base_path').$flag['js']));?>, <?=checkCompressRate($flag['params']['JSfile'], $flag['params']['JSdoc'], $flag['js']);?>% compress)
				</li>
				<li><strong>Input files:</strong> <?=$flag['params']['JSfile'];?></li>
				<? if($modx->db->getValue("SELECT count(`id`) FROM ".$modx->getFullTableName("site_content")." WHERE id=".(int)$flag['params']['JSdoc'])): ?>
					<li><strong>ID input document:</strong> <a href="index.php?a=27&id=<?=$flag['params']['JSdoc'];?>"><?=$flag['params']['JSdoc'];?></a></li>
				<? endif; ?>
			</ul>
		<? else: ?>
			<h2>JS skip</h2>
		<? endif; ?>

		<hr />

		<? if(!empty($flag['css']) && file_exists($modx->getConfig('base_path').$flag['css'])): ?>
			<h2>CSS Done</h2>
			<ul>
				<li>
					<strong>Out file:</strong>
					<a href="<?=$modx->getConfig('site_url').$flag['css'];?>" target="_blank"><?=$flag['css'];?></a>
					(<?=$modx->nicesize(filesize($modx->getConfig('base_path').$flag['css']));?>, <?=checkCompressRate($flag['params']['CSSfile'], $flag['params']['CSSdoc'], $flag['css']);?>% compress)
				</li>
				<li><strong>Input files:</strong> <?=$flag['params']['CSSfile'];?></li>
				<? if($modx->db->getValue("SELECT count(`id`) FROM ".$modx->getFullTableName("site_content")." WHERE id=".(int)$flag['params']['CSSdoc'])): ?>
					<li><strong>ID input document:</strong> <a href="index.php?a=27&id=<?=$flag['params']['CSSdoc'];?>"><?=$flag['params']['CSSdoc'];?></a></li>
				<? endif; ?>
			</ul>
		<? else: ?>
			<h2>CSS skip</h2>
		<? endif; ?>

	</div>
</div>

<div class="section">
	<div class="sectionBody" style="padding:10px 20px;">
		<h3>Autorun with MODX Parser</h1>
		<?php
			$run = array_merge($flag['params'], array('API' => 0, 'inject' => 1));
			$options = array();
			foreach($run as $k => $v){
				$options[] = '&'.$k.'=`'.$v.'`';
			}
		?>
		<blockquote style="border-left:4px solid #aaa;padding-left:10px;">
			[!MinifyX? <?=implode(" ", $options);?>!]
		</blockquote>
	</div>
</div>
<? include('footer.inc.php'); ?>