<form id="frmUnstoringRegister" name="frmUnstoringRegister" action="layer_unstoring_ps.php" method="post">
    <input type="hidden" name="mode" value="<?= $data['mode'] ?>"/>
    <input type="hidden" name="title" value="<?= $title ?>"/>
    <input type="hidden" name="sno" value="<?=$data['sno']; ?>"/>
    <input type="hidden" name="addressFl" value="<?= $type ?>"/>
    <input type="hidden" name="mallFl" value="<?= $mallFl ?>"/>
    <div class="table-title gd-help-manual" style="padding-bottom: 5px;">
        <?= $title ?> 등록
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
        </colgroup>
        <tr>
            <th class="input_title r_space require">관리 명칭</th>
            <td>
                <input type="text" name="unstoringNm" value="<?=$data['unstoringNm']; ?>" class="form-control width-2xl js-maxlength" maxlength="30"/>
            </td>
        </tr>
        <tr>
            <th class="input_title r_space require"><?= $title ?> 주소</th>
            <td>
                <div id="unstoringFl_new">
                    <div class="form-inline mgt10 mgb5">
                        <input type="text" name="unstoringZonecode" value="<?php echo $data['unstoringZonecode']; ?>" maxlength="5" class="form-control width-2xs"/>
                        <input type="hidden" name="unstoringZipcode" value="<?php echo $data['unstoringZipcode']; ?>"/>
                        <span id="unstoringZipcodeText" class="number <?php if (strlen($data['unstoringZipcode']) != 7) { echo 'display-none'; } ?>">(<?php echo $data['unstoringZipcode']; ?>)</span>
                        <input type="button" onclick="postcode_search('unstoringZonecode', 'unstoringAddress', 'unstoringZipcode');" value="우편번호찾기" class="btn btn-gray btn-sm"/>
                        <input type="checkbox" name="postFl" value="<?php echo $data['postFl']; ?>"/>우편번호 생략
                    </div>
                    <div class="form-inline">
                        <input type="text" name="unstoringAddress" value="<?php echo $data['unstoringAddress']; ?>" class="form-control width-xl"/>
                        <input type="text" name="unstoringAddressSub" value="<?php echo $data['unstoringAddressSub']; ?>" class="form-control width-xl"/>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>기본 연락처</th>
            <td>
                <input type="text" name="mainContact" value="<?=$data['mainContact']; ?>" class="form-control width-2xl js-maxlength" maxlength="30"/>
            </td>
        </tr>
        <tr>
            <th>추가 연락처</th>
            <td>
                <input type="text" name="additionalContact" value="<?=$data['additionalContact']; ?>" class="form-control width-2xl js-maxlength" maxlength="30"/>
            </td>
        </tr>
    </table>
    <input type="checkbox" name="mainFl" value="<?php echo $data['mainFl'] ?>"> <?= $mallName ?> 기본 <?= $title ?>로 설정합니다.
    <div class="center">
        <input type="button" value="취소" id="unstoringChoiceCancel" class="btn btn-lg btn-white" style="font-weight: bold;margin-right: 10px"/>
        <input type="button" value="저장" id="unstoringChoiceConfirm" class="btn btn-lg btn-black"/>
    </div>
