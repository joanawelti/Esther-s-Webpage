<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2006  Robert Lemke (robert@typo3.org)
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
 * $Id: class.tx_templavoila_mod1_clipboard.php 18146 2009-03-21 01:04:59Z steffenk $
 *
 * @author     Robert Lemke <robert@typo3.org>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   55: class tx_templavoila_mod1_clipboard
 *   72:     function init(&$pObj)
 *  116:     function element_getSelectButtons($elementPointer, $listOfButtons = 'copy,cut,ref')
 *  182:     function element_getPasteButtons($destinationPointer)
 *  246:     function sidebar_renderNonUsedElements()
 *  336:     function renderReferenceCount($uid)
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Submodule 'clipboard' for the templavoila page module
 *
 * @author		Robert Lemke <robert@typo3.org>
 * @package		TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_mod1_clipboard {

		// References to the page module object
	var $pObj;										// A pointer to the parent object, that is the templavoila page module script. Set by calling the method init() of this class.
	var $doc;										// A reference to the doc object of the parent object.

	/**
	 * Initializes the clipboard object. The calling class must make sure that the right locallang files are already loaded.
	 * This method is usually called by the templavoila page module.
	 *
	 * Also takes the GET variable "CB" and submits it to the t3lib clipboard class which handles all
	 * the incoming information and stores it in the user session.
	 *
	 * @param	$pObj:		Reference to the parent object ($this)
	 * @return	void
	 * @access	public
	 */
	function init(&$pObj) {
		global $LANG, $BACK_PATH;

			// Make local reference to some important variables:
		$this->pObj =& $pObj;
		$this->doc =& $this->pObj->doc;
		$this->extKey =& $this->pObj->extKey;
		$this->MOD_SETTINGS =& $this->pObj->MOD_SETTINGS;

			// Initialize the t3lib clipboard:
		$this->t3libClipboardObj = t3lib_div::makeInstance('t3lib_clipboard');
		$this->t3libClipboardObj->backPath = $BACK_PATH;
		$this->t3libClipboardObj->initializeClipboard();
		$this->t3libClipboardObj->lockToNormal();

			// Clipboard actions are handled:
		$CB = t3lib_div::_GP('CB');	// CB is the clipboard command array
		$this->t3libClipboardObj->setCmd($CB);		// Execute commands.

		if (isset ($CB['setFlexMode'])) {
			switch ($CB['setFlexMode']) {
				case 'copy' : $this->t3libClipboardObj->clipData['normal']['flexMode'] = 'copy'; break;
				case 'cut':  $this->t3libClipboardObj->clipData['normal']['flexMode'] = 'cut'; break;
				case 'ref': $this->t3libClipboardObj->clipData['normal']['flexMode'] = 'ref'; break;
				default: unset ($this->t3libClipboardObj->clipData['normal']['flexMode']); break;
			}
		}

		$this->t3libClipboardObj->cleanCurrent();	// Clean up pad
		$this->t3libClipboardObj->endClipboard();	// Save the clipboard content

			// Add a list of non-used elements to the sidebar:
			$this->pObj->sideBarObj->addItem('nonUsedElements', $this, 'sidebar_renderNonUsedElements', $LANG->getLL('nonusedelements'),30);
	}

	/**
	 * Renders the copy, cut and reference buttons for the element specified by the
	 * flexform pointer.
	 *
	 * @param	array		$elementPointer: Flex form pointer specifying the element we want to render the buttons for
	 * @param	string		$listOfButtons: A comma separated list of buttons which should be rendered. Possible values: 'copy', 'cut' and 'ref'
	 * @return	string		HTML output: linked images which act as copy, cut and reference buttons
	 * @access	public
	 */
	function element_getSelectButtons($elementPointer, $listOfButtons = 'copy,cut,ref') {
		global $LANG;

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

		$copyIcon = '<img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/clip_copy'.($clipActive_copy ? '_h':'').'.gif','').' title="'.$LANG->getLL ('copyrecord').'" border="0" alt="" />';
		$cutIcon = '<img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/clip_cut'.($clipActive_cut ? '_h':'').'.gif','').' title="'.$LANG->getLL ('cutrecord').'" border="0" alt="" />';
		$refIcon = '<img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,t3lib_extMgm::extRelPath('templavoila').'mod1/clip_ref'.($clipActive_ref ? '_h':'').'.gif','').' title="'.$LANG->getLL ('createreference').'" border="0" alt="" />';

		$removeElement = '&amp;CB[removeAll]=normal';
		$setElement = '&amp;CB[el]['.rawurlencode('tt_content|'.$elementRecord['uid']).']='.rawurlencode($this->pObj->apiObj->flexform_getStringFromPointer($elementPointer));
		$setElementRef = '&amp;CB[el]['.rawurlencode('tt_content|'.$elementRecord['uid']).']=1';

		$linkCopy = '<a href="index.php?'.$this->pObj->link_getParameters().'&amp;CB[setCopyMode]=1&amp;CB[setFlexMode]=copy'.($clipActive_copy ? $removeElement : $setElement).'">'.$copyIcon.'</a>';
		$linkCut = '<a href="index.php?'.$this->pObj->link_getParameters().'&amp;CB[setCopyMode]=0&amp;CB[setFlexMode]=cut'.($clipActive_cut ? $removeElement : $setElement).'">'.$cutIcon.'</a>';
		$linkRef = '<a href="index.php?'.$this->pObj->link_getParameters().'&amp;CB[setCopyMode]=1&amp;CB[setFlexMode]=ref'.($clipActive_ref ? $removeElement : $setElementRef).'">'.$refIcon.'</a>';

		$output =
			(t3lib_div::inList($listOfButtons, 'copy') ? $linkCopy : '').
			(t3lib_div::inList($listOfButtons, 'ref') ? $linkRef : '').
			(t3lib_div::inList($listOfButtons, 'cut') ? $linkCut : '');

		return $output;
	}

	/**
	 * Renders and returns paste buttons for the destination specified by the flexform pointer.
	 * The buttons are (or is) only rendered if a suitable element is found in the "normal" clipboard
	 * and if it is valid to paste it at the given position.
	 *
	 * @param	array		$destinationPointer: Flexform pointer defining the destination location where a possible element would be pasted.
	 * @return	string		HTML output: linked image(s) which act as paste button(s)
	 */
	function element_getPasteButtons($destinationPointer) {
		global $LANG, $BE_USER;

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
		$pasteAfterIcon = '<img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/clip_pasteafter.gif','').' style="text-align: center; vertical-align: middle;" hspace="1" vspace="5" border="0" title="'.$LANG->getLL ('pasterecord').'" alt="" />';
		$pasteSubRefIcon = '<img'.t3lib_iconWorks::skinImg('clip_pastesubref.gif','').' style="text-align: center; vertical-align: middle;" hspace="1" vspace="5" border="0" title="'.$LANG->getLL ('pastefce_andreferencesubs').'" alt="" />';

		$sourcePointerString = $this->pObj->apiObj->flexform_getStringFromPointer ($clipboardElementPointer);
		$destinationPointerString = $this->pObj->apiObj->flexform_getStringFromPointer ($destinationPointer);

			// FCEs with sub elements have two different paste icons, normal elements only one:
		if ($pasteMode == 'copy' && $clipboardElementHasSubElements) {
			$output  = '<a href="index.php?'.$this->pObj->link_getParameters().'&amp;CB[removeAll]=normal&amp;pasteRecord=copy&amp;source='.rawurlencode($sourcePointerString).'&amp;destination='.rawurlencode($destinationPointerString).'">'.$pasteAfterIcon.'</a>';
			$output .= '<a href="index.php?'.$this->pObj->link_getParameters().'&amp;CB[removeAll]=normal&amp;pasteRecord=copyref&amp;source='.rawurlencode($sourcePointerString).'&amp;destination='.rawurlencode($destinationPointerString).'">'.$pasteSubRefIcon.'</a>';
		} else {
			$output = '<a href="index.php?'.$this->pObj->link_getParameters().'&amp;CB[removeAll]=normal&amp;pasteRecord='.$pasteMode.'&amp;source='.rawurlencode($sourcePointerString).'&amp;destination='.rawurlencode($destinationPointerString).'">'.$pasteAfterIcon.'</a>';
		}

		return $output;
	}

	/**
	 * Displays a list of local content elements on the page which were NOT used in the hierarchical structure of the page.
	 *
	 * @param	$pObj:		Reference to the parent object ($this)
	 * @return	string		HTML output
	 * @access	protected
	 */
	function sidebar_renderNonUsedElements() {
		global $LANG, $TYPO3_DB, $BE_USER;

		$output = '';
		$elementRows = array();
		$usedUids = array_keys($this->pObj->global_tt_content_elementRegister);
		$usedUids[] = 0;
		$pid = $this->pObj->id;	// If workspaces should evaluated non-used elements it must consider the id: For "element" and "branch" versions it should accept the incoming id, for "page" type versions it must be remapped (because content elements are then related to the id of the offline version)

		$res = $TYPO3_DB->exec_SELECTquery (
			t3lib_BEfunc::getCommonSelectFields('tt_content','',array('uid', 'header', 'bodytext', 'sys_language_uid')),
			'tt_content',
			'pid='.intval($pid).' '.
				'AND uid NOT IN ('.implode(',',$usedUids).') '.
				'AND t3ver_state!=1'.
				t3lib_BEfunc::deleteClause('tt_content').
				t3lib_BEfunc::versioningPlaceholderClause('tt_content'),
			'',
			'uid'
		);

		$this->deleteUids = array();	// Used to collect all those tt_content uids with no references which can be deleted
		while(false !== ($row = $TYPO3_DB->sql_fetch_assoc($res)))	{
			$elementPointerString = 'tt_content:'.$row['uid'];

				// Prepare the language icon:
			$languageLabel = htmlspecialchars ($this->pObj->allAvailableLanguages[$row['sys_language_uid']]['title']);
			$languageIcon = $this->pObj->allAvailableLanguages[$row['sys_language_uid']]['flagIcon'] ? '<img src="'.$this->pObj->allAvailableLanguages[$row['sys_language_uid']]['flagIcon'].'" title="'.$languageLabel.'" alt="'.$languageLabel.'"  />' : ($languageLabel && $row['sys_language_uid'] ? '['.$languageLabel.']' : '');

				// Prepare buttons:
			$cutButton = $this->element_getSelectButtons($elementPointerString, 'ref');
			$recordIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, t3lib_iconWorks::getIcon('tt_content', $row),'').' width="18" height="16" border="0" title="[tt_content:'.$row['uid'].'" alt="" />';
			$recordButton = $this->pObj->doc->wrapClickMenuOnIcon($recordIcon, 'tt_content', $row['uid'], 1, '&callingScriptId='.rawurlencode($this->pObj->doc->scriptID), 'new,copy,cut,pasteinto,pasteafter,delete');



			$elementRows[] = '
				<tr class="bgColor4">
					<td style="width:1%">'.
						$cutButton.
					'</td>
					<td style="width:1%;">'.$languageIcon.'</td>
					<td style="width:1%;">'.$this->renderReferenceCount($row['uid']).'</td>
					<td>'.$recordButton.
						htmlspecialchars(' '.t3lib_div::fixed_lgd_cs(trim(strip_tags($row['header'].($row['header'] && $row['bodytext'] ? ' - ' : '').$row['bodytext'])),100)).'
					</td>
				</tr>
			';
		}

		if (count ($elementRows)) {

				// Control for deleting all deleteable records:
			$deleteAll = '';
			if (count($this->deleteUids) && 0===$BE_USER->workspace)	{
				$params = '';
				foreach($this->deleteUids as $deleteUid)	{
					$params.= '&cmd[tt_content]['.$deleteUid.'][delete]=1';
				}
				$label = $LANG->getLL('rendernonusedelements_deleteall');
				$deleteAll = '<a href="#" onclick="'.htmlspecialchars('jumpToUrl(\''.$this->doc->issueCommand($params,'').'\');').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/garbage.gif','width="11" height="12"').' title="'.htmlspecialchars($label).'" alt="" />'.
						htmlspecialchars($label).
						'</a>';
			}

				// Create table and header cell:
			$output = '
				<table border="0" cellpadding="0" cellspacing="1" width="100%" class="lrPadding">
					<tr class="bgColor4-20">
						<td colspan="5">'.$LANG->getLL('inititemno_elementsNotBeingUsed','1').':</td>
					</tr>
					'.implode('',$elementRows).'
					<tr class="bgColor4">
						<td colspan="5">'.$deleteAll.'</td>
					</tr>
				</table>
			';
		}
		return $output;

	}

	/**
	 * Render a reference count in form of an HTML table for the content
	 * element specified by $uid.
	 *
	 * @param	integer		$uid: Element record Uid
	 * @return	string		HTML-table
	 * @access	protected
	 */
	function renderReferenceCount($uid)	{
		global $TYPO3_DB, $BE_USER, $LANG;

		$rows = $TYPO3_DB->exec_SELECTgetRows(
			'*',
			'sys_refindex',
			'ref_table='.$TYPO3_DB->fullQuoteStr('tt_content','sys_refindex').
				' AND ref_uid='.intval($uid).
				' AND deleted=0'
		);

			// Compile information for title tag:
		$infoData = array();
		if (is_array($rows))	{
			foreach($rows as $row)	{
				$infoData[] = $row['tablename'].':'.$row['recuid'].':'.$row['field'];
			}
		}

		if (count($infoData))	{
			return '<a href="#" onclick="'.htmlspecialchars('top.launchView(\'tt_content\', \''.$uid.'\'); return false;').'" title="'.htmlspecialchars(t3lib_div::fixed_lgd_cs(implode(' / ',$infoData),100)).'">Ref: '.count($infoData).'</a>';
		} elseif (0===$BE_USER->workspace) {
			$this->deleteUids[] = $uid;
			$params = '&cmd[tt_content]['.$uid.'][delete]=1';
			return '<a href="#" onclick="'.htmlspecialchars('jumpToUrl(\''.$this->doc->issueCommand($params,'').'\');').'">'.
					'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('renderreferencecount_delete',1).'" alt="" />'.
					'</a>';
		} else {
			return '';
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/class.tx_templavoila_mod1_clipboard.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/class.tx_templavoila_mod1_clipboard.php']);
}

?>
