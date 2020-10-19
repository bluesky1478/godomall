<script type="text/javascript">
    <!--
    $(document).ready(function () {
        var storageDefault = (function () {
            return $(':radio[name="storageDefault[goods]"]:checked');
        })();

        $('body').on('click', ':radio[name="storageDefault[goods]"]', function () {
            var radio = this;
            if (storageDefault.length > 0) {
                var msg = '이미 기본 저장소로 설정된 정보가 있습니다. 기본 저장소를 변경하시겠습니까?';
                dialog_confirm(msg, function (result) {
                    if (result) {
                        storageDefault = $(radio);
                    } else {
                        $(storageDefault).prop('checked', true);
                    }
                    $(radio).prop('checked', result);
                });
            } else {
                storageDefault = $(radio);
            }
        });

        $('body').on('click', '.js-ftp-type', function () {
            var target = $(this).closest('table').find('.js-ftp-passive');
            target.prop('disabled', $(this).val() == 'sftp');
        })


        $('#btnSave').bind('click', function () {
            var isValid = true;
            $('#imageStorage input[type=text]').each(function (i) {
                var no = $(this).closest('table').data('no');
                var caption = $(this).attr('caption');

                if ($(this).hasClass('js-ftpPath-key') == false && $(this).hasClass('js-savePathText-key') == false) {
                    if ($(this).val() == '') {
                        alert(no + "번째 추가저장소 " + caption + "를 입력해주세요.");
                        $(this).focus();
                        isValid = false;
                        return false;
                    }
                }

                var eleName = $(this).attr('name');
                if (eleName.indexOf('storageName') > -1) {
                    if ($(this).val().length > 20) {
                        alert('저장소명은 20자 이하로 입력해주세요.');
                        isValid = false;
                        return false;
                    }
                }
            })

            if (!isValid) {
                return;
            }

            //alert('파일저장소가 삭제되었습니다. 정상적으로 보이지 않는 상품이미지가 있는지 확인하시기 바랍니다.');
            $('#frmStorage').attr('target', 'ifrmProcess');
            $('#frmStorage').submit();
        });

        <?php
        $storageID = 'imageStorage';
        $storageCnt = count($data['storageName']);
        for ($i = 1; $i < $storageCnt; $i++) {
            echo '	storage_add(\'y\');' . chr(10);
        }
        echo '	fill_storage();' . chr(10);
        ?>
        $('input[name*=\'ftpPort\']').number_only(8);

        $('body').on('keyup', '.js-ftpPath-key', function () {
            var tbl = $(this).closest('table');
            tbl.find('.savePathText').html($(this).val() + "/");
        })

        $('body').on('keyup', '.js-savePathText-key', function () {
            var tbl = $(this).closest('table');
            tbl.find('.httpUrlText').html('/' + $(this).val());
        })

        $('input:radio[name*=ftpType]:checked').trigger('click');

        function fill_storage() {
            var storageDefaultData;
            <?php
            echo 'storageDefaultData=' . json_encode($data['storageDefault']) . ';';
            for ($i = 1; $i < $storageCnt; $i++) {

                if (!$data['passive'][$storageID . $i]) {
                    $data['passive'][$storageID . $i] = 'y';
                }

                echo "$('#" . $storageID . $i . "').find('.savePathText').html('" . $data['ftpPath'][$storageID . $i] . "/');" . chr(10);
                echo "$('#" . $storageID . $i . "').find('.httpUrlText').html('/" . $data['savePath'][$storageID . $i] . "');" . chr(10);
                echo "	$('#frmStorage input[name=\'ftpType[" . $i . "]\'][value=\'" . $data['ftpType'][$storageID . $i] . "\']').prop('checked',true);" . chr(10);
                echo "	$('#frmStorage input[name=\'storageName[" . $i . "]\']').val('" . gd_htmlspecialchars_slashes($data['storageName'][$storageID . $i], 'add') . "');" . chr(10);
                echo "	$('#frmStorage input[name=\'httpUrl[" . $i . "]\']').val('" . gd_htmlspecialchars_slashes($data['httpUrl'][$storageID . $i], 'add') . "');" . chr(10);
                echo "	$('#frmStorage input[name=\'ftpPath[" . $i . "]\']').val('" . gd_htmlspecialchars_slashes($data['ftpPath'][$storageID . $i], 'add') . "');" . chr(10);
                echo "	$('#frmStorage input[name=\'ftpHost[" . $i . "]\']').val('" . gd_htmlspecialchars_slashes($data['ftpHost'][$storageID . $i], 'add') . "');" . chr(10);
                echo "	$('#frmStorage input[name=\'savePath[" . $i . "]\']').val('" . gd_htmlspecialchars_slashes($data['savePath'][$storageID . $i], 'add') . "');" . chr(10);
                echo "	$('#frmStorage input[name=\'ftpId[" . $i . "]\']').val('" . gd_htmlspecialchars_slashes($data['ftpId'][$storageID . $i], 'add') . "');" . chr(10);
                echo "	$('#frmStorage input[name=\'ftpPw[" . $i . "]\']').val('******');" . chr(10);
                echo "	$('#frmStorage input[name=\'ftpPwChk[" . $i . "]\']').val('" . $data['ftpPw'][$storageID . $i] . "');" . chr(10);
                echo "	$('#frmStorage input[name=\'ftpPort[" . $i . "]\']').val('" . gd_htmlspecialchars_slashes($data['ftpPort'][$storageID . $i], 'add') . "');" . chr(10);
                echo "	$('#frmStorage input[name=\'passive[" . $i . "]\'][value=\'" . $data['passive'][$storageID . $i] . "\']').prop('checked',true);" . chr(10);
            }
            ?>
            if (typeof storageDefaultData === 'object') {
                for (var storage in storageDefaultData) {
                    if (storageDefaultData.hasOwnProperty(storage)) {
                        if (storageDefaultData[storage].indexOf('goods') > -1) {
                            var find = $('tr#' + storage).find(':radio[name="storageDefault[goods]"]');
                            find.prop('checked', true);
                            storageDefault = find;
                        }
                    }
                }
            }
        }
    });

    function storage_add(delType) {
        var fieldID = '<?=$storageID;?>';
        var fieldNoChk = $('tr[id*=\'' + fieldID + '\']').length;
        if (fieldNoChk == '') {
            var fieldNoChk = 0;
        }
        var fieldNo = parseInt(fieldNoChk) + 1;
        var fieldNum = parseInt(fieldNo) - 1;
        if (fieldNum >= <?=DEFAULT_LIMIT_STORAGE;?>) {
            alert('파일 저장소는 최대 <?=DEFAULT_LIMIT_STORAGE;?>개까지 추가할 수 있습니다.');
            return false;
        }
        var addHtml = '';
        addHtml += '<tr id="' + fieldID + fieldNo + '">';
        addHtml += '<th>추가 저장소' + (fieldNum + 1) + '<div class="mgt5"><input type="button" value="삭제" class="btn btn-white btn-icon-minus  btn-sm" style="margin-left:10px" onclick="info_remove(\'' + fieldID + fieldNo + '\',\'' + delType + '\')" /></div></th>';
        addHtml += '<td>';
        addHtml += '<table class="table table-cols mgt20" data-no="' + fieldNo + '">';
        addHtml += '<colgroup><col class="width-md" /><col class="width-2xl"/><col class="width-md" /><col/></colgroup>';
        addHtml += '<tr>';
        addHtml += '<th>저장소명</th>';
        addHtml += '<td class="form-inline">';
        addHtml += '<input type="text" name="storageName[' + fieldNo + ']" class="form-control width-xl js-maxlength" maxlength="20"  caption="저장소명"  />';
        addHtml += '<input type="button" class="btn btn-black btn-sm" value="저장소 체크" onclick="storage_checker(this,\'' + fieldNo + '\');" style="margin-left:10px">';
        addHtml += '</td>';
        addHtml += '<th>FTP TYPE</th>';
        addHtml += '<td >';
        addHtml += '<label class="radio-inline"><input type="radio" name="ftpType[' + fieldNo + ']" value="ftp" caption="FTP TYPE"  class="js-ftp-type" />FTP</label>';
        addHtml += '<label class="radio-inline"><input type="radio" name="ftpType[' + fieldNo + ']" value="sftp" caption="FTP TYPE" class="js-ftp-type" />SFTP</label>';
        addHtml += '</td>';

        addHtml += '</tr>';
        addHtml += '<tr>';
        addHtml += '<th>FTP WEB ROOT 경로</th>';
        addHtml += '<td><input type="text" name="ftpPath[' + fieldNo + ']" class="form-control width-xl js-ftpPath-key" caption="FTP web root 경로" /></td>';
        addHtml += '<th>FTP 저장경로</th>';
        addHtml += '<td class="form-inline"><span class="savePathText"></span><input type="text" caption="FTP 저장경로" name="savePath[' + fieldNo + ']" class="form-control js-savePathText-key" style="width:250px" /></td>';
        addHtml += '</tr>';
        addHtml += '<tr>';
        addHtml += '<th>HTTP 경로</th>';
        addHtml += '<td colspan=3 class="form-inline"><input type="text" caption="HTTP 경로" name="httpUrl[' + fieldNo + ']" class="form-control width-xl" /><span class="httpUrlText"></span></td>';
        addHtml += '</tr>';
        addHtml += '<tr>';
        addHtml += '<th>FTP HOST</th>';
        addHtml += '<td colspan=3><input type="text" caption="FTP HOST" name="ftpHost[' + fieldNo + ']" class="form-control width90p" /></td>';
        addHtml += '<tr>';
        addHtml += '<th>FTP ID</th>';
        addHtml += '<td><input type="text" name="ftpId[' + fieldNo + ']" caption="FTP ID" class="form-control width-sm" /></td>';
        addHtml += '<th>FTP PASSWORD</th>';
        addHtml += '<td><input type="password" name="ftpPw[' + fieldNo + ']" caption="FTP 패스워드" class="form-control width-sm" /><input type="hidden" name="ftpPwChk[' + fieldNo + ']" value="" /></td>';
        addHtml += '</tr>';
        addHtml += '<tr>';
        addHtml += '<th>FTP Port</th>';
        addHtml += '<td ><input type="text" name="ftpPort[' + fieldNo + ']" caption="FTP Port" class=" form-control width-sm" /></td>';
        addHtml += '<th>Passive Mode</th>';
        addHtml += '<td >';
        addHtml += '<label class="radio-inline"><input type="radio" name="passive[' + fieldNo + ']" value="y" caption="PASSIVE MODE" class="js-ftp-passive"  />사용</label>';
        addHtml += '<label class="radio-inline"><input type="radio" name="passive[' + fieldNo + ']" value="n" caption="PASSIVE MODE" class="js-ftp-passive" />사용안함</label>';
        addHtml += '</td>';
        addHtml += '</tr>';
        addHtml += '</table>';
        addHtml += '<label class="radio-inline"><input type="radio" name="storageDefault[goods]" value="' + fieldNo + '">상품 등록 시 기본 저장소로 노출되도록 설정합니다.</label>';
        addHtml += '</td>';
        addHtml += '</tr>';
        $('#' + fieldID).append(addHtml);

        $('input[name*=\'ftpPort\']').number_only(8);
    }

    /**
     * 저장소 체커
     *
     * @param string formNo 번호 (무조건 0)
     * @param string divID 해당 ID
     */
    function storage_checker(obj, fieldNo) {
        var httpUrl = $('#frmStorage input[name=\'httpUrl[' + fieldNo + ']\']').val();
        var ftpPath = $('#frmStorage input[name=\'ftpPath[' + fieldNo + ']\']').val();
        var ftpHost = $('#frmStorage input[name=\'ftpHost[' + fieldNo + ']\']').val();
        var ftpId = $('#frmStorage input[name=\'ftpId[' + fieldNo + ']\']').val();
        var ftpPw = $('#frmStorage input[name=\'ftpPw[' + fieldNo + ']\']').val();
        var oldFtpPw = $('#frmStorage input[name=\'ftpPwChk[' + fieldNo + ']\']').val();
        var ftpPort = $('#frmStorage input[name=\'ftpPort[' + fieldNo + ']\']').val();
        var ftpType = $('#frmStorage input[name=\'ftpType[' + fieldNo + ']\']:checked').val();
        var passive = $('#frmStorage input[name=\'passive[' + fieldNo + ']\']:checked').val();
        if (httpUrl == '') {
            alert('HTTP 경로를 입력해 주세요!');
            return false;
        } else {
            $('#frmStorageChecker input[name=\'httpUrl\']').val(httpUrl);
        }

        if (ftpPath == '') {
            //            alert('FTP WEB ROOT 경로를 입력해 주세요!');
            //            return false;
        } else {
            $('#frmStorageChecker input[name=\'ftpPath\']').val(ftpPath);
        }

        if (ftpHost == '') {
            alert('FTP HOST를 입력해 주세요!');
            return false;
        } else {
            $('#frmStorageChecker input[name=\'ftpHost\']').val(ftpHost);
        }

        if (ftpId == '') {
            alert('FTP ID를 입력해 주세요!');
            return false;
        } else {
            $('#frmStorageChecker input[name=\'ftpId\']').val(ftpId);
        }

        if (ftpPw == '') {
            alert('FTP 패스워드를 입력해 주세요!');
            return false;
        } else {
            $('#frmStorageChecker input[name=\'ftpPw\']').val(ftpPw);
            $('#frmStorageChecker input[name=\'oldFtpPw\']').val(oldFtpPw);
        }


        if (ftpPort == '') {
            alert('FTP Port를 입력해 주세요!');
            return false;
        } else {
            $('#frmStorageChecker input[name=\'ftpPort\']').val(ftpPort);
        }

        if (ftpType == '') {
            alert('FTP TYPE을 선택해 주세요!');
            return false;
        } else {
            $('#frmStorageChecker input[name=\'ftpType\']').val(ftpType);
        }

        if (passive == '') {
            alert('passive를 선택해 주세요!');
            return false;
        } else {
            $('#frmStorageChecker input[name=\'passive\']').val(passive);
        }

        //alert('처리중');

        $(obj).prop('disabled', true);
        var objVal = $(obj).val();
        $(obj).val('처리중...');

        var errorMsg = '<b>저장소 정보가 유효하지 않습니다.</b> \n';
        errorMsg += '\n• FTP 정보를 다시 한번 확인해 주세요. ';
        errorMsg += '\n• 계정용량이나 data 폴더의 권한을 확인해주세요. ';
        errorMsg += '\n• FTP 저장 경로"의 권한은 707 이나 777 이여야 합니다. ';
        errorMsg += '\n• SSH는 접속제한/접속불가 될 수 있습니다. ';
        $.ajax({
            method: "POST",
            url: "./base_ps.php",
            data: $('#frmStorageChecker').serialize(),
            dataType: 'json',
            cache: false,
            //async: true,
        }).success(function (data) {
            if (data['result'] == 'ok') {
                alert('저장소 체크가 완료되었습니다.');
            }
            else {
                alert(errorMsg);
            }
            $(obj).prop('disabled', false);
            $(obj).val(objVal);
        }).error(function (e) {
            alert(errorMsg);
            $(obj).prop('disabled', false);
            $(obj).val(objVal);
        });


        //formName.submit();
    }

    /**
     * 정보 삭제
     *
     * @param string thisID 해당 ID
     * @param boolean setYN 처리 여부
     */
    function info_remove(thisID, setYN) {
        if (setYN == 'y') {
            var msg = "파일저장소 삭제 시 기존 등록된 이미지가 보이지 않으므로, 가능한 파일저장소를 삭제하지 않는 것을 권장합니다. 파일저장소를 정말로 삭제하시겠습니까?";
            dialog_confirm(msg, function (result) {
                if (result) {
                    $('#' + thisID).remove();
                }
            })
        } else {
            $('#' + thisID).remove();
        }
    }
    //-->
