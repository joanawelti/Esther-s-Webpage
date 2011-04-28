<?php

###########################
## EXTENSION: cms
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/cms/ext_tables.php
###########################

$_EXTKEY = 'cms';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


# TYPO3 SVN ID: $Id: ext_tables.php 8269 2010-07-25 18:16:39Z psychomieze $
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

		// Adding pages_types:
		// t3lib_div::array_merge() MUST be used!
	$PAGES_TYPES = t3lib_div::array_merge(array(
		'3' => array(
		),
		'4' => array(
		),
		'5' => array(
		),
		'6' => array(
			'type' => 'web',
			'allowedTables' => '*'
		),
		'7' => array(
		),
		'199' => array(		// TypoScript: Limit is 200. When the doktype is 200 or above, the page WILL NOT be regarded as a 'page' by TypoScript. Rather is it a system-type page
			'type' => 'sys',
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
				'doktype;;2;;1-1-1, hidden, nav_hide, title;;3;;2-2-2, subtitle, nav_title,
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
		'typeicon_classes' => array(
			'header' => 'mimetypes-x-content-header',
			'textpic' => 'mimetypes-x-content-text-picture',
			'image' => 'mimetypes-x-content-image',
			'bullets' => 'mimetypes-x-content-list-bullets',
			'table' => 'mimetypes-x-content-table',
			'splash' => 'mimetypes-x-content-splash',
			'uploads' => 'mimetypes-x-content-uploads',
			'multimedia' => 'mimetypes-x-content-multimedia',
			'media' => 'mimetypes-x-content-multimedia',
			'menu' => 'mimetypes-x-content-menu',
			'list' => 'mimetypes-x-content-plugin',
			'mailform' => 'mimetypes-x-content-form',
			'search' => 'mimetypes-x-content-search',
			'login' => 'mimetypes-x-content-login',
			'shortcut' => 'mimetypes-x-content-link',
			'script' => 'mimetypes-x-content-script',
			'div' => 'mimetypes-x-content-divider',
			'html' => 'mimetypes-x-content-html',
			'text' => 'mimetypes-x-content-text',
			'default' => 'mimetypes-x-content-text',
		),
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
		'typeicon_classes' => array(
			'default' => 'status-user-frontend',
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
		'typeicon_classes' => array(
			'default' => 'status-user-group-frontend',
		),
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
		'typeicon_classes' => array(
			'default' => 'mimetypes-x-content-domain',
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
		'typeicon_classes' => array(
			'default' => 'mimetypes-x-content-page-language-overlay',
		),

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
		'typeicon_classes' => array(
			'default' => 'mimetypes-x-content-template-extension',
			'1' => 'mimetypes-x-content-template',
		),
		'typeicons' => array (
			'0' => 'template_add.gif'
		),
		'dividers2tabs' => 1,
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


# TYPO3 SVN ID: $Id: ext_tables.php 7543 2010-05-06 16:25:16Z psychomieze $
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

	// add flexform
t3lib_extMgm::addPiFlexFormValue('*', 'FILE:EXT:css_styled_content/flexform_ds.xml','table');
$TCA['tt_content']['types']['table']['showitem']='CType;;4;;1-1-1, hidden, header;;3;;2-2-2, linkToTop;;;;4-4-4,
			--div--;LLL:EXT:cms/locallang_ttc.xml:CType.I.5, layout;;10;;3-3-3, cols, bodytext;;9;nowrap:wizards[table], text_properties, pi_flexform,
			--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access, starttime, endtime, fe_group';

t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'CSS Styled Content');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/v3.8/', 'CSS Styled Content TYPO3 v3.8');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/v3.9/', 'CSS Styled Content TYPO3 v3.9');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/v4.2/', 'CSS Styled Content TYPO3 v4.2');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/v4.3/', 'CSS Styled Content TYPO3 v4.3');

$TCA['tt_content']['columns']['section_frame']['config']['items'][0] = array('LLL:EXT:css_styled_content/locallang_db.php:tt_content.tx_cssstyledcontent_section_frame.I.0', '0');
$TCA['tt_content']['columns']['section_frame']['config']['items'][9] = array('LLL:EXT:css_styled_content/locallang_db.php:tt_content.tx_cssstyledcontent_section_frame.I.9', '66');


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


if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE == 'BE')	{
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = array(
		'name' => 'tx_impexp_clickmenu',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_impexp_clickmenu.php'
	);


	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['taskcenter']['impexp']['tx_impexp_task'] = array(
		'title'       => 'LLL:EXT:impexp/locallang_csh.xml:.alttitle',
		'description' => 'LLL:EXT:impexp/locallang_csh.xml:.description',
		'icon'		  => 'EXT:impexp/export.gif'
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
			--div--;LLL:EXT:setup/mod/locallang.xml:opening,condensedMode,startModule,thumbnailsByDefault,helpText,edit_showFieldHelp,titleLen,
			--div--;LLL:EXT:setup/mod/locallang.xml:editFunctionsTab,edit_RTE,edit_wideDocument,edit_docModuleUpload,enableFlashUploader,resizeTextareas,resizeTextareas_MaxHeight,resizeTextareas_Flexible,disableCMlayers,copyLevels,recursiveDelete,
			--div--;LLL:EXT:setup/mod/locallang.xml:adminFunctions,simulate,installToolEnableButton'

);

###########################
## EXTENSION: taskcenter
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/taskcenter/ext_tables.php
###########################

$_EXTKEY = 'taskcenter';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModulePath('tools_txtaskcenterM1', t3lib_extMgm::extPath($_EXTKEY) . 'task/');
	t3lib_extMgm::addModule('user','task', 'top', t3lib_extMgm::extPath($_EXTKEY) . 'task/');

	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['Taskcenter::saveCollapseState']	= 'EXT:taskcenter/classes/class.tx_taskcenter_status.php:tx_taskcenter_status->saveCollapseState';
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['Taskcenter::saveSortingState']	= 'EXT:taskcenter/classes/class.tx_taskcenter_status.php:tx_taskcenter_status->saveSortingState';
}

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
## EXTENSION: t3skin
## FILE:      /Applications/MAMP/htdocs/typo3/sysext/t3skin/ext_tables.php
###########################

$_EXTKEY = 't3skin';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE == 'BE' || (TYPO3_MODE == 'FE' && isset($GLOBALS['BE_USER']) && $GLOBALS['BE_USER']->isFrontendEditingActive())) {
	global $TBE_STYLES;

		// register as a skin
	$TBE_STYLES['skins'][$_EXTKEY] = array(
		'name' => 't3skin',
	);

		// Support for other extensions to add own icons...
	$presetSkinImgs = is_array($TBE_STYLES['skinImg']) ?
		$TBE_STYLES['skinImg'] :
		array();

	$TBE_STYLES['skins'][$_EXTKEY]['stylesheetDirectories']['sprites'] = 'EXT:t3skin/stylesheets/sprites/';

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

		// Alternative dimensions for frameset sizes:
	$TBE_STYLES['dims']['leftMenuFrameW'] = 190;		// Left menu frame width
	$TBE_STYLES['dims']['topFrameH']      = 42;			// Top frame height
	$TBE_STYLES['dims']['navFrameWidth']  = 280;		// Default navigation frame width

		// Setting roll-over background color for click menus:
		// Notice, this line uses the the 'scriptIDindex' feature to override another value in this array (namely $TBE_STYLES['mainColors']['bgColor5']), for a specific script "typo3/alt_clickmenu.php"
	$TBE_STYLES['scriptIDindex']['typo3/alt_clickmenu.php']['mainColors']['bgColor5'] = '#dedede';

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
		'gfx/ol/blank.gif'                         => array('clear.gif','width="18" height="16"'),
		'MOD:web/website.gif'                      => array($temp_eP.'icons/module_web.gif','width="24" height="24"'),
		'MOD:web_layout/layout.gif'                => array($temp_eP.'icons/module_web_layout.gif','width="24" height="24"'),
		'MOD:web_view/view.gif'                    => array($temp_eP.'icons/module_web_view.png','width="24" height="24"'),
		'MOD:web_list/list.gif'                    => array($temp_eP.'icons/module_web_list.gif','width="24" height="24"'),
		'MOD:web_info/info.gif'                    => array($temp_eP.'icons/module_web_info.png','width="24" height="24"'),
		'MOD:web_perm/perm.gif'                    => array($temp_eP.'icons/module_web_perms.png','width="24" height="24"'),
		'MOD:web_func/func.gif'                    => array($temp_eP.'icons/module_web_func.png','width="24" height="24"'),
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
		'MOD:tools_em/em.gif'                      => array($temp_eP.'icons/module_tools_em.png','width="24" height="24"'),
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

		// Logo at login screen
	$TBE_STYLES['logo_login'] = $temp_eP . 'images/login/typo3logo-white-greyback.gif';

		// extJS theme
	$TBE_STYLES['extJS']['theme'] =  $temp_eP . 'extjs/xtheme-t3skin.css';

	// Adding HTML template for login screen
	$TBE_STYLES['htmlTemplates']['templates/login.html'] = 'sysext/t3skin/templates/login.html';

	$GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][] = t3lib_extMgm::extPath('t3skin').'registerIe6Stylesheet.php';

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['preHeaderRenderHook'][] = t3lib_extMgm::extPath('t3skin').'pngfix/class.tx_templatehook.php:tx_templatehook->registerPngFix';
}


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
	$TYPO3_CONF_VARS['BE']['AJAX']['tx_t3editor::saveCode'] = 'EXT:t3editor/classes/class.tx_t3editor.php:tx_t3editor->ajaxSaveCode';
	$TYPO3_CONF_VARS['BE']['AJAX']['tx_t3editor::getPlugins'] = 'EXT:t3editor/classes/class.tx_t3editor.php:tx_t3editor->getPlugins';
	$TYPO3_CONF_VARS['BE']['AJAX']['tx_t3editor_TSrefLoader::getTypes'] = 'EXT:t3editor/classes/ts_codecompletion/class.tx_t3editor_tsrefloader.php:tx_t3editor_TSrefLoader->processAjaxRequest';
	$TYPO3_CONF_VARS['BE']['AJAX']['tx_t3editor_TSrefLoader::getDescription'] = 'EXT:t3editor/classes/ts_codecompletion/class.tx_t3editor_tsrefloader.php:tx_t3editor_TSrefLoader->processAjaxRequest';
	$TYPO3_CONF_VARS['BE']['AJAX']['tx_t3editor_codecompletion::loadTemplates'] = 'EXT:t3editor/classes/ts_codecompletion/class.tx_t3editor_codecompletion.php:tx_t3editor_codecompletion->processAjaxRequest';
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
		'typeicon_classes' => array(
			'default' => 'mimetypes-x-sys_workspace'
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'versioningWS_alwaysAllowLiveEdit' => true,
		'dividers2tabs' => true
	)
);



