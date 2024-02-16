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
 * @since         3.6.0
 */

namespace App\Test\TestCase\Controller\Users;

use App\Test\Factory\ProfileFactory;
use App\Test\Factory\RoleFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;
use App\Test\Lib\Model\GroupsUsersModelTrait;
use App\Test\Lib\Utility\PaginationTestTrait;
use Cake\I18n\FrozenDate;

class UsersIndexControllerOrderTest extends AppIntegrationTestCase
{
    use GroupsUsersModelTrait;
    use PaginationTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        RoleFactory::make()->guest()->persist();
    }

    public function testUsersIndexController_Success_OrderByUsername(): void
    {
        UserFactory::make(5)->user()->persist();

        $this->logInAsUser();

        $this->getJson('/users.json?order=User.username');
        $this->assertSuccess();
        $this->assertBodyContentIsSorted('username');

        $this->getJson('/users.json?order[]=User.username DESC');
        $this->assertSuccess();
        $this->assertBodyContentIsSorted('username', 'desc');
    }

    public function testUsersIndexController_Success_OrderByFirstName(): void
    {
        ProfileFactory::make($this->getArrayOfDistinctRandomStrings(5, 'first_name'))
            ->with('Users', UserFactory::make()->user()->without('Profiles'))
            ->persist();

        $this->logInAsUser();

        $this->getJson('/users.json?order[]=Profile.first_name');
        $this->assertSuccess();
        $this->assertBodyContentIsSorted('profile.first_name');

        $this->getJson('/users.json?order=Profile.first_name DESC');
        $this->assertSuccess();
        $this->assertBodyContentIsSorted('profile.first_name', 'desc');
    }

    public function testUsersIndexController_Success_OrderByLastName(): void
    {
        ProfileFactory::make($this->getArrayOfDistinctRandomStrings(5, 'last_name'))
            ->with('Users', UserFactory::make()->user()->without('Profiles'))
            ->persist();

        $this->logInAsUser();

        $this->getJson('/users.json?order=Profile.last_name');
        $this->assertSuccess();
        $this->assertBodyContentIsSorted('profile.last_name');

        $this->getJson('/users.json?order[]=Profile.last_name DESC');
        $this->assertSuccess();
        $this->assertBodyContentIsSorted('profile.last_name', 'desc');
    }

    public function testUsersIndexController_Success_OrderByCreated(): void
    {
        $yesterday = FrozenDate::yesterday();
        $userOnYesterdayA = UserFactory::make(['username' => 'A@test.test', 'created' => $yesterday])->user()->persist();
        $userOnYesterdayB = UserFactory::make(['username' => 'B@test.test', 'created' => $yesterday])->user()->persist();
        $userTodayZ = UserFactory::make(['username' => 'Z@test-test', 'created' => FrozenDate::today()])->user()->persist();

        $this->logInAsUser();

        $this->getJson('/users.json?order[]=User.created DESC&order[]=User.username ASC');
        $this->assertSuccess();
        $this->assertEquals($this->_responseJsonBody[0]->id, $userTodayZ->id);
        $this->assertEquals($this->_responseJsonBody[1]->id, $userOnYesterdayA->id);
        $this->assertEquals($this->_responseJsonBody[2]->id, $userOnYesterdayB->id);
    }

    public function testUsersIndexController_Success_OrderByModifiedAndUsername(): void
    {
        $userOnBeforeYesterday = UserFactory::make(['modified' => FrozenDate::now()->subDays(2)])->user()->persist();
        $userOnYesterdayB = UserFactory::make(['username' => 'B@test.test', 'modified' => FrozenDate::now()->subDays(1)])->user()->persist();
        $userOnYesterdayA = UserFactory::make(['username' => 'A@test.test', 'modified' => FrozenDate::now()->subDays(1)])->user()->persist();
        $userOnYesterdayC = UserFactory::make(['username' => 'C@test.test', 'modified' => FrozenDate::now()->subDays(1)])->user()->persist();
        $userToday = UserFactory::make(['modified' => FrozenDate::now()])->user()->persist();

        $this->logInAs($userOnBeforeYesterday);

        $this->getJson('/users.json?order[]=User.modified');
        $this->assertSuccess();
        $this->assertBodyContentIsSorted('modified');

        $this->getJson('/users.json?order[]=User.modified DESC&order[]=User.username ASC');
        $this->assertSuccess();

        $this->assertEquals($this->_responseJsonBody[0]->id, $userToday->id);
        $this->assertEquals($this->_responseJsonBody[1]->id, $userOnYesterdayA->id);
        $this->assertEquals($this->_responseJsonBody[2]->id, $userOnYesterdayB->id);
        $this->assertEquals($this->_responseJsonBody[3]->id, $userOnYesterdayC->id);
        $this->assertEquals($this->_responseJsonBody[4]->id, $userOnBeforeYesterday->id);
    }

    public function testUsersIndexController_Error_Order(): void
    {
        $this->logInAsUser();

        $this->getJson('/users.json?order[]=Users.modi');
        $this->assertBadRequestError('Invalid order. "Users.modi" is not in the list of allowed order.');
        $this->getJson('/users.json?order[]=User.modified RAND');
        $this->assertBadRequestError('Invalid order. "RAND" is not a valid order.');
        $this->getJson('/users.json?order[]=');
        $this->assertBadRequestError('Invalid order. "" is not a valid field.');
    }
}
