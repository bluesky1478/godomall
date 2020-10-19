<?php
/**
 * 에디터 이미지 업로드 Class
 *
 * @author sj
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
namespace Bundle\Component\File;

use Framework\Debug\Exception\Except;
use Framework\Utility\ArrayUtils;

class EditorUpload
{
    const ECT_UPLOAD_ONLYIMAGE = '%s.ECT_UPLOAD_ONLYIMAGE';
    const ECT_UPLOAD_FAILURE = '%s.ECT_UPLOAD_FAILURE';

    const TEXT_UPLOAD_ONLYIMAGE = '이미지 파일만 업로드가 가능합니다.';
    const TEXT_UPLOAD_FAILURE = '업로드가 실패하였습니다.';

    private $file;
    private $target;
    private $extType;
    private $chkType;
    private $dataFile = null;

    /**
     * 생성자
     */
    public function  __construct()
    {
    }

    /**
     * 에디터에서 이미지 업로드 (mini_editor)
     * @return string src 이미지 주소
     */
    public function uploadEditorImage($req, $files)
    {
        $src = '';
        $req['idx'] += 0;
        $req['mini_url'] = trim($req['mini_url']);

        if ($req['mini_url'] != '' && $req['mini_url'] != 'http://') {
            $src = $req['mini_url'];
        } else {
            if (!preg_match("/^image/", $files['mini_file']['type'])) {
                throw new Except(sprintf(parent::ECT_UPLOAD_ONLYIMAGE, 'EditorUpload'), __('이미지 파일만 업로드가 가능합니다.'));
                return;
            }

            if (is_uploaded_file($files['mini_file']['tmp_name'])) {
                $this->dataFile = \App::load('\\Component\\File\\DataFile');
                $this->file = &$files['mini_file'];
                $splitUploadNm = explode(".", $this->file['name']);
                $saveFileNm = time() . "." . $splitUploadNm[count($splitUploadNm) - 1];
                $this->target = $saveFileNm;

                $this->upload_set('image');
                if (!$this->upload()) {
                    throw new Except(sprintf(self::ECT_UPLOAD_ONLYIMAGE, 'EditorUpload'), __('이미지 파일만 업로드가 가능합니다.'));
                    exit;
                }

                $arrSrc = $this->dataFile->getUrl('editor', $this->target);
                //$src = $arrSrc['fullPath'];
                $src = $arrSrc['path'];
            }
        }
        if (!$src) {
            throw new Except(sprintf(self::ECT_UPLOAD_FAILURE, 'EditorUpload'), self::ECT_UPLOAD_FAILURE);
        }
        return $src;
    }

    /**
     * 에디터에서 이미지 업로드 (tiny_mce)
     * @param array $files $_FILES
     * @param string $mode 저장타입
     * @return string src 이미지 주소
     */
    public function uploadEditorImageByTinymce($files, $mode = '')
    {
        if (!preg_match("/^image/", $files['mini_file']['type'])) {
            throw new Except(sprintf(parent::ECT_UPLOAD_ONLYIMAGE, 'EditorUpload'), __('이미지 파일만 업로드가 가능합니다.'));
            return;
        }

        $msg = '';
        if (is_uploaded_file($files['mini_file']['tmp_name'])) {
            $this->dataFile = \App::load('\\Component\\File\\DataFile');
            $this->file = &$files['mini_file'];

            if ($mode == '')
                $mode = 'etc';
            $mode .= '/';
            $this->file['name'] = str_replace(' ', '', $this->file['name']);
            $this->target = $mode . $this->file['name'];

            $this->dataFile->setImageStorage('local', 'editor', 'editor');
            if ($this->dataFile->fileExists('editor', $this->target)) {
                $splitUploadNm = explode('.', $this->file['name']);
                $ext = $splitUploadNm[count($splitUploadNm) - 1];
                unset($splitUploadNm[count($splitUploadNm) - 1]);

                $fname = implode('.', $splitUploadNm);
                unset($splitUploadNm);

                $imageStorage = $this->dataFile->getImageStorage('editor');
                $fileList = glob($imageStorage['rootPath'] . '/' . $mode . $fname . '([0-9]*).' . $ext);
                if (ArrayUtils::isEmpty($fileList) === false) {
                    sort($fileList);
                    $arrLastFname = explode($imageStorage['rootPath'] . '/' . $mode . $fname, $fileList[count($fileList) - 1]);
                    $splitFname = explode('.', $arrLastFname[1]);
                    $newNum = preg_replace('/[^0-9]*/', '', $splitFname[0]) + 1;
                    $this->target = $mode . $fname . '(' . $newNum . ').' . $ext;
                    $msg = sprintf(__('같은 이름의 파일이 존재하여, 파일이름이 %1$s(%2$d)%3$s 로 변경되었습니다.'), $fname, $newNum, $ext);
                } else {
                    $newNum = 1;
                    $this->target = $mode . $fname . '(' . $newNum . ').' . $ext;
                    $msg = '같은 이름의 파일이 존재하여, 파일이름이 ' . $fname . '(' . $newNum . ').' . $ext . ' 로 변경되었습니다.';
                    $msg = sprintf(__('같은 이름의 파일이 존재하여, 파일이름이 %1$s(%2$d)%3$s 로 변경되었습니다.'), $fname, $newNum, $ext);
                }
            }

            $this->upload_set('image');
            if (!$this->upload()) {
                throw new Except(sprintf(self::ECT_UPLOAD_ONLYIMAGE, 'EditorUpload'), __('이미지 파일만 업로드가 가능합니다.'));
                exit;
            }

            $arrSrc = $this->dataFile->getUrl('editor', $this->target);
            //$src = $arrSrc['fullPath'];
            $src = $arrSrc['path'];
        }


        if (!$src) {
            throw new Except(sprintf(self::ECT_UPLOAD_FAILURE, 'EditorUpload'), self::ECT_UPLOAD_FAILURE);
        }
        return array('msg' => $msg, 'src' => $src);
    }

    /**
     * 변수 할당
     * @void
     */
    function upload_set($chkType = '')
    {
        switch ($this->chkType) {
            case "design":
                $this->extType = array('html', 'php');
                $this->chkType = "text";
                break;
            default:
                $this->extType = array('html', 'htm', 'php');
                $this->chkType = $chkType;
                break;
        }
    }

    /**
     * 일반 업로드 파일 확장자 검증
     * @return bool
     */
    function file_extension_check()
    {
        if ($this->file['name']) {
            $tmp = explode('.', $this->file['name']);
            $extension = strtolower($tmp[count($tmp) - 1]);
            if (in_array($extension, $this->extType))
                return false;
        }
        return true;
    }

    /**
     * 일반 업로드 파일 검증
     * @return bool
     */
    function file_type_check()
    {
        if (!function_exists('mime_content_type'))
            return true;
        if ($this->file['tmp_name']) {
            $mime = mime_content_type($this->file['tmp_name']);
            if ($this->chkType && !preg_match('/' . $this->chkType . '/', $mime))
                return false;
        }
        return true;
    }

    /**
     * 파일업로드
     * @return bool
     */
    function upload()
    {
        if ($this->file['tmp_name']) {
            if (!$this->file_extension_check()) {
                return false;
            }
            if (!$this->file_type_check()) {
                return false;
            }

            $this->dataFile->setImageStorage('local', 'editor', 'editor');

            // 이미지 저장
            $this->dataFile->setSrcLocalFile($this->file['tmp_name']);
            $this->dataFile->setDestFile('editor', $this->target);
            $this->dataFile->move(true);
        }
        return true;
    }
}
