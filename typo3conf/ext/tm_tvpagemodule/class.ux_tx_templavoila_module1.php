<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2006 Robert Lemke (robert@typo3.org)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
 * Module 'Page' for the 'templavoila' extension.
 *
 * @author		Robert Lemke <robert@typo3.org>
 * @coauthor		Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author		Tapio Markula
 * @author		Marc Ehret <me@kenx.de>
 * @author		Stephen Galinski
 * @package		TYPO3
 * @subpackage		tx_templavoila
*/
/**
* Modifications made by Tapio Markula has much a customer project related features
*/

require_once(t3lib_extMgm::extPath('tm_tvpagemodule').'class.tx_tmtvpagemoduleCreateLinks.php');
// Added: variable for getting $_GET information
if(!$typo3GET)
	$typo3GET=t3lib_div::_GET();

/** enable options and/or loaded extension related global conditions starts ***/

if($changedBEForms)
	require_once(t3lib_extMgm::extPath('tm_shared_lib').'class.tx_tmsharedlib_specialfunctions.php');

if ($tm_contentaccess && $tm_contentaccessConf['enable.']['extra_be_icons']) // adds some new be icons
	$extraBEIcons=1;

if($tm_tvpagemoduleConf['enable.']['me_templavoilalayout']) // enables functionality of 'me_templavoilalayout'
	$me_templavoilalayout=1;

if($tm_tvpagemoduleConf['enable.']['sg_templavoilalayout'])// enables funtionality of Stefan Galinski
	$sg_templavoilalayout=1;

/** enable options and/or loaded extension related global conditions ends *****/

/*************************** class starts *************************************/

class ux_tx_templavoila_module1 extends tx_templavoila_module1 {

	// Added new class variables
	var $toolBar=''; // extra toolbar - relates with a customer project
	var $paste = ''; // paste link, which has been used in several places
	var $newCont = ''; // new content element link, which has been used in several places
	var $max=0; // relate with move up/down button to check the quantity of elements in a content area
	var $table=''; // table info - either 'pages' or 'tt_content'
	var $beTemplate = ''; // relate with me_templavoilalayout by Marc Ehret <me@kenx.de>
	var $flagRenderBeLayout = FALSE; // relate with me_templavoilalayout by Marc Ehret <me@kenx.de>
	var $userCookieData=array();
	var $Top='0'; // positioning of some tabs
	var $disableEditViewEditingButtons=array();
	var $enableEditViewEditingButtons=array();
	var $modSharedTSconfig=array();
	var $PageTSConfig=array();
	var $calcPerms;
	var $rootLine=false; // testing, if for shortcut pages should be set
	var $limitToRootlines=false;
	var $defaultorder= 'edit,move,hide,unlink,make_local,copy,cut,ref'; // default order of action links
	var $linkSet=array(); // used temporarily for getting 'copy,cut,ref' links
	var $shortCut='';
	var $tabbar='';
	var $wizard=''; // wizard text
	var $markers=array();
	var $docheaderButtons=array();


	/*******************************************
	 *
	 * Initialization functions
	 *
	 *******************************************/

	/**
	 * Initialisation of this backend module
	 *
	 * @return	void
	 * @access public
	 */
	function init() {
		parent::init();
		global $BE_USER,$typo3GET,$changedBEForms; // Added for the added funtion 'publish', which has been used in a customer project
			
		if($typo3GET['publish'] && $changedBEForms) // executes publish function, if publish button has been pressed
			tx_tmsharedlib_specialfunctions::publish('tv');
		if(array_key_exists('editIconModeTemplaVoila',$BE_USER->uc) && $BE_USER->uc['editIconModeTemplaVoila']!='' && !$this->modTSconfig['properties']['disableUserSetup'])
			$this->modTSconfig['properties']['editIconModeTemplaVoila'] = $BE_USER->uc['editIconModeTemplaVoila'];

		$typesForDisableEditViewEditingButtons = t3lib_div::trimExplode(',',strtolower($this->modTSconfig['properties']['disableEditViewEditingButtons']),1);

		if($this->modTSconfig['properties']['disableEditViewEditingButtons']) {
			$typesForDisableEditViewEditingButtons = t3lib_div::trimExplode(',',strtolower($this->modTSconfig['properties']['disableEditViewEditingButtons']),1);
			if(is_array($typesForDisableEditViewEditingButtons))
				$this->disableEditViewEditingButtons = array_flip($typesForDisableEditViewEditingButtons);
			$typesForDisableEditViewEditingButtons=NULL;
			}
		if($this->modTSconfig['properties']['enableEditViewEditingButtons']) {
			$typesForEnableEditViewEditingButtons = t3lib_div::trimExplode(',',strtolower($this->modTSconfig['properties']['enableEditViewEditingButtons']),1);
			if(is_array($typesForEnableEditViewEditingButtons))
				$this->enableEditViewEditingButtons = array_flip($typesForEnableEditViewEditingButtons);
			$typesForEnableEditViewEditingButtons=NULL;
			}

			// Added: possiblity to show hidden elements using TS Config for users/user groups - overrides default behaviout
		if(isset($this->modTSconfig['properties']['tt_content_showHidden']))
			$this->MOD_SETTINGS['tt_content_showHidden']=$this->modTSconfig['properties']['tt_content_showHidden'];
		if (!isset($this->modTSconfig['properties']['sideBarEnable']))
			$this->modTSconfig['properties']['sideBarEnable'] = 1;

		$this->PageTSConfig=t3lib_BEfunc::getPagesTSconfig($typo3GET['id']);

		// Added :confitions in order to accect showing content for page type 'shortcut' (4)
		// testing if doktype 'shortcut' can have content
		if(isset($this->modTSconfig['properties']['limitToRootlines'])) { // limit to some branches
			$idValues = t3lib_div::trimExplode(',',$this->modTSconfig['properties']['limitToRootlines'],1); // converts comma separated list as an array
			$this->limitToRootlines=true;
			$this->rootline=false;
			$countlimitToRootlinesValues=count($idValues);

			for($i=0; $i< $countlimitToRootlinesValues;$i++) { // tests if set page or its parent exists in rootline
				$countRootLineItems=count(t3lib_BEfunc::BEgetRootLine($idValues[$i])); // quantity for the loop
				$rootLineItem=t3lib_BEfunc::BEgetRootLine($idValues[$i]); // rootline of the defined page
				$rootLineItemThis=t3lib_BEfunc::BEgetRootLine($typo3GET['id']); // rootline of the current page as comparison
				for($j=0;$j<$countRootLineItems;$j++) {
					if($rootLineItem[$j]['uid']==$idValues[$i] && ($rootLineItem[$j]['uid']==$rootLineItemThis[$j]['uid'])) { // check rootline and check against with the rootline of the current page - must match with the branch of the current page
						$this->rootline=true;
						break; // if a matching page has been found, stop going remaining values
						}
					}
				}
			}
		else	$this->rootline=true; // if rootline check is not needed, this variable must be set to true
		}



	/**
	 * Preparing menu content and initializing clipboard and module TSconfig
	 *
	 * @return	void
	 * @access public
	 */
	function menuConfig() {
		global $TYPO3_CONF_VARS,$typo3GET,$changedBEForms; // Added: needed to check if this file has been used as wizard

			// Prepare array of sys_language uids for available translations:
		$this->translatedLanguagesArr = $this->getAvailableLanguages($this->id);
		$translatedLanguagesUids = array();
		foreach ($this->translatedLanguagesArr as $languageRecord) {
			$translatedLanguagesUids[$languageRecord['uid']] = $languageRecord['title'];
		}

		$this->MOD_MENU = array(
			'tt_content_showHidden' => 1,
			'showOutline' => 1,
			'language' => $translatedLanguagesUids,
			'clip_parentPos' => '',
			'clip' => '',
			'langDisplayMode' => '',
			'recordsView_table' => '', // new in 1.3.
			'recordsView_start' => '', // new in 1.3.
		);
			// Hook: menuConfig_preProcessModMenu
		$menuHooks = $this->hooks_prepareObjectsArray('menuConfigClass');
		foreach ($menuHooks as $hookObj) {
 				if (method_exists ($hookObj, 'menuConfig_preProcessModMenu')) {
 					$hookObj->menuConfig_preProcessModMenu ($this->MOD_MENU, $this);
 				}
 			}

			// page/be_user TSconfig settings and blinding of menu-items
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id,'mod.'.$this->MCONF['name']);
		$this->MOD_MENU['view'] = t3lib_BEfunc::unsetMenuItems($this->modTSconfig['properties'],$this->MOD_MENU['view'],'menu.function');

		if (!isset($this->modTSconfig['properties']['sideBarEnable']))
			$this->modTSconfig['properties']['sideBarEnable'] = 1;
		$doktype = $GLOBALS['TYPO3_DB']->exec_SELECTquery('doktype','pages','uid='.$this->id.' AND deleted=0','', '', '');
		$doktype = $GLOBALS['TYPO3_DB']->sql_fetch_row($doktype);
		$doktype = intval($doktype[0]);
		$showDoktype=0;
		SWITCH($doktype) {
			CASE 3: // external url
			#CASE 7: // mount point
			CASE 199: // spacer
			CASE 254: // sys folder
			#CASE 255: // recycler
			break;
			CASE 4:	// shortcut
			if(isset($this->modTSconfig['properties']['allowContentForShortcut']) && $this->rootline) {
				if($this->modTSconfig['properties']['allowContentForShortcut'])
					$showDoktype=1;
				}
			elseif(!isset($this->limitToRootlines) && isset($this->PageTSConfig['mod.']['web_txtemplavoilaM1.']['allowContentForShortcut'])) {
				if($this->PageTSConfig['mod.']['web_txtemplavoilaM1.']['allowContentForShortcut'])
					$showDoktype=1;
				}
			break;
			default:
			$showDoktype=1;
			}
		if($typo3GET['wizard'] || !$showDoktype) // Added: no sidebar if this file has been used as a wizard or for sys folder pages
			$this->modTSconfig['properties']['sideBarEnable'] = 0;

		$this->modSharedTSconfig = t3lib_BEfunc::getModTSconfig($this->id, 'mod.SHARED');
		if(!$changedBEForms) // if not enabled tabBar for backend forms not use neither for this module
			$this->modSharedTSconfig['properties']['extraTabBar']=false;