###########################
## EXTENSION: powermail
## FILE:      /Applications/MAMP/htdocs/typo3conf/ext/powermail/ext_tables.php
###########################

$_EXTKEY = 'powermail';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) die ('Access denied.');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/default_css/', 'Add default CSS');
$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['powermail']); // Get backandconfig
t3lib_extMgm::allowTableOnStandardPages('tx_powermail_fieldsets');

// including files
include_once(t3lib_extMgm::extPath('powermail') . 'lib/class.user_powermail_tx_powermail_forms_recip_id.php');
if (TYPO3_MODE=='BE') {
	include_once(t3lib_extMgm::extPath('powermail') . 'lib/class.user_powermail_tx_powermail_forms_recip_table.php');
	include_once(t3lib_extMgm::extPath('powermail') . 'lib/class.user_powermail_tx_powermail_forms_preview.php');
	include_once(t3lib_extMgm::extPath('powermail') . 'lib/class.user_powermail_tx_powermail_forms_sender_field.php');
	include_once(t3lib_extMgm::extPath('powermail') . 'lib/class.user_powermail_tx_powermail_fields_fe_field.php');
	include_once(t3lib_extMgm::extPath('powermail') . 'lib/class.user_powermail_tx_powermail_example.php');
	include_once(t3lib_extMgm::extPath('powermail') . 'lib/class.user_powermail_tx_powermail_uid.php');
	include_once(t3lib_extMgm::extPath('powermail') . 'lib/user_powermail_updateError.php');
}
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1'] = 'layout,select_key,pages,recursive';

