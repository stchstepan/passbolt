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
 * @since         2.0.0
 */
namespace App\Test\TestCase\Utility;

use App\Test\Lib\AppIntegrationTestCase;
use App\Utility\Healthchecks;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Passbolt\JwtAuthentication\Service\AccessToken\JwksGetService;
use Passbolt\JwtAuthentication\Service\AccessToken\JwtKeyPairService;

class HealthchecksTest extends AppIntegrationTestCase
{
    public function tearDown(): void
    {
        parent::tearDown();

        $this->disableFeaturePlugin('JwtAuthentication');
    }

    public function testHealthcheckApplication()
    {
        $check = Healthchecks::application();
        $attributes = [
            'schema', 'robotsIndexDisabled', 'sslForce', 'sslFullBaseUrl', 'seleniumDisabled',
            'registrationClosed', 'jsProd', 'emailNotificationEnabled', 'latestVersion',
        ];
        $this->assertArrayHasAttributes($attributes, $check['application']);
    }

    public function testHealthcheckAppUser()
    {
        $check = Healthchecks::appUser();
        $attributes = ['adminCount'];
        $this->assertArrayHasAttributes($attributes, $check['application']);
    }

    public function testHealthcheckConfigFiles()
    {
        $check = Healthchecks::configFiles();
        $attributes = ['app', 'passbolt'];
        $this->assertArrayHasAttributes($attributes, $check['configFile']);
    }

    public function testHealthcheckCore()
    {
        $check = Healthchecks::core();
        $attributes = ['cache', 'debugDisabled', 'salt', 'fullBaseUrl', 'validFullBaseUrl', 'fullBaseUrlReachable'];
        $this->assertArrayHasAttributes($attributes, $check['core']);
    }

    public function testDatabase()
    {
        $check = Healthchecks::database('test');
        $attributes = ['connect', 'supportedBackend', 'tablesCount', 'defaultContent'];
        $this->assertArrayHasAttributes($attributes, $check['database']);
    }

    public function testHealthcheckEnvironment()
    {
        $check = Healthchecks::environment();
        $expectedCheck = [
            'environment' => [
                'phpVersion' => (bool)version_compare(
                    PHP_VERSION,
                    Configure::read(Healthchecks::PHP_MIN_VERSION_CONFIG),
                    '>='
                ),
                'nextMinPhpVersion' => (bool)version_compare(
                    PHP_VERSION,
                    Configure::read(Healthchecks::PHP_NEXT_MIN_VERSION_CONFIG),
                    '>='
                ),
                'pcre' => true,
                'mbstring' => true,
                'gnupg' => true,
                'intl' => true,
                'image' => true,
                'tmpWritable' => true,
                'logWritable' => true,
                //'allow_url_fopen' => true,
            ],
        ];
        $this->assertSame($expectedCheck, $check);
    }

    public function testHealthcheckGpg()
    {
        $check = Healthchecks::gpg();
        $attributes = [
            'canDecrypt',
            'canDecryptVerify',
            'canEncrypt',
            'canEncryptSign',
            'canSign',
            'canVerify',
            'gpgHome',
            'gpgHomeWritable',
            'gpgKey',
            'gpgKeyNotDefault',
            'gpgKeyPrivate',
            'gpgKeyPrivateBlock',
            'gpgKeyPrivateFingerprint',
            'gpgKeyPrivateReadable',
            'gpgKeyPublic',
            'gpgKeyPublicBlock',
            'gpgKeyPublicEmail',
            'gpgKeyPublicFingerprint',
            'gpgKeyPublicInKeyring',
            'gpgKeyPublicReadable',
            'info',
            'isPrivateServerKeyGopengpgCompatible',
            'isPublicServerKeyGopengpgCompatible',
            'lib',
        ];
        $this->assertArrayHasExactAttributes($attributes, $check['gpg']);
    }

    public function testSsl()
    {
        $check = Healthchecks::ssl();
        $attributes = ['peerValid', 'hostValid', 'notSelfSigned'];
        $this->assertArrayHasAttributes($attributes, $check['ssl']);
    }

    public function testHealthchecksJwt()
    {
        $service = new JwtKeyPairService(
            null,
            (new JwksGetService())->setKeyPath('foo')
        );
        $attributes = ['isEnabled', 'keyPairValid', 'jwtWritable'];

        $check = Healthchecks::jwt($service)['jwt'];
        $this->assertArrayHasAttributes($attributes, $check);
        $this->assertFalse($check['keyPairValid']);

        $this->enableFeaturePlugin('JwtAuthentication');
        $check = Healthchecks::jwt(new JwtKeyPairService())['jwt'];
        $this->assertArrayHasAttributes($attributes, $check);
        $this->assertTrue($check['keyPairValid']);
    }

    public function testDatabase_DummyConnectionFails()
    {
        /** Create a dummy database connection in config. Make sure details are invalid. */
        ConnectionManager::setConfig(
            'healthcheck',
            ['url' => 'mysql://foo:bar@localhost/invalid_database']
        );

        $check = Healthchecks::database('healthcheck');

        $result = $check['database'];
        $attributes = ['connect', 'supportedBackend', 'tablesCount', 'defaultContent'];
        $this->assertArrayHasAttributes($attributes, $result);
        /**
         * Here in `connection` key we get connection error message.
         * Example: "SQLSTATE[HY000] [2002] No such file or directory"
         */
        $this->assertTextContains('SQLSTATE[HY000]', $result['info']['connection']);
        $this->assertFalse($result['connect']);
        $this->assertTrue($result['supportedBackend']);
        $this->assertFalse($result['defaultContent']);

        ConnectionManager::drop('healthcheck');
    }

    public function testDatabase_NotSupportedBackend()
    {
        if (!extension_loaded('sqlite3')) {
            $this->markTestSkipped();
        }

        /** Create a database connection with invalid database driver. */
        ConnectionManager::setConfig(
            'healthcheck',
            ['url' => 'sqlite://./tmp/healthcheck.sqlite']
        );

        $check = Healthchecks::database('healthcheck');

        $result = $check['database'];
        $attributes = ['connect', 'supportedBackend', 'tablesCount', 'defaultContent'];
        $this->assertArrayHasAttributes($attributes, $result);
        $this->assertFalse($result['supportedBackend']);
        $this->assertFalse($result['defaultContent']);

        ConnectionManager::drop('healthcheck');
    }
}