</form>
<script type="text/javascript">

    var title = '<?= $title ?>';
    var $mallFl = '<?= $mallFl ?>';
    var checkedNo = '<?= $checkedNo ?>';
    //var checkedList = JSON.parse('<?//= $checkedList ?>//');
    var page = '<?= $page ?>';

    $(document).ready(function() {

        $('input[maxlength]').maxlength({
            showOnReady: true,
            alwaysShow: true
        });

        setTimeout(function () {
            $('#frmUnstoringRegister').find('input[maxlength]').next('span.bootstrap-maxlength').css({
                top: '12px',
                left: '420px'
            });
        }, 100);

        initCheckbox();

        var params = $('#frmUnstoringRegister');
        $("input[name='postFl']").on('click', function () {
            if ($(this).is(':checked')) {
                $(this).val('y');
                $('input[name=unstoringZonecode]').prop('disabled', true);
            } else {
                $(this).val('n');
                $('input[name=unstoringZonecode]').prop('disabled', false);
            }
        })

        $("input[name='mainFl']").on('click', function () {
            ($(this).is(':checked')) ? $(this).val($mallFl) : $(this).val('n');
        })

        $('#unstoringChoiceCancel').on('click', function () {
            var dialogs = BootstrapDialog.dialogs;
            var i = 0;
            for (var index in dialogs) {
                var dialog = dialogs[index];
                if (i == 1 && dialog.isOpened()) {
                    dialog.close();
                    return false;
                }
                i++;
            }
        });

        $('#unstoringChoiceConfirm').on('click', function () {

            /*if ($('input[name=\'unstoringNm\']').val() == '') {
                alert('관리 명칭을 입력하세요.');
                return false;
            }
            /*if ($('input[name=\'unstoringAddress\']').val() == '') {
                alert(title + ' 주소를 입력하세요.');
                return false;
            }

            if ($('input[name=postFl]').val() != 'y') {
                if ($('input[name=\'unstoringZonecode\']').val() == '') {
                    alert('우편번호를 입력하세요.');
                    return false;
                }
            }*/
            var unstoringInfo = params.serializeArray();

            setPostFl(unstoringInfo);
            setMainFl(unstoringInfo);

            $.ajax({
                type: "POST",
                url: '../share/layer_unstoring_ps.php',
                data: unstoringInfo
            }).success(function (data) {
                if (data['result'] != 'fail') {
                    var loadChk = $('#layerUnstoringListForm').length;
                    $.ajax({
                        type: 'GET',
                        url: '../share/layer_unstoring_list.php',
                        data: {
                            subTitle: title,
                            mallFl: mallFl,
                            unstoringNo: checkedNo,
                            page: page,
                        }
                    }).success(function(data){
                            if (loadChk == 0) {
                                data = '<div id="registeredAddList">' + data + '</div>';
                            }
                            var originAddressData = $(data).find('#addressList');
                            var newDataList = $(originAddressData).find('input[name=\'chk\']');

                            for (var j in newDataList) {
                                if (newDataList[j].value == undefined)  break;
                                $(originAddressData).find('#address_' + newDataList[j].value).prop('checked', false);
                            }

                            for(var i in checkedRowData) {
                                for(var j in newDataList) {
                                    if (newDataList[j].value == undefined)  break;
                                    if (checkedRowData[i].sno == newDataList[j].value) {
                                        $(originAddressData).find('#address_' + newDataList[j].value).prop('checked', true);
                                        break;
                                    }
                                }
                            }
                            $('#addressList').replaceWith(originAddressData);
                            updateCheckedInfo(unstoringInfo);
                    }).error(function (data) {
                        alert(data.message);
                    });
                    current_layer_close();
                } else {
                    alert(data['msg']);
                    return false;
                }
            }).error(function (data) {
                alert(data.message);
            });
        })
    })

    // 수정된 체크되어있는 주소 정보 업데이트
    function updateCheckedInfo(unstoringInfo) {
        var unstoringNm = '';
        var updatedPostFl = '';
        var updatedMainFl = '';
        var unstoringZonecode = '';
        var unstoringZipcode = '';
        var unstoringAddress = '';
        var unstoringAddressSub = '';
        var mainContact = '';
        var additionalContact = '';

        for (var i in unstoringInfo) {
            if(unstoringInfo[i].name == 'unstoringNm')   unstoringNm = unstoringInfo[i].value;
            if(unstoringInfo[i].name == 'postFl')   updatedPostFl = unstoringInfo[i].value;
            if(unstoringInfo[i].name == 'mainFl')   updatedMainFl = unstoringInfo[i].value;
            if(unstoringInfo[i].name == 'unstoringZonecode')   unstoringZonecode = unstoringInfo[i].value;
            if(unstoringInfo[i].name == 'unstoringZipcode')   unstoringZipcode = unstoringInfo[i].value;
            if(unstoringInfo[i].name == 'unstoringAddress')   unstoringAddress = unstoringInfo[i].value;
            if(unstoringInfo[i].name == 'unstoringAddressSub')   unstoringAddressSub = unstoringInfo[i].value;
            if(unstoringInfo[i].name == 'mainContact')   mainContact = unstoringInfo[i].value;
            if(unstoringInfo[i].name == 'additionalContact')   additionalContact = unstoringInfo[i].value;
        }

        for ( var i in checkedRowData ){
            if (updatedMainFl != 'n') {
                checkedRowData[i].mainFl = 'n';
            }
            if (checkedRowData[i].sno == '<?=$data['sno']; ?>') {
                checkedRowData[i].unstoringNm = unstoringNm;
                checkedRowData[i].unstoringZonecode = unstoringZonecode;
                checkedRowData[i].unstoringZipcode = unstoringZipcode;
                checkedRowData[i].unstoringAddress = unstoringAddress;
                checkedRowData[i].unstoringAddressSub = unstoringAddressSub;
                checkedRowData[i].mainContact = mainContact;
                checkedRowData[i].additionalContact = additionalContact;
                checkedRowData[i].mainFl = updatedMainFl;
                checkedRowData[i].postFl = updatedPostFl;
            }
        }
    }

    function setPostFl(unstoringInfo) {
        var postFl = $('input[name=postFl]').val();
        if (postFl == '') {
            unstoringInfo.push({name: 'postFl', value: 'n'});
        }
    }

    function setMainFl(unstoringInfo) {
        var mainFl = $('input[name=mainFl]').val();
        if (mainFl == '') {
            unstoringInfo.push({name: 'mainFl', value: 'n'});
        } else {
            unstoringInfo.push({name: 'mainFl', value: mainFl});
        }
    }

    function initCheckbox() {
        if($("input[name='mainFl']").val() != '' && $("input[name='mainFl']").val() != 'n') {
            $("input[name='mainFl']").prop('checked', true);
            $("input[name='mainFl']").prop('disabled', true);
        }
        if ('<?= $mallFl ?>' != 'kr') {
            $("input[name='postFl']").val('y');
        }
        if($("input[name='postFl']").val() == 'y') {
            $("input[name='postFl']").prop('checked', true);
            $('input[name=unstoringZonecode]').prop('disabled', true);
        }
    }

    function current_layer_close(){
        var dialogs = BootstrapDialog.dialogs;
        var i = 0;
        for (var index in dialogs) {
            var dialog = dialogs[index];
            if (i == 1 && dialog.isOpened()) {
                dialog.close();
                return false;
            }
            i++;
        }
    }

