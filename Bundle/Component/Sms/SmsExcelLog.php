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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Sms;

use Respect\Validation\Validator;


/**
 * SMS 발송대상 엑셀업로드
 * @package Bundle\Component\Sms
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class SmsExcelLog
{
    /**
     * 업로드키 값을 이용하여 수신대상을 카운트
     *
     * @param $uploadKey
     *
     * @return array
     * @throws \Exception
     */
    public function countValidationLogByUploadKey($uploadKey)
    {
        if (!$this->validateUploadKey($uploadKey)) {
            throw new \Exception('잘못된 업로드키 입니다.');
        }
        $dao = \App::load('Component\\Sms\\SmsExcelLogDAO');
        $count = $dao->countValidationLogByUploadKey($uploadKey);

        return $count;
    }

    /**
     * 엑셀 업로드 검증을 통화한 수신대상 조회
     *
     * @param $uploadKey
     *
     * @return array
     * @throws \Exception
     */
    public function getValidationLogByUploadKey($uploadKey)
    {
        if (!$this->validateUploadKey($uploadKey)) {
            throw new \Exception('잘못된 업로드키 입니다.');
        }
        $dao = \App::load('Component\\Sms\\SmsExcelLogDAO');
        $logs = $dao->selectValidationLogByUploadKey($uploadKey);

        return $logs;
    }

    /**
     * 업로드키 검증
     *
     * @param $uploadKey
     *
     * @return bool
     */
    protected function validateUploadKey($uploadKey)
    {
        $uploadDt = substr($uploadKey, 0, (strlen($uploadKey) - 4));
        $validate = Validator::date('ymdHis')->validate($uploadDt);
        if (!$validate) {
            $logger = \App::getInstance('logger');
            $logger->error(sprintf('Wrong upload key. [%s]', $uploadKey));
        }

        return $validate;
    }

    /**
     * 엑셀 업로드키로 대상 삭제
     *
     * @param $uploadKey
     *
     * @throws \Exception
     */
    public function deleteLogByUploadKey($uploadKey)
    {
        if (!$this->validateUploadKey($uploadKey)) {
            throw new \Exception('잘못된 업로드키 입니다.');
        }
        $dao = \App::load('Component\\Sms\\SmsExcelLogDAO');
        $dao->deleteLogByUploadKey($uploadKey);
    }
}