t3lib_extMgm::addToInsertRecords('tx_powermail_fieldsets');

$TCA['tx_powermail_fieldsets'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_fieldsets',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',		
		'sortby' => 'sorting',
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',		
		'enablecolumns' => array (		
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime'
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY) . 'icon_tx_powermail_fieldsets.gif',
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'fe_group, form, title, felder, hidden',
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_powermail_fields');


t3lib_extMgm::addToInsertRecords('tx_powermail_fields');

$TCA['tx_powermail_fields'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_fields',		
		'requestUpdate' => 'formtype',
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',			
		'sortby' => 'sorting',
		'delete' => 'deleted',			
		'enablecolumns' => array (		
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime'
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY) . 'icon_tx_powermail_fields.gif',
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'fieldset, title, name, flexform, value, size, maxsize, mandantory, more, fe_field, hidden',
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_powermail_mails');


t3lib_extMgm::addToInsertRecords('tx_powermail_mails');

$TCA['tx_powermail_mails'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_mails',		
		'label'     => 'sender',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',			
		'default_sortby' => 'ORDER BY crdate DESC',	
		'delete' => 'deleted',			
		'enablecolumns' => array (		
			'disabled' => 'hidden'
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY) . 'icon_tx_powermail_mails.gif',
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'formid, recipient, subject_r, sender, content, piVars, senderIP, UserAgent, Referer, SP_TZ, hidden',
	)
);

t3lib_div::loadTCA('tt_content');

t3lib_extMgm::addPlugin(
	array(
		'LLL:EXT:powermail/locallang_db.xml:tt_content.CType_pi1', 
		$_EXTKEY . '_pi1', 
		t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
	),
	'CType'
);

$tempColumns = Array (
    'tx_powermail_title' => Array (        
        'exclude' => 1,        
        'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.title',        
        'config' => Array (
            'type' => 'input',    
            'size' => '30',  
			'max' => '30',  
            'eval' => 'required,trim,lower,alphanum_x,nospace',
        )
    ),
    'tx_powermail_recipient' => Array (        
        'exclude' => 1,        
        'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.recipient',        
        'config' => Array (
            'type' => 'text',
            'cols' => '60',    
            'rows' => '2',
        )
    ),
	'tx_powermail_subject_r' => Array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.subject_r',		
		'config' => Array (
			'type' => 'input',	
			'size' => '30',
		)
	),
	'tx_powermail_subject_s' => Array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.subject_s',		
		'config' => Array (
			'type' => 'input',	
			'size' => '30',
		)
	),
	'tx_powermail_sender' => Array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.sender',		
		'config' => Array (
			'type' => 'select',	
			'items' => Array (
				Array('LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.recip_table.I.0', '0'),
			),
			'itemsProcFunc' => 'user_powermail_tx_powermail_forms_sender_field->main',
			'size' => 1,	
			'maxitems' => 1,
		)
	),
	'tx_powermail_sendername' => Array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.sendername',		
		'config' => Array (
			'type' => 'select',	
			'items' => array(),
			'itemsProcFunc' => 'user_powermail_tx_powermail_forms_sender_field->main',
			'size' => 3,	
			'maxitems' => 10,
		)
	),
	'tx_powermail_confirm' => Array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.confirm',		
		'config' => Array (
			'type' => 'check',
			'default' => 1,
		)
	),
	'tx_powermail_pages' => Array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.startingpoint',		
		'config' => Array (
			'type' => 'group',
			'internal_type' => 'db',
			'allowed' => 'pages',
			'size' => 1,	
			'maxitems' => 1,
			'show_thumbs' => 1,
		)
	),
	'tx_powermail_multiple' => Array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.multiple',		
		'config' => Array (
			'type'  => 'select',
			'items' => array (
				Array('LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.multiple.I.0', '0'),
				Array('LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.multiple.I.1', '1'),
				Array('LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.multiple.I.2', '2'),
			),
		)
	),
	'tx_powermail_recip_table' => Array (		
		'exclude' => 1,
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.recip_table',		
		'config' => Array (
			'type' => 'select',
			'items' => Array (
				Array('', '0'),
			),
			'itemsProcFunc' => 'user_powermail_tx_powermail_forms_recip_table->main',
			'size' => 1,	
			'maxitems' => 1,
		)
	),
	'tx_powermail_recip_id' => Array (		
		'exclude' => 1,		
		'displayCond' => 'FIELD:tx_powermail_recip_table:REQ:true',
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.recip_id',		
		'config' => Array (
			'type' => 'select',
			'items' => Array (
			),
			'itemsProcFunc' => 'user_powermail_tx_powermail_forms_recip_id->main',
			'size' => 5,	
			'maxitems' => 100,
			'allowNonIdValues' => 1,
		)
	),
	'tx_powermail_thanks' => Array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.thanks',		
		'config' => Array (
			'type' => 'text',
			'cols' => '60',
			'rows' => '2',
			'default' => '###POWERMAIL_ALL###',
		)
	),
	'tx_powermail_mailsender' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.mailsender',
		'config' => Array (
			'type' => 'text',
			'cols' => '60',
			'rows' => '2',
			'default' => '###POWERMAIL_ALL###',
		)
	),
	'tx_powermail_mailreceiver' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.mailreceiver',
		'config' => Array (
			'type' => 'text',
			'cols' => '60',
			'rows' => '2',
			'default' => '###POWERMAIL_ALL###',
		)
	),
	'tx_powermail_redirect' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.redirect',
		'config' => Array (
			'type' => 'input',
			'size' => '30',
			'wizards' => Array(
				'_PADDING' => 2,
				'link' => Array(
					'type' => 'popup',
					'title' => 'Link',
					'icon' => 'link_popup.gif',
					'script' => 'browse_links.php?mode=wizard',
					'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
				),
			),
		)
	),
	'user_powermail_updateError' => Array (
		'label' => 'Powermail error',
		'config' => Array (
			'type' => 'user',
			'userFunc' => 'user_powermail_updateError->user_updateError'
		)
	),
	'tx_powermail_fieldsets' => Array(
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.fieldsets',		
		'config' => Array (
			'type' => 'inline',
			'foreign_table' => 'tx_powermail_fieldsets',
			'foreign_field' => 'tt_content',
			'foreign_sortby' => 'sorting',
			'foreign_label' => 'title',
			'maxitems' => 1000,
			'appearance' => Array(
				'collapseAll' => 1,
				'expandSingle' => 1,
				'useSortable' => 1,
				'newRecordLinkAddTitle' => 1,
				'newRecordLinkPosition' => 'both',
				
				'showSynchronizationLink' => 0,
				'showAllLocalizationLink' => 1,
				'showPossibleLocalizationRecords' => 1,
				'showRemovedLocalizationRecords' => 1,
			),
			'behaviour' => array(
				'localizeChildrenAtParentLocalization' => 1,						    		
				'localizationMode' => 'select',
			),			
		)
	),
	'tx_powermail_users' => Array (		
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.users',
		'config' => Array (
			'type' => 'passthrough'
		)
	),
	'tx_powermail_preview' => Array (		
		'label' => 'LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.preview',
		'config' => Array (
			'type' => 'user',
			'userFunc' => 'user_powermail_tx_powermail_forms_preview->main',
		)
	),
);
// If preview window is deactivated, clear tx_powermail_preview
if ($confArr['usePreview'] != 1) unset($tempColumns['tx_powermail_preview']);

