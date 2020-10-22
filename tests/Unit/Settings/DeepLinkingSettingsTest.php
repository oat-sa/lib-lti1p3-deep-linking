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

namespace OAT\Library\Lti1p3DeepLinking\Tests\Unit\Settings;

use OAT\Library\Lti1p3Core\Resource\Link\LinkInterface;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLinkInterface;
use OAT\Library\Lti1p3DeepLinking\Message\Launch\Builder\DeepLinkingLaunchRequestBuilder;
use OAT\Library\Lti1p3DeepLinking\Settings\DeepLinkingSettings;
use PHPUnit\Framework\TestCase;

class DeepLinkingSettingsTest extends TestCase
{
    /** @var DeepLinkingLaunchRequestBuilder */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new DeepLinkingSettings(
            'http://platform.com/return',
            [
                LinkInterface::TYPE,
                LtiResourceLinkInterface::TYPE
            ],
            [
                'iframe',
                'window',
                'embed'
            ],
            'image/*',
            true,
            true,
            'title',
            'text'
        );
    }

    public function testGetters(): void
    {
        $this->assertEquals('http://platform.com/return', $this->subject->getDeepLinkingReturnUrl());
        $this->assertEquals(
            [
                LinkInterface::TYPE,
                LtiResourceLinkInterface::TYPE
            ],
            $this->subject->getAcceptedTypes()
        );
        $this->assertEquals(
            [
                'iframe',
                'window',
                'embed'
            ],
            $this->subject->getAcceptedPresentationDocumentTargets()
        );
        $this->assertEquals('image/*', $this->subject->getAcceptedMediaTypes());
        $this->assertTrue($this->subject->shouldAcceptMultiple());
        $this->assertTrue($this->subject->shouldAutoCreate());
        $this->assertEquals('title', $this->subject->getTitle());
        $this->assertEquals('text', $this->subject->getText());
    }

    public function testNormalize(): void
    {
        $this->assertEquals(
            [
                'deep_link_return_url' => 'http://platform.com/return',
                'accept_types' => [
                    LinkInterface::TYPE,
                    LtiResourceLinkInterface::TYPE
                ],
                'accept_presentation_document_targets' => [
                    'iframe',
                    'window',
                    'embed'
                ],
                'accept_media_types' => 'image/*',
                'accept_multiple' => true,
                'auto_create' => true,
                'title' => 'title',
                'text' => 'text'
            ],
            $this->subject->normalize()
        );
    }


    public function testDefaultParameters(): void
    {
        $subject = new DeepLinkingSettings(
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

        $this->assertTrue($subject->shouldAcceptMultiple());
        $this->assertFalse($subject->shouldAutoCreate());

        $this->assertEquals(
            [
                'deep_link_return_url' => 'http://platform.com/return',
                'accept_types' => [
                    LinkInterface::TYPE,
                    LtiResourceLinkInterface::TYPE
                ],
                'accept_presentation_document_targets' => [
                    'iframe',
                    'window',
                    'embed'
                ],
                'accept_multiple' => true,
                'auto_create' => false,
            ],
            $subject->normalize()
        );
    }
}
