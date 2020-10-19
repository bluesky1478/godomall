<div id="ftp_form">
    <div class="center" style="font-weight: bold; font-size: 14px; margin-bottom: 10px;">고도의 이미지호스팅 신청고객은 고객님의 FTP 계정정보를 입력하세요.</div>
    <form id="frm_layer_goods_image_hosting">
        <table class="table-cols-none" style="width: 80%; margin: auto;">
            <colgroup><col style="width:100px;" /><col /></colgroup>
            <tr style="height: 30px;">
                <td style="text-align: right; font-weight: bold; color: #7b7b7b; padding-right: 5px;">FTP 주소</td>
                <td>
                    <select name="ftp_host" class="form-control" style="font-weight: bold;">
                        <option value="ftp://ftp..godohosting.com">ftp://ftp..godohosting.com</option>
                        <option value="ftp://ftp..hgodo.com">ftp://.hgodo.com</option>
                    </select>
                </td>
            </tr>
            <tr style="height: 30px;">
                <td style="text-align: right; font-weight: bold; color: #7b7b7b; padding-right: 5px;">FTP ID</td>
                <td>
                    <input type="text" name="ftp_id" maxlength="40" class="form-control" style="border-top: 0px; border-left: 0px; border-right: 0px; border-bottom-style: dashed" />
                </td>
            </tr>
            <tr style="height: 30px;">
                <td style="text-align: right; font-weight: bold; color: #7b7b7b; padding-right: 5px;">FTP Password</td>
                <td>
                    <input type="password" name="ftp_pass" maxlength="40" class="form-control" style="border-top: 0px; border-left: 0px; border-right: 0px; border-bottom-style: dashed" />
                </td>
            </tr>
        </table>
    </form>

    <div style="margin-top:10px;" class="notice-info">
        FTP 주소는 FTP ID를 입력하면 자동으로 구성됩니다.<br/>
        hgodo.com의 ftp 접속은 보안 정책에 따라 30일 이내에 접속허용 연장을 진행해주셔야 됩니다.<br/>
        연장진행이 되지 않는경우 ftp 접속은 차단되며, 마이고도-> 기본관리에서 연장 및 잠금 해제가 가능합니다.
    </div>
    <div class="text-center" style="margin-top:10px;">
        <span><input type="button" value="취소" class="btn btn-lg btn-white js-close" /></span>
        <span><input type="button" value="확인" class="btn btn-lg btn-black js-action" /></span>
    </div>
