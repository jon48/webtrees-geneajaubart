<?php

namespace Fisharebest\Localization\Locale;

use Fisharebest\Localization\Script\ScriptCyrl;

/**
 * Class LocaleBsCyrl
 *
 * @author    Greg Roach <fisharebest@gmail.com>
 * @copyright (c) 2019 Greg Roach
 * @license   GPLv3+
 */
class LocaleBsCyrl extends LocaleBs
{
    public function script()
    {
        return new ScriptCyrl();
    }

    protected function percentFormat()
    {
        return self::PLACEHOLDER . self::PERCENT;
    }
}
