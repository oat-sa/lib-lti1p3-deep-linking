<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3DeepLinking\Tests\Unit\Message\Launch\Builder;

use Exception;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\LtiMessageInterface;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayload;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Resource\Link\LinkInterface;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLinkInterface;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tool\Tool;
use OAT\Library\Lti1p3DeepLinking\Message\Launch\Builder\DeepLinkingLaunchRequestBuilder;
use OAT\Library\Lti1p3DeepLinking\Settings\DeepLinkingSettings;
use OAT\Library\Lti1p3DeepLinking\Settings\DeepLinkingSettingsInterface;
use PHPUnit\Framework\TestCase;

class DeepLinkingLaunchRequestBuilderTest extends TestCase
{
    use DomainTestingTrait;

    /** @var RegistrationInterface */
    private $registration;

    /** @var DeepLinkingSettingsInterface */
    private $settings;

    /** @var DeepLinkingLaunchRequestBuilder */
    private $subject;

    protected function setUp(): void
    {
        $this->registration = $this->createTestRegistration();

        $this->settings = new DeepLinkingSettings(
            'http://platform.com/return',
            [
                LinkInterface::TYPE,
                LtiResourceLinkInterface::TYPE
            ],
            [
                'iframe',
                'window',
                'embed'
            ]
        );

        $this->subject = new DeepLinkingLaunchRequestBuilder();
    }

    public function testBuildDeepLinkingLaunchRequestSuccess(): void
    {
        $result = $this->subject->buildDeepLinkingLaunchRequest(
            $this->settings,
            $this->registration,
            'loginHint',
            'http://tool.com/some-deep-linking-url',
            null,
            [
                'Instructor'
            ],
            [
                'a' => 'b'
            ]
        );

        $this->assertInstanceOf(LtiMessageInterface::class, $result);

        $this->assertEquals(
            'http://tool.com/some-deep-linking-url',
            $result->getParameters()->getMandatory('target_link_uri')
        );

        $payload = new LtiMessagePayload(
            $this->parseJwt($result->getParameters()->getMandatory('lti_message_hint'))
        );

        $this->assertEquals(['Instructor'], $payload->getRoles());

        $this->assertEquals('b', $payload->getClaim('a'));

        $this->assertEquals(
            $this->settings->getDeepLinkingReturnUrl(),
            $payload->getDeepLinkingSettings()->getDeepLinkingReturnUrl()
        );

        $this->assertEquals(
            $this->settings->getAcceptedTypes(),
            $payload->getDeepLinkingSettings()->getAcceptedTypes()
        );

        $this->assertEquals(
            $this->settings->getAcceptedMediaTypes(),
            $payload->getDeepLinkingSettings()->getAcceptedMediaTypes()
        );

        $securityToken  = $this->parseJwt($payload->getDeepLinkingSettings()->getData());

        $this->assertTrue(
            $this->verifyJwt($securityToken, $this->registration->getPlatformKeyChain()->getPublicKey())
        );
    }

    public function testBuildDeepLinkingFromDefaultToolUrlLaunchRequestSuccess(): void
    {
        $result = $this->subject->buildDeepLinkingLaunchRequest(
            $this->settings,
            $this->registration,
            'loginHint',
            null,
            null,
            [
                'Instructor'
            ],
            [
                'a' => 'b'
            ]
        );

        $this->assertInstanceOf(LtiMessageInterface::class, $result);

        $this->assertEquals(
            $this->registration->getTool()->getDeepLinkingUrl(),
            $result->getParameters()->getMandatory('target_link_uri')
        );

        $payload = new LtiMessagePayload(
            $this->parseJwt($result->getParameters()->getMandatory('lti_message_hint'))
        );

        $this->assertEquals(['Instructor'], $payload->getRoles());

        $this->assertEquals('b', $payload->getClaim('a'));

        $this->assertEquals(
            $this->settings->getDeepLinkingReturnUrl(),
            $payload->getDeepLinkingSettings()->getDeepLinkingReturnUrl()
        );

        $this->assertEquals(
            $this->settings->getAcceptedTypes(),
            $payload->getDeepLinkingSettings()->getAcceptedTypes()
        );

        $this->assertEquals(
            $this->settings->getAcceptedMediaTypes(),
            $payload->getDeepLinkingSettings()->getAcceptedMediaTypes()
        );

        $securityToken  = $this->parseJwt($payload->getDeepLinkingSettings()->getData());

        $this->assertTrue(
            $this->verifyJwt($securityToken, $this->registration->getPlatformKeyChain()->getPublicKey())
        );
    }

    public function testBuildDeepLinkingLaunchRequestErrorOnMissingLaunchUrl(): void
    {
        $tool = new Tool(
            'toolIdentifier',
            'toolName',
            'toolAudience',
            'http://tool.com/oidc-init',
            'http://tool.com/launch'
        );

        $registration  = $this->createTestRegistration(
            'registrationIdentifier',
            'registrationClientId',
            $this->createTestPlatform(),
            $tool,
            ['deploymentIdentifier']
        );

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Neither deep linking url nor tool default deep linking url were presented');

        $this->subject->buildDeepLinkingLaunchRequest(
            $this->settings,
            $registration,
            'loginHint'
        );
    }

    public function testBuildDeepLinkingLaunchRequestErrorOnInvalidDeploymentId(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Invalid deployment id invalid for registration registrationIdentifier');

        $this->subject->buildDeepLinkingLaunchRequest(
            $this->settings,
            $this->registration,
            'loginHint',
            null,
            'invalid'
        );
    }

    public function testBuildDeepLinkingLaunchRequestGenericError(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot create deep linking launch request: custom error');

        $settingsMock = $this->createMock(DeepLinkingSettingsInterface::class);
        $settingsMock
            ->expects($this->any())
            ->method('normalize')
            ->willThrowException(new Exception('custom error'));

        $this->subject->buildDeepLinkingLaunchRequest(
            $settingsMock,
            $this->registration,
            'loginHint'
        );
    }
}
