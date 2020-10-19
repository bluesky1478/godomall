<?php
/**
 * 파라미터검증 Class
 *
 * @author    gise, sunny
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

/**
 * 검증기
 */

namespace Bundle\Component\Validator;

use Logger;

/**
 * Class Validator
 * @package Component\Validator
 */
class Validator
{
    const TEXT_ALPHA_INVALID = '%s은(는) 알파벳으로만 구성되어야 합니다.';
    const TEXT_ALPHA_S_INVALID = '%s은(는) 알파벳(+공백)으로만 구성되어야 합니다.';
    const TEXT_ALPAH_NUM_INVALID = '%s은(는) 알파벳과 숫자로만 구성되어야 합니다.';
    const TEXT_ALPAH_NUM_S_INVALID = '%s은(는) 알파벳(+공백)과 숫자만 구성되어야 합니다.';
    const TEXT_COLOR_INVALID = '%s은(는) 색상형식(#ffffff)에 맞지 않습니다.';
    const TEXT_DATE_INVALID = '%s은(는) 날짜형식에 맞지 않습니다.';
    const TEXT_DATETIME_INVALID = '%s은(는) 날짜시간형식(YYYY-MM-DD HH:II:SS)에 맞지 않습니다.';
    const TEXT_DESIGN_XMLID_INVALID = '%s은(는) 디자인 XML ID 형식에 맞지 않습니다.';
    const TEXT_DESIGN_LINKID_INVALID = '%s은(는) 디자인 링크 ID 형식에 맞지 않습니다.';
    const TEXT_DESIGN_SKINNAME_INVALID = '%s은(는) 디자인 스킨명 형식에 맞지 않습니다.';
    const TEXT_DIRECTORY_INVALID = '%s은(는) 디렉토리형식(/dir/)에 맞지 않습니다.';
    const TEXT_DIRECTORYNAME_INVALID = '%s은(는) 디렉토리명 형식에 맞지 않습니다.';
    const TEXT_DOMAIN_INVALID = '%s은(는) 도메인형식에 맞지 않습니다.';
    const TEXT_EMAIL_INVALID = '%s은(는) 이메일형식에 맞지 않습니다.';
    const TEXT_FILENAME_INVALID = '%s은(는) 파일명 형식(file.txt)에 맞지 않습니다.';
    const TEXT_IP_INVALID = '%s은(는) IP형식에 맞지 않습니다.';
    const TEXT_MAXLEN_INVALID = '%s은(는) %s자 이하로 입력하셔야 합니다.';
    const TEXT_MINLEN_INVALID = '%s은(는) %s자 이상으로 입력하셔야 합니다.';
    const TEXT_NUMBER_INVALID = '%s은(는) 숫자형식에 맞지 않습니다.';
    const TEXT_ONEZERO_INVALID = '%s은(는) 0  또는 1 이어야 합니다.';
    const TEXT_PASSWORD_INVALID = '%s은(는) 비밀번호형식에 맞지 않습니다.';
    const TEXT_PATH_INVALID = '%s은(는) 경로형식에 맞지 않습니다.';
    const TEXT_PATTERN_INVALID = '%s은(는) 형식에 맞지 않습니다.';
    const TEXT_PHONE_INVALID = '%s은(는) 전화번호형식에 맞지 않습니다.';
    const TEXT_TIME_INVALID = '%s은(는) 시간형식(HH:II)에 맞지 않습니다.';
    const TEXT_URL_INVALID = '%s은(는) URL형식에 맞지 않습니다.';
    const TEXT_USERID_INVALID = '%s은(는) 아이디형식에 맞지 않습니다.';
    const TEXT_YN_INVALID = '%s은(는) y 또는 n 이어야 합니다.';
    const TEXT_BOOLEAN_INVALID = '%s은(는) true 또는 false 이어야 합니다.';
    /**
     * act 함수 실행 시 데이터가 없는경우에도 체크하도록 하기 위한 플래그
     * @var bool
     */
    protected $ignoreIssetByAct = false;
    /** @var array 복합 검증 규칙 */
    public $rules = [];
    /** @var array 검증 결과 에러 */
    public $errors = [];

    /**
     * 복합 검증 초기화
     */
    public function init()
    {
        $this->rules = [];
        $this->errors = [];
    }

