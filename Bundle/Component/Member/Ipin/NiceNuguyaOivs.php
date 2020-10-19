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
namespace Bundle\Component\Member\Ipin;

/**
 * Class NiceNuguyaOivs
 * @package Component\Member\Ipin
 * @author yjwee
 */
class NiceNuguyaOivs
{

    //#############################################################################
    //#####
    //#####	한국신용정보주식회사				나이스아이핀 서비스 확인 스크립트
    //#####
    //#####	=====================================================================
    //#####
    //#####	Descriptions
    //#####		- 한신정에서 제공하는 서비스에 대한 확인 작업을 처리한다.
    //#####
    //#####	---------------------------------------------------------------------
    //#####
    //#####	작성자 		: (주)한국신용정보 (www.nice.co.kr)
    //#####	원본참조	:
    //#####	원본파일	:
    //#####	작성일자	: 2006.03.07
    //#####
    //#############################################################################

    /**
     * decodeChunked Transfer-Encoding : Chunked (Decoding)
     *
     * @param $buffer
     * @return string
     */
    function decodeChunked($buffer)
    {
        $length = 0;
        $new = '';

        $chunkend = strpos($buffer, "\r\n") + 2;
        $temp = substr($buffer, 0, $chunkend);
        $chunk_size = hexdec(trim($temp));
        $chunkstart = $chunkend;

        while ($chunk_size > 0) {
            $chunkend = strpos($buffer, "\r\n", $chunkstart + $chunk_size);

            if ($chunkend == FALSE) {
                $chunk = substr($buffer, $chunkstart);
                $new .= $chunk;
                $length += strlen($chunk);
                break;
            }

            $chunk = substr($buffer, $chunkstart, $chunkend - $chunkstart);
            $new .= $chunk;
            $length += strlen($chunk);

            $chunkstart = $chunkend + 2;

            $chunkend = strpos($buffer, "\r\n", $chunkstart) + 2;
            if ($chunkend == FALSE) break;

            $temp = substr($buffer, $chunkstart, $chunkend - $chunkstart);
            $chunk_size = hexdec(trim($temp));
            $chunkstart = $chunkend;
        }

        return $new;
    }

    /**
     * resolveResponseData Removing Header and Return Contents
     *
     * @param $buffer
     * @return bool|string
     */
    function resolveResponseData($buffer)
    {
        $data = $buffer . "\r\n\r\n\r\n\r\n";
        //	Remove 100 Header
        if (preg_match('/^HTTP\/1\.1 100/', $data)) {
            if ($pos = strpos($data, "\r\n\r\n")) {
                $data = ltrim(substr($data, $pos));
            } elseif ($pos = strpos($data, "\r\n")) {
                $data = ltrim(substr($data, $pos));
            }
        }

        //	Separate Content from Header
        if ($pos = strpos($data, "\r\n\r\n")) {
            $lb = "\r\n";
        } elseif ($pos = strpos($data, "\n\n")) {
            $lb = "\n";
        } else {
            return false;
        }

        $header_data = trim(substr($data, 0, $pos));
        $header_array = explode($lb, $header_data);
        $data = ltrim(substr($data, $pos));

        //	Clean Header
        if (count($header_array) > 0) {
            if (!strpos($header_array[0], "200")) return false;
        } else {
            return false;
        }

        foreach ($header_array as $header_line) {
            $arr = explode(':', $header_line);
            if (count($arr) >= 2)
                $headers[trim($arr[0])] = trim($arr[1]);
        }

        // decode transfer-encoding
        if (isset($headers['Transfer-Encoding']) && $headers['Transfer-Encoding'] == 'chunked') {
            if (!$data = $this->decodeChunked($data)) return false;
        }

        //	decode content-encoding
        if (isset($headers['Content-Encoding']) && $headers['Content-Encoding'] != '') {
            if ($headers['Content-Encoding'] == 'deflate' || $headers['Content-Encoding'] == 'gzip') {
                if (function_exists('gzinflate')) {
                    if ($headers['Content-Encoding'] == 'deflate' && $degzdata = @gzinflate($data))
                        $data = $degzdata;
                    elseif ($headers['Content-Encoding'] == 'gzip' && $degzdata = gzinflate(substr($data, 10)))
                        $data = $degzdata;
                    else
                        return false;
                } else {
                    return false;
                }
            }
        }

        if (strlen($data) == 0) return false;

        return $data;
    }

    /**
     * getPingInfo
     *
     * @return bool|mixed|string
     */
    function getPingInfo()
    {
        $domain = "secure.nuguya.com";
        $port = 80;
        $url = "/nuguya/rlnmPing.do";

        $reqest = "";
        $reqest .= "GET " . $url . " HTTP/1.1\r\n";
        $reqest .= "Host: " . $domain . ":" . $port . "\r\n";
        $reqest .= "Content-Type: text/xml; charset=euc-kr\r\n";
        $reqest .= "Connection: close\r\n";
        $reqest .= "\r\n";

        $pingInfo = "";
        $sock = null;

        $sock = @fsockopen($domain, $port, $errno, $errstr, 10);
        if (!$sock) return false;

        fwrite($sock, $reqest);

        // Get Response Data
        $data = "";
        $respData = "";
        while (!feof($sock)) {
            $data = fgets($sock, 32768);
            if ($data == "0\r\n") break;
            $respData .= $data;
        }

        fclose($sock);

        if ($respData == '') {
            $pingInfo = $respData;
        } else {
            $pingInfo = preg_replace("/[\r\n]/", "", $this->resolveResponseData($respData));
            if ($pingInfo == false)
                $pingInfo = "";
        }

        return $pingInfo;
    }
}
