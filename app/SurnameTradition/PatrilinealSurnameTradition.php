<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2022 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Fisharebest\Webtrees\SurnameTradition;

use Fisharebest\Webtrees\Elements\NameType;
use Fisharebest\Webtrees\Individual;

/**
 * Children take their father’s surname.
 */
class PatrilinealSurnameTradition extends DefaultSurnameTradition
{
    /**
     * What name is given to a new child
     *
     * @param Individual|null $father
     * @param Individual|null $mother
     * @param string          $sex
     *
     * @return array<int,string>
     */
    public function newChildNames(?Individual $father, ?Individual $mother, string $sex): array
    {
        if (preg_match(self::REGEX_SPFX_SURN, $this->extractName($father), $match)) {
            $name = $match['NAME'];
            $spfx = $match['SPFX'];
            $surn = $match['SURN'];

            return [
                $this->buildName($name, ['TYPE' => NameType::VALUE_BIRTH, 'SPFX' => $spfx, 'SURN' => $surn]),
            ];
        }

        return parent::newChildNames($father, $mother, $sex);
    }

    /**
     * What name is given to a new parent
     *
     * @param Individual $child
     * @param string     $sex
     *
     * @return array<int,string>
     */
    public function newParentNames(Individual $child, string $sex): array
    {
        if ($sex === 'M' && preg_match(self::REGEX_SPFX_SURN, $this->extractName($child), $match)) {
            $name = $match['NAME'];
            $spfx = $match['SPFX'];
            $surn = $match['SURN'];

            return [
                $this->buildName($name, ['TYPE' => NameType::VALUE_BIRTH, 'SPFX' => $spfx, 'SURN' => $surn]),
            ];
        }

        return parent::newParentNames($child, $sex);
    }

    /**
     * @param string               $name        A name
     * @param array<string,string> $inflections A list of inflections
     *
     * @return string An inflected name
     */
    protected function inflect(string $name, array $inflections): string
    {
        foreach ($inflections as $from => $to) {
            $name = preg_replace('~' . $from . '~u', $to, $name);
        }

        return $name;
    }
}
