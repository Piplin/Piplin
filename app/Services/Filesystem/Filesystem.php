<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Services\Filesystem;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * An extension to the laravel filesystem class to add the tempnam function.
 */
class Filesystem extends BaseFilesystem
{
    /**
     * Create a randomly generated unique file and return the path.
     *
     * @param string $path   The folder to use
     * @param string $prefix A prefix to apply to the filename
     *
     * @throws FileNotFoundException
     * @throws IOException
     * @return string
     */
    public function tempnam($path, $prefix = '')
    {
        if ($this->isDirectory($path)) {
            $tmpFile = tempnam($path, $prefix);
            if ($tmpFile !== false) {
                return $tmpFile;
            }
            throw new IOException('A temporary file could not be created.'); // @codeCoverageIgnore
        }
        throw new FileNotFoundException("Directory does not exist at path {$path}");
    }
    /**
     * Touches a file to create it or update the last modified time.
     *
     * @param string $path The file to touch
     *
     * @throws FileNotFoundException
     * @return bool
     */
    public function touch($path)
    {
        if (touch($path)) {
            return true;
        }
        throw new IOException('The file could not be touched.'); // @codeCoverageIgnore
    }
    /**
     * Generate the MD5 hash of a file.
     *
     * @param string $path       The path of the file
     * @param bool   $raw_output
     *
     * @throws IOException
     * @throws FileNotFoundException
     * @return string
     */
    public function md5($path, $raw_output = null)
    {
        if ($this->isFile($path)) {
            $hash = md5_file($path, $raw_output);
            if ($hash !== false) {
                return $hash;
            }
            throw new IOException('The MD5 hash could not be generated'); // @codeCoverageIgnore
        }
        throw new FileNotFoundException("File does not exist at path {$path}");
    }
}