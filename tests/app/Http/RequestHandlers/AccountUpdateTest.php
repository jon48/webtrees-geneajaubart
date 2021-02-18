<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2020 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Fisharebest\Webtrees\Http\Controllers\Admin;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Http\RequestHandlers\AccountUpdate;
use Fisharebest\Webtrees\Services\UserService;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\User;

/**
 * Test the AccountUpdate request handler.
 *
 * @covers \Fisharebest\Webtrees\Http\RequestHandlers\AccountUpdate
 */
class AccountUpdateTest extends TestCase
{
    /**
     * @return void
     */
    public function testHandler(): void
    {
        $user_service = self::createMock(UserService::class);

        $user = self::createMock(User::class);
        $user->expects(self::once())->method('setEmail')->with('b');
        $user->expects(self::once())->method('setPassword')->with('e');
        $user->expects(self::once())->method('setRealName')->with('d');
        $user->expects(self::once())->method('setUserName')->with('h');
        $user->expects(self::exactly(4))
            ->method('setPreference')
            ->withConsecutive(
                [User::PREF_CONTACT_METHOD, 'a'],
                [User::PREF_LANGUAGE, 'c'],
                [User::PREF_TIME_ZONE, 'g'],
                [User::PREF_IS_VISIBLE_ONLINE, 'i']
            );

        $tree = self::createMock(Tree::class);
        $tree->expects(self::once())->method('setUserPreference')->with($user, User::PREF_TREE_DEFAULT_XREF, 'f');

        $handler  = new AccountUpdate($user_service);
        $request  = self::createRequest()
            ->withAttribute('tree', $tree)
            ->withAttribute('user', $user)
            ->withParsedBody([
                'contact-method' => 'a',
                'email'          => 'b',
                'language'       => 'c',
                'real_name'      => 'd',
                'password'       => 'e',
                'default-xref'   => 'f',
                'timezone'       => 'g',
                'user_name'      => 'h',
                'visible-online' => 'i',
            ]);
        $response = $handler->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
    }
}
