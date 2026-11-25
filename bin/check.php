<?php
declare(strict_types=1);

$directories = [__DIR__, dirname(__DIR__).'/config', dirname(__DIR__).'/src'];
$failed = false;

foreach ($directories as $directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $output = [];
        $exitCode = 0;
        exec('php -l '.escapeshellarg($file->getPathname()), $output, $exitCode);
        echo implode("\n", $output)."\n";
        if ($exitCode !== 0) {
            $failed = true;
        }
    }
}

exit($failed ? 1 : 0);
