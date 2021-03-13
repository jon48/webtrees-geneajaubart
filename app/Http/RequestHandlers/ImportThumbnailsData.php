<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2021 webtrees development team
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

namespace Fisharebest\Webtrees\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Media;
use Fisharebest\Webtrees\Mime;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\SearchService;
use Illuminate\Support\Collection;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function abs;
use function array_map;
use function e;
use function explode;
use function glob;
use function implode;
use function intdiv;
use function is_file;
use function max;
use function response;
use function route;
use function str_contains;
use function str_replace;
use function stripos;
use function substr;
use function substr_compare;
use function view;

use const GLOB_NOSORT;

/**
 * Import custom thumbnails from webtrees 1.x.
 */
class ImportThumbnailsData implements RequestHandlerInterface
{
    private const FINGERPRINT_PIXELS = 10;

    /** @var SearchService */
    private $search_service;

    /**
     * ImportThumbnailsData constructor.
     *
     * @param SearchService $search_service
     */
    public function __construct(SearchService $search_service)
    {
        $this->search_service = $search_service;
    }

    /**
     * Import custom thumbnails from webtrees 1.x.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data_filesystem = Registry::filesystem()->data();

        $start  = (int) $request->getQueryParams()['start'];
        $length = (int) $request->getQueryParams()['length'];
        $search = $request->getQueryParams()['search']['value'];

        // Fetch all thumbnails
        $thumbnails = Collection::make($data_filesystem->listContents('', true))
            ->filter(static function (array $metadata): bool {
                return $metadata['type'] === 'file' && str_contains($metadata['path'], '/thumbs/');
            })
            ->map(static function (array $metadata): string {
                return $metadata['path'];
            });

        $recordsTotal = $thumbnails->count();

        if ($search !== '') {
            $thumbnails = $thumbnails->filter(static function (string $thumbnail) use ($search): bool {
                return stripos($thumbnail, $search) !== false;
            });
        }

        $recordsFiltered = $thumbnails->count();

        $data = $thumbnails
            ->slice($start, $length)
            ->map(function (string $thumbnail) use ($data_filesystem): array {
                // Turn each filename into a row for the table
                $original = $this->findOriginalFileFromThumbnail($thumbnail);

                $original_url  = route(AdminMediaFileThumbnail::class, ['path' => $original]);
                $thumbnail_url = route(AdminMediaFileThumbnail::class, ['path' => $thumbnail]);

                $difference = $this->imageDiff($data_filesystem, $thumbnail, $original);

                $media = $this->search_service->findMediaObjectsForMediaFile($original);

                $media_links = array_map(static function (Media $media): string {
                    return '<a href="' . e($media->url()) . '">' . $media->fullName() . '</a>';
                }, $media);

                $media_links = implode('<br>', $media_links);

                $action = view('admin/webtrees1-thumbnails-form', [
                    'difference' => $difference,
                    'media'      => $media,
                    'thumbnail'  => $thumbnail,
                ]);

                return [
                    '<img src="' . e($thumbnail_url) . '" title="' . e($thumbnail) . '">',
                    '<img src="' . e($original_url) . '" title="' . e($original) . '">',
                    $media_links,
                    I18N::percentage($difference / 100.0),
                    $action,
                ];
            });

        return response([
            'draw'            => (int) $request->getQueryParams()['draw'],
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data->values()->all(),
        ]);
    }

    /**
     * Find the original image that corresponds to a (webtrees 1.x) thumbnail file.
     *
     * @param string $thumbnail
     *
     * @return string
     */
    private function findOriginalFileFromThumbnail(string $thumbnail): string
    {
        // First option - a file with the same name
        $original = str_replace('/thumbs/', '/', $thumbnail);

        // Second option - a .PNG thumbnail for some other image type
        if (substr_compare($original, '.png', -4, 4) === 0) {
            $pattern = substr($original, 0, -3) . '*';
            $matches = glob($pattern, GLOB_NOSORT);
            if ($matches !== [] && is_file($matches[0])) {
                $original = $matches[0];
            }
        }

        return $original;
    }

    /**
     * Compare two images, and return a quantified difference.
     * 0 (different) ... 100 (same)
     *
     * @param FilesystemInterface $data_filesystem
     * @param string              $thumbnail
     * @param string              $original
     *
     * @return int
     */
    private function imageDiff(FilesystemInterface $data_filesystem, string $thumbnail, string $original): int
    {
        // The original filename was generated from the thumbnail filename.
        // It may not actually exist.
        if (!$data_filesystem->has($original)) {
            return 100;
        }

        $thumbnail_type = explode('/', $data_filesystem->getMimetype($thumbnail) ?: Mime::DEFAULT_TYPE)[0];
        $original_type  = explode('/', $data_filesystem->getMimetype($original) ?: Mime::DEFAULT_TYPE)[0];

        if ($thumbnail_type !== 'image') {
            // If the thumbnail file is not an image then similarity is unimportant.
            // Response with an exact match, so the GUI will recommend deleting it.
            return 100;
        }

        if ($original_type !== 'image') {
            // If the original file is not an image then similarity is unimportant .
            // Response with an exact mismatch, so the GUI will recommend importing it.
            return 0;
        }

        $pixels1 = $this->scaledImagePixels($data_filesystem, $thumbnail);
        $pixels2 = $this->scaledImagePixels($data_filesystem, $original);

        $max_difference = 0;

        foreach ($pixels1 as $x => $row) {
            foreach ($row as $y => $pixel) {
                $max_difference = max($max_difference, abs($pixel - $pixels2[$x][$y]));
            }
        }

        // The maximum difference is 255 (black versus white).
        return 100 - intdiv($max_difference * 100, 255);
    }

    /**
     * Scale an image to 10x10 and read the individual pixels.
     * This is a slow operation, add we will do it many times on
     * the "import webtrees 1 thumbnails" page so cache the results.
     *
     * @param FilesystemInterface $filesystem
     * @param string              $path
     *
     * @return int[][]
     */
    private function scaledImagePixels(FilesystemInterface $filesystem, string $path): array
    {
        return Registry::cache()->file()->remember('pixels-' . $path, static function () use ($filesystem, $path): array {
            $blob    = $filesystem->read($path);
            $manager = new ImageManager();
            $image   = $manager->make($blob)->resize(self::FINGERPRINT_PIXELS, self::FINGERPRINT_PIXELS);

            $pixels = [];
            for ($x = 0; $x < self::FINGERPRINT_PIXELS; ++$x) {
                $pixels[$x] = [];
                for ($y = 0; $y < self::FINGERPRINT_PIXELS; ++$y) {
                    $pixel          = $image->pickColor($x, $y);
                    $pixels[$x][$y] = (int) (($pixel[0] + $pixel[1] + $pixel[2]) / 3);
                }
            }

            return $pixels;
        });
    }
}
