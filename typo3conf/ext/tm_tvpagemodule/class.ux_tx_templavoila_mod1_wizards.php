<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2006  Robert Lemke (robert@typo3.org)
*  All rights reserved
*
*  script is part of the TYPO3 project. The TYPO3 project is
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
 * Submodule 'wizards' for the templavoila page module
 *
 * $Id: class.tx_templavoila_mod1_wizards.php 2413 2006-06-23 12:56:10Z liels_bugs $
 *
 * @author     Robert Lemke <robert@typo3.org>
 */
/**
 * Submodule 'Wizards' for the templavoila page module
 *
 * Note: This class is closely bound to the page module class and uses many variables and functions directly. After major modifications of
 *       the page module all functions of this wizard class should be checked to make sure that they still work.
 *
 * @author		Robert Lemke <robert@typo3.org>
 * @package		TYPO3
 * @subpackage	tx_templavoila
 */
 

if(!$typo3GET)
	$typo3GET=t3lib_div::_GET();
#t3lib_div::debug($typo3GET);
require_once(PATH_t3lib.'class.t3lib_tceforms.php');
require_once(PATH_t3lib.'class.t3lib_befunc.php');
class ux_tx_templavoila_mod1_wizards extends tx_templavoila_mod1_wizards {
	
	
	var $modSharedTSconfig=array();
	var $modTSconfig=array();


	/********************************************
	 *
	 * Wizards render functions
	 *
	 ********************************************/

