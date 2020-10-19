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


use SplSubject;

/**
 * Class SmsAutoObserver
 * @package Bundle\Component\Sms
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class SmsAutoObserver implements \SplObserver
{
    protected $smsType;
    protected $smsAutoCodeType;
    protected $receiver;
    protected $replaceArguments;
    /** @var null|\Closure */
    protected $validateFunction = null;

    /**
     * update
     *
     * @param SplSubject|SmsAuto $subject
     */
    public function update(SplSubject $subject)
    {
        $logger = \App::getInstance('logger');
        $validateFunction = $this->validateFunction;
        if ($validateFunction != null) {
            $validateResult = $validateFunction();
            if ($validateResult['result'] === true) {
                $subject->setSmsType($this->smsType);
                $subject->setSmsAutoCodeType($this->smsAutoCodeType);
                $subject->setReceiver($this->receiver);
                $subject->setReplaceArguments($this->replaceArguments);
                $subject->autoSend();
            } else {
                $logger->info(sprintf('%s, %s', __METHOD__, $validateResult['message']));
            }
        } else {
            $subject->setSmsType($this->smsType);
            $subject->setSmsAutoCodeType($this->smsAutoCodeType);
            $subject->setReceiver($this->receiver);
            $subject->setReplaceArguments($this->replaceArguments);
            $subject->autoSend();
        }
    }

    /**
     * @param mixed $smsType
     */
    public function setSmsType($smsType)
    {
        $this->smsType = $smsType;
    }

    /**
     * @param mixed $replaceArguments
     */
    public function setReplaceArguments($replaceArguments)
    {
        $this->replaceArguments = $replaceArguments;
    }

    /**
     * @param mixed $smsAutoCodeType
     */
    public function setSmsAutoCodeType($smsAutoCodeType)
    {
        $this->smsAutoCodeType = $smsAutoCodeType;
    }

    /**
     * @param mixed $receiver
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * @param \Closure $validateFunction
     */
    public function setValidateFunction(\Closure $validateFunction)
    {
        $this->validateFunction = $validateFunction;
    }
}
