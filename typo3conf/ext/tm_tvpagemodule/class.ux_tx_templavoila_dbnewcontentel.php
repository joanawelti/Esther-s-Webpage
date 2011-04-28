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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * New content elements wizard for templavoila
 *
 * $Id: db_new_content_el.php,v 1.14 2006/04/05 08:26:29 robert_typo3 Exp $
 * Originally based on the CE wizard / cms extension by Kasper Skaarhoj <kasper@typo3.com>
 * XHTML compatible.
 *
 * @author		Robert Lemke <robert@typo3.org>
 * @coauthor	Kasper Skaarhoj <kasper@typo3.com>
 */

/**
 * Script Class for the New Content element wizard
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @author	Tapio Markula
 * @package TYPO3
 * @subpackage templavoila
 */
 
/**** if this page has been loaded into frontend editing starts ***************/

if(!$typo3GET)
	$typo3GET=t3lib_div::_GET();
if(isset($typo3GET['frontEnd']) || isset($typo3GET['templaVoilaFE']))
	$frontEnd=1;
#t3lib_div::debug($typo3GET);		
/**** if this page has been loaded into frontend editing ends *****************/
class ux_tx_templavoila_dbnewcontentel extends tx_templavoila_dbnewcontentel {
	
	var $modTSconfig=array();
	/**
	 * Initialize internal variables.
	 *
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$BACK_PATH,$TBE_MODULES_EXT,$typo3GET; // Added: if /mod1/index.php has been used as content element wizard
		$this->modTSconfig= t3lib_BEfunc::getModTSconfig($this->id,'mod.web_txtemplavoilaM1');
			// Setting class files to include:
		if (is_array($TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']))	{
			$this->include_once = array_merge($this->include_once,$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']);
		}

			// Setting internal vars:
		$this->id = intval(t3lib_div::GPvar('id'));
		$this->parentRecord = t3lib_div::GPvar('parentRecord');
		$this->altRoot = t3lib_div::GPvar('altRoot');
		$this->defVals = t3lib_div::GPvar('defVals');

			// Starting the document template object:
		$this->doc = t3lib_div::makeInstance('mediumDoc');
		$this->doc->docType= 'xhtml_trans';
		$this->doc->backPath = $BACK_PATH;
		$this->doc->JScode='';
		if($typo3GET['wizard'] && $this->modTSconfig['properties']['newTarget'])  // Added: if /mod1/index.php has been used as content element wizard needed target 
			$this->doc->JScode.='
		<base target="_parent" />'
		;
		$this->doc->form='<form action="" name="editForm">';

			// Getting the current page and receiving access information (used in main())
		$perms_clause = $BE_USER->getPagePermsClause(1);
		$pageinfo = t3lib_BEfunc::readPageAccess($this->id,$perms_clause);
		$this->access = is_array($pageinfo) ? 1 : 0;


		$this->apiObj = t3lib_div::makeInstance ('tx_templavoila_api');

			// If no parent record was specified, find one:
		if (!$this->parentRecord) {
			$mainContentAreaFieldName = $this->apiObj->ds_getFieldNameByColumnPosition ($this->id, 0);
			if ($mainContentAreaFieldName != FALSE) {
				$this->parentRecord = 'pages:'.$this->id.':sDEF:lDEF:'.$mainContentAreaFieldName.':vDEF:0';
			}
		}
	}

	/**
	 * Creating the module output.
	 *
	 * @return	void
	 * @todo	provide position mapping if no position is given already. Like the columns selector but for our cascading element style ...
	 */
	function main()	{
		global $LANG,$BACK_PATH,$typo3GET,$frontEnd; // Added: if /mod1/index.php has been used as content element wizard
		if($frontEnd)
			$feParams='&frontEnd=1&templaVoilaFE=1';
		if ($this->id && $this->access)	{

				// Creating content
			$this->content='';
			$this->content.=$this->doc->startPage($LANG->getLL('newContentElement'));
			if(!$typo3GET['wizard']) // Added: if /mod1/index.php has been used as content element wizard, no header
				$this->content.=$this->doc->header($LANG->getLL('newContentElement'));
			$this->content.=$this->doc->spacer(5);

			$elRow = t3lib_BEfunc::getRecordWSOL('pages',$this->id);
			if(!$typo3GET['wizard']) {  // Added: if /mod1/index.php has been used as content element wizard take off original headers
				$header= t3lib_iconWorks::getIconImage('pages',$elRow,$BACK_PATH,' title="'.htmlspecialchars(t3lib_BEfunc::getRecordIconAltText($elRow,'pages')).'" align="top"');
				$header.= t3lib_BEfunc::getRecordTitle('pages',$elRow,1);
				}
			$this->content.=$this->doc->section('',$header,0,1);
			$this->content.=$this->doc->spacer(10);

				// Wizard
			$wizardCode='';
			$tableRows=array();
			$wizardItems = $this->getWizardItems();

				// Traverse items for the wizard.
				// An item is either a header or an item rendered with a title/description and icon:
			$counter=0;
			foreach($wizardItems as $key => $wizardItem)	{
				if ($wizardItem['header'])	{
					if ($counter>0) $tableRows[]='
						<tr>
							<td colspan="3"><br /></td>
						</tr>';
					$tableRows[]='
						<tr class="bgColor5">
							<td colspan="3"><strong>'.htmlspecialchars($wizardItem['header']).'</strong></td>
						</tr>';
				} else {
					$tableLinks=array();

						// href URI for icon/title:
					$newRecordLink = 'index.php?'.$this->linkParams().$feParams.'&createNewRecord='.rawurlencode($this->parentRecord).$wizardItem['params'];

						// Icon:
					$iInfo = @getimagesize($wizardItem['icon']);
					$tableLinks[]='<a href="'.$newRecordLink.'"><img'.t3lib_iconWorks::skinImg($this->doc->backPath,$wizardItem['icon'],'').' alt="" /></a>';

						// Title + description:
					$tableLinks[]='<a href="'.$newRecordLink.'"><strong>'.htmlspecialchars($wizardItem['title']).'</strong><br />'.nl2br(htmlspecialchars(trim($wizardItem['description']))).'</a>';

						// Finally, put it together in a table row:
					$tableRows[]='
						<tr>
							<td valign="top">'.implode('</td>
							<td valign="top">',$tableLinks).'</td>
						</tr>';
					$counter++;
				}
			}
				// Add the wizard table to the content:
			$wizardCode .= $LANG->getLL('sel1',1).'<br /><br />

			<!--
				Content Element wizard table:
			-->
				<table border="0" cellpadding="1" cellspacing="2" id="typo3-ceWizardTable">
					'.implode('',$tableRows).'
				</table>';
			if($typo3GET['wizard']) // Added: if /mod1/index.php has been used as content element wizard, different header
				$selectType = $GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:createnewcontent_selectType');
			else	$selectType= $LANG->getLL('1_selectType');
			$this->content .= $this->doc->section($selectType, $wizardCode, 0, 1);
			if($frontEnd)
				$this->content .='<input type="hidden" name="frontEnd" value="1" />';

		} else {		// In case of no access:
			$this->content='';
			$this->content.=$this->doc->startPage($LANG->getLL('newContentElement'));
			$this->content.=$this->doc->header($LANG->getLL('newContentElement'));
			
			$this->content.=$this->doc->spacer(5);
		}
	}

}
?>
