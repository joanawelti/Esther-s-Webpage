<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2007  Robert Lemke (robert@typo3.org)
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
 * Submodule 'clipboard' for the templavoila page module
 *
 * $Id: class.tx_templavoila_mod1_clipboard.php,v 1.10 2006/04/05 08:26:29 robert_typo3 Exp $
 *
 * @author     Robert Lemke <robert@typo3.org>
 */


/**
 * Submodule 'clipboard' for the templavoila page module
 *
 * @author		Robert Lemke <robert@typo3.org>
 * @author		Tapio Markula
 * @package		TYPO3
 * @subpackage	tx_templavoila
 */

require_once(t3lib_extMgm::extPath('tm_tvpagemodule').'class.tx_tmtvpagemoduleCreateLinks.php');

class ux_tx_templavoila_mod1_clipboard extends tx_templavoila_mod1_clipboard {

	#var $modTSconfig=array();
	#var $disableEditViewEditingButtons=array();
	 
	 /**
	 * Extra function for disabling features
	 *
	 * @return	void		doesn't return anything because just resets parts of the class variable $this
	 */
	 function confpropertiesButtonDisabling(){
		global $BE_USER;
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id,'mod.web_txtemplavoilaM1');
		$typesForDisableEditViewEditingButtons = t3lib_div::trimExplode(',',strtolower($this->modTSconfig['properties']['disableEditViewEditingButtons']),1);
		if(isset($typesForDisableEditViewEditingButtons) && is_array($typesForDisableEditViewEditingButtons))
		 	$this->disableEditViewEditingButtons = array_flip($typesForDisableEditViewEditingButtons);
		if(array_key_exists('editIconModeTemplaVoila',$BE_USER->uc) && $BE_USER->uc['editIconModeTemplaVoila']!='' && !$this->modTSconfig['properties']['disableUserSetup'])
			$this->modTSconfig['properties']['editIconModeTemplaVoila'] = $BE_USER->uc['editIconModeTemplaVoila'];
	 }

	/**
	 * Renders the copy, cut and reference buttons for the element specified by the
	 * flexform pointer.
	 *
	 * @param	array		$elementPointer: Flex form pointer specifying the element we want to render the buttons for
	 * @param	string		$listOfButtons: A comma separated list of buttons which should be rendered. Possible values: 'copy', 'cut' and 'ref'
	 * @param	boolean		$noSidebar: tests, if relates with sidebar or not
	 * @param	boolean		$useSet: tests, if used $this->pObj->linkSet as reference to the parent object or not
	 * @return	string		HTML output: linked images which act as copy, cut and reference buttons
	 * @access public
	 */
	function element_getSelectButtons($elementPointer,$listOfButtons='copy,cut,ref',$noSidebar=0,$useSet=false) {
		$this->confpropertiesButtonDisabling();

		$clipActive_copy = $clipActive_cut = $clipActive_ref = FALSE;
		if (!$elementPointer = $this->pObj->apiObj->flexform_getValidPointer($elementPointer)) return '';
		$elementRecord = $this->pObj->apiObj->flexform_getRecordByPointer($elementPointer);

			// Fetch the element from the "normal" clipboard (if any) and set the button states accordingly:
		if (is_array ($this->t3libClipboardObj->clipData['normal']['el'])) {
			reset ($this->t3libClipboardObj->clipData['normal']['el']);
			list ($clipboardElementTableAndUid, $clipboardElementPointerString) = each ($this->t3libClipboardObj->clipData['normal']['el']);
			$clipboardElementPointer = $this->pObj->apiObj->flexform_getValidPointer ($clipboardElementPointerString);

				// If we have no flexform reference pointing to the element, we create a short flexform pointer pointing to the record directly:
			if (!is_array($clipboardElementPointer)) {
				list ($clipboardElementTable, $clipboardElementUid) = explode ('|',$clipboardElementTableAndUid);

				$clipboardElementPointer = array (
					'table' => 'tt_content',
					'uid' => $clipboardElementUid
				);
				$pointToTheSameRecord = ($elementRecord['uid'] == $clipboardElementUid);
			} else {
				unset ($clipboardElementPointer['targetCheckUid']);
				unset ($elementPointer['targetCheckUid']);
				$pointToTheSameRecord = ($clipboardElementPointer == $elementPointer);
			}

				// Set whether the current element is selected for copy/cut/reference or not:
			if ($pointToTheSameRecord) {
				$selectMode = isset ($this->t3libClipboardObj->clipData['normal']['flexMode']) ? $this->t3libClipboardObj->clipData['normal']['flexMode'] : ($this->t3libClipboardObj->clipData['normal']['mode'] == 'copy' ? 'copy' : 'cut');
				$clipActive_copy = ($selectMode == 'copy');
				$clipActive_cut = ($selectMode == 'cut');
				$clipActive_ref = ($selectMode == 'ref');
			}

		}
		if($useSet) {
			$mode = tx_tvpagemoduleCreateLinks::lookMode($this->modTSconfig['properties']['editIconModeTemplaVoila']);
			$linkMode=$this->modTSconfig['properties']['editIconModeTemplaVoila'];
			}
		if(!isset($this->disableEditViewEditingButtons['copy']))
			$copyIcon=tx_tvpagemoduleCreateLinks::greateLink($this->pObj->doc->backPath,'gfx/clip_copy'.($clipActive_copy ? '_h':'').'.gif','copy',$GLOBALS['LANG']->getLL ('copyrecord'),$GLOBALS['LANG']->getLL ('copyrecord'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_copy'),$linkMode);
		if(!isset($this->disableEditViewEditingButtons['cut']))
			$cutIcon=tx_tvpagemoduleCreateLinks::greateLink($this->pObj->doc->backPath,'gfx/clip_cut'.($clipActive_cut ? '_h':'').'.gif','cut',$GLOBALS['LANG']->getLL ('cutrecord'),$GLOBALS['LANG']->getLL ('cutrecord'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_cut'),$linkMode);
		if(!isset($this->disableEditViewEditingButtons['ref']))
			$refIcon=tx_tvpagemoduleCreateLinks::greateLink($this->pObj->doc->backPath,t3lib_extMgm::extRelPath('templavoila').'mod1/clip_ref'.($clipActive_ref ? '_h':'').'.gif','ref',$GLOBALS['LANG']->getLL ('createreference'),$GLOBALS['LANG']->getLL ('createreference'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_ref'),$linkMode);

		$removeElement = '&amp;CB[removeAll]=normal';
		$setElement = '&amp;CB[el]['.rawurlencode('tt_content|'.$elementRecord['uid']).']='.rawurlencode($this->pObj->apiObj->flexform_getStringFromPointer($elementPointer));
		$setElementRef = '&amp;CB[el]['.rawurlencode('tt_content|'.$elementRecord['uid']).']=1';

		if(!isset($this->disableEditViewEditingButtons['copy']) && (t3lib_div::inList($listOfButtons, 'copy'))) {
			if($mode=='option')
				$this->pObj->linkSet['copy'] = '<option value="index.php?'.$this->pObj->link_getParameters().'&amp;CB[setCopyMode]=1&amp;CB[setFlexMode]=copy'.($clipActive_copy ? $removeElement : $setElement).'">'.$copyIcon.'</option>';
			else	$this->pObj->linkSet['copy'] = '<a class="commandLink commandLink-copy'.$mode.'" href="index.php?'.$this->pObj->link_getParameters().'&amp;CB[setCopyMode]=1&amp;CB[setFlexMode]=copy'.($clipActive_copy ? $removeElement : $setElement).'">'.$copyIcon.'</a>';
			}
		if(!isset($this->disableEditViewEditingButtons['cut']) && (t3lib_div::inList($listOfButtons, 'cut'))) {
			if($mode=='option')
				$this->pObj->linkSet['cut'] = '<option value="index.php?'.$this->pObj->link_getParameters().'&amp;CB[setCopyMode]=0&amp;CB[setFlexMode]=cut'.($clipActive_cut ? $removeElement : $setElement).'">'.$cutIcon.'</option>';
			else	$this->pObj->linkSet['cut'] = '<a class="commandLink commandLink-cut'.$mode.'"  href="index.php?'.$this->pObj->link_getParameters().'&amp;CB[setCopyMode]=0&amp;CB[setFlexMode]=cut'.($clipActive_cut ? $removeElement : $setElement).'">'.$cutIcon.'</a>';
			}
		if(!isset($this->disableEditViewEditingButtons['ref']) && (t3lib_div::inList($listOfButtons, 'ref'))) {
			if($mode=='option' && $noSidebar)
				$this->pObj->linkSet['ref'] = '<option value="index.php?'.$this->pObj->link_getParameters().'&amp;CB[setCopyMode]=1&amp;CB[setFlexMode]=ref'.($clipActive_ref ? $removeElement : $setElementRef).'">'.$refIcon.'</option>';
			else	$this->pObj->linkSet['ref'] = '<a class="commandLink commandLink-ref'.$mode.'" href="index.php?'.$this->pObj->link_getParameters().'&amp;CB[setCopyMode]=1&amp;CB[setFlexMode]=ref'.($clipActive_ref ? $removeElement : $setElementRef).'">'.$refIcon.'</a>';
			}
		if(!$useSet){
			if(t3lib_div::inList($listOfButtons, 'copy'))
				$buttons .= $this->pObj->linkSet['copy'];
			if(t3lib_div::inList($listOfButtons, 'cut'))
				$buttons .= $this->pObj->linkSet['cut'];
			if(t3lib_div::inList($listOfButtons, 'ref'))
				$buttons .= $this->pObj->linkSet['ref'];
			return	$buttons;
			}
		else 	return;
	}

	/**
	 * Renders and returns paste buttons for the destination specified by the flexform pointer.
	 * The buttons are (or is) only rendered if a suitable element is found in the "normal" clipboard
	 * and if it is valid to paste it at the given position.
	 *
	 * @param	array		$destinationPointer: Flexform pointer defining the destination location where a possible element would be pasted.
	 * @param	string		$type: link type (use option elements or normal links)
	 * @return	string		HTML output: linked image(s) which act as paste button(s)
	 */
	function element_getPasteButtons($destinationPointer,$type='') {
		global $LANG;
		$this->confpropertiesButtonDisabling();

		$origDestinationPointer = $destinationPointer;
		if (!$destinationPointer = $this->pObj->apiObj->flexform_getValidPointer($destinationPointer)) return '';
		if (!is_array ($this->t3libClipboardObj->clipData['normal']['el'])) return '';

		reset ($this->t3libClipboardObj->clipData['normal']['el']);
		list ($clipboardElementTableAndUid, $clipboardElementPointerString) = each ($this->t3libClipboardObj->clipData['normal']['el']);
		$clipboardElementPointer = $this->pObj->apiObj->flexform_getValidPointer ($clipboardElementPointerString);

			// If we have no flexform reference pointing to the element, we create a short flexform pointer pointing to the record directly:
		list ($clipboardElementTable, $clipboardElementUid) = explode ('|',$clipboardElementTableAndUid);
		if (!is_array($clipboardElementPointer)) {
			if ($clipboardElementTable != 'tt_content') return '';

			$clipboardElementPointer = array (
				'table' => 'tt_content',
				'uid' => $clipboardElementUid
			);
		}

			// If the destination element is already a sub element of the clipboard element, we mustn't show any paste icon:
		$destinationRecord = $this->pObj->apiObj->flexform_getRecordByPointer($destinationPointer);
		$clipboardElementRecord = $this->pObj->apiObj->flexform_getRecordByPointer($clipboardElementPointer);
		$dummyArr = array();
		$clipboardSubElementUidsArr = $this->pObj->apiObj->flexform_getListOfSubElementUidsRecursively ('tt_content', $clipboardElementRecord['uid'], $dummyArr);
		$clipboardElementHasSubElements = count($clipboardSubElementUidsArr) > 0;

		if ($clipboardElementHasSubElements) {
			if (array_search ($destinationRecord['uid'], $clipboardSubElementUidsArr) !== FALSE) {
				return '';
			}
			if ($origDestinationPointer['uid'] == $clipboardElementUid) {
				return '';
			}
		}

			// Prepare the ingredients for the different buttons:
		$pasteMode = isset ($this->t3libClipboardObj->clipData['normal']['flexMode']) ? $this->t3libClipboardObj->clipData['normal']['flexMode'] : ($this->t3libClipboardObj->clipData['normal']['mode'] == 'copy' ? 'copy' : 'cut');

		$mode = tx_tvpagemoduleCreateLinks::lookMode($this->modTSconfig['properties']['editIconModeTemplaVoila'],$type,true);
		$pasteAfterIcon = tx_tvpagemoduleCreateLinks::greateLink($this->pObj->doc->backPath,'gfx/clip_pasteafter.gif','paste',$LANG->getLL ('pasterecord'),$LANG->getLL ('pasterecord'),$GLOBALS['LANG']->sL('LLL:EXT:tm_tvpagemodule/locallang_extra.php:text_paste'),$this->modTSconfig['properties']['editIconModeTemplaVoila'],'',false,false,true,$type);
		$pasteSubRefIcon = tx_tvpagemoduleCreateLinks::greateLink($this->pObj->doc->backPath,'gfx/clip_pastesubref.gif','paste',$LANG->getLL ('pastefce_andreferencesubs'),$LANG->getLL ('pastefce_andreferencesubs'),$LANG->getLL ('pastefce_andreferencesubs'),$this->modTSconfig['properties']['editIconModeTemplaVoila'],'',false,false,true,$type);

		$sourcePointerString = $this->pObj->apiObj->flexform_getStringFromPointer ($clipboardElementPointer);
		$destinationPointerString = $this->pObj->apiObj->flexform_getStringFromPointer ($destinationPointer);

			// FCEs with sub elements have two different paste icons, normal elements only one:
		if ($pasteMode == 'copy' && $clipboardElementHasSubElements) {
			if($mode=='option' && $type!='normal'){
				$output  = '<option value="index.php?'.$this->pObj->link_getParameters().'&amp;CB[removeAll]=normal&amp;pasteRecord=copy&amp;source='.rawurlencode($sourcePointerString).'&amp;destination='.rawurlencode($destinationPointerString).'">'.$pasteAfterIcon.'</option>';
				$output .= '<option value="index.php?'.$this->pObj->link_getParameters().'&amp;CB[removeAll]=normal&amp;pasteRecord=copyref&amp;source='.rawurlencode($sourcePointerString).'&amp;destination='.rawurlencode($destinationPointerString).'">'.$pasteSubRefIcon.'</option>';
				}
			else	{
				$output  = '<a class="commandLink commandLink-paste'.$mode.'"  href="index.php?'.$this->pObj->link_getParameters().'&amp;CB[removeAll]=normal&amp;pasteRecord=copy&amp;source='.rawurlencode($sourcePointerString).'&amp;destination='.rawurlencode($destinationPointerString).'">'.$pasteAfterIcon.'</a>';
				$output .= '<a class="commandLink commandLink-paste'.$mode.'" href="index.php?'.$this->pObj->link_getParameters().'&amp;CB[removeAll]=normal&amp;pasteRecord=copyref&amp;source='.rawurlencode($sourcePointerString).'&amp;destination='.rawurlencode($destinationPointerString).'">'.$pasteSubRefIcon.'</a>';
				}
			}
		else	{
			if($mode=='option' && $type!='normal')
				$output = '<option value="index.php?'.$this->pObj->link_getParameters().'&amp;CB[removeAll]=normal&amp;pasteRecord='.$pasteMode.'&amp;source='.rawurlencode($sourcePointerString).'&amp;destination='.rawurlencode($destinationPointerString).'">'.$pasteAfterIcon.'</option>';
			else	$output = '<a class="commandLink commandLink-paste'.$mode.'" href="index.php?'.$this->pObj->link_getParameters().'&amp;CB[removeAll]=normal&amp;pasteRecord='.$pasteMode.'&amp;source='.rawurlencode($sourcePointerString).'&amp;destination='.rawurlencode($destinationPointerString).'">'.$pasteAfterIcon.'</a>';
			}
		return $output;
	}
}
?>
