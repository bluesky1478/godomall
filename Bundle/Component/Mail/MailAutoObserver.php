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


use SplSubject;

/**
 * Class MailAutoObserver
 * @package Bundle\Component\Mail
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class MailAutoObserver implements \SplObserver
{
    protected $type;
    protected $replaceInfo;
    protected $mallSno;
    /** @var null|\Closure */
    protected $validateFunction = null;

    /**
     * Receive update from subject
     * @link  http://php.net/manual/en/splobserver.update.php
     *
     * @param SplSubject|MailMimeAuto $subject <p>
     *                                         The <b>SplSubject</b> notifying the observer of an update.
     *                                         </p>
     *
     * @return void
     * @since 5.1.0
     * @throws \Exception
     */
    public function update(SplSubject $subject)
    {
        $logger = \App::getInstance('logger');
        $validateFunction = $this->validateFunction;
        if ($validateFunction != null) {
            $validateResult = $validateFunction();
            if ($validateResult['result'] === true) {
                $subject->init($this->type, $this->replaceInfo, $this->mallSno)->autoSend();
            } else {
                $logger->info(sprintf('%s, %s', __METHOD__, $validateResult['message']));
            }
        } else {
            $subject->init($this->type, $this->replaceInfo, $this->mallSno)->autoSend();
        }
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param mixed $replaceInfo
     */
    public function setReplaceInfo($replaceInfo)
    {
        $this->replaceInfo = $replaceInfo;
    }

    /**
     * @param null|int $mallSno
     */
    public function setMallSno($mallSno = null)
    {
        $this->mallSno = $mallSno;
    }

    /**
     * @param \Closure $validateFunction
     */
    public function setValidateFunction(\Closure $validateFunction)
    {
        $this->validateFunction = $validateFunction;
    }
}
