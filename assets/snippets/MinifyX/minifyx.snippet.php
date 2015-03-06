<?php
/**
 * MinifyX for MODX Evolution
 * created by Agel_Nash
 *
 * @category  snippet
 * @version   v 2.0
 * @internal  @legacy_names MinifyX
 */
$param = is_array($modx->event->params) ? $modx->event->params : array();

$data=array(
  'js' => false,
  'css' => false,
  'params' => &$param
  );
$process = $useJS = $useCSS = false;

include(MODX_BASE_PATH."assets/snippets/MinifyX/MinifyX.core.php");
$MinifyX = new MinifyX($modx);

$param['hashName'] = isset($param['hashName']) ? $param['hashName'] : "MinifyX";

// Список исходных файлов через запятую
$param['CSSfile'] = isset($param['CSSfile']) ? $param['CSSfile'] : '';
$param['JSfile'] = isset($param['JSfile']) ? $param['JSfile'] : '';

// ID документов с доп.стилями (максимум по 1 документу для JS и CSS)
$param['CSSdoc'] = isset($param['CSSdoc']) ? (int)$param['CSSdoc'] : 0;
$param['JSdoc'] = isset($param['JSdoc']) ? (int)$param['JSdoc'] : 0;

// Необходимо ли сжатие стилей и скриптов (либо же просто объединить в 1 файл)
$param['jsCompress'] = isset($param['jsCompress']) ? (int)$param['jsCompress'] : 1;
$param['cssCompress'] = isset($param['cssCompress']) ? (int)$param['cssCompress'] : 1;

// Подключить ли результатирующий файл к странице средствами MODX
$param['inject'] = isset($param['inject']) ? (int)$param['inject'] : 0;

// В какую папку положить результатирующий файл
if(empty($param['outFolder'])){
  $param['outFolder'] = $MinifyX->outFolder;
}else{
  $MinifyX->outFolder = $param['outFolder'];
}
// Имена результатирующих файлов
if(empty($param['outJS'])){
  $param['outJS'] = $MinifyX->jsFile;
}else{
  $MinifyX->jsFile = $param['outJS'];
}
if(empty($param['outCSS'])) {
  $param['outCSS'] = $MinifyX->cssFile;
}else{
  $MinifyX->cssFile = $param['outCSS'];
}

// нужно ли рендерить документы с доп. стилями
$MinifyX->parse = $param['parseDoc'] = isset($param['parseDoc']) ? (int)$param['parseDoc'] : 1;

if(empty($param['API'])){
  $hashFile = MODX_BASE_PATH."assets/cache/".$param['hashName'].".json";
  if(!empty($param['CSSdoc']) || !empty($param['JSdoc'])){
    $result = $modx->db->select(
      'MAX(`editedon`) AS `last_update`',
      $modx->getFullTableName('site_content'),
      '`id` IN ('.(int)$param['CSSdoc'].', '.(int)$param['JSdoc'].')'
      );
    $last_update = (int)$modx->db->getValue($result);
  }else{
    $last_update = 0;
  }

  $hash = json_encode(array(
    'param' => sha1(json_encode($param)),
    'css' => $MinifyX->checkHashFile($param['CSSfile'], 'css'),
    'js' => $MinifyX->checkHashFile($param['JSfile'], 'js'),
    'last_update' => $last_update
    ));
  if(!file_exists($hashFile) || file_get_contents($hashFile) != $hash){
    file_put_contents($hashFile, $hash);
    $process = true;
  }else{
    $process = false;
  }
}else{
  $process = true;
}

// Обработка CSS
if(!empty($param['CSSfile']) || !empty($param['CSSdoc'])){
  $useCSS = true;
  if($process){
    $data['css'] = $MinifyX->minCSS($param['CSSfile'], $param['CSSdoc'], $param['cssCompress']);
  }
}

// Обработка JS
if(!empty($param['JSfile']) || !empty($param['JSdoc'])){
  $useJS = true;
  if($process){
    $data['js'] = $MinifyX->minJS($param['JSfile'], $param['JSdoc'], $param['jsCompress']);
  }
}

//На всякий случай регистрируем плейсхолдер с адресами к файлам
if($useCSS){
  $css = empty($data['css']) ? $params['outFolder'].$params['outCSS'] : $data['css'];
  $modx->setPlaceholder('css.'.$param['hashName'], $css);
  if(empty($param['API']) && !empty($param['inject'])){
    $modx->regClientCSS($modx->getConfig('site_url').$css);
  }
}

if($useJS){
  $js = empty($data['js']) ? $params['outFolder'].$params['outJS'] : $data['js'];
  $modx->setPlaceholder('js.'.$param['hashName'], $js);
  if(empty($param['API']) && !empty($param['inject'])){
    $modx->regClientStartupScript($modx->getConfig('site_url').$js);
  }
}
return empty($API) ? '' : $data;