</script>
<form id="frmStorage" name="frmStorage" action="./base_ps.php" method="post" class="content-form">
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
        </h3>
        <input type="button" id="btnSave" value="저장" class="btn btn-red"/>
    </div>


    <input type="hidden" name="mode" value="file_storage">

    <div class="table-title gd-help-manual">
        <?php echo end($naviMenu->location); ?>
    </div>
    <table id="<?php echo $storageID; ?>" class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>로컬 저장소
            </th>
            <td>
                <table class="table table-cols mgt20">
                    <colgroup>
                        <col class="width-xs"/>
                        <col/>
                    </colgroup>
                    <tr id="">
                        <th>기본 경로</th>
                        <td class="bold">
                            <?php echo UserFilePath::data()->www(); ?> ※ 기본적인 모든 이미지 및 화일 경로 (상품,게시판,스킨)
                            <input type="hidden" name="storageName[0]" value="기본 경로"/>
                            <input type="hidden" name="httpUrl[0]" value="local"/>
                            <input type="hidden" name="ftpPath[0]" value=""/>
                            <input type="hidden" name="ftpHost[0]" value="local"/>
                            <input type="hidden" name="ftpId[0]" value=""/>
                            <input type="hidden" name="savePath[0]" value=""/>
                            <input type="hidden" name="ftpPw[0]" value=""/>
                            <input type="hidden" name="ftpPort[0]" value=""/>
                            <input type="hidden" name="ftpType[0]" value=""/>
                            <input type="hidden" name="passive[0]" value=""/>
                        </td>
                    </tr>
                </table>
                <label class="radio-inline">
                    <input type="radio" name="storageDefault[goods]" value="0" <?= in_array('goods', $data['storageDefault']['imageStorage0']) ? 'checked="checked"' : '' ?>>
                    상품 등록 시 기본 저장소로 노출되도록 설정합니다.
                </label>
            </td>
        </tr>
        <tr>
            <th>
                파일저장소 추가
            </th>
            <td>
                <input type="button" class="btn btn-white btn-red-line btn-sm" value="추가" onclick="storage_add();">
                <div class="notice-info">최대 5개까지 파일 저장소를 추가하실 수 있습니다.</div>
            </td>
        </tr>
    </table>
</form>

<div class="display-none">
    <form id="frmStorageChecker" name="frmStorageChecker" action="./base_ps.php" method="post" class="content-form">
        <input type="text" name="mode" value="file_storage_checker">
        <input type="text" name="httpUrl" value="">
        <input type="text" name="ftpPath" value="">
        <input type="text" name="ftpHost" value="">
        <input type="text" name="ftpId" value="">
        <input type="text" name="ftpPw" value="">
        <input type="text" name="oldFtpPw" value="">
        <input type="text" name="ftpPort" value="">
        <input type="text" name="savePath" value="">
        <input type="text" name="ftpType" value="">
        <input type="text" name="passive" value="">
    </form>
</div>
