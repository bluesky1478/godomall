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

namespace Bundle\Component\Mail;


use App;
use Component\Member\Group\GroupDomain;
use Component\Member\Group\Util as GroupUtil;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;
use Logger;
use Request;
use UserFilePath;

/**
 * Class MailUtil
 * @package Bundle\Component\Mail
 * @author  yjwee
 */
class MailUtil
{
    /**
     * MailUtil constructor.
     */
    function __construct()
    {
    }

    /**
     * getBasicConfig
     *
     * @static
     *
     * @param null $name
     *
     * @return array
     */
    public static function getBasicConfig($name = null)
    {
        $config = gd_policy('basic.info');
        if ($name === null) {
            return $config;
        }

        return $config[$name];
    }

    /**
     * 자동메일 템플릿 반환
     *
     * @param     $fileName
     * @param int $mallSno
     *
     * @return string
     */
    public static function loadAutoMailTemplate($fileName, $mallSno = DEFAULT_MALL_NUMBER)
    {
        if ($mallSno == DEFAULT_MALL_NUMBER) {
            ob_start();
            @include UserFilePath::data('mail', $fileName);
            $body = ob_get_contents();
            ob_end_clean();

            return $body;
        } else {
            /** @var \Framework\File\UserFilePathResolver $userPath */
            $userPath = \App::getInstance('user.path');
            /** @var \Framework\File\FileHandler $fileHandler */
            $fileHandler = \App::getInstance('file');
            $body = $fileHandler->read($userPath->data('mail', $mallSno, $fileName));

            return $body;
        }
    }

    /**
     * 자동메일설정의 카테고리, 유형선택 배열 값을 반환하는 함수
     *
     * @param null $category
     * @param null $type
     *
     * @return array
     */
    public static function getAutoTemplateType($category = null, $type = null)
    {
        $autoTemplateType = [
            'ORDER'  => [
                'ORDER'    => __('주문내역확인'),
                'INCASH'   => __('입금확인'),
                'DELIVERY' => __('상품배송'),
            ],
            'JOIN'   => [
                'JOIN'         => __('회원가입'),
                'APPROVAL'     => __('가입승인'),
                'FINDPASSWORD' => __('비밀번호찾기 인증'),
                'QNA'          => __('게시글 답변'),
                'HACKOUT'      => __('회원탈퇴'),
            ],
            'MEMBER' => [
                'SLEEPNOTICE'       => __('휴면전환사전안내'),
                'WAKE'              => __('휴면회원해제인증'),
                'REJECTEMAIL'       => __('이메일수신거부'),
                'AGREEMENT'         => __('정보수신동의설정'),
                'AGREEMENT2YPERIOD' => __('수신동의여부확인'),
                'CHANGEPASSWORD'    => __('비밀번호변경알림'),
                'GROUPCHANGE'       => __('회원등급변경안내'),
            ],
            'POINT'  => [
                'ADDMILEAGE'    => __('마일리지 지급'),
                'REMOVEMILEAGE' => __('마일리지 차감'),
                'DELETEMILEAGE' => __('마일리지 소멸'),
                'ADDDEPOSIT'    => __('예치금 지급'),
                'REMOVEDEPOSIT' => __('예치금 차감'),
            ],
            'ADMIN'  => [
                'ADMINSECURITY' => __('관리자 보안 인증메일'),
            ],
        ];
        if ($category !== null && $type !== null) {
            $category = strtoupper($category);
            $type = strtoupper($type);

            return $autoTemplateType[$category][$type];
        }
        if ($category !== null && $type === null) {
            $category = strtoupper($category);

            return $autoTemplateType[$category];
        }

        return $autoTemplateType;
    }

