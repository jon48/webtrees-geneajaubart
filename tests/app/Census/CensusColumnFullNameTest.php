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

namespace Fisharebest\Webtrees\Census;

use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\TestCase;
use Illuminate\Support\Collection;

/**
 * Test harness for the class CensusColumnFullName
 */
class CensusColumnFullNameTest extends TestCase
{
    /**
     * @covers \Fisharebest\Webtrees\Census\CensusColumnFullName
     * @covers \Fisharebest\Webtrees\Census\AbstractCensusColumn
     *
     * @return void
     */
    public function testFullName(): void
    {
        $individual = self::createMock(Individual::class);
        $individual->method('getAllNames')->willReturn([['full' => 'Joe Bloggs']]);
        $individual->method('spouseFamilies')->willReturn(new Collection());

        $census = self::createMock(CensusInterface::class);
        $census->method('censusDate')->willReturn('');

        $column = new CensusColumnFullName($census, '', '');

        self::assertSame('Joe Bloggs', $column->generate($individual, $individual));
    }
}
