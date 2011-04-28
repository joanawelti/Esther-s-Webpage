<?php
###########################
## EXTENSION: cms
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/cms/ext_tables.php
###########################

$_EXTKEY = 'cms';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
# TYPO3 CVS ID: $Id: ext_tables.php 2332 2007-05-09 22:56:41Z tkahler $
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::addModule('web','layout','top',t3lib_extMgm::extPath($_EXTKEY).'layout/');
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_web_layout','EXT:cms/locallang_csh_weblayout.xml');
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_web_info','EXT:cms/locallang_csh_webinfo.xml');

	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_cms_webinfo_page',
		t3lib_extMgm::extPath($_EXTKEY).'web_info/class.tx_cms_webinfo.php',
		'LLL:EXT:cms/locallang_tca.php:mod_tx_cms_webinfo_page'
	);
	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_cms_webinfo_lang',
		t3lib_extMgm::extPath($_EXTKEY).'web_info/class.tx_cms_webinfo_lang.php',
		'LLL:EXT:cms/locallang_tca.php:mod_tx_cms_webinfo_lang'
	);
}


// ******************************************************************
// Extend 'pages'-table
// ******************************************************************

if (TYPO3_MODE=='BE')	{
	// Setting ICON_TYPES (obsolete by the removal of the plugin_mgm extension)
	$ICON_TYPES = Array();
}

	// Adding pages_types:
		// t3lib_div::array_merge() MUST be used!
	$PAGES_TYPES = t3lib_div::array_merge(array(
		'3' => Array(
			'icon' => 'pages_link.gif'
		),
		'4' => Array(
			'icon' => 'pages_shortcut.gif'
		),
		'5' => Array(
			'icon' => 'pages_notinmenu.gif'
		),
		'7' => Array(
			'icon' => 'pages_mountpoint.gif'
		),
		'6' => Array(
			'type' => 'web',
			'icon' => 'be_users_section.gif',
			'allowedTables' => '*'
		),
		'199' => Array(		// TypoScript: Limit is 200. When the doktype is 200 or above, the page WILL NOT be regarded as a 'page' by TypoScript. Rather is it a system-type page
			'type' => 'sys',
			'icon' => 'spacer_icon.gif',
		)
	),$PAGES_TYPES);

	// Add allowed records to pages:
	t3lib_extMgm::allowTableOnStandardPages('pages_language_overlay,tt_content,sys_template,sys_domain');

	// Merging in CMS doktypes:
	array_splice(
		$TCA['pages']['columns']['doktype']['config']['items'],
		1,
		0,
		Array(
			Array('LLL:EXT:cms/locallang_tca.php:pages.doktype.I.0', '2'),
			Array('LLL:EXT:lang/locallang_general.php:LGL.external', '3'),
			Array('LLL:EXT:cms/locallang_tca.php:pages.doktype.I.2', '4'),
			Array('LLL:EXT:cms/locallang_tca.php:pages.doktype.I.3', '5'),
			Array('LLL:EXT:cms/locallang_tca.php:pages.doktype.I.4', '6'),
			Array('LLL:EXT:cms/locallang_tca.php:pages.doktype.I.5', '7'),
			Array('-----', '--div--'),
			Array('LLL:EXT:cms/locallang_tca.php:pages.doktype.I.7', '199')
		)
	);

	// Setting enablecolumns:
	$TCA['pages']['ctrl']['enablecolumns'] = Array (
		'disabled' => 'hidden',
		'starttime' => 'starttime',
		'endtime' => 'endtime',
		'fe_group' => 'fe_group',
	);

	// Adding default value columns:
	$TCA['pages']['ctrl']['useColumnsForDefaultValues'].=',fe_group,hidden';
	$TCA['pages']['ctrl']['transForeignTable'] = 'pages_language_overlay';

	// Adding new columns:
	$TCA['pages']['columns'] = array_merge($TCA['pages']['columns'],Array(
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '1'
			)
		),
		'starttime' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'endtime' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => Array (
					'upper' => mktime(0,0,0,12,31,2020),
				)
			)
		),
		'layout' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.layout',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:lang/locallang_general.php:LGL.normal', '0'),
					Array('LLL:EXT:cms/locallang_tca.php:pages.layout.I.1', '1'),
					Array('LLL:EXT:cms/locallang_tca.php:pages.layout.I.2', '2'),
					Array('LLL:EXT:cms/locallang_tca.php:pages.layout.I.3', '3')
				),
				'default' => '0'
			)
		),
		'fe_group' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fe_group',
			'config' => Array (
				'type' => 'select',
				'size' => 5,
				'maxitems' => 20,
				'items' => Array (
					Array('LLL:EXT:lang/locallang_general.php:LGL.hide_at_login', -1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.any_login', -2),
					Array('LLL:EXT:lang/locallang_general.php:LGL.usergroups', '--div--')
				),
				'exclusiveKeys' => '-1,-2',
				'foreign_table' => 'fe_groups',
			)
		),
		'extendToSubpages' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.extendToSubpages',
			'config' => Array (
				'type' => 'check'
			)
		),
		'nav_title' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.nav_title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '256',
				'checkbox' => '',
				'eval' => 'trim'
			)
		),
		'nav_hide' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.nav_hide',
			'config' => Array (
				'type' => 'check'
			)
		),
		'subtitle' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.subtitle',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '256',
				'eval' => ''
			)
		),
		'target' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.target',
			'config' => Array (
				'type' => 'input',
				'size' => '7',
				'max' => '20',
				'eval' => 'trim',
				'checkbox' => ''
			)
		),
		'alias' => Array (
			'displayCond' => 'VERSION:IS:false',
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.alias',
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'max' => '32',
				'eval' => 'nospace,alphanum_x,lower,unique',
				'softref' => 'notify'
			)
		),
		'url' => Array (
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.url',
			'config' => Array (
				'type' => 'input',
				'size' => '25',
				'max' => '256',
				'eval' => 'trim,required',
				'softref' => 'url'
			)
		),
		'urltype' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.type',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', '0'),
					Array('http://', '1'),
					Array('https://', '4'),
					Array('ftp://', '2'),
					Array('mailto:', '3')
				),
				'default' => '1'
			)
		),
		'lastUpdated' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.lastUpdated',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'newUntil' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.newUntil',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'cache_timeout' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.cache_timeout',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value', 0),
					Array('LLL:EXT:cms/locallang_tca.php:pages.cache_timeout.I.1', 60),
					Array('LLL:EXT:cms/locallang_tca.php:pages.cache_timeout.I.2', 5*60),
					Array('LLL:EXT:cms/locallang_tca.php:pages.cache_timeout.I.3', 15*60),
					Array('LLL:EXT:cms/locallang_tca.php:pages.cache_timeout.I.4', 30*60),
					Array('LLL:EXT:cms/locallang_tca.php:pages.cache_timeout.I.5', 60*60),
					Array('LLL:EXT:cms/locallang_tca.php:pages.cache_timeout.I.6', 4*60*60),
					Array('LLL:EXT:cms/locallang_tca.php:pages.cache_timeout.I.7', 24*60*60),
					Array('LLL:EXT:cms/locallang_tca.php:pages.cache_timeout.I.8', 2*24*60*60),
					Array('LLL:EXT:cms/locallang_tca.php:pages.cache_timeout.I.9', 7*24*60*60),
					Array('LLL:EXT:cms/locallang_tca.php:pages.cache_timeout.I.10', 31*24*60*60)
				),
				'default' => '0'
			)
		),
		'no_cache' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.no_cache',
			'config' => Array (
				'type' => 'check'
			)
		),
		'no_search' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.no_search',
			'config' => Array (
				'type' => 'check'
			)
		),
		'shortcut' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.shortcut_page',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '3',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1'
			)
		),
		'shortcut_mode' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.shortcut_mode',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
					Array('LLL:EXT:cms/locallang_tca.php:pages.shortcut_mode.I.1', 1),
					Array('LLL:EXT:cms/locallang_tca.php:pages.shortcut_mode.I.2', 2),
				),
				'default' => '0'
			)
		),
		'content_from_pid' => Array (
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.content_from_pid',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
					'allowed' => 'pages',
				'size' => '1',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1'
			)
		),
		'mount_pid' => Array (
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.mount_pid',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
					'allowed' => 'pages',
				'size' => '1',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1'
			)
		),
		'keywords' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.keywords',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'description' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.description',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim'
			)
		),
		'abstract' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.abstract',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'author' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.author',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'author_email' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.email',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80',
				'softref' => 'email[subst]'
			)
		),
		'media' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.media',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'].',html,htm,ttf,txt,css',
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
				'uploadfolder' => 'uploads/media',
				'show_thumbs' => '1',
				'size' => '3',
				'maxitems' => '5',
				'minitems' => '0'
			)
		),
		'is_siteroot' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.is_siteroot',
			'config' => Array (
				'type' => 'check'
			)
		),
		'mount_pid_ol' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.mount_pid_ol',
			'config' => Array (
				'type' => 'check'
			)
		),
		'module' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.module',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', ''),
					Array('LLL:EXT:cms/locallang_tca.php:pages.module.I.1', 'shop'),
					Array('LLL:EXT:cms/locallang_tca.php:pages.module.I.2', 'board'),
					Array('LLL:EXT:cms/locallang_tca.php:pages.module.I.3', 'news'),
					Array('LLL:EXT:cms/locallang_tca.php:pages.module.I.4', 'fe_users'),
					Array('LLL:EXT:cms/locallang_tca.php:pages.module.I.6', 'approve')
				),
				'default' => ''
			)
		),
		'fe_login_mode' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.fe_login_mode',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
					Array('LLL:EXT:cms/locallang_tca.php:pages.fe_login_mode.disable', 1),
					Array('LLL:EXT:cms/locallang_tca.php:pages.fe_login_mode.enable', 2),
				)
			)
		),
		'l18n_cfg' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.l18n_cfg',
			'config' => Array (
				'type' => 'check',
				'items' => Array (
					Array('LLL:EXT:cms/locallang_tca.php:pages.l18n_cfg.I.1', ''),
					Array($GLOBALS['TYPO3_CONF_VARS']['FE']['hidePagesIfNotTranslatedByDefault'] ? 'LLL:EXT:cms/locallang_tca.php:pages.l18n_cfg.I.2a' : 'LLL:EXT:cms/locallang_tca.php:pages.l18n_cfg.I.2', ''),
				),
			)
		),
	));

		// Add columns to info-display list.
	$TCA['pages']['interface']['showRecordFieldList'].=',alias,hidden,starttime,endtime,fe_group,url,target,no_cache,shortcut,keywords,description,abstract,newUntil,lastUpdated,cache_timeout';

		// Setting main palette
	$TCA['pages']['ctrl']['mainpalette']='1,15';

		// Totally overriding all type-settings:
	$TCA['pages']['types'] = Array (
		'1' => Array('showitem' => 'hidden;;;;1-1-1, doktype;;2;button, title;;3;;2-2-2, subtitle, nav_hide, TSconfig;;6;nowrap;5-5-5, storage_pid;;7, l18n_cfg'),
		'2' => Array('showitem' => 'hidden;;;;1-1-1, doktype;;2;button, title;;3;;2-2-2, subtitle, nav_hide, nav_title, --div--, abstract;;5;;3-3-3, keywords, description, media;;;;4-4-4, --div--, TSconfig;;6;nowrap;5-5-5, storage_pid;;7, l18n_cfg, fe_login_mode, module, content_from_pid'),
		'3' => Array('showitem' => 'hidden;;;;1-1-1, doktype, title;;3;;2-2-2, subtitle, nav_hide, url;;;;3-3-3, urltype, TSconfig;;6;nowrap;5-5-5, storage_pid;;7, l18n_cfg'),
		'4' => Array('showitem' => 'hidden;;;;1-1-1, doktype, title;;3;;2-2-2, subtitle, nav_hide, shortcut;;;;3-3-3, shortcut_mode, TSconfig;;6;nowrap;5-5-5, storage_pid;;7, l18n_cfg'),
		'5' => Array('showitem' => 'hidden;;;;1-1-1, doktype;;2;button, title;;3;;2-2-2, subtitle, nav_hide, nav_title, --div--, media;;;;4-4-4, --div--, TSconfig;;6;nowrap;5-5-5, storage_pid;;7, l18n_cfg, fe_login_mode, module, content_from_pid'),
		'7' => Array('showitem' => 'hidden;;;;1-1-1, doktype;;2;button, title;;3;;2-2-2, subtitle, nav_hide, nav_title, --div--, mount_pid;;;;3-3-3, mount_pid_ol, media;;;;4-4-4, --div--, TSconfig;;6;nowrap;5-5-5, storage_pid;;7, l18n_cfg, fe_login_mode, module, content_from_pid'),
		'199' => Array('showitem' => 'hidden;;;;1-1-1, doktype, title;;;;2-2-2, TSconfig;;6;nowrap;5-5-5, storage_pid;;7'),
		'254' => Array('showitem' => 'hidden;;;;1-1-1, doktype, title;LLL:EXT:lang/locallang_general.php:LGL.title;;;2-2-2, --div--, TSconfig;;6;nowrap;5-5-5, storage_pid;;7, module'),
		'255' => Array('showitem' => 'hidden;;;;1-1-1, doktype, title;;;;2-2-2')
	);
		// Merging palette settings:
		// t3lib_div::array_merge() MUST be used - otherwise the keys will be re-numbered!
	$TCA['pages']['palettes'] = t3lib_div::array_merge($TCA['pages']['palettes'],Array(
		'1' => Array('showitem' => 'starttime,endtime,extendToSubpages'),
		'15' => Array('showitem' => 'fe_group'),
		'2' => Array('showitem' => 'layout, lastUpdated, newUntil, no_search'),
		'3' => Array('showitem' => 'alias, target, no_cache, cache_timeout'),
		'5' => Array('showitem' => 'author,author_email'),
	));