	/**
	 * Creates the screen for "new page wizard"
	 *
	 * @param	integer		$positionPid: Can be positive and negative depending of where the new page is going: Negative always points to a position AFTER the page having the abs. value of the positionId. Positive numbers means to create as the first subpage to another page.
	 * @return	string		Content for the screen output.
	 * @todo				Check required field(s), support t3d
	 */
    function renderWizard_createNewPage($positionPid) {
		global $LANG, $BE_USER, $TYPO3_CONF_VARS,$typo3GET;
		
		$this->modSharedTSconfig = t3lib_BEfunc::getModTSconfig($this->id,'mod.SHARED');
		if(isset($typo3GET['positionPid']) && $typo3GET['cmd']=="crPage") { // these must unset because these values should not exist in this connection
			$this->modSharedTSconfig['properties']['startID']=NULL;
			$this->id=NULL;
			}
			// The user already submitted the create page form:
		if (t3lib_div::_GP('doCreate')) {
			
				// Check if the HTTP_REFERER is valid
			$refInfo = parse_url(t3lib_div::getIndpEnv('HTTP_REFERER'));
			$httpHost = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
			if ($httpHost == $refInfo['host'] || t3lib_div::_GP('vC') == $BE_USER->veriCode() || $TYPO3_CONF_VARS['SYS']['doNotCheckReferer'])	{
					// Create new page
				$newID = $this->createPage (t3lib_div::_GP('data'), $positionPid);
			
				if ($newID > 0) {
					
					

						// Get TSconfig for a different selection of fields in the editing form
					$TSconfig = t3lib_BEfunc::getModTSconfig($newID, 'mod.web_txtemplavoilaM1.createPageWizard.fieldNames');
					$fieldNames = isset ($TSconfig['value']) ? $TSconfig['value'] : 'hidden,title,alias';
			
					// Added: some new paramms
						// Create parameters and finally run the classic page module's edit form for the new page:
					$params = '&PageWizard=1&edit[pages]['.$newID.']=edit&columnsOnly='.rawurlencode($fieldNames);
					$returnUrl = rawurlencode(t3lib_div::getIndpEnv('SCRIPT_NAME').'?id='.$newID.'&updatePageTree=1');

					header('Location: '.t3lib_div::locationHeaderUrl($this->doc->backPath.'alt_doc.php?returnUrl='.$returnUrl.$params));
					exit();
				} else { debug('Error: Could not create page!'); }
			} else { debug('Error: Referer host did not match with server host.'); }
		}

			// Based on t3d/xml templates:
		if (false != ($templateFile = t3lib_div::_GP('templateFile'))) {

			if (t3lib_div::getFileAbsFileName($templateFile) && @is_file($templateFile))	{

					// First, find positive PID for import of the page:
				$importPID = t3lib_BEfunc::getTSconfig_pidValue('pages','',$positionPid);

					// Initialize the import object:
				$import = $this->getImportObject();
				if ($import->loadFile($templateFile, 1))	{
						// Find the original page id:
					$origPageId = key($import->dat['header']['pagetree']);

						// Perform import of content
					$import->importData($importPID);

						// Find the new page id (root page):
					$newID = $import->import_mapId['pages'][$origPageId];

					if ($newID)	{
							// If the page was destined to be inserted after another page, move it now:
						if ($positionPid<0)	{
							$cmd = array();
							$cmd['pages'][$newID]['move'] = $positionPid;
							$tceObject = $import->getNewTCE();
							$tceObject->start(array(),$cmd);
							$tceObject->process_cmdmap();
						}

						// PLAIN COPY FROM ABOVE - BEGIN
							// Get TSconfig for a different selection of fields in the editing form
						$TSconfig = t3lib_BEfunc::getModTSconfig($newID, 'tx_templavoila.mod1.createPageWizard.fieldNames');
						$fieldNames = isset ($TSconfig['value']) ? $TSconfig['value'] : 'hidden,title,alias';
							// Added: a new param
							// Create parameters and finally run the classic page module's edit form for the new page:
						$params = '&PageWizard=1&edit[pages]['.$newID.']=edit&columnsOnly='.rawurlencode($fieldNames);
						$returnUrl = rawurlencode(t3lib_div::getIndpEnv('SCRIPT_NAME').'?id='.$newID.'&updatePageTree=1');

						header('Location: '.t3lib_div::locationHeaderUrl($this->doc->backPath.'alt_doc.php?returnUrl='.$returnUrl.$params));
						exit();

						// PLAIN COPY FROM ABOVE - END
					} else { debug('Error: Could not create page!'); }
				}
			}
		}
			// Start assembling the HTML output

		$this->doc->form='<form action="'.htmlspecialchars('index.php?id='.$this->pObj->id).'" method="post" autocomplete="off" enctype="'.$TYPO3_CONF_VARS['SYS']['form_enctype'].'" onsubmit="return TBE_EDITOR_checkSubmit(1);">';
 		$this->doc->divClass = '';
		$this->doc->getTabMenu(0,'_',0,array(''=>''));

			// init tceforms for javascript printing
		$tceforms = t3lib_div::makeInstance('t3lib_TCEforms');
		$tceforms->initDefaultBEMode();
		$tceforms->backPath = $GLOBALS['BACK_PATH'];
		$tceforms->doSaveFieldName = 'doSave';

			// Setting up the context sensitive menu:
		$CMparts = $this->doc->getContextMenuCode();
		$this->doc->JScode.= $CMparts[0] . $tceforms->printNeededJSFunctions_top();
		$this->doc->bodyTagAdditions = $CMparts[1];
		$this->doc->postCode.= $CMparts[2] . $tceforms->printNeededJSFunctions();
		
		// Changed: changed title
		$content.=$this->doc->header($LANG->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:template'));
		
		#$content.=$this->doc->header($LANG->sL('LLL:EXT:lang/locallang_core.xml:db_new.php.pagetitle'));
		
		$content.=$this->doc->startPage($LANG->getLL ('createnewpage_title'));

		#$content .= '<input type="text" name="title" value="" />';
			// Add template selectors
		$tmplSelectorCode = '';
		$tmplSelector = $this->renderTemplateSelector ($positionPid,'tmplobj');
		if ($tmplSelector) {
#			$tmplSelectorCode.='<em>'.$LANG->getLL ('createnewpage_templateobject_createemptypage').'</em>';
			$tmplSelectorCode.=$this->doc->spacer(5);
			$tmplSelectorCode.=$tmplSelector;
			$tmplSelectorCode.=$this->doc->spacer(10);
		}

		$tmplSelector = $this->renderTemplateSelector ($positionPid,'t3d');
		if ($tmplSelector) {
			#$tmplSelectorCode.='<em>'.$LANG->getLL ('createnewpage_templateobject_createpagewithdefaultcontent').'</em>';
			$tmplSelectorCode.=$this->doc->spacer(5);
			$tmplSelectorCode.=$tmplSelector;
			$tmplSelectorCode.=$this->doc->spacer(10);
		}

		if ($tmplSelectorCode) {
			$content.='<h3>'.htmlspecialchars($LANG->getLL ('createnewpage_selecttemplate')).'</h3>';
			$content.=$LANG->getLL ('createnewpage_templateobject_description');
			$content.=$this->doc->spacer(10);
			$content.=$tmplSelectorCode;
		}

		$content .= '<input type="hidden" name="positionPid" value="'.$positionPid.'" />';
		$content .= '<input type="hidden" name="doCreate" value="1" />';
		$content .= '<input type="hidden" name="cmd" value="crPage" />';
		
		return $content;
	}
	
	/**
	 * Performs the neccessary steps to creates a new page
	 *
	 * @param	array		$pageArray: array containing the fields for the new page
	 * @param	integer		$positionPid: location within the page tree (parent id)
	 * @return	integer		uid of the new page record
	 */
	function createPage($pageArray,$positionPid) {
		global $typo3GET;		
		$this->modSharedTSconfig = t3lib_BEfunc::getModTSconfig($this->id,'mod.SHARED');
		if(isset($typo3GET['positionPid']) && $typo3GET['cmd']=="crPage") { // must unset because these variables must not exist in this connection
			$this->modSharedTSconfig['properties']['startID']=NULL;
			$this->id=NULL;
			}
		elseif($typo3GET['id']==0 && $typo3GET['cmd']!="crPage" && $this->modSharedTSconfig['properties']['startID'])	
			$this->id=$this->modSharedTSconfig['properties']['startID'];
		
		$dataArr = array();
		$dataArr['pages']['NEW'] = $pageArray;
		$dataArr['pages']['NEW']['pid'] = $positionPid;
		if (is_null($dataArr['pages']['NEW']['hidden'])) {
			$dataArr['pages']['NEW']['hidden'] = 0;
		}
		unset($dataArr['pages']['NEW']['uid']);

			// If no data structure is set, try to find one by using the template object
		if ($dataArr['pages']['NEW']['tx_templavoila_to'] && !$dataArr['pages']['NEW']['tx_templavoila_ds']) {
			$templateObjectRow = t3lib_BEfunc::getRecordWSOL('tx_templavoila_tmplobj',$dataArr['pages']['NEW']['tx_templavoila_to'],'uid,pid,datastructure');
			$dataArr['pages']['NEW']['tx_templavoila_ds'] = $templateObjectRow['datastructure'];
		}

		$tce = t3lib_div::makeInstance('t3lib_TCEmain');

			// set default TCA values specific for the user
		$TCAdefaultOverride = $GLOBALS['BE_USER']->getTSConfigProp('TCAdefaults');
		if (is_array($TCAdefaultOverride))	{
			$tce->setDefaultsFromUserTS($TCAdefaultOverride);
		}

		$tce->stripslashes_values=0;
		$tce->start($dataArr,array());		
				
		if(strstr($positionPid,'-') || strstr($positionPid,'+'))
			$newPositionPid=substr($positionPid,1); // id of the new page
		else	$newPositionPid=$positionPid;
		
		$pageMaxItem=$GLOBALS['TYPO3_DB']->exec_SELECTquery('COUNT(*)','pages','title=\'\'','', '', '');				
		$pageMaxItem=$GLOBALS['TYPO3_DB']->sql_fetch_row($pageMaxItem);				
		$pageMaxItem=$pageMaxItem[0];	
		
		if($pageMaxItem<1)				
			$tce->process_datamap();
		else	{
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('pages', 'title=\'\'', array('title' => 'Missed title - please change this title!'));
			t3lib_BEfunc::getSetUpdateSignal('updatePageTree');
			}
		
		return $tce->substNEWwithIDs['NEW'];
	}
}
?>