</script>

<script type="text/html" id="registeredAddTemplate">
    <tr class="text-center"
        data-unstoringno="<?= $val['unstoringNo']; ?>"
        data-mainfl="<?= $val['mainFl'] ?>" <?php if ($val['checkedNo'] == true) { ?>style="background: #E6E6E6" <?php } ?>>
        <td>
            <input type="checkbox" name="chk" value="<?= $val['unstoringNo']; ?>"
                   data-unstoringnm="<?= $val['unstoringNm'] ?>"
                   data-zipcode="<?= $val['unstoringZipcode'] ?>"
                   data-zonecode="<?= $val['unstoringZonecode'] ?>"
                   data-postfl="<?= $val['postFl'] ?>"
                   data-unstoringadd="<?= $val['unstoringAddress'] ?>"
                   data-unstoringaddsub="<?= $val['unstoringAddressSub'] ?>"
                   data-maincont="<?= $val['mainContact'] ?>"
                   data-additionalcont="<?= $val['additionalContact'] ?>"
                   data-mainfl="<?= $val['mainFl'] ?>"
                   data-addressfl="<?= $val['addressFl'] ?>"/>
        </td>
        <td>
            <?php if ($val['mainFl'] == 'y') { ?>
                <span>
        (기본 <?= $title ?>)
        </span></br>
            <?php } ?>
            <span><?= $val['unstoringNm'] ?></span>
        </td>
        <td>
    <span>
    <?php if ($val['postFl'] != 'y') { ?>
        (<?= $val['unstoringZonecode'] ?>)<?php } ?> <?= $val['unstoringAddress'] ?> <?= $val['unstoringAddressSub'] ?>
    </span>
        </td>
        <td>
            <span>기본 : <?= $val['mainContact'] ?></span></br>
            <span>추가 : <?= $val['additionalContact'] ?></span>
        </td>
        <td>
            <button type="button" class="btn btn-white btn-sm btnModify">수정</button></br>
            <button type="button" class="btn btn-white btn-sm btnDelete">삭제</button>
        </td>
    </tr>
</script>
