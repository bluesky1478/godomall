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

namespace Bundle\Component\Design;

use Component\Database\DBTableField;
use Component\Board\BoardAdmin;
use Component\Board\BoardTheme;
use Component\Policy\Policy;
use Component\Validator\Validator;
use Component\Storage\Storage;
use Framework\Utility\ImageUtils;
use DirectoryIterator;
use FileHandler;
use Globals;
use Message;
use Session;
use UserFilePath;

/**
 * 스킨 처리 클래스
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SkinSave extends \Component\Design\SkinBase
{
    private $skinNameMaxLength = 20;

    private $archiveExceptDir = ['.', '..', '.svn', '__gd__history', '__gd__preview',];

    /**
     * 스킨 설정 저장
     * @param array $data 스킨이름(폴더명)
     * @return bool 처리결과
     * @throws \Exception
     */
    public function saveSkinConfig(array $data, $mallSno = DEFAULT_MALL_NUMBER)
    {
        $saveData = ['frontLive', 'frontWork', 'mobileLive', 'mobileWork'];

        // Validation
        $validator = new Validator();

        // Set
        $conf = gd_policy('design.skin', $mallSno);

        //디폴트 게시판 스킨초기화
        $this->initBoardSkinSetting($data,$mallSno);

        foreach ($saveData as $sVal) {
            if (isset($data[$sVal]) === true) {
                $conf[$sVal] = $data[$sVal];
                $validator->add($sVal, 'designSkinName');
            }
        }

        // Validation
        if ($validator->act($data, true) === false) {
            throw new \Exception(implode('\n', $validator->errors));
        }

        // Save
        $config = new Policy();

        return $config->setValue('design.skin', $conf, $mallSno);
    }

    private function initBoardSkinSetting($data, $mallSno = DEFAULT_MALL_NUMBER)
    {
        if ($data['domainFl']) {
            $domainFl = $data['domainFl'];
        } else {
            $domainFl = $mallSno == DEFAULT_MALL_NUMBER ? '' : \Globals::get('gGlobal.useMallList.' . $mallSno . '.domainFl');
        }

        $globalLiveSkin['front'] = Globals::get('gGlobal.useMallList.'.$data['sno'].'.skin.frontLive');
        $globalLiveSkin['mobile'] = Globals::get('gGlobal.useMallList.'.$data['sno'].'.skin.mobileLive');
        if (empty($data['frontLive']) === false) {
            if ($globalLiveSkin['front'] != $data['frontLive']) {
                $this->updateBoardTheme($data['frontLive'] , $domainFl, 'n');
            }
        }
        if (empty($data['mobileLive']) === false) {
            if ($globalLiveSkin['mobile'] != $data['mobileLive']) {
                $this->updateBoardTheme($data['mobileLive'] , $domainFl, 'y');
            }
        }
    }

    protected function updateBoardTheme($skinName , $domainFl, $isMobile = 'n'){
        $board = new BoardAdmin();
        $boardList = $board->getBoardList(null, false, null, false, null);
        //프론트용
        $updateField = $isMobile == 'y' ? "mobileTheme" . $domainFl . "Sno" : "theme" . $domainFl . "Sno" ;
        foreach ($boardList['data'] as $row) {
            $query = "SELECT sno FROM " . DB_BOARD_THEME . " WHERE liveSkin='" . $skinName . "' AND bdMobileFl ='" . $isMobile . "' AND themeId='" . $row['bdKind'] . "' AND bdBasicFl='y' ";
            $result = $this->db->query_fetch($query, null, false);
            $skinSno = $result['sno'];
            if ($skinSno) {
                $query = "UPDATE " . DB_BOARD . " SET " . $updateField . " = " . $skinSno . " WHERE bdId='" . $row['bdId'] . "' ";
                $this->db->query($query);
            }
        }
    }

    protected function initBoardSkinSettingWrapper($data, $sno = DEFAULT_MALL_NUMBER)
    {
        $this->initBoardSkinSetting($data, DEFAULT_MALL_NUMBER);
    }

    /**
     * 배너 디비 처리
     *
     * @param string $skinType 스킨 타입 (front, mobile)
     * @param string $skinName 스킨 명
     * @param string $handleMode 처리 모드 (conf, copy, delete, upload)
     * @param string $handleParam 처리시 필요한 데이터 (conf => 경로, copy => 복사할 스킨명)
     * @return bool 처리결과
     */
    public function _setBannerDb($skinType, $skinName, $handleMode, $handleParam = null)
    {
        // Banner DB
        $designBanner = \App::load('\\Component\\Design\\DesignBanner');

        // 처리 모드가 delete 인경우
        if ($handleMode === 'delete') {
            // SQL 처리
            $result = $designBanner->deleteBannerAllSkin($skinType, $skinName);
        } // 처리 모드가 upload 인경우
        elseif ($handleMode === 'upload') {
            $sqlFile = $handleParam . 'banner_data.sql';
            $getData = FileHandler::read($sqlFile);
            if (empty($getData) === false) {
                $getData = explode(PHP_EOL, $getData);
                foreach ($getData as $bannerSQL) {
                    if (empty($bannerSQL) === false) {
                        $bannerSQL = str_replace('___SKIN_NAME___', $skinName, $bannerSQL);
                        $result = $this->db->query($bannerSQL);
                    }
                }
            }
        } // 그외
        else {
            // SQL 처리
            $result = true;
            $bannerDB = $designBanner->getBannerDownData($skinType, $skinName);

            // 처리 모드가 conf 인경우 화일로 저장
            if ($handleMode === 'conf' && empty($handleParam) === false) {
                $confFile = $handleParam . 'banner_data.sql';
                if (empty($bannerDB) === false) {
                    $result = FileHandler::write($confFile, implode(PHP_EOL, $bannerDB));
                }
            }

            // 처리 모드가 copy 인경우 디비에 insert
            if ($handleMode === 'copy' && empty($handleParam) === false) {
                //추가된 게시판스킨 복사
                $arrBind = null;
                $boardThemeField = implode(',', DBTableField::setTableField('tableBoardTheme', null, ['liveSkin','regDt,modDt']));
                $query = "INSERT INTO ".DB_BOARD_THEME."(liveSkin, ".$boardThemeField.") ";
                $query.= " SELECT '".$handleParam."',".$boardThemeField." FROM ".DB_BOARD_THEME." WHERE liveSkin = ? AND bdBasicFl = 'n' AND bdMobileFl = ?";
                $this->db->bind_param_push($arrBind, 's',$skinName);
                $this->db->bind_param_push($arrBind, 's',$skinType == 'mobile' ? 'y' : 'n');
                $this->db->bind_query($query, $arrBind);

                if (empty($bannerDB) === false) {
                    foreach ($bannerDB as $bannerSQL) {
                        $bannerSQL = str_replace('___SKIN_NAME___', $handleParam, $bannerSQL);
                        $result = $this->db->query($bannerSQL);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 스킨 복사
     * @param array $getData 복사할 정보
     * @return bool 처리결과
     * @throws \Exception
     */
    public function copySkin(array $getData)
    {
        // Validation
        if (Validator::required($getData['skinName']) === false) {
            throw new \Exception(sprintf(__('%s 스킨이 존재하지 않습니다. 스킨 설정을 다시 확인해 주시기 바랍니다.'), $getData['skinName']));
        }
        if (Validator::maxlen($this->skinNameMaxLength, $getData['copySkinCode'], true) === false) {
            throw new \Exception(sprintf(__('스킨명은 최대 %s 자리까지만 가능합니다.'), $this->skinNameMaxLength));
        }

        // 스킨코드 공백 체크
        $getData['copySkinCode'] = trim($getData['copySkinCode']);
        $getData['copySkinCode'] = str_replace(' ', '_', $getData['copySkinCode']);

        // 경로 설정
        $oriSkinPath = $this->skinRootPath->add($getData['skinName']);
        $targetSkinPath = $this->skinRootPath->add($getData['copySkinCode']);

        // 이미 존재한 스킨인지 체크
        if ($targetSkinPath->isDir() === true) {
            throw new \Exception(sprintf(__('%s 스킨과 동일한 스킨이 존재합니다. 다시 확인해 주시기 바랍니다.'), $getData['copySkinCode']));
        }

        // 스킨 폴더 복사 진행
        FileHandler::copy($oriSkinPath, $targetSkinPath, true);

        // 설정 화일 복사 진행
        foreach (new DirectoryIterator($this->skinConfigDir) as $fileInfo) {
            if ($fileInfo->isFile() === true) {
                foreach ($this->skinConfExt as $extVal) {
                    if ($fileInfo->getBasename($extVal) === $getData['skinName']) {
                        $tmp = str_replace($getData['skinName'], $getData['copySkinCode'], $fileInfo->getBasename());
                        FileHandler::copy($fileInfo->getPathname(), $this->skinConfigDir . $tmp);
                    }
                }
            }
        }

        // 기존 스킨 정보
        $oriSkinInfo = $this->getSkinInfo($getData['skinName']);

        // 스킨 txt 작성
        $data = gd_policy('basic.info');
        $txtContents = [];
        $txtContents[] = 'skin_name : ' . (empty($getData['copySkinName']) === true ? $getData['copySkinCode'] : $getData['copySkinName']);
        $txtContents[] = 'skin_code : ' . $getData['copySkinCode'];
        $txtContents[] = 'skin_copy_date : ' . date('Y-m-d');
        $txtContents[] = 'skin_version : ' . $oriSkinInfo['skin_version'];
        if (empty($oriSkinInfo['skin_device']) === false) {
            $txtContents[] = 'skin_device : ' . $oriSkinInfo['skin_device'];
        }
        if (empty($oriSkinInfo['skin_country']) === false) {
            $txtContents[] = 'skin_country : ' . $oriSkinInfo['skin_country'];
        }
        $txtContents[] = 'apply_solution : 고도몰5';
        $txtContents[] = 'worker : ' . $data['mallNm'] . ' - ' . Session::get('manager.managerNm');
        $txtContents[] = 'notice1 : ' . $getData['skinName'] . ' 스킨에서 ' . $getData['copySkinCode'] . ' 스킨으로 복사한 스킨임';

        FileHandler::write($this->skinConfigDir . $getData['copySkinCode'] . '.txt', implode(PHP_EOL, $txtContents));

        // Banner DB
        $this->_setBannerDb($getData['skinType'], $getData['skinName'], 'copy', $getData['copySkinCode']);

        // 계정용량 갱신 - 스킨
        gd_set_du('skin');

        return true;
    }

    /**
     * 스킨 수정
     * @param string $getData 수정할 정보
     * @param string $getFile 수정할 대표이미지
     * @return bool 처리결과
     * @throws \Exception
     */
    public function modifySkin($getData, $getFile)
    {
        $data = parent::getSkinInfo($getData['skinCode']);

        // 스킨 썸네일 업로드
        $this->skinThumbnailsUpload($getData['skinCode'], $getFile);

        $txtContents = [];
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'skin_name';
                    $txtContents[] = $key . ' : ' . $getData['skinName'];
                    break;
                case 'skin_cover';
                    continue;
                    break;
                default:
                    $txtContents[] = $key . ' : ' . $value;
                    break;
            }
        }

        FileHandler::write($this->skinConfigDir . $getData['skinCode'] . '.txt', implode(PHP_EOL, $txtContents));
        return true;
    }

    /**
     * 스킨 삭제
     * @param string $skinName 스킨명
     * @return bool 처리결과
     * @throws \Exception
     */
    public function deleteSkin($skinName)
    {
        // Validation
        if (Validator::required($skinName) === false) {
            throw new \Exception(sprintf(__('%s 스킨이 존재하지 않습니다. 스킨 설정을 다시 확인해 주시기 바랍니다.'), $skinName));
        }

        // 경로 설정
        $deletePath = $this->skinRootPath->add($skinName);

        // 스킨 폴더 삭제 진행
        FileHandler::delete($deletePath, true);

        // Banner DB
        $this->_setBannerDb($this->skinType, $skinName, 'delete');

        // 게시판스킨  DB 삭제처리
        $boardTheme = new BoardTheme();
        $boardTheme->deleteBySkinName($skinName, $this->skinType);

        // 설정 화일 삭제 진행
        foreach (new DirectoryIterator($this->skinConfigDir) as $fileInfo) {
            if ($fileInfo->isFile() === true) {
                foreach ($this->skinConfExt as $extVal) {
                    if ($fileInfo->getBasename($extVal) === $skinName) {
                        FileHandler::delete($fileInfo->getPathname());
                    }
                }
            }
        }

        // 계정용량 갱신 - 스킨
        gd_set_du('skin');

        return true;
    }

    /**
     * 스킨 다운
     * @param string $skinName 스킨명
     * @return boolean 처리결과
     * @throws \Exception
     */
    public function downSkin($skinName)
    {
        // Validation
        if (Validator::required($skinName) === false) {
            throw new \Exception(sprintf(__('%s 스킨이 존재하지 않습니다. 스킨 설정을 다시 확인해 주시기 바랍니다.'), $skinName));
        }

        // 경로 설정
        $compressPath = $this->skinRootPath->add($skinName) . DS;
        $confPath = $compressPath . '__conf__' . DS;
        $backupFileName = UserFilePath::temporary('skin_copy') . DS . $skinName . '_backup.zip';

        // 스킨 폴더에 설정 화일 복사 진행
        foreach (new DirectoryIterator($this->skinConfigDir) as $fileInfo) {
            if ($fileInfo->isFile() === true) {
                foreach ($this->skinConfExt as $extVal) {
                    if ($fileInfo->getBasename($extVal) === $skinName) {
                        FileHandler::copy($fileInfo->getPathname(), $confPath . $fileInfo->getBasename());
                    }
                }
            }
        }

        // Banner DB
        $result = $this->_setBannerDb($this->skinType, $skinName, 'conf', $confPath);
        if ($result === false) {
            // 설정 화일 삭제 진행
            FileHandler::delete($confPath, true);
            throw new \Exception(sprintf(__('%s 스킨 다운로드시 오류가 발생되었습니다.'), $skinName));
        }

        // 해당 스킨을 압축
        $archive = \App::load('\\Framework\\File\\Archive\\ArchiveHandler');
        $archive->create($backupFileName, [$compressPath], null, 'zip', $compressPath);

        // 스킨 다운
        if (FileHandler::isFile($backupFileName) === true) {
            header("Content-type: application/zip");
            header("Content-disposition:attachment;filename=" . str_replace(dirname($backupFileName) . DS, '', $backupFileName));
            header("Content-length:" . filesize($backupFileName));
            header("Content-Transfer-Encoding: binary");
            header("Pragma: no-cache");
            header("Expires: 0");
            readfile($backupFileName);

            // 압축스킨파일 삭제
            FileHandler::delete($backupFileName, true);
        } else {
            // 설정 화일 삭제 진행
            FileHandler::delete($confPath, true);
            throw new \Exception(sprintf(__('%s 스킨 다운로드시 오류가 발생되었습니다.'), $skinName));
        }

        // 설정 화일 삭제 진행
        FileHandler::delete($confPath, true);

        return true;
    }

    /**
     * 스킨 업로드
     * @param array $getData 업로드 정보
     * @param string $getFile 업로드 한 스킨 압축화일
     * @return bool 처리결과
     * @throws \Exception
     */
    public function uploadSkin(array $getData, $getFile)
    {
        // 업로드한 zip 화일 오류 여부
        if ($getFile['uploadSkin']['error'] !== '0') {
            /**
             * 에러 코드 설명
             * 1. 업로드한 파일이 php.ini upload_max_filesize 지시어보다 큽니다.
             * 2. 업로드한 파일이 HTML 폼에서 지정한 MAX_FILE_SIZE 지시어보다 큽니다.
             * 3. 파일이 일부분만 전송되었습니다.
             * 4. 파일이 전송되지 않았습니다.
             * 6. 임시 폴더가 없습니다.
             * 7. 디스크에 파일 쓰기를 실패했습니다.
             * 8. 확장에 의해 파일 업로드가 중지되었습니다.
             */
            switch ($getFile['uploadSkin']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    throw new \Exception(__('업로드할 스킨 압축파일(zip)이 ' . ini_get('upload_max_filesize') . '보다 클 수 없습니다.'));
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new \Exception(__('업로드할 스킨 압축파일(zip)이 전송되지 않았습니다.'));
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    throw new \Exception(__('업로드할 스킨 압축파일(zip)이 일부분만 전송되었습니다.'));
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    throw new \Exception(__('디스크에 업로드할 스킨 압축파일(zip) 쓰기를 실패했습니다.'));
                    break;
                case UPLOAD_ERR_EXTENSION:
                    throw new \Exception(__('확장에 의해 업로드할 스킨 압축파일(zip) 업로드가 중지되었습니다.'));
                    break;
                default:
                    throw new \Exception(__('업로드할 스킨 압축파일(zip)에 오류가 있습니다.'));
                    break;
            }
        }

        // Validation
        if (Validator::maxlen($this->skinNameMaxLength, $getData['uploadSkinCode'], true) === false) {
            throw new \Exception(sprintf(__('스킨명은 최대 %s 자리까지만 가능합니다.'), $this->skinNameMaxLength));
        }

        // 스킨코드 공백 체크
        $getData['uploadSkinCode'] = trim($getData['uploadSkinCode']);
        $getData['uploadSkinCode'] = str_replace(' ', '_', $getData['uploadSkinCode']);

        // 경로 설정
        $newSkinPath = $this->skinRootPath->add($getData['uploadSkinCode']);
        $extractPath = UserFilePath::temporary('skin_copy');
        $confPath = $newSkinPath . DS . '__conf__' . DS;

        // 이미 존재한 스킨인지 체크
        if ($newSkinPath->isDir() === true) {
            throw new \Exception(sprintf(__('%s 스킨과 동일한 스킨이 존재합니다. 다시 확인해 주시기 바랍니다.'), $getData['uploadSkinCode']));
        }

        // 폴더내 화일을 모두 삭제
        $this->_emptyTempFolder($extractPath);

        // 폴더 생성
        $tmpNewPath = $extractPath . DS . $getData['uploadSkinCode'] . DS;
        $result = FileHandler::makeDirectory($tmpNewPath);
        if ($result !== true) {
            throw new \Exception(__('스킨 업로드시 오류가 발생되었습니다.') . ' : Create fails');
        }

        // 압축 해제
        $archive = \App::load('\\Framework\\File\\Archive\\ArchiveHandler');
        $archive->extract($getFile['uploadSkin']['tmp_name'], $tmpNewPath, null, 'zip');

        // 압축 해제후 퍼미션 변경
        $this->_filePermnChange($tmpNewPath);

        // 업로드할 스킨의 정보 체크
        $tmpConfPath = $tmpNewPath . '__conf__' . DS;
        $tmpInfo = $this->uploadSkinInfoCheck($tmpConfPath);

        // 스킨 폴더 이동 (tmp -> skin)
        $result = FileHandler::move($tmpNewPath, $newSkinPath);

        if ($result === true) {
            // 설정 화일 복사 진행
            foreach (new DirectoryIterator($confPath) as $fileInfo) {
                if ($fileInfo->isFile() === true) {
                    if (in_array(('.' . $fileInfo->getExtension()), $this->skinConfExt)) {
                        // 설정 화일 이동
                        $result = FileHandler::move($fileInfo->getPathname(), $this->skinConfigDir . $getData['uploadSkinCode'] . '.' . $fileInfo->getExtension());
                    }
                }
            }

            // 스킨 썸네일 업로드
            $this->skinThumbnailsUpload($getData['uploadSkinCode'], $getFile);

            // 스킨 txt 작성
            $data = gd_policy('basic.info');
            $txtContents = [];
            $txtContents[] = 'skin_name : ' . (empty($getData['uploadSkinName']) === true ? $getData['uploadSkinCode'] : $getData['uploadSkinName']);
            $txtContents[] = 'skin_code : ' . $getData['uploadSkinCode'];
            if (empty($tmpInfo['skin_version']) === false) {
                $txtContents[] = 'skin_version : ' . $tmpInfo['skin_version'];
            }
            $txtContents[] = 'skin_upload_date : ' . date('Y-m-d');
            if (empty($tmpInfo['skin_device']) === false) {
                $txtContents[] = 'skin_device : ' . $tmpInfo['skin_device'];
            }
            if (empty($tmpInfo['skin_country']) === false) {
                $txtContents[] = 'skin_country : ' . $tmpInfo['skin_country'];
            }
            $txtContents[] = 'apply_solution : 고도몰5';
            $txtContents[] = 'worker : ' . $data['mallNm'] . ' - ' . Session::get('manager.managerNm');
            $txtContents[] = 'notice1 : 압축화일(zip)로 업로드한 스킨임';
            if (empty($tmpInfo['skin_name']) === false) {
                $txtContents[] = 'notice2 : ' . $tmpInfo['skin_name'] . '(' . $tmpInfo['skin_code'] . ') - ' . $tmpInfo['skin_date'];
            }

            FileHandler::write($this->skinConfigDir . $getData['uploadSkinCode'] . '.txt', implode(PHP_EOL, $txtContents));

            // Banner DB
            $this->_setBannerDb($this->skinType, $getData['uploadSkinCode'], 'upload', $confPath);

            // 설정화일 있는 폴더 삭제
            FileHandler::delete($confPath, true);

        } else {
            throw new \Exception(__('스킨 업로드시 오류가 발생되었습니다.') . ' : Move fails');
        }

        // 폴더내 화일을 모두 삭제
        $this->_emptyTempFolder($extractPath);

        // 계정용량 갱신 - 스킨
        gd_set_du('skin');

        return true;
    }

    /**
     * 업로드할 스킨의 정보 체크
     * @param string $ConfPath 스킨의 conf 폴더 경로
     * @return bool|array 처리결과
     * @throws \Exception
     */
    public function uploadSkinInfoCheck($ConfPath)
    {
        // 해당 경로 체크
        if (is_dir($ConfPath) === false) {
            return false;
        }

        // 스킨 정보
        $setData = [];

        // 설정 화일 체크
        foreach (new DirectoryIterator($ConfPath) as $fileInfo) {
            if ($fileInfo->isFile() === true) {
                if ($fileInfo->getExtension() === 'txt') {
                    // 추출할 정보
                    $arrInfo = ['skin_name', 'skin_code', 'skin_version', 'skin_date', 'skin_device', 'skin_country',];

                    // 스킨 정보 추출
                    $getData = FileHandler::read($fileInfo->getPathname());
                    if (empty($getData) === false) {
                        $getData = explode(PHP_EOL, $getData);
                        foreach ($getData as $iVal) {
                            if (empty($iVal) === false) {
                                $tmp = explode(':', $iVal);
                                if (isset($tmp[1]) === true) {
                                    if (in_array(trim($tmp[0]), $arrInfo) === true) {
                                        $setData[trim($tmp[0])] = trim($tmp[1]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // 스킨 디바이스 체크
        if (empty($setData['skin_device']) === false) {
            if ($setData['skin_device'] !== $this->skinType) {
                throw new \Exception(__('스킨 업로드시 오류가 발생되었습니다.') . ' : ' . sprintf(__('현재 업로드하시는 스킨은 %s 스킨 입니다. 다시 확인해 주시기 바랍니다.'), $this->deviceType[$setData['skin_device']]));
            }
        }

        return $setData;
    }

    /**
     * 해당 폴더내 모든 화일과 폴더 퍼미션 700 변경
     *
     * @param string $newPath 퍼미션 변경할 폴더
     * @return bool 처리결과
     */
    public function _filePermnChange($newPath)
    {
        if (FileHandler::isDirectory($newPath)) {
            foreach (new DirectoryIterator($newPath) as $fileInfo) {
                if ($fileInfo->isDot() === true) {
                    continue;
                }
                if (in_array($fileInfo->getFilename(), $this->archiveExceptDir) === true) {
                    continue;
                }

                // 폴더인 경우 퍼미션 변경후 재귀
                FileHandler::chmod($fileInfo->getPathname(), 0707);
                $this->_filePermnChange($fileInfo->getPathname());
            }
        } else {
            if (FileHandler::isFile($newPath)) {
                // 화일 퍼미션 변경후 완료
                $result = FileHandler::chmod($newPath, 0707);
                return $result;
            }
        }
    }

    /**
     * temporary->skin_copy 폴더 비우기
     *
     * @param string $savePath 폴더
     */
    public function _emptyTempFolder($savePath)
    {
        // 폴더내 화일을 모두 삭제
        foreach (new DirectoryIterator($savePath) as $fileInfo) {
            if ($fileInfo->isDot() === true) {
                continue;
            }
            if (empty($fileInfo->getPathname()) === false && in_array($fileInfo->getFilename(), $this->archiveExceptDir) === false) {
                FileHandler::delete($fileInfo->getPathname(), true);
            }
        }
    }

    /**
     * 스킨 썸네일 이미지 업로드
     *
     * @param $skinCode 스킨코드
     * @param $getFile 썸네일이미지 정보
     * @return bool|void
     */
    public function skinThumbnailsUpload($skinCode, $getFile)
    {
        if (empty($skinCode) === true) return;
        if (empty($getFile['thumbnails']['name']) === false && $getFile['thumbnails']['size'] > 0) {
            if (gd_file_uploadable($getFile['thumbnails'], 'image') === true) {
                $thumbnailName = $skinCode . '.jpg';
                $confPath = $uploadPath = UserFilePath::data('conf', 'skin_' . $this->skinType);
                ImageUtils::thumbnail($getFile['thumbnails']['tmp_name'], $confPath . DS . $thumbnailName, 150, 150, 5);
            }
        }

        return $thumbnailName;
    }

    public function mallIconConfig($getValue, $files = [])
    {
        $getValue = array_merge(gd_policy('design.mallIconType'), $getValue);
        $menuType =
        $iconTail = '';
        if ($getValue['menuType'] == 'mobile') {
            $menuType = ucfirst($getValue['menuType']);
            $iconTail = '_' . $getValue['menuType'];
        }

        if (is_array($getValue['mallIconDel']) === true) {
            foreach ($getValue['mallIconDel'] as $key => $icon) {
                Storage::disk(Storage::PATH_CODE_COMMONIMG)->delete($icon);
                if (empty($getValue['mallIcon' . $menuType][$key]) === false) {
                    unset($getValue['mallIcon' . $menuType][$key]);
                }
            }
        }

        if (is_array($files['mallIcon' . $menuType]) === true) {
            foreach (array_filter($files['mallIcon' . $menuType]['name']) as $key => $value) {
                if (gd_file_uploadable($files['mallIcon' . $menuType], 'image', $key) === false) {
                    throw new \Exception(__('이미지파일을 업로드해주세요'));
                }
                if ($files['mallIcon' . $menuType]['size'][$key] > (1024 * 500)) {
                    throw new \Exception(__('500kb이하의 파일만 가능합니다.'));
                }
                $ext = explode('.', $value);
                $extension = array_pop($ext);
                $iconName = 'ico_' . $getValue['mallDomainFl'][$key] . $iconTail . '.' . $extension;

                Storage::disk(Storage::PATH_CODE_COMMONIMG)->upload($files['mallIcon' . $menuType]['tmp_name'][$key], $iconName);
                $getValue['mallIcon' . $menuType][$key] = $iconName;
            }
        }
        unset($getValue['mode']);
        unset($getValue['menuType']);
        unset($getValue['mallDomainFl']);
        unset($getValue['mallIconDel']);

        gd_set_policy('design.mallIconType', $getValue);
    }
}