// ******************************************************************
// This is the standard TypoScript content table, tt_content
// ******************************************************************
$TCA['tt_content'] = Array (
	'ctrl' => Array (
		'label' => 'header',
		'label_alt' => 'subheader,bodytext',
		'sortby' => 'sorting',
		'tstamp' => 'tstamp',
		'title' => 'LLL:EXT:cms/locallang_tca.php:tt_content',
		'delete' => 'deleted',
		'versioningWS' => TRUE,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'type' => 'CType',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',
		'copyAfterDuplFields' => 'colPos,sys_language_uid',
		'useColumnsForDefaultValues' => 'colPos,sys_language_uid',
		'shadowColumnsForNewPlaceholders' => 'colPos',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		),
		'typeicon_column' => 'CType',
		'typeicons' => Array (
			'header' => 'tt_content_header.gif',
			'textpic' => 'tt_content_textpic.gif',
			'image' => 'tt_content_image.gif',
			'bullets' => 'tt_content_bullets.gif',
			'table' => 'tt_content_table.gif',
			'splash' => 'tt_content_news.gif',
			'uploads' => 'tt_content_uploads.gif',
			'multimedia' => 'tt_content_mm.gif',
			'menu' => 'tt_content_menu.gif',
			'list' => 'tt_content_list.gif',
			'mailform' => 'tt_content_form.gif',
			'search' => 'tt_content_search.gif',
			'login' => 'tt_content_login.gif',
			'shortcut' => 'tt_content_shortcut.gif',
			'script' => 'tt_content_script.gif',
			'div' => 'tt_content_div.gif',
			'html' => 'tt_content_html.gif'
		),
		'mainpalette' => '1,15',
		'thumbnail' => 'image',
		'requestUpdate' => 'list_type',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tbl_tt_content.php'
	)
);

