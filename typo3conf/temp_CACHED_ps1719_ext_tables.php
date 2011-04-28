<?php

###########################
## EXTENSION: cms
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/cms/ext_tables.php
###########################

$_EXTKEY = 'cms';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


# TYPO3 SVN ID: $Id: ext_tables.php 6289 2009-10-28 09:30:11Z steffenk $
if (!defined ('TYPO3_MODE'))	die ('Access denied.');


if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModule('web','layout','top',t3lib_extMgm::extPath($_EXTKEY).'layout/');
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_web_layout','EXT:cms/locallang_csh_weblayout.xml');
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_web_info','EXT:cms/locallang_csh_webinfo.xml');

	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_cms_webinfo_page',
		t3lib_extMgm::extPath($_EXTKEY).'web_info/class.tx_cms_webinfo.php',
		'LLL:EXT:cms/locallang_tca.xml:mod_tx_cms_webinfo_page'
	);
	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_cms_webinfo_lang',
		t3lib_extMgm::extPath($_EXTKEY).'web_info/class.tx_cms_webinfo_lang.php',
		'LLL:EXT:cms/locallang_tca.xml:mod_tx_cms_webinfo_lang'
	);
}


// ******************************************************************
// Extend 'pages'-table
// ******************************************************************

if (TYPO3_MODE=='BE')	{
	// Setting ICON_TYPES (obsolete by the removal of the plugin_mgm extension)
	$ICON_TYPES = array(
		'shop' => array('icon' => 'gfx/i/modules_shop.gif'),
		'board' => array('icon' => 'gfx/i/modules_board.gif'),
		'news' => array('icon' => 'gfx/i/modules_news.gif'),
		'fe_users' => array('icon' => 'gfx/i/fe_users.gif'),
		'approve' => array('icon' => 'gfx/state_checked.png'),
	);
}

	// Adding pages_types:
		// t3lib_div::array_merge() MUST be used!
	$PAGES_TYPES = t3lib_div::array_merge(array(
		'3' => array(
			'icon' => 'pages_link.gif'
		),
		'4' => array(
			'icon' => 'pages_shortcut.gif'
		),
		'5' => array(
			'icon' => 'pages_notinmenu.gif'
		),
		'6' => array(
			'type' => 'web',
			'icon' => 'be_users_section.gif',
			'allowedTables' => '*'
		),
		'7' => array(
			'icon' => 'pages_mountpoint.gif'
		),
		'199' => array(		// TypoScript: Limit is 200. When the doktype is 200 or above, the page WILL NOT be regarded as a 'page' by TypoScript. Rather is it a system-type page
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
		array(
			array('LLL:EXT:cms/locallang_tca.xml:pages.doktype.I.4', '6', 'i/be_users_section.gif'),
			array('LLL:EXT:cms/locallang_tca.xml:pages.doktype.div.link', '--div--'),
			array('LLL:EXT:cms/locallang_tca.xml:pages.doktype.I.2', '4', 'i/pages_shortcut.gif'),
			array('LLL:EXT:cms/locallang_tca.xml:pages.doktype.I.5', '7', 'i/pages_mountpoint.gif'),
			array('LLL:EXT:cms/locallang_tca.xml:pages.doktype.I.8', '3', 'i/pages_link.gif'),
			array('LLL:EXT:cms/locallang_tca.xml:pages.doktype.div.special', '--div--')
		)
	);
	array_splice(
		$TCA['pages']['columns']['doktype']['config']['items'],
		10,
		0,
		array(
			array('LLL:EXT:cms/locallang_tca.xml:pages.doktype.I.7', '199', 'i/spacer_icon.gif')
		)
	);
	array_unshift(
		$TCA['pages']['columns']['doktype']['config']['items'],
		array('LLL:EXT:cms/locallang_tca.xml:pages.doktype.div.page', '--div--')
	);

	// Setting enablecolumns:
	$TCA['pages']['ctrl']['enablecolumns'] = array (
		'disabled' => 'hidden',
		'starttime' => 'starttime',
		'endtime' => 'endtime',
		'fe_group' => 'fe_group',
	);

	// Enable Tabs
	$TCA['pages']['ctrl']['dividers2tabs'] = 1;

	// Adding default value columns:
	$TCA['pages']['ctrl']['useColumnsForDefaultValues'].=',fe_group,hidden';
	$TCA['pages']['ctrl']['transForeignTable'] = 'pages_language_overlay';

	// Adding new columns:
	$TCA['pages']['columns'] = array_merge($TCA['pages']['columns'],array(
		'hidden' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.hidden',
			'config' => array (
				'type' => 'check',
				'default' => '1'
			)
		),
		'starttime' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'endtime' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array (
					'upper' => mktime(0,0,0,12,31,2020),
				)
			)
		),
		'layout' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.layout',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('LLL:EXT:lang/locallang_general.xml:LGL.normal', '0'),
					array('LLL:EXT:cms/locallang_tca.xml:pages.layout.I.1', '1'),
					array('LLL:EXT:cms/locallang_tca.xml:pages.layout.I.2', '2'),
					array('LLL:EXT:cms/locallang_tca.xml:pages.layout.I.3', '3')
				),
				'default' => '0'
			)
		),
		'fe_group' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config' => array (
				'type' => 'select',
				'size' => 5,
				'maxitems' => 20,
				'items' => array (
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'exclusiveKeys' => '-1,-2',
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'ORDER BY fe_groups.title',
			)
		),
		'extendToSubpages' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.extendToSubpages',
			'config' => array (
				'type' => 'check'
			)
		),
		'nav_title' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.nav_title',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max' => '255',
				'checkbox' => '',
				'eval' => 'trim'
			)
		),
		'nav_hide' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.nav_hide',
			'config' => array (
				'type' => 'check'
			)
		),
		'subtitle' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.subtitle',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max' => '255',
				'eval' => ''
			)
		),
		'target' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.target',
			'config' => array (
				'type' => 'input',
				'size' => '20',
				'max' => '80',
				'eval' => 'trim',
				'checkbox' => ''
			)
		),
		'alias' => array (
			'displayCond' => 'VERSION:IS:false',
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.alias',
			'config' => array (
				'type' => 'input',
				'size' => '10',
				'max' => '32',
				'eval' => 'nospace,alphanum_x,lower,unique',
				'softref' => 'notify'
			)
		),
		'url' => array (
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.url',
			'config' => array (
				'type' => 'input',
				'size' => '25',
				'max' => '255',
				'eval' => 'trim,required',
				'softref' => 'url'
			)
		),
		'urltype' => array (
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.type',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('', '0'),
					array('http://', '1'),
					array('https://', '4'),
					array('ftp://', '2'),
					array('mailto:', '3')
				),
				'default' => '1'
			)
		),
		'lastUpdated' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.lastUpdated',
			'config' => array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'newUntil' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.newUntil',
			'config' => array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'cache_timeout' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.cache_timeout',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0),
					array('LLL:EXT:cms/locallang_tca.xml:pages.cache_timeout.I.1', 60),
					array('LLL:EXT:cms/locallang_tca.xml:pages.cache_timeout.I.2', 300),
					array('LLL:EXT:cms/locallang_tca.xml:pages.cache_timeout.I.3', 900),
					array('LLL:EXT:cms/locallang_tca.xml:pages.cache_timeout.I.4', 1800),
					array('LLL:EXT:cms/locallang_tca.xml:pages.cache_timeout.I.5', 3600),
					array('LLL:EXT:cms/locallang_tca.xml:pages.cache_timeout.I.6', 14400),
					array('LLL:EXT:cms/locallang_tca.xml:pages.cache_timeout.I.7', 86400),
					array('LLL:EXT:cms/locallang_tca.xml:pages.cache_timeout.I.8', 172800),
					array('LLL:EXT:cms/locallang_tca.xml:pages.cache_timeout.I.9', 604800),
					array('LLL:EXT:cms/locallang_tca.xml:pages.cache_timeout.I.10', 2678400)
				),
				'default' => '0'
			)
		),
		'no_cache' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.no_cache',
			'config' => array (
				'type' => 'check'
			)
		),
		'no_search' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.no_search',
			'config' => array (
				'type' => 'check'
			)
		),
		'shortcut' => array (
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.shortcut_page',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '3',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1',
				'wizards' => array(
					'suggest' => array(
						'type' => 'suggest',
					),
				),
			),
		),
		'shortcut_mode' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.shortcut_mode',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:cms/locallang_tca.xml:pages.shortcut_mode.I.1', 1),
					array('LLL:EXT:cms/locallang_tca.xml:pages.shortcut_mode.I.2', 2),
				),
				'default' => '0'
			)
		),
		'content_from_pid' => array (
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.content_from_pid',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '1',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1',
				'wizards' => array(
					'suggest' => array(
						'type' => 'suggest',
					),
				),
			),
		),
		'mount_pid' => array (
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.mount_pid',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '1',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1',
				'wizards' => array(
					'suggest' => array(
						'type' => 'suggest',
					),
				),
			),
		),
		'keywords' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.keywords',
			'config' => array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'description' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.description',
			'config' => array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'abstract' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.abstract',
			'config' => array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'author' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.author',
			'config' => array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'author_email' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.email',
			'config' => array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80',
				'softref' => 'email[subst]'
			)
		),
		'media' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.media',
			'config' => array (
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
		'is_siteroot' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.is_siteroot',
			'config' => array (
				'type' => 'check'
			)
		),
		'mount_pid_ol' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.mount_pid_ol',
			'config' => array (
				'type' => 'check'
			)
		),
		'module' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.module',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('', '', ''),
					array('LLL:EXT:cms/locallang_tca.xml:pages.module.I.1', 'shop', 'i/modules_shop.gif'),
					array('LLL:EXT:cms/locallang_tca.xml:pages.module.I.2', 'board', 'i/modules_board.gif'),
					array('LLL:EXT:cms/locallang_tca.xml:pages.module.I.3', 'news', 'i/modules_news.gif'),
					array('LLL:EXT:cms/locallang_tca.xml:pages.module.I.4', 'fe_users', 'i/fe_users.gif'),
					array('LLL:EXT:cms/locallang_tca.xml:pages.module.I.6', 'approve', 'state_checked.png')
				),
				'default' => '',
				'iconsInOptionTags' => 1,
				'noIconsBelowSelect' => 1,
			)
		),
		'fe_login_mode' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.fe_login_mode',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:cms/locallang_tca.xml:pages.fe_login_mode.disableAll', 1),
					array('LLL:EXT:cms/locallang_tca.xml:pages.fe_login_mode.disableGroups', 3),
					array('LLL:EXT:cms/locallang_tca.xml:pages.fe_login_mode.enableAgain', 2),
				)
			)
		),
		'l18n_cfg' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.l18n_cfg',
			'config' => array (
				'type' => 'check',
				'items' => array (
					array('LLL:EXT:cms/locallang_tca.xml:pages.l18n_cfg.I.1', ''),
					array($GLOBALS['TYPO3_CONF_VARS']['FE']['hidePagesIfNotTranslatedByDefault'] ? 'LLL:EXT:cms/locallang_tca.xml:pages.l18n_cfg.I.2a' : 'LLL:EXT:cms/locallang_tca.xml:pages.l18n_cfg.I.2', ''),
				),
			)
		),
	));

		// Add columns to info-display list.
	$TCA['pages']['interface']['showRecordFieldList'].=',alias,hidden,starttime,endtime,fe_group,url,target,no_cache,shortcut,keywords,description,abstract,newUntil,lastUpdated,cache_timeout';


		// Totally overriding all type-settings:
	$TCA['pages']['types'] = array (
			// normal
		'1' => array('showitem' =>
				'doktype;;2;;1-1-1, hidden, nav_hide, title;;3;;2-2-2, subtitle, nav_title,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.metadata,
				--palette--;LLL:EXT:lang/locallang_general.xml:LGL.author;5;;3-3-3, abstract, keywords, description,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.files,
				media,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.options,
				TSconfig;;6;nowrap;6-6-6, storage_pid;;7, l18n_cfg, module, content_from_pid,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access,
				starttime, endtime, fe_login_mode, fe_group, extendToSubpages,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended,
		'),
			// external URL
		'3' => array('showitem' =>
				'doktype;;2;;1-1-1, hidden, nav_hide, title;;3;;2-2-2, subtitle,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.url,
				url;;;;3-3-3, urltype,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.files,
				media,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.options,
				TSconfig;;6;nowrap;5-5-5, storage_pid;;7, l18n_cfg,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access,
				starttime, endtime, fe_group, extendToSubpages,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended,
		'),
			// shortcut
		'4' => array('showitem' =>
				'doktype;;2;;1-1-1, hidden, nav_hide, title;;3;;2-2-2, subtitle,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.shortcut,
				shortcut;;;;3-3-3, shortcut_mode,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.files,
				media,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.options,
				TSconfig;;6;nowrap;5-5-5, storage_pid;;7, l18n_cfg,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access,
				starttime, endtime, fe_group, extendToSubpages,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended,
		'),
			// not in menu
		'5' => array('showitem' =>
				'doktype;;2;;1-1-1, hidden, nav_hide, title;;3;;2-2-2, subtitle,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.files,
				media,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.options,
				TSconfig;;6;nowrap;5-5-5, storage_pid;;7, l18n_cfg, module, content_from_pid,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access,
				starttime, endtime, fe_login_mode, fe_group, extendToSubpages,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended,
		'),
			// mount page
		'7' => array('showitem' =>
				'doktype;;2;;1-1-1, hidden, nav_hide, title;;3;;2-2-2, subtitle, nav_title,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.mount,
				mount_pid;;;;3-3-3, mount_pid_ol,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.files,
				media,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.options,
				TSconfig;;6;nowrap;5-5-5, storage_pid;;7, l18n_cfg, module, content_from_pid,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access,
				starttime, endtime, fe_login_mode, fe_group, extendToSubpages,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended,
		'),
			// spacer
		'199' => array('showitem' =>
				'doktype;;2;;1-1-1, hidden, title,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.options,
				TSconfig;;6;nowrap;5-5-5, storage_pid;;7,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended,
		'),
			// sysfolder
		'254' => array('showitem' =>
				'doktype;;2;;1-1-1, hidden, title;LLL:EXT:lang/locallang_general.xml:LGL.title,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.files,
				media,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.options,
				TSconfig;;6;nowrap;5-5-5, storage_pid;;7, module,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended,
		'),
			// trash
		'255' => array('showitem' =>
				'doktype;;2;;1-1-1, hidden, title,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended,
		')
	);

		// Merging palette settings:
		// t3lib_div::array_merge() MUST be used - otherwise the keys will be re-numbered!
	$TCA['pages']['palettes'] = t3lib_div::array_merge($TCA['pages']['palettes'],array(
		'1' => array('showitem' => 'starttime, endtime, extendToSubpages'),
		'2' => array('showitem' => 'layout, lastUpdated, newUntil, no_search'),
		'3' => array('showitem' => 'alias, target, no_cache, cache_timeout'),
		'5' => array('showitem' => 'author, author_email', 'canNotCollapse' => 1)
	));


	// if the compat version is less than 4.2, pagetype 2 ("Advanced")
	// and pagetype 5 ("Not in menu") are added to TCA.
	if (!t3lib_div::compat_version('4.2')) {
			// Merging in CMS doktypes
		array_splice(
			$TCA['pages']['columns']['doktype']['config']['items'],
			2,
			0,
			array(
				array('LLL:EXT:cms/locallang_tca.xml:pages.doktype.I.0', '2', 'i/pages.gif'),
				array('LLL:EXT:cms/locallang_tca.xml:pages.doktype.I.3', '5', 'i/pages_notinmenu.gif'),
			)
		);
			// setting the doktype 1 ("Standard") to show less fields
		$TCA['pages']['types'][1] = array(
				// standard
			'showitem' =>
					'doktype;;2;;1-1-1, hidden, nav_hide, title;;3;;2-2-2, subtitle,
				--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access,
					starttime, endtime, fe_group, extendToSubpages,
				--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.options,
					TSconfig;;6;nowrap;4-4-4, storage_pid;;7, l18n_cfg,
				--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended,
		');
			// adding doktype 2 ("Advanced")
		$TCA['pages']['types'][2] = array(
			'showitem' =>
					'doktype;;2;;1-1-1, hidden, nav_hide, title;;3;;2-2-2, subtitle, nav_title,
				--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.metadata,
					abstract;;5;;3-3-3, keywords, description,
				--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.files,
					media,
				--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access,
					starttime, endtime, fe_login_mode, fe_group, extendToSubpages,
				--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.options,
					TSconfig;;6;nowrap;6-6-6, storage_pid;;7, l18n_cfg, module, content_from_pid,
				--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended,
		');
	}

// ******************************************************************
// This is the standard TypoScript content table, tt_content
// ******************************************************************
$TCA['tt_content'] = array (
	'ctrl' => array (
		'label' => 'header',
		'label_alt' => 'subheader,bodytext',
		'sortby' => 'sorting',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'title' => 'LLL:EXT:cms/locallang_tca.xml:tt_content',
		'delete' => 'deleted',
		'versioningWS' => 2,
		'versioning_followPages' => true,
		'origUid' => 't3_origuid',
		'type' => 'CType',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.xml:LGL.prependAtCopy',
		'copyAfterDuplFields' => 'colPos,sys_language_uid',
		'useColumnsForDefaultValues' => 'colPos,sys_language_uid',
		'shadowColumnsForNewPlaceholders' => 'colPos',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		),
		'typeicon_column' => 'CType',
		'typeicons' => array (
			'header' => 'tt_content_header.gif',
			'textpic' => 'tt_content_textpic.gif',
			'image' => 'tt_content_image.gif',
			'bullets' => 'tt_content_bullets.gif',
			'table' => 'tt_content_table.gif',
			'splash' => 'tt_content_news.gif',
			'uploads' => 'tt_content_uploads.gif',
			'multimedia' => 'tt_content_mm.gif',
			'media' => 'tt_content_mm.gif',
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
		'mainpalette' => '15',
		'thumbnail' => 'image',
		'requestUpdate' => 'list_type,rte_enabled',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tbl_tt_content.php',
		'dividers2tabs' => 1
	)
);

// ******************************************************************
// fe_users
// ******************************************************************
$TCA['fe_users'] = array (
	'ctrl' => array (
		'label' => 'username',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'fe_cruser_id' => 'fe_cruser_id',
		'title' => 'LLL:EXT:cms/locallang_tca.xml:fe_users',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'disable',
			'starttime' => 'starttime',
			'endtime' => 'endtime'
		),
		'useColumnsForDefaultValues' => 'usergroup,lockToDomain,disable,starttime,endtime',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tbl_cms.php',
		'dividers2tabs' => 1
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'username,password,usergroup,name,address,telephone,fax,email,title,zip,city,country,www,company',
	)
);

// ******************************************************************
// fe_groups
// ******************************************************************
$TCA['fe_groups'] = array (
	'ctrl' => array (
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'delete' => 'deleted',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.xml:LGL.prependAtCopy',
		'enablecolumns' => array (
			'disabled' => 'hidden'
		),
		'title' => 'LLL:EXT:cms/locallang_tca.xml:fe_groups',
		'useColumnsForDefaultValues' => 'lockToDomain',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tbl_cms.php',
		'dividers2tabs' => 1
	)
);

// ******************************************************************
// sys_domain
// ******************************************************************
$TCA['sys_domain'] = array (
	'ctrl' => array (
		'label' => 'domainName',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'title' => 'LLL:EXT:cms/locallang_tca.xml:sys_domain',
		'iconfile' => 'domain.gif',
		'enablecolumns' => array (
			'disabled' => 'hidden'
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tbl_cms.php'
	)
);

// ******************************************************************
// pages_language_overlay
// ******************************************************************
$TCA['pages_language_overlay'] = array (
	'ctrl' => array (
		'label'                           => 'title',
		'tstamp'                          => 'tstamp',
		'title'                           => 'LLL:EXT:cms/locallang_tca.xml:pages_language_overlay',
		'versioningWS'                    => true,
		'versioning_followPages'          => true,
		'origUid'                         => 't3_origuid',
		'crdate'                          => 'crdate',
		'cruser_id'                       => 'cruser_id',
		'delete'                          => 'deleted',
		'enablecolumns'                   => array (
			'disabled'  => 'hidden',
			'starttime' => 'starttime',
			'endtime'   => 'endtime'
		),
		'transOrigPointerField'           => 'pid',
		'transOrigPointerTable'           => 'pages',
		'transOrigDiffSourceField'        => 'l18n_diffsource',
		'shadowColumnsForNewPlaceholders' => 'title',
		'languageField'                   => 'sys_language_uid',
		'mainpalette'                     => 1,
		'dynamicConfigFile'               => t3lib_extMgm::extPath($_EXTKEY) . 'tbl_cms.php',
		'type'                            => 'doktype',
		'dividers2tabs'                   => true
	)
);


// ******************************************************************
// sys_template
// ******************************************************************
$TCA['sys_template'] = array (
	'ctrl' => array (
		'label' => 'title',
		'tstamp' => 'tstamp',
		'sortby' => 'sorting',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.xml:LGL.prependAtCopy',
		'title' => 'LLL:EXT:cms/locallang_tca.xml:sys_template',
		'versioningWS' => true,
		'origUid' => 't3_origuid',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'delete' => 'deleted',
		'adminOnly' => 1,	// Only admin, if any
		'iconfile' => 'template.gif',
		'thumbnail' => 'resources',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime'
		),
		'typeicon_column' => 'root',
		'typeicons' => array (
			'0' => 'template_add.gif'
		),
		'dividers2tabs' => 1,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tbl_cms.php'
	)
);

// ******************************************************************
// static_template
// ******************************************************************
$TCA['static_template'] = array (
	'ctrl' => array (
		'label' => 'title',
		'tstamp' => 'tstamp',
		'title' => 'LLL:EXT:cms/locallang_tca.xml:static_template',
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


###########################
## EXTENSION: sv
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/sv/ext_tables.php
###########################

$_EXTKEY = 'sv';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE == 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['sv']['services'] = array(
		'title'       => 'LLL:EXT:sv/reports/locallang.xml:report_title',
		'description' => 'LLL:EXT:sv/reports/locallang.xml:report_description',
		'icon'		  => 'EXT:sv/reports/tx_sv_report.png',
		'report'      => 'tx_sv_reports_ServicesList'
	);
}

###########################
## EXTENSION: css_styled_content
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/css_styled_content/ext_tables.php
###########################

$_EXTKEY = 'css_styled_content';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


# TYPO3 SVN ID: $Id: ext_tables.php 5337 2009-04-21 08:49:20Z francois $
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

	// add flexform
t3lib_extMgm::addPiFlexFormValue('*', 'FILE:EXT:css_styled_content/flexform_ds.xml','table');
$TCA['tt_content']['types']['table']['showitem']='CType;;4;;1-1-1, hidden, header;;3;;2-2-2, linkToTop;;;;4-4-4,
			--div--;LLL:EXT:cms/locallang_ttc.xml:CType.I.5, layout;;10;;3-3-3, cols, bodytext;;9;nowrap:wizards[table], text_properties, pi_flexform,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access, starttime, endtime';

t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'CSS Styled Content');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/v3.8/', 'CSS Styled Content TYPO3 v3.8');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/v3.9/', 'CSS Styled Content TYPO3 v3.9');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/v4.2/', 'CSS Styled Content TYPO3 v4.2');


###########################
## EXTENSION: tsconfig_help
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/tsconfig_help/ext_tables.php
###########################

$_EXTKEY = 'tsconfig_help';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE == 'BE')	{

	t3lib_extMgm::addModule('help','txtsconfighelpM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}

###########################
## EXTENSION: context_help
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/context_help/ext_tables.php
###########################

$_EXTKEY = 'context_help';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addLLrefForTCAdescr('fe_groups','EXT:context_help/locallang_csh_fe_groups.xml');
t3lib_extMgm::addLLrefForTCAdescr('fe_users','EXT:context_help/locallang_csh_fe_users.xml');
t3lib_extMgm::addLLrefForTCAdescr('pages','EXT:context_help/locallang_csh_pages.xml');
t3lib_extMgm::addLLrefForTCAdescr('pages_language_overlay','EXT:context_help/locallang_csh_pageslol.xml');
t3lib_extMgm::addLLrefForTCAdescr('static_template','EXT:context_help/locallang_csh_statictpl.xml');
t3lib_extMgm::addLLrefForTCAdescr('sys_domain','EXT:context_help/locallang_csh_sysdomain.xml');
t3lib_extMgm::addLLrefForTCAdescr('sys_template','EXT:context_help/locallang_csh_systmpl.xml');
t3lib_extMgm::addLLrefForTCAdescr('tt_content','EXT:context_help/locallang_csh_ttcontent.xml');

###########################
## EXTENSION: extra_page_cm_options
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/extra_page_cm_options/ext_tables.php
###########################

$_EXTKEY = 'extra_page_cm_options';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][]=array(
		'name' => 'tx_extrapagecmoptions',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_extrapagecmoptions.php'
	);
}

###########################
## EXTENSION: impexp
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/impexp/ext_tables.php
###########################

$_EXTKEY = 'impexp';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][]=array(
		'name' => 'tx_impexp_clickmenu',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_impexp_clickmenu.php'
	);

	t3lib_extMgm::insertModuleFunction(
		'user_task',
		'tx_impexp_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY).'modfunc1/class.tx_impexp_modfunc1.php',
		'LLL:EXT:impexp/app/locallang.xml:moduleFunction.tx_impexp_modfunc1'
	);

	t3lib_extMgm::addLLrefForTCAdescr('xMOD_tx_impexp','EXT:impexp/locallang_csh.xml');
}

###########################
## EXTENSION: sys_note
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/sys_note/ext_tables.php
###########################

$_EXTKEY = 'sys_note';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


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

###########################
## EXTENSION: tstemplate
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/tstemplate/ext_tables.php
###########################

$_EXTKEY = 'tstemplate';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	t3lib_extMgm::addModule('web','ts','',t3lib_extMgm::extPath($_EXTKEY).'ts/');

###########################
## EXTENSION: tstemplate_ceditor
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/tstemplate_ceditor/ext_tables.php
###########################

$_EXTKEY = 'tstemplate_ceditor';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_ts',
		'tx_tstemplateceditor',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_tstemplateceditor.php',
		'LLL:EXT:tstemplate/ts/locallang.xml:constantEditor'
	);
}

###########################
## EXTENSION: tstemplate_info
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/tstemplate_info/ext_tables.php
###########################

$_EXTKEY = 'tstemplate_info';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_ts',
		'tx_tstemplateinfo',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_tstemplateinfo.php',
		'LLL:EXT:tstemplate/ts/locallang.xml:infoModify'
	);
}

###########################
## EXTENSION: tstemplate_objbrowser
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/tstemplate_objbrowser/ext_tables.php
###########################

$_EXTKEY = 'tstemplate_objbrowser';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_ts',
		'tx_tstemplateobjbrowser',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_tstemplateobjbrowser.php',
		'LLL:EXT:tstemplate/ts/locallang.xml:objectBrowser'
	);
}

