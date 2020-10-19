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

use Component\Member\MemberValidation;
use Component\Validator\Validator;
use Framework\Object\SingletonTrait;
use Framework\Utility\StringUtils;

/**
 * Class JoinItemPolicy
 * @package Bundle\Component\Policy
 * @author  yjwee
 * @method static JoinItemPolicy getInstance
 */
class JoinItemPolicy extends \Component\Policy\Policy
{
    use SingletonTrait;

    /** 가입 항목 관리 정책 키 값 */
    const KEY = 'member.joinitem';
    /** @var array $standardValidation 정책의 기준 검증 값이 다른 항목에 대한 검증 정의 */
    protected $standardValidation = [
        'memId'  => [
            'minlen' => 4,
            'maxlen' => 50,
        ],  // 회원, 관리자의 아이디에 대한 기본 길이는 동일하다.
        'memPw'  => [
            'minlen' => 4,
            'maxlen' => 16,
        ],
        'nickNm' => [
            'minlen' => 2,
            'maxlen' => 20,
        ],
    ];
    /** @var array $requestPolicy 저장될 정책정보 */
    protected $requestPolicy;
    /** @var array $currentPolicy 현재 저장된 정책정보 */
    protected $currentPolicy;
    /** @var string $mallSno 상점번호 */
    protected $mallSno;
    /** @var array $otherFieldKeys 부가정보 필드 키값 */
    protected $otherFieldKeys = [
        //@formatter:off
        'fax', 'recommId', 'birthDt', 'calendarFl', 'sexFl',
        'marriFl', 'marriDate', 'job', 'interest', 'expirationFl', 'memo'
        //@formatter:on
    ];
    /** @var array $extraFieldKeys 추가정보 필드 키값 */
    protected $extraFieldKeys = [
        //@formatter:off
        'ex1', 'ex2', 'ex3', 'ex4', 'ex5', 'ex6'
        //@formatter:on
    ];

    public function __construct(array $config = [])
    {
        parent::__construct($config['storage']);
    }

    /**
     * saveMemberJoinItem
     *
     * @param $getValue
     *
     * @throws \Exception
     */
    public function saveMemberJoinItem($getValue)
    {
        $logger = \App::getInstance('logger');
        $logger->debug(__METHOD__, $getValue);

        $this->requestPolicy = $getValue;
        $this->mallSno = $this->requestPolicy['mallSno'];

        if ($this->mallSno == DEFAULT_MALL_NUMBER) {
            $this->saveDefaultMemberJoinItem($getValue);
        } else {
            $this->requestPolicy['under14Fl'] = 'n';
            $this->requestPolicy['smsFl']['use'] = 'n';
            $this->checkRangeItem('memId');
            if (isset($this->requestPolicy['memPw'])) {
                $this->checkRangeItem('memPw');
            }
            if (StringUtils::strIsSet($getValue['nickNm']['use'], 'n') == 'y') {
                $this->checkRangeItem('nickNm');
            }
            if (StringUtils::strIsSet($this->requestPolicy['calendarFl']['use'], 'n') == 'y' && StringUtils::strIsSet($this->requestPolicy['birthDt']['use'], 'n') == 'n') {
                throw new \Exception(__('생일 양/음력 항목은 생일 항목을 사용하여야 합니다.'));
            }
            if (StringUtils::strIsSet($this->requestPolicy['marriFl']['use'], 'n') == 'n' && StringUtils::strIsSet($this->requestPolicy['marriDate']['use'], 'n') == 'y') {
                throw new \Exception(__('결혼 기념일은 결혼여부를 사용하셔야 합니다.'));
            }
            if (StringUtils::strIsSet($this->requestPolicy['recommId']['use'], 'n') == 'n' && StringUtils::strIsSet($this->requestPolicy['recommFl']['use'], 'n') == 'y') {
                throw new \Exception(__('회원정보 변경 시 추천인아이디 등록 불가는 추천인아이디 항목을 사용하여야 합니다'));
            }

            $i = 0;
            if ($this->requestPolicy['ex']) {
                foreach ($this->requestPolicy['ex']['name'] as $k => $v) {
                    if ($v) {
                        $i++;
                        $this->requestPolicy['ex' . $i]['name'] = $v;
                        if (StringUtils::strIsSet($this->requestPolicy['ex']['use'][$k])) {
                            $this->requestPolicy['ex' . $i]['use'] = $this->requestPolicy['ex']['use'][$k];
                        }
                        if (StringUtils::strIsSet($this->requestPolicy['ex']['require'][$k])) {
                            $this->requestPolicy['ex' . $i]['require'] = $this->requestPolicy['ex']['require'][$k];
                        }
                        if (StringUtils::strIsSet($this->requestPolicy['ex']['type'][$k])) {
                            $this->requestPolicy['ex' . $i]['type'] = $this->requestPolicy['ex']['type'][$k];
                        }
                        if (StringUtils::strIsSet($this->requestPolicy['ex']['value'][$k])) {
                            $this->requestPolicy['ex' . $i]['value'] = $this->requestPolicy['ex']['value'][$k];
                        }
                    }
                }
            }
            unset($this->requestPolicy['ex']);

            if ($this->getStorage()->setValue(self::KEY, $this->requestPolicy, $this->mallSno) !== true) {
                throw new \Exception(__('회원가입 항목 설정 저장 중 오류가 발생하였습니다.'));
            }
        }
    }