</div>
<div id="loading" style="display:none">
    <div id="loading_image" style="width:128px; margin:10px auto;">
        <img src="/admin/gd_share/img/mobileapp/common/page-loader-bk.gif" style="algin:center;">
    </div>
    <div id="loading_text" style="text-align:center; margin:10px auto;">
        <span>처리중입니다. 잠시만 기다려주세요.</span>
    </div>
    <div id="loading_text_complete" style="text-align:center; margin:10px auto; display:none;">
        <span id="loading_text_result">처리 완료되었습니다.</span><br/><br/>
        <span><input type="button" value="확인" class="btn btn-lg btn-black js-close-result" /></span>
    </div>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function(){
        $('[name="ftp_id"]').keyup(function(){
            var sId = $(this).val();
            sId = sId.replace(/ /g, '');
            $(this).val(sId);

            $('select[name="ftp_host"] option:eq(0)').val('ftp://ftp.' + sId + '.godohosting.com');
            $('select[name="ftp_host"] option:eq(0)').text('ftp://ftp.' + sId + '.godohosting.com');
            $('select[name="ftp_host"] option:eq(1)').val('ftp://' + sId + '.hgodo.com');
            $('select[name="ftp_host"] option:eq(1)').text('ftp://' + sId + '.hgodo.com');
        });

        $('.js-close').click(function(e){
            $('div.bootstrap-dialog-close-button').click();
        });

        $('.js-close-result').click(function(e){
            location.reload();
        });

        $('.js-action').click(function(e){
            if ($('[name="ftp_id"]').val() == '') {
                alert('FTP ID를 입력해주세요.');
                return false;
            }

            if ($('[name="ftp_pass"]').val() == '') {
                alert('FTP Password를 입력해주세요.');
                return false;
            }

            var ftpHostIndex = $('select[name=ftp_host]').prop('selectedIndex');
            var httpUrl = '';
            var ftpHost = '';
            if (ftpHostIndex == 0) {
                httpUrl = $('[name="ftp_id"]').val() + '.godohosting.com';
                ftpHost = 'ftp.' + $('[name="ftp_id"]').val() + '.godohosting.com';
            } else {
                httpUrl = $('[name="ftp_id"]').val() + '.hgodo.com';
                ftpHost = $('[name="ftp_id"]').val() + '.hgodo.com';
            }

            var data = {
                'mode' : 'ftpVerify',
                'httpUrl' : httpUrl,
                'ftpPath' : '',
                'ftpHost' : ftpHost,
                'ftpId' : $('[name="ftp_id"]').val(),
                'ftpPw' : $('[name="ftp_pass"]').val(),
                'oldFtpPw' : '',
                'ftpPort' : 21,
                'savePath' : '',
                'ftpType' : 'ftp',
                'passive' : 'n',
            };

            var errorMsg = "<b>저장소 정보가 유효하지 않습니다.</b> \n";
            errorMsg += "\n• FTP 정보를 다시 한번 확인해 주세요.";
            errorMsg += "\n• 계정용량이나 data 폴더의 권한을 확인해주세요.";
            errorMsg += "\n• 'FTP 저장 경로'의 권한은 707 이나 777 이여야 합니다.";
            errorMsg += "\n• SSH는 접속제한/접속불가 될 수 있습니다.";

            $.ajax({
                method: 'POST',
                url: './goods_imghost_ps.php',
                data: data,
            }).success(function (result) {
                if (result.result == 'ok') {
                    // ftp정보 폼 히든처리후 로딩처리
                    $('#ftp_form').css('display', 'none');
                    $('#loading').css('display', '');

                    var goodsNo = [];
                    var totalCount = 0;
                    $('input[name*="goodsNo"]:checked').each(function() {
                        goodsNo.push($(this).val());
                        totalCount += 1;
                    });

                    var data2 = {
                        'mode' : 'putReplace',
                        'httpUrl' : httpUrl,
                        'ftpPath' : '',
                        'ftpHost' : ftpHost,
                        'ftpId' : $('[name="ftp_id"]').val(),
                        'ftpPw' : $('[name="ftp_pass"]').val(),
                        'oldFtpPw' : '',
                        'ftpPort' : 21,
                        'savePath' : '',
                        'ftpType' : 'ftp',
                        'passive' : 'n',
                        'goodsNo' : goodsNo,
                    };

                    $.ajax({
                        method: 'POST',
                        url: './goods_imghost_ps.php',
                        data: data2,
                    }).success(function (result2) {
                        if (result2.result == 'ok') {
                            $('#loading_image').css('display', 'none');
                            $('#loading_text').css('display', 'none');
                            $('#loading_text_complete').css('display', '');
                            $('#loading_text_result').html('총 ' + totalCount + ' 건중 ' + result2.resultCount + ' 건이 처리되었습니다.');
                        }
                    }).error(function (e2) {
                        alert(result2.errMsg);
                        location.reload();
                    });
                } else {
                    // 로딩창 없애고 다시 ftp정보 폼 노출
                    $('#ftp_form').css('display', '');
                    $('#loading').css('display', 'none');
                    alert(errorMsg);
                }
            }).error(function (e) {
                alert('ftp에 연결이 되지않습니다. ftp정보를 다시 확인해주세요.');
            });
        });
    });
    //-->
</script>
