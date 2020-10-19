<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Smart to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

/**
 * 복합 검증 규칙 객체
 * @property string $eleName 원소 이름
 * @property string $command 규칙 코드
 * @property string $errMsg 에러메시지
 * @property array $args 규칙인자
 */

namespace Bundle\Component\Validator;

class ValidatorRule
{
    public $eleName;
    public $command;
    public $required;
    public $errMsg;
    public $args = [];
}
