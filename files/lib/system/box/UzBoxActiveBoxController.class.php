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
namespace wcf\system\box;

use wcf\system\cache\builder\UzBoxActiveCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Most active box controller.
 */
class UzBoxActiveBoxController extends AbstractDatabaseObjectListBoxController
{
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
    public function getLink(): string
    {
        if (MODULE_MEMBERS_LIST) {
            $parameters = 'sortField=activityPoints&sortOrder=DESC';

            return LinkHandler::getInstance()->getLink('MembersList', [], $parameters);
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    protected function getObjectList()
    {
        // get conditions as parameters for cache builder
        $parameters = [];
        foreach ($this->box->getConditions() as $condition) {
            $parameters[] = $condition->conditionData;
        }
        $parameters[] = ['limit' => $this->limit];

        return UzBoxActiveCacheBuilder::getInstance()->getData($parameters);
    }

    /**
     * @inheritDoc
     */
    protected function getTemplate()
    {
        return WCF::getTPL()->fetch('boxUzActive', 'wcf', [
            'boxUserList' => $this->objectList,
        ], true);
    }

    /**
     * @inheritDoc
     */
    public function hasContent()
    {
        if ($this->objectList === null) {
            $this->objectList = $this->getObjectList();
        }

        EventHandler::getInstance()->fireAction($this, 'hasContent');

        return $this->objectList !== null && \count($this->objectList) > 0;
    }

    /**
     * @inheritDoc
     */
    protected function loadContent()
    {
        $this->content = $this->getTemplate();
    }

    /**
     * @inheritDoc
     */
    public function hasLink()
    {
        return MODULE_MEMBERS_LIST == 1;
    }
}
