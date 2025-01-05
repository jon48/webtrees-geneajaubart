<?php

/**
 *  MyArtJaub Entry Helper module
 *
 * @package MyArtJaub\Webtrees
 * @subpackage EntryHelper
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2024-2025, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

//phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\EntryHelper;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubTrait;

/**
 * EntryHelper Module
 */
class EntryHelperModule extends AbstractModule implements
    ModuleMyArtJaubInterface,
    ModuleGlobalInterface
{
    use ModuleMyArtJaubTrait;
    use ModuleGlobalTrait;

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     */
    public function title(): string
    {
        return I18N::translate('Data entry helper');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::description()
     */
    public function description(): string
    {
        return I18N::translate('Provides assistance when entering genealogical data.');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleVersion()
     */
    public function customModuleVersion(): string
    {
        return '2.1.20-v.1';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleGlobalInterface::bodyContent()
     */
    public function bodyContent(): string
    {
        $translations = [
            'citationLabel' => e(I18N::translate('Source citation')),
            'citationCopy' => e(I18N::translate('Copy source citation')),
            'citationPaste' => e(I18N::translate('Fill source citation from last copy'))
        ];

        return '<script>const MAJ_ENTRYHELPER_TRANSLATIONS = ' . json_encode($translations) . ';</script>' .
            '<script src="' . $this->assetUrl('js/entryhelper.min.js') . '"></script>';
    }
}

return app(EntryHelperModule::class);