// ******************************************************************
// fe_users
// ******************************************************************
$TCA['fe_users'] = Array (
	'ctrl' => Array (
		'label' => 'username',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'fe_cruser_id' => 'fe_cruser_id',
		'title' => 'LLL:EXT:cms/locallang_tca.php:fe_users',
		'delete' => 'deleted',
		'mainpalette' => '1',
		'enablecolumns' => Array (
			'disabled' => 'disable',
			'starttime' => 'starttime',
			'endtime' => 'endtime'
		),
		'useColumnsForDefaultValues' => 'usergroup,lockToDomain,disable,starttime,endtime',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tbl_cms.php'
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'username,password,usergroup,name,address,telephone,fax,email,title,zip,city,country,www,company',
	)
);

// ******************************************************************
// fe_groups
// ******************************************************************
$TCA['fe_groups'] = Array (
	'ctrl' => Array (
		'label' => 'title',
		'tstamp' => 'tstamp',
		'delete' => 'deleted',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',
		'enablecolumns' => Array (
			'disabled' => 'hidden'
		),
		'title' => 'LLL:EXT:cms/locallang_tca.php:fe_groups',
		'useColumnsForDefaultValues' => 'lockToDomain',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tbl_cms.php'
	)
);

// ******************************************************************
// sys_domain
// ******************************************************************
$TCA['sys_domain'] = Array (
	'ctrl' => Array (
		'label' => 'domainName',
		'tstamp' => 'tstamp',
		'sortby' => 'sorting',
		'title' => 'LLL:EXT:cms/locallang_tca.php:sys_domain',
		'iconfile' => 'domain.gif',
		'enablecolumns' => Array (
			'disabled' => 'hidden'
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tbl_cms.php'
	)
);

// ******************************************************************
// pages_language_overlay
// ******************************************************************
$TCA['pages_language_overlay'] = Array (
	'ctrl' => Array (
		'label' => 'title',
		'tstamp' => 'tstamp',
		'title' => 'LLL:EXT:cms/locallang_tca.php:pages_language_overlay',
		'versioningWS' => TRUE,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'delete' => 'deleted',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime'
		),
		'transOrigPointerField' => 'pid',
		'transOrigPointerTable' => 'pages',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'shadowColumnsForNewPlaceholders' => 'title',
		'languageField' => 'sys_language_uid',
		'mainpalette' => 1,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tbl_cms.php'
	)
);


