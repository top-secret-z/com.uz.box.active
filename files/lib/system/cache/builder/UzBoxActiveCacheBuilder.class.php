<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace wcf\system\cache\builder;

use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Caches the users with most activity points iaw conditions.
 */
class UzBoxActiveCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected $maxLifetime = 180;

    /**
     * @inheritDoc
     *
     * @throws SystemException
     */
    protected function rebuild(array $parameters): array
    {
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
                if ((int)$condition['userIsEnabled'] === 0) {
                    $conditionBuilder->add('activationCode > ?', [0]);
                }

                if ((int)$condition['userIsEnabled'] === 1) {
                    $conditionBuilder->add('activationCode = ?', [0]);
                }
            }

            if (isset($condition['groupIDs'])) {
                $conditionBuilder->add('userID IN (SELECT userID FROM wcf1_user_to_group WHERE groupID IN (?))', [$condition['groupIDs']]);
            }

            if (isset($condition['notGroupIDs'])) {
                $conditionBuilder->add('userID NOT IN (SELECT userID FROM wcf1_user_to_group WHERE groupID IN (?))', [$condition['notGroupIDs']]);
            }
        }

        // must have points
        $conditionBuilder->add('activityPoints > ?', [0]);

        // get users
        $userIDs = [];
        $sql = "SELECT userID FROM wcf1_user " . $conditionBuilder . " ORDER BY activityPoints DESC";

        $statement = WCF::getDB()->prepare($sql, $sqlLimit);
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