    /**
     * 페이코 로그인 사용 시 회원가입 항목 설정
     */
    public function usePaycoLogin()
    {
        $this->setThirdPartyLogin();
    }

    /**
     * Third Party 의 회원정보를 이용한 로그인, 회원가입을 사용할 경우
     * 회원가입 항목의 이메일 및 아이디를 정책에 맞게 강제로 설정한다.
     */
    public function setThirdPartyLogin()
    {
        $policy = $this->getValue(self::KEY);
        unset($policy['email']['require']);

        $policy['email']['use'] = 'y';
        $policy['email']['require'] = 'y';
        $policy['memId']['maxlen'] = $this->standardValidation['memId']['maxlen'];
        $this->setValue(self::KEY, $policy);
    }

    /**
     * 가입항목에서 메일 사용 여부 확인
     *
     * @return bool
     */
    public function useEmail()
    {
        $policy = $this->getValue(self::KEY);

        return $policy['email']['use'] == 'y';
    }

    /**
     * 회원가입항목 정책 반환
     *
     * @param string $mallSno 상점번호
     *
     * @return array
     */
    public function getPolicy($mallSno = null)
    {
        if ($mallSno === null) {
            $mallSno = \Component\Mall\Mall::getSession('sno');
        }
        $this->currentPolicy = $this->getStorage()->getValue(self::KEY, $mallSno);
        if ($mallSno > DEFAULT_MALL_NUMBER) {
            // 해외상점은 회원가입 시 주소를 받지 못하게 설정
            $this->currentPolicy['address']['use'] = 'n';
        }
        if ($this->currentPolicy['memId']['minlen'] < 4) {
            $this->currentPolicy['memId']['minlen'] = 4;
        }
        if ($this->currentPolicy['memId']['maxlen'] < 10) {
            $this->currentPolicy['memId']['maxlen'] = 16;
        }
        if ($this->currentPolicy['memPw']['minlen'] < 4) {
            $this->currentPolicy['memPw']['minlen'] = 4;
        }
        if ($this->currentPolicy['memPw']['maxlen'] < 10) {
            $this->currentPolicy['memPw']['maxlen'] = 16;
        }
        if (gd_isset($this->currentPolicy['busiNo']['charlen']) == false) {
            $this->currentPolicy['busiNo']['charlen'] = 10;
        }

        return $this->currentPolicy;
    }

    /**
     * 현재 저장된 정책에 부가정보
     *
     */
    protected function mergePolicy()
    {
        $otherPolicy = $extraPolicy = [];
        $default = $this->getStorage()->getValue(self::KEY, DEFAULT_MALL_NUMBER);
        foreach ($default as $key => $value) {
            if (in_array($key, $this->otherFieldKeys)) {
                $otherPolicy[$key] = $value;
            }
            if (in_array($key, $this->extraFieldKeys)) {
                $extraPolicy[$key] = $value;
            }
        }
        $this->currentPolicy = array_merge($this->currentPolicy, $otherPolicy, $extraPolicy);
    }