// If settings for powermail found in localconf, clear user_powermail_updateError
if (strlen($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['powermail']) > 1) unset($tempColumns['user_powermail_updateError']);

t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addTCAcolumns('tt_content',$tempColumns,1);
$TCA['tt_content']['types'][$_EXTKEY . '_pi1']['showitem'] = '
	CType;;4;button;1-1-1, hidden,1-1-1, header;;3;;3-3-3, linkToTop;;;;3-3-3,
	--div--;LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.div1, tx_powermail_title;;;;2-2-2, tx_powermail_pages;;;;3-3-3, tx_powermail_confirm;;;;3-3-3, tx_powermail_multiple,
	--div--;LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.div2, tx_powermail_fieldsets;;;;4-4-4, user_powermail_updateError, tx_powermail_preview,
	--div--;LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.div3, tx_powermail_sender, tx_powermail_sendername, tx_powermail_subject_s,, tx_powermail_mailsender;;;richtext:rte_transform[mode=ts],
	--div--;LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.div4, tx_powermail_subject_r, tx_powermail_recipient, tx_powermail_users;;;;5-5-5,tx_powermail_recip_table, tx_powermail_recip_id, tx_powermail_mailreceiver;;;richtext:rte_transform[mode=ts],
	--div--;LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.div5, tx_powermail_thanks;;;richtext:rte_transform[mode=ts], tx_powermail_redirect,
	--div--;LLL:EXT:powermail/locallang_db.xml:tx_powermail_forms.div8' . (t3lib_div::int_from_ver(TYPO3_version) < t3lib_div::int_from_ver('4.2.0') ? '' : ', starttime, endtime');
	
