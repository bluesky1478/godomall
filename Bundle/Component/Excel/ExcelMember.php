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

namespace Bundle\Component\Excel;

/**
 * Class ExcelMember
 * @package Bundle\Component\Excel
 * @author  yjwee
 */
class ExcelMember
{
    public function formatMember($isDownload = false)
    {
        // 회원등급
        $groupsArr = [];
        $groupBase = '';
        $groups = gd_member_groups();
        if (is_array($groups)) {
            foreach ($groups as $k => $v) {
                array_push($groupsArr, $k . ':' . $v);
                if (empty($groupBase) === true) $groupBase = $k;
            }
        }
        $groupsStr = implode("<br style='mso-data-placement:same-cell;'>", $groupsArr);

        // 직업
        $jobsArr = [];
        $jobBase = '';
        $jobs = gd_code('01002');
        if (is_array($jobs)) {
            foreach ($jobs as $k => $v) {
                array_push($jobsArr, $k . ':' . $v);
                if (empty($jobBase) === true) $jobBase = $k;
            }
        }
        $jobsStr = implode("<br style='mso-data-placement:same-cell;'>", $jobsArr);

        // 관심분야
        $interestsArr = [];
        $interestBase = '';
        $interests = gd_code('01001');
        if (is_array($interests)) {
            foreach ($interests as $k => $v) {
                array_push($interestsArr, $k . ':' . $v);
                if (empty($interestBase) === true) $interestBase = $k;
            }
        }
        $interestsStr = implode("<br style='mso-data-placement:same-cell;'>", $interestsArr);

        $arrField = [
            [
                'dbName'   => 'member',
                'dbKey'    => 'memNo',
                'excelKey' => 'mem_no',
                'text'     => __('회원 번호'),
                'sample'   => '',
                'comment'  => __('숫자 10자'),
                'desc'     => __('숫자 10자 이내의 unique 코드, 등록시에는 자동 생성 되므로 등록시에는 넣지 마세요.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'memId',
                'excelKey' => 'mem_id',
                'text'     => __('아이디'),
                'sample'   => 'test_id_1',
                'comment'  => __('영문, 숫자,특수문자(.-_@) 20자'),
                'desc'     => __('영문, 숫자,특수문자(.-_@) 20자 이내 입력'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'groupSno',
                'excelKey' => 'group_sno',
                'text'     => __('등급(번호)'),
                'sample'   => $groupBase,
                'comment'  => $groupsStr,
                'desc'     => sprintf('[%s.<br />' . $groupsStr, __('운영정책>회원등급(레벨)]에서 설정한 등급번호 입력')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'memNm',
                'excelKey' => 'mem_name',
                'text'     => __('이름'),
                'sample'   => __('홍길동'),
                'comment'  => __('영문 40자, 한글 20자'),
                'desc'     => __('영문 40자, 한글 20자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'nickNm',
                'excelKey' => 'nick_name',
                'text'     => __('닉네임'),
                'sample'   => __('의인'),
                'comment'  => __('영문 50자, 한글 25자'),
                'desc'     => __('영문 50자, 한글 25자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'memPw',
                'excelKey' => 'mem_password',
                'text'     => __('비밀번호'),
                'sample'   => 'test_pwd_1',
                'comment'  => __('영문, 숫자, 특수문자(!@#$%^&*()_+-=`~) 16자'),
                'desc'     => __('2가지 이상 조합하여야 합니다. 암호화되어 저장됩니다. 16자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'memPwEnc',
                'excelKey' => 'mem_password_enc',
                'text'     => sprintf('%s(%s)', __('비밀번호'), __('암호화문자')),
                'sample'   => '',
                'comment'  => __('암호화(password)된 150자'),
                'desc'     => __('암호화(password)된 150자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'appFl',
                'excelKey' => 'app_fl',
                'text'     => __('가입승인'),
                'sample'   => 'n',
                'comment'  => sprintf('y:%s<br />n:%s', __('승인'), __('미승인')),
                'desc'     => sprintf('y:%s, n:%s, %s', __('승인'), __('미승인'), __('기본은 n(미승인)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'sexFl',
                'excelKey' => 'sex_fl',
                'text'     => __('성별'),
                'sample'   => 'm',
                'comment'  => sprintf('m:%s<br />w:%s', __('남자'), __('여자')),
                'desc'     => sprintf('m:%s, w:%s, %s', __('남자'), __('여자'), __('기본은 m(남자)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'birthDt',
                'excelKey' => 'birth_dt',
                'text'     => __('생일'),
                'sample'   => '2010-01-01',
                'comment'  => sprintf('%s(YYYY-MM-DD)', __('태어난 해')),
                'desc'     => __('생년월일 입력'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'calendarFl',
                'excelKey' => 'calendar_fl',
                'text'     => sprintf('%s,%s', __('양력'), __('음력')),
                'sample'   => 's',
                'comment'  => sprintf('s:%s<br />l:%s', __('양력'), __('음력')),
                'desc'     => sprintf('s:%s, l:%s, %s', __('양력'), __('음력'), __('기본은 s(양력)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'email',
                'excelKey' => 'email',
                'text'     => __('이메일'),
                'sample'   => 'test@godo.co.kr',
                'comment'  => __('영문 100자, 한글 50자'),
                'desc'     => __('영문 100자, 한글 50자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'zonecode',
                'excelKey' => 'zonecode',
                'text'     => __('우편번호'),
                'sample'   => '13587',
                'comment'  => sprintf('%s) XXXXX', __('형식')),
                'desc'     => __('5자리의 신규 우편번호 입력. 형식) XXXXX'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'address',
                'excelKey' => 'address',
                'text'     => __('주소'),
                'sample'   => __('서울 강남구 삼성2동'),
                'comment'  => __('영문 150자, 한글 75자'),
                'desc'     => __('영문 150자, 한글 75자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'addressSub',
                'excelKey' => 'address_sub',
                'text'     => __('상세주소'),
                'sample'   => '143-9',
                'comment'  => __('영문 100자, 한글 50자'),
                'desc'     => __('영문 100자, 한글 50자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'phone',
                'excelKey' => 'phone',
                'text'     => __('전화번호'),
                'sample'   => '02-1234-1234',
                'comment'  => sprintf('%s) XXX-XXXX-XXXX', __('형식')),
                'desc'     => __('하이픈(-) 를 포함한 13자리의 번호 입력. 형식) XXX-XXXX-XXXX'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'cellPhone',
                'excelKey' => 'cell_phone',
                'text'     => __('휴대폰'),
                'sample'   => '010-1234-1234',
                'comment'  => sprintf('%s) XXX-XXXX-XXXX', __('형식')),
                'desc'     => __('하이픈(-) 를 포함한 13자리의 번호 입력. 형식) XXX-XXXX-XXXX'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'fax',
                'excelKey' => 'fax',
                'text'     => __('팩스번호'),
                'sample'   => '02-1234-1234',
                'comment'  => sprintf('%s) XXX-XXXX-XXXX', __('형식')),
                'desc'     => __('하이픈(-) 를 포함한 13자리의 번호 입력. 형식) XXX-XXXX-XXXX'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'company',
                'excelKey' => 'company',
                'text'     => __('회사명'),
                'sample'   => '',
                'comment'  => __('영문 50자, 한글 25자'),
                'desc'     => __('영문 50자, 한글 25자'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'service',
                'excelKey' => 'service',
                'text'     => __('업태'),
                'sample'   => '',
                'comment'  => __('영문 30자, 한글 15자'),
                'desc'     => __('영문 30자, 한글 15자'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'item',
                'excelKey' => 'item',
                'text'     => __('종목'),
                'sample'   => '',
                'comment'  => __('영문 30자, 한글 15자'),
                'desc'     => __('영문 30자, 한글 15자'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'busiNo',
                'excelKey' => 'business_no',
                'text'     => __('사업자번호'),
                'sample'   => '123-45-67890',
                'comment'  => sprintf('%s) XXX-XX-XXXXX', __('형식')),
                'desc'     => __('하이픈(-) 를 포함한 12자리의 번호 입력. 형식) XXX-XX-XXXXX'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'ceo',
                'excelKey' => 'ceo_name',
                'text'     => __('대표자명'),
                'sample'   => '',
                'comment'  => __('영문 20자, 한글 10자'),
                'desc'     => __('영문 20자, 한글 10자'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'comZonecode',
                'excelKey' => 'com_zonecode',
                'text'     => __('사업장우편번호'),
                'sample'   => '13587',
                'comment'  => sprintf('%s) XXXXX', __('형식')),
                'desc'     => __('5자리의 신규 우편번호 입력. 형식) XXXXX'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'comAddress',
                'excelKey' => 'com_address',
                'text'     => __('사업장주소'),
                'sample'   => __('서울 강남구 삼성2동'),
                'comment'  => __('영문 150자, 한글 75자'),
                'desc'     => __('영문 150자, 한글 75자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'comAddressSub',
                'excelKey' => 'com_address_sub',
                'text'     => __('사업장상세주소'),
                'sample'   => '143-9',
                'comment'  => __('영문 100자, 한글 50자'),
                'desc'     => __('영문 100자, 한글 50자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'mileage',
                'excelKey' => 'mileage',
                'text'     => __('마일리지'),
                'sample'   => '2000',
                'comment'  => __('숫자 10자'),
                'desc'     => __('숫자 10자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'deposit',
                'excelKey' => 'deposit',
                'text'     => __('예치금'),
                'sample'   => '2000',
                'comment'  => __('숫자 10자'),
                'desc'     => __('숫자 10자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'maillingFl',
                'excelKey' => 'mailling_fl',
                'text'     => __('메일수신동의'),
                'sample'   => 'y',
                'comment'  => sprintf('y:%s<br />n:%s', __('받음'), __('거부')),
                'desc'     => sprintf('y:%s, n:%s, %s', __('받음'), __('거부'), __('기본은 y(받음)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'smsFl',
                'excelKey' => 'sms_fl',
                'text'     => __('SMS수신동의'),
                'sample'   => 'y',
                'comment'  => sprintf('y:%s<br />n:%s', __('받음'), __('거부')),
                'desc'     => sprintf('y:%s, n:%s, %s', __('받음'), __('거부'), __('기본은 y(받음)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'marriFl',
                'excelKey' => 'marri_fl',
                'text'     => __('결혼여부'),
                'sample'   => 'n',
                'comment'  => sprintf('n:%s<br />y:%s', __('미혼'), __('기혼')),
                'desc'     => sprintf('n:%s, y:%s, %s', __('미혼'), __('기혼'), __('기본은 n(미혼)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'marriDate',
                'excelKey' => 'marri_date',
                'text'     => __('결혼기념일'),
                'sample'   => '2010-09-01',
                'comment'  => sprintf('%s) YYYY-MM-DD', __('형식')),
                'desc'     => sprintf('%s) YYYY-MM-DD', __('형식')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'job',
                'excelKey' => 'job',
                'text'     => __('직업'),
                'sample'   => $jobBase,
                'comment'  => $jobsStr,
                'desc'     => sprintf('%s.<br />' . $jobsStr, __('[운영정책>코드관리]에서 설정한 직업코드번호 입력')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'interest',
                'excelKey' => 'interest',
                'text'     => __('관심분야'),
                'sample'   => $interestBase,
                'comment'  => sprintf('%s) 004001|004002<br />' . $interestsStr, __('예')),
                'desc'     => sprintf('. %s) 004001|004002<br />' . $interestsStr, __('[운영정책>코드관리]에서 설정한 관심분야번호 입력. 다수 경우 \'|\' 를 구분자로 입력'), __('예')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'reEntryFl',
                'excelKey' => 're_entry_fl',
                'text'     => __('재가입여부'),
                'sample'   => 'n',
                'comment'  => sprintf('n:%s<br />y:%s', __('신규가입'), __('재가입')),
                'desc'     => sprintf('n:%s, y:%s, %s', __('신규가입'), __('재가입'), __('기본은 n(신규가입)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'entryDt',
                'excelKey' => 'entry_dt',
                'text'     => __('회원가입일'),
                'sample'   => '2010-09-01 11:30:30',
                'comment'  => sprintf('%s) YYYY-MM-DD HH:II:SS', __('형식')),
                'desc'     => sprintf('%s) YYYY-MM-DD HH:II:SS', __('형식')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'entryPath',
                'excelKey' => 'entry_path',
                'text'     => __('가입경로'),
                'sample'   => 'pc',
                'comment'  => sprintf('pc:PC<br />mobile:%s', __('모바일')),
                'desc'     => sprintf('pc:PC, mobile:%s, %s', __('모바일'), __('기본은 pc(PC)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'lastLoginDt',
                'excelKey' => 'last_login_dt',
                'text'     => __('최종로그인'),
                'sample'   => '2010-09-01 11:30:30',
                'comment'  => sprintf('%s) YYYY-MM-DD HH:II:SS', __('형식')),
                'desc'     => sprintf('%s) YYYY-MM-DD HH:II:SS', __('형식')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'lastLoginIp',
                'excelKey' => 'last_login_ip',
                'text'     => __('최종로그인IP'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => '',
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'lastSaleDt',
                'excelKey' => 'last_sale_dt',
                'text'     => __('최종구매일'),
                'sample'   => '2010-09-01 11:30:30',
                'comment'  => sprintf('%s) YYYY-MM-DD HH:II:SS', __('형식')),
                'desc'     => sprintf('%s) YYYY-MM-DD HH:II:SS', __('형식')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'loginCnt',
                'excelKey' => 'login_count',
                'text'     => __('로그인횟수'),
                'sample'   => '5',
                'comment'  => __('숫자 5자'),
                'desc'     => __('로그인한 횟수 입력. 숫자 5자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'saleCnt',
                'excelKey' => 'sale_count',
                'text'     => __('상품주문건수'),
                'sample'   => '1',
                'comment'  => __('숫자 5자'),
                'desc'     => __('구매 확정된 상품주문의 수 입력. 숫자 5자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'saleAmt',
                'excelKey' => 'sale_amount',
                'text'     => __('총 주문금액'),
                'sample'   => '12000',
                'comment'  => __('숫자 10자'),
                'desc'     => __('구매한 주문 금액 입력. 숫자 10자 이내 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'memo',
                'excelKey' => 'memo',
                'text'     => __('남기는말'),
                'sample'   => '',
                'comment'  => __('영문 255자, 한글 127자'),
                'desc'     => __('회원이 남긴 말 입력. 영문 255자, 한글 127자'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'recommId',
                'excelKey' => 'recomm_id',
                'text'     => __('추천인ID'),
                'sample'   => __('test_id'),
                'comment'  => __('영문 20자, 한글 10자'),
                'desc'     => __('추천인 회원아이디 입력. 영문 20자, 한글 10자'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'recommFl',
                'excelKey' => 'recomm_fl',
                'text'     => __('추천인아이디등록여부'),
                'sample'   => 'n',
                'comment'  => sprintf('n:%s<br />y:%s', __('등록안함'), __('등록함')),
                'desc'     => sprintf('n:%s, y:%s, %s', __('등록안함'), __('등록함'), __('기본은 n(등록안함)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'ex1',
                'excelKey' => 'ex1',
                'text'     => sprintf(__('추가%d'), 1),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('추가정보 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'ex2',
                'excelKey' => 'ex2',
                'text'     => sprintf(__('추가%d'), 2),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('추가정보 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'ex3',
                'excelKey' => 'ex3',
                'text'     => sprintf(__('추가%d'), 3),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('추가정보 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'ex4',
                'excelKey' => 'ex4',
                'text'     => sprintf(__('추가%d'), 4),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('추가정보 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'ex5',
                'excelKey' => 'ex5',
                'text'     => sprintf(__('추가%d'), 5),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('추가정보 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'ex6',
                'excelKey' => 'ex6',
                'text'     => sprintf(__('추가%d'), 6),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('추가정보 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'privateApprovalFl',
                'excelKey' => 'private_approval_fl',
                'text'     => __('개인정보 수집 및 이용 필수'),
                'sample'   => 'n',
                'comment'  => sprintf('n:%s<br />y:%s', __('동의안함'), __('동의함')),
                'desc'     => sprintf('n:%s, y:%s, %s', __('동의안함'), __('동의함'), __('기본은 n(동의안함)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'privateApprovalOptionFl',
                'excelKey' => 'private_approval_option_fl',
                'text'     => __('개인정보 수집 및 이용 선택'),
                'sample'   => 'n',
                'comment'  => sprintf('n:%s<br />y:%s', __('동의안함'), __('동의함')),
                'desc'     => sprintf('n:%s, y:%s, %s', __('동의안함'), __('동의함'), __('기본은 n(동의안함)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'privateOfferFl',
                'excelKey' => 'private_offer_fl',
                'text'     => __('개인정보동의 제3자 제공'),
                'sample'   => 'n',
                'comment'  => sprintf('n:%s<br />y:%s', __('동의안함'), __('동의함')),
                'desc'     => sprintf('n:%s, y:%s, %s', __('동의안함'), __('동의함'), __('기본은 n(동의안함)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'privateConsignFl',
                'excelKey' => 'private_consign_fl',
                'text'     => __('개인정보동의 취급업무 위탁'),
                'sample'   => 'n',
                'comment'  => sprintf('n:%s<br />y:%s', __('동의안함'), __('동의함')),
                'desc'     => sprintf('n:%s, y:%s, %s', __('동의안함'), __('동의함'), __('기본은 n(동의안함)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'foreigner',
                'excelKey' => 'foreigner',
                'text'     => __('내외국인구분'),
                'sample'   => '1',
                'comment'  => sprintf('1:%s<br />2:%s', __('내국인'), __('외국인')),
                'desc'     => sprintf('1:%s, 2:%s, %s', __('내국인'), __('외국인'), __('기본은 1(내국인)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'dupeinfo',
                'excelKey' => 'dupeinfo',
                'text'     => __('본인확인 중복가입확인정보'),
                'sample'   => '',
                'comment'  => __('영문 64자'),
                'desc'     => __('영문 64자'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'adultFl',
                'excelKey' => 'adult_fl',
                'text'     => __('성인여부'),
                'sample'   => '',
                'comment'  => sprintf('n:%s<br />y:%s', __('인증안됨'), __('인증됨')),
                'desc'     => sprintf('n:%s, y:%s', __('인증 안된 회원'), __('인증된 회원')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'adultConfirmDt',
                'excelKey' => 'adult_confirm_dt',
                'text'     => __('성인여부인증시간'),
                'sample'   => '',
                'comment'  => __('성인여부인증시간'),
                'desc'     => __('성인여부인증시간'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'pakey',
                'excelKey' => 'pakey',
                'text'     => __('본인확인 번호'),
                'sample'   => '',
                'comment'  => __('영문 13자'),
                'desc'     => __('영문 13자'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'rncheck',
                'excelKey' => 'rncheck',
                'text'     => __('본인확인 방법'),
                'sample'   => '',
                'comment'  => sprintf('none:%s<br />realname:%s<br />ipin:%s<br />authCellphone:%s', __('안함'), __('실명인증'), __('아이핀'), __('휴대폰본인확인')),
                'desc'     => sprintf('none:%s, realname:%s, ipin:%s, authCellphone:%s,%s', __('안함'), __('실명인증'), __('아이핀'), __('휴대폰본인확인'), __('기본은 none(안함)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'adminMemo',
                'excelKey' => 'admin_memo',
                'text'     => __('관리자 메모'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('관리자 메모를 입력.'),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'sleepFl',
                'excelKey' => 'sleep_fl',
                'text'     => __('휴면회원여부'),
                'sample'   => 'n',
                'comment'  => sprintf('n:%s<br />y:%s', __('일반회원'), __('휴면회원')),
                'desc'     => sprintf('n:%s, y:%s, %s', __('일반회원'), __('휴면회원'), __('기본은 n(일반회원)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'sleepMailFl',
                'excelKey' => 'sleep_mail_fl',
                'text'     => __('휴면회원전환안내메일발송여부'),
                'sample'   => 'n',
                'comment'  => sprintf('n:%s<br />y:%s', __('미발송'), __('발송')),
                'desc'     => sprintf(__('n:%s, y:%s, %s'), __('미발송'), __('발송'), __('기본은 n(미발송)입니다.')),
            ],
            [
                'dbName'   => 'member',
                'dbKey'    => 'expirationFl',
                'excelKey' => 'expiration_fl',
                'text'     => __('개인정보유효기간'),
                'sample'   => '1',
                'comment'  => sprintf('1:1%1$s<br />3:3%1$s<br />5:5%1$s<br />999:%2$s', __('년'), __('탈퇴시')),
                'desc'     => sprintf('1:1%1$s, 3:3%1$s, 5:5%1$s, 999:%2$s %3$s', __('년'), __('탈퇴시'), __('기본은 1(1년)입니다.')),
            ],
        ];

        // 다운로드용 정의
        if ($isDownload) {
            $tmp = [];
            $excludeDbKey = ['memPw'];
            foreach ($arrField as $key => $val) {
                if (in_array($val['dbKey'], $excludeDbKey)) {
                    continue;
                }
                array_push($tmp, $val);
            }
            $arrField = $tmp;
        }

        return $arrField;
    }
}