    /**
     * mail.configAuto 설정 값을 반환
     *
     * @static
     *
     * @param null $category
     * @param null $type
     *
     * @param int  $mallSno
     *
     * @return array
     */
    public static function getMailConfigAuto($category = null, $type = null, $mallSno = DEFAULT_MALL_NUMBER)
    {
        $config = ComponentUtils::getPolicy('mail.configAuto', $mallSno);

        if ($category === null && $type === null) {
            return $config;
        }
        if ($category !== null && $type === null) {
            return $config[$category];
        }

        return $config[$category][$type];
    }


    /**
     * 유형에 맞는 템플릿 제목과 본문을 반환하는 함수
     *
     * @param string $type
     * @param int    $mallSno
     *
     * @return array
     */
    public static function getAutoMailConfigTemplate($type, $mallSno = DEFAULT_MALL_NUMBER)
    {
        $template = [];
        $typeUpper = strtoupper($type);
        $template['subject'] = MailUtil::loadAutoMailTemplate('subject_' . $typeUpper . '.php', $mallSno);
        $template['body'] = MailUtil::loadAutoMailTemplate('body_' . $typeUpper . '.php', $mallSno);

        return $template;
    }

    /**
     * httpSrc
     *
     * @param $content
     *
     * @return mixed
     */
    public static function httpSrc($content)
    {
        $host = 'http://' . Request::getHost() . ':324';
        $host = preg_replace_callback(
            "/:[0-9].+$/", function ($matches) {
            return '';
        }, $host
        );

        $pattern_a = [
            "@(\s*href|\s*src)(\s*=\s*'{1})(/[^']+)('{1})@i",
            "@(\s*href|\s*src)(\s*=\s*\"{1})(/[^\"]+)(\"{1})@i",
            "@(\s*href|\s*src)(\s*=\s*)(/[^\s>\"\']+)(\s|>)@i",
        ];
        $content = preg_replace_callback(
            $pattern_a, function ($matches) {
            global $host;

            return $matches[1] . $matches[2] . $host . $matches[3] . $matches[4];
        }, $content
        );

        return $content;
    }

    /**
     * getSendedCount
     *
     * @static
     * @return string
     */
    public static function getSendedCount()
    {
        $db = \App::load('DB');
        $db->strField = 'SUM(receiverCnt) AS sum';
        $db->strWhere = 'substring(regDt, 1, 7)=substring(now(), 1, 7) AND sendType=\'manual\'';
        $query = $db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MAIL_LOG . ' ' . implode(' ', $query);
        $data = StringUtils::htmlSpecialCharsStripSlashes($db->query_fetch($strSQL, null, false));

        return StringUtils::strIsSet($data['sum'], 0);
    }