    /**
     * 복합 검증 규칙 삽입
     * errMsg 이후로 전달받는 파라미터는 호출하는 검증 함수의 파라미터로 전달된다.
     *
     * @param string $eleName  원소명
     * @param string $command  규칙 (alpha : 알파벳 검증, alphaS : 알파벳+space 검증, alphaNum : 알파벳+숫자 검증, alphaNumS :
     *                         알파벳+숫자+space 검증, alphaNumS_hangul : 알파벳+숫자+space+한글 검증, color : 색상코드 검증(#ffffff), date :
     *                         날짜 검증(YYYY-MM-DD), datetime : datetime 검증, designLinkid : Design Link ID 검증,
     *                         designSkinName : Design 스킨명 검증, designXmlid : Design XML ID 검증, directory : 디렉토리 검증,
     *                         directoryKr : 디렉토리(+한글) 검증, directoryname : 디렉토리명 검증, directorynameKr : 디렉토리명(+한글) 검증,
     *                         domain : 도메인 검증, email : 이메일 검증, filename : 파일명 검증, filenameKr : 파일명(+한글) 검증, ip : IP
     *                         검증, maxlen : 문자열 최대길이 검증, minlen : 문자열 최소길이 검증, number : 숫자 검증, onezero : 0,1 검증,
     *                         password : 비밀번호 검증, path : 경로 검증, pattern : 패턴 검증, phone : 전화번호 검증, required : 필수 검증,
     *                         time : 시간 검증(HH:II), url : URL 검증, userid : 아이디 검증, yn : y,n 검증)
     * @param bool   $required 필수여부
     * @param string $errMsg   에러메시지 (문장 or {원소명})
     */
    public function add($eleName, $command, $required = false, $errMsg = null)
    {
        $rule = new ValidatorRule();
        $rule->eleName = $eleName;
        $rule->command = $command;
        $rule->required = $required;
        $rule->errMsg = $errMsg;
        $numargs = func_num_args();
        for ($i = 4; $i < $numargs; $i++) {
            $arg = func_get_arg($i);
            array_push($rule->args, $arg);
        }
        array_push($this->rules, $rule);
    }

