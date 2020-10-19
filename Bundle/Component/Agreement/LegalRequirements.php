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

namespace Bundle\Component\Agreement;

use Framework\Utility\HttpUtils;
use Component\Validator\Validator;
use App;
use Globals;
use Session;
use Component\Database\DBTableField;

/**
 * 법정 필수 사항 점검 Class
 *
 * @author minji lee <mj2@godo.co.kr>
 */
class LegalRequirements
{
    const LEGAL_REQUIREMENTS_LIST = [
        'necessary' => [
            'base_info' => ['title' => '사업자 정보 입력', 'desc' => '상호 / 대표자명 / 사업장 주소 / 전화번호 / 사업자등록번호 / 통신판매신고번호 등 사업자 정보 필수 기재', 'url' => '/policy/base_info.php'],
            'private' => ['title' => '개인정보처리방침 설정', 'desc' => '개인정보처리방침 내용 입력 및 개인정보 보호책임자 입력', 'url' => '/policy/base_agreement_with_private.php?mallSno=1&mode=private'],
            'base_agreement_with_private' => ['title' => '제3자 제공 및 개인정보 처리 위탁 설정', 'desc' => '제3자 제공 및 처리 위탁 내역 고지 및 동의 여부 선택권 제공', 'url' => '/policy/base_agreement_with_private.php?mallSno=1&mode=privateItem'],
            'ssl_admin_setting' => ['title' => '보안 서버 설치', 'desc' => '개인정보 취급 페이지 내 보안서버 의무 적용', 'url' => '/policy/ssl_admin_setting.php'],
            'manage_security' => ['title' => '운영 보안 설정', 'desc' => '관리자 2차 인증 및 관리자 IP 접속 제한 설정', 'url' => '/policy/manage_security.php'],
            'member_join' => ['title' => '회원 가입 정책 설정', 'desc' => '14세 미만 회원가입 시 법정대리인 동의 후 가입 가능', 'url' => '/member/member_join.php'],
            'member_password_change' => ['title' => '비밀번호 변경 주기 설정', 'desc' => '비밀번호 유효기간 설정하여 6개월에 1회 이상 변경하도록 안내', 'url' => '/policy/member_password_change.php'],
            'sms_auto' => ['title' => 'sms 발신번호 등록', 'desc' => '거짓 전화번호로 인한 피해 예방을 위해 사전 등록한 발신번호로만 sms 발송 가능', 'url' => '/member/sms_auto.php'],
            'sms080_config' => ['title' => '080 수신거부 설정', 'desc' => '광고성 정보 전송 시, 수신거부의사 표시 기능 의무제공', 'url' => '/member/sms080_config'],
        ],
        'operation' => [
            'manage_list' => ['title' => '쇼핑몰 운영자 관리', 'desc' => '고객 개인정보 보호를 위해 운영자별 권한 설정', 'url' => '/policy/manage_list.php'],
            'admin_log_list' => ['title' => '관리자 접속 기록 확인', 'desc' => '월 1회 이상 개인정보처리시스템 접속 기록 확인 · 감독', 'url' => '/policy/admin_log_list.php'],
            'goods_must_info_list' => ['title' => '상품정보제공 고시 설정', 'desc' => '판매 상품의 필수(상세) 정보 등록 필요', 'url' => '/goods/goods_must_info_list.php'],
            'sms_auto' => ['title' => '광고 수신거부 처리 결과 통보', 'desc' => '수신거부 의사 표시 받은 날로 부터 14일 이내 처리 결과 통보', 'url' => '/member/sms_auto.php'],
            'member_modify_event_list' => ['title' => '회원정보수정 이벤트', 'desc' => '회원 비밀번호 변경 유도 및 고객 정보 업데이트 가능', 'url' => '/member/member_modify_event_list.php'],
            'member_sleep_list' => ['title' => '휴면 회원 데이터 분리', 'desc' => '1년 이상 서비스를 사용하지 않는 고객은 휴면상태로 별도 보관', 'url' => '/member/member_sleep_list.php'],
        ],
        'recommend' => [
            'pg_info' => ['title' => '전자결제(PG)', 'desc' => '구매안전 (에스크로) 서비스 적용 의무화', 'url' => '/service/service_info.php?menu=pg_info'],
            'member_auth_info' => ['title' => '휴대폰인증', 'desc' => '본인명의의 휴대폰으로 회원 가입자의 식별 및 성인인증 가능', 'url' => '/service/service_info.php?menu=member_auth_info'],
            'member_ipin_info' => ['title' => '아이핀인증', 'desc' => '아이핀 ID와 패스워드로 회원 가입자의 식별 및 성인인증 가능', 'url' => '/service/service_info.php?menu=member_ipin_info'],
            'convenience_refusal_info' => ['title' => '080 수신거부', 'desc' => '광고성 정보 전달 시, 고객의 수신거부 요청 ARS로 자동처리', 'url' => '/service/service_info.php?menu=convenience_refusal_info'],
        ]
    ];

    /**
     * 생성자
     *
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = App::load('DB');
        }
    }

    /**
     * 필수 설정 체크 값 가져오기
     *
     * @return array
     */
    public function getLegalRequirements()
    {
        $list = $this::LEGAL_REQUIREMENTS_LIST;
        $data['list'] = $list;
        //$policy = gd_policy('basic.legalRequirements'); // db에 저장된 체크 값
        $data['data'] = gd_policy('basic.legalRequirements'); // db에 저장된 체크 값
        /*
        foreach($list as $key => $val) {
            foreach($val as $name => $tmp) {
                if($policy[$key][$name] == 'true') {
                    unset($data['list'][$key][$name]);
                    $data['list'][$key][$name] = $tmp;
                }
            }
        }
        */
        $data['config'] = $this->getLegalRequirementsConfig(); // 레이어 show hide 여부
        return $data;
    }

    /**
     * 필수 설정 체크 값 저장
     *
     * @param $arrData
     * @return bool
     */
    public function saveLegalRequirements($arrData)
    {
        $data = gd_policy('basic.legalRequirements');
        $data[$arrData['name']][$arrData['key']] = $arrData['val'];
        gd_set_policy('basic.legalRequirements', $data);

        $logData['managerId'] = \Session::get('manager.managerId');
        $logData['menu'] = $this::LEGAL_REQUIREMENTS_LIST[$arrData['name']][$arrData['key']]['title'];
        $logData['checked'] = ($arrData['val'] == 'true') ? 'y' : 'n';
        $arrBind = $this->db->get_binding(DBTableField::tableLogLegalRequirements(), $logData, 'insert', array_keys($logData));
        $this->db->set_insert_db(DB_LOG_LEGAL_REQUIREMENTS, $arrBind['param'], $arrBind['bind'], 'y');
        return true;
    }

    /**
     * 레이아웃 show hide 쿠키 값 가져오기
     *
     * @return array
     */
    public function getLegalRequirementsConfig()
    {
        $cookie = \App::getInstance('cookie');
        $data['displayFl'] = gd_isset($cookie->get('legalRequirements_displayFl'), 'false');
        $data['checkedFl'] = gd_isset($cookie->get('legalRequirements_checkedFl'), 'false');
        return $data;
    }

    public function saveLegalRequirementsConfig($arrData)
    {
        $cookie = \App::getInstance('cookie');
        $cookie->set('legalRequirements_'.$arrData['key'], $arrData['val'], 3600 * 24 * 7);
    }
}