    /**
     * sendMemberGroupChangeMail
     *
     * @static
     *
     * @param             $arrData
     * @param GroupDomain $groupVo
     *
     * @throws \Exception
     */
    public static function sendMemberGroupChangeMail($arrData, GroupDomain $groupVo = null)
    {
        if ($groupVo == null) {
            /** @var \Bundle\Component\Member\MemberGroup $memberGroup */
            $memberGroup = App::load('\\Component\\Member\\MemberGroup');
            $groupVo = new GroupDomain($memberGroup->getGroup($arrData['groupSno']));
        }

        $groupPolicy = ComponentUtils::getPolicy('member.group');
        /**
         * json array to array
         */
        $groupVo->toStrip('fixedRateOption')->toJsonDecode('fixedRateOption');

        // 등급 평가 기준
        $tmp = GroupUtil::getAppraisalString($groupVo->toArray());
        $getData['evaluateStr'] = str_replace("\n", ', ', (empty($tmp) ? __('미설정') : $tmp));

        $groupVo->setGroupDcData();

        $mailData = [
            'memNo'             => $arrData['memNo'],
            'email'             => $arrData['email'],
            'memNm'             => $arrData['memNm'],
            'maillingFl'        => $arrData['maillingFl'],
            'groupNm'           => $groupVo->getGroupNm(),
            'grpLabel'          => $groupPolicy['grpLabel'],
            'dcLine'            => NumberUtils::currencySymbol() . NumberUtils::moneyFormat($groupVo->getDcLine()) . NumberUtils::currencyString(),
            'dcPercent'         => number_format($groupVo->getDcPercent()),
            'dcExScm'           => $groupVo->getDcExScm(),
            'dcExCategory'      => $groupVo->getDcExCategory(),
            'dcExBrand'         => $groupVo->getDcExBrand(),
            'dcExGoods'         => $groupVo->getDcExGoods(),
            'overlapDcLine'     => NumberUtils::currencySymbol() . NumberUtils::moneyFormat($groupVo->getOverlapDcLine()) . NumberUtils::currencyString(),
            'overlapDcPercent'  => number_format($groupVo->getOverlapDcPercent()),
            'overlapDcScm'      => $groupVo->getOverlapDcScm(),
            'overlapDcCategory' => $groupVo->getOverlapDcCategory(),
            'overlapDcBrand'    => $groupVo->getOverlapDcBrand(),
            'overlapDcGoods'    => $groupVo->getOverlapDcGoods(),
            'mileageLine'       => NumberUtils::currencySymbol() . NumberUtils::moneyFormat($groupVo->getMileageLine()) . NumberUtils::currencyString(),
            'mileagePercent'    => number_format($groupVo->getMileagePercent()),
            'fixedRateOption'   => $groupVo->getFixedRateOption(),
            'settleGb'          => GroupUtil::getSettleGbData($groupVo->getSettleGb()),
            'changeDt'          => DateTimeUtils::dateFormat('Y년 m월 d일', 'now'),
            'calcKeep'          => $groupPolicy['calcKeep'],
        ];

        \Logger::debug(__METHOD__, $mailData);
        /** @var \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
        $mailMimeAuto = App::load('\\Component\\Mail\\MailMimeAuto');
        $mailMimeAuto->init(MailMimeAuto::GROUPCHANGE, $mailData)->autoSend();
    }

    public function relativeToAbsolute($text, $base, $mallSno = DEFAULT_MALL_NUMBER)
    {
        if (empty($base))
            return $text;
        // base url needs trailing /
        if (substr($base, -1, 1) != "/")
            $base .= "/";
        // Replace links
        $replaceLink = function ($text, $base, $mallSno) {
            switch ($mallSno) {
                case 2:
                    $base .= 'us/';
                    break;
                case 3:
                    $base .= 'cn/';
                    break;
                case 4:
                    $base .= 'jp/';
                    break;
            }
            $pattern = "/<a([^>]*) " .
                "href=\"[^http|ftp|https|mailto]([^\"]*)\"/";
            $replace = "<a\${1} href=\"" . $base . "\${2}\"";
            $text = preg_replace($pattern, $replace, $text);

            return $text;
        };
        $text = $replaceLink($text, $base, $mallSno);

        // Replace images
        $pattern = "/<img([^>]*) " .
            "src=\"[^http|ftp|https]([^\"]*)\"/";
        $replace = "<img\${1} src=\"" . $base . "\${2}\"";
        $text = preg_replace($pattern, $replace, $text);

        // Done
        return $text;
    }

    public static function hasMallDomain()
    {
        $defaultDomain = gd_policy('basic.info');
        $defaultDomain = $defaultDomain['mallDomain'];

        return isset($defaultDomain) && $defaultDomain != '';
    }

    public static function saveSenderMailByBaseMail($beforeBaseMail, $baseMail)
    {
        $configAuto = gd_policy('mail.configAuto');
        foreach ($configAuto as &$type) {
            foreach ($type as &$config) {
                if ($config['senderMail'] == '') {
                    $config['senderMail'] = $baseMail;
                } else if ($config['senderMail'] == $beforeBaseMail) {
                    $config['senderMail'] = $baseMail;
                }
            }
        }
        gd_set_policy('mail.configAuto', $configAuto, true);
    }
}