// If preview window is deactivated, clear tx_powermail_preview
if($confArr['usePreview'] != 1) $TCA['tt_content']['types'][$_EXTKEY . '_pi1']['showitem'] = str_replace('tx_powermail_preview,','',$TCA['tt_content']['types'][$_EXTKEY . '_pi1']['showitem']); // remove field



// add tx_powermail_recip_table to the requestUpdate
$TCA['tt_content']['ctrl']['requestUpdate'] .= $TCA['tt_content']['ctrl']['requestUpdate'] ? ',tx_powermail_recip_table' : 'tx_powermail_recip_table';
// possibility to activate dividers2tabs only if version is lower than 4.2
if (t3lib_div::int_from_ver(TYPO3_version) < t3lib_div::int_from_ver('4.2.0')) {
	$TCA['tt_content']['ctrl']['dividers2tabs'] = $confArr['TabDividers'] == 0 ? FALSE : TRUE; 
}



t3lib_extMgm::addLLrefForTCAdescr('tt_content','EXT:powermail/lang/locallang_csh_tt_content.php');

if (TYPO3_MODE=='BE') {	
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_powermail_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY) . 'pi1/class.tx_powermail_pi1_wizicon.php';
	t3lib_extMgm::addModule('web', 'txpowermailM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
}

###########################
## EXTENSION: static_info_tables
## FILE:      /Applications/MAMP/htdocs/typo3conf/ext/static_info_tables/ext_tables.php
###########################

$_EXTKEY = 'static_info_tables';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addStaticFile(STATIC_INFO_TABLES_EXTkey, 'static/static_info_tables/', 'Static Info tables');

$TCA['static_territories'] = array(
	'ctrl' => array(
		'label' => 'tr_name_en',
		'label_alt' => 'tr_name_en,tr_iso_nr',
		'readOnly' => 1,	// This should always be true, as it prevents the static data from being altered
		'adminOnly' => 1,
		'rootLevel' => 1,
		'is_static' => 1,
		'default_sortby' => 'ORDER BY tr_name_en',
		'title' => 'LLL:EXT:'.STATIC_INFO_TABLES_EXTkey.'/locallang_db.xml:static_territories.title',
		'dynamicConfigFile' => PATH_BE_staticinfotables.'tca.php',
		'iconfile' => PATH_BE_staticinfotables_rel.'icon_static_territories.gif',
	),
	'interface' => array(
		'showRecordFieldList' => 'tr_name_en,tr_iso_nr'
	)
);

// Country reference data from ISO 3166-1
$TCA['static_countries'] = array(
	'ctrl' => array(
		'label' => 'cn_short_en',
		'label_alt' => 'cn_short_en,cn_iso_2',
		'readOnly' => 1,	// This should always be true, as it prevents the static data from being altered
		'adminOnly' => 1,
		'rootLevel' => 1,
		'is_static' => 1,
		'default_sortby' => 'ORDER BY cn_short_en',
		'delete' => 'deleted',
		'title' => 'LLL:EXT:'.STATIC_INFO_TABLES_EXTkey.'/locallang_db.xml:static_countries.title',
		'dynamicConfigFile' => PATH_BE_staticinfotables.'tca.php',
		'iconfile' => PATH_BE_staticinfotables_rel.'icon_static_countries.gif',
	),
	'interface' => array(
		'showRecordFieldList' => 'cn_iso_2,cn_iso_3,cn_iso_nr,cn_official_name_local,cn_official_name_en,cn_capital,cn_tldomain,cn_currency_iso_3,cn_currency_iso_nr,cn_phone,cn_uno_member,cn_eu_member,cn_address_format,cn_short_en'
	)
);

// Country subdivision reference data from ISO 3166-2
$TCA['static_country_zones'] = array(
	'ctrl' => array(
		'label' => 'zn_name_local',
		'label_alt' => 'zn_name_local,zn_code',
		'readOnly' => 1,
		'adminOnly' => 1,
		'rootLevel' => 1,
		'is_static' => 1,
		'default_sortby' => 'ORDER BY zn_name_local',
		'title' => 'LLL:EXT:'.STATIC_INFO_TABLES_EXTkey.'/locallang_db.xml:static_country_zones.title',
		'dynamicConfigFile' => PATH_BE_staticinfotables.'tca.php',
		'iconfile' => PATH_BE_staticinfotables_rel.'icon_static_countries.gif',
	),
	'interface' => array(
		'showRecordFieldList' => 'zn_country_iso_nr,zn_country_iso_3,zn_code,zn_name_local,zn_name_en'
	)
);

