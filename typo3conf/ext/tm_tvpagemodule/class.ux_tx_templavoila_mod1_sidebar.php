<?php

/**
 * Submodule 'Sidebar' for the templavoila page module
 *
 * Note: This class is closely bound to the page module class and uses many variables and functions directly. After major modifications of
 *       the page module all functions of this sidebar should be checked to make sure that they still work.
 *
 * @author		Robert Lemke <robert@typo3.org>
 * @author		Tapio Markula
 * @package		TYPO3
 * @subpackage	tx_templavoila
 */
 
class ux_tx_templavoila_mod1_sidebar extends tx_templavoila_mod1_sidebar {
	
	var $modTSconfig;	// This module's TSconfig
	
	function render() {
		
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($pObj->id,'mod.web_txtemplavoilaM1');
		$this->modTSconfig['properties']['disableSidebarItems']="foo,goo";
		$typesForDisableSidebarItems = t3lib_div::trimExplode(',',$this->modTSconfig['properties']['disableSidebarItems'],1);
		$countRemovableItems=count($typesForDisableSidebarItems);
			
		for($i=0; $i< $countRemovableItems;$i++) {
				$this->removeItem($typesForDisableSidebarItems[$i]);			
			}				
		
		if (is_array ($this->sideBarItems) && count ($this->sideBarItems)) {
			uasort ($this->sideBarItems, array ($this, 'sortItemsCompare'));

				// Render content of each sidebar item:
			$index = 0;
			$numSortedSideBarItems = array();
			foreach ($this->sideBarItems as $itemKey => $sideBarItem) {
				$content = trim($sideBarItem['object']->{$sideBarItem['method']}($this->pObj));				
				if (!$sideBarItem['hideIfEmpty'] || $content != '') {
					$numSortedSideBarItems[$index] = $this->sideBarItems[$itemKey];
					$numSortedSideBarItems[$index]['content'] = $content;
					$index++;
					}
			}

				// Create the whole sidebar:
			switch ($this->position) {
				case 'left':
					$sideBar = '
						<!-- TemplaVoila Sidebar (left) begin -->

						<div id="tx_templavoila_mod1_sidebar-bar" style="height: 100%; width: '.$this->sideBarWidth.'px; margin: 0 4px 0 0; display:none;" class="bgColor-10">
							<div style="text-align:right;"><a href="#" onClick="tx_templavoila_mod1_sidebar_toggle();"><img '.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/minusbullet_list.gif','').' title="" alt="" /></a></div>
							'.$this->doc->getDynTabMenu($numSortedSideBarItems, 'TEMPLAVOILA:pagemodule:sidebar', 1, true).'
						</div>
						<div id="tx_templavoila_mod1_sidebar-showbutton" style="height: 100%; width: 18px; margin: 0 4px 0 0; display:block; " class="bgColor-10">
							<a href="#" onClick="tx_templavoila_mod1_sidebar_toggle();"><img '.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/plusbullet_list.gif','').' title="" alt="" /></a>
						</div>

						<script type="text/javascript">
						/*<![CDATA[*/

							tx_templavoila_mod1_sidebar_activate();

						/*]]>*/
						</script>

						<!-- TemplaVoila Sidebar end -->
					';
				break;

				case 'toprows':
					$sideBar = '
						<!-- TemplaVoila Sidebar (top) begin -->

						<div id="tx_templavoila_mod1_sidebar-bar" style="width:100%; margin-bottom: 10px;" class="bgColor-10">
							'.$this->doc->getDynTabMenu($numSortedSideBarItems, 'TEMPLAVOILA:pagemodule:sidebar', 1, true).'
						</div>

						<!-- TemplaVoila Sidebar end -->
					';
				break;

				case 'toptabs':
					$sideBar = '
						<!-- TemplaVoila Sidebar (top) begin -->

						<div id="tx_templavoila_mod1_sidebar-bar" style="width:100%; border-bottom: 1px solid black; margin-bottom: 10px;" class="bgColor-10">
							'.$this->doc->getDynTabMenu($numSortedSideBarItems, 'TEMPLAVOILA:pagemodule:sidebar', 1, FALSE, 100, 0, TRUE).'
						</div>

						<!-- TemplaVoila Sidebar end -->
					';
				break;

				default:
					$sideBar = '
						<!-- TemplaVoila Sidebar ERROR: Invalid position -->
					';
				break;
			}
			return $sideBar;
		}
		return FALSE;
	}
	
	
	
	/**
	 * Renders the "advanced functions" sidebar item.
	 *
	 * @param	object		&$pObj: Reference to the page object (the templavoila page module)
	 * @return	string		HTML output
	 * @access	public
	 */
	function renderItem_advancedFunctions(&$pObj) {
		global $LANG;
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($pObj->id,'mod.web_txtemplavoilaM1');
		
		$tableRows = array ('
			<tr class="bgColor4-20">
				<th colspan="2">&nbsp;</th>
			</tr>
		');

			// Render checkbox for showing hidden elements:
		// Added: possiblity to show hidden elements using TS Config for users/user groups - overrides default behaviout
		if(!isset($this->modTSconfig['properties']['tt_content_showHidden'])) {
			$tableRows[] = '
				<tr class="bgColor4">
					<td width="1%" nowrap="nowrap">
						'. t3lib_BEfunc::cshItem('_MOD_web_txtemplavoilaM1', 'advancedfunctions_showhiddenelements', $this->doc->backPath) .'
						'.$LANG->getLL('sidebar_advancedfunctions_labelshowhidden', 1).':
					</td>
					<td>'.t3lib_BEfunc::getFuncCheck($pObj->id,'SET[tt_content_showHidden]',$pObj->MOD_SETTINGS['tt_content_showHidden'],'','').'</td>
				</tr>
			';
			}

			// Render checkbox for showing outline:
		if ($GLOBALS['BE_USER']->isAdmin())	{
			$tableRows[] = '
				<tr class="bgColor4">
					<td width="1%" nowrap="nowrap">
						'. t3lib_BEfunc::cshItem('_MOD_web_txtemplavoilaM1', 'advancedfunctions_showoutline', $this->doc->backPath) .'
						'.$LANG->getLL('sidebar_advancedfunctions_labelshowoutline', 1).'
					:</td>
					<td>'.t3lib_BEfunc::getFuncCheck($pObj->id,'SET[showOutline]',$pObj->MOD_SETTINGS['showOutline'],'','').'</td>
				</tr>
			';
		}

			// Render cache menu:
		if ($pObj->id>0) {
			$cacheMenu = $this->doc->clearCacheMenu(intval($pObj->id), FALSE);
			if ($cacheMenu != '') {
					// Show cache functions only if they are available to the user
				$cshItem = t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'TCEforms_cacheSelector', $GLOBALS['BACK_PATH'],'', TRUE);
				$tableRows[] = '
					<tr class="bgColor4">
						<td nowrap="nowrap">'.$cshItem.' '.$LANG->getLL('sidebar_advancedfunctions_labelcachefunctions', 1).':</td>
						<td>'.$cacheMenu.'</td>
					</tr>
				';
			}
		}

		return (count ($tableRows)) ? '<table border="0" cellpadding="0" cellspacing="1" class="lrPadding" width="100%">'.implode ('', $tableRows).'</table>' : '';
	}
}
?>