###########################
## EXTENSION: tstemplate_analyzer
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/tstemplate_analyzer/ext_tables.php
###########################

$_EXTKEY = 'tstemplate_analyzer';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_ts',
		'tx_tstemplateanalyzer',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_tstemplateanalyzer.php',
		'LLL:EXT:tstemplate/ts/locallang.xml:templateAnalyzer'
	);
}

###########################
## EXTENSION: func_wizards
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/func_wizards/ext_tables.php
###########################

$_EXTKEY = 'func_wizards';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


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

###########################
## EXTENSION: wizard_crpages
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/wizard_crpages/ext_tables.php
###########################

$_EXTKEY = 'wizard_crpages';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


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

###########################
## EXTENSION: wizard_sortpages
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/wizard_sortpages/ext_tables.php
###########################

$_EXTKEY = 'wizard_sortpages';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


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

###########################
## EXTENSION: lowlevel
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/lowlevel/ext_tables.php
###########################

$_EXTKEY = 'lowlevel';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


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

###########################
## EXTENSION: install
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/install/ext_tables.php
###########################

$_EXTKEY = 'install';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE') {
	t3lib_extMgm::addModule('tools', 'install', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod/');

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['typo3'][] = 'tx_install_report_InstallStatus';
}


###########################
## EXTENSION: belog
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/belog/ext_tables.php
###########################