    /**
     * 복합 검증 실행
     *
     * @param  array $arr 데이터
     *
     * @return bool
     */
    public function act(&$arr, $garbageDelete = false)
    {
        $ret = true;
        $eleNames = [];
        foreach ($this->rules as $rule) {
            if (isset($arr[$rule->eleName]) === true || $this->ignoreIssetByAct) {
                $eleValue = $arr[$rule->eleName];
                // 필수 항목 체크
                if ($rule->required === true && $this->required($eleValue) === false) {
                    $ret = false;
                    if ($rule->errMsg == '') {
                        $msg = sprintf(__('필수항목을 입력해주세요. : %s'), $rule->eleName);
                    } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                        $msg = sprintf(__('필수항목을 입력해주세요. : %s'), $matches[1]);
                    } else {
                        $msg = sprintf($rule->errMsg, $rule->eleName);
                    }
                    $this->pushErrors($rule->eleName, $msg);
                }

                // 검증
                switch ($rule->command) {
                    case 'alpha' : // 알파벳 검증
                        if ($this->alpha($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 알파벳으로만 구성되어야 합니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 알파벳으로만 구성되어야 합니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'alphaS' : // 알파벳+space 검증
                        if ($this->alphaS($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 알파벳(+공백)으로만 구성되어야 합니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 알파벳(+공백)으로만 구성되어야 합니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'alphaNum' : // 알파벳+숫자 검증
                        if ($this->alphaNum($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 알파벳과 숫자로만 구성되어야 합니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 알파벳과 숫자로만 구성되어야 합니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'alphaNumS' : // 알파벳+숫자+space 검증
                        if ($this->alphaNumS($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 알파벳(+공백)과 숫자만 구성되어야 합니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 알파벳(+공백)과 숫자만 구성되어야 합니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'alphaNumS_hangul' : // 알파벳+숫자+space+한글 검증
                        if ($this->alphaNumS_hangul($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 알파벳(+공백)과 숫자만 구성되어야 합니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 알파벳(+공백)과 숫자만 구성되어야 합니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'alphaHangul' : // 알파벳+한글 검증
                        if ($this->alphaHangul($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 영어와 한글만 구성되어야 합니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 영어와 한글만 구성되어야 합니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'color' : // 색상코드 검증(#ffffff)
                        if ($this->color($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 색상형식(#ffffff)에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 색상형식(#ffffff)에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'date' : // 날짜 검증(YYYY-MM-DD)
                        if ($this->date($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 날짜형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 날짜형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'datetime' : // datetime 검증
                        if ($this->datetime($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 날짜시간형식(YYYY-MM-DD HH:II:SS)에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 날짜시간형식(YYYY-MM-DD HH:II:SS)에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'designLinkid' : // Design Link ID 검증
                        if (isset($rule->args[0]) === false) {
                            $rule->args[0] = false;
                        }
                        if ($this->designLinkid($eleValue, $rule->args[0]) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 디자인 링크 ID 형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 디자인 링크 ID 형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'designSkinName' : // Design 스킨명 검증
                        if (isset($rule->args[0]) === false) {
                            $rule->args[0] = false;
                        }
                        if ($this->designSkinName($eleValue, $rule->args[0]) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 디자인 스킨명 형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 디자인 스킨명 형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'designXmlid' : // Design XML ID 검증
                        if (isset($rule->args[0]) === false) {
                            $rule->args[0] = false;
                        }
                        if ($this->designXmlid($eleValue, $rule->args[0]) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 디자인 XML ID 형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 디자인 XML ID 형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'directory' : // 디렉토리 검증
                        if ($this->directory($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 디렉토리형식(/dir/)에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 디렉토리형식(/dir/)에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'directoryKr' : // 디렉토리(+한글) 검증
                        if ($this->directoryKr($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 디렉토리형식(/dir/)에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 디렉토리형식(/dir/)에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'directoryname' : // 디렉토리명 검증
                        if ($this->directoryname($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 디렉토리명 형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 디렉토리명 형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'directorynameKr' : // 디렉토리명(+한글) 검증
                        if ($this->directorynameKr($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 디렉토리명 형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 디렉토리명 형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'domain' : // 도메인 검증
                        if ($this->domain($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 도메인형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 도메인형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'email' : // 이메일 검증
                        if ($this->email($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 이메일형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 이메일형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'filename' : // 파일명 검증
                        if ($this->filename($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 파일명 형식(file.txt)에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 파일명 형식(file.txt)에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'filenameKr' : // 파일명(+한글) 검증
                        if ($this->filenameKr($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 파일명 형식(file.txt)에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 파일명 형식(file.txt)에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'ip' : // IP 검증
                        if ($this->ip($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) IP형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) IP형식에 맞지 않습니다.'), $$matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'maxlen' : // 문자열 최대길이 검증
                        if ($this->maxlen($rule->args[0], $eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) %s자 이하로 입력하셔야 합니다.'), $rule->eleName, $rule->args[0]);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) %s자 이하로 입력하셔야 합니다.'), $matches[1], $rule->args[0]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'mbMaxlen' : // 문자열 최대길이 검증 mb
                        if ($this->maxlen($rule->args[0], $eleValue, false, 'UTF-8') === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) %s자 이하로 입력하셔야 합니다.'), $rule->eleName, $rule->args[0]);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) %s자 이하로 입력하셔야 합니다.'), $matches[1], $rule->args[0]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'minlen' : // 문자열 최소길이 검증
                        if ($this->minlen($rule->args[0], $eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) %s자 이상으로 입력하셔야 합니다.'), $rule->eleName, $rule->args[0]);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) %s자 이상으로 입력하셔야 합니다.'), $matches[1], $rule->args[0]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'number' : // 숫자 검증
                        $args = $rule->args;
                        array_unshift($args, $eleValue);
                        if (call_user_func_array(
                                [
                                    $this,
                                    'number',
                                ], $args
                            ) === false
                        ) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 숫자형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 숫자형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'double' : // 숫자 및 소수점 검증
                        $args = $rule->args;
                        array_unshift($args, $eleValue);
                        if (call_user_func_array(
                                [
                                    $this,
                                    'double',
                                ], $args
                            ) === false
                        ) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 숫자형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 숫자형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'memberDcDouble' : // 숫자 및 소수점 검증
                        $args = $rule->args;
                        array_unshift($args, $eleValue);
                        if (call_user_func_array(
                                [
                                    $this,
                                    'double',
                                ], $args
                            ) === false
                        ) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s 할인율 합계는 %s를 초과할 수 없습니다.'), $rule->eleName, '100%');
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s 할인율 합계는 %s를 초과할 수 없습니다.'), $matches[1], '100%');
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'signDouble' : // 숫자 및 소수점 검증
                        $args = $rule->args;
                        array_unshift($args, $eleValue);
                        if (call_user_func_array(
                                [
                                    $this,
                                    'signDouble',
                                ], $args
                            ) === false
                        ) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 숫자형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 숫자형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'onezero' : // 0,1 검증
                        if ($this->onezero($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 0  또는 1 이어야 합니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 0  또는 1 이어야 합니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'password' : // 비밀번호  검증
                        if ($this->password($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 비밀번호형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 비밀번호형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'path' : // 경로 검증
                        if ($this->path($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 경로형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 경로형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'pattern' : // 패턴 검증
                        if ($this->pattern($rule->args[0], $eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'phone' : // 전화번호 검증
                        if ($this->phone($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 전화번호형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 전화번호형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'required' : // 필수 검증
                        if ($this->required($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('필수항목을 입력해주세요. : %s'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('필수항목을 입력해주세요. : %s'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'time' : // 시간 검증(HH:II)
                        if ($this->time($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 시간형식(HH:II)에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 시간형식(HH:II)에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'url' : // URL 검증
                        if ($this->url($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) URL형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) URL형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'userid' : // 아이디 검증
                        if ($this->userid($eleValue, $rule->args[0], $rule->args[1]) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 아이디형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 아이디형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'recommid' : // 추천 아이디 검증
                        if ($this->recommid($eleValue, $rule->args[0], $rule->args[1]) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 아이디형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 아이디형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'yn' : // y,n 검증
                        if ($this->yn($eleValue) === false) {
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) y 또는 n 이어야 합니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) y 또는 n 이어야 합니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                    case 'memberNameGlobal': // 해외상점 회원이름 검증 (사업자 상호 검증)
                        if($this->memberNameGlobal($eleValue) === false){
                            $ret = false;
                            if ($rule->errMsg == '') {
                                $msg = sprintf(__('%s은(는) 형식에 맞지 않습니다.'), $rule->eleName);
                            } elseif (preg_match('/^\{(.*)\}$/', $rule->errMsg, $matches) == 1) {
                                $msg = sprintf(__('%s은(는) 형식에 맞지 않습니다.'), $matches[1]);
                            } else {
                                $msg = sprintf($rule->errMsg, $rule->eleName);
                            }
                            $this->pushErrors($rule->eleName, $msg);
                        }
                        break;
                }
            }
            array_push($eleNames, $rule->eleName);
        }
        // 불필요 정보 제거
        if ($garbageDelete === true) {
            if (is_array($arr)) {
                foreach (array_keys($arr) as $key) {
                    if (array_search($key, $eleNames) === false) {
                        unset($arr[$key]);
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * 복합 검증 에러 삽입
     *
     * @param string $eleName 원소명
     * @param string $errMsg  에러메시지
     */
    protected function pushErrors($eleName, $errMsg)
    {
        if (isset($this->errors[$eleName]) === false) {
            $this->errors[$eleName] = $errMsg;
        }
    }

    /**
     * 원소이름
     * @return array
     */
    public function getEleName()
    {
        $eleNames = [];
        foreach ($this->rules as $rule) {
            array_push($eleNames, $rule->eleName);
        }

        return $eleNames;
    }

    /**
     * 알파벳 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function alpha($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^[A-Za-z]*$/', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 알파벳+space 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function alphaS($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^[A-Za-z ]*$/', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 알파벳+숫자 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function alphaNum($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^[A-Za-z0-9]*$/', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 알파벳+숫자+space 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function alphaNumS($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^[A-Za-z0-9 ]*$/', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 알파벳+숫자+space+한글 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function alphaNumS_hangul($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            $hangul_jamo = '\x{1100}-\x{11ff}';
            $hangul_compatibility_jamo = '\x{3130}-\x{318f}';
            $hangul_syllables = '\x{ac00}-\x{d7af}';
            if (static::pattern('/^[A-Za-z0-9 ' . $hangul_jamo . $hangul_compatibility_jamo . $hangul_syllables . ']*$/u', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 알파벳+한글 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function alphaHangul($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            $hangul_jamo = '\x{1100}-\x{11ff}';
            $hangul_compatibility_jamo = '\x{3130}-\x{318f}';
            $hangul_syllables = '\x{ac00}-\x{d7af}';
            if (static::pattern('/^[A-Za-z' . $hangul_jamo . $hangul_compatibility_jamo . $hangul_syllables . ']*$/u', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 색상코드 검증(#ffffff)
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function color($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^#?[A-Za-z0-9]*$/', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 날짜 검증(YYYY-MM-DD)
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function date($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $value) === false) {
                return false;
            }
            $year = substr($value, 0, 4);
            $month = substr($value, 5, 2);
            $day = substr($value, 8, 2);
            if (checkdate($month, $day, $year) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * datetime 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function datetime($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Design Link ID 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function designLinkid($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern("~^[\sa-zA-Z0-9\~!@#$%\^&()\-_+=\{\}\[\];',\.\/]+$~", $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Design 스킨명 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function designSkinName($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^[A-Za-z0-9_-]+$/', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Design XML ID 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function designXmlid($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern("~^[\sa-zA-Z0-9\~!@#$%\^&()\-_+=\{\}\[\];',\.]+$~", $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 디렉토리 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function directory($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern("~^(/?[\sa-zA-Z0-9\~!@#$%\^&()\-_+=\{\}\[\];',\.\:]+)+/?$~", $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 디렉토리(+한글) 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function directoryKr($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern("~^(/?[\sa-zA-Z0-9\~!@#$%\^&()\-_+=\{\}\[\];',\.\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}]+)+/?$~u", $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 디렉토리명 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function directoryname($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern("~^[\sa-zA-Z0-9\~!@#$%\^&()\-_+=\{\}\[\];',\.]+$~", $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 디렉토리명(+한글) 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function directorynameKr($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern("~^[\sa-zA-Z0-9\~!@#$%\^&()\-_+=\{\}\[\];',\.\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}]+$~u", $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 도메인 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function domain($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^[.a-zA-Z0-9-]+\.[a-zA-Z]+$/', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 도메인 검증 (한글)
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function domainKr($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            $hangul_jamo = '\x{1100}-\x{11ff}';
            $hangul_compatibility_jamo = '\x{3130}-\x{318f}';
            $hangul_syllables = '\x{ac00}-\x{d7af}';
            if (static::pattern('/^[.a-zA-Z0-9-' . $hangul_jamo . $hangul_compatibility_jamo . $hangul_syllables .  ']+\.[a-zA-Z' . $hangul_jamo . $hangul_compatibility_jamo . $hangul_syllables .  ']+$/u', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 이메일 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function email($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            return filter_var($value, FILTER_VALIDATE_EMAIL) != false;
        }

        return true;
    }

    /**
     * 파일명 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function filename($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern("~^[\sa-zA-Z0-9\~!@#$%\^&()\-_+=\{\}\[\];',\.]+(\.\w+)$~", $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 파일명(+한글) 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function filenameKr($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern("~^[\sa-zA-Z0-9\~!@#$%\^&()\-_+=\{\}\[\];',\.\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}]+(\.\w+)$~u", $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * IP 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function ip($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^\\d{1,3}(\\.\\d{1,3}){3}$/', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 문자열 최대길이 검증
     *
     * @param  number $max      최대길이
     * @param  string $value
     * @param  bool   $required 필수여부
     * @param  string $encoding 인코딩값
     *
     * @return bool
     */
    public static function maxlen($max, $value, $required = false, $encoding = null)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        }

        if ($isRequired === true) {
            if (($encoding === null && $max < \strlen($value))
                || ($encoding !== null && $max < mb_strlen($value, $encoding))) {
                return false;
            }
        }

        return true;
    }

    /**
     * 문자열 최소길이 검증
     *
     * @param  number $min      최소길이
     * @param  string $value
     * @param  bool   $required 필수여부
     * @param  string $encoding 인코딩값
     *
     * @return bool
     */
    public static function minlen($min, $value, $required = false, $encoding = null)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        }

        if ($isRequired === true) {
            if (($encoding === null && $min > \strlen($value))
                || ($encoding !== null && $min > mb_strlen($value, $encoding))) {
                return false;
            }
        }

        return true;
    }

    /**
     * 숫자 검증
     *
     * @param  mixed  $value
     * @param  number $min      최소값
     * @param  number $max      최대값
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function number($value, $min = null, $max = null, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^[0-9]*$/', $value) === false) {
                return false;
            }
            if (isset($min) && ($value < $min)) {
                return false;
            }
            if (isset($max) && ($value > $max)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 숫자 및 소수점 검증
     * 체크 시 값에 , 가 있을 경우 오류가 발생한다.
     *
     * @param  mixed  $value
     * @param  number $min      최소값
     * @param  number $max      최대값
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function double($value, $min = null, $max = null, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^\d+(\.\d*)?$/', $value) === false) {
                return false;
            }
            if (isset($min) && ($value < $min)) {
                return false;
            }
            if (isset($max) && ($value > $max)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 부호, 숫자, 소숫점 검증
     *
     * @static
     *
     * @param mixed      $value
     * @param null       $min      최소값
     * @param null       $max      최대값
     * @param bool|false $required 필수여부
     *
     * @return bool
     */
    public static function signDouble($value, $min = null, $max = null, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^[+-]?\d+(\.\d*)?$/', $value) === false) {
                return false;
            }
            if (isset($min) && ($value < $min)) {
                return false;
            }
            if (isset($max) && ($value > $max)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 0,1 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function onezero($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^[.01]+$/', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 비밀번호  검증
     * 10-16자의 영문 대소문자,숫자,특수문자 사용
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     * @param int $minLength
     * @param int $maxLength
     *
     * @return bool
     */
    public static function password($value, $required = false, $minLength = 10, $maxLength = 16)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        }
        if ($isRequired === true) {
            $patterns = static::getPasswordPattern();
            if (static::minlen($minLength, $value) === false || static::maxlen($maxLength, $value) === false) {
                return false;
            }
            $hasSpecial = static::pattern('/([^a-zA-Z0-9])/', $value);
            $isSpecial = static::pattern($patterns['special'], $value);
            if ($hasSpecial && !$isSpecial) {
                return false;
            }
            $isAlpha = static::pattern($patterns['alpha'], $value);
            $isNumeric = static::pattern($patterns['numeric'], $value);
            if (($isAlpha && $isNumeric) === false
                && ($isAlpha && $isSpecial) === false
                && ($isNumeric && $isSpecial) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 비밀번호  검증
     * 10-16자의 영문 대소문자,숫자,특수문자 모두 사용
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     * @param int $minLength
     * @param int $maxLength
     *
     * @return bool
     */
    public static function difficultPassword($value, $required = false, $minLength = 10, $maxLength = 16)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        }
        if ($isRequired === true) {
            $patterns = static::getPasswordPattern();
            if (static::minlen($minLength, $value) === false || static::maxlen($maxLength, $value) === false) {
                return false;
            }

            $isAlpha = static::pattern($patterns['alpha'], $value);
            $isNumeric = static::pattern($patterns['numeric'], $value);
            $isSpecial = static::pattern($patterns['special'], $value);
            if (($isAlpha && $isNumeric && $isSpecial) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 비밀번호 검증 숫자 또는 알파벳 패턴인지 체크한다.
     *
     * @static
     *
     * @param      $value
     * @param bool $required
     * @param int $minLength
     * @param int $maxLength
     *
     * @return bool
     */
    public static function simplePassword($value, $required = false, $minLength = 10, $maxLength = 16)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            $patterns = static::getPasswordPattern();
            if (static::minlen($minLength, $value) === false || static::maxlen($maxLength, $value) === false) {
                return false;
            }
            if (static::pattern($patterns['alpha'], $value) || static::pattern($patterns['numeric'], $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 경로 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function path($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern("~^(/?[\sa-zA-Z0-9\~!@#$%\^&()\-_+=\{\}\[\];',\.]+)+/?(\.\w+)*$~", $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 패턴 검증
     *
     * @param  string $pattern
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function pattern($pattern, $value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (preg_match($pattern, $value) == 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * 전화번호 검증
     * static::required에서 값이 null이 아니거나 ''이 아니면 필수가 아니더라도 검증하게끔 되어있다.
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function phone($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (is_array($value)) {
                $value = implode('-', $value);
            }
            if (strlen($value) > 8) {
                if (static::pattern('/^[0-9]{2,4}\-?[0-9]{3,4}\-?[0-9]{4}$/', $value) === false) {
                    return false;
                }
            } else {
                if (static::pattern('/^[0-9]{3,4}\-?[0-9]{4}$/', $value) === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 필수 검증
     *
     * @param  mixed $value
     *
     * @return bool
     */
    public static function required($value)
    {
        $value = trim($value);
        if (is_null($value) === true || $value == '') {
            return false;
        }

        return true;
    }

    /**
     * 시간 검증(HH:II)
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function time($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^[0-9]{2}:[0-9]{2}$/', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 트리 디렉토리 ID 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function treeDirId($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            //			if (static::pattern("~^[\sa-zA-Z0-9\~!@#$%\^&()\-_+=\{\}\[\];',\.]+$~", $value) === false) {
            //				return false;
            //			}
        }

        return true;
    }

    /**
     * URL 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function url($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            return filter_var($value, FILTER_VALIDATE_URL);
        }

        return true;
    }

    /**
     * 아이디 검증
     * 4-20자의 영문 대소문자,숫자,특수기호(_)만 사용
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @param bool    $length   길이체크 여부
     *
     * @return bool
     */
    public static function userid($value, $required = false, $length = true)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            preg_match_all('/@/', $value, $match);
            if (count($match[0]) > 1) {
                return false;
            }
            if (static::pattern('/^[a-zA-Z0-9\.\-\_\@]*$/', $value) === false) {
                return false;
            }
            if ($length) {
                $policy = gd_policy('member.joinitem');
                if (static::minlen($policy['memId']['minlen'], $value) === false || static::maxlen($policy['memId']['maxlen'], $value) === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 추천 아이디 검증
     * 4-20자의 영문 대소문자,숫자,특수기호(_),한글만 사용
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @param bool    $length   길이체크 여부
     *
     * @return bool
     */
    public static function recommid($value, $required = false, $length = true)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            $hangul_jamo = '\x{1100}-\x{11ff}';
            $hangul_compatibility_jamo = '\x{3130}-\x{318f}';
            $hangul_syllables = '\x{ac00}-\x{d7af}';
            preg_match_all('/@/', $value, $match);
            if (count($match[0]) > 1) {
                return false;
            }

            if (static::pattern('/^[a-zA-Z0-9\.\-\_\@' . $hangul_jamo . $hangul_compatibility_jamo . $hangul_syllables . ']*$/u', $value) === false) {
                return false;
            }
            if ($length) {
                $policy = gd_policy('member.joinitem');
                if (static::minlen($policy['memId']['minlen'], $value) === false || static::maxlen($policy['memId']['maxlen'], $value) === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * y,n 검증
     *
     * @param  string $value
     * @param  bool   $required 필수여부
     *
     * @return bool
     */
    public static function yn($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (static::pattern('/^[.nyNY]+$/', $value) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 해외몰 회원명 검증 (사업자 상호 검증)
     *
     * @static
     *
     * @param      $value
     * @param bool $required
     *
     * @return bool
     */
    public static function memberNameGlobal($value, $required = false)
    {
        $isRequired = static::required($value);
        if ($required === true && $isRequired === false) {
            return false;
        } elseif ($isRequired === true) {
            if (preg_match('/[\"\'\\\|\/]/', $value) > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * 비밀번호 검증 패턴 반환
     *
     * @return array
     */
    public static function getPasswordPattern()
    {
        return [
            'alpha'   => '/[a-zA-Z]/',
            'numeric' => '/[0-9]/',
            'special' => '/[\!\@\#\$\%\^\&\*\(\)\_\+\-\=\`\~]/',
        ];
    }

    /**
     * @param boolean $ignoreIssetByAct
     */
    public function setIgnoreIssetByAct($ignoreIssetByAct)
    {
        $this->ignoreIssetByAct = $ignoreIssetByAct;
    }

    /**
     * isset 검증 후 act 수행
     * 
     * @param $arr
     * @param bool $garbageDelete
     * @return bool
     */
    public function actAfterIsset(&$arr, $garbageDelete = false)
    {
        $result = true;
        foreach ($this->rules as $rule) {
            if (isset($arr[$rule->eleName]) === false && $rule->required === true) {
                $result = false;
                if ($rule->errMsg == '') {
                    $msg = sprintf(__('필수항목을 입력해주세요. : %s'), $rule->eleName);
                } else {
                    $msg = sprintf($rule->errMsg, $rule->eleName);
                }
                $this->pushErrors($rule->eleName, $msg);
            }
        }

        if ($result) {
            $result = $this->act($arr, $garbageDelete);
        }

        return $result;
    }
}
