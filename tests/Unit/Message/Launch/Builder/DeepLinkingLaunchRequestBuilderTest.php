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

use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3DeepLinking\Message\Launch\Builder\DeepLinkingLaunchRequestBuilder;
use OAT\Library\Lti1p3DeepLinking\Settings\DeepLinkingSettings;
use PHPUnit\Framework\TestCase;

class DeepLinkingLaunchRequestBuilderTest extends TestCase
{
    use DomainTestingTrait;

    public function testStuff(): void
    {
        $subject = new DeepLinkingLaunchRequestBuilder();

        $settings = new DeepLinkingSettings(
            'http://platform.com/return',
            ['link', 'ltiResourceLink'],
            ['iframe', 'window', 'embed']
        );

        $message = $subject->buildDeepLinkingLaunchRequest(
            $settings,
            $this->createTestRegistration(),
            'loginHint'
        );

        var_export($message->getParameter('lti_message_hint'));
    }
}
