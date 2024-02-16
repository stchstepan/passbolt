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
use Passbolt\Rbacs\Service\Rbacs\RbacsInsertDefaultsService;

class V410InsertDefaultRbacsUiActions extends AbstractMigration
{
    /**
     * Up
     *
     * @thows \Exception if insertion fails
     * @return void
     */
    public function up()
    {
        try {
            (new RbacsInsertDefaultsService())->allowAllUiActionsForUsers();
        } catch (\Throwable $e) {
            Log::error('There was an error in V410InsertDefaultRbacsUiActions');
            Log::error($e->getMessage());
        }
    }
}
// @codingStandardsIgnoreEnd
