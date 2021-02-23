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

namespace OAT\Library\Lti1p3DeepLinking\Tests\Unit\Factory;

use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\DeepLinkingContentItemsClaim;
use OAT\Library\Lti1p3Core\Resource\File\FileInterface;
use OAT\Library\Lti1p3Core\Resource\HtmlFragment\HtmlFragmentInterface;
use OAT\Library\Lti1p3Core\Resource\Image\ImageInterface;
use OAT\Library\Lti1p3Core\Resource\Link\LinkInterface;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLinkInterface;
use OAT\Library\Lti1p3Core\Resource\ResourceCollectionInterface;
use OAT\Library\Lti1p3DeepLinking\Factory\ResourceCollectionFactory;
use OAT\Library\Lti1p3DeepLinking\Factory\ResourceCollectionFactoryInterface;
use PHPUnit\Framework\TestCase;

class ResourceCollectionFactoryTest extends TestCase
{
    /** @var array */
    private $contentItems;

    /** @var ResourceCollectionFactoryInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->contentItems = [
            [
                'type'=> LinkInterface::TYPE,
                'url' => 'linkUrl'
            ],
            [
                'type'=> LtiResourceLinkInterface::TYPE,
                'url' => 'ltiResourceLinkUrl'
            ],
            [
                'type'=> ImageInterface::TYPE,
                'url' => 'imageUrl'
            ],
            [
                'type'=> FileInterface::TYPE,
                'url' => 'fileUrl'
            ],
            [
                'type'=> HtmlFragmentInterface::TYPE,
                'html' => 'html'
            ],
            [
                'type'=> 'custom'
            ]
        ];

        $this->subject = new ResourceCollectionFactory();
    }

    public function testCreateFromClaim(): void
    {
        $claim = new DeepLinkingContentItemsClaim($this->contentItems);

        $result =  $this->subject->createFromClaim($claim);

        $this->assertInstanceOf(ResourceCollectionInterface::class, $result);
        $this->assertEquals(6, $result->count());
    }

    public function testCreate(): void
    {
        $result =  $this->subject->create($this->contentItems);

        $this->assertInstanceOf(ResourceCollectionInterface::class, $result);
        $this->assertEquals(6, $result->count());
    }

    public function testCreateError(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot build resource');

        $this->subject->create(
            [
                [
                    'type' => LinkInterface::TYPE
                ]
            ]
        );
    }
}
