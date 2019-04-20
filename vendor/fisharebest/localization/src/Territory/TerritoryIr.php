<?php

namespace Fisharebest\Localization\Territory;

/**
 * Class AbstractTerritory - Representation of the territory IR - Islamic Republic of Iran.
 *
 * @author    Greg Roach <fisharebest@gmail.com>
 * @copyright (c) 2019 Greg Roach
 * @license   GPLv3+
 */
class TerritoryIr extends AbstractTerritory implements TerritoryInterface
{
    public function code()
    {
        return 'IR';
    }

    public function firstDay()
    {
        return 6;
    }

    public function weekendStart()
    {
        return 5;
    }

    public function weekendEnd()
    {
        return 5;
    }
}