// Language reference data from ISO 639-1
$TCA['static_languages'] = array(
	'ctrl' => array(
		'label' => 'lg_name_en',
		'label_alt' => 'lg_name_en,lg_iso_2',
		'readOnly' => 1,
		'adminOnly' => 1,
		'rootLevel' => 1,
		'is_static' => 1,
		'default_sortby' => 'ORDER BY lg_name_en',
		'title' => 'LLL:EXT:'.STATIC_INFO_TABLES_EXTkey.'/locallang_db.xml:static_languages.title',
		'dynamicConfigFile' => PATH_BE_staticinfotables.'tca.php',
		'iconfile' => PATH_BE_staticinfotables_rel.'icon_static_languages.gif',
	),
	'interface' => array(
		'showRecordFieldList' => 'lg_name_local,lg_name_en,lg_iso_2,lg_typo3,lg_country_iso_2,lg_collate_locale,lg_sacred,lg_constructed'
	)
);

// Currency reference data from ISO 4217
$TCA['static_currencies'] = array(
	'ctrl' => array(
		'label' => 'cu_name_en',
		'label_alt' => 'cu_name_en,cu_iso_3',
		'readOnly' => 1,
		'adminOnly' => 1,
		'rootLevel' => 1,
		'is_static' => 1,
		'default_sortby' => 'ORDER BY cu_name_en',
		'title' => 'LLL:EXT:'.STATIC_INFO_TABLES_EXTkey.'/locallang_db.xml:static_currencies.title',
		'dynamicConfigFile' => PATH_BE_staticinfotables.'tca.php',
		'iconfile' => PATH_BE_staticinfotables_rel.'icon_static_currencies.gif',
	),
	'interface' => array(
		'showRecordFieldList' => 'cu_iso_3,cu_iso_nr,cu_name_en,cu_symbol_left,cu_symbol_right,cu_thousands_point,cu_decimal_point,cu_decimal_digits,cu_sub_name_en,cu_sub_divisor,cu_sub_symbol_left,cu_sub_symbol_right'
	)
);

$TCA['static_countries']['ctrl']['readOnly'] = 0;
$TCA['static_languages']['ctrl']['readOnly'] = 0;
$TCA['static_country_zones']['ctrl']['readOnly'] = 0;
$TCA['static_currencies']['ctrl']['readOnly'] = 0;
$TCA['static_territories']['ctrl']['readOnly'] = 0;


// ******************************************************************
// sys_language
// ******************************************************************

t3lib_div::loadTCA('sys_language');
$TCA['sys_language']['columns']['static_lang_isocode']['config'] = array(
	'type' => 'select',
	'items' => array(
		array('',0),
	),
	#'foreign_table' => 'static_languages',
	#'foreign_table_where' => 'AND static_languages.pid=0 ORDER BY static_languages.lg_name_en',
	'itemsProcFunc' => 'tx_staticinfotables_div->selectItemsTCA',
	'itemsProcFunc_config' => array(
		'table' => 'static_languages',
		'indexField' => 'uid',
		// I think that will make more sense in the future
		// 'indexField' => 'lg_iso_2',
		'prependHotlist' => 1,
		//	defaults:
		//'hotlistLimit' => 8,
		//'hotlistSort' => 1,
		//'hotlistOnly' => 0,
		//'hotlistApp' => TYPO3_MODE,
	),
	'size' => 1,
	'minitems' => 0,
	'maxitems' => 1,
);

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:'.STATIC_INFO_TABLES_EXTkey.'/class.tx_staticinfotables_syslanguage.php:&tx_staticinfotables_syslanguage';


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
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'extensions/Acronym/skin/images/acronym.gif',
		)
	);

	t3lib_extMgm::allowTableOnStandardPages('tx_rtehtmlarea_acronym');


###########################
## EXTENSION: templavoila
## FILE:      /Applications/MAMP/htdocs/typo3conf/ext/templavoila/ext_tables.php
###########################

$_EXTKEY = 'templavoila';
$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];


# TYPO3 CVS ID: $Id: ext_tables.php 26531 2009-11-14 15:37:41Z tolleiv $
if (!defined ('TYPO3_MODE'))  die ('Access denied.');

// unserializing the configuration so we can use it here:
$_EXTCONF = unserialize($_EXTCONF);