    /**
     * 가입항목 중 길이 범위를 지정하는 항목에 대한 검증
     *
     * @param $key
     *
     * @throws \Exception
     */
    public function checkRangeItem($key)
    {
        if (Validator::required($this->requestPolicy[$key]['minlen']) === false) {
            throw new \Exception(__('최소자리수는 필수 입니다.'));
        }
        if (Validator::required($this->requestPolicy[$key]['maxlen']) === false) {
            throw new \Exception(__('최대자리수는 필수 입니다.'));
        }
        $standardMinLen = $this->standardValidation[$key]['minlen'];
        if ($this->requestPolicy[$key]['minlen'] < $standardMinLen) {
            throw new \Exception(sprintf(__('최소 %i 이상 입력해 주세요.'), $standardMinLen));
        }
        $standardMaxLen = $this->standardValidation[$key]['maxlen'];
        if ($this->requestPolicy[$key]['maxlen'] > $standardMaxLen) {
            throw new \Exception(sprintf(__('최대 %s 이하 입력해 주세요.'), $standardMaxLen));
        }
        if ($this->requestPolicy[$key]['minlen'] >= $this->requestPolicy[$key]['maxlen'] && $key != 'nickNm') {
            throw new \Exception(__('최소 자리수는 최대 자리수 보다 크게 입력 할수 없습니다.'));
        }
    }

    /**
     * saveDefaultMemberJoinItem
     *
     * @param $getValue
     *
     * @throws \Exception
     */
    protected function saveDefaultMemberJoinItem($getValue)
    {
        // 만 14세 미만 가입 설정 조건 체크
        if (!MemberValidation::checkUnder14Policy(null, StringUtils::strIsSet($getValue['birthDt']['use'], 'n'), StringUtils::strIsSet($getValue['birthDt']['require'], 'n'))) {
            throw new \Exception(__('<div><strong><span class="text-danger">가입연령제한 설정 사용이 불가합니다.</span><br />만 14(19)세 미만 가입연령제한 설정을 사용 중입니다.<br />본인인증서비스를 사용하시거나 회원가입 항목의 \'생일\'항목을 필수로 설정하셔야 합니다.</strong></div><div class="mgl20 mgt10" style="font-weight:bold; list-style-type: disc;"><ul><li style="list-style-type: disc;">본인인증서비스 설정</li><ul><li>-&nbsp;<a href="../policy/member_auth_cellphone.php" target="_blank" class="btn-link-underline">휴대폰인증</a></li><li>-&nbsp;<a href="../policy/member_auth_ipin.php" target="_blank" class="btn-link-underline">아이핀인증</a></li></ul></ul></div>'), 904);
        }

        //본인인증 제외중인 경우, 만 14세 미만이고 생일 항목의 필수를 해제하였을 경우 정책 확인
        if (MemberValidation::checkRequireSNSMemberAuth(null, null, StringUtils::strIsSet($getValue['birthDt']['use'], 'n'), StringUtils::strIsSet($getValue['birthDt']['require'], 'n')) === true) {
            throw new \Exception(__('<div class="mgb10">간편 로그인 본인인증 \'제외함\'설정을 사용 중입니다.<br /><br />만 14(19)세 미만 가입연령제한 설정을 함께 사용할 경우,<br />본인인증서비스를 사용하시거나 회원가입 항목의 ‘생일’항목을 필수로 설정하셔야 합니다.<br /><br /><a href="/member/member_join.php" class="btn-link-underline"  target="_blank">회원 > 회원관리 > 회원 가입 정책 관리</a>에서 설정을 확인해주세요.</div>'), 901);
        }

        if (Validator::required($getValue['memId']['minlen']) === false) {
            throw new \Exception(__('최소자리수는 필수 입니다.'));
        }
        if (Validator::required($getValue['memId']['maxlen']) === false) {
            throw new \Exception(__('최대자리수는 필수 입니다.'));
        }
        if ($getValue['memId']['minlen'] < 4) {
            throw new \Exception(sprintf(__('최소 %s 이상 입력해 주세요.'), 4));
        }
        if ($getValue['memId']['maxlen'] < 10) {
            throw new \Exception(sprintf(__('최소 %s 이상 입력해 주세요.'), 10));
        }
        if ($getValue['memId']['minlen'] >= $getValue['memId']['maxlen']) {
            throw new \Exception(__('아이디의 최소 자리수는 최대 자리수 보다 크게 입력 할수 없습니다.'));
        }
        if (StringUtils::strIsSet($getValue['nickNm']['use'], 'n') == 'y') {
            $this->checkRangeItem('nickNm');
        }
        if (StringUtils::strIsSet($getValue['calendarFl']['use'], 'n') == 'y' && StringUtils::strIsSet($getValue['birthDt']['use'], 'n') == 'n') {
            throw new \Exception(__('생일 양/음력 항목은 생일 항목을 사용하여야 합니다.'));
        }
        if (StringUtils::strIsSet($getValue['marriFl']['use'], 'n') == 'n' && StringUtils::strIsSet($getValue['marriDate']['use'], 'n') == 'y') {
            throw new \Exception(__('결혼 기념일은 결혼여부를 사용하셔야 합니다.'));
        }
        if (StringUtils::strIsSet($this->requestPolicy['recommId']['use'], 'n') == 'n' && StringUtils::strIsSet($this->requestPolicy['recommFl']['use'], 'n') == 'y') {
            throw new \Exception(__('회원정보 변경 시 추천인아이디 등록 불가는 추천인아이디 항목을 사용하여야 합니다'));
        }

        $getValue['memPw']['minlen'] = 10;
        $getValue['memPw']['maxlen'] = 16;

        $i = 0;
        if ($getValue['ex']) {
            foreach ($getValue['ex']['name'] as $k => $v) {
                if ($v) {
                    $i++;
                    $getValue['ex' . $i]['name'] = $v;
                    if (StringUtils::strIsSet($getValue['ex']['use'][$k])) {
                        $getValue['ex' . $i]['use'] = $getValue['ex']['use'][$k];
                    }
                    if (StringUtils::strIsSet($getValue['ex']['require'][$k])) {
                        $getValue['ex' . $i]['require'] = $getValue['ex']['require'][$k];
                    }
                    if (StringUtils::strIsSet($getValue['ex']['type'][$k])) {
                        $getValue['ex' . $i]['type'] = $getValue['ex']['type'][$k];
                    }
                    if (StringUtils::strIsSet($getValue['ex']['value'][$k])) {
                        $getValue['ex' . $i]['value'] = $getValue['ex']['value'][$k];
                    }
                }
            }
        }
        unset($getValue['ex']);

        if ($this->setValue(self::KEY, $getValue) !== true) {
            throw new \Exception("회원가입 항목 설정 저장 중 오류가 발생하였습니다.");
        }
    }

