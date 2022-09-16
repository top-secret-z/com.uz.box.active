<?php
namespace wcf\system\cache\builder;
use wcf\data\user\User;
use wcf\system\cache\builder\AbstractCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the users with most activity points iaw conditions.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.box.active
 */
class UzBoxActiveCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $maxLifetime = 180;
	
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		/**
		 * preset data
		 */
		$sqlLimit = 0;
		$conditionBuilder = new PreparedStatementConditionBuilder();
		
		foreach ($parameters as $condition) {
			if (isset($condition['limit'])) {
				$sqlLimit = $condition['limit'];
			}
			
			if (isset($condition['lastActivity'])) {
				$conditionBuilder->add('lastActivityTime > ?', [TIME_NOW - $condition['lastActivity'] * 86400]);
			}
			
			if (isset($condition['userIsBanned'])) {
				$conditionBuilder->add('banned = ?', [$condition['userIsBanned']]);
			}
			
			if (isset($condition['userIsEnabled'])) {
				if ($condition['userIsEnabled'] == 0) $conditionBuilder->add('activationCode > ?', [0]);
				if ($condition['userIsEnabled'] == 1) $conditionBuilder->add('activationCode = ?', [0]);
			}
			
			if (isset($condition['groupIDs'])) {
				$conditionBuilder->add('userID IN (SELECT userID FROM wcf'.WCF_N.'_user_to_group WHERE groupID IN (?))', [$condition['groupIDs']]);
			}
			
			if (isset($condition['notGroupIDs'])) {
				$conditionBuilder->add('userID NOT IN (SELECT userID FROM wcf'.WCF_N.'_user_to_group WHERE groupID IN (?))', [$condition['notGroupIDs']]);
			}
		}
		
		// must have points
		$conditionBuilder->add('activityPoints > ?', [0]);
		
		// get users
		$userIDs = [];
		$sql = "SELECT		userID
				FROM		wcf".WCF_N."_user
				".$conditionBuilder."
				ORDER BY activityPoints DESC";
		
		$statement = WCF::getDB()->prepareStatement($sql, $sqlLimit);
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) {
			$userIDs[] = $row['userID'];
		}
		
		$users = [];
		if (!empty($userIDs)) {
			foreach ($userIDs as $userID) {
				$user = UserProfileRuntimeCache::getInstance()->getObject($userID);
				$users[] = $user;
			}
		}
		
		return $users;
	}
}