if (TYPO3_MODE=='BE') {

		// Adding click menu item:
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = array(
		'name' => 'tx_templavoila_cm1',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_templavoila_cm1.php'
	);
	include_once(t3lib_extMgm::extPath('templavoila').'class.tx_templavoila_handlestaticdatastructures.php');

		// Adding backend modules:
	t3lib_extMgm::addModule('web','txtemplavoilaM1','top',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
	t3lib_extMgm::addModule('web','txtemplavoilaM2','',t3lib_extMgm::extPath($_EXTKEY).'mod2/');

		// Remove default Page module (layout) manually if wanted:
	if (!$_EXTCONF['enable.']['oldPageModule']) {
		$tmp = $GLOBALS['TBE_MODULES']['web'];
		$GLOBALS['TBE_MODULES']['web'] = str_replace (',,',',',str_replace ('layout','',$tmp));
		unset ($GLOBALS['TBE_MODULES']['_PATHS']['web_layout']);
	}

		// Registering CSH:
	t3lib_extMgm::addLLrefForTCAdescr('be_groups','EXT:templavoila/locallang_csh_begr.xml');
	t3lib_extMgm::addLLrefForTCAdescr('pages','EXT:templavoila/locallang_csh_pages.xml');
	t3lib_extMgm::addLLrefForTCAdescr('tt_content','EXT:templavoila/locallang_csh_ttc.xml');
	t3lib_extMgm::addLLrefForTCAdescr('tx_templavoila_datastructure','EXT:templavoila/locallang_csh_ds.xml');
	t3lib_extMgm::addLLrefForTCAdescr('tx_templavoila_tmplobj','EXT:templavoila/locallang_csh_to.xml');
	t3lib_extMgm::addLLrefForTCAdescr('xMOD_tx_templavoila','EXT:templavoila/locallang_csh_module.xml');
	t3lib_extMgm::addLLrefForTCAdescr('xEXT_templavoila','EXT:templavoila/locallang_csh_intro.xml');
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_web_txtemplavoilaM1','EXT:templavoila/locallang_csh_pm.xml');


	t3lib_extMgm::insertModuleFunction(
		'tools_txextdevevalM1',
		'tx_templavoila_extdeveval',
		t3lib_extMgm::extPath($_EXTKEY).'class.tx_templavoila_extdeveval.php',
		'TemplaVoila L10N Mode Conversion Tool'
	);
}

	// Adding tables:
$TCA['tx_templavoila_tmplobj'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:templavoila/locallang_db.xml:tx_templavoila_tmplobj',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_to.gif',
		'selicon_field' => 'previewicon',
		'selicon_field_path' => 'uploads/tx_templavoila',
		'type' => 'parent',
		'versioningWS' => TRUE,
		'origUid' => 't3_origuid',
		'shadowColumnsForNewPlaceholders' => 'title,datastructure,rendertype,sys_language_uid,parent,rendertype_ref',
	)
);
$TCA['tx_templavoila_datastructure'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:templavoila/locallang_db.xml:tx_templavoila_datastructure',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_ds.gif',
		'selicon_field' => 'previewicon',
		'selicon_field_path' => 'uploads/tx_templavoila',
		'versioningWS' => TRUE,
		'origUid' => 't3_origuid',
		'shadowColumnsForNewPlaceholders' => 'scope,title',
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_templavoila_datastructure');
t3lib_extMgm::allowTableOnStandardPages('tx_templavoila_tmplobj');


	// Adding access list to be_groups
t3lib_div::loadTCA('be_groups');
$tempColumns = array (
	'tx_templavoila_access' => array(
		'label' => 'LLL:EXT:templavoila/locallang_db.xml:be_groups.tx_templavoila_access',
		'config' => Array (
			'type' => 'group',
			'internal_type' => 'db',
			'allowed' => 'tx_templavoila_datastructure,tx_templavoila_tmplobj',
			'prepend_tname' => 1,
			'size' => 5,
			'autoSizeMax' => 15,
			'multiple' => 1,
			'minitems' => 0,
			'maxitems' => 1000,
			'show_thumbs'=> 1,
		),
	)
);
t3lib_extMgm::addTCAcolumns('be_groups', $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes('be_groups','tx_templavoila_access;;;;1-1-1', '1');

	// Adding the new content element, "Flexible Content":
t3lib_div::loadTCA('tt_content');
$tempColumns = array(
	'tx_templavoila_ds' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:templavoila/locallang_db.xml:tt_content.tx_templavoila_ds',
		'config' => Array (
			'type' => 'select',
			'items' => Array (
				Array('',0),
			),
			'allowNonIdValues' => 1,
			'itemsProcFunc' => 'tx_templavoila_handleStaticdatastructures->dataSourceItemsProcFunc',
			'size' => 1,
			'minitems' => 0,
			'maxitems' => 1,
		)
	),
	'tx_templavoila_to' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:templavoila/locallang_db.xml:tt_content.tx_templavoila_to',
		'displayCond' => 'FIELD:CType:=:' . $_EXTKEY . '_pi1',
		'config' => Array (
			'type' => 'select',
			'items' => Array (
				Array('',0),
			),
			'itemsProcFunc' => 'tx_templavoila_handleStaticdatastructures->templateObjectItemsProcFunc',
			'size' => 1,
			'minitems' => 0,
			'maxitems' => 1,
		)
	),
	'tx_templavoila_flex' => Array (
		'l10n_cat' => 'text',
		'exclude' => 1,
		'label' => 'LLL:EXT:templavoila/locallang_db.xml:tt_content.tx_templavoila_flex',
		'displayCond' => 'FIELD:tx_templavoila_ds:REQ:true',
		'config' => Array (
			'type' => 'flex',
			'ds_pointerField' => 'tx_templavoila_ds',
			'ds_tableField' => 'tx_templavoila_datastructure:dataprot',
		)
	),
	'tx_templavoila_pito' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:templavoila/locallang_db.xml:tt_content.tx_templavoila_pito',
		'config' => Array (
			'type' => 'select',
			'items' => Array (
				Array('',0),
			),
			'itemsProcFunc' => 'tx_templavoila_handleStaticdatastructures->pi_templates',
			'size' => 1,
			'minitems' => 0,
			'maxitems' => 1,
		)
	),
);
t3lib_extMgm::addTCAcolumns('tt_content', $tempColumns, 1);

$TCA['tt_content']['ctrl']['typeicons'][$_EXTKEY . '_pi1'] = t3lib_extMgm::extRelPath($_EXTKEY) . '/icon_fce_ce.png';
t3lib_extMgm::addPlugin(array('LLL:EXT:templavoila/locallang_db.xml:tt_content.CType_pi1', $_EXTKEY . '_pi1', 'EXT:' . $_EXTKEY . '/icon_fce_ce.png'), 'CType');

