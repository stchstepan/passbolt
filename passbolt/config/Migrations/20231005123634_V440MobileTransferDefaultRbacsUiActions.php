<?php
declare(strict_types=1);

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
 * @since         4.4.0
 */

use Cake\Log\Log;
use Migrations\AbstractMigration;
use Passbolt\Rbacs\Service\Rbacs\RbacsInsertDefaultsService;

class V440MobileTransferDefaultRbacsUiActions extends AbstractMigration
{
    /**
     * Up
     *
     * @throws \Exception if insertion fails
     * @return void
     */
    public function up()
    {
        try {
            (new RbacsInsertDefaultsService())->allowAllUiActionsForUsers();
        } catch (\Throwable $e) {
            Log::error('There was an error in V440MobileTransferDefaultRbacsUiActions');
            Log::error($e->getMessage());
        }
    }
}
