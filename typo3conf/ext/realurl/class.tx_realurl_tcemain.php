<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Dmitry Dulepov (dmitry@typo3.org)
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
 * $Id: class.tx_realurl_tcemain.php 32382 2010-04-20 12:04:05Z dmitry $
 */

/**
 * TCEmain hook to update path cache if page is renamed
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 */
class tx_realurl_tcemain {

	/**
	 * RealURL configuration for the current host
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Checks if the update table can affect cache entries
	 *
	 * @param string $tableName
	 * @return boolean
	 */
	protected static function isTableOfInterest($tableName) {
		return ($tableName == 'pages' || $tableName == 'pages_language_overlay');
	}

	/**
	 * Clears URL decode and encode caches for the given page
	 *
	 * @param $pageId
	 * @return void
	 */
	protected function clearOtherCaches($pageId) {
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_urldecodecache',
			'page_id=' . $pageId);
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_urlencodecache',
			'page_id=' . $pageId);
	}

	/**
	 * Expires record in the patch cache
	 *
	 * @param int $pageId
	 * @param int $languageId
	 * @return void
	 */
	protected function expirePathCache($pageId, $languageId) {
		$expirationTime = $this->getExpirationTime();
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_realurl_pathcache',
			'page_id=' . $pageId . ' AND language_id=' . $languageId . ' AND expire=0',
			array(
				'expire' => $expirationTime
			));
	}

	/**
	 * Fetches RealURl configuration for the given page
	 *
	 * @param int $pageId
	 * @return void
	 */
	protected function fetchRealURLConfiguration($pageId) {
		$rootLine = t3lib_BEfunc::BEgetRootLine($pageId);
		$rootPageId = $rootLine[1]['uid'];
		foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'] as $config) {
			if (is_array($config) && $config['pagePath']['rootpage_id'] == $rootPageId) {
				$this->config = $config;
				return;
			}
		}
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT'])) {
			$this->config = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT'];
		}
		else {
			$this->config = array();
		}
	}

	/**
	 * Obtains expiration time for the path cache records
	 *
	 * @return int
	 */
	protected function getExpirationTime() {
		$timeOffset = (isset($this->config['pagePath']['expireDays']) ? $this->config['pagePath']['expireDays'] : 60) * 24 * 3600;
		$date = getdate(time() + $timeOffset);
		return mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
	}

	/**
	 * Obtains real page id and language from the table name and passed id of the record in the table.
	 *
	 * @param $tableName
	 * @param $id
	 * @return array First member is page id, second is language
	 */
	protected static function getPageData($tableName, $id) {
		if ($tableName == 'pages_language_overlay') {
			$result = self::getInfoFromOverlayPid($id);
		}
		else {
			$result = array($id, 0);
		}
		return $result;
	}

	/**
	 * Retrieves field list to check for modification
	 *
	 * @param string $tableName
	 * @return	array
	 */
	protected function getFieldList($tableName) {
		if ($tableName == 'pages_language_overlay') {
			$fieldList = TX_REALURL_SEGTITLEFIELDLIST_PLO;
		}
		else {
			if (isset($this->config['pagePath']['segTitleFieldList'])) {
				$fieldList = $this->config['pagePath']['segTitleFieldList'];
			}
			else {
				$fieldList = TX_REALURL_SEGTITLEFIELDLIST_DEFAULT;
			}
		}
		return array_unique(t3lib_div::trimExplode(',', $fieldList, true));
	}

	/**
	 * Retrieves real page id given its overlay id
	 *
	 * @param	int		$pid	Page id
	 * @return	array		Array with two members: real page uid and sys_language_uid
	 */
	protected static function getInfoFromOverlayPid($pid) {
		list($rec) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('pid,sys_language_uid',
			'pages_language_overlay', 'uid=' . $pid);
		return array($rec['pid'], $rec['sys_language_uid']);
	}

	/**
	 * TCEmain hook to expire old records and add new ones
	 *
	 * @param string $status 'new' (ignoring) or 'update'
	 * @param string $tableName
	 * @param int $recordId
	 * @param array $databaseData
	 * @return void
	 */
	public function processDatamap_afterDatabaseOperations($status, $tableName, $recordId, array $databaseData) {
		if ($status == 'update' && t3lib_div::testInt($recordId)) {
			list($pageId, $languageId) = $this->getPageData($tableName, $recordId);
			$this->fetchRealURLConfiguration($pageId);
			if ($this->shouldFixCaches($tableName, $databaseData)) {
				$this->expirePathCache($pageId, $languageId);
				$this->clearOtherCaches($pageId);
			}
			// TODO Handle changes to tx_realurl_exclude recursively
		}
	}

	/**
	 * Checks if we need to fix caches
	 *
	 * @param string $tableName
	 * @param array $databaseData
	 * @return boolean
	 */
	protected function shouldFixCaches($tableName, array $databaseData) {
		$result = false;
		if (self::isTableOfInterest($tableName)) {
			$interestingFields = $this->getFieldList($tableName);
			$result = count(array_intersect($interestingFields, array_keys($databaseData))) > 0;
		}
		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurl/class.tx_realurl_tcemain.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurl/class.tx_realurl_tcemain.php']);
}

?>