			// CLEANSE SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name']);
	}

	/*******************************************
	 *
	 * Main functions
	 *
	 *******************************************/

	/**
	 * Main function of the module.
	 *
	 * @return	void
	 * @access public
	 */
	function main() {
		global $BE_USER,$LANG,$BACK_PATH,$extraBEIcons,$typo3GET,$changedBEForms; // Added: needed to check if this file has been used as wizard
		$this->menuConfig();
		
		if (!is_callable(array('t3lib_div', 'int_from_ver')) || t3lib_div::int_from_ver(TYPO3_version) < 4000000) {
			$this->content = 'Fatal error:This version of TemplaVoila does not work with TYPO3 versions lower than 4.0.0! Please upgrade your TYPO3 core installation.';
			return;
		}
			// Access check! The page will show only if there is a valid page and if this page may be viewed by the user
		if (is_array($this->altRoot))	{
			$access = true;
		} else {
			$pageInfoArr = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
			$access = (intval($pageInfoArr['uid'] > 0));
		}
		// Added: If this file has been used as content element wizard, new headers
		if($typo3GET['wizard']) {
			$this->wizard = '<div class="mainHeader"><h2 class="mainHeader">'.$GLOBALS['LANG']->sL('LLL:EXT:templavoila/mod1/locallang.php:createnewcontent_newContentElement').'</h2></div>';
			$this->wizard .= '<h3>'.$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:createnewcontent_selectContentArea').'</h3>';
			}
		
		$cmd = t3lib_div::_GP ('cmd');	
		$body = ''; // main body of the page
		
		if(TYPO3_branch >= 4.2)	// initialize possible markers
			$this->markers = array(
			'CSH' => '',
			'STAT' => '', // hook
			'EXTRA_SPACER'=>'',
			'TABBAR'=>'',
			'CONTENT' => '',
			);	
			
		if ($access)    {

			$render_editPageScreen = true; // New in 1.1.1 compared with 1.0.1.		
			
			$this->calcPerms = $GLOBALS['BE_USER']->calcPerms($pageInfoArr);// new in 1.3.2
			// Define the root element record: - New in 1.1.1 compared with 1.0.1
			$this->rootElementTable = is_array($this->altRoot) ? $this->altRoot['table'] : 'pages';
			$this->rootElementUid = is_array($this->altRoot) ? $this->altRoot['uid'] : $this->id;
			$this->rootElementRecord = t3lib_BEfunc::getRecordWSOL($this->rootElementTable, $this->rootElementUid, '*');
			$this->rootElementUid_pidForContent = $this->rootElementRecord['t3ver_swapmode']==0 && $this->rootElementRecord['_ORIG_uid'] ? $this->rootElementRecord['_ORIG_uid'] : $this->rootElementRecord['uid'];
			
			if ($this->rootElementTable == 'pages') {
					// Initialize the special doktype class:
				$specialDoktypesObj =& t3lib_div::getUserObj ('&tx_templavoila_mod1_specialdoktypes','');
				$specialDoktypesObj->init($this);
				$methodName = 'renderDoktype_'.$this->rootElementRecord['doktype'];
				}
			
				// Check if we have to update the pagetree:
			if (t3lib_div::_GP('updatePageTree')) {
				t3lib_BEfunc::getSetUpdateSignal('updatePageTree');
			}

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('noDoc');
			$this->doc->docType= 'xhtml_trans';
			$this->doc->backPath = $BACK_PATH;
			$this->doc->divClass = '';
					
			$this->docheaderButtons=$this->getButtons();
			$this->doc->form.='<form class="editActions" style="margin:0 !important;padding:0 !important" name="editActions" action="'.htmlspecialchars('index.php?'.$this->link_getParameters()).'" method="post">';
			
				// Added: function jumpToUrl(), which is needed for the SELECT-menus. Also, the id in the parent frameset is configured.
				// Added: JavaScript for higlighting content area (function menuFix() and function addEvent())  because MS IE doesn't suppor :hover for DIV elements.
			$this->doc->JScode .= $this->doc->wrapScriptTags('
	function jumpToUrl(URL)	{ //
		document.location = URL;
		return false;
	}
	if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
		' . $this->doc->redirectUrls() . '
	function jumpToUrl(URL)	{
		window.location.href = URL;
		return false;
	}
	function jumpExt(URL,anchor)	{	//
		var anc = anchor?anchor:"";
		window.location.href = URL+(T3_THIS_LOCATION?"&returnUrl="+T3_THIS_LOCATION:"")+anc;
		return false;
	}
	function jumpSelf(URL)	{	//
		window.location.href = URL+(T3_RETURN_URL?"&returnUrl="+T3_RETURN_URL:"");
		return false;
	}

	function setHighlight(id)	{	//
		top.fsMod.recentIds["web"]=id;
		top.fsMod.navFrameHighlightedID["web"]="pages"+id+"_"+top.fsMod.currentBank;	// For highlighting

		if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav)	{
			top.content.nav_frame.refresh_nav();
		}
	}

	function editRecords(table,idList,addParams,CBflag)	{	//
		window.location.href="'.$BACK_PATH.'alt_doc.php?returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).
			'&edit["+table+"]["+idList+"]=edit"+addParams;
	}
	function editList(table,idList)	{	//
		var list="";

			// Checking how many is checked, how many is not
		var pointer=0;
		var pos = idList.indexOf(",");
		while (pos!=-1)	{
			if (cbValue(table+"|"+idList.substr(pointer,pos-pointer))) {
				list+=idList.substr(pointer,pos-pointer)+",";
			}
			pointer=pos+1;
			pos = idList.indexOf(",",pointer);
		}
		if (cbValue(table+"|"+idList.substr(pointer))) {
			list+=idList.substr(pointer)+",";
		}

		return list ? list : idList;
	}
	// JavaScript for Drop Down Menu based on Son of Suckerfish Drop Down Menu ends
			');

				// Set up JS for dynamic tab menu and side bar
			$this->doc->JScode .= $this->doc->getDynTabMenuJScode();
			
			// Added: if this file has been used as content element wizard, no language info
			if($typo3GET['wizard']) {
				$this->doc->JScode .='
<style type="text/css">
	/*<![CDATA[*/
	.lrPadding { display:none; }
				';
				if($this->modTSconfig['properties']['newTarget'])
					$this->doc->JScode .='
	.contentElementWizardTableContainer {
		height:330px;
		overflow:scroll;
	}
					';	
				$this->doc->JScode .='
	/*]]>*/
</style>';
				}
				$this->doc->JScode .='
<style type="text/css">
	/*<![CDATA[*/
					';	
				if(TYPO3_branch >= 4.2)
					$this->doc->JScode .='
	html { overflow:hidden ; }
					';
				if (t3lib_extMgm::isLoaded('t3skin')) 
				// Fix padding for t3skin in disabled tabs
					$this->doc->JScode .= '
	table.typo3-dyntabmenu td.disabled, table.typo3-dyntabmenu td.disabled_over, table.typo3-dyntabmenu td.disabled:hover { padding-left: 10px; }
					';
				if ($this->sideBarObj->position == 'left' && $this->modTSconfig['properties']['sideBarEnable'])
					$this->doc->JScode .= '
	#tx_templavoila_mod1_sidebar-bar { 
		width:auto !important; 
		background-color:transparent;           
	}
	#tx_templavoila_mod1_sidebar-bar .tab, #tx_templavoila_mod1_sidebar-bar .tabact,
	#tx_templavoila_mod1_sidebar-bar .disabled { white-space:nowrap; }
					';				
				$this->doc->JScode .='
	/*]]>*/
</style>';
			// Added: check if this pages have been used as wizard - no sidebar then
			$this->doc->JScode .= ($this->modTSconfig['properties']['sideBarEnable'] && !$typo3GET['wizard']) ? $this->sideBarObj->getJScode() : '';

				// Setting up support for context menus (when clicking the items icon)
			$CMparts = $this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
			$this->doc->JScode.= $CMparts[0];
			$this->doc->postCode.= $CMparts[2];

			$this->handleIncomingCommands();// adds the default language //  or an unknown flag - that looked bad, taken off
			if (isset($this->modSharedTSconfig['properties']['defaultLanguageLabel'])) {
				$this->allAvailableLanguages[0]['title'] = $this->modSharedTSconfig['properties']['defaultLanguageLabel'];
				$this->allAvailableLanguages[0]['flagIcon'] = $this->doc->backPath . 'gfx/flags/' . $this->modSharedTSconfig['properties']['defaultLanguageFlag'];
				}
			#else	$this->allAvailableLanguages[0]['flagIcon'] = $this->doc->backPath . 'gfx/flags/unknown.gif';

				// Start creating HTML output
			$start = $this->doc->startPage($LANG->getLL('title'));

			
			if(TYPO3_branch < 4.2)
				$body .= $this->wizard;	

				// Show message if the page is of a special doktype:
			if ($this->rootElementTable == 'pages') {
				if (method_exists($specialDoktypesObj, $methodName) || $this->rootElementRecord['doktype']==199) {					
					$this->markers['EXTRA_SPACER']='<div style="height:65px"></div>';
					if($this->rootElementRecord['doktype']==199)
						$result = $this->renderDoktype_199($this->rootElementRecord);
					else	$result = $specialDoktypesObj->$methodName($this->rootElementRecord);
					if ($result !== FALSE) {
						$body .= $result;
						// rootline check is inside the init-function
						if(isset($this->modTSconfig['properties']['allowContentForShortcut']) && $this->rootline) {
							if(!($this->modTSconfig['properties']['allowContentForShortcut'] && $this->rootElementRecord['doktype']==4))
								$render_editPageScreen = false; // New in 1.1.1 compared with 1.0.1 -! Do not output editing code for special doctypes!
							}
						elseif(!isset($this->limitToRootlines) && isset($PageTSConfig['mod.']['web_txtemplavoilaM1.']['allowContentForShortcut'])) {
							if(!($this->PageTSConfig['mod.']['web_txtemplavoilaM1.']['allowContentForShortcut'] && $this->rootElementRecord['doktype']==4))
								$render_editPageScreen = false;
							}
						else	{
							if($extraBEIcons)
								$imageName = 'edit2Page.gif'; // as default the same as in the folder /extra-icons/
							else	$imageName ='edit2.gif';
							$render_editPageScreen = false;
							
							// this has been added to 1.3
							if ($GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'edit')) {									
								$body .= '<div class="editPageProperties"><strong class="editPageProperties">'.$this->link_edit('<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/'.$imageName,'').' title="'.htmlspecialchars($LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:editPage')).'" alt="" style="text-align: center; vertical-align: middle; border:0;" />'.$LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:editPage'),'pages',$this->id).'</strong></div>';
								}
							}
						}
					}
					
				}
				// normal rendering
			if ($render_editPageScreen) { // new in 1.1.1 compared with 1.0.1
					// Render "edit current page" (important to do before calling ->sideBarObj->render() - otherwise the translation tab is not rendered!
				$editCurrentPageHTML = $this->render_editPageScreen();
				
					// Hook for adding new sidebars or removing existing - new in 1.3.2
				$sideBarHooks = $this->hooks_prepareObjectsArray('sideBarClass');
					foreach ($sideBarHooks as $hookObj)	{
						if (method_exists($hookObj, 'main_alterSideBar')) {
							$hookObj->main_alterSideBar($this->sideBarObj, $this);
						}
					}

				// Show the "edit current page" screen along with the sidebar
			$this->shortCut = ($BE_USER->mayMakeShortcut() ? $this->doc->makeShortcutIcon('id,altRoot',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']) : '');
			if(TYPO3_branch < 4.2)
				$shortCut='<br />'.$this->shortCut;
			if ($this->sideBarObj->position == 'left' && $this->modTSconfig['properties']['sideBarEnable']) {
				$marginTop=0;
				if(TYPO3_branch < 4.2)
					$marginTop+=22;
				else	$marginTop+=10;
				if(isset($this->enableEditViewEditingButtons['new_page_extra']))
					$marginTop += 20;
				if(TYPO3_branch < 4.2 && $this->modSharedTSconfig['properties']['extraTabBar'] && !$typo3GET['wizard'])
					$marginTop += 20;
				if(TYPO3_branch < 4.2 && $this->MOD_SETTINGS['showOutline'])
					$marginTop -= 20; // take off the space, which is normal reserved for icon menu
				#if(TYPO3_branch < 4.2)
					$padding='padding-top:20px;';
				$body .=' 
					<div id="topspacer1" style="margin-top:'.$marginTop.'px"></div>
					<table cellspacing="0" cellpadding="0" class="typo3-sidebar-left">
						<tr valign="top">
							<td style="vertical-align:top;position:relative;z-index:2;" class="sidebar-obj">
							'.$this->sideBarObj->render().'
							</td>
							<td style="vertical-align:top;position:relative;z-index:1;'.$padding.'" class="sidebar-shortcut">
							'.$editCurrentPageHTML.$shortCut.'
							</td>
						</tr>
					</table>
				';				
				}
			else	{
				$marginTop=0;
				if(TYPO3_branch < 4.2)
					$marginTop+=22;
				if(isset($this->enableEditViewEditingButtons['new_page_extra']))
					$marginTop += 20;
				if(TYPO3_branch < 4.2 && $this->modSharedTSconfig['properties']['extraTabBar'] && !$typo3GET['wizard'])
					$marginTop += 20;
				if(TYPO3_branch < 4.2 && $this->MOD_SETTINGS['showOutline'])
					$marginTop -= 20; // take off the space, which is normal reserved for icon menu
				$sideBarTop = $this->modTSconfig['properties']['sideBarEnable']  && ($this->sideBarObj->position == 'toprows' || $this->sideBarObj->position == 'toptabs') ? $this->sideBarObj->render() : '';
				if(TYPO3_branch >= 4.2) {
					$body .= '
					<div id="sidebartopcontainer">';	
					$endWrap ='
					</div>
					';
					}
				$body .= '<div style="margin-top:'.$marginTop.'px;" class="sideBarTop">'.$sideBarTop.'</div>'.$endWrap;
				$body .= $editCurrentPageHTML.$shortCut;				
				}
			}

		} else {	// No access or no current page uid:
			if(TYPO3_branch >= 4.2)
				$this->doc = t3lib_div::makeInstance('template');
			else $this->doc = t3lib_div::makeInstance('mediumDoc');
			
			$this->doc->docType= 'xhtml_trans';
			$this->doc->backPath = $BACK_PATH;					
			$this->docheaderButtons=$this->getButtons();	
			
			$this->markers['EXTRA_SPACER']='<div style="height:65px"></div>';
			if(TYPO3_branch >= 4.2)
					$this->doc->JScode .='
<style type="text/css">
/*<![CDATA[*/
	body#ext-templavoila-mod1-index-php {
	overflow: hidden; 
	height: 100%; 
}	
/*]]>*/
</style>';
			$start = $this->doc->startPage($LANG->getLL('title'));
			switch ($cmd) {
					// Create a new page
				case 'crPage' :
						// Output the page creation form
					$body .= $this->wizardsObj->renderWizard_createNewPage (t3lib_div::_GP ('positionPid'));					
					break;

					// If no access or if ID == zero
				default:
					$body.=$this->doc->header($LANG->getLL('title'));
					$body.=$LANG->getLL('default_introduction');
					
			}
		}
			// start page
		$this->content = $start;
			// generate main content
		if(TYPO3_branch >= 4.2) {
			$this->doc->setModuleTemplate(t3lib_extMgm::extRelPath('tm_tvpagemodule').'htmlTemplates/tv_layout.html');
			if ($this->doc->moduleTemplate == '') {
				$this->doc->setModuleTemplate('../' . t3lib_extMgm::extRelPath('tm_tvpagemodule').'htmlTemplates/tv_layout.html');
			}
			$pageinfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
			$this->markers['CSH']=$this->docheaderButtons['csh'];
			$this->markers['CONTENT']=$body;
			$this->content.= $this->doc->moduleBody($pageinfo, $this->docheaderButtons, $this->markers);
		}
		else	$this->content .= $body;
			// end page 
		$this->content .= $this->doc->endPage();
	}                                                     


	/********************************************
	 *
	 * Rendering functions
	 *
	 ********************************************/

	/**
	 * Displays the default view of a page, showing the nested structure of elements.
	 *
	 * @return	string		The modules content
	 * @access protected
	 */
	function render_editPageScreen() {
		global $LANG, $BE_USER, $TYPO3_CONF_VARS,$typo3GET; // Added: '$typo3GET' for checking if this file has been used as content element wizard

		$output = '';

			// Fetch the content structure of page:
		$contentTreeData = $this->apiObj->getContentTree($this->rootElementTable, $this->rootElementRecord);

			// Set internal variable which registers all used content elements:
		$this->global_tt_content_elementRegister = $contentTreeData['contentElementUsage'];

			// Setting localization mode for root element:
		$this->rootElementLangMode = $contentTreeData['tree']['ds_meta']['langDisable'] ? 'disable' : ($contentTreeData['tree']['ds_meta']['langChildren'] ? 'inheritance' : 'separate');
		$this->rootElementLangParadigm = ($this->modTSconfig['properties']['translationParadigm'] == 'free') ? 'free' : 'bound';

			// Create a back button if neccessary:
		if (is_array ($this->altRoot)) {
			$output .= '<div style="text-align:right; width:100%; margin-bottom:5px;" class="backButtonContainer"><a href="index.php?id='.$this->id.'"><img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/goback.gif','').' title="'.htmlspecialchars($LANG->getLL ('goback')).'" alt="" /></a></div>';
		}

			// Add the localization module if localization is enabled:
		if ($this->alternativeLanguagesDefined()) {
			$this->localizationObj =& t3lib_div::getUserObj ('&tx_templavoila_mod1_localization','');
			$this->localizationObj->init($this);
		}

			// Hook for content at the very top (fx. a toolbar):
		if (is_array ($TYPO3_CONF_VARS['EXTCONF']['templavoila']['mod1']['renderTopToolbar'])) {
			foreach ($TYPO3_CONF_VARS['EXTCONF']['templavoila']['mod1']['renderTopToolbar'] as $_funcRef) {
				$_params = array ();
				$output .= t3lib_div::callUserFunction($_funcRef, $_params, $this);
			}
		}

			// Display the content as outline or the nested page structure:
		if ($BE_USER->isAdmin() && $this->MOD_SETTINGS['showOutline']) {			
			if(TYPO3_branch < 4.2 && $this->modSharedTSconfig['properties']['extraTabBar'] && !$typo3GET['wizard']) 
				$output .= tx_tmsharedlib_specialfunctions::getTabMenu('tv');
			$output .= $this->render_outline($contentTreeData['tree']);
		} else {			
			$output.= $this->render_framework_allSheets($contentTreeData['tree'], $this->currentLanguageKey,$array,$array,'page');			
		}
		// See http://bugs.typo3.org/view.php?id=4821 - new in 1.3.2
		$renderHooks = $this->hooks_prepareObjectsArray('render_editPageScreen');
			foreach ($renderHooks as $hookObj)	{
				if (method_exists ($hookObj, 'render_editPageScreen_addContent')) {
					$output .= $hookObj->render_editPageScreen_addContent($this);
				}
			}

		// Added: if this file has been used as content element wizard, file db_new.php will be rendered in a IFRAME
		if($typo3GET['wizard'] && $this->modTSconfig['properties']['newTarget'])
			$output .='
