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

namespace OAT\Library\Lti1p3DeepLinking\Tests\Integration;

use OAT\Library\Lti1p3Core\Message\Launch\Validator\Platform\PlatformLaunchValidator;
use OAT\Library\Lti1p3Core\Message\Launch\Validator\Tool\ToolLaunchValidator;
use OAT\Library\Lti1p3Core\Message\LtiMessageInterface;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLink;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLinkInterface;
use OAT\Library\Lti1p3Core\Resource\ResourceCollection;
use OAT\Library\Lti1p3Core\Security\Oidc\OidcAuthenticator;
use OAT\Library\Lti1p3Core\Security\Oidc\OidcInitiator;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use OAT\Library\Lti1p3DeepLinking\Factory\ResourceCollectionFactory;
use OAT\Library\Lti1p3DeepLinking\Message\Launch\Builder\DeepLinkingLaunchRequestBuilder;
use OAT\Library\Lti1p3DeepLinking\Message\Launch\Builder\DeepLinkingLaunchResponseBuilder;
use OAT\Library\Lti1p3DeepLinking\Settings\DeepLinkingSettings;
use PHPUnit\Framework\TestCase;

/**
 * @see https://www.imsglobal.org/spec/lti-dl/v2p0/#workflow
 */
class DeepLinkingFlowTest extends TestCase
{
    use DomainTestingTrait;
    use NetworkTestingTrait;

    public function testDeepLinkingFlow(): void
    {
        // Step 0 - Dependencies preparation

        $registrationRepository = $this->createTestRegistrationRepository();
        $userAuthenticator = $this->createTestUserAuthenticator();
        $nonceRepository = $this->createTestNonceRepository();

        $registration = $registrationRepository->find('registrationIdentifier');

        // Step 1 - Platform deep linking launch request creation

        $settings = new DeepLinkingSettings(
            'http://platform.com/return',
            [LtiResourceLinkInterface::TYPE],
            ['window']
        );

        $platformRequestMessage = (new DeepLinkingLaunchRequestBuilder())->buildDeepLinkingLaunchRequest(
            $settings,
            $registration,
            'loginHint'
        );

        // Step 2 - OIDC handling

        $oidcInitPlatformRequest = $this->createServerRequest('GET', $platformRequestMessage->toUrl());

        $oidcInit = new OidcInitiator($registrationRepository);

        $oidcInitToolMessage = $oidcInit->initiate($oidcInitPlatformRequest);

        $oidcAuthToolRequest = $this->createServerRequest('GET', $oidcInitToolMessage->toUrl());

        $oidcAuth = new OidcAuthenticator($registrationRepository, $userAuthenticator);

        $oidcAuthPlatformMessage = $oidcAuth->authenticate($oidcAuthToolRequest);

        $oidcLaunchRequest = $this->createServerRequest(
            'POST',
            $oidcAuthPlatformMessage->getUrl(),
            $oidcAuthPlatformMessage->getParameters()->all()
        );

        $oidcLaunchValidator = new ToolLaunchValidator($registrationRepository, $nonceRepository);

        $launchValidationResult = $oidcLaunchValidator->validatePlatformOriginatingLaunch($oidcLaunchRequest);

        $this->assertFalse($launchValidationResult->hasError());
        $this->assertEquals(
            LtiMessageInterface::LTI_MESSAGE_TYPE_DEEP_LINKING_REQUEST,
            $launchValidationResult->getPayload()->getMessageType()
        );
        $this->assertEquals(
            'http://platform.com/return',
            $launchValidationResult->getPayload()->getDeepLinkingSettings()->getDeepLinkingReturnUrl()
        );
        $this->assertEquals(
            [LtiResourceLinkInterface::TYPE],
            $launchValidationResult->getPayload()->getDeepLinkingSettings()->getAcceptedTypes()
        );
        $this->assertEquals(
            ['window'],
            $launchValidationResult->getPayload()->getDeepLinkingSettings()->getAcceptedPresentationDocumentTargets()
        );

        // Step 3 - Tool content item selection

        $resource = new LtiResourceLink(
            'ltiResourceLinkIdentifier',
            [
                'title' => 'Tool LTI Resource Link',
                'url' => 'http://tool.com/launch'
            ]
        );

        $resourceCollection = (new ResourceCollection())->add($resource);

        $deepLinkingResponseMessage = (new DeepLinkingLaunchResponseBuilder())->buildDeepLinkingLaunchResponse(
            $resourceCollection,
            $registration,
            $launchValidationResult->getPayload()->getDeepLinkingSettings()->getDeepLinkingReturnUrl(),
            null,
            $launchValidationResult->getPayload()->getDeepLinkingSettings()->getData()
        );

        // Step 4 - Platform content item reception and validation

        $deepLinkingResponse = $this->createServerRequest(
            'POST',
            $deepLinkingResponseMessage->getUrl(),
            $deepLinkingResponseMessage->getParameters()->all()
        );

        $deepLinkingResponseValidator = new PlatformLaunchValidator($registrationRepository, $nonceRepository);

        $deepLinkValidationResult = $deepLinkingResponseValidator->validateToolOriginatingLaunch($deepLinkingResponse);

        $this->assertFalse($deepLinkValidationResult->hasError());
        $this->assertEquals(
            LtiMessageInterface::LTI_MESSAGE_TYPE_DEEP_LINKING_RESPONSE,
            $deepLinkValidationResult->getPayload()->getMessageType()
        );
        $this->assertEquals(
            '1 item(s) provided',
            $deepLinkValidationResult->getPayload()->getDeepLinkingMessage()
        );

        // Step 5 - Platform content item manipulation

        $returnedResourceCollection = (new ResourceCollectionFactory())->createFromClaim(
            $deepLinkValidationResult->getPayload()->getDeepLinkingContentItems()
        );

        $this->assertEquals(1, $returnedResourceCollection->count());

        $returnedResource = current($returnedResourceCollection->getByType(LtiResourceLinkInterface::TYPE));

        $this->assertInstanceOf(LtiResourceLinkInterface::class, $returnedResource);
        $this->assertEquals('Tool LTI Resource Link', $returnedResource->getTitle());
        $this->assertEquals('http://tool.com/launch', $returnedResource->getUrl());
    }
}