    /**
     * @return array 아이디, 패스워드, 닉네임 기본 정책 값
     */
    public function getStandardValidation(): array
    {
        return $this->standardValidation;
    }

    /*
     * 간편로그인 - 회원가입 항목값 설정
     */
    public function getJoinPolicyDisplay($mallSno)
    {
        //기본 정보
        $baseInfo =["아이디"=>"memId","이름"=>"memNm", "닉네임"=>"nickNm", "비밀번호"=>"memPw", "이메일"=>"email", "휴대폰번호"=>"cellPhone", "주소"=>"address","전화번호"=>"phone", "SMS수신동의"=>"smsFl", "이메일수신동의"=>"maillingFl"];
        //사업자 정보
        $businessInfo=["상호"=>"company", "사업자번호"=>"busiNo", "대표자명"=>"ceo", "업태"=>"service", "종목"=>"service", "사업장 주소"=>"comAddress"];
        //부가정보
        $supplementInfo=["팩스"=>"fax", "추천인아이디"=>"recommId", "생일"=>"birthDt","성별"=>"sexFl","결혼여부"=>"marriFl","직업"=>"job","관심분야"=>"interest","개인정보유효기간"=>"expirationFl","남기는 말씀"=>"memo"];
        //추가정보
        $additional=['ex1','ex2','ex3','ex4','ex5','ex6'];
        $totalInfo=[];
        $policyOrigin = $this->getPolicy($mallSno);

        foreach($policyOrigin as $key => $val) {
            if ($val['use'] == 'y') {
                if ($display = array_search($key, $baseInfo)) {
                    if ($val['require'] == 'y') {
                        $totalInfo['baseInfo']['requireY'][$key] = $display;
                    } else {
                        $totalInfo['baseInfo']['requireN'][$key] = $display;
                    }
                } elseif ($display = array_search($key, $businessInfo)) {
                    if ($val['require'] == 'y') {
                        $totalInfo['businessInfo']['requireY'][$key] = $display;
                    } else {
                        $totalInfo['businessInfo']['requireN'][$key] = $display;
                    }
                } elseif ($display = array_search($key, $supplementInfo)) {
                    if ($val['require'] == 'y') {
                        $totalInfo['supplementInfo']['requireY'][$key] = $display;
                    } else {
                        $totalInfo['supplementInfo']['requireN'][$key] = $display;
                    }
                } elseif (in_array($key, $additional)){
                    if ($val['require'] == 'y') {
                        $totalInfo['additionInfo']['requireY'][$key] = $val;
                    }
                    else {
                        $totalInfo['additionInfo']['requireN'][$key] = $val;
                    }
                }

                if(empty($policyOrigin['businessinfo']) === false){
                    $totalInfo['businessMember']['use'] = 'y';
                }
            }
        }

        return $totalInfo;
    }
}