$_EXTKEY = 'belog';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


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

###########################
## EXTENSION: beuser
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/beuser/ext_tables.php
###########################

$_EXTKEY = 'beuser';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::addModule('tools','beuser','top',t3lib_extMgm::extPath($_EXTKEY).'mod/');

	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = array(
		'name' => 'tx_beuser',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_beuser.php'
	);
}

###########################
## EXTENSION: aboutmodules
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/aboutmodules/ext_tables.php
###########################

$_EXTKEY = 'aboutmodules';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	t3lib_extMgm::addModule('help','aboutmodules','after:about',t3lib_extMgm::extPath($_EXTKEY).'mod/');

###########################
## EXTENSION: setup
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/setup/ext_tables.php
###########################

$_EXTKEY = 'setup';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::addModule('user','setup','after:task',t3lib_extMgm::extPath($_EXTKEY).'mod/');
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_user_setup','EXT:setup/locallang_csh_mod.xml');
}

$GLOBALS['TYPO3_USER_SETTINGS'] = array(
	'ctrl' => array (
		'dividers2tabs' => 1
	),
	'columns' => array (
		'realName' => array(
			'type' => 'text',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:beUser_realName',
			'table' => 'be_users',
			'csh' => 'beUser_realName',
		),
		'email' => array(
			'type' => 'text',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:beUser_email',
			'table' => 'be_users',
			'csh' => 'beUser_email',
		),
		'emailMeAtLogin' => array(
			'type' => 'check',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:emailMeAtLogin',
			'csh' => 'emailMeAtLogin',
		),
		'password' => array(
			'type' => 'password',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:newPassword',
			'table' => 'be_users',
			'csh' => 'newPassword',
			'eval' => 'md5',
		),
		'password2' => array(
			'type' => 'password',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:newPasswordAgain',
			'table' => 'be_users',
			'csh' => 'newPasswordAgain',
			'eval' => 'md5',
		),
		'lang' => array(
			'type' => 'select',
			'itemsProcFunc' => 'SC_mod_user_setup_index->renderLanguageSelect',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:language',
			'csh' => 'language',
		),
		'condensedMode' => array(
			'type' => 'check',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:condensedMode',
			'csh' => 'condensedMode',
		),
		'noMenuMode' => array(
			'type' => 'select',
			'items' => array(
				'0' => 'LLL:EXT:setup/mod/locallang.xml:noMenuMode_def',
				'1' => 'LLL:EXT:setup/mod/locallang.xml:noMenuMode_sel',
				'icons' => 'LLL:EXT:setup/mod/locallang.xml:noMenuMode_icons',
			),
			'label' => 'LLL:EXT:setup/mod/locallang.xml:noMenuMode',
			'csh' => 'noMenuMode',
		),
		'startModule' => array(
			'type' => 'select',
			'itemsProcFunc' => 'SC_mod_user_setup_index->renderStartModuleSelect',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:startModule',
			'csh' => 'startModule',
		),
		'thumbnailsByDefault' => array(
			'type' => 'check',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:showThumbs',
			'csh' => 'showThumbs',
		),
		'helpText' => array(
			'type' => 'check',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:helpText',
			'csh' => 'helpText',
		),
		'edit_wideDocument' => array(
			'type' => 'check',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:edit_wideDocument',
			'csh' => 'edit_wideDocument',
		),
		'edit_showFieldHelp' => array(
			'type' => 'select',
			'items' => array (
				'0' => 'LLL:EXT:setup/mod/locallang.xml:edit_showFieldHelp_none',
				'icon' => 'LLL:EXT:setup/mod/locallang.xml:edit_showFieldHelp_icon',
				'text' => 'LLL:EXT:setup/mod/locallang.xml:edit_showFieldHelp_message',
			),
			'label' => 'LLL:EXT:setup/mod/locallang.xml:edit_showFieldHelp',
			'csh' => 'edit_showFieldHelp',
		),
		'titleLen' => array(
			'type' => 'text',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:maxTitleLen',
			'csh' => 'maxTitleLen',
		),

		'edit_RTE' => array(
			'type' => 'check',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:edit_RTE',
			'csh' => 'edit_RTE',
		),
		'edit_docModuleUpload' => array(
			'type' => 'check',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:edit_docModuleUpload',
			'csh' => 'edit_docModuleUpload',
		),
		'disableCMlayers' => array(
			'type' => 'check',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:disableCMlayers',
			'csh' => 'disableCMlayers',
		),
		'copyLevels' => array(
			'type' => 'text',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:copyLevels',
			'csh' => 'copyLevels',
		),
		'recursiveDelete' => array(
			'type' => 'check',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:recursiveDelete',
			'csh' => 'recursiveDelete',
		),
		'simulate' => array(
			'type' => 'select',
			'itemsProcFunc' => 'SC_mod_user_setup_index->renderSimulateUserSelect',
			'access' => 'admin',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:simulate',
			'csh' => 'simuser'
		),
		'enableFlashUploader' => array(
			'type' => 'check',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:enableFlashUploader',
			'csh' => 'enableFlashUploader',
		),
		'resizeTextareas' => array(
			'type' => 'check',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:resizeTextareas',
			'csh' => 'resizeTextareas',
		),
		'resizeTextareas_MaxHeight' => array(
			'type' => 'text',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:resizeTextareas_MaxHeight',
			'csh' => 'resizeTextareas_MaxHeight',
		),
		'resizeTextareas_Flexible' => array(
			'type' => 'check',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:resizeTextareas_Flexible',
			'csh' => 'resizeTextareas_Flexible',
		),
		'installToolEnableButton' => array(
			'type' => 'user',
			'label' => 'LLL:EXT:setup/mod/locallang.xml:enableInstallTool.label',
			'userFunc' => 'SC_mod_user_setup_index->renderInstallToolEnableFileButton',
			'access' => 'admin',
			'csh' => 'enableInstallTool'
		),
	),
	'showitem' => '--div--;LLL:EXT:setup/mod/locallang.xml:personal_data,realName,email,emailMeAtLogin,password,password2,lang,
			--div--;LLL:EXT:setup/mod/locallang.xml:opening,condensedMode,noMenuMode,startModule,thumbnailsByDefault,helpText,edit_showFieldHelp,titleLen,
			--div--;LLL:EXT:setup/mod/locallang.xml:editFunctionsTab,edit_RTE,edit_wideDocument,edit_docModuleUpload,enableFlashUploader,resizeTextareas,resizeTextareas_MaxHeight,resizeTextareas_Flexible,disableCMlayers,copyLevels,recursiveDelete,
			--div--;LLL:EXT:setup/mod/locallang.xml:adminFunctions,simulate,installToolEnableButton'

);

###########################
## EXTENSION: taskcenter
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/taskcenter/ext_tables.php
###########################

$_EXTKEY = 'taskcenter';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	t3lib_extMgm::addModule('user','task','top',t3lib_extMgm::extPath($_EXTKEY).'task/');

###########################
## EXTENSION: info_pagetsconfig
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/info_pagetsconfig/ext_tables.php
###########################

$_EXTKEY = 'info_pagetsconfig';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


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


###########################
## EXTENSION: viewpage
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/viewpage/ext_tables.php
###########################

$_EXTKEY = 'viewpage';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	t3lib_extMgm::addModule('web','view','after:layout',t3lib_extMgm::extPath($_EXTKEY).'view/');

###########################
## EXTENSION: rtehtmlarea
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/rtehtmlarea/ext_tables.php
###########################

$_EXTKEY = 'rtehtmlarea';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

		// Add static template for Click-enlarge rendering
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/clickenlarge/','Clickenlarge Rendering');

	$TCA['tx_rtehtmlarea_acronym'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:rtehtmlarea/locallang_db.xml:tx_rtehtmlarea_acronym',
		'label' => 'term',
		'default_sortby' => 'ORDER BY term',
		'sortby' => 'sorting',
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


###########################
## EXTENSION: t3skin
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/t3skin/ext_tables.php
###########################

$_EXTKEY = 't3skin';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE == 'BE' || (TYPO3_MODE == 'FE' && isset($GLOBALS['BE_USER']) && $GLOBALS['BE_USER']->isFrontendEditingActive())) {
	global $TBE_STYLES;

		// Support for other extensions to add own icons...
	$presetSkinImgs = is_array($TBE_STYLES['skinImg']) ?
		$TBE_STYLES['skinImg'] :
		array();

	/**
	 * Setting up backend styles and colors
	 */
	$TBE_STYLES['mainColors'] = array(	// Always use #xxxxxx color definitions!
		'bgColor'    => '#FFFFFF',		// Light background color
		'bgColor2'   => '#FEFEFE',		// Steel-blue
		'bgColor3'   => '#F1F3F5',		// dok.color
		'bgColor4'   => '#E6E9EB',		// light tablerow background, brownish
		'bgColor5'   => '#F8F9FB',		// light tablerow background, greenish
		'bgColor6'   => '#E6E9EB',		// light tablerow background, yellowish, for section headers. Light.
		'hoverColor' => '#FF0000',
		'navFrameHL' => '#F8F9FB'
	);

	$TBE_STYLES['colorschemes'][0] = '-|class-main1,-|class-main2,-|class-main3,-|class-main4,-|class-main5';
	$TBE_STYLES['colorschemes'][1] = '-|class-main11,-|class-main12,-|class-main13,-|class-main14,-|class-main15';
	$TBE_STYLES['colorschemes'][2] = '-|class-main21,-|class-main22,-|class-main23,-|class-main24,-|class-main25';
	$TBE_STYLES['colorschemes'][3] = '-|class-main31,-|class-main32,-|class-main33,-|class-main34,-|class-main35';
	$TBE_STYLES['colorschemes'][4] = '-|class-main41,-|class-main42,-|class-main43,-|class-main44,-|class-main45';
	$TBE_STYLES['colorschemes'][5] = '-|class-main51,-|class-main52,-|class-main53,-|class-main54,-|class-main55';

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

	$TBE_STYLES['borderschemes'][0] = array('', '', '', 'wrapperTable');
	$TBE_STYLES['borderschemes'][1] = array('', '', '', 'wrapperTable1');
	$TBE_STYLES['borderschemes'][2] = array('', '', '', 'wrapperTable2');
	$TBE_STYLES['borderschemes'][3] = array('', '', '', 'wrapperTable3');
	$TBE_STYLES['borderschemes'][4] = array('', '', '', 'wrapperTable4');
	$TBE_STYLES['borderschemes'][5] = array('', '', '', 'wrapperTable5');



		// Setting the relative path to the extension in temp. variable:
	$temp_eP = t3lib_extMgm::extRelPath($_EXTKEY);

		// Setting login box image rotation folder:
	$TBE_STYLES['loginBoxImage_rotationFolder'] = $temp_eP.'images/login/';
	$TBE_STYLES['loginBoxImage_author']['loginimage_4_2.jpg'] = 'Photo by Photo by J.C. Franca (www.digitalphoto.com.br)';
#	$TBE_STYLES['loginBoxImage_rotationFolder'] = '';

		// Setting up stylesheets (See template() constructor!)
#	$TBE_STYLES['stylesheet']                   = $temp_eP.'stylesheets/stylesheet.css';			// Alternative stylesheet to the default "typo3/stylesheet.css" stylesheet.
#	$TBE_STYLES['stylesheet2']                  = $temp_eP.'stylesheets/stylesheet.css';			// Additional stylesheet (not used by default).  Set BEFORE any in-document styles
	$TBE_STYLES['styleSheetFile_post']          = $temp_eP.'stylesheets/stylesheet_post.css';		// Additional stylesheet. Set AFTER any in-document styles
#	$TBE_STYLES['inDocStyles_TBEstyle']         = '* {text-align: right;}';							// Additional default in-document styles.
	$TBE_STYLES['stylesheets']['modulemenu']    = $temp_eP.'stylesheets/modulemenu.css';
	$TBE_STYLES['stylesheets']['backend-style'] = $temp_eP.'stylesheets/backend-style.css';
	$TBE_STYLES['stylesheets']['admPanel'] = $temp_eP.'stylesheets/admPanel.css';

		// Alternative dimensions for frameset sizes:
	$TBE_STYLES['dims']['leftMenuFrameW'] = 160;		// Left menu frame width
	$TBE_STYLES['dims']['topFrameH']      = 45;			// Top frame heigth
	$TBE_STYLES['dims']['shortcutFrameH'] = 35;			// Shortcut frame height
	$TBE_STYLES['dims']['selMenuFrame']   = 200;		// Width of the selector box menu frame
	$TBE_STYLES['dims']['navFrameWidth']  = 260;		// Default navigation frame width

		// Setting roll-over background color for click menus:
		// Notice, this line uses the the 'scriptIDindex' feature to override another value in this array (namely $TBE_STYLES['mainColors']['bgColor5']), for a specific script "typo3/alt_clickmenu.php"
	$TBE_STYLES['scriptIDindex']['typo3/alt_clickmenu.php']['mainColors']['bgColor5'] = '#F8F9FB';

		// Setting up auto detection of alternative icons:
	$TBE_STYLES['skinImgAutoCfg'] = array(
		'absDir'             => t3lib_extMgm::extPath($_EXTKEY).'icons/',
		'relDir'             => t3lib_extMgm::extRelPath($_EXTKEY).'icons/',
		'forceFileExtension' => 'gif',	// Force to look for PNG alternatives...
#		'scaleFactor'        => 2/3,	// Scaling factor, default is 1
		'iconSizeWidth'      => 16,
		'iconSizeHeight'     => 16,
	);

		// Changing icon for filemounts, needs to be done here as overwriting the original icon would also change the filelist tree's root icon
	$TCA['sys_filemounts']['ctrl']['iconfile'] = '_icon_ftp_2.gif';

		// Manual setting up of alternative icons. This is mainly for module icons which has a special prefix:
	$TBE_STYLES['skinImg'] = array_merge($presetSkinImgs, array (
		'gfx/ol/blank.gif'                         => array('clear.gif','width="14" height="14"'),
		'MOD:web/website.gif'                      => array($temp_eP.'icons/module_web.gif','width="24" height="24"'),
		'MOD:web_layout/layout.gif'                => array($temp_eP.'icons/module_web_layout.gif','width="24" height="24"'),
		'MOD:web_view/view.gif'                    => array($temp_eP.'icons/module_web_view.gif','width="24" height="24"'),
		'MOD:web_list/list.gif'                    => array($temp_eP.'icons/module_web_list.gif','width="24" height="24"'),
		'MOD:web_info/info.gif'                    => array($temp_eP.'icons/module_web_info.gif','width="24" height="24"'),
		'MOD:web_perm/perm.gif'                    => array($temp_eP.'icons/module_web_perms.gif','width="24" height="24"'),
		'MOD:web_perm/legend.gif'                  => array($temp_eP.'icons/legend.gif','width="24" height="24"'),
		'MOD:web_func/func.gif'                    => array($temp_eP.'icons/module_web_func.gif','width="24" height="24"'),
		'MOD:web_ts/ts1.gif'                       => array($temp_eP.'icons/module_web_ts.gif','width="24" height="24"'),
		'MOD:web_modules/modules.gif'              => array($temp_eP.'icons/module_web_modules.gif','width="24" height="24"'),
		'MOD:web_txversionM1/cm_icon.gif'          => array($temp_eP.'icons/module_web_version.gif','width="24" height="24"'),
		'MOD:file/file.gif'                        => array($temp_eP.'icons/module_file.gif','width="22" height="24"'),
		'MOD:file_list/list.gif'                   => array($temp_eP.'icons/module_file_list.gif','width="22" height="24"'),
		'MOD:file_images/images.gif'               => array($temp_eP.'icons/module_file_images.gif','width="22" height="22"'),
		'MOD:user/user.gif'                        => array($temp_eP.'icons/module_user.gif','width="22" height="22"'),
		'MOD:user_task/task.gif'                   => array($temp_eP.'icons/module_user_taskcenter.gif','width="22" height="22"'),
		'MOD:user_setup/setup.gif'                 => array($temp_eP.'icons/module_user_setup.gif','width="22" height="22"'),
		'MOD:user_doc/document.gif'                => array($temp_eP.'icons/module_doc.gif','width="22" height="22"'),
		'MOD:user_ws/sys_workspace.gif'            => array($temp_eP.'icons/module_user_ws.gif','width="22" height="22"'),
		'MOD:tools/tool.gif'                       => array($temp_eP.'icons/module_tools.gif','width="25" height="24"'),
		'MOD:tools_beuser/beuser.gif'              => array($temp_eP.'icons/module_tools_user.gif','width="24" height="24"'),
		'MOD:tools_em/em.gif'                      => array($temp_eP.'icons/module_tools_em.gif','width="24" height="24"'),
		'MOD:tools_em/install.gif'                 => array($temp_eP.'icons/module_tools_em.gif','width="24" height="24"'),
		'MOD:tools_dbint/db.gif'                   => array($temp_eP.'icons/module_tools_dbint.gif','width="25" height="24"'),
		'MOD:tools_config/config.gif'              => array($temp_eP.'icons/module_tools_config.gif','width="24" height="24"'),
		'MOD:tools_install/install.gif'            => array($temp_eP.'icons/module_tools_install.gif','width="24" height="24"'),
		'MOD:tools_log/log.gif'                    => array($temp_eP.'icons/module_tools_log.gif','width="24" height="24"'),
		'MOD:tools_txphpmyadmin/thirdparty_db.gif' => array($temp_eP.'icons/module_tools_phpmyadmin.gif','width="24" height="24"'),
		'MOD:tools_isearch/isearch.gif'            => array($temp_eP.'icons/module_tools_isearch.gif','width="24" height="24"'),
		'MOD:help/help.gif'                        => array($temp_eP.'icons/module_help.gif','width="23" height="24"'),
		'MOD:help_about/info.gif'                  => array($temp_eP.'icons/module_help_about.gif','width="25" height="24"'),
		'MOD:help_aboutmodules/aboutmodules.gif'   => array($temp_eP.'icons/module_help_aboutmodules.gif','width="24" height="24"'),
		'MOD:help_cshmanual/about.gif'         => array($temp_eP.'icons/module_help_cshmanual.gif','width="25" height="24"'),
		'MOD:help_txtsconfighelpM1/moduleicon.gif' => array($temp_eP.'icons/module_help_ts.gif','width="25" height="24"'),
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
	$TBE_STYLES['skinImg']['MOD:tools_em/install.gif']   = array($temp_eP.'icons/ext/templavoila/mod1/moduleicon.gif','width="22" height="22"');
	$TBE_STYLES['skinImg']['MOD:tools_em/uninstall.gif'] = array($temp_eP.'icons/ext/templavoila/mod1/moduleicon.gif','width="22" height="22"');

		// extJS theme
	$TBE_STYLES['extJS']['theme'] =  $temp_eP . 'extjs/xtheme-t3skin.css';

	//print_a($TBE_STYLES,2);

	// Adding HTML template for login screen
	$TBE_STYLES['htmlTemplates']['templates/login.html'] = 'sysext/t3skin/templates/login.html';

	$GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][] = t3lib_extMgm::extPath('t3skin').'registerIe6Stylesheet.php';

}


###########################
## EXTENSION: indexed_search
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/indexed_search/ext_tables.php
###########################

$_EXTKEY = 'indexed_search';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


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


###########################
## EXTENSION: about
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/about/ext_tables.php
###########################

$_EXTKEY = 'about';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE') {
	t3lib_extMgm::addModule('help','about','top',t3lib_extMgm::extPath($_EXTKEY).'mod/');
}