<iframe name="wizard" href="/typo3/close.html;" border="0" frameborder="0" style="border-style:none; width:100%; height:600px" id="WizardIFrame">
</frame>
		';
		if(TYPO3_branch < 4.2 && !$this->modTSconfig['properties']['disableCSH'] && !$typo3GET['wizard']) // Added: possible to hide content sensitive help link for the page module
			$output .= t3lib_BEfunc::cshItem('_MOD_web_txtemplavoilaM1', '', $this->doc->backPath,'<hr/>|'.$LANG->getLL('csh_whatisthetemplavoilapagemodule', 1));

		return $output;
	}





	/*******************************************
	 *
	 * Framework rendering functions
	 *
	 *******************************************/

	/**
	 * Rendering the sheet tabs if applicable for the content Tree Array
	 *
	 * this function has not changed but seems that if this function is not in this XCLASS, the function doesn't work
	 *
	 * @param	array		$contentTreeArr: DataStructure info array (the whole tree)
	 * @param	string		$languageKey: language key for the display
	 * @param	array		$parentPointer: flexform Pointer to parent element
	 * @param	array		$parentDsMeta: meta array from parent DS (passing information about parent containers localization mode)
	 * @param	string		$recType: tests, if record type relates page or content records.
	 * @return	string		HTML
	 * @access protected
	 * @see	render_framework_singleSheet()
	 */
	function render_framework_allSheets($contentTreeArr, $languageKey='DEF', $parentPointer=array(), $parentDsMeta=array(),$recType='content') {

		// If more than one sheet is available, render a dynamic sheet tab menu, otherwise just render the single sheet framework
		if (is_array($contentTreeArr['sub']) && (count($contentTreeArr['sub'])>1 || !isset($contentTreeArr['sub']['sDEF'])))	{
			$parts = array();

			foreach(array_keys($contentTreeArr['sub']) as $sheetKey)	{

				$this->containedElementsPointer++;
				$this->containedElements[$this->containedElementsPointer] = 0;
				$frContent = $this->render_framework_singleSheet($contentTreeArr, $languageKey, $sheetKey, $parentPointer, $parentDsMeta,'subContent');

				$parts[] = array(
					'label' => ($contentTreeArr['meta'][$sheetKey]['title'] ? $contentTreeArr['meta'][$sheetKey]['title'] : $sheetKey),	#.' ['.$this->containedElements[$this->containedElementsPointer].']',
					'description' => $contentTreeArr['meta'][$sheetKey]['description'],
					'linkTitle' => $contentTreeArr['meta'][$sheetKey]['short'],
					'content' => $frContent,
				);

				$this->containedElementsPointer--;
				}
			return $this->doc->getDynTabMenu($parts,'TEMPLAVOILA:pagemodule:'.$this->apiObj->flexform_getStringFromPointer($parentPointer));
			}
		else	return $this->render_framework_singleSheet($contentTreeArr, $languageKey, 'sDEF', $parentPointer, $parentDsMeta,$recType);
	}

	/**
	 * Renders the display framework of a single sheet. Calls itself recursively
	 *
	 * @param	array		$contentTreeArr: DataStructure info array (the whole tree)
	 * @param	string		$languageKey: language key for the display
	 * @param	string		$sheet: the sheet key of the sheet which should be rendered
	 * @param	array		$parentPointer: flexform pointer to parent element
	 * @param	array		$parentDsMeta: meta array from parent DS (passing information about parent containers localization mode)
	 * @param	string		$recType: tests, if record type relates page or content records.
	 * @return	string		HTML
	 * @access protected
	 * @see	render_framework_singleSheet()
	 */
	function render_framework_singleSheet($contentTreeArr, $languageKey, $sheet, $parentPointer=array(), $parentDsMeta=array(),$recType='content') {
		global $BE_USER,$LANG,$TYPO3_CONF_VARS,$extraBEIcons,$typo3GET; // Added: '$typo3GET' for checking if this file has been used as content element wizard

		// Added: added for disabling or enabling links - some links use anable feature, because they have made for a customer project
		$elementBelongsToCurrentPage = $contentTreeArr['el']['table'] == 'pages' || $contentTreeArr['el']['pid'] == $this->rootElementUid_pidForContent;

		$this->canEditPage=$canEditPage = $GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'edit'); // new in 1.3.2
		$canEditContent = $GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'editcontent');// new in 1.3.2

			// Prepare the record icon including a content sensitive menu link wrapped around it:
		if(!$typo3GET['wizard'] && !$typo3GET['templaVoilaFE']) {
			$wrapRecordIconStart='<span class="contentIcon">';
			$wrapRecordIconEnd='</span>';
			}			
		
		$menuCommands = array();// new in 1.3.2
		if ($GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'new')) {
			$menuCommands[] = 'new';
			}
		if ($canEditContent) {
			$menuCommands[] = 'copy,cut,pasteinto,pasteafter,delete';
			}
		else 	{
			$menuCommands[] = 'copy';
			}


		$recordIcon = $wrapRecordIconStart.'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,$contentTreeArr['el']['icon'],'').' style="text-align: center; vertical-align: middle;" border="0" title="'.htmlspecialchars('['.$contentTreeArr['el']['table'].':'.$contentTreeArr['el']['uid'].']').'" alt="" />'.$wrapRecordIconEnd;

			// docheader button to the top of the page
		if($contentTreeArr['el']['table'] == 'pages') 
			$this->docheaderButtons=$this->getButtons($contentTreeArr);
		
		// these has been defined later
		#$titleBarLeftButtons = $this->translatorMode ? $recordIcon : (count($menuCommands) == 0 ? $recordIcon : $this->doc->wrapClickMenuOnIcon($recordIcon,$contentTreeArr['el']['table'], $contentTreeArr['el']['uid'], 1,'&amp;callingScriptId='.rawurlencode($this->doc->scriptID), implode(',', $menuCommands)));
		#$titleBarLeftButtons.= $this->getRecordStatHookValue($contentTreeArr['el']['table'],$contentTreeArr['el']['uid']);
		
			// Prepare table specific settings:
		switch ($contentTreeArr['el']['table']) {

			case 'pages' :		
					// possible contextual menu
				if(!$typo3GET['wizard'] && !$typo3GET['templaVoilaFE']) 
					$contextmenu = $this->translatorMode || !$canEditPage ? $recordIcon : $this->doc->wrapClickMenuOnIcon($recordIcon,$contentTreeArr['el']['table'], $contentTreeArr['el']['uid'], 1,'&amp;callingScriptId='.rawurlencode($this->doc->scriptID), $menuCommands);
				$menuCommands=NULL; // unset menucommands
				$elementTitlebarColor = isset ($this->currentDataStructureArr['pages']['ROOT']['tx_templavoila']['pageModule']['titleBarColor']) ? $this->currentDataStructureArr['pages']['ROOT']['tx_templavoila']['pageModule']['titleBarColor'] : $this->doc->bgColor2;
				$titleBarLeftButtons = '';
				$titleBarRightButtons = '';	
				if($this->modSharedTSconfig['properties']['extraTabBar'] && !$typo3GET['wizard']) {
					$tabbar = tx_tmsharedlib_specialfunctions::getTabMenu('tv');
				 	if(TYPO3_branch >= 4.2)
						$tabbar ='
	<div id="tabbarcontainer" style="position:absolute;top:0px;left:0;width:100%;z-index:1;">'
	.$tabbar.'
	</div>
	<div style="height:20px;" id="tabspacer"></div>
					';
					}
				if(TYPO3_branch < 4.2) 
					$titleBarLeftButtons .= $tabbar;
				if(TYPO3_branch >= 4.2)
					$this->markers['STAT']=$this->getRecordStatHookValue($contentTreeArr['el']['table'],$contentTreeArr['el']['uid']);
				// Added: special toolbar (Contents - Properties - Preview) - relates with a customer project;
				if(!$typo3GET['wizard'] && !$typo3GET['templaVoilaFE']) { // Added: extra toolbar, extra links and default links hided, if this file has been used as content element wizard
					$positionTop=5;
					if(isset($this->enableEditViewEditingButtons['new_page_extra']))
						$positionTop=$positionTop+20;
					if($this->modSharedTSconfig['properties']['extraTabBar'])
						$positionTop=$positionTop+20;
					$style=' style="position:absolute; top:'.$positionTop.'px; left:10px;display:block; height:0 !important"';
					$titleBarLeftButtons .='<span class="titlebarLeftTopButtons"'.$style.'>';
					$titleBarLeftButtons .= $this->getRecordStatHookValue($contentTreeArr['el']['table'],$contentTreeArr['el']['uid']); 				
					$titleBarLeftButtons .= $contextmenu;
					# original: $titleBarLeftButtons .= $this->translatorMode || !$canEditPage ? '' : $this->link_edit('<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','').' title="'.htmlspecialchars($LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:editPage')).'" alt="" style="text-align: center; vertical-align: middle; border:0;" />',$contentTreeArr['el']['table'],$contentTreeArr['el']['uid']);
					if(!($this->translatorMode || !$canEditPage))
						$titleBarLeftButtons .= $this->docheaderButtons['edit_page'];
					$titleBarLeftButtons .= $this->docheaderButtons['view'];
					$titleBarLeftButtons .= $this->docheaderButtons['new_page'];
					$titleBarLeftButtons .= $this->docheaderButtons['new_record'];
					$titleBarLeftButtons .= $this->docheaderButtons['record_list'];					
				}
				else	$titleBarLeftButtons .= $recordIcon;
			break;

			case 'tt_content' :
				#$titleBarLeftButtons ='<div class="titlebarLeftContentButtons">';
				// contextual menu
				if($typo3GET['wizard'] || $typo3GET['templaVoilaFE'])
					$titleBarLeftButtons = $recordIcon;
				else	$titleBarLeftButtons = $this->translatorMode ? $recordIcon : (count($menuCommands) == 0 ? $recordIcon : $this->doc->wrapClickMenuOnIcon($recordIcon,$contentTreeArr['el']['table'], $contentTreeArr['el']['uid'], 1,'&amp;callingScriptId='.rawurlencode($this->doc->scriptID), implode(',', $menuCommands))); // changed in 1.3.2
				$menuCommands=NULL; // unset menucommands
				$titleBarLeftButtons.= $this->getRecordStatHookValue($contentTreeArr['el']['table'],$contentTreeArr['el']['uid']);					
				#$titleBarLeftButtons .='</div>';
				
				$elementTitlebarColor = ($elementBelongsToCurrentPage ? $this->doc->bgColor5 : $this->doc->bgColor6);
				#$elementTitlebarStyle = 'background-color: '.$elementTitlebarColor; // Disabled: colors have been set using a CSS file - no with direct color attributes or properties

				$languageUid = $contentTreeArr['el']['sys_language_uid'];
				
				if ($typo3GET['wizard']) // Added: $typo3GET['wizard'] - if this file has been used as content element wizard, this link set has been disabled
					$titleBarRightButtons = '';
				elseif ($this->translatorMode)
					$titleBarRightButtons = $this->clipboardObj->element_getSelectButtons($parentPointer, 'copy');
				elseif($canEditContent)	{
					// Create CE specific buttons:
					if(!isset($this->disableEditViewEditingButtons['make_local'])) {
						$link=tx_tvpagemoduleCreateLinks::greateLink($this->doc->backPath,t3lib_extMgm::extRelPath('templavoila').'mod1/makelocalcopy.gif','makeLocal',$LANG->getLL('makeLocal'),$LANG->getLL('makeLocal'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_makeLocal'),$this->modTSconfig['properties']['editIconModeTemplaVoila']);
						$linkSet['make_local'] = !$elementBelongsToCurrentPage ? $this->link_makeLocal($link, $parentPointer) : '';
						}

					if(!isset($this->disableEditViewEditingButtons['unlink'])) {
						if($this->modTSconfig['properties']['realDelete']) {
							$realdelete=TRUE;
							$title = $LANG->getLL('deleteRecord');
							$label = $GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang.php:p_delete');
							$labelLong = $GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_deleteRecord_long');
							}
						else	{
							$realdelete=FALSE;
							$title = $LANG->getLL('unlinkRecord');
							$label = $GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_unlinkRecord');
							$labelLong = $GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_unlinkRecord_long');
							}

						$link=tx_tvpagemoduleCreateLinks::greateLink($this->doc->backPath,'gfx/garbage.gif','unlink',$title,$labelLong,$label,$this->modTSconfig['properties']['editIconModeTemplaVoila']);
						$linkSet['unlink'] = $this->link_unlink($link, $parentPointer,$realdelete,'normal');
						}

					if(!isset($this->disableEditViewEditingButtons['edit']) && $GLOBALS['BE_USER']->recordEditAccessInternals('tt_content', $contentTreeArr['previewData']['fullRow'])) { // Added -new condition in 1.3.2
						$link=tx_tvpagemoduleCreateLinks::greateLink($this->doc->backPath,'gfx/edit2.gif','edit2',$LANG->getLL('editrecord'),$LANG->getLL ('editrecord'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_editrecord'),$this->modTSconfig['properties']['editIconModeTemplaVoila']);
						$linkSet['edit'] = ($elementBelongsToCurrentPage ? $this->link_edit($link,$contentTreeArr['el']['table'],$contentTreeArr['el']['uid']) : '');
						}

					// Added: move up/down links

					if(!isset($this->disableEditViewEditingButtons['move'])) {
						if($this->modTSconfig['properties']['editIconModeTemplaVoila']==6)
							$altImage=t3lib_extMgm::siteRelPath('tm_tvpagemodule').'gfx/button_up.gif';
						$link=tx_tvpagemoduleCreateLinks::greateLink($this->doc->backPath,'gfx/button_up.gif','up',$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_moveup'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_moveup'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_moveup_short'),$this->modTSconfig['properties']['editIconModeTemplaVoila'],$altImage);

						// Added: in a customer project the customer wanted move up/down as icons and other as SELECT menu
						if($parentPointer['position']!=1) {
							if($this->modTSconfig['properties']['editIconModeTemplaVoila']!=6)
								$linkSet['up'] = $this->moveUpDown($link,$parentPointer,'moveUp');
							if($this->modTSconfig['properties']['editIconModeTemplaVoila']==6)
								$move2 = '</td><td>'.$this->moveUpDown($link,$parentPointer,'moveUp');
							}

						if($this->modTSconfig['properties']['editIconModeTemplaVoila']==6)
							$altImage=t3lib_extMgm::siteRelPath('tm_tvpagemodule').'gfx/button_down.gif"';
						$link=tx_tvpagemoduleCreateLinks::greateLink($this->doc->backPath,'gfx/button_down.gif','down',$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_movedown'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_movedown'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_movedown_short'),$this->modTSconfig['properties']['editIconModeTemplaVoila'],$altImage);

						if($parentPointer['position']<$this->max) {
							if($this->modTSconfig['properties']['editIconModeTemplaVoila']!=6)
								$linkSet['down'] = $this->moveUpDown($link,$parentPointer,'moveDown');
							if($this->modTSconfig['properties']['editIconModeTemplaVoila']==6)
								$move2 .='</td><td>'.$this->moveUpDown($link,$parentPointer,'moveDown');
							}
						}
					$this->linkSet=$linkSet;
					$this->clipboardObj->element_getSelectButtons($parentPointer,'copy,cut,ref',1,true);
					$linkSet=$this->linkSet;

					if($this->modTSconfig['properties']['buttonOrder'])
						$allowOrder = t3lib_div::trimExplode(',',$this->modTSconfig['properties']['buttonOrder'],1);
					else	$allowOrder = t3lib_div::trimExplode(',',$this->defaultorder,1); // order of edit icons
					foreach($allowOrder as $item => $value) {
						if(array_key_exists($value,$linkSet))
							$links.= $linkSet[$value];
						elseif($value == 'move' && (isset($linkSet['up']) || isset($linkSet['down'])))
							$links.= $linkSet['up'].$linkSet['down'];
						}

					if($this->modTSconfig['properties']['editIconModeTemplaVoila']==5 || $this->modTSconfig['properties']['editIconModeTemplaVoila']==6) {
						$emptyOption='<option value="">'.$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_select').'</option>';
						$selectStart = '<select name="action'.$contentTreeArr['el']['uid'].'" onchange="if (this[this.selectedIndex].value)
document.location=this[this.selectedIndex].value">'; //	document.forms[\'editActions\'].submit();" onkeydown="document.forms[\'editActions\'].submit();
						$selectEnd = '</select>'; // $this->render_framework_subElements($contentTreeArr, $languageKey, $sheet).'
						}
					$titleBarRightButtons = '<table cellpadding="0" cellspacing="0" border="0" class="normalOptions"><tr valign="top"><td>'.$selectStart.$emptyOption.$links.$selectEnd.$move2.'</td></tr></table>';

					# NICE FOR DEBUG: # $titleBarRightButtons.= implode('/',$parentPointer).'UID:'.$contentTreeArr['el']['uid'].'/'.$contentTreeArr['el']['_ORIG_uid'].' PID:'.$contentTreeArr['el']['pid'];
				}
			break;
		}

			// Prepare the language icon:
		$languageLabel = htmlspecialchars ($this->allAvailableLanguages[$contentTreeArr['el']['sys_language_uid']]['title']);
		$languageIcon = $this->allAvailableLanguages[$languageUid]['flagIcon'] ? '<img class="langIcon" src="'.$this->allAvailableLanguages[$languageUid]['flagIcon'].'" title="'.$languageLabel.'" alt="'.$languageLabel.'" style="text-align: center; vertical-align: middle;" />' : ($languageLabel && $languageUid ? '['.$languageLabel.']' : '');

			// If there was a langauge icon and the language was not default or [all] and if that langauge is accessible for the user, then wrap the  flag with an edit link (to support the "Click the flag!" principle for translators)
		if ($languageIcon && $languageUid>0 && $GLOBALS['BE_USER']->checkLanguageAccess($languageUid) && $contentTreeArr['el']['table']==='tt_content')	{
			$languageIcon = $this->link_edit($languageIcon, 'tt_content', $contentTreeArr['el']['uid'], TRUE);
		}

			// Create warning messages if neccessary:
		$warnings = '';
		if ($this->global_tt_content_elementRegister[$contentTreeArr['el']['uid']] > 1 && $this->rootElementLangParadigm !='free') {
			$warnings .= '<br/>'.$this->doc->icons(2).' <em>'.htmlspecialchars(sprintf($LANG->getLL('warning_elementusedmorethanonce',''), $this->global_tt_content_elementRegister[$contentTreeArr['el']['uid']], $contentTreeArr['el']['uid'])).'</em>';
		}

			// Displaying warning for container content (in default sheet - a limitation) elements if localization is enabled:
		$isContainerEl = count($contentTreeArr['sub']['sDEF']);
		if (!$this->modTSconfig['properties']['disableContainerElementLocalizationWarning'] && $this->rootElementLangParadigm !='free' && $isContainerEl && $contentTreeArr['el']['table'] === 'tt_content' && $contentTreeArr['el']['CType'] === 'templavoila_pi1' && !$contentTreeArr['ds_meta']['langDisable'])	{
			if ($contentTreeArr['ds_meta']['langChildren'])	{
				if (!$this->modTSconfig['properties']['disableContainerElementLocalizationWarning_warningOnly']) {
					$warnings .= '<br/>'.$this->doc->icons(2).' <b>'.$LANG->getLL('warning_containerInheritance').'</b>';
				}
			} else {
				$warnings .= '<br/>'.$this->doc->icons(3).' <b>'.$LANG->getLL('warning_containerSeparate').'</b>';
			}
		}

			// Preview made:
		$previewContent = $this->render_previewData($contentTreeArr['previewData'], $contentTreeArr['el'], $contentTreeArr['ds_meta'], $languageKey, $sheet);

			// Wrap workspace notification colors:
		if ($contentTreeArr['el']['_ORIG_uid'])	{
			$previewContent = '<div class="ver-element">'.($previewContent ? $previewContent : '<em>[New version]</em>').'</div>';
		}

			// Finally assemble the table:
			// Changed: the generation of the table must be radically changed in a customer project - icons work in conventional way

		if(!$this->modTSconfig['properties']['editIconModeTemplaVoila']==0)
			$colSpan=' colspan="2"';

		if($recType=='page')
			$recTypeClass='Page';
		elseif($recType=='content')
			$recTypeClass='Content';
		elseif($recType=='subContent')
			$recTypeClass='SubContent typo3-page-ceHeaderContent ';


		if($this->modTSconfig['properties']['editIconModeTemplaVoila']==1 || $this->modTSconfig['properties']['editIconModeTemplaVoila']==6)
			$mode='textMode';
		elseif($this->modTSconfig['properties']['editIconModeTemplaVoila']==2)
			$mode='bothMode';
		elseif($this->modTSconfig['properties']['editIconModeTemplaVoila']==3)
			$mode='bothMode';
		elseif($this->modTSconfig['properties']['editIconModeTemplaVoila']==4)
			$mode='buttonMode';
		elseif($this->modTSconfig['properties']['editIconModeTemplaVoila']==5)
			$mode = 'option';
		else	$mode='iconMode';

		if(TYPO3_branch >= 4.2 && $recType=='page') {			
			$this->markers['TABBAR']=$tabbar.$this->wizard.$warnings;
			}
		else	$finalContent = '
		<table cellpadding="0" cellspacing="0" width="100%" class="typo3-page-ceHeader'.$recTypeClass.'">';
		
		if(TYPO3_branch < 4.2 || (TYPO3_branch >= 4.2 && $recType!='page')) {
			if((integer)$this->modTSconfig['properties']['editIconModeTemplaVoila']>4) {
				$finalContent .='
			<tr>';
				$finalContent .='<td class="bgColor4 titleBarRight titleBarRight'.$recTypeClass.'" colspan="2">';
				$finalContent .= $titleBarRightButtons;
				$finalContent .= '</td>
			</tr>';
				}
			$finalContent .='
			<tr>
				<td class="bgColor4 titleBarLeft nobr titleBarLeft'.$recTypeClass.'"'.$colSpan.'>'.
				$languageIcon;
			$finalContent .= $titleBarLeftButtons;
			$finalContent .= '<span class="leftbuttonset">'.($elementBelongsToCurrentPage?'':'<em>').htmlspecialchars($contentTreeArr['el']['title']).($elementBelongsToCurrentPage ? '' : '</em>').
								$warnings.
				'</span>';
				$finalContent .= '</td>';
				
			if((string)$this->modTSconfig['properties']['editIconModeTemplaVoila']==0) {
				$finalContent .= '
				<td class="bgColor4 titleBarRight titleBarRight'.$recTypeClass.' titleBarRight'.$recTypeClass.$mode.'" nowrap="nowrap" style="text-align:right;">';
				$finalContent .= $titleBarRightButtons;
				$finalContent .= '
				</td>';
				}
			else 	{
				$finalContent .='
			</tr>';
				if((integer)$this->modTSconfig['properties']['editIconModeTemplaVoila']>0 && (integer)$this->modTSconfig['properties']['editIconModeTemplaVoila']<5) {
					$finalContent .='
			<tr>';
					$finalContent .='
				<td class="bgColor4 titleBarRight titleBarRight'.$recTypeClass.' titleBarRight'.$recTypeClass.$mode.'" colspan="2">';
					$finalContent .= $titleBarRightButtons;
					$finalContent .= '
				</td>
			</tr>';
					}
				$finalContent .='
			<tr>
				<td class="bgColor5 titleBarRight titleBarRight'.$recTypeClass.' titleBarRight'.$recTypeClass.$mode.'" colspan="2">';
				$finalContent .= $this->render_framework_subElements($contentTreeArr, $languageKey, $sheet);
				$finalContent .= '
					</td>';
				}
				$finalContent .='
			</tr>
			<tr>
				<td colspan="2" class="bgColor4">';
			if((string)$this->modTSconfig['properties']['editIconModeTemplaVoila']==0)
				$finalContent .= $this->render_framework_subElements($contentTreeArr, $languageKey, $sheet);
			}
		elseif(TYPO3_branch >= 4.2)
			$finalContent .= $this->render_framework_subElements($contentTreeArr, $languageKey, $sheet);
		$finalContent .= $previewContent.$this->render_localizationInfoTable($contentTreeArr, $parentPointer, $parentDsMeta);
		
		if(TYPO3_branch < 4.2 || (TYPO3_branch >= 4.2 && $recType!='page'))
			$finalContent .= '</td>
			</tr>
		</table>
		';			
		return $finalContent;
	}
                                 
	/**
	 * Renders the sub elements of the given elementContentTree array. This function basically
	 * renders the "new" and "paste" buttons for the parent element and then traverses through
	 * the sub elements (if any exist). The sub element's (preview-) content will be rendered
	 * by render_framework_singleSheet().
	 *
	 * Calls render_framework_allSheets() and therefore generates a recursion.
	 *
	 * @param	array		$elementContentTreeArr: Content tree starting with the element which possibly has sub elements
	 * @param	string		$languageKey: Language key for current display
	 * @param	string		$sheet: Key of the sheet we want to render
	 * @return	string		HTML output (a table) of the sub elements and some "insert new" and "paste" buttons
	 * @access protected
	 * @see render_framework_allSheets(), render_framework_singleSheet()
	 */
	function render_framework_subElements($elementContentTreeArr, $languageKey, $sheet) {
		global $LANG,$BE_USER,$typo3GET,$me_templavoilalayout,$sg_templavoilalayout,$tm_shared_lib; // Added: '$typo3GET' for checking if this file has been used as content element wizard
		$this->init();

			// Define l/v keys for current language:

		$langChildren = intval($elementContentTreeArr['ds_meta']['langChildren']);
		$langDisable = intval($elementContentTreeArr['ds_meta']['langDisable']);

		$lKey = $langDisable ? 'lDEF' : ($langChildren ? 'lDEF' : 'l'.$languageKey);
		$vKey = $langDisable ? 'vDEF' : ($langChildren ? 'v'.$languageKey : 'vDEF');

		if (!is_array($elementContentTreeArr['sub'][$sheet]) || !is_array($elementContentTreeArr['sub'][$sheet][$lKey])) return '';

		$output = '';
		$cells = array();
		$headerCells = array();
		$count=0;

//--------- sg_templavoilalayout -------------------------------------------
		if($sg_templavoilalayout) { // && !$typo3GET['wizard']
			// get/set layout
			$contentElements = 0;
			$elLayoutAllWrap = $elementContentTreeArr['ds_meta']['elLayoutAllWrap'];
			$elLayoutContentWrap = $elementContentTreeArr['ds_meta']['elLayoutContentWrap'];
			$elLayoutContent = $elementContentTreeArr['ds_meta']['elLayoutContent'];
			if(empty($elLayoutAllWrap))
				$elLayoutAllWrap = '
	<table border="0" cellpadding="2" cellspacing="2" width="100%">
		<tr>
			<td>
			<table width="96%;"> ###content### </table>
			</td>
		</tr>
	</table>';
			if(empty($elLayoutContentWrap))
				$elLayoutContentWrap = '
			<td valign="top" style="border: 1px solid #666666; ###avgWidth###">
					###field_generic###
			</td>';
			if(empty($elLayoutContent))
				$elLayoutContent = '
	<h3 class="ElHeader">###header###</h3>
	<div class="ElContent bgColor3" style="padding: 5px;">###content###</div>';
			}
//--------- sg_templavoilalayout -------------------------------------------


//--------- me_templavoilalayout -------------------------------------------
		// gets the layout - added to 1.3
		if($me_templavoilalayout) {
			$this->beTemplate = $elementContentTreeArr['ds_meta']['beLayout'];
					// no layout, no special rendering
			$this->flagRenderBeLayout = $this->beTemplate? TRUE : FALSE;
			}
//----------- me_templavoilalayout -----------------------------------------


		// Traverse container fields:
		foreach($elementContentTreeArr['sub'][$sheet][$lKey] as $fieldID => $fieldValuesContent)	{

			if ($elementContentTreeArr['previewData']['sheets'][$sheet][$fieldID]['isMapped'] && is_array($fieldValuesContent[$vKey]))	{
				$fieldContent = $fieldValuesContent[$vKey];

				$cellContent = '';

					// Create flexform pointer pointing to "before the first sub element":
				$subElementPointer = array (
					'table' => $elementContentTreeArr['el']['table'],
					'uid' => $elementContentTreeArr['el']['uid'],
					'sheet' => $sheet,
					'sLang' => $lKey,
					'field' => $fieldID,
					'vLang' => $vKey,
					'position' => 0
				);

				$canCreateNew = $GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'new'); // new condition in 1.3.2
				if (!$this->translatorMode  && $canCreateNew)	{

					// "New" and "Paste" icon:
				$link=tx_tvpagemoduleCreateLinks::greateLink($this->doc->backPath,'gfx/new_el.gif','new',$LANG->getLL ('createnewrecord'),$LANG->getLL ('createnewrecord'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_createnewrecord'),$this->modTSconfig['properties']['editIconModeTemplaVoila'],'',true,false,false,'',true);

					// Added: conditions for different modes
					// Added: because of a request of a customer, added possibility to restrict creating more than one new content elements into an empty content area
				if(!$this->modTSconfig['properties']['oneContentEl'] || ($this->modTSconfig['properties']['oneContentEl'] && !is_array($fieldContent['el_list'])))  {
					$cellContent .='<table class="topLinkContainer" cellspacing="0" cellpadding="0"><tr valign="top"><td>';
					if($this->modTSconfig['properties']['editIconModeTemplaVoila']!=5) {
						$cellContent .= $this->link_new($link, $subElementPointer,'wizard');
						$cellContent .='</td><td>';
						if(!isset($this->disableEditViewEditingButtons['paste']) && !$typo3GET['wizard']) // if this page has been used as wizard, no paste buttons
							$cellContent .= $this->clipboardObj->element_getPasteButtons ($subElementPointer);
						}
					else	{
						$thisElementPointer=$subElementPointer;
						if(!isset($this->disableEditViewEditingButtons['new_text']))
							$this->newCont = $this->link_new($GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:new_text'), $thisElementPointer,'text');
						if(!isset($this->disableEditViewEditingButtons['new_img']))
							$this->newCont .= $this->link_new($GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:new_image'), $thisElementPointer,'image');
						if(!isset($this->disableEditViewEditingButtons['new_dyn']))
							$this->newCont .= $this->link_new($GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:new_dynelement'), $thisElementPointer,'dynelement');
						if(!isset($this->disableEditViewEditingButtons['new_wiz']))
							$this->newCont .= $this->link_new($GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:new_cont_wizard'), $thisElementPointer,'wizard');
						$this->paste =  $this->clipboardObj->element_getPasteButtons($thisElementPointer);

						if($typo3GET['wizard']) // if this page has been used as wizard, different first option
							$title=$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_select_new_wiz');
						else	$title=$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_select_new');
						$emptyOption='<option value="">'.$title.'</option>';
						$new = $this->newCont;
						if(!isset($this->disableEditViewEditingButtons['paste']) && !$typo3GET['wizard']) // if this page has been used as wizard, no paste buttons
							$paste = $this->paste;
						else	$paste='';

						if($typo3GET['wizard'] && $this->modTSconfig['properties']['newTarget']) // if this page has been used as wizard, frame target
							$location='wizard';
						else	$location='document';

						$selectStart = '<select  name="action'.$contentTreeArr['el']['uid'].'" onchange="if (this[this.selectedIndex].value)
		'.$location.'.location=this[this.selectedIndex].value">';
						$selectEnd = '</select>';
						$cellContent .= $selectStart.$emptyOption.$new . $paste.$selectEnd;
						}
					$cellContent .='</td></tr></table>';
					}
				// Added: for links after content elements
				if($this->modTSconfig['properties']['editIconModeTemplaVoila']==5)
					$thisElementPointer['position']++;

			}


					// Render the list of elements (and possibly call itself recursively if needed):

				if (is_array($fieldContent['el_list']))	 {
					$this->max=count($fieldContent['el_list']);

					foreach($fieldContent['el_list'] as $position => $subElementKey)	{

						$subElementArr = $fieldContent['el'][$subElementKey];

						if ((!$subElementArr['el']['isHidden'] || $this->MOD_SETTINGS['tt_content_showHidden']) && $this->displayElement($subElementArr))	{

								// When "onlyLocalized" display mode is set and an alternative language gets displayed
							if (($this->MOD_SETTINGS['langDisplayMode'] == 'onlyLocalized') && $this->currentLanguageUid>0)	{

									// Default language element. Subsitute displayed element with localized element
								if (($subElementArr['el']['sys_language_uid']==0) && is_array($subElementArr['localizationInfo'][$this->currentLanguageUid]) && ($localizedUid = $subElementArr['localizationInfo'][$this->currentLanguageUid]['localization_uid']))	{
									$localizedRecord = t3lib_BEfunc::getRecordWSOL('tt_content', $localizedUid, '*');
									$tree = $this->apiObj->getContentTree('tt_content', $localizedRecord);
									$subElementArr = $tree['tree'];
								}
							}
							$this->containedElements[$this->containedElementsPointer]++;

								// Modify the flexform pointer so it points to the position of the current sub element:
							$subElementPointer['position'] = $position;

							$cellContent .= $this->render_framework_allSheets($subElementArr, $languageKey, $subElementPointer, $elementContentTreeArr['ds_meta']);

							// hook adding additional information
							if (is_array ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TM_TVPAGEMODULE_EXTkey]['addItemInformationTV'])) {
								foreach  ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TM_TVPAGEMODULE_EXTkey]['addItemInformationTV'] as $classRef) {
									$hookObj= &t3lib_div::getUserObj($classRef);
									if (method_exists($hookObj, 'addItemInformationTV'))
										$cellContent .= $hookObj->addItemInformationTV($subElementArr, $languageKey, $subElementPointer, $elementContentTreeArr['ds_meta'],$this->modTSconfig);
									}
								}

							if (!$this->translatorMode && $canCreateNew)	{ // new condition in 1.3.2

									// "New" and "Paste"
								$link=tx_tvpagemoduleCreateLinks::greateLink($this->doc->backPath,'gfx/new_el.gif','new',$LANG->getLL ('createnewrecord'),$LANG->getLL ('createnewrecord'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_createnewrecord'),$this->modTSconfig['properties']['editIconModeTemplaVoila'],'',true,false,false,'',true);

								if(!$this->modTSconfig['properties']['oneContentEl']) {// creating new content element disabled if just one content element/ content area
									// Added: almost the same as links on top of each content area - look at explanations for links on the top of each content area
									$cellContent .='
										<table class="endLinkContainer" cellspacing="0" cellpadding="0"><tr valign="top"><td>'; // class name is different compared with links on the top of each content area
									if($this->modTSconfig['properties']['editIconModeTemplaVoila']!=5) {
										$cellContent .= $this->link_new($link, $subElementPointer,'wizard');
										if(!isset($this->disableEditViewEditingButtons['paste']) && !$typo3GET['wizard'])
											$cellContent .= '</td><td>'.$this->clipboardObj->element_getPasteButtons ($subElementPointer);
										}
									else	{
										$thisElementPointer=$subElementPointer;
										if(!isset($this->disableEditViewEditingButtons['new_text']))
											$this->newCont = $this->link_new($GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:new_text'), $thisElementPointer,'text');
										if(!isset($this->disableEditViewEditingButtons['new_img']))
											$this->newCont .= $this->link_new($GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:new_image'), $thisElementPointer,'image');
										if(!isset($this->disableEditViewEditingButtons['new_dyn']))
											$this->newCont .= $this->link_new($GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:new_dynelement'), $thisElementPointer,'dynelement');
										if(!isset($this->disableEditViewEditingButtons['new_wiz']))
											$this->newCont .= $this->link_new($GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:new_cont_wizard'), $thisElementPointer);
										$this->paste =  $this->clipboardObj->element_getPasteButtons($thisElementPointer);

										if($typo3GET['wizard'])
											$title=$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_select_new_wiz');
										else	$title=$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_select_new');
										$emptyOption='<option value="">'.$title.'</option>';
										$new = $this->newCont;
										if(!isset($this->disableEditViewEditingButtons['paste']) && !$typo3GET['wizard'])
											$paste = $this->paste;
										else	$paste='';
										$selectStart = '<select  name="action'.$contentTreeArr['el']['uid'].'" onchange="if (this[this.selectedIndex].value)
						document.location=this[this.selectedIndex].value">';
										$selectEnd = '</select>';
										$cellContent .= $selectStart.$emptyOption.$new . $paste.$selectEnd;
										}

									$cellContent .='</td></tr></table>';

									}
								}
							}
						$count++;
						}
					}

					// Added: link to take off all paste links - needs tm_shared_lib to be installed
				if($tm_shared_lib) {
					$tceform->clipObj = t3lib_div::makeInstance('t3lib_clipboard');
					$tceform->clipObj->initializeClipboard();	// Initialize - reads the clipboard content from the user session
					$elFromTable = $tceform->clipObj->elFromTable('tt_content'); 
					// clear content elements from clipboard
					if (count($elFromTable) && !isset($disableEditingButtons['clear_clipboard']) && !$typo3GET['wizard'])
						$clipboard = '<div class="clipboard">'.$tceform->clipObj->printClipboard2().'</div>';
					}

				//------------------- me_templavoilalayout --------------------------------

				if ($me_templavoilalayout && $this->flagRenderBeLayout==TRUE) {
					$beTemplateCell = '<table width="100%" class="beTemplateCell"><tr><td valign="top" style="background-color: '.$this->doc->bgColor4.'; padding-top:0; padding-bottom:0;">'.$LANG->sL($fieldContent['meta']['title'],1).'</td></tr><tr><td valign="top" style="padding: 5px;">'.$cellContent.'</td></tr></table>';
					$this->beTemplate = str_replace('###'.$fieldID.'###', $beTemplateCell, $this->beTemplate).$clipboard;
					}
				//-------------------- me_templavoilalayout -----------------------------------
				else	{
					// Add cell content to registers:

					//-------------------- sg_templavoilalayout -----------------------------------
					if($sg_templavoilalayout) {
						// cell title
						$cellTitle = $LANG->sL($fieldContent['meta']['title'], 1);
						$repValue = str_replace('###header###', $cellTitle,
						str_replace('###content###', $cellContent, $elLayoutContent));
						}
					//-------------------- sg_templavoilalayout -----------------------------------


					//-------------------- sg_templavoilalayout -----------------------------------
					if($sg_templavoilalayout) {
						if (empty($elementContentTreeArr['ds_meta']['elLayoutContentWrap'])) {
							$elAvgWidth = round(100 / count($elementContentTreeArr['sub'][$sheet][$lKey]));
							$output .= str_replace('###avgWidth###', 'width: ' . $elAvgWidth . '%;',
								str_replace('###field_generic###', $repValue, $elLayoutContentWrap));
							}
						else
							$elLayoutContentWrap = str_replace('###' . $fieldID . '###',
								$repValue, $elLayoutContentWrap);
						}
					//-------------------- sg_templavoilalayout -----------------------------------
					else	{
						// Added: added some class attributes
						$headerCells[]='<td valign="top" width="'.round(100/count($elementContentTreeArr['sub'][$sheet][$lKey])).'%" class="headerCell">'.$LANG->sL($fieldContent['meta']['title'],1).'</td>';
						$cells[]='<td class="otherCell" valign="top" width="'.round(100/count($elementContentTreeArr['sub'][$sheet][$lKey])).'%"><ins class="contentArea">'.$cellContent.'</ins></td>';
						}
					}


			}
		}

		//---------------- me_templavoilalayout -----------------------------------
		if($me_templavoilalayout && $this->flagRenderBeLayout==TRUE) {
			// removes not used markers
			// following line added
			$this->beTemplate = preg_replace('/###field_.+?###/i', '', $this->beTemplate);
			return $this->beTemplate;

			}
		//---------------- me_templavoilalayout -----------------------------------

		//---------------- sg_templavoilalayout -----------------------------------
		if($sg_templavoilalayout) {
			return str_replace('###content###', empty($output) ? $elLayoutContentWrap :
			'<tr>' . $output . '</tr>', $elLayoutAllWrap).$clipboard;
			}

		//---------------- sg_templavoilalayout -----------------------------------
			// Compile the content area for the current element (basically what was put together above):
		elseif (count ($headerCells) || count ($cells)) {

			// Added: id  and class for the table + container
			if($typo3GET['wizard'])
				$extraClass=' contentElementWizardTable contentElementWizardTableContainer';
			if(TYPO3_branch >= 4.2)
				$extraClass .=' T42_container';
			$output = '
			<div class="Container'.$extraClass.'">

				<table border="0" cellpadding="2" cellspacing="2" width="100%" class="'.$extraClass.'" id="contentTable">
					<tr>'.(count($headerCells) ? implode('', $headerCells) : '<td>&nbsp;</td>').'</tr>
					<tr>'.(count($cells) ? implode('', $cells) : '<td>&nbsp;</td>').'</tr>
				</table>
			</div>'.$clipboard;
		}
		return $output;

	}



	/*******************************************
	 *
	 * Rendering functions for certain subparts
	 *
	 *******************************************/



	/**
	 * Rendering the preview of content for Page module.
	 *
	 * @param	array		$previewData: Array with data from which a preview can be rendered.
	 * @param	array		$elData: Element data
	 * @param	array		$ds_meta: Data Structure Meta data
	 * @param	string		$languageKey: Current language key (so localized content can be shown)
	 * @param	string		$sheet: Sheet key
	 * @return	string		HTML content
	 */
	function render_previewData($previewData, $elData, $ds_meta, $languageKey, $sheet) {
		global $LANG;

			// General preview of the row:
		$previewContent = is_array($previewData['fullRow']) && $elData['table']=='tt_content' ? $this->render_previewContent($previewData['fullRow']) : '';

			// Preview of FlexForm content if any:
		if (is_array($previewData['sheets'][$sheet]))	{

				// Define l/v keys for current language:
			$langChildren = intval($ds_meta['langChildren']);
			$langDisable = intval($ds_meta['langDisable']);
			$lKey = $langDisable ? 'lDEF' : ($langChildren ? 'lDEF' : 'l'.$languageKey);
			$vKey = $langDisable ? 'vDEF' : ($langChildren ? 'v'.$languageKey : 'vDEF');

			foreach($previewData['sheets'][$sheet] as $fieldData)	{
				$TCEformsConfiguration = $fieldData['TCEforms']['config'];
				// after 1.1.1 this has been done with a new funtion - added
				#$TCEformsLabel = $LANG->sL($fieldData['TCEforms']['label'], 1); // title for non-section elements
				$TCEformsLabel = $this->localizedFFLabel($fieldData['TCEforms']['label'], 1);	// new in 1.3 title for non-section elements

				if ($fieldData['type']=='array')	{	// Making preview for array/section parts of a FlexForm structure:
					if (is_array($fieldData['subElements'][$lKey])) {
						if ($fieldData['section']) {
							foreach($fieldData['subElements'][$lKey] as $sectionData) {
								if(is_array($sectionData)) {
									$sectionFieldKey = key($sectionData);
									if (is_array ($sectionData[$sectionFieldKey]['el'])) {
										$previewContent .= '<ul>';
										// Added: span element with a class
										foreach ($sectionData[$sectionFieldKey]['el'] as $containerFieldKey => $containerData) {
											$previewContent .= '<span class="exampleContent"><li><strong>'.$containerFieldKey.'</strong> '.$this->link_edit(htmlspecialchars(t3lib_div::fixed_lgd_cs(strip_tags($containerData[$vKey]),200)), 'tt_content', $previewData['fullRow']['uid']).'</span></li>';
										}
										$previewContent .= '</ul>';
										}
									}
								}
						} else {
							// Added: span element with a class
			 				foreach ($fieldData['subElements'][$lKey] as $containerKey => $containerData) {
								$previewContent .= '<span class="exampleContent"><strong>'.$containerKey.'</strong>"'.$this->link_edit(htmlspecialchars(t3lib_div::fixed_lgd_cs(strip_tags($containerData[$vKey]),200)), 'tt_content', $previewData['fullRow']['uid']).'</span><br />';
							}
						}
					}
				} else {	// Preview of flexform fields on top-level:
					$fieldValue = $fieldData['data'][$lKey][$vKey];
					//  Changed in 1.1.1 compared with 1.0.1
					if ($TCEformsConfiguration['type'] == 'group') {
						if ($TCEformsConfiguration['internal_type'] == 'file')	{
							// Render preview for images:
							$thumbnail = t3lib_BEfunc::thumbCode (array('dummyFieldName'=> $fieldValue), '', 'dummyFieldName', $this->doc->backPath, '', $TCEformsConfiguration['uploadfolder']);
							$previewContent .= '<strong>'.$TCEformsLabel.'</strong> '.$thumbnail.'<br />';
						}
					} else if ($TCEformsConfiguration['type'] != '') {
						// Render for everything else: - a small fix in 1.3 (added check if empty)
						$previewContent .= '<span class="exampleContent"><strong>'.$TCEformsLabel.'</strong> '. (!$fieldValue ? '' : $this->link_edit(htmlspecialchars(t3lib_div::fixed_lgd_cs(strip_tags($fieldValue),200)), 'tt_content', $previewData['fullRow']['uid'])).'</span><br />';

					}
				}
			}
		}

		return $previewContent;
	}

	/**
	 * Returns an HTMLized preview of a certain content element. If you'd like to register a new content type, you can easily use the hook
	 * provided at the beginning of the function.
	 *
	 * @param	array		$row: The row of tt_content containing the content element record.
	 * @return	string		HTML preview content
	 * @access protected
	 * @see		getContentTree(), render_localizationInfoTable()
	 */
	function render_previewContent($row) {
		global $TYPO3_CONF_VARS, $LANG,$typo3GET; // Added: Added if this page has been used as content element wizard

		$hookObjectsArr = $this->hooks_prepareObjectsArray ('renderPreviewContentClass');
		$alreadyRendered = FALSE;
		$output = '';

			// Hook: renderPreviewContent_preProcess. Set 'alreadyRendered' to true if you provided a preview content for the current cType !
		reset($hookObjectsArr);
		while (list(,$hookObj) = each($hookObjectsArr)) {
			if (method_exists ($hookObj, 'renderPreviewContent_preProcess')) {
				$output .= $hookObj->renderPreviewContent_preProcess ($row, 'tt_content', $alreadyRendered, $this);
			}
		}

		if (!$alreadyRendered && !$typo3GET['wizard']) { // Added: no rendering if this page has been used as content element wizard
				// Added: some params for edit links
				// span element and class for example content
				// Preview content for non-flexible content elements:
			switch($row['CType'])	{
				case 'text':		//	Text
				case 'table':		//	Table
				case 'mailform':	//	Form
					$output = '<span class="exampleContent exampleContent_'.$row['CType'].'">' . $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','bodytext'),1).'</strong> '.htmlspecialchars(t3lib_div::fixed_lgd_cs(trim(strip_tags($row['bodytext'])),2000)),'tt_content',$row['uid'],$forced=FALSE,$type='normal').'</span><br />';
 					break;
				case 'image':		//	Image
					$output = '<span class="exampleContent exampleContent_'.$row['CType'].'">' . $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','image'),1).'</strong><br /> ', 'tt_content', $row['uid'],$forced=FALSE,$type='normal').t3lib_BEfunc::thumbCode ($row, 'tt_content', 'image', $this->doc->backPath).'</span><br />';
 					break;
				case 'textpic':		//	Text w/image
				case 'splash':		//	Textbox
					$thumbnail = '<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','image'),1).'</strong><br />';
					$thumbnail .= t3lib_BEfunc::thumbCode($row, 'tt_content', 'image', $this->doc->backPath);
					$text = '<span class="exampleContent exampleContent_'.$row['CType'].'">' . $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','bodytext'),1).'</strong> '.htmlspecialchars(t3lib_div::fixed_lgd_cs(trim(strip_tags($row['bodytext'])),2000)),'tt_content',$row['uid'],$forced=FALSE,$type='normal').'</span>';
 					$output='<table><tr><td valign="top">'.$text.'</td><td valign="top">'.$thumbnail.'</td></tr></table>'.'<br />';
					break;
				case 'bullets':		//	Bullets
					$htmlBullets = '';
					$bulletsArr = explode ("\n", t3lib_div::fixed_lgd_cs($row['bodytext'],2000));
					if (is_array ($bulletsArr)) {
						foreach ($bulletsArr as $listItem) {
							$htmlBullets .= htmlspecialchars(trim(strip_tags($listItem))).'<br />';
						}
					}
					$output = '<span class="exampleContent exampleContent_'.$row['CType'].'">' . $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','bodytext'),1).'</strong><br />'.$htmlBullets, 'tt_content', $row['uid'],$forced=FALSE,$type='normal').'</span><br />';
 					break;
				case 'uploads':		//	Filelinks
					$output = '<span class="exampleContent exampleContent_'.$row['CType'].'">' . $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','media'),1).'</strong><br />'.str_replace (',','<br />',htmlspecialchars(t3lib_div::fixed_lgd_cs(trim(strip_tags($row['media'])),2000))), 'tt_content', $row['uid'],$forced=FALSE,$type='normal').'</span><br />';
 					break;
				case 'multimedia':	//	Multimedia
					$output = '<span class="exampleContent exampleContent_'.$row['CType'].'">' . $this->link_edit ('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','multimedia'),1).'</strong><br />' . str_replace (',','<br />',htmlspecialchars(t3lib_div::fixed_lgd_cs(trim(strip_tags($row['multimedia'])),2000))), 'tt_content', $row['uid'],$forced=FALSE,$type='normal').'</span><br />';
 					break;
				case 'menu':		//	Menu / Sitemap
					$output = '<span class="exampleContent exampleContent_'.$row['CType'].'">' . $this->link_edit ('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','menu_type')).'</strong> '.$LANG->sL(t3lib_BEfunc::getLabelFromItemlist('tt_content','menu_type',$row['menu_type']),$forced=FALSE,$type='normal').'</span><br />'.
 						'<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','pages')).'</strong> '.$row['pages'], 'tt_content', $row['uid']).'<br />';
					break;
				case 'list':		//	Insert Plugin - in 1.3 fixed a small bug (menu_type - list_type
					$extraInfo = $this->render_previewContent_extraPluginInfo($row); // new after 1.3.1
					$output = '<span class="exampleContent exampleContent_'.$row['CType'].'">' . $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','list_type')).'</strong> ' . htmlspecialchars($LANG->sL(t3lib_BEfunc::getLabelFromItemlist('tt_content','list_type',$extraInfo ? $extraInfo : $row['list_type'])).' '.$row['list_type']), 'tt_content', $row['uid'],$forced=FALSE,$type='normal').'</span><br />';
 					break;
				case 'html':		//	HTML
					$output = '<span class="exampleContent exampleContent_'.$row['CType'].'">'.$this->link_edit ('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','bodytext'),1).'</strong> ' . htmlspecialchars(t3lib_div::fixed_lgd_cs(trim($row['bodytext']),2000)),'tt_content',$row['uid'],$forced=FALSE,$type='normal').'<span><br />';
					break;
				case 'header': // Header New in 1.1.1 compared with 1.0.1 - in 1.3 fixed a small bug (menu_type - list_type
					#if($this->render_previewContent_extraPluginInfo($row))
					#	$extraInfo = $this->render_previewContent_extraPluginInfo($row);					
					$output = '<span class="exampleContent exampleContent_'.$row['CType'].'">' . $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','list_type')).'</strong> ' . htmlspecialchars($LANG->sL(t3lib_BEfunc::getLabelFromItemlist('tt_content','list_type',$row['list_type']))).' &ndash; '.htmlspecialchars($extraInfo ? $extraInfo : $row['list_type']), 'tt_content', $row['uid'], $forced=FALSE, $type='normal').'</span><br />';
  					break;
                                                                                    
				case 'search':			//	Search Box
				case 'login':			//	Login Box
				case 'shortcut':		//	Insert records
				case 'div':			//	Divider
					$output = '<span class="exampleContent exampleContent_'.$row['CType'].'"><strong>'.htmlspecialchars ($row['CType']).'</strong></span><br />';
					break;
				case 'templavoila_pi1': //	Flexible Content Element: Rendered directly in getContentTree*()
					$output='<span class="exampleContentFCE"><strong>'.htmlspecialchars ($row['CType']).'</strong></span><br />';			
					break;
				default:
						// return CType name for unhandled CType
					$output='<span class="exampleContent exampleContent_'.$row['CType'].'"><strong>'.htmlspecialchars ($row['CType']).'</strong></span><br />';
			}
		}
		return $output;
	}
	
	/**
	 * Renders additional information about plugins (if available)
	 *
	 * @param	array	$row	Row from database
	 * @return	string	Information
	 */
	function render_previewContent_extraPluginInfo($row) {
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info'][$row['list_type']]))	{
			$hookArr = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info'][$row['list_type']];
		} elseif (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['_DEFAULT']))	{
			$hookArr = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['_DEFAULT'];
		}
		$hookOut = '';
		if (count($hookArr) > 0)	{
			$_params = array('pObj' => &$this, 'row' => $row, 'infoArr' => $infoArr);
			foreach ($hookArr as $_funcRef)	{
				$hookOut .= t3lib_div::callUserFunction($_funcRef, $_params, $this);
			}
		}
		return $hookOut;
	}


	/*******************************************
	 *
	 * Outline rendering:
	 *
	 *******************************************/



	/**
	 * Rendering a single element in outline:
	 *
	 * @param	array		$contentTreeArr: DataStructure info array (the whole tree)
	 * @param	array		$entries: Entries accumulated in this array (passed by reference)
	 * @param	integer		$indentLevel: Indentation level
	 * @param	array		$parentPointer: Element position in structure
	 * @param	string		$controls: HTML for controls to add for this element
	 * @return	void
	 * @access protected
	 * @see	render_outline_allSheets()
	 */
	function render_outline_element($contentTreeArr, &$entries, $indentLevel=0, $parentPointer=array(), $controls='') {
		global $BE_USER,$LANG, $TYPO3_CONF_VARS,$extraBEIcons,$typo3GET; // Added: '$typo3GET' for checking if this file has been used as content element wizard
		if($contentTreeArr['el']['table'] == 'pages') 
			$this->docheaderButtons=$this->getButtons($contentTreeArr);
		
		if(TYPO3_branch >= 4.2 && $contentTreeArr['el']['table'] == 'pages') {
			$elementBelongsToCurrentPage = $contentTreeArr['el']['table'] == 'pages' || $contentTreeArr['el']['pid'] == $this->rootElementUid_pidForContent;
			$canEditPage = $GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'edit'); // new in 1.3.2
		if(!$typo3GET['wizard'] && !$typo3GET['templaVoilaFE']) {
			$wrapRecordIconStart='<span class="contentIcon">';
			$wrapRecordIconEnd='</span>';
			}	
		if($this->modSharedTSconfig['properties']['extraTabBar'] && !$typo3GET['wizard']) {
			$tabbar = tx_tmsharedlib_specialfunctions::getTabMenu('tv');
			$tabbar = '
	<div id="tabbarcontainer" style="position:absolute;top:0px;left:0;width:100%;z-index:1;">'
	.$tabbar.'
	</div>
	<div style="height:20px;" id="tabspacer"></div>';
			}
	
		$recordIcon = $wrapRecordIconStart.'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,$contentTreeArr['el']['icon'],'').' style="text-align: center; vertical-align: middle;" border="0" title="'.htmlspecialchars('['.$contentTreeArr['el']['table'].':'.$contentTreeArr['el']['uid'].']').'" alt="" />'.$wrapRecordIconEnd;

			if(!$typo3GET['wizard'] && !$typo3GET['templaVoilaFE'])
				$contextmenu = $this->translatorMode || !$canEditPage ? $recordIcon : $this->doc->wrapClickMenuOnIcon($recordIcon,$contentTreeArr['el']['table'], $contentTreeArr['el']['uid'], 1,'&amp;callingScriptId='.rawurlencode($this->doc->scriptID), 'new,copy,cut,pasteinto,pasteafter,delete');
			if(TYPO3_branch >= 4.2) {
				$this->markers['TABBAR']=$tabbar;
				}
			}
		/*
		editIconModeTemplaVoila = 0 // only icons
		editIconModeTemplaVoila = 1 // only text
		editIconModeTemplaVoila = 2 // icon left text right
		editIconModeTemplaVoila = 3 // text left icons right
		editIconModeTemplaVoila = 4 // icon left text right like in a button
		editIconModeTemplaVoila = 5 // SELECT-menu
		editIconModeTemplaVoila = 6 // special collection
		*/

		if($this->modTSconfig['properties']['editIconModeTemplaVoila']==1)
			$mode=' textMode';
		else if($this->modTSconfig['properties']['editIconModeTemplaVoila']==2)
			$mode=' bothMode';
		else if($this->modTSconfig['properties']['editIconModeTemplaVoila']==3)
			$mode=' bothMode';
		else if($this->modTSconfig['properties']['editIconModeTemplaVoila']==4)
			$mode=' buttonMode';
		else if($this->modTSconfig['properties']['editIconModeTemplaVoila']==5)
			$mode=' option';
		else	$mode=' iconMode';

			// Get record of element:
		$elementBelongsToCurrentPage = $contentTreeArr['el']['table'] == 'pages' || $contentTreeArr['el']['pid'] == $this->rootElementUid_pidForContent;

			// Prepare the record icon including a context sensitive menu link wrapped around it: // added contenticon
		if(!$typo3GET['wizard'] && !$typo3GET['templaVoilaFE']) // disabled if this file has been used as content element wizard
			$recordIcon = '<span class="contentIcon"><img'.t3lib_iconWorks::skinImg($this->doc->backPath,$contentTreeArr['el']['icon'],'').' style="text-align: center; vertical-align: middle;" border="0" title="'.htmlspecialchars('['.$contentTreeArr['el']['table'].':'.$contentTreeArr['el']['uid'].']').'" alt="" /></span>';
		
		#$titleBarLeftButtons = $this->translatorMode ? $recordIcon : $this->doc->wrapClickMenuOnIcon($recordIcon,$contentTreeArr['el']['table'], $contentTreeArr['el']['uid'], 1,'&amp;callingScriptId='.rawurlencode($this->doc->scriptID), 'new,copy,cut,pasteinto,pasteafter,delete');
		#$titleBarLeftButtons.= $this->getRecordStatHookValue($contentTreeArr['el']['table'],$contentTreeArr['el']['uid']);

			// Prepare table specific settings:
		switch ($contentTreeArr['el']['table']) {
			case 'pages' :
				if(TYPO3_branch >= 4.2)
					$this->markers['STAT']=$this->getRecordStatHookValue($contentTreeArr['el']['table'],$contentTreeArr['el']['uid']);
				else	{					
					$titleBarLeftButtons = $this->translatorMode ? $recordIcon : $this->doc->wrapClickMenuOnIcon($recordIcon,$contentTreeArr['el']['table'], $contentTreeArr['el']['uid'], 1,'&amp;callingScriptId='.rawurlencode($this->doc->scriptID), 'new,copy,cut,pasteinto,pasteafter,delete');
					$titleBarLeftButtons .= $this->getRecordStatHookValue($contentTreeArr['el']['table'],$contentTreeArr['el']['uid']);
					$titleBarLeftButtons .= $this->docheaderButtons['edit_page'];
					$titleBarLeftButtons .= $this->docheaderButtons['view_page'];
					}
				$titleBarRightButtons = '';
			break;
			case 'tt_content' :
				$titleBarLeftButtons = $this->translatorMode ? $recordIcon : $this->doc->wrapClickMenuOnIcon($recordIcon,$contentTreeArr['el']['table'], $contentTreeArr['el']['uid'], 1,'&amp;callingScriptId='.rawurlencode($this->doc->scriptID), 'new,copy,cut,pasteinto,pasteafter,delete');
				$titleBarLeftButtons.= $this->getRecordStatHookValue($contentTreeArr['el']['table'],$contentTreeArr['el']['uid']);
					
				$languageUid = $contentTreeArr['el']['sys_language_uid'];
				if ($this->translatorMode)	{
					$titleBarRightButtons = '';
				}
				else {
						// Create CE specific buttons:
				if(!isset($this->disableEditViewEditingButtons['make_local'])) {
					$link=tx_tvpagemoduleCreateLinks::greateLink($this->doc->backPath,t3lib_extMgm::extRelPath('templavoila').'mod1/makelocalcopy.gif','makeLocal',$LANG->getLL('makeLocal'),$LANG->getLL('makeLocal'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_makeLocal'),$this->modTSconfig['properties']['editIconModeTemplaVoila']);
					$linkSet['make_local'] =  !$elementBelongsToCurrentPage ? $this->link_makeLocal($link, $parentPointer) : '';
					}

				if(!isset($this->disableEditViewEditingButtons['unlink'])) {
					if($this->modTSconfig['properties']['realDelete']) {
						$realdelete=TRUE;
						$title = $LANG->getLL('deleteRecord');
						$label = $GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang.php:p_delete');
						$labelLong = $GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_deleteRecord_long');
						}
					else	{
						$realdelete=FALSE;
						$title = $LANG->getLL('unlinkRecord');
						$label = $GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_unlinkRecord');
						$labelLong = $GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_unlinkRecord_long');
						}
					$link=tx_tvpagemoduleCreateLinks::greateLink($this->doc->backPath,'gfx/garbage.gif','unlink',$title,$labelLong,$label,$this->modTSconfig['properties']['editIconModeTemplaVoila']);
					$linkSet['unlink'] = $this->link_unlink($link, $parentPointer,$realdelete,'normal');
					}

				if(!isset($this->disableEditViewEditingButtons['edit'])) {
					$link=tx_tvpagemoduleCreateLinks::greateLink($this->doc->backPath,'gfx/edit2.gif','edit2',$LANG->getLL('editrecord'),$LANG->getLL ('editrecord'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_editrecord'),$this->modTSconfig['properties']['editIconModeTemplaVoila']);
					$linkSet['edit'] = ($elementBelongsToCurrentPage ? $this->link_edit($link,$contentTreeArr['el']['table'],$contentTreeArr['el']['uid']) : '');
					}

					if($this->modTSconfig['properties']['editIconModeTemplaVoila']==5 || $this->modTSconfig['properties']['editIconModeTemplaVoila']==6) {
						$emptyOption='<option value="">'.$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_select').'</option>';
						$selectStart = '<select  name="action'.$contentTreeArr['el']['uid'].'" onchange="if (this[this.selectedIndex].value)
document.location=this[this.selectedIndex].value">';
						$selectEnd = '</select>';
						}
					$this->linkSet=$linkSet;
					$this->clipboardObj->element_getSelectButtons($parentPointer,'copy,cut,ref',1,true);
					$linkSet=$this->linkSet;
					if($this->modTSconfig['properties']['buttonOrder'])
						$allowOrder = t3lib_div::trimExplode(',',$this->modTSconfig['properties']['buttonOrder'],1);
					else	$allowOrder = t3lib_div::trimExplode(',',$this->defaultorder,1); // order of edit icons

					foreach($allowOrder as $item => $value) {
						if(array_key_exists($value,$linkSet))
							$links.= $linkSet[$value];
						}
					$titleBarRightButtons = $selectStart.$emptyOption.$links.$selectEnd;
				}
			break;
		}

			// Prepare the language icon:
		$languageLabel = htmlspecialchars ($this->allAvailableLanguages[$contentTreeArr['el']['sys_language_uid']]['title']);
		$languageIcon = $this->allAvailableLanguages[$languageUid]['flagIcon'] ? '<img class="langIcon" src="'.$this->allAvailableLanguages[$languageUid]['flagIcon'].'" title="'.$languageLabel.'" alt="'.$languageLabel.'" style="text-align: center; vertical-align: middle;" />' : ($languageLabel && $languageUid ? '['.$languageLabel.']' : '');

			// If there was a langauge icon and the language was not default or [all] and if that langauge is accessible for the user, then wrap the flag with an edit link (to support the "Click the flag!" principle for translators)
		if ($languageIcon && $languageUid>0 && $GLOBALS['BE_USER']->checkLanguageAccess($languageUid) && $contentTreeArr['el']['table']==='tt_content')	{
			$languageIcon = $this->link_edit($languageIcon, 'tt_content', $contentTreeArr['el']['uid'], TRUE);
		}

			// Create warning messages if neccessary:
		$warnings = '';
		if ($this->global_tt_content_elementRegister[$contentTreeArr['el']['uid']] > 1 && $this->rootElementLangParadigm !='free') {
			$warnings .= '<br/>'.$this->doc->icons(2).' <em>'.htmlspecialchars(sprintf($LANG->getLL('warning_elementusedmorethanonce',''), $this->global_tt_content_elementRegister[$contentTreeArr['el']['uid']], $contentTreeArr['el']['uid'])).'</em>';
		}

			// Displaying warning for container content (in default sheet - a limitation) elements if localization is enabled:
		$isContainerEl = count($contentTreeArr['sub']['sDEF']);
		if (!$this->modTSconfig['properties']['disableContainerElementLocalizationWarning'] && $this->rootElementLangParadigm !='free' && $isContainerEl && $contentTreeArr['el']['table'] === 'tt_content' && $contentTreeArr['el']['CType'] === 'templavoila_pi1' && !$contentTreeArr['ds_meta']['langDisable'])	{
			if ($contentTreeArr['ds_meta']['langChildren'])	{
				if (!$this->modTSconfig['properties']['disableContainerElementLocalizationWarning_warningOnly']) {
					$warnings .= '<br/>'.$this->doc->icons(2).' <b>'.$LANG->getLL('warning_containerInheritance_short').'</b>';
				}
			} else {
				$warnings .= '<br />'.$this->doc->icons(3).' <b>'.$LANG->getLL('warning_containerSeparate_short').'</b>';
			}
		}

			// Create entry for this element:
		$entries[] = array(
			'indentLevel' => $indentLevel,
			'icon' => $titleBarLeftButtons,
			'title' => ($elementBelongsToCurrentPage?'':'<em>').htmlspecialchars($contentTreeArr['el']['title']).($elementBelongsToCurrentPage ? '' : '</em>'),
			'warnings' => $warnings,
			'controls' => $titleBarRightButtons.$controls,
			'table' => $contentTreeArr['el']['table'],
			'uid' =>  $contentTreeArr['el']['uid'],
			'flag' => $languageIcon,
			'isNewVersion' => $contentTreeArr['el']['_ORIG_uid'] ? TRUE : FALSE,
			'elementTitlebarStyle' => (!$elementBelongsToCurrentPage ? 'background-color: '.$this->doc->bgColor6 : '')
		);


			// Create entry for localizaitons...
		$this->render_outline_localizations($contentTreeArr, $entries, $indentLevel+1);

			// Create entries for sub-elements in all sheets:
		if ($contentTreeArr['sub'])	{
			foreach($contentTreeArr['sub'] as $sheetKey => $sheetInfo)	{
				if (is_array($sheetInfo)) {
					$this->render_outline_subElements($contentTreeArr, $sheetKey, $entries, $indentLevel+1);
				}
			}
		}
	}


	/**
	 * Rendering outline for child-elements
	 *
	 * @param	array		$contentTreeArr: DataStructure info array (the whole tree)
	 * @param	string		$sheet: Which sheet to display
	 * @param	array		$entries: Entries accumulated in this array (passed by reference)
	 * @param	integer		$indentLevel: Indentation level
	 * @return	void
	 * @access protected
	 */
	function render_outline_subElements($contentTreeArr, $sheet, &$entries, $indentLevel) {
		global $LANG,$BE_USER; // Added: $BE_USER

		// Define l/v keys for current language:
		$langChildren = intval($contentTreeArr['ds_meta']['langChildren']);
		$langDisable = intval($contentTreeArr['ds_meta']['langDisable']);
		$lKeys = $langDisable ? array('lDEF') : ($langChildren ? array('lDEF') : $this->translatedLanguagesArr_isoCodes['all_lKeys']);
		$vKeys = $langDisable ? array('vDEF') : ($langChildren ? $this->translatedLanguagesArr_isoCodes['all_vKeys'] : array('vDEF'));

			// Traverse container fields:
		foreach($lKeys as $lKey)	{

				// Traverse fields:
			if (is_array($contentTreeArr['sub'][$sheet][$lKey]))	{
				foreach($contentTreeArr['sub'][$sheet][$lKey] as $fieldID => $fieldValuesContent)	{
					foreach($vKeys as $vKey)	{

						if (is_array($fieldValuesContent[$vKey]))	{
							$fieldContent = $fieldValuesContent[$vKey];

								// Create flexform pointer pointing to "before the first sub element":
							$subElementPointer = array (
								'table' => $contentTreeArr['el']['table'],
								'uid' => $contentTreeArr['el']['uid'],
								'sheet' => $sheet,
								'sLang' => $lKey,
								'field' => $fieldID,
								'vLang' => $vKey,
								'position' => 0
							);

							if (!$this->translatorMode)	{

									// "New" and "Paste" links:
								$link=tx_tvpagemoduleCreateLinks::greateLink($this->doc->backPath,'gfx/new_el.gif','new',$LANG->getLL ('createnewrecord'),$LANG->getLL ('createnewrecord'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_createnewrecord'),$this->modTSconfig['properties']['editIconModeTemplaVoila'],'',false,true);
														// Added: there is no space for two select menus - that's why a conventional text link
								if($this->modTSconfig['properties']['editIconModeTemplaVoila']==5 || $this->modTSconfig['properties']['editIconModeTemplaVoila']==6) {
									$controls = $this->link_new($link, $subElementPointer,'wizard','normal');
									if(!isset($this->disableEditViewEditingButtons['paste']))
										$controls .= $this->clipboardObj->element_getPasteButtons($subElementPointer,$type='normal');
									}
								else	{
									$controls = $this->link_new($link, $subElementPointer);
									if(!isset($this->disableEditViewEditingButtons['paste']))
										$controls .= $this->clipboardObj->element_getPasteButtons($subElementPointer);
									}
							}

								// Add entry for lKey level:
							$specialPath = ($sheet!='sDEF'?'<'.$sheet.'>':'').($lKey!='lDEF'?'<'.$lKey.'>':'').($vKey!='vDEF'?'<'.$vKey.'>':'');
							$entries[] = array(
								'indentLevel' => $indentLevel,
								'icon' => '',
								'title' => '<b>'.$LANG->sL($fieldContent['meta']['title'],1).'</b>'.($specialPath ? ' <em>'.htmlspecialchars($specialPath).'</em>' : ''),
								'id' => '<'.$sheet.'><'.$lKey.'><'.$fieldID.'><'.$vKey.'>',
								'controls' => $controls
							);

								// Render the list of elements (and possibly call itself recursively if needed):
							if (is_array($fieldContent['el_list']))	 {
								foreach($fieldContent['el_list'] as $position => $subElementKey)	{
									$subElementArr = $fieldContent['el'][$subElementKey];
									if (!$subElementArr['el']['isHidden'] || $this->MOD_SETTINGS['tt_content_showHidden'])	{

											// Modify the flexform pointer so it points to the position of the curren sub element:
										$subElementPointer['position'] = $position;

										if (!$this->translatorMode)	{

											// "New" and "Paste" links:
											$link=tx_tvpagemoduleCreateLinks::greateLink($this->doc->backPath,'gfx/new_el.gif','new',$LANG->getLL ('createnewrecord'),$LANG->getLL ('createnewrecord'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_createnewrecord'),$this->modTSconfig['properties']['editIconModeTemplaVoila'],'',false,true);

											// Added: there is no space for two select menus - that's why a conventional text link
										if($this->modTSconfig['properties']['editIconModeTemplaVoila']==5 || $this->modTSconfig['properties']['editIconModeTemplaVoila']==6) {
											$controls = $this->link_new($link, $subElementPointer,'wizard','normal');
											if(!isset($this->disableEditViewEditingButtons['paste']))
												$controls .= $this->clipboardObj->element_getPasteButtons($subElementPointer,'normal');
											}
										else	{
											$controls = $this->link_new($link, $subElementPointer);
											if(!isset($this->disableEditViewEditingButtons['paste']))
												$controls .= $this->clipboardObj->element_getPasteButtons($subElementPointer);
											}
										}

										$this->render_outline_element($subElementArr, $entries, $indentLevel+1, $subElementPointer, $controls);
									}
								}
							}
						}
					}
				}
			}
		}
	}



	/*******************************************
	 *
	 * Link functions (protected)
	 *
	 *******************************************/



	/**
	 * Returns an HTML link for editing
	 *
	 * @param	string		$label: the label (or image)
	 * @param	string		$table: the table, fx. 'tt_content'
	 * @param	integer		$uid: the uid of the element to be edited
	 * @param	boolean		$forced: by default the link is not shown if translatorMode is set, but with this boolean it can be forced anyway.
	 * @param	string		$type: defines link mode ans class attribute for links
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	 function link_edit($label, $table, $uid, $forced=FALSE,$type='') {
		global $BE_USER,$typo3GET; // Added: some extra parameters for frontend editing (/mod1/index.php has been used in frontend editing as if content element wizard)

		if(array_key_exists('editIconModeTemplaVoila',$BE_USER->uc) && $BE_USER->uc['editIconModeTemplaVoila']!='' && !$this->modTSconfig['properties']['disableUserSetup'])
			$this->modTSconfig['properties']['editIconModeTemplaVoila'] = $BE_USER->uc['editIconModeTemplaVoila'];
		
		$mode = tx_tvpagemoduleCreateLinks::lookMode($this->modTSconfig['properties']['editIconModeTemplaVoila'],$type);
		if($type!=7)
			$modeClass =' class="commandLink commandLink-edit'.$mode.'"'; 

		if ($label) { // new conditions in 1.3.2
			if (($table == 'pages' && ($this->calcPerms & 2) ||
				$table != 'pages' && ($this->calcPerms & 16)) &&
				(!$this->translatorMode || $forced))	{
				if($table == "pages" &&	 $this->currentLanguageUid) {
					return '<a'.$modeClass.' href="'.'index.php?'.$this->link_getParameters().'&amp;editPageLanguageOverlay='.$this->currentLanguageUid.'">'.$label.'</a>';
 					}
				else	{
					$onClick = t3lib_BEfunc::editOnClick('&edit['.$table.']['.$uid.']=edit&'.$this->link_getParameters(), $this->doc->backPath); // t3lib_div::getIndpEnv('TYPO3_SITE_URL')
					if($mode=='option' && $table!='pages' && $type!='normal')
						return '<option value="'.t3lib_div::getIndpEnv('TYPO3_SITE_URL').TYPO3_mainDir.'alt_doc.php?returnUrl='.t3lib_extMgm::extRelPath('templavoila').'mod1/index.php?id='.$typo3GET['id'].'&amp;edit['.$table.']['.$uid.']=edit&amp;'.$this->link_getParameters().'">'.$label.'</option>';
 					else	return '<a'.$modeClass.' href="#" onclick="'.htmlspecialchars($onClick).';return false;">'.$label.'</a>';
					}
				}
			else	return $label;
			}
		return '';
	}


	/**
	 * Returns an HTML link for creating a new record
	 *
	 * @param	string		$label: the label (or image)
	 * @param	array		$parentPointer: Flexform pointer defining the parent element of the new record
	 * @param	string		$Ctype: content type for new content elements
	 * @param	string		$type: defines link mode ans class attribute for links
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function link_new($label,$parentPointer,$Ctype="wizard",$type='') {
		global $BE_USER,$typo3GET;
			// Added: alternative link style
		if(array_key_exists('editIconModeTemplaVoila',$BE_USER->uc) && $BE_USER->uc['editIconModeTemplaVoila']!='' && !$this->modTSconfig['properties']['disableUserSetup'])
			$this->modTSconfig['properties']['editIconModeTemplaVoila'] = $BE_USER->uc['editIconModeTemplaVoila'];

		$mode = tx_tvpagemoduleCreateLinks::lookMode($this->modTSconfig['properties']['editIconModeTemplaVoila'],$type,true);

		if($typo3GET['wizard'] && $this->modTSconfig['properties']['newTarget']) {
			$targetFrame='&wizard=1';
			$target= ' target="wizard"';
			}

		switch ($Ctype) {
			CASE "text":
			$parameters =
				$this->link_getParameters().$targetFrame.
				'&createNewRecord='.rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer));
 			if($mode=="option" && $type!='normal') // '.t3lib_div::getIndpEnv('TYPO3_SITE_URL').TYPO3_mainDir.'alt_doc.php
				return '<option value="index.php?'.htmlspecialchars($parameters).'&amp;defVals[tt_content][CType]=text">'.$label.'</option>';
			else	return '<a class="commandLink commandLink-new'.$mode.'" href="'.'index.php?'.htmlspecialchars($parameters).'&amp;defVals[tt_content][CType]=text">'.$label.'</a>';
 			break;

			CASE "image":
			$parameters =
				$this->link_getParameters().$targetFrame.
				'&createNewRecord='.rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer));
 			if($mode=="option" && $type!='normal')
				return '<option value="index.php?'.htmlspecialchars($parameters).'&amp;defVals[tt_content][CType]=image&defVals[tt_content][imagecols]=2">'.$label.'</option>';
			else	return '<a class="commandLink commandLink-new'.$mode.'" href="'.'index.php?'.htmlspecialchars($parameters).'&amp;defVals[tt_content][CType]=image&amp;defVals[tt_content][imagecols]=2">'.$label.'</a>';
 			break;

			CASE "dynelement":
			$parameters =
				$this->link_getParameters().$targetFrame.
				'&createNewRecord='.rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer));
 			if($mode=="option" && $type!='normal')
				return '<option value="index.php?'.htmlspecialchars($parameters).'&amp;defVals[tt_content][CType]=templavoila_pi1">'.$label.'</option>';
			else	return '<a class="commandLink commandLink-new'.$mode.'" href="'.'index.php?'.htmlspecialchars($parameters).'&amp;defVals[tt_content][CType]=templavoila_pi1">'.$label.'</a>';
 			break;

			CASE "wizard":
			default:
			$parameters =
				$this->link_getParameters().$targetFrame.
				'&parentRecord='.rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer));
 			if($mode=="option" && $type!='normal')
				return '<option value="'.'db_new_content_el.php?'.htmlspecialchars($parameters).'">'.$label.'</option>';
			else	return '<a'.$target.' class="commandLink commandLink-new'.$mode.'" href="'.'db_new_content_el.php?'.htmlspecialchars($parameters).'">'.$label.'</a>';
 			break;

		}
	}


	/**
	 * Returns an HTML link for unlinking a content element. Unlinking means that the record still exists but
	 * is not connected to any other content element or page.
	 *
	 * @param	string		$label: the label
	 * @param	array		$unlinkPointer: flexform pointer pointing to the element to be unlinked
	 * @param	boolean		$realDelete: if set, the record is not just unlinked but deleted!
	 * @param	string		$type: defines link mode ans class attribute for links
	 * @return	string		HTML anchor tag containing the label and the unlink-link
	 * @access protected
	 */
	function link_unlink($label, $unlinkPointer, $realDelete=FALSE, $type='sidebar') {
		global $LANG,$BE_USER;
			// Added: alternative link style
		if(array_key_exists('editIconModeTemplaVoila',$BE_USER->uc) && $BE_USER->uc['editIconModeTemplaVoila']!='' && !$this->modTSconfig['properties']['disableUserSetup'])
			$this->modTSconfig['properties']['editIconModeTemplaVoila'] = $BE_USER->uc['editIconModeTemplaVoila'];

		$mode = tx_tvpagemoduleCreateLinks::lookMode($this->modTSconfig['properties']['editIconModeTemplaVoila']);
		$unlinkPointerString = rawurlencode($this->apiObj->flexform_getStringFromPointer ($unlinkPointer));

		if ($realDelete) {
			if($mode=='option')
				return '<option value="index.php?'.$this->link_getParameters().'&amp;deleteRecord='.$unlinkPointerString.'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('deleteRecordMsg')).');').'">'.$label.'</option>';
 			else	return '<a class="commandLink commandLink-linkUnlink'.$mode.'" href="index.php?'.$this->link_getParameters().'&amp;deleteRecord='.$unlinkPointerString.'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('deleteRecordMsg')).');').'">'.$label.'</a>';
			}
		else	{
			if($mode=='option')
				return '<option value="index.php?'.$this->link_getParameters().'&amp;unlinkRecord='.$unlinkPointerString.'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('unlinkRecordMsg')).');').'">'.$label.'</option>';
 			else	return '<a class="commandLink commandLink-linkUnlink'.$mode.'" href="index.php?'.$this->link_getParameters().'&amp;unlinkRecord='.$unlinkPointerString.'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('unlinkRecordMsg')).');').'">'.$label.'</a>';
			}
		}

	/**
	 * Returns an HTML link for moving up/down a content element.
	 *
	 * @param	string		$label: the label
	 * @param	array		$sourcePointer: flexform pointer pointing to the element to be moved
	 * @param	string		$type: defines link mode ans class attribute for links
	 * @return	string		HTML anchor tag containing the label and the unlink-link
	 * @access protected
	 */
	function moveUpDown($label,$sourcePointer,$cmd='moveUp',$mode='') { // Added: base on other link functions
		global $LANG, $BE_USER;
		if(array_key_exists('editIconModeTemplaVoila',$BE_USER->uc) && $BE_USER->uc['editIconModeTemplaVoila']!='' && !$this->modTSconfig['properties']['disableUserSetup'])
			$this->modTSconfig['properties']['editIconModeTemplaVoila'] = $BE_USER->uc['editIconModeTemplaVoila'];
		$mode = tx_tvpagemoduleCreateLinks::lookMode($this->modTSconfig['properties']['editIconModeTemplaVoila']);
		$sourcePointerString = rawurlencode($this->apiObj->flexform_getStringFromPointer ($sourcePointer));
		if($mode=='option')
			return '<option value="index.php?'.$this->link_getParameters().'&amp;'.$cmd.'='.$sourcePointerString.'">'.$label.'</option>'; // &amp;'.$cmd.'=1
 		else	return '<a class="commandLink commandLink-move'.$mode.'" href="index.php?'.$this->link_getParameters().'&amp;'.$cmd.'='.$sourcePointerString.'">'.$label.'</a>';
	}

	/**
	 * Returns an HTML link for making a reference content element local to the page (copying it).
	 *
	 * @param	string		$label: the label
	 * @param	array		$makeLocalPointer: tlexform pointer pointing to the element which shall be copied
	 * @return	string		HTML anchor tag containing the label and the unlink-link
	 * @access protected
	 */
	function link_makeLocal($label, $makeLocalPointer) {
		global $LANG,$BE_USER;

		$mode = tx_tvpagemoduleCreateLinks::lookMode($this->modTSconfig['properties']['editIconModeTemplaVoila']);
		if($mode=='option')
			return '<option value="index.php?'.$this->link_getParameters().'&amp;makeLocalRecord='.rawurlencode($this->apiObj->flexform_getStringFromPointer($makeLocalPointer)).'" >'.$label.'</option>'; // onclick taken off because it might cause problems (onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('makeLocalMsg')).');').'")
 		else	return '<a class="commandLink commandLink-linkMakeLocal'.$mode.'" href="index.php?'.$this->link_getParameters().'&amp;makeLocalRecord='.rawurlencode($this->apiObj->flexform_getStringFromPointer($makeLocalPointer)).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('makeLocalMsg')).');').'">'.$label.'</a>';
	}

	/**
	 * Creates additional parameters which are used for linking to the current page while editing it
	 *
	 * @return	string		parameters
	 * @access public
	 */
	function link_getParameters() {
		global $typo3GET;
		// Added: information for alt_doc.php
		if(isset($typo3GET['templaVoilaFE']))
			$frontEnd=1;
		if(isset($typo3GET['forceNoPopup'])) // info fo
			$noPopUp='&forceNoPopup='.$typo3GET['forceNoPopup'];

		$thisUser='&amp;pageModule=1';
		if($frontEnd)
			$feParams='&amp;frontEnd=1&templaVoilaFE=1';
 		$output =
			'id='.$this->id.
			(is_array($this->altRoot) ? t3lib_div::implodeArrayForUrl('altRoot',$this->altRoot) : '') .
			($this->versionId ? '&amp;versionId='.rawurlencode($this->versionId) : '');
		$output .= $feParams.$noPopUp.$thisUser;
		return $output;
	}


	/*************************************************
	 *
	 * Processing and structure functions (protected)
	 *
	 *************************************************/

	/**
	 * Checks various GET / POST parameters for submitted commands and handles them accordingly.
	 * All commands will trigger a redirect by sending a location header after they work is done.
	 *
	 * Currently supported commands: 'createNewRecord', 'unlinkRecord', 'deleteRecord','pasteRecord',
	 * 'makeLocalRecord', 'localizeElement', 'createNewPageTranslation' and 'editPageLanguageOverlay'
	 *
	 * @return	void
	 * @access protected
	 */
	function handleIncomingCommands() {
		global $typo3GET; // Added: needed in the switch option 'createNewRecord'

		$possibleCommands = array ('moveUp','moveDown','createNewRecord', 'unlinkRecord', 'deleteRecord','pasteRecord', 'makeLocalRecord', 'localizeElement', 'createNewPageTranslation', 'editPageLanguageOverlay');

		foreach ($possibleCommands as $command) {
			$commandParameters=$typo3GET[$command];
			if ($typo3GET[$command]) { // ($commandParameters = t3lib_div::_GP($command))!=''

				$redirectLocation = 'index.php?'.$this->link_getParameters();

				switch ($command) {
					// Added: moveUp/moveDown added - solution base on the plugin 'mk_tvfrontend
					case 'moveUp':
					case 'moveDown':

					$sourcePointer = $this->apiObj->flexform_getPointerFromString ($commandParameters);
					$destinationPointer = $sourcePointer;

					if ($command=='moveUp')	{
						if ($destinationPointer['position']>1)
							$destinationPointer['position'] = $destinationPointer['position']-2;
						$this->apiObj->moveElement_setElementReferences($sourcePointer, $destinationPointer);
						}
					else	{
						$destinationPointer['position'] = $destinationPointer['position']+1;
						$this->apiObj->moveElement_setElementReferences($sourcePointer, $destinationPointer);
						}
					break;
					case 'createNewRecord':
							// Historically "defVals" has been used for submitting the preset row data for the new element, so we still support it here:
						$defVals = t3lib_div::_GP('defVals');
						$newRow = is_array ($defVals['tt_content']) ? $defVals['tt_content'] : array();

							// Create new record and open it for editing
						$destinationPointer = $this->apiObj->flexform_getPointerFromString($commandParameters);
						$newUid = $this->apiObj->insertElement($destinationPointer, $newRow);
						if($typo3GET['wizard']) // Added: if the page module has been used content element wizard, alt_doc.php needs an extra parameter to get correct frame target
							$wizard='&wizard=1';
						$redirectLocation = $GLOBALS['BACK_PATH'].'alt_doc.php?edit[tt_content]['.$newUid.']=edit'.$wizard.'&returnUrl='.rawurlencode(t3lib_extMgm::extRelPath('templavoila').'mod1/index.php?'.$this->link_getParameters());
  					break;

					case 'unlinkRecord':
						$unlinkDestinationPointer = $this->apiObj->flexform_getPointerFromString($commandParameters);
						$this->apiObj->unlinkElement($unlinkDestinationPointer);
					break;

					case 'deleteRecord':
						$deleteDestinationPointer = $this->apiObj->flexform_getPointerFromString($commandParameters);
						$this->apiObj->deleteElement($deleteDestinationPointer);
					break;

					case 'pasteRecord':
						$sourcePointer = $this->apiObj->flexform_getPointerFromString (t3lib_div::_GP('source'));
						$destinationPointer = $this->apiObj->flexform_getPointerFromString (t3lib_div::_GP('destination'));
						switch ($commandParameters) {
							case 'copy' :	$this->apiObj->copyElement ($sourcePointer, $destinationPointer); break;
							case 'copyref':	$this->apiObj->copyElement ($sourcePointer, $destinationPointer, FALSE); break;
							case 'cut':	$this->apiObj->moveElement ($sourcePointer, $destinationPointer); break;
							case 'ref':	list(,$uid) = explode(':', t3lib_div::_GP('source'));
							$this->apiObj->referenceElementByUid ($uid, $destinationPointer);
							break;
							}
					break;

					case 'makeLocalRecord':
						$sourcePointer = $this->apiObj->flexform_getPointerFromString ($commandParameters);
						$this->apiObj->copyElement ($sourcePointer, $sourcePointer);
						$this->apiObj->unlinkElement ($sourcePointer);
					break;

					case 'localizeElement':
						$sourcePointer = $this->apiObj->flexform_getPointerFromString (t3lib_div::_GP('source'));
						$this->apiObj->localizeElement ($sourcePointer, $commandParameters);
					break;

					case 'createNewPageTranslation':
							// Create parameters and finally run the classic page module for creating a new page translation
						$params = '&edit[pages_language_overlay]['.intval (t3lib_div::_GP('pid')).']=new&overrideVals[pages_language_overlay][sys_language_uid]='.intval($commandParameters);
						$returnUrl = '&returnUrl='.rawurlencode(t3lib_extMgm::extRelPath('templavoila').'mod1/index.php?'.$this->link_getParameters());
  						$redirectLocation = $GLOBALS['BACK_PATH'].'alt_doc.php?'.$params.$returnUrl;
					break;

					case 'editPageLanguageOverlay':
							// Look for pages language overlay record for language:
						$sys_language_uid = intval($commandParameters);
						$params = '';
						if ($sys_language_uid != 0) {
							// Edit overlay record
							list($pLOrecord) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
									'*',
									'pages_language_overlay',
									'pid='.intval($this->id).' AND sys_language_uid='.$sys_language_uid.
										t3lib_BEfunc::deleteClause('pages_language_overlay').
										t3lib_BEfunc::versioningPlaceholderClause('pages_language_overlay')
								);
							if ($pLOrecord) {
								t3lib_beFunc::workspaceOL('pages_language_overlay', $pLOrecord);
								if (is_array($pLOrecord))	{
									$params = '&edit[pages_language_overlay]['.$pLOrecord['uid'].']=edit';
								}
							}
						}
						else {
							// Edit default language (page properties)
							// No workspace overlay because we already on this page
							$params = '&edit[pages]['.intval($this->id).']=edit';
						}
						if ($params) {
							$returnUrl = '&returnUrl='.rawurlencode(t3lib_extMgm::extRelPath('templavoila').'mod1/index.php?'.$this->link_getParameters());
							$redirectLocation = $GLOBALS['BACK_PATH'].'alt_doc.php?'.$params.$returnUrl;	//.'&localizationMode=text';
 						}
					break;
				}
			}
		}

		if (isset ($redirectLocation)) {
			header('Location: '.t3lib_div::locationHeaderUrl($redirectLocation));
		}
	}

	/**
	 * Returns label, localized and converted to current charset. Label must be from FlexForm (= always in UTF-8).
	 * new funtion 1.3
	 *
	 * @param	string		$label:	label
	 * @param	boolean		$hsc:	<code>true</code> if HSC required
	 * @return	string		Converted label
	 */
	function localizedFFLabel($label, $hsc) {
		global	$LANG;

		$charset = $LANG->origCharSet;
		$LANG->origCharSet = 'utf-8';
		$result = $LANG->hscAndCharConv($label, $hsc);
		$LANG->origCharSet = $charset;
		return $result;
	}

	/**
	 * Rendering information for doktype 199
	 *
	 * @param	array		$pageRecord:  the current page record
	 * @return	string		HTML
	 */
	function renderDoktype_199($pageRecord) {
		global $LANG,$BE_USER, $TYPO3_CONF_VARS;

			// Prepare the record icon including a content sensitive menu link wrapped around it:
		$pageTitle = htmlspecialchars(t3lib_div::fixed_lgd_cs(t3lib_BEfunc::getRecordTitle('pages', $pageRecord), 50));
		$recordIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, t3lib_iconWorks::getIcon('pages', $pageRecord), '').' style="text-align: center; vertical-align: middle;" width="18" height="16" border="0" title="'.$pageTitle.'" alt="" />';
		$content =
			$this->doc->icons(1).
			$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:cannotedit_spacer');
		return $content;
	}
	
	/**
	 * Create the panel of buttons for submitting the form or otherwise perform operations.
	 * @param	array	$contentTreeAr: possible array about the page record
	 * @return	array	all available buttons as an assoc. array
	 */
	function getButtons($contentTreeArr=array())	{
		global $BE_USER,$LANG,$typo3GET,$extraBEIcons;

		$buttons = array(
			'view' => '',
			'edit_page' => '',
			'new_page' => '',
			'new_record' => '',
			'record_list' => '',
			'shortcut' => '',
			'csh' => '',
		);

		if(!empty($contentTreeArr)) {
			// View page	
			if(!isset($this->disableEditViewEditingButtons['view_page'])) {
				$addGetVars = ($this->currentLanguageUid?'&L='.$this->currentLanguageUid:'');
				$viewPageOnClick = 'onclick= "'.htmlspecialchars(t3lib_BEfunc::viewOnClick($contentTreeArr['el']['uid'], $this->doc->backPath, t3lib_BEfunc::BEgetRootLine($contentTreeArr['el']['uid']),'','',$addGetVars)).'"';
				$viewPageIcon = '<img class="pageIcon" '.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/zoom.gif','width="12" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.showPage',1).'"alt="" style="text-align: center; vertical-align: middle; border:0;" />';
				$buttons['view'] =  '<a href="#" '.$viewPageOnClick.'>'.$viewPageIcon.'</a>';
				}			
		
			// Edit page
			if($extraBEIcons)
				$imageName = 'edit2Page.gif'; // as default the same as in the folder /extra-icons/ of the plugin 'tm_contentaccess'
			else	$imageName ='edit2.gif';
			if(!($this->translatorMode || !$this->canEditPage) && !$typo3GET['wizard'] && !$typo3GET['templaVoilaFE'] && !isset($this->disableEditViewEditingButtons['edit_page']) && !$this->modSharedTSconfig['properties']['extraTabBar'])
				$buttons['edit_page'] =  $this->translatorMode ? '' : $this->link_edit('<img class="pageIcon" '.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/'.$imageName,'').' title="'.htmlspecialchars($LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:editPage')).'" alt="" style="text-align: center; vertical-align: middle; border:0;" />',$contentTreeArr['el']['table'],$contentTreeArr['el']['uid'],TRUE,7);				
		
			// New page
			if(isset($this->enableEditViewEditingButtons['new_page'])) // Added: create new page link as icon
				$buttons['new_page'] = '<a href="'.htmlspecialchars($this->doc->backPath.'db_new.php?id='.$contentTreeArr['el']['uid'].'&pagesOnly=1&returnUrl=').rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).'">'.
					'<img class="pageIcon"'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_page.gif','').' title="'.$LANG->sL('LLL:EXT:lang/locallang_mod_web_list.php:newRecordGeneral',1).' (pages)"  alt="" style="text-align: center; vertical-align: middle; border:0;" />'.
					'</a>';			
		
			// New record
			if(!isset($this->disableEditViewEditingButtons['new_record'])) // Added: create new record link as icon
				$buttons['new_record'] = '<a href="'.htmlspecialchars($this->doc->backPath.'db_new.php?id='.$contentTreeArr['el']['uid'].'&returnUrl=').rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).'">'.
 					'<img class="pageIcon" '.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_record.gif','').' title="'.$LANG->sL('LLL:EXT:lang/locallang_mod_web_list.php:newRecordGeneral',1).'"  alt="" style="text-align: center; vertical-align: middle; border:0;" />'.
					'</a>';
			
			// Web > List module			
			if($BE_USER->check('modules','web_list') && !isset($this->disableEditViewEditingButtons['list'])) // Added: create new record link as icon
				$buttons['record_list'] = '<a href="'.htmlspecialchars($this->doc->backPath.'db_list.php?id='.$contentTreeArr['el']['uid'].'&returnUrl=').rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).'">'.
					'<img class="pageIcon" '.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/list.gif','').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showList',1).'"  alt="" style="text-align: center; vertical-align: middle; border:0;" />'.
					'</a>';
			}
					
			// Create shortcut?
		$buttons['shortcut'] = ($BE_USER->mayMakeShortcut() ? $this->doc->makeShortcutIcon('id,altRoot',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']) : '');
						
			// CSH
		$buttons['csh'] = t3lib_BEfunc::cshItem('_MOD_web_txtemplavoilaM1', '', $this->doc->backPath,'');
					'</a>';	
		return $buttons;
	}
	
	/**
	 * Hook for record statistic
	 * @param	string	$table: name of table
	 * @param	string	$id: (u)id of the record
	 * @return	string	HTML
	 */
	function getRecordStatHookValue($table,$id)	{
			// Call stats information hook
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']))	{
			$stat='';
			$_params = array($table,$id);
			if(TYPO3_branch >= 4.2 && $table=='pages')
				$stat.= '<div class="buttongroup">';
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks'] as $_funcRef)	{
				$stat.= t3lib_div::callUserFunction($_funcRef,$_params,$this);
			}
			if(TYPO3_branch >= 4.2 && $table=='pages')
				$stat.= '</div>';
			return $stat;
		}
	}
}
?>