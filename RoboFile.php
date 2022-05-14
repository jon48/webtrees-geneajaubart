<?php

/**
 * Robo tasks - webtrees GeneaJaubart
 *
 * @see http://robo.li/
 * phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

declare(strict_types=1);

use Robo\Result;
use Symfony\Component\Finder\Finder;

class RoboFile extends \Robo\Tasks
{
    /**
     * Build and copy the application languages.
     *
     * @param string $target_dir Directory to which languages must be copied
     * @param string $base_dir Base directory of the application
     * @throws \Exception
     * @return \Robo\Result<mixed>
     */
    public function buildLanguages(string $target_dir, string $base_dir = __DIR__): Result
    {
        $this->taskExec('composer webtrees:lang')->progressMessage("Copying languages")->run();
        $languages_build = $this->taskFilesystemStack();

        $languages_dir = 'resources/lang';
        $base_languages_dir = "$base_dir/$languages_dir";
        $languages = Finder::create()->directories()->in($base_languages_dir)->depth(0);
        foreach ($languages as $language_dir) {
            $language_target_dir = "$target_dir/$languages_dir/{$language_dir->getFilename()}";
            $languages_build->mkdir($language_target_dir);
            $languages_build->copy("$language_dir/messages.php", "$language_target_dir/messages.php");
        }
        return $languages_build->progressMessage("Copying languages")->run();
    }

    /**
     * Build the application modules ready for use.
     *
     * @param string $base_dir Base directory of the application
     * @throws \Exception
     * @return \Robo\Result<mixed>
     */
    public function buildModules(string $base_dir = __DIR__, bool $delete = false): Result
    {
        $collection = $this->collectionBuilder();

        $modules_dir = $base_dir . '/modules_v4';
        $modules = Finder::create()->directories()->name('myartjaub_*')->in($modules_dir)->depth(0);
        foreach ($modules as $module) {
            $collection->progressMessage("Building module {$module->getRelativePathname()}");
            $module_dir = $modules_dir . '/' . $module->getRelativePathname();

            if ($module->getRelativePathname() === 'myartjaub_ruraltheme') {
                $collection->taskComposerInstall()
                    ->dir($module_dir)
                    ->noSuggest();
            }

            if (Finder::create()->name('package-lock.json')->in($module_dir)->depth(0)->count() > 0) {
                $collection->taskExec("npm install --no-fund")
                    ->dir($module_dir);
                $collection->taskExecStack()
                    ->dir($module_dir)
                    ->stopOnFail()
                    ->exec('npm run production');
            }

            if ($delete) {
                $collection->taskFilesystemStack()
                    ->remove($module_dir . '/node_modules')
                    ->remove($module_dir . '/vendor');
            }
        }

        return $collection
            ->progressMessage("Application built.")
            ->run();
    }

    /**
     * Clean the application folder by removing development files.
     *
     * @param string $base_dir Base directory of the application
     * @throws \Exception
     * @return \Robo\Result<mixed>
     */
    public function clean(string $base_dir = __DIR__): Result
    {
        $collection = $this->collectionBuilder();

        $modules_dir = $base_dir . '/modules_v4';
        $modules = Finder::create()->directories()->name('myartjaub_*')->in($modules_dir)->depth(0);
        foreach ($modules as $module) {
            $collection->progressMessage("Starting deleting development folders for {$module->getRelativePathname()}");
            $module_dir = $modules_dir . '/' . $module->getRelativePathname();

            $collection->taskFilesystemStack()
                ->remove($module_dir . '/src')
                ->remove($module_dir . '/node_modules')
                ->remove($module_dir . '/vendor');
        }

        $filesToDelete = Finder::create()
            ->in($base_dir)
            ->files()
            ->notPath('vendor')
            ->name(['composer*.*', 'package*.*', 'webpack*.*']);

        $collection
            ->progressMessage("Starting deleting development files")
            ->taskFilesystemStack()
                ->remove($filesToDelete);

        return $collection
            ->progressMessage('Repository cleaning done.')
            ->run();
    }

    /**
     * Run the Semistandard Javascript Linter on MyArtJaub modules
     *
     * @throws \Exception
     * @return \Robo\Result<mixed>
     */
    public function lintSemistandard(): Result
    {
        $collection = $this->collectionBuilder();

        $modules_dir = __DIR__ . '/modules_v4';
        $modules = Finder::create()->directories()->name('myartjaub_*')->in($modules_dir)->depth(0);
        foreach ($modules as $module) {
            $module_dir = $modules_dir . '/' . $module->getRelativePathname();

            if (
                Finder::create()->name('package-lock.json')->in($module_dir)->depth(0)->count() > 0
                && Finder::create()->name('composer.json')->in($module_dir)->depth(0)->count() === 0
            ) {
                $collection->taskExecStack()
                    ->dir($module_dir)
                    ->stopOnFail()
                    ->exec('npx semistandard');
            }
        }

        return $collection->run();
    }

    /**
     * Package the specific commitish in a zip file for distribution.
     * Commitish can be a commit hash, a tag, or a branch.
     *
     * @param string $version Package version
     * @param string $commit Commitish to be packaged
     * @throws \Exception
     * @return \Robo\Result<mixed>
     */
    public function package(string $version, string $commit = 'main'): Result
    {
        $getCommitResult =
            $this->taskExec("git rev-parse --quiet --verify $commit")
                ->interactive(false)
                ->run();
        if (!$getCommitResult->wasSuccessful() || $getCommitResult->getMessage() === '') {
            throw new \Exception('The commit requested does not exist');
        }

        $collection = $this->collectionBuilder();

        // Define paths and variables
        $workDir = $collection->workDir("build/release.tmp");

        $PROJECT_NAME = 'webtrees-geneajaubart';

        $output_name = "webtrees-$version";

        $build_archive_dir = "$workDir/$output_name.build";
        $build_archive_zip = "$build_archive_dir.zip";

        $build_release_path = "build/$output_name.zip";

        // Extract webtrees archive and build
        $this->taskFilesystemStack()
                ->mkdir($workDir)
            ->progressMessage("Starting build of $PROJECT_NAME $commit")
            ->progressMessage("Export git archive at commit $commit")
            ->taskExec("git archive --format=zip --output=$build_archive_zip $commit")
            ->taskExtract($build_archive_zip)
                ->to($build_archive_dir)
            ->taskComposerInstall()
                ->dir($build_archive_dir)
                ->noDev()
                ->preferDist()
                ->optimizeAutoloader()
            ->progressMessage("Extraction of $PROJECT_NAME $commit completed!")
            ->run();

        // Build and copy webtrees languages
        $this->buildLanguages($build_archive_dir);

        // Build MyArtJaub modules
        $this->buildModules($build_archive_dir, true);

        $collection->progressMessage("Build of $PROJECT_NAME $commit completed!");

        $this->clean($build_archive_dir);

        $filesToCompress = Finder::create()
            ->in($build_archive_dir)
            ->depth(0); // do not recurse, the Pack task already does it.

        $collection
            ->progressMessage("Starting packaging of $PROJECT_NAME $commit")
            ->taskPack($build_release_path);

        $nbCompressedFiles = 0;
        foreach ($filesToCompress as $file) {
            $collection->addFile($file->getRelativePathname(), $file->getRealPath());
            $nbCompressedFiles++;
        }

        return $collection
            ->progressMessage("Added $nbCompressedFiles items to archive.")
            ->progressMessage("Deleting work directory...")
            ->taskDeleteDir($workDir)
            ->progressMessage("Package created: $build_release_path !!!")
            ->run();
    }
}
