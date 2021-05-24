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
     * Build the application ready for use.
     *
     * @param string $base_dir Base directory of the application
     * @throws \Exception
     * @return \Robo\Result<mixed>
     */
    public function build(string $base_dir = __DIR__, bool $delete = false): Result
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
                    ->noSuggest()
                    ->run();
            }

            if (Finder::create()->name('package-lock.json')->in($module_dir)->depth(0)->count() > 0) {
                $collection->taskExec("npm install --no-fund")
                    ->dir($module_dir)
                    ->run();
                $collection->taskExecStack()
                    ->dir($module_dir)
                    ->stopOnFail()
                    ->exec('npm run production')
                    ->run();
            }

            if ($delete) {
                $collection->taskFilesystemStack()
                    ->remove($module_dir . '/node_modules')
                    ->remove($module_dir . '/vendor')
                    ->run();
            }
        }

        return $collection
            ->progressMessage("Application built.")
            ->run();
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
    public function package(string $version, string $commit = 'master'): Result
    {
        $getCommitResult =
            $this->taskExec("git rev-parse --quiet --verify $commit")
                ->interactive(false)
                ->run();
        if (!$getCommitResult->wasSuccessful() || $getCommitResult->getMessage() === '') {
            throw new \Exception('The commit requested does not exist');
        }

        $collection = $this->collectionBuilder();

        $workDir = $collection->workDir("build/release.tmp");

        $PROJECT_NAME = 'webtrees-geneajaubart';

        $output_name = "webtrees-$version";
        $output_name = str_replace('-v.', '.', $output_name);

        $build_archive_dir = "$workDir/$output_name.build";
        $build_archive_zip = "$build_archive_dir.zip";

        $build_release_path = "build/$output_name.zip";

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
                ->noSuggest()
                ->optimizeAutoloader()
            ->progressMessage("Extraction of $PROJECT_NAME $commit completed!")
            ->run();

        $this->build($build_archive_dir, true);

        $collection->progressMessage("Build of $PROJECT_NAME $commit completed!");

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