// ******************************************************************
// sys_template
// ******************************************************************
$TCA['sys_template'] = Array (
	'ctrl' => Array (
		'label' => 'title',
		'tstamp' => 'tstamp',
		'sortby' => 'sorting',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',
		'title' => 'LLL:EXT:cms/locallang_tca.php:sys_template',
		'versioningWS' => TRUE,
		'origUid' => 't3_origuid',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'delete' => 'deleted',
		'adminOnly' => 1,	// Only admin, if any
		'iconfile' => 'template.gif',
		'thumbnail' => 'resources',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime'
		),
		'typeicon_column' => 'root',
		'typeicons' => Array (
			'0' => 'template_add.gif'
		),
		'mainpalette' => '1',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tbl_cms.php'
	)
);

// ******************************************************************
// static_template
// ******************************************************************
$TCA['static_template'] = Array (
	'ctrl' => Array (
		'label' => 'title',
		'tstamp' => 'tstamp',
		'title' => 'LLL:EXT:cms/locallang_tca.php:static_template',
		'readOnly' => 1,	// This should always be true, as it prevents the static templates from being altered
		'adminOnly' => 1,	// Only admin, if any
		'rootLevel' => 1,
		'is_static' => 1,
		'default_sortby' => 'ORDER BY title',
		'crdate' => 'crdate',
		'iconfile' => 'template_standard.gif',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tbl_cms.php'
	)
);

?><?php
###########################
## EXTENSION: version
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/version/ext_tables.php
###########################

$_EXTKEY = 'version';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][]=array(
		'name' => 'tx_version_cm1',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_version_cm1.php'
	);

	t3lib_extMgm::addModule('web','txversionM1','',t3lib_extMgm::extPath($_EXTKEY).'cm1/');
}
?><?php
###########################
## EXTENSION: sv
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/sv/ext_tables.php
###########################

$_EXTKEY = 'sv';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// normal services should be added here

?><?php
###########################
## EXTENSION: css_styled_content
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/css_styled_content/ext_tables.php
###########################

$_EXTKEY = 'css_styled_content';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
# TYPO3 CVS ID: $Id: ext_tables.php 937 2005-12-26 23:59:37Z kurfuerst $
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

	// add flexform
t3lib_extMgm::addPiFlexFormValue('default', 'FILE:EXT:css_styled_content/flexform_ds.xml');
t3lib_extMgm::addToAllTCAtypes('tt_content','pi_flexform;;;;1-1-1','table');

t3lib_extMgm::addStaticFile($_EXTKEY,'static/','CSS Styled Content');
?><?php
###########################
## EXTENSION: context_help
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/context_help/ext_tables.php
###########################

$_EXTKEY = 'context_help';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addLLrefForTCAdescr('fe_groups','EXT:context_help/locallang_csh_fe_groups.xml');
t3lib_extMgm::addLLrefForTCAdescr('fe_users','EXT:context_help/locallang_csh_fe_users.xml');
t3lib_extMgm::addLLrefForTCAdescr('pages','EXT:context_help/locallang_csh_pages.xml');
t3lib_extMgm::addLLrefForTCAdescr('pages_language_overlay','EXT:context_help/locallang_csh_pageslol.xml');
t3lib_extMgm::addLLrefForTCAdescr('static_template','EXT:context_help/locallang_csh_statictpl.xml');
t3lib_extMgm::addLLrefForTCAdescr('sys_domain','EXT:context_help/locallang_csh_sysdomain.xml');
t3lib_extMgm::addLLrefForTCAdescr('sys_template','EXT:context_help/locallang_csh_systmpl.xml');
t3lib_extMgm::addLLrefForTCAdescr('tt_content','EXT:context_help/locallang_csh_ttcontent.xml');
?><?php
###########################
## EXTENSION: extra_page_cm_options
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/extra_page_cm_options/ext_tables.php
###########################

$_EXTKEY = 'extra_page_cm_options';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][]=array(
		'name' => 'tx_extrapagecmoptions',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_extrapagecmoptions.php'
	);
}
?><?php
###########################
## EXTENSION: impexp
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/impexp/ext_tables.php
###########################

$_EXTKEY = 'impexp';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][]=array(
		'name' => 'tx_impexp_clickmenu',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_impexp_clickmenu.php'
	);
	t3lib_extMgm::addModulePath('xMOD_tximpexp',t3lib_extMgm::extPath($_EXTKEY).'app/');

	t3lib_extMgm::insertModuleFunction(
		'user_task',
		'tx_impexp_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY).'modfunc1/class.tx_impexp_modfunc1.php',
		'LLL:EXT:impexp/app/locallang.xml:moduleFunction.tx_impexp_modfunc1'
	);

	t3lib_extMgm::addLLrefForTCAdescr('xMOD_tx_impexp','EXT:impexp/locallang_csh.xml');
}
?><?php
###########################
## EXTENSION: sys_note
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/sys_note/ext_tables.php
###########################

$_EXTKEY = 'sys_note';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	$TCA['sys_note'] = Array (
		'ctrl' => Array (
			'label' => 'subject',
			'default_sortby' => 'ORDER BY crdate',
			'tstamp' => 'tstamp',
			'crdate' => 'crdate',
			'cruser_id' => 'cruser',
			'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',
			'delete' => 'deleted',
			'title' => 'LLL:EXT:sys_note/locallang_tca.php:sys_note',
			'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
		),
		'interface' => Array (
			'showRecordFieldList' => 'category,subject,message,author,email,personal'
		),
		'columns' => Array (
			'category' => Array (
				'label' => 'LLL:EXT:lang/locallang_general.php:LGL.category',
				'config' => Array (
					'type' => 'select',
					'items' => Array (
						Array('', '0'),
						Array('LLL:EXT:sys_note/locallang_tca.php:sys_note.category.I.1', '1'),
						Array('LLL:EXT:sys_note/locallang_tca.php:sys_note.category.I.2', '3'),
						Array('LLL:EXT:sys_note/locallang_tca.php:sys_note.category.I.3', '4'),
						Array('LLL:EXT:sys_note/locallang_tca.php:sys_note.category.I.4', '2')
					),
					'default' => '0'
				)
			),
			'subject' => Array (
				'label' => 'LLL:EXT:sys_note/locallang_tca.php:sys_note.subject',
				'config' => Array (
					'type' => 'input',
					'size' => '40',
					'max' => '256'
				)
			),
			'message' => Array (
				'label' => 'LLL:EXT:sys_note/locallang_tca.php:sys_note.message',
				'config' => Array (
					'type' => 'text',
					'cols' => '40',
					'rows' => '15'
				)
			),
			'author' => Array (
				'label' => 'LLL:EXT:lang/locallang_general.php:LGL.author',
				'config' => Array (
					'type' => 'input',
					'size' => '20',
					'eval' => 'trim',
					'max' => '80'
				)
			),
			'email' => Array (
				'label' => 'LLL:EXT:lang/locallang_general.php:LGL.email',
				'config' => Array (
					'type' => 'input',
					'size' => '20',
					'eval' => 'trim',
					'max' => '80'
				)
			),
			'personal' => Array (
				'label' => 'LLL:EXT:sys_note/locallang_tca.php:sys_note.personal',
				'config' => Array (
					'type' => 'check'
				)
			)
		),
		'types' => Array (
			'0' => Array('showitem' => 'category;;;;2-2-2, author, email, personal, subject;;;;3-3-3, message')
		)
	);

	t3lib_extMgm::allowTableOnStandardPages('sys_note');
}

t3lib_extMgm::addLLrefForTCAdescr('sys_note','EXT:sys_note/locallang_csh_sysnote.xml');
?><?php
###########################
## EXTENSION: tstemplate
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/tstemplate/ext_tables.php
###########################

$_EXTKEY = 'tstemplate';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	t3lib_extMgm::addModule('web','ts','',t3lib_extMgm::extPath($_EXTKEY).'ts/');
?><?php
###########################
## EXTENSION: tstemplate_ceditor
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/tstemplate_ceditor/ext_tables.php
###########################

$_EXTKEY = 'tstemplate_ceditor';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_ts',
		'tx_tstemplateceditor',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_tstemplateceditor.php',
		'Constant Editor'
	);
}
?><?php
###########################
## EXTENSION: tstemplate_info
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/tstemplate_info/ext_tables.php
###########################

$_EXTKEY = 'tstemplate_info';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_ts',
		'tx_tstemplateinfo',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_tstemplateinfo.php',
		'Info/Modify'
	);
}
?><?php
###########################
## EXTENSION: tstemplate_objbrowser
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/tstemplate_objbrowser/ext_tables.php
###########################

$_EXTKEY = 'tstemplate_objbrowser';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_ts',
		'tx_tstemplateobjbrowser',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_tstemplateobjbrowser.php',
		'TypoScript Object Browser'
	);
}
?><?php
###########################
## EXTENSION: tstemplate_analyzer
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/tstemplate_analyzer/ext_tables.php
###########################

$_EXTKEY = 'tstemplate_analyzer';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_ts',
		'tx_tstemplateanalyzer',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_tstemplateanalyzer.php',
		'Template Analyzer'
	);
}
?><?php
###########################
## EXTENSION: func_wizards
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/func_wizards/ext_tables.php
###########################

$_EXTKEY = 'func_wizards';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_func',
		'tx_funcwizards_webfunc',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_funcwizards_webfunc.php',
		'LLL:EXT:func_wizards/locallang.php:mod_wizards'
	);
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_web_func','EXT:func_wizards/locallang_csh.xml');
}
?><?php
###########################
## EXTENSION: wizard_crpages
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/wizard_crpages/ext_tables.php
###########################

$_EXTKEY = 'wizard_crpages';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_func',
		'tx_wizardcrpages_webfunc_2',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_wizardcrpages_webfunc_2.php',
		'LLL:EXT:wizard_crpages/locallang.php:wiz_crMany',
		'wiz'
	);
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_web_func','EXT:wizard_crpages/locallang_csh.xml');
}
?><?php
###########################
## EXTENSION: wizard_sortpages
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/wizard_sortpages/ext_tables.php
###########################

$_EXTKEY = 'wizard_sortpages';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_func',
		'tx_wizardsortpages_webfunc_2',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_wizardsortpages_webfunc_2.php',
		'LLL:EXT:wizard_sortpages/locallang.php:wiz_sort',
		'wiz'
	);
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_web_func','EXT:wizard_sortpages/locallang_csh.xml');
}
?><?php
###########################
## EXTENSION: lowlevel
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/lowlevel/ext_tables.php
###########################

$_EXTKEY = 'lowlevel';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::addModule('tools','dbint','',t3lib_extMgm::extPath($_EXTKEY).'dbint/');
	t3lib_extMgm::addModule('tools','config','',t3lib_extMgm::extPath($_EXTKEY).'config/');

/*
	t3lib_extMgm::insertModuleFunction(
		'web_func',
		'tx_lowlevel_cleaner',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_lowlevel_cleaner.php',
		'Cleaner',
		'function',
		'online'
	);
*/
}
?><?php
###########################
## EXTENSION: install
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/install/ext_tables.php
###########################

$_EXTKEY = 'install';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	t3lib_extMgm::addModule('tools','install','',t3lib_extMgm::extPath($_EXTKEY).'mod/');
?><?php
###########################
## EXTENSION: belog
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/belog/ext_tables.php
###########################

$_EXTKEY = 'belog';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::addModule('tools','log','',t3lib_extMgm::extPath($_EXTKEY).'mod/');
	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_belog_webinfo',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_belog_webinfo.php',
		'Log'
	);
}
?><?php
###########################
## EXTENSION: beuser
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/beuser/ext_tables.php
###########################

