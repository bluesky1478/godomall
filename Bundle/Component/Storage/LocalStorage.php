<?php
namespace Bundle\Component\Storage;

use Framework\StaticProxy\Proxy\Request;
use Framework\StaticProxy\Proxy\UserFilePath;
use League\Flysystem\Adapter\Local;

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
class LocalStorage extends \Component\Storage\AbstractStorage
{
    protected $realPath;

    public function __construct($pathCode, $storageName)
    {
        $basePath = $this->getDiskPath($pathCode);
        $this->storageName = $storageName;
        $this->basePath = $basePath->www();
        $this->realPath = (string)$basePath;
        $this->setAdapter();
    }

    protected function setAdapter()
    {
        parent::__construct(new Local((string)$this->realPath));
    }

    public function getPath($pathCodeDirName)
    {
        return UserFilePath::data($pathCodeDirName);
    }

    public function isFile($filePath)
    {
        return is_file(realpath($this->getRealPath($filePath)));
    }

    public function getHttpPath($filePath)
    {
        $result = $this->basePath . DS . $filePath;
        return $result;
    }

    public function getFilename($filePath)
    {
        if (substr($filePath, -1) != DS || substr($filePath, -1) != '\\') {
            return basename($filePath);
        }

        return null;
    }

    public function getMountPath($filePath)
    {
        return $filePath;
    }

    public function getRealPath($filePath)
    {
        return $this->getAdapter()->getPathPrefix() . $filePath;
    }

    public function getDownLoadPath($filePath)
    {
        return $this->getRealPath($filePath);
    }

    final public function download($filePath, $downloadFilename)
    {
        $realPath = $this->getRealPath($filePath);
        parent::setDownloadHeader($realPath, $downloadFilename);
    }

    public function isFileExists($filePath)
    {
        return file_exists(realpath($this->getRealPath($filePath)));
    }

    public function delete($filePath)
    {
        //if ($this->isFile($filePath)) {
        if ($this->isFileExists($filePath)) {
            return parent::delete($filePath);
        }

        return false;
    }

    public function rename($oldFilePath, $newFilePath)
    {
        return @rename($oldFilePath, $newFilePath);
    }

}
