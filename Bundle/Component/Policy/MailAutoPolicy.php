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

namespace Bundle\Component\Policy;

use Exception;
use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;
use UserFilePath;

/**
 * Class MailAutoPolicy
 * @package Bundle\Component\Policy
 * @author  yjwee
 */
class MailAutoPolicy extends \Component\Policy\Policy
{
    const KEY = 'mail.configAuto';
    protected $validator;
    /** @var \Framework\File\FileHandler $fileHandler */
    protected $fileHandler;

    public function __construct(array $config = [])
    {
        $storage = is_object($config['storage']) ? $config['storage'] : null;
        parent::__construct($storage);
        $this->validator = \App::getInstance('Validator');
        if (!$this->validator instanceof \Component\Validator\Validator) {
            $this->validator = new \Component\Validator\Validator();
        }
        $this->fileHandler = \App::getInstance('file');
    }


    /**
     * 자동메일설정 저장
     *
     * @param $params
     *
     * @return bool
     * @throws Exception
     */
    public function saveAutoMailConfig($params)
    {
        StringUtils::strIsSet($params['mallSno'], DEFAULT_MALL_NUMBER);
        $this->validateSaveAutoMailConfig($params);
        $type = strtoupper($params['type']);
        $this->saveAutoMailTemplate($params['subject'], 'subject_' . $type . '.php', $params['mallSno']);
        $this->saveAutoMailTemplate($params['body'], 'body_' . $type . '.php', $params['mallSno']);

        $config = ComponentUtils::getPolicy(self::KEY, $params['mallSno']);
        $category = $params['category'];
        $type = $params['type'];
        $autoSendFl = $params['autoSendFl'];
        $senderMail = $params['senderMail'];
        $config[$category][$type] = [
            'autoSendFl' => $autoSendFl,
            'senderMail' => $senderMail,
        ];
        if ($category === 'order') {
            $config[$category][$type]['sendTarget'] = $params['sendTarget'];
            $config[$category][$type]['sendType'] = $params['sendType'];
        }
        if ($category === 'join' && $type === 'qna') {
            $config[$category][$type]['boardInfo'] = json_decode($params['boardInfo'], true);
            $config[$category][$type]['initFl'] = 'y';
        }
        if ($category === 'join' && $type === 'join') {
            $config[$category][$type]['mailDisapproval'] = $params['mailDisapproval'];
        }
        if ($category === 'admin' && $type === 'adminsecurity') {
            $config[$category][$type]['autoSendFl'] = 'y';
        }

        return ComponentUtils::setPolicy(self::KEY, $config, true, $params['mallSno']);
    }

    /**
     * 자동메일설정 저장항목 검증
     *
     * @param $params
     *
     * @throws Exception
     */
    protected function validateSaveAutoMailConfig($params)
    {
        $this->validator->init();
        $this->validator->add('category', 'pattern', true, '{' . __('카테고리') . '}', '/^(order|join|member|point|admin)$/');
        if ($params['category'] === 'order') {
            $this->validator->add('type', 'pattern', true, '{' . __('주문/배송 유형선택') . '}', '/^(order|incash|delivery)$/');
            $this->validator->add('sendTarget', '', true, '{' . __('발송대상') . '}');
        } else if ($params['category'] === 'join') {
            $this->validator->add('type', 'pattern', true, '{' . __('가입/탈퇴/문의 유형선택') . '}', '/^(join|findpassword|qna|hackout|approval)$/');
        } else if ($params['category'] === 'member') {
            $this->validator->add('type', 'pattern', true, '{' . __('회원정보 유형선택') . '}', '/^(sleepnotice|wake|rejectemail|agreement|agreement2yperiod|changepassword|groupchange)$/');
        } else if ($params['category'] === 'point') {
            $this->validator->add('type', 'pattern', true, '{' . __('마일리지/예치금 유형선택') . '}', '/^(addmileage|removemileage|deletemileage|adddeposit|removedeposit)$/');
        } else if ($params['category'] === 'admin') {
            $this->validator->add('type', 'pattern', true, '{' . __('관리자보안 유형선택') . '}', '/^(adminsecurity)$/');
        }
        $isFindPassword = $params['category'] === 'join' && $params['type'] === 'findpassword';
        if ($isFindPassword === false) {
            $this->validator->add('autoSendFl', 'yn', true, '{' . __('발송여부') . '}');
        }
        if ($params['category'] === 'join' && $params['join'] === 'join') {
            $this->validator->add('mailDisapproval', 'yn', false, '{' . __('승인대기 회원 포함') . '}');
        }
        $this->validator->add('subject', '', true, '{' . __('제목') . '}');
        $this->validator->add('senderEmail', 'email', true, '{' . __('발송자이메일') . '}');
        $this->validator->add('body', '', true, '{' . __('내용') . '}');

        if ($this->validator->act($params, true) === false) {
            throw new \Exception(implode("\n", $this->validator->errors), 500);
        }
    }

    /**
     * 자동메일 템플릿 저장
     *
     * @param     $data
     * @param     $fileName
     * @param int $mallSno
     */
    protected function saveAutoMailTemplate($data, $fileName, $mallSno = DEFAULT_MALL_NUMBER)
    {
        $data = StringUtils::htmlSpecialCharsStripSlashes($data);
        if ($mallSno == DEFAULT_MALL_NUMBER) {
            $fname = \UserFilePath::data('mail', $fileName);
            $fp = fopen($fname, 'w');
            fwrite($fp, $data);
            fclose($fp);
            @chmod($fname, 0707);
        } else {
            $fname = \UserFilePath::data('mail', $mallSno, $fileName);
            $this->fileHandler->write($fname, $data, \Framework\File\FileHandler::PERM_WRITE);
        }

    }
}
