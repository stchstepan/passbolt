<?php
/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SA (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SA (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         4.1.0
 */
// @codingStandardsIgnoreStart
use Cake\Log\Log;
use Migrations\AbstractMigration;
use Passbolt\Rbacs\Service\UiActions\UiActionsInsertDefaultsService;

class V410InsertUiActions extends AbstractMigration
{
    /**
     * Up
     *
     * @return void
     */
    public function up()
    {
        try {
            (new UiActionsInsertDefaultsService())->insertDefaultsIfNotExist();
        } catch (\Throwable $e) {
            Log::error('There was an error in V410InsertUiActions');
            Log::error($e->getMessage());
        }
    }
}
// @codingStandardsIgnoreEnd
