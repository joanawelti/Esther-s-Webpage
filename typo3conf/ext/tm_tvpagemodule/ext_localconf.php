<?php
if (!defined ("TYPO3_MODE"))     die ("Access denied.");

/********************* Typo3 version test ends *********************************/
	
if(!$tm_tvpagemodule && !$tm_tvpagemoduleConf) { // if tm_shared_lib installed before tm_tvpagemodule these have been set
	$tm_tvpagemoduleConf=unserialize($TYPO3_CONF_VARS['EXT']['extConf']['tm_tvpagemodule']);
	$tm_tvpagemodule=1;
}
if(!$tm_shared_lib)  { // these configurations need tm_shared_lib installed before tm_tvpagemodule
	$tm_tvpagemoduleConf['enable.']['changedBEForms']=false;
	$tm_tvpagemoduleConf['enable.']['userSetup']=false;
	if(!$tm_contentaccess && !$tm_contentaccessConf &&t3lib_extMgm::isLoaded('tm_contentaccess')) {
		$tm_contentaccessConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['tm_contentaccess']);
		$tm_contentaccess=1;
		}	
	}

/********************* XCLASS-extensions starts *******************************/

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/index.php']=t3lib_extMgm::extPath($_EXTKEY) . 'class.ux_tx_templavoila_module1.php';
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/class.tx_templavoila_mod1_clipboard.php']=t3lib_extMgm::extPath($_EXTKEY) . 'class.ux_tx_templavoila_mod1_clipboard.php';
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/class.tx_templavoila_mod1_wizards.php']=t3lib_extMgm::extPath($_EXTKEY) . 'class.ux_tx_templavoila_mod1_wizards.php';
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/class.tx_templavoila_mod1_sidebar.php']=t3lib_extMgm::extPath($_EXTKEY) . 'class.ux_tx_templavoila_mod1_sidebar.php';
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/db_new_content_el.php']=t3lib_extMgm::extPath($_EXTKEY) . 'class.ux_tx_templavoila_dbnewcontentel.php';
	
/********************* XCLASS-extensions ends**********************************/
?>
