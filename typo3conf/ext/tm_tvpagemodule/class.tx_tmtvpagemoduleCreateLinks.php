<?php
class tx_tvpagemoduleCreateLinks {
	
	var $modTSconfig=array(); // mod.FE_BE related configurations
/*********** function greateLink starts ***************************************/
	
	/**
	 * this function create link content for used edit panel and edit icon links
	 *
	 * @param	string		$imagePath: image path
	 * @param	string		$imagename: image name
	 * @param	string		$classnamePostfix: postfix for classes used for text or images
	 * @param	string		$title: title attribute for images
	 * @param	string		$altTitle: alternative title attribute for images - not used
	 * @param	string		$label: possible link text
	 * @param	integer		$mode: link mode as integer
	 * @param	string		$altImage: alternative image
	 * @param	boolean		$newPaste: tests, if the link is new or paste link
	 * @param	boolean		$no5and6: tests, if the mode is not 5 or 6
	 * @param	boolean		$pastelinks: tests,
	 * @param	string		$pastemod: exeption handling for paste links 
	 * @param	boolean		$alignCenterEnabled: tests, if align="center" has been used or not
	 * @return	string		code between a tags
	 */
	function greateLink($imagePath,$imagename,$classnamePostfix,$title,$altTitle,$label,$mode,$altImage='',$newPaste=false,$no5and6=false,$pastelinks=false,$pastemod='',$alignCenterEnabled=false) {
		// Create CE specific buttons:
		/*
		editIconModeTemplaVoila = 0 // only icons
		editIconModeTemplaVoila = 1 // only text
		editIconModeTemplaVoila = 2 // icon left text right
		editIconModeTemplaVoila = 3 // text left icons right
		editIconModeTemplaVoila = 4 // icon left text right like in a button
		editIconModeTemplaVoila = 5 // SELECT-menu
		editIconModeTemplaVoila = 6 // special collection		
		*/
		
		if($altImage)
			$image='src="'.$altImage.'"';
		else	$image=t3lib_iconWorks::skinImg($imagePath,$imagename);
		
		if($altImage) // special set, where move up/down icons
			$mode = 0;
		if($no5and6 && ($mode==1 || $mode==5 || $mode==6)) // in certain situation selector boxes not at all available
			$mode = 1;
		
		if($alignCenterEnabled) // added because of a compatibility problem with 'skin_grey_2'
			$alignCenter = ' style="text-align: center; vertical-align: middle;"';
		
		$link1 = '<img class="iconMode button-'.$classnamePostfix.'" '.$image.' title="'.$title.'" alt="'.$title.'" border="0" />';
		$link2 = '<span class="textMode text-'.$classnamePostfix.'" title="'.$title.'">'.$label.'</span>';
		$link3 = '<span class="buttonMode text-'.$classnamePostfix.'" title="'.$title.'"><img class="button-'.$classnamePostfix.'" '.$image.' '.$alignCenter.' title="'.$title.'" alt="'.$title.'" border="0" /><span class="buttonModeText">'.$label.'</span></span>';
		$link4 = $altTitle; // selectorbox 
		$link5 = '<span class="textMode text-'.$classnamePostfix.'" title="'.$title.'">'.$label.'</span>'; // new and paste link 
		
		if($mode==1 || (($mode==6 || $mode==5) && $pastemod=='normal')) // paste buttons have exeptions
			$link=$link2;				
		elseif($mode==2)
			$link=$link1.$link2;
		elseif($mode==3)
			$link=$link2.$link1;					
		elseif($mode==4)
			$link=$link3;
		elseif($mode==5)
			$link=$link4;
		elseif($mode==6 && ($newPaste || $pastelinks)) // special handling for new and paste icons
			$link=$link5;
		elseif($mode==6)
			$link=$link4;
		else	$link=$link1;	
		return $link;
	}
	/**
	 * @param	integer		$mode: link mode as integer
	 * @param	string		$type: link layout mode setting correct class information
	 * @param	boolean		$newAndPasteLink: tests, if needed special handling for paste and new links
	 * @return	string		layout mode for links
	 */
	function lookMode($mode,$type='',$newAndPasteLink=false) {	
		
		/*
		editIconModeTemplaVoila = 0 // only icons
		editIconModeTemplaVoila = 1 // only text
		editIconModeTemplaVoila = 2 // icon left text right
		editIconModeTemplaVoila = 3 // text left icons right
		editIconModeTemplaVoila = 4 // icon left text right like in a button
		editIconModeTemplaVoila = 5 // SELECT-menu
		editIconModeTemplaVoila = 6 // special collection
		editIconModeTemplaVoila = 7 // docheaderbutton		
		*/
		
		if(($mode==5 || $mode==6) && $type=='normal' && $newAndPasteLink) // special for paste and new links
			$mode=' textMode';
		elseif($mode==1 || $type=='normal') // new and paste has in certain circumstances only this mode
			$mode=' textMode';					
		elseif($mode==2)
			$mode=' bothMode';					
		elseif($mode==3)
			$mode=' bothMode';					
		elseif($mode==4)
			$mode=' buttonMode';
		elseif($mode==5 || $mode==6)
			$mode='option';	
		elseif($mode==7)
			$mode='';
		else	$mode=' iconMode';
		return $mode;		
	}
/*********** function greateLink ends *****************************************/
}
?>
