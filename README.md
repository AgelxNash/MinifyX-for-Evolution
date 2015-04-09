MinifyX-for-Evolution
=====================
Component MinifyX for MODX Evolution

Базовая настройка
----------
- Залить папку assets в корень вашего сайта
- Создать сниппет MinifyX с кодом из файла **snippet.txt**

Расширенная настройка
----------
- Создать плагин MinifyX с кодом из файла **plugin.txt** и привязать к событию OnDocFormSave
- Создать модуль MinifyX с кодом из файал **module.txt**
- Создать новый документ MinifyX для кастомных CSS стилей с шаблоном blank и типом содержимого text/css
- Создать новый документ MinifyX для кастомных JS скриптов с шаблоном blank и типом содержимого text/javascript
- В настройках модуля на вкладке конфигурация добавить параметр &CSSfile=CSS файлы;textarea; &JSfile=JS файлы;textarea; &CSSdoc=ID CSS документа;int; &JSdoc=ID JS документа;int;
- В параметры ID JS документа и ID CSS документа вписать ID ранее созданных документов
- В параметры JS файлы и CSS файлы вписать полный путь от корня сайта к файлам которые необходимо сжимать. Если несколько файлов одного типа, то их следует разделять запятой
- Поставить галочку "общие параметры" и сохранить модуль
- Опять открыть модуль на редактирование и зайти на вкладку зависимости. Затем запустить управление зависимостями и добавить плагин MinifyX в зависимость.

Возможности при базовой настройке
----------
Запуск сниппета с параметрами **не рекомендуется**

	[!MinifyX? &CSSfile=`assets/templates/tpl/css/bootstrap.css,assets/js/prettify/prettify.css` &CSSdoc=`2` &JSfile=`assets/js/jquery-1.8.3.min.js,assets/templates/tpl/js/modernizr.custom.28468.js,assets/js/jquery.validate.js,assets/js/jquery.form.min.js,assets/js/prettify/prettify.js` &JSdoc=`3` &parse=`1`!]

Параметры сниппета
-------
- **CSSfile** Список файлов с CSS стилями, которые нужно включить в конечный файл и сжать
- **CSSdoc** ID документа в дереве документов с CSS стилями
- **JSfile** Список файлов с JavaScript кодом, которые нужно включить в конечный файл и сжать
- **JSdoc** ID документа в дереве документов с JavaScript кодом
- **parseDoc** Обрабатывать ли MODX теги в документах созданных в дереве документов
- **outCSS** имя CSS файла на выходе. По умолчанию style.css
- **outJS** имя JS файла на выходе. По умолчанию script.js
- **outFolder** в какую папку сохранять сжатый файл. По умолчанию assets/templates/
- **API** режим запуска сниппета. По умолчанию происходит API отключен и в случае успешного выполнения кода сжатые добавляются в секцию head вашего веб-сайта. Если же режим API включен (для включение необходимо указать любое значение этому параметру), то результат своей работы сниппет отдает в виде массива array('js'=>'путь к сжатому JS скрипту','css'=>'путь к не сжатому CSS скрипту'); В случае если сжать файл не удалось или какой-то тип файлов не требовалось сжимать, то этот элемент массива будет пуст.
- **inject** Подключить ли результатирующий файл к странице средствами MODX. По умолчанию отключено
- **cssCompress** Необходимо ли сжатие стилей и скриптов (либо же просто объединить в 1 файл). По умолчанию сжатие включено
- **jsCompress** Необходимо ли сжатие стилей и скриптов (либо же просто объединить в 1 файл). По умолчанию сжатие включено

Возможности при расширенной настройке
--------
- Автоматическое пересоздание CSS или JS файла при изменении документа с CSS стилями и JS кодом соответственно
**ВАЖНО!!!** Если в этих документах используются MODX теги и значение этих тегов зависит от других документов, то необходима донастройка плагина и изменение контролирующего события, чтобы файлы пересоздавались при каждой отчистке кеша MODX. Но делать этого я не рекомендую. Лучше просто исключите этот документ из настроек MinifyX и подключайте его в шаблоне как src="[~id~]"
- Возможность ручного пересоздания сжатых файлов при помощи запуска модуля

Использование GZIP сжатия
--------
Добавить в .htaccess следующие строки:
```
AddEncoding gzip .jgz

#add support gzip JavaScript
RewriteCond %{HTTP_USER_AGENT} ".*Safari.*" [OR]
RewriteCond %{HTTP:Accept-Encoding} gzip
RewriteCond %{REQUEST_FILENAME}.jgz -f
RewriteRule (.*)\.js$ $1\.js.jgz [L]
AddType "text/javascript" .js.jgz

#add support gzip CSS
RewriteCond %{HTTP_USER_AGENT} ".*Safari.*" [OR]
RewriteCond %{HTTP:Accept-Encoding} gzip
RewriteCond %{REQUEST_FILENAME}.jgz -f
RewriteRule (.*)\.js$ $1\.css.jgz [L]
AddType "text/css" .css.jgz
AddEncoding gzip .jgz
```

Пример использования
---------
```
[!MinifyX?
	&CSSfile=`
		assets/js/templates/v1/css/reset.css,
		assets/js/templates/v1/css/grid.css,
		assets/js/templates/v1/css/style.css,
		assets/js/templates/v1/js/custom.css
	`
	&JSfile=`
		assets/js/jquery.js,
		assets/js/templates/v1/js/custom.js
	`
	&cssCompress=`1`
	&jsCompress=`1`
	&outFolder=`assets/templates/`
	&outCSS=`v1.css`
	&outJS=`v1.js`
!]

<link rel="stylesheet" type="text/css" href="/assets/templates/v1.css.jgz" />
<!-- либо <link rel="stylesheet" type="text/css" href="/assets/templates/v1.css" /> !-->
<script type="text/javascript" src="/assets/templates/v1.js.jgz"></script>
<!-- либо <script type="text/javascript" src="/assets/templates/v1.js"></script> !-->
```