$_EXTKEY = 'beuser';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::addModule('tools','beuser','top',t3lib_extMgm::extPath($_EXTKEY).'mod/');

	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = array(
		'name' => 'tx_beuser',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_beuser.php'
	);
}
?><?php
###########################
## EXTENSION: aboutmodules
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/aboutmodules/ext_tables.php
###########################

$_EXTKEY = 'aboutmodules';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	t3lib_extMgm::addModule('help','aboutmodules','top',t3lib_extMgm::extPath($_EXTKEY).'mod/');
?><?php
###########################
## EXTENSION: setup
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/setup/ext_tables.php
###########################

$_EXTKEY = 'setup';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::addModule('user','setup','after:task',t3lib_extMgm::extPath($_EXTKEY).'mod/');
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_user_setup','EXT:setup/locallang_csh_mod.xml');
}
?><?php
###########################
## EXTENSION: taskcenter
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/taskcenter/ext_tables.php
###########################

$_EXTKEY = 'taskcenter';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	t3lib_extMgm::addModule('user','task','top',t3lib_extMgm::extPath($_EXTKEY).'task/');
?><?php
###########################
## EXTENSION: info_pagetsconfig
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/info_pagetsconfig/ext_tables.php
###########################

$_EXTKEY = 'info_pagetsconfig';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_infopagetsconfig_webinfo',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_infopagetsconfig_webinfo.php',
		'LLL:EXT:info_pagetsconfig/locallang.php:mod_pagetsconfig'
	);
}

t3lib_extMgm::addLLrefForTCAdescr('_MOD_web_info','EXT:info_pagetsconfig/locallang_csh_webinfo.xml');

?><?php
###########################
## EXTENSION: viewpage
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/viewpage/ext_tables.php
###########################

$_EXTKEY = 'viewpage';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	t3lib_extMgm::addModule('web','view','after:layout',t3lib_extMgm::extPath($_EXTKEY).'view/');
?><?php
###########################
## EXTENSION: rtehtmlarea
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/rtehtmlarea/ext_tables.php
###########################

$_EXTKEY = 'rtehtmlarea';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

	// Add static template for enabling the Click-enlarge feature
if ($TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['enableClickEnlarge']) {
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/clickenlarge/','Clickenlarge Rendering');
}

	$TCA['tx_rtehtmlarea_acronym'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:rtehtmlarea/locallang_db.xml:tx_rtehtmlarea_acronym',
		'label' => 'term',
		'default_sortby' => 'ORDER BY term',
		'sortby' => 'sorting',
		'rootLevel' => 1,
		'delete' => 'deleted',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'htmlarea/skins/default/images/Acronym/ed_acronym.gif',
		)
	);
	 
	t3lib_extMgm::allowTableOnStandardPages('tx_rtehtmlarea_acronym');
?><?php
###########################
## EXTENSION: t3skin
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/t3skin/ext_tables.php
###########################

$_EXTKEY = 't3skin';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// require_once(t3lib_extMgm::extPath('t3skin').'debuglib.php');

