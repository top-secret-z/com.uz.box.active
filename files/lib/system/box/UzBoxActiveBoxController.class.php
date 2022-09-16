<?php
namespace wcf\system\box;
use wcf\system\cache\builder\UzBoxActiveCacheBuilder;
use wcf\system\box\AbstractDatabaseObjectListBoxController;
use wcf\system\event\EventHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Most active box controller.
 *
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.box.active
 */
class UzBoxActiveBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	protected $conditionDefinition = 'com.uz.box.active.condition';
	
	// all positions
	
	/**
	 * @inheritDoc
	 */
	public $defaultLimit = 5;
	public $maximumLimit = 100;
	
	/**
	 * @inheritDoc
	 */
	protected $sortFieldLanguageItemPrefix = 'com.uz.box.active';
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		if (MODULE_MEMBERS_LIST) {
			$parameters = 'sortField=activityPoints&sortOrder=DESC';
			
			return LinkHandler::getInstance()->getLink('MembersList', [], $parameters);
		}
		
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {
		// get conditions as parameters for cache builder
		$parameters = [];
		foreach ($this->box->getConditions() as $condition) {
			$parameters[] = $condition->conditionData;
		}
		$parameters[] = ['limit' => $this->limit];
		
		$userList = UzBoxActiveCacheBuilder::getInstance()->getData($parameters);
		
		return $userList;
	
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		return WCF::getTPL()->fetch('boxUzActive', 'wcf', [
				'boxUserList' => $this->objectList
		], true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasContent() {
		if ($this->objectList === null) {
			$this->objectList = $this->getObjectList();
		}
		
		EventHandler::getInstance()->fireAction($this, 'hasContent');
		
		return ($this->objectList !== null && count($this->objectList) > 0);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		$this->content = $this->getTemplate();
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasLink() {
		return MODULE_MEMBERS_LIST == 1;
	}
}