###########################
## EXTENSION: cshmanual
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/cshmanual/ext_tables.php
###########################

$_EXTKEY = 'cshmanual';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE') {
	t3lib_extMgm::addModule('help','cshmanual','top',t3lib_extMgm::extPath($_EXTKEY).'mod/');
}

###########################
## EXTENSION: opendocs
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/opendocs/ext_tables.php
###########################

$_EXTKEY = 'opendocs';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];



if (!defined('TYPO3_MODE')) 	die('Access denied.');


if(TYPO3_MODE == 'BE') {

	$opendocsPath = t3lib_extMgm::extPath('opendocs');

		// register toolbar item
	$GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][] = $opendocsPath.'registerToolbarItem.php';


		// register AJAX calls
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['tx_opendocs::renderMenu']   = $opendocsPath.'class.tx_opendocs.php:tx_opendocs->renderAjax';
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['tx_opendocs::closeDocument'] = $opendocsPath.'class.tx_opendocs.php:tx_opendocs->closeDocument';

		// register update signal to update the number of open documents
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['updateSignalHook']['tx_opendocs::updateNumber'] = $opendocsPath.'class.tx_opendocs.php:tx_opendocs->updateNumberOfOpenDocsHook';


		// register menu module if option is wanted
	$_EXTCONF = unserialize($_EXTCONF);
	if($_EXTCONF['enableModule']) {
		t3lib_extMgm::addModule('user', 'doc', 'after:ws', $opendocsPath.'mod/');
	}
}


