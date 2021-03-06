<?php
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

namespace Bundle\Component\Storage;


use Framework\StaticProxy\Proxy\Encryptor;
use League\Flysystem\Sftp\SftpAdapter;

class SftpStorage extends \Component\Storage\AbstractStorage
{
    protected $sftp;

    public function __construct($pathCode, $storageName, $ftpData, $isSetAdapter)
    {
        $this->ftpData = $ftpData;
        if ($ftpData && $isSetAdapter) {
            $this->setAdapter();
        }

        $basePath = $this->getDiskPath($pathCode);
        $this->httpUrl = $ftpData['httpUrl'];
        $this->basePath = $ftpData['savePath'] . DS . $basePath;
        $this->storageName = $storageName;
    }

    protected function setAdapter()
    {
        if (!$this->getAdapter()) {
            parent::__construct(
                new SftpAdapter([
                        'host' => $this->ftpData['ftpHost'],
                        'username' => $this->ftpData['ftpId'],
                        'password' => Encryptor::decrypt($this->ftpData['ftpPw']),
                        'port' => $this->ftpData['ftpPort'],
                        'root' => $this->ftpData['ftpPath'],
                        'timeout' => 90,
                        'privateKey' => '',
                    ]
                )
            );
            $this->sftp = $this->getAdapter()->getConnection();
        }
    }


    public function getPath($pathCodeDirName)
    {
        return $pathCodeDirName;
    }

    public function isFile($filePath)
    {
        if (substr($filePath, -1) == DS || substr($filePath, -1) == '\\') {
            return false;
        }
        return true;
    }

    /**
     * getHttpPath
     *
     * @param $filePath
     * @return string
     */
    public function getHttpPath($filePath)
    {
        return $this->httpUrl . DS . $this->getMountPath($filePath);
    }

    /**
     * getFilename
     *
     * @param $fliePath
     * @return null|string
     */
    public function getFilename($fliePath)
    {
        if ($this->isFile($fliePath)) {
            return basename($fliePath);
        }
        return null;
    }


    public function getMountPath($fliePath)
    {
        return $this->basePath . DS . $fliePath;
    }

    /**
     * getRealPath
     *
     * @param $filePath
     * @return string
     */
    public function getRealPath($filePath)
    {
        return $this->ftpData['ftpPath'] .DS. $this->getMountPath($filePath);
    }

    /**
     * getDownloadPath
     *
     * @param $filePath
     * @return string
     */
    public function getDownloadPath($filePath)
    {
        $this->setAdapter();
        $realPath = $this->getRealPath($filePath);
        $tmpfname = tempnam("/tmp", "storage");
        $local = fopen($tmpfname, 'w');
        $stream = $this->sftp->get($realPath);
        fwrite($local, $stream);
        return $tmpfname;
    }

    /**
     * getDownloadPath
     *
     * @param $filePath
     * @param $downloadFilename
     * @return string
     * @throws \Exception
     */
    final public function download($filePath, $downloadFilename)
    {
        $tmpFilePath = $this->getDownloadPath($filePath);
        parent::setDownloadHeader($tmpFilePath , $downloadFilename);

        @unlink($tmpFilePath);
    }

    public function createDir($dirname, array $config = [])
    {
        $this->setAdapter();
        return (bool)parent::createDir($this->getMountPath($dirname), $config);
    }

    public function deleteDir($dirname)
    {
        $this->setAdapter();
        return (bool)parent::deleteDir($this->getMountPath($dirname));
    }

    public function delete($filePath)
    {
        $this->setAdapter();
        if ($this->isFileExists($filePath)) {
            return parent::delete($this->getMountPath($filePath));
        }
        return false;
    }

    /**
     * getSize
     *
     * @param $filePath
     * @return int
     */
    public function getSize($filePath)
    {
        $this->setAdapter();
        return $this->sftp->size($this->getRealPath($filePath));
    }

    public function isFileExists($filePath)
    {
        $this->setAdapter();
        return $this->sftp->file_exists($this->getRealPath($filePath));
    }

    public function listContents($directory = '', $recursive = false)
    {
        $this->setAdapter();
        return parent::listContents($this->getMountPath($directory), $recursive); // TODO: Change the autogenerated stub
    }

    public function rename($oldFilePath, $newFilePath)
    {
        $this->setAdapter();
        return $this->sftp->rename($oldFilePath, $newFilePath);
    }
}
