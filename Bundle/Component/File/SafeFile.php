<?php
/**
 * fopen 확장판
 *
 * fopen 을 이용해 파일을 저장시 미리 체크를 해서 계정용량이나 권한여부 체크 및 중복 적용 방지를 위한 락처리
 * @author    artherot
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\File;

use UserFilePath;

class SafeFile
{

    public $filePath;
    public $fileData;
    private $tmpPath;
    private $tmpPathLock;
    private $saveMode;
    private $fpLock;
    private $fpTmp;

    const TRG_FILE_NOT_FOUND = 'File not found';
    const TRG_FILE_ERROR = 'File save error';

    const TEXT_TMP_FILE_NOT_FOUND = '임시 파일 작성에 실패하였습니다. 파일여부와 파일권한을 확인해야 합니다.';
    const TEXT_FILE_SAVE_ERROR = '파일 작성중 오류가 발생했습니다. 계정용량이나 파일권한을 확인해야 합니다.';

    /**
     * 생성자
     */
    function __construct()
    {
        $this->tmpPath = UserFilePath::data('etc', 'chkQuota');
        $this->tmpPathLock = UserFilePath::data('etc', 'chkQuotaLock');
    }

    /**
     * 파일 열기 (fopen 의 w 옵션)
     *
     * @param string $filePath 저장할 파일 경로
     * @param string $saveMode fopen mode
     *
     * @return bool
     * @throws \Exception
     */
    function open($filePath, $saveMode = 'w')
    {
        if (!is_file($this->tmpPathLock)) {
          //  throw new \Exception(self::TRG_FILE_NOT_FOUND, self::TEXT_TMP_FILE_NOT_FOUND);
        }

        $this->filePath = $filePath;
        $this->fileData = '';
        $this->saveMode = $saveMode;

        if (!$this->fpLock = fopen($this->tmpPathLock, $this->saveMode)) {
            throw new \Exception(__('임시 파일 작성에 실패하였습니다. 파일여부와 파일권한을 확인해야 합니다.'));
        }

        if (!flock($this->fpLock, LOCK_EX)) {
            return false;
        }

        if (!$this->fpTmp = fopen($this->tmpPath, $this->saveMode)) {
            throw new \Exception(__('임시 파일 작성에 실패하였습니다. 파일여부와 파일권한을 확인해야 합니다.'));
        }
    }

    /**
     * 임시 파일 저장
     *
     * @param string $getData 저장할 내용
     *
     * @return bool
     * @throws \Exception
     */
    function write($getData)
    {
        if ($this->fpTmp === false) {
            return false;
        }
        if (fwrite($this->fpTmp, $getData) === false) {
            throw new \Exception(self::TRG_FILE_ERROR, __('파일 작성중 오류가 발생했습니다. 계정용량이나 파일권한을 확인해야 합니다.'));
        }
        $this->fileData .= $getData;
    }

    /**
     * 임시 파일 닫기 및 원본 파일 저장 (fclose)
     */
    function close()
    {
        if ($this->fpTmp === false) {
            return false;
        }

        fclose($this->fpTmp);

        $this->fpTmp = fopen($this->tmpPath, $this->saveMode);
        fclose($this->fpTmp);

        if (!$fpOri = @fopen($this->filePath, $this->saveMode)) {
            throw new \Exception(self::TRG_FILE_ERROR . __('파일 작성중 오류가 발생했습니다. 계정용량이나 파일권한을 확인해야 합니다.'));
        }
        fwrite($fpOri, $this->fileData);
        fclose($fpOri);

        flock($this->fpLock, LOCK_UN);
        fclose($this->fpLock);

        $this->fpLock = false;
        $this->fpTmp = false;
    }
}