###########################
## EXTENSION: recycler
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/recycler/ext_tables.php
###########################

$_EXTKEY = 'recycler';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE == 'BE') {

		// add module

	t3lib_extMgm::addModulePath('web_txrecyclerM1',t3lib_extMgm::extPath ($_EXTKEY).'mod1/');
	t3lib_extMgm::addModule('web','txrecyclerM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');

}

###########################
## EXTENSION: t3editor
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/t3editor/ext_tables.php
###########################

$_EXTKEY = 't3editor';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (TYPO3_MODE == 'BE') {
	// Register AJAX handlers:
	$TYPO3_CONF_VARS['BE']['AJAX']['tx_t3editor::saveCode'] = 'EXT:t3editor/class.tx_t3editor.php:tx_t3editor->saveCode';
	$TYPO3_CONF_VARS['BE']['AJAX']['tx_t3editor::getPlugins'] = 'EXT:t3editor/class.tx_t3editor.php:tx_t3editor->getPlugins';
	$TYPO3_CONF_VARS['BE']['AJAX']['tx_t3editor_TSrefLoader::getTypes'] = 'EXT:t3editor/lib/ts_codecompletion/class.tx_t3editor_tsrefloader.php:tx_t3editor_TSrefLoader->processAjaxRequest';
	$TYPO3_CONF_VARS['BE']['AJAX']['tx_t3editor_TSrefLoader::getDescription'] = 'EXT:t3editor/lib/ts_codecompletion/class.tx_t3editor_tsrefloader.php:tx_t3editor_TSrefLoader->processAjaxRequest';
	$TYPO3_CONF_VARS['BE']['AJAX']['tx_t3editor_codecompletion::loadTemplates'] = 'EXT:t3editor/lib/ts_codecompletion/class.tx_t3editor_codecompletion.php:tx_t3editor_codecompletion->processAjaxRequest';
}