if ($_EXTCONF['enable.']['selectDataSource']) {
	$TCA['tt_content']['types'][$_EXTKEY . '_pi1']['showitem'] = 'CType;;4;button;1-1-1, header;;3;;2-2-2,tx_templavoila_ds,tx_templavoila_to,tx_templavoila_flex;;;;2-2-2, hidden;;1;;3-3-3';
	if ($TCA['tt_content']['ctrl']['requestUpdate'] != '') {
		$TCA['tt_content']['ctrl']['requestUpdate'] .= ',';
	}
	$TCA['tt_content']['ctrl']['requestUpdate'] .= 'tx_templavoila_ds';
}
else {
	$TCA['tt_content']['types'][$_EXTKEY . '_pi1']['showitem'] = 'CType;;4;button;1-1-1, header;;3;;2-2-2,tx_templavoila_to,tx_templavoila_flex;;;;2-2-2, hidden;;1;;3-3-3';
}

	// For pages:
$tempColumns = array (
	'tx_templavoila_ds' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:templavoila/locallang_db.xml:pages.tx_templavoila_ds',
		'config' => array (
			'type' => 'select',
			'items' => Array (
				array('',0),
			),
			'allowNonIdValues' => 1,
			'itemsProcFunc' => 'tx_templavoila_handleStaticdatastructures->dataSourceItemsProcFunc',
			'size' => 1,
			'minitems' => 0,
			'maxitems' => 1,
			'suppress_icons' => 'IF_VALUE_FALSE',
		)
	),
	'tx_templavoila_to' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:templavoila/locallang_db.xml:pages.tx_templavoila_to',
		'displayCond' => 'FIELD:tx_templavoila_ds:REQ:true',
		'config' => Array (
			'type' => 'select',
			'items' => Array (
				Array('',0),
			),
			'itemsProcFunc' => 'tx_templavoila_handleStaticdatastructures->templateObjectItemsProcFunc',
			'size' => 1,
			'minitems' => 0,
			'maxitems' => 1,
		)
	),
	'tx_templavoila_next_ds' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:templavoila/locallang_db.xml:pages.tx_templavoila_next_ds',
		'config' => Array (
			'type' => 'select',
			'items' => Array (
				Array('',0),
			),
			'allowNonIdValues' => 1,
			'itemsProcFunc' => 'tx_templavoila_handleStaticdatastructures->dataSourceItemsProcFunc',
			'size' => 1,
			'minitems' => 0,
			'maxitems' => 1,
			'suppress_icons' => 'IF_VALUE_FALSE',
		)
	),
	'tx_templavoila_next_to' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:templavoila/locallang_db.xml:pages.tx_templavoila_next_to',
		'displayCond' => 'FIELD:tx_templavoila_next_ds:REQ:true',
		'config' => Array (
			'type' => 'select',
			'items' => Array (
				Array('',0),
			),
			'itemsProcFunc' => 'tx_templavoila_handleStaticdatastructures->templateObjectItemsProcFunc',
			'size' => 1,
			'minitems' => 0,
			'maxitems' => 1,
		)
	),
	'tx_templavoila_flex' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:templavoila/locallang_db.xml:pages.tx_templavoila_flex',
		'config' => Array (
			'type' => 'flex',
			'ds_pointerField' => 'tx_templavoila_ds',
			'ds_pointerField_searchParent' => 'pid',
			'ds_pointerField_searchParent_subField' => 'tx_templavoila_next_ds',
			'ds_tableField' => 'tx_templavoila_datastructure:dataprot',
		)
	),
);
t3lib_extMgm::addTCAcolumns('pages', $tempColumns, 1);
if ($_EXTCONF['enable.']['selectDataSource']) {
	t3lib_extMgm::addToAllTCAtypes('pages','tx_templavoila_ds;;;;1-1-1,tx_templavoila_to,tx_templavoila_nextds;;;;1-1-1,tx_templavoila_next_to,tx_templavoila_flex;;;;1-1-1');
	if ($TCA['pages']['ctrl']['requestUpdate'] != '') {
		$TCA['pages']['ctrl']['requestUpdate'] .= ',';
	}
	$TCA['pages']['ctrl']['requestUpdate'] .= 'tx_templavoila_ds,tx_templavoila_next_ds';
}
else {
	t3lib_extMgm::addToAllTCAtypes('pages','tx_templavoila_to;;;;1-1-1,tx_templavoila_next_to;;;;1-1-1,tx_templavoila_flex;;;;1-1-1');
	unset($TCA['pages']['columns']['tx_templavoila_to']['displayCond']);
	unset($TCA['pages']['columns']['tx_templavoila_next_to']['displayCond']);
}

	// Configure the referencing wizard to be used in the web_func module:
if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_func',
		'tx_templavoila_referenceElementsWizard',
		t3lib_extMgm::extPath($_EXTKEY).'func_wizards/class.tx_templavoila_referenceelementswizard.php',
		'LLL:EXT:templavoila/locallang.xml:wiz_refElements',
		'wiz'
	);
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_web_func','EXT:wizard_crpages/locallang_csh.xml');
}

?>