if (TYPO3_MODE=='BE')	{

	$presetSkinImgs = is_array($TBE_STYLES['skinImg']) ? $TBE_STYLES['skinImg'] : array();	// Means, support for other extensions to add own icons...

	/**
	 * Setting up backend styles and colors
	 */
	$TBE_STYLES['mainColors'] = Array (	// Always use #xxxxxx color definitions!
	'bgColor' => '#FFFFFF',			// Light background color
	'bgColor2' => '#FEFEFE',		// Steel-blue
	'bgColor3' => '#F1F3F5',		// dok.color
	'bgColor4' => '#E6E9EB',		// light tablerow background, brownish
	'bgColor5' => '#F8F9FB',		// light tablerow background, greenish
	'bgColor6' => '#E6E9EB',		// light tablerow background, yellowish, for section headers. Light.
	'hoverColor' => '#FF0000',
	'navFrameHL' => '#F8F9FB'
	);

	$TBE_STYLES['colorschemes'][0]='-|class-main1,-|class-main2,-|class-main3,-|class-main4,-|class-main5';
	$TBE_STYLES['colorschemes'][1]='-|class-main11,-|class-main12,-|class-main13,-|class-main14,-|class-main15';
	$TBE_STYLES['colorschemes'][2]='-|class-main21,-|class-main22,-|class-main23,-|class-main24,-|class-main25';
	$TBE_STYLES['colorschemes'][3]='-|class-main31,-|class-main32,-|class-main33,-|class-main34,-|class-main35';
	$TBE_STYLES['colorschemes'][4]='-|class-main41,-|class-main42,-|class-main43,-|class-main44,-|class-main45';
	$TBE_STYLES['colorschemes'][5]='-|class-main51,-|class-main52,-|class-main53,-|class-main54,-|class-main55';

	$TBE_STYLES['styleschemes'][0]['all'] = 'CLASS: formField';
	$TBE_STYLES['styleschemes'][1]['all'] = 'CLASS: formField1';
	$TBE_STYLES['styleschemes'][2]['all'] = 'CLASS: formField2';
	$TBE_STYLES['styleschemes'][3]['all'] = 'CLASS: formField3';
	$TBE_STYLES['styleschemes'][4]['all'] = 'CLASS: formField4';
	$TBE_STYLES['styleschemes'][5]['all'] = 'CLASS: formField5';

	$TBE_STYLES['styleschemes'][0]['check'] = 'CLASS: checkbox';
	$TBE_STYLES['styleschemes'][1]['check'] = 'CLASS: checkbox';
	$TBE_STYLES['styleschemes'][2]['check'] = 'CLASS: checkbox';
	$TBE_STYLES['styleschemes'][3]['check'] = 'CLASS: checkbox';
	$TBE_STYLES['styleschemes'][4]['check'] = 'CLASS: checkbox';
	$TBE_STYLES['styleschemes'][5]['check'] = 'CLASS: checkbox';

	$TBE_STYLES['styleschemes'][0]['radio'] = 'CLASS: radio';
	$TBE_STYLES['styleschemes'][1]['radio'] = 'CLASS: radio';
	$TBE_STYLES['styleschemes'][2]['radio'] = 'CLASS: radio';
	$TBE_STYLES['styleschemes'][3]['radio'] = 'CLASS: radio';
	$TBE_STYLES['styleschemes'][4]['radio'] = 'CLASS: radio';
	$TBE_STYLES['styleschemes'][5]['radio'] = 'CLASS: radio';

	$TBE_STYLES['styleschemes'][0]['select'] = 'CLASS: select';
	$TBE_STYLES['styleschemes'][1]['select'] = 'CLASS: select';
	$TBE_STYLES['styleschemes'][2]['select'] = 'CLASS: select';
	$TBE_STYLES['styleschemes'][3]['select'] = 'CLASS: select';
	$TBE_STYLES['styleschemes'][4]['select'] = 'CLASS: select';
	$TBE_STYLES['styleschemes'][5]['select'] = 'CLASS: select';

	$TBE_STYLES['borderschemes'][0]= array('','','','wrapperTable');
	$TBE_STYLES['borderschemes'][1]= array('','','','wrapperTable1');
	$TBE_STYLES['borderschemes'][2]= array('','','','wrapperTable2');
	$TBE_STYLES['borderschemes'][3]= array('','','','wrapperTable3');
	$TBE_STYLES['borderschemes'][4]= array('','','','wrapperTable4');
	$TBE_STYLES['borderschemes'][5]= array('','','','wrapperTable5');



	// Setting the relative path to the extension in temp. variable:
	$temp_eP = t3lib_extMgm::extRelPath($_EXTKEY);

	// Setting login box image rotation folder:
	$TBE_STYLES['loginBoxImage_rotationFolder'] = $temp_eP.'images/login/';
	$TBE_STYLES['loginBoxImage_author']['loginimage_4_1.jpg'] = 'Photo by Ture Andersen (www.tureandersen.dk)';
	#$TBE_STYLES['loginBoxImage_rotationFolder'] = '';

	// Setting up stylesheets (See template() constructor!)
	#	$TBE_STYLES['stylesheet'] = $temp_eP.'stylesheets/stylesheet.css';				// Alternative stylesheet to the default "typo3/stylesheet.css" stylesheet.
	#	$TBE_STYLES['stylesheet2'] = $temp_eP.'stylesheets/stylesheet.css';										// Additional stylesheet (not used by default).  Set BEFORE any in-document styles
	$TBE_STYLES['styleSheetFile_post'] = $temp_eP.'stylesheets/stylesheet_post.css';								// Additional stylesheet. Set AFTER any in-document styles
	#	$TBE_STYLES['inDocStyles_TBEstyle'] = '* {text-align: right;}';										// Additional default in-document styles.

	// Alternative dimensions for frameset sizes:
	$TBE_STYLES['dims']['leftMenuFrameW']=140;		// Left menu frame width
	$TBE_STYLES['dims']['topFrameH']=45;			// Top frame heigth
	$TBE_STYLES['dims']['shortcutFrameH']=35;		// Shortcut frame height
	$TBE_STYLES['dims']['selMenuFrame']=200;		// Width of the selector box menu frame
	$TBE_STYLES['dims']['navFrameWidth']=260;		// Default navigation frame width

	$TBE_STYLES['border'] = $temp_eP.'noborder.html';

	// Setting roll-over background color for click menus:
	// Notice, this line uses the the 'scriptIDindex' feature to override another value in this array (namely $TBE_STYLES['mainColors']['bgColor5']), for a specific script "typo3/alt_clickmenu.php"
	$TBE_STYLES['scriptIDindex']['typo3/alt_clickmenu.php']['mainColors']['bgColor5']='#F8F9FB';

	// Setting up auto detection of alternative icons:
	$TBE_STYLES['skinImgAutoCfg']=array(
	'absDir' => t3lib_extMgm::extPath($_EXTKEY).'icons/',
	'relDir' => t3lib_extMgm::extRelPath($_EXTKEY).'icons/',
	'forceFileExtension' => 'gif',	// Force to look for PNG alternatives...
	#		'scaleFactor' => 2/3,	// Scaling factor, default is 1
	);

	// Manual setting up of alternative icons. This is mainly for module icons which has a special prefix:
	$TBE_STYLES['skinImg'] = array_merge($presetSkinImgs, array (
	'gfx/ol/blank.gif' => array('clear.gif','width="14" height="14"'),
	'MOD:web/website.gif'  => array($temp_eP.'icons/module_web.gif','width="24" height="24"'),
	'MOD:web_layout/layout.gif'  => array($temp_eP.'icons/module_web_layout.gif','width="24" height="24"'),
	'MOD:web_view/view.gif'  => array($temp_eP.'icons/module_web_view.gif','width="24" height="24"'),
	'MOD:web_list/list.gif'  => array($temp_eP.'icons/module_web_list.gif','width="24" height="24"'),
	'MOD:web_info/info.gif'  => array($temp_eP.'icons/module_web_info.gif','width="24" height="24"'),
	'MOD:web_perm/perm.gif'  => array($temp_eP.'icons/module_web_perms.gif','width="24" height="24"'),
	'MOD:web_perm/legend.gif'  => array($temp_eP.'icons/legend.gif','width="24" height="24"'),
	'MOD:web_func/func.gif'  => array($temp_eP.'icons/module_web_func.gif','width="24" height="24"'),
	'MOD:web_ts/ts1.gif'  => array($temp_eP.'icons/module_web_ts.gif','width="24" height="24"'),
	'MOD:web_modules/modules.gif' => array($temp_eP.'icons/module_web_modules.gif','width="24" height="24"'),
	'MOD:file/file.gif'  => array($temp_eP.'icons/module_file.gif','width="22" height="24"'),
	'MOD:file_list/list.gif'  => array($temp_eP.'icons/module_file_list.gif','width="22" height="24"'),
	'MOD:file_images/images.gif'  => array($temp_eP.'icons/module_file_images.gif','width="22" height="22"'),
	'MOD:doc/document.gif'  => array($temp_eP.'icons/module_doc.gif','width="22" height="22"'),
	'MOD:user/user.gif'  => array($temp_eP.'icons/module_user.gif','width="22" height="22"'),
	'MOD:user_task/task.gif'  => array($temp_eP.'icons/module_user_taskcenter.gif','width="22" height="22"'),
	'MOD:user_setup/setup.gif'  => array($temp_eP.'icons/module_user_setup.gif','width="22" height="22"'),
	'MOD:tools/tool.gif'  => array($temp_eP.'icons/module_tools.gif','width="25" height="24"'),
	'MOD:tools_beuser/beuser.gif'  => array($temp_eP.'icons/module_tools_user.gif','width="24" height="24"'),
	'MOD:tools_em/em.gif'  => array($temp_eP.'icons/module_tools_em.gif','width="24" height="24"'),
	'MOD:tools_em/install.gif'  => array($temp_eP.'icons/module_tools_em.gif','width="24" height="24"'),
	'MOD:tools_dbint/db.gif'  => array($temp_eP.'icons/module_tools_dbint.gif','width="25" height="24"'),
	'MOD:tools_config/config.gif'  => array($temp_eP.'icons/module_tools_config.gif','width="24" height="24"'),
	'MOD:tools_install/install.gif'  => array($temp_eP.'icons/module_tools_install.gif','width="24" height="24"'),
	'MOD:tools_log/log.gif'  => array($temp_eP.'icons/module_tools_log.gif','width="24" height="24"'),
	'MOD:tools_txphpmyadmin/thirdparty_db.gif'  => array($temp_eP.'icons/module_tools_phpmyadmin.gif','width="24" height="24"'),
	'MOD:tools_isearch/isearch.gif' => array($temp_eP.'icons/module_tools_isearch.gif','width="24" height="24"'),
	'MOD:help/help.gif'  => array($temp_eP.'icons/module_help.gif','width="23" height="24"'),
	'MOD:help_about/info.gif'  => array($temp_eP.'icons/module_help_about.gif','width="25" height="24"'),
	'MOD:help_aboutmodules/aboutmodules.gif'  => array($temp_eP.'icons/module_help_aboutmodules.gif','width="24" height="24"'),
	));

	// Adding icon for photomarathon extensions' backend module, if enabled:
	if (t3lib_extMgm::isloaded('user_photomarathon'))	{
		$TBE_STYLES['skinImg']['MOD:web_uphotomarathon/tab_icon.gif'] = array($temp_eP.'icons/ext/user_photomarathon/tab_icon.gif','width="24" height="24"');
	}
	// Adding icon for templavoila extensions' backend module, if enabled:
	if (t3lib_extMgm::isloaded('templavoila'))	{
		$TBE_STYLES['skinImg']['MOD:web_txtemplavoilaM1/moduleicon.gif'] = array($temp_eP.'icons/ext/templavoila/mod1/moduleicon.gif','width="22" height="22"');
		$TBE_STYLES['skinImg']['MOD:web_txtemplavoilaM2/moduleicon.gif'] = array($temp_eP.'icons/ext/templavoila/mod1/moduleicon.gif','width="22" height="22"');
	}
	// Adding icon for extension manager' backend module, if enabled:
	$TBE_STYLES['skinImg']['MOD:tools_em/install.gif'] = array($temp_eP.'icons/ext/templavoila/mod1/moduleicon.gif','width="22" height="22"');
	$TBE_STYLES['skinImg']['MOD:tools_em/uninstall.gif'] = array($temp_eP.'icons/ext/templavoila/mod1/moduleicon.gif','width="22" height="22"');

	//print_a($TBE_STYLES,2);
}
?><?php
###########################
## EXTENSION: indexed_search
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/indexed_search/ext_tables.php
###########################

