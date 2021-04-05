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

namespace OAT\Library\Lti1p3DeepLinking\Factory;

use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\DeepLinkingContentItemsClaim;
use OAT\Library\Lti1p3Core\Resource\File\File;
use OAT\Library\Lti1p3Core\Resource\File\FileInterface;
use OAT\Library\Lti1p3Core\Resource\HtmlFragment\HtmlFragment;
use OAT\Library\Lti1p3Core\Resource\HtmlFragment\HtmlFragmentInterface;
use OAT\Library\Lti1p3Core\Resource\Image\Image;
use OAT\Library\Lti1p3Core\Resource\Image\ImageInterface;
use OAT\Library\Lti1p3Core\Resource\Link\Link;
use OAT\Library\Lti1p3Core\Resource\Link\LinkInterface;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLink;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLinkInterface;
use OAT\Library\Lti1p3Core\Resource\Resource;
use OAT\Library\Lti1p3Core\Resource\ResourceCollection;
use OAT\Library\Lti1p3Core\Resource\ResourceCollectionInterface;
use OAT\Library\Lti1p3Core\Resource\ResourceInterface;
use OAT\Library\Lti1p3Core\Util\Generator\IdGenerator;
use OAT\Library\Lti1p3Core\Util\Generator\IdGeneratorInterface;
use Throwable;

class ResourceCollectionFactory implements ResourceCollectionFactoryInterface
{
    /** @var IdGeneratorInterface */
    private $generator;

    public function __construct(?IdGeneratorInterface $generator = null)
    {
        $this->generator = $generator ?? new IdGenerator();
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function create(array $contentItems): ResourceCollectionInterface
    {
        $collection = new ResourceCollection();

        foreach ($contentItems as $item) {
            $collection->add($this->createResource($item));
        }

        return $collection;
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function createFromClaim(DeepLinkingContentItemsClaim $claim): ResourceCollectionInterface
    {
        return $this->create($claim->getContentItems());
    }

    /**
     * @throws LtiExceptionInterface
     */
    private function createResource(array $resourceData): ResourceInterface
    {
        try {
            $identifier = $this->generator->generate();

            switch ($resourceData['type']) {
                case FileInterface::TYPE:
                    return new File($identifier, $resourceData['url'], $resourceData);

                case HtmlFragmentInterface::TYPE:
                    return new HtmlFragment($identifier, $resourceData['html'], $resourceData);

                case ImageInterface::TYPE:
                    return new Image($identifier, $resourceData['url'], $resourceData);

                case LinkInterface::TYPE:
                    return new Link($identifier, $resourceData['url'], $resourceData);

                case LtiResourceLinkInterface::TYPE:
                    return new LtiResourceLink($identifier, $resourceData);

                default:
                    return new Resource($identifier, $resourceData['type'], $resourceData);
            }

        } catch (Throwable $exception)  {
            throw new LtiException(
                sprintf('Cannot build resource: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