###########################
## EXTENSION: reports
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/reports/ext_tables.php
###########################

$_EXTKEY = 'reports';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModulePath('tools_txreportsM1', t3lib_extMgm::extPath($_EXTKEY) . 'mod/');
	t3lib_extMgm::addModule('tools', 'txreportsM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod/');

	$statusReport = array(
		'title'       => 'LLL:EXT:reports/reports/locallang.xml:status_report_title',
		'description' => 'LLL:EXT:reports/reports/locallang.xml:status_report_description',
		'report'      => 'tx_reports_reports_Status'
	);

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status'] = array_merge(
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status'],
		$statusReport
	);

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['typo3'][] = 'tx_reports_reports_status_Typo3Status';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['system'][] = 'tx_reports_reports_status_SystemStatus';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['security'][] = 'tx_reports_reports_status_SecurityStatus';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['configuration'][] = 'tx_reports_reports_status_ConfigurationStatus';

	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['Reports::saveCollapseState'] = 'EXT:reports/reports/class.tx_reports_reports_status.php:tx_reports_reports_Status->saveCollapseState';
}


###########################
## EXTENSION: scheduler
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/scheduler/ext_tables.php
###########################

$_EXTKEY = 'scheduler';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


/* $Id: ext_tables.php 6536 2009-11-25 14:07:18Z stucki $ */

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE == 'BE') {
		// Add module
	t3lib_extMgm::addModule('tools', 'txschedulerM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');

		// Add context sensitive help (csh) to the backend module
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_tools_txschedulerM1', 'EXT:' . $_EXTKEY . '/mod1/locallang_csh_scheduler.xml');
}

###########################
## EXTENSION: version
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/version/ext_tables.php
###########################

$_EXTKEY = 'version';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][]=array(
		'name' => 'tx_version_cm1',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_version_cm1.php'
	);

	t3lib_extMgm::addModule('web','txversionM1','',t3lib_extMgm::extPath($_EXTKEY).'cm1/');
}




/**
 * Table "sys_workspace":
 */
$TCA['sys_workspace'] = array(
	'ctrl' => array(
		'label' => 'title',
		'tstamp' => 'tstamp',
		'title' => 'LLL:EXT:lang/locallang_tca.php:sys_workspace',
		'adminOnly' => 1,
		'rootLevel' => 1,
		'delete' => 'deleted',
		'iconfile' => 'sys_workspace.png',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'versioningWS_alwaysAllowLiveEdit' => true,
		'dividers2tabs' => true
	)
);


?>