$_EXTKEY = 'indexed_search';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];

?><?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addPlugin(Array('LLL:EXT:indexed_search/locallang.php:mod_indexed_search', $_EXTKEY));

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY] = 'layout,select_key,pages';

if (TYPO3_MODE=='BE')    {
	t3lib_extMgm::addModule('tools','isearch','after:log',t3lib_extMgm::extPath($_EXTKEY).'mod/');

	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_indexedsearch_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY).'modfunc1/class.tx_indexedsearch_modfunc1.php',
		'LLL:EXT:indexed_search/locallang.php:mod_indexed_search'
	);
	t3lib_extMgm::insertModuleFunction(
		"web_info",
		"tx_indexedsearch_modfunc2",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc2/class.tx_indexedsearch_modfunc2.php",
		"LLL:EXT:indexed_search/locallang.php:mod2_indexed_search"
	);
}

t3lib_extMgm::allowTableOnStandardPages('index_config');
t3lib_extMgm::addLLrefForTCAdescr('index_config','EXT:indexed_search/locallang_csh_indexcfg.xml');

if (t3lib_extMgm::isLoaded('crawler'))	{
	$TCA['index_config'] = Array (
		'ctrl' => Array (
			'title' => 'LLL:EXT:indexed_search/locallang_db.php:index_config',
			'label' => 'title',
			'tstamp' => 'tstamp',
			'crdate' => 'crdate',
			'cruser_id' => 'cruser_id',
			'type' => 'type',
			'default_sortby' => 'ORDER BY crdate',
			'enablecolumns' => Array (
				'disabled' => 'hidden',
				'starttime' => 'starttime',
			),
			'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
			'iconfile' => 'default.gif',
		),
		'feInterface' => Array (
			'fe_admin_fieldList' => 'hidden, starttime, title, description, type, depth, table2index, alternative_source_pid, get_params, chashcalc, filepath, extensions',
		)
	);
}


	// Example of crawlerhook (see also ext_localconf.php!)
/*
	t3lib_div::loadTCA('index_config');
	$TCA['index_config']['columns']['type']['config']['items'][] =  Array('My Crawler hook!', 'tx_myext_example1');
	$TCA['index_config']['types']['tx_myext_example1'] = $TCA['index_config']['types']['0'];
*/
?>