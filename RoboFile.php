<?php
/**
 * Robo tasks - webtrees GeneaJaubart
 * 
 * @see http://robo.li/
 */

use Robo\Robo;
use Symfony\Component\Finder\Finder;

class RoboFile extends \Robo\Tasks
{

    /**
     * Package the specific commitish in a zip file for distribution.
     * Commitish can be a commit hash, a tag, or a branch.
     * 
     * @param string $commit Commitish to be packaged
     * @throws \Exception
     * @return \Robo\Result
     */
    function package($version, $commit = 'main-1.7') {
        $getCommitResult = 
            $this->taskExec("git rev-parse --quiet --verify $commit")
                ->interactive(false)
                ->run();
        if(!$getCommitResult->wasSuccessful() || $getCommitResult->getMessage() === '') {
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
        
        $buildTasks = $this->collectionBuilder();
        
        $buildTasksResult = $buildTasks
            ->taskFilesystemStack()
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
            ->progressMessage("Build of $PROJECT_NAME $commit completed!")
            ->run();
            
        // Exit if the build step failed
        if (!$buildTasksResult->wasSuccessful()) {
            return $buildTasksResult;
        }
        
        $filesToCompress = Finder::create()
            ->in($build_archive_dir)
            ->depth(0); // do not recurse, the Pack task already does it.
        
        $collection
            ->progressMessage("Starting packaging of $PROJECT_NAME $commit")
            ->taskPack($build_release_path)
            ;
        
       $nbCompressedFiles = 0;
        foreach ($filesToCompress as $file) {
            $collection->addFile($file->getRelativePathname(), $file->getRealPath());
            $nbCompressedFiles++;
        }
        
        $collection
            ->progressMessage("Added $nbCompressedFiles items to archive.")
            ->progressMessage("Deleting work directory...")
            ->taskDeleteDir($workDir)
            ->progressMessage("Package created: $build_release_path !!!")
            ;
        
        return $collection->run();
    }
}