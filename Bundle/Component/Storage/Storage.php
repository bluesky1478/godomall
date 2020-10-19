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
use Framework\StaticProxy\Proxy\Session;
use League\Flysystem;
use League\Flysystem\MountManager;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Sftp\SftpAdapter;


class Storage
{
    const PATH_CODE_BOARD = 1;
    const PATH_CODE_GOODS = 3;
    const PATH_CODE_ADD_GOODS = 4;
    const PATH_CODE_GOODS_ICON = 5;
    const PATH_CODE_GIFT = 6;
    const PATH_CODE_EDITOR = 7;
    const PATH_CODE_ETC = 8;
    const PATH_CODE_MOBILE = 9;
    const PATH_CODE_COUPON_BG = 10;
    const PATH_CODE_COUPON_IMAGE = 11;
    const PATH_CODE_GROUP_ICON = 12;
    const PATH_CODE_GROUP_IMAGE = 13;
    const PATH_CODE_CATEGORY = 14;
    const PATH_CODE_BRAND = 15;
    const PATH_CODE_SCM = 16;
    const PATH_CODE_FRONT_SKIN_CODI = 17;
    const PATH_CODE_MOBILE_SKIN_CODI = 18;
    const PATH_CODE_CHECK = 19;
    const PATH_CODE_COMMON = 20;
    const PATH_CODE_ATTENDANCE_ICON_USER = 21;
    const PATH_CODE_GHOST_DEPOSITOR_BANNER = 22;
    const PATH_CODE_MULTI_POPUP = 23;
    const PATH_CODE_POLL = 24;
    const PATH_CODE_DISPLAY = 25;
    const PATH_CODE_COMMONIMG = 26;
    const PATH_CODE_DEFAULT = 27;
    const PATH_CODE_PLUS_REIVEW = 28;
    const PATH_CODE_EVENT_GROUP = 29;
    const PATH_CODE_EVENT_GROUP_TMP = 30;
    const PATH_CODE_JOIN_EVENT = 31;

    public static function copy($pathCode, $toStorageName, $toPath, $fromStorageName, $fromPath, $recursive = false)
    {
        $toStorage = Storage::disk($pathCode, $toStorageName, true);
        $fromStorage = Storage::disk($pathCode, $fromStorageName, true);

        $manager = new MountManager([
            'to' => $toStorage,
            'from' => $fromStorage,
        ]);
        if ($toStorage->isFile($toPath)) {     //개별파일 복사인경우
            $result = $manager->copy('to://' . $toStorage->getMountPath($toPath), 'from://' . $fromStorage->getMountPath($fromPath));
            return $result;
        }
        set_time_limit(1000);
        $result = false;
        $contents = $manager->listContents('to://' . $toPath, $recursive);
        $addRecursive = '';
        foreach ($contents as $entry) {
            $update = true;
            if ($update) {
                if ($entry['type'] == 'file') {
                    $result = $manager->copy('to://' . $entry['path'], 'from://' . $fromStorage->getMountPath($fromPath . $addRecursive . $entry['basename']));
                } else {
                    $addRecursive .= $entry['filename'] . DS;
                }
            }
        }

        return $result;
    }

    /**
     * disk
     *
     * @static
     * @param $pathCode
     * @param $storageName
     * @return FtpStorage|LocalStorage
     * @throws \Exception
     */
    public static function disk($pathCode, $storageName = 'local', $isSetAdapter = false)
    {
        if ($storageName == 'local') {
            $storage = new LocalStorage($pathCode, $storageName, $isSetAdapter);
        } else if ($storageName == 'http') {
            $storage = new HttpStorage($pathCode, $storageName, $isSetAdapter);
        } else {
            $storageInfo = gd_policy('basic.storage');
            foreach ($storageInfo['httpUrl'] as $key => $val) {
                if (strtoupper($storageName) == strtoupper($val)) {
                    foreach ($storageInfo as $uKey => $uVal) {
                        $ftpData[$uKey] = '';
                        if (empty($storageInfo[$uKey][$key]) == false) {
                            $ftpData[$uKey] = $storageInfo[$uKey][$key];
                        }
                    }
                    break;
                }
            }

            if ($ftpData['ftpType'] == 'sftp') {
                $storage = new SftpStorage($pathCode, $storageName, $ftpData, $isSetAdapter);
            } else {
                $storage = new FtpStorage($pathCode, $storageName, $ftpData, $isSetAdapter);
            }
        }

        return $storage;
    }

    /**
     * custom disk
     * 임의로 ftp정보를 받아서 연결하는경우
     *
     * @static
     * @param $ftpData
     * @param $isSetAdapter
     * @return FtpStorage
     * @throws \Exception
     */
    public static function customDisk($ftpData, $isSetAdapter = false)
    {
        if ($ftpData['ftpType'] == 'sftp') {
            $storage = new SftpStorage('', 'custom', $ftpData, $isSetAdapter);
        } else {
            $storage = new FtpStorage('', 'custom', $ftpData, $isSetAdapter);
        }

        return $storage;
    }

    public static function customCopy($pathCode, $toStorageName, $toPath, $ftpData, $fromPath)
    {
        $toStorage = Storage::disk($pathCode, $toStorageName, true);
        $fromStorage = Storage::customDisk($ftpData, true);

        $manager = new MountManager([
            'to' => $toStorage,
            'from' => $fromStorage,
        ]);

        if ($toStorage->isFile($toPath)) { //개별파일 복사인경우
            $result = $manager->copy('to://' . $toStorage->getMountPath($toPath), 'from://' . $fromStorage->getMountPath($fromPath));
            return $result;
        }
    }

    /**
     * checkUseStorage
     *
     * @static
     * @param $ftpData
     * @return bool
     * @throws \Exception
     */
    public static function checkUseStorage($ftpData)
    {
        $passive = $ftpData['passive'] == 'y' ? true : false;
        if($ftpData['ftpPw'] == '******') {
            $ftpPw = \Encryptor::decrypt($ftpData['oldFtpPw']);
        }else {
            $ftpPw = $ftpData['ftpPw'];
        }

        if ($ftpData['ftpType'] == 'ftp') {
            $storage = new FtpAdapter([
                    'host' => $ftpData['ftpHost'],
                    'username' => $ftpData['ftpId'],
                    'password' => $ftpPw,
                    'port' => $ftpData['ftpPort'],
                    'root' => $ftpData['ftpPath'],
                    'passive' => $passive,
                    'ssl' => false,
                    'timeout' => 30,
                ]
            );

        } else {
            $storage = new SftpAdapter([
                    'host' => $ftpData['ftpHost'],
                    'username' => $ftpData['ftpId'],
                    'password' => $ftpPw,
                    'port' => $ftpData['ftpPort'],
                    'root' => $ftpData['ftpPath'],
                    'timeout' => 30,
                    'privateKey' => '',
                ]
            );
        }
        try {
            $storage->connect();
        } catch (\Exception $e) {
            throw $e;
        }

        return true;
    }


}
