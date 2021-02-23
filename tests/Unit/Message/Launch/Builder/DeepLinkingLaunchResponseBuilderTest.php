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
use OAT\Library\Lti1p3Core\Resource\Link\Link;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLink;
use OAT\Library\Lti1p3Core\Resource\ResourceCollection;
use OAT\Library\Lti1p3Core\Resource\ResourceCollectionInterface;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3DeepLinking\Message\Launch\Builder\DeepLinkingLaunchResponseBuilder;
use PHPUnit\Framework\TestCase;

class DeepLinkingLaunchResponseBuilderTest extends TestCase
{
    use DomainTestingTrait;

    /** @var RegistrationInterface */
    private $registration;

    /** @var ResourceCollectionInterface */
    private $collection;

    /** @var DeepLinkingLaunchResponseBuilder */
    private $subject;

    protected function setUp(): void
    {
        $this->registration = $this->createTestRegistration();

        $this->collection = new ResourceCollection(
            [
                new Link('linkIdentifier', 'http://example.com/link'),
                new LtiResourceLink('ltiResourceLinkIdentifier'),
            ]
        );

        $this->subject = new DeepLinkingLaunchResponseBuilder();
    }

    public function testBuildDeepLinkingLaunchResponseSuccess(): void
    {
        $result = $this->subject->buildDeepLinkingLaunchResponse(
            $this->collection,
            $this->registration,
            'http://example.com/deep-linking-return',
            null,
            'data'
        );

        $this->assertInstanceOf(LtiMessageInterface::class, $result);

        $payload = new LtiMessagePayload(
            $this->parseJwt($result->getParameters()->getMandatory('JWT'))
        );

        $this->assertEquals('data', $payload->getDeepLinkingData());
        $this->assertEquals('2 item(s) provided', $payload->getDeepLinkingMessage());
        $this->assertEquals('2 item(s) provided', $payload->getDeepLinkingLog());
    }

    public function testBuildDeepLinkingLaunchResponseSuccessWithGivenMessageAndLog(): void
    {
        $result = $this->subject->buildDeepLinkingLaunchResponse(
            $this->collection,
            $this->registration,
            'http://example.com/deep-linking-return',
            null,
            'data',
            'message',
            'log'
        );

        $this->assertInstanceOf(LtiMessageInterface::class, $result);

        $payload = new LtiMessagePayload(
            $this->parseJwt($result->getParameters()->getMandatory('JWT'))
        );

        $this->assertEquals('data', $payload->getDeepLinkingData());
        $this->assertEquals('message', $payload->getDeepLinkingMessage());
        $this->assertEquals('log', $payload->getDeepLinkingLog());
    }

    public function testBuildDeepLinkingLaunchResponseErrorOnInvalidDeploymentId(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Invalid deployment id invalid for registration registrationIdentifier');

        $this->subject->buildDeepLinkingLaunchResponse(
            $this->collection,
            $this->registration,
            'http://example.com/deep-linking-return',
            'invalid'
        );
    }

    public function testBuildDeepLinkingLaunchResponseGenericError(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot create deep linking launch response: custom error');

        $collectionMock = $this->createMock(ResourceCollectionInterface::class);
        $collectionMock
            ->expects($this->any())
            ->method('count')
            ->willThrowException(new Exception('custom error'));

        $this->subject->buildDeepLinkingLaunchResponse(
            $collectionMock,
            $this->registration,
            'http://example.com/deep-linking-return'
        );
    }

    public function testBuildDeepLinkingLaunchErrorResponseSuccess(): void
    {
        $result = $this->subject->buildDeepLinkingLaunchErrorResponse(
            $this->registration,
            'http://example.com/deep-linking-return',
            null,
            'data',
            'errorMessage',
            'errorLog'
        );

        $this->assertInstanceOf(LtiMessageInterface::class, $result);

        $payload = new LtiMessagePayload(
            $this->parseJwt($result->getParameters()->getMandatory('JWT'))
        );

        $this->assertEquals('data', $payload->getDeepLinkingData());
        $this->assertEquals('errorMessage', $payload->getDeepLinkingErrorMessage());
        $this->assertEquals('errorLog', $payload->getDeepLinkingErrorLog());
    }

    public function testBuildDeepLinkingLaunchErrorResponseErrorOnInvalidDeploymentId(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Invalid deployment id invalid for registration registrationIdentifier');

        $this->subject->buildDeepLinkingLaunchErrorResponse(
            $this->registration,
            'http://example.com/deep-linking-return',
            'invalid'
        );
    }

    public function testBuildDeepLinkingLaunchErrorResponseGenericError(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot create deep linking launch error response: custom error');

        $registrationMock = $this->createMock(RegistrationInterface::class);
        $registrationMock
            ->expects($this->any())
            ->method('getDefaultDeploymentId')
            ->willReturn($this->registration->getDefaultDeploymentId());
        $registrationMock
            ->expects($this->any())
            ->method('getCLientId')
            ->willThrowException(new Exception('custom error'));

        $this->subject->buildDeepLinkingLaunchErrorResponse(
            $registrationMock,
            'http://example.com/deep-linking-return'
        );
    }
}
