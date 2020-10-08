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

namespace OAT\Library\Lti1p3DeepLinking\Builder;

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
use Ramsey\Uuid\Uuid;

class ResourceCollectionBuilder implements ResourceCollectionBuilderInterface
{
    public function buildFromClaim(DeepLinkingContentItemsClaim $claim): ResourceCollectionInterface
    {
        $collection = new ResourceCollection();

        foreach ($claim->getContentItems() as $item) {
            $collection->add($this->buildResource($item));
        }

        return $collection;
    }

    private function buildResource(array $resourceData): ResourceInterface
    {
        $identifier = Uuid::uuid4()->toString();

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
    }
}
