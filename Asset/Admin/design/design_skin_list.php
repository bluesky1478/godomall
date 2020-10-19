<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?>
        <small>보유중인 스킨을 확인 및 설정 하실 수 있습니다.</small>
    </h3>
</div>

<?php if ($mallCnt > 1) { ?>
    <div class="mgt10 design-notice-box">
        <strong class="text-darkred">해외몰 사용 시에는 글로벌 지원 스킨을 적용하셔야만 정상적인 이용이 가능합니다.</strong>
        <br />기존 무료 스킨 혹은 별도로 구매한 스킨을 해외몰 디자인으로 적용하시려면,
        <br />글로벌 지원 스킨을 참고하여 <b>해외몰 기능 지원 함수나 치환코드 등을 이용</b>하셔야만 합니다.
        <br />(글로벌 지원 스킨은 스킨명 하단에 “글로벌스킨” 아이콘이 노출되며, 무료스킨 추가 팝업에서 확인이 가능합니다.)
    </div>
<?php } ?>

<div class="sub-sector banner-float">
    <p><?php echo $freeSkinBanner;?></p>
</div>

<form id="frmDesignSkinList" name="frmDesignSkinList" action="design_skin_list_ps.php" method="post" class="design-list-width">
    <input type="hidden" name="mode" value="skinChange"/>
    <input type="hidden" name="skinType" value="<?php echo $skinType; ?>"/>
    <input type="hidden" name="skinUse" value=""/>
    <input type="hidden" name="skinUseAllFl" value=""/>

    <!-- 현 사용중인 스킨 & 작업중인 스킨 -->
    <?php
    // 설정된 디자인 스킨 미리보기 경로 수정
    $livePreviewUrl = $skinPreviewUrl . $skinConf['liveInfo']['skin_code'];
    $workPreviewUrl = $skinPreviewUrl . $skinConf['workInfo']['skin_code'];
    if ($mallSno > 1) {
        $livePreviewUrl = $uriHome . $mallList[$mallSno]['domainFl'] . $skinPreviewPage . $skinConf['liveInfo']['skin_code'];
        $workPreviewUrl = $uriHome . $mallList[$mallSno]['domainFl'] . $skinPreviewPage . $skinConf['workInfo']['skin_code'];
    }
    ?>
    <div class="table-title gd-help-manual">
        현재 설정된 디자인 스킨
    </div>

    <?php if ($mallCnt > 1) { ?>
        <ul class="multi-skin-nav nav nav-tabs" style="margin-bottom:20px;">
            <?php foreach ($mallList as $key => $mall) { ?>
                <li role="presentation" class="js-popover <?php echo $mallSno == $mall['sno'] ? 'active' : 'passive'; ?>" data-html="true" data-content="<?php echo $mall['mallName']; ?>" data-placement="top">
                    <a href="./design_skin_list.php?mallSno=<?php echo $mall['sno']; ?>">
                        <?php if ($mall['sno'] == 1) { ?>
                            <img src="<?php echo PATH_ADMIN_GD_SHARE; ?>img/ico_standard.png" class="standard" height="17">
                        <?php } ?>
                        <span class="flag flag-16 flag-<?php echo $mall['domainFl']?>"></span>
                        <span class="mall-name"><?php echo $mall['mallName']; ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <div class="current_skin_box" >
        <dl class="live">
            <dd class="skin_img">
                <div class="ico"><img src="<?php echo PATH_ADMIN_GD_SHARE; ?>img/ico_live.png" alt="사용스킨"></div>
                <img src="<?php echo $skinConf['liveInfo']['skin_cover']; ?>" alt="<?php echo $skinConf['liveInfo']['skin_name']; ?> 스킨"/>
            </dd>
            <dt class="skin_btn">
            <div class="skin_title">사용중인 스킨 <img src="<?php echo PATH_ADMIN_GD_SHARE; ?>img/icon_live.png" width="34" height="18" alt="사용스킨"/></div>
            <div class="skin_text">
                쇼핑몰 방문자에게 보여지는 스킨입니다.<br/>
                선택된 스킨이 실제 쇼핑몰에서 보여집니다.
            </div>
            <div class="skin_name"><?php echo $skinConf['liveInfo']['skin_name']; ?> (<?php echo $skinConf['liveInfo']['skin_code']; ?>) 스킨</div>
            <a href="<?php echo $livePreviewUrl; ?>" class="btn btn-gray btn-sm" target="_blank">미리보기</a>
            <?php if (isset($cacheUrl)) { ?><a href="<?= $cacheUrl ?>" class="btn btn-gray btn-sm">갱신</a><?php } ?>
            </dt>
        </dl>

        <dl class="work">
            <dd class="skin_img">
                <div class="ico" style="left:20px;"><img src="<?php echo PATH_ADMIN_GD_SHARE; ?>img/ico_work.png" alt="작업스킨"></div>
                <img src="<?php echo $skinConf['workInfo']['skin_cover']; ?>" alt="<?php echo $skinConf['workInfo']['skin_name']; ?> 스킨"/>
            </dd>
            <dt class="skin_btn">
            <div class="skin_title">작업중인 스킨 <img src="<?php echo PATH_ADMIN_GD_SHARE; ?>img/icon_work.png" width="40" height="18" alt="작업스킨"/></div>
            <div class="skin_text">
                디자인 작업을 위한 스킨입니다.<br/>
                선택된 스킨으로 디자인을 꾸밀 수 있습니다.
            </div>
            <div class="skin_name"><?php echo $skinConf['workInfo']['skin_name']; ?>(<?php echo $skinConf['workInfo']['skin_code']; ?>) 스킨</div>
            <a href="<?php echo $workPreviewUrl; ?>" class="btn btn-gray btn-sm" target="_blank">미리보기</a>
            </dt>
        </dl>
    </div>
    <!-- //현 사용중인 스킨 & 작업중인 스킨 -->

    <!-- 디자인 스킨 변경 -->
    <div class="table-title gd-help-manual">
        보유 스킨 리스트
    </div>
    <div class="table-header mgb10">
        <div class="pull-left mgb5">
            전체<strong><?php echo number_format($skinCnt);?></strong>개
            <button type="button" class="btn btn-lg btn-red-line js-skin-upload">스킨업로드</button>
            <button type="button" class="btn btn-lg btn-red-line js-skin-popup">무료스킨추가</button>
        </div>
        <div class="pull-right">
        </div>
    </div>

    <table class="table table-rows table-fixed" style="margin-bottom: 8px">
        <colgroup>
            <col class="width5p"/>
            <col class="width20p"/>
            <col class="width15p"/>
            <col class="width15p"/>
            <col class="width15p"/>
            <col class="width10p"/>
            <col/>
        </colgroup>
        <thead>
        <tr>
            <th>선택</th>
            <th colspan="2">디자인 스킨명 (스킨코드)</th>
            <th>상점별<br />사용 스킨</th>
            <th>상점별<br />작업 스킨</th>
            <th>미리보기</th>
            <th>관리</th>
        </tr>
        </thead>
        <tbody class="checkTr">
        <?php
        if (is_array($skinList) === true) {
            foreach ($skinList as $data) {
                if (empty($data) === false && is_array($data) === true) {
                    $languageFl = array_values($useSkin[$data['skin_code']][$skinType . 'WorkLanguageFl'])[0] ?? array_values($useSkin[$data['skin_code']][$skinType . 'LiveLanguageFl'])[0];
                    ?>
                    <tr class="text-center">
                        <td style="white-space:nowrap; height:auto;"><input type="radio" name="<?php echo $skinType;?>Skin" value="<?php echo $data['skin_code']; ?>" data-delfl="<?php echo $useSkin[$data['skin_code']]['delFl']; ?>" /></td>
                        <td><img src="<?php echo $data['skin_cover']; ?>" alt="<?php echo $data['skin_name']; ?> 스킨" style="max-width:150px; "/></td>
                        <td>
                            <?php
                            if (empty($data['skin_name']) === false) {
                                echo $data['skin_name'] . '<br />(' . $data['skin_code'] . ')';
                            } else {
                                echo $data['skin_code'];
                            }
                            ?><br />
                            <button title="" class="btn btn-sm btn-white js-skin-info" type="button" data-skin-code="<?php echo $data['skin_code']; ?>">스킨정보</button>
                        </td>
                        <td>
                            <?php foreach ($useSkin[$data['skin_code']][$skinType . 'LiveName'] as $key => $value) { ?>
                                <div>
                                    <?php if (in_array($key, array_keys($mallSelect)) === true) {?>
                                        <?php if ($mallCnt > 1) { ?>
                                            <span class="flag flag-32 flag-<?php echo $useSkin[$data['skin_code']][$skinType . 'LiveLanguageFl'][$key]; ?>"></span><br />
                                        <?php } else { ?>
                                            <img src="<?php echo PATH_ADMIN_GD_SHARE; ?>img/icon_live.png" width="34" height="18" alt="사용스킨"/><br />
                                        <?php } ?>
                                        (<?php echo $value; ?>)
                                    <?php } ?>
                                    <input type="hidden" name="skinLive[]" value="<?php echo $key . STR_DIVISION . $data['skin_code'];?>">
                                </div>
                            <?php } ?>

                        </td>
                        <td>
                            <?php foreach ($useSkin[$data['skin_code']][$skinType . 'WorkName'] as $key => $value) { ?>
                                <div>
                                    <?php if (in_array($key, array_keys($mallSelect)) === true) {?>
                                        <?php if ($mallCnt > 1) { ?>
                                            <span class="flag flag-32 flag-<?php echo $useSkin[$data['skin_code']][$skinType . 'WorkLanguageFl'][$key]; ?>"></span><br />
                                        <?php } else { ?>
                                            <img src="<?php echo PATH_ADMIN_GD_SHARE; ?>img/icon_work.png" width="34" height="18" alt="작업스킨"/><br />
                                        <?php } ?>
                                        (<?php echo $value; ?>)
                                    <?php } ?>
                                    <input type="hidden" name="skinWork[]" value="<?php echo $key . STR_DIVISION . $data['skin_code'];?>">
                                </div>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if (empty($languageFl) === false && $languageFl != 'kr') { ?>
                                <a href="<?php echo $uriHome . $languageFl . $skinPreviewPage . $data['skin_code']; ?>" class="btn btn-white btn-sm" target="_blank">미리보기</a>
                            <?php } else { ?>
                                <a href="<?php echo $skinPreviewUrl . $data['skin_code'];?>" class="btn btn-white btn-sm" target="_blank">미리보기</a>
                            <?php } ?>
                        </td>
                        <td>
                            <button type="button" data-skin="<?php echo $data['skin_code']; ?>" class="btn-dark-gray btn-sm js-skin-down">다운</button>
                            <button type="button" data-skin="<?php echo $data['skin_code']; ?>" class="btn-dark-gray btn-sm js-skin-copy">복사</button>
                            <?php if ($useSkin[$data['skin_code']]['delFl'] != 'n') { ?>
                                <button type="button" class="btn-dark-gray btn-sm js-skin-remove">삭제</button>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
            }
        }
        ?>
        </tbody>
    </table>
    <!-- //디자인 스킨 변경 -->
    <div class="form-inline">
        선택한 스킨을
        <?php
            if($mallCnt>1) { // 해외몰 사용중
                echo gd_select_box('sno', 'sno', $mallSelect, null, null, '- 상점 선택 -');
            } else { // 기준몰만 사용중
                echo gd_select_box('sno', 'sno', $mallSelect, null, $mallSno, '기준몰',"disabled", null);
            }
            ?>
        의 <input type="submit" value="사용 스킨으로 설정" class="btn btn-red btn-lg js-skin-use-btn" data-skin-use="Live" /> <input type="submit" value="작업 스킨으로 설정" class="btn btn-red btn-lg js-skin-use-btn" data-skin-use="Work" />
    </div>
</form>

<?php if ($mallCnt > 1) { ?>
<form id="frmMallIcon" name="frmMallIcon" action="design_skin_list_ps.php" method="post" target="ifrmProcess" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="mallIconConfig"/>
    <input type="hidden" name="menuType" value="<?php echo $menuType; ?>"/>
    <div class="table-title mgt20">
        해외몰 홈아이콘 관리
    </div>
    <div class="table-header design-list-width mgb10">
        <table class="table table-rows table-fixed">
            <colgroup>
                <col class="width-md"/>
                <col/>
                <col/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>유형</th>
                <td class="vtop">
                    <label><input type="radio" name="iconType<?php echo ucfirst($menuType); ?>" value="check" <?php echo $checked['iconType']['check']; ?>> 노출형</label>
                    <div class="form-inline mgt10">
                        <?php foreach ($mallListAll as $key => $mall) { ?>
                            <img src="<?php echo $uriCommon . '/' . $mallIcon[$key]; ?>" />
                        <?php } ?>
                    </div>
                </td>
                <td class="vtop">
                    <label><input type="radio" name="iconType<?php echo ucfirst($menuType); ?>" value="select_flag" <?php echo $checked['iconType']['select_flag']; ?>> 선택형I (국기)</label>
                    <div class="mgt10 width-3xs">
                        <div class="global-select"><img src="<?php echo $uriCommon . '/' . $mallIcon[1]; ?>" /></div>
                        <div class="table-header pdt5 mgt5">
                            <?php foreach ($mallListAll as $key => $mall) { ?>
                                <div class="mgt5"><img src="<?php echo $uriCommon . '/' . $mallIcon[$key]; ?>" /></div>
                            <?php } ?>
                        </div>
                    </div>
                </td>
                <td class="vtop">
                    <label><input type="radio" name="iconType<?php echo ucfirst($menuType); ?>" value="select_language" <?php echo $checked['iconType']['select_language']; ?>> 선택형II (국기, 언어)</label>
                    <div class="mgt10 width-lg">
                        <div class="global-select"><img src="<?php echo $uriCommon . '/' . $mallIcon[1]; ?>" /> <?php echo $mallListAll[1]['languageFl']; ?></div>
                        <div class="table-header pdt5 mgt5">
                            <?php foreach ($mallListAll as $key => $mall) { ?>
                                <div class="mgt5"><img src="<?php echo $uriCommon . '/' . $mallIcon[$key]; ?>" /> <?php echo $mall['languageFl']; ?></div>
                            <?php } ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>아이콘 관리</th>
                <td colspan="3">
                    <?php foreach ($mallListAll as $key => $mall) { ?>
                        <div class="form-inline mgt5">
                            <?php echo $mall['mallName']; ?> | <img src="<?php echo $uriCommon . '/' . $mallIcon[$key]; ?>" />
                            <input type="hidden" name="mallDomainFl[<?php echo $key; ?>]" value="<?php echo $mall['domainFl']; ?>"/>
                            <input type="file" name="mallIcon<?php echo ucfirst($menuType); ?>[<?php echo $key; ?>]" class="form-control"/>
                            <label><input type="checkbox" name="mallIconDel[<?php echo $key; ?>]" value="<?php echo  $mallIcon[$key]; ?>">삭제</label>
                        </div>
                    <?php } ?>
                    <div class="notice-info mgt5">
                        권장 이미지 사이즈 : 22 px x 17 px / 500kb이하<br />
                        권장 확장자 : png<br />
                        기존 제공되는 아이콘의 경우, 삭제될 경우 복원되지 않습니다.
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="center"><input type="submit" value="저장" class="btn btn-red btn-lg"></div>
    </div>
</form>
<?php } ?>

<script type="text/javascript">
    <!--
    $(document).ready(function (n) {
        // 스킨 변경 하기
        $('.js-skin-use-btn').click(function () {
            $('input[name="skinUse"]').val($(this).data('skin-use'));
        });

        $("#frmDesignSkinList").validate({
            dialog:false,
            submitHandler: function (form) {
                var type = $('input[name="skinUse"]').val();
                var value = $('input[name="<?php echo $skinType;?>Skin"]:checked').val();
                for (var i = 0; i < $('input[name="skin' + type + '[]"]').length; i++) {
                    var useSkin = $('input[name="skin' + type + '[]"]').eq(i).val().split('<?php echo STR_DIVISION; ?>');
                    if ($('select[name="sno"]').val() != useSkin[0] && value == useSkin[1]) {
                        alert('이미 다른 상점몰에서 ' + (type == 'Live' ? '사용' : '작업') + '스킨으로 설정되어 있어 적용이 불가합니다.');
                        return false;
                    }
                }
                if(type == 'Live'){
                    dialog_confirm('‘현재 사용스킨’ 변경 시 게시판에 적용된 스킨은 ‘게시판 기본스킨’으로 변경됩니다.\n 변경하시겠습니까?' +
                        '<br><label><input type="checkbox" id="workSkin" value="y"> 작업 스킨도 동시 변경되도록 처리</label>',function(result){
                        if(result) {
                            // 작업스킨 동시변경 체크시
                           if($('#workSkin').is(':checked') == true){
                               $('input[name="skinUseAllFl"]').val('y');
                           } else if($('#workSkin').is(':checked') == false){
                               $('input[name="skinUseAllFl"]').val('n');
                           }

                            form.target = 'ifrmProcess';
                            form.submit();
                        }
                    });
                }
                else {
                    form.target = 'ifrmProcess';
                    form.submit();
                }

            },
            dialog: false,
            rules: {
                <?php echo $skinType;?>Skin: "required",
                sno: "required"
            },
            messages: {
                <?php echo $skinType;?>Skin: {
                    required: '스킨을 선택해 주세요.'
                },
                sno: {
                    required: '상점을 선택해 주세요.'
                }
            }
        });

        // 스킨 다운
        $('.js-skin-down').click(function () {
            var skinName = $(this).data('skin');

            BootstrapDialog.show({
                title: '스킨 다운',
                type: BootstrapDialog.TYPE_INFO,
                message: skinName + ' 스킨을 ZIP 파일로 압축 후 다운로드 하시겠습니까?',
                buttons: [
                    {
                        id: 'btn-cancel',
                        label: '다운 취소',
                        action: function(dialogItself){
                            dialogItself.close();
                        }
                    },
                    {
                        id: 'btn-down',
                        label: skinName + ' 스킨 다운',
                        cssClass: 'btn-black',
                        action: function(dialog) {
                            var $downButton = this;
                            $downButton.disable();
                            $('#btn-down').html(skinName + ' 스킨을 ZIP 파일로 압축 중입니다.');
                            $('#btn-cancel').html('닫기');
                            $downButton.spin();
                            dialog.getModalBody().html('상단 또는 하단의 상태바에서 스킨 파일을 저장 또는 저장된 파일을 확인 하세요.');
                            location.href = 'design_skin_list_ps.php?mode=downSkin&skinType=<?php echo $skinType; ?>&skinName=' + skinName;
                            setTimeout(function() {
                                $('#btn-down').hide();
                            }, 3000);
                            setTimeout(function() {
                                dialog.close();
                            }, 6000);
                        }
                    }
                ]
            });
            return;
        });

        // 스킨 삭제
        $('.js-skin-remove').click(function () {
            var skinName = $(this).parents('tr:eq(0)').find('input[name=<?php echo $skinType;?>Skin]').val();
            var delFl = $(this).parents('tr:eq(0)').find('input[name=<?php echo $skinType;?>Skin]').data('delfl');

            if (delFl == 'n') {
                BootstrapDialog.show({
                    title: '스킨 삭제 금지',
                    type: BootstrapDialog.TYPE_DANGER,
                    message: '사용중인 '+skinName+' 스킨은 삭제할 수 없습니다.',
                });
                return;
            }

            BootstrapDialog.show({
                title: '스킨 삭제',
                type: BootstrapDialog.TYPE_DANGER,
                message: skinName + ' 스킨을 정말로 삭제 하시겠습니까? 삭제시 복구가 불가능합니다.',
                buttons: [
                    {
                        id: 'btn-cancel',
                        label: '삭제 취소',
                        action: function(dialogItself){
                            dialogItself.close();
                        }
                    },
                    {
                        id: 'btn-del',
                        label: skinName + ' 스킨 삭제',
                        cssClass: 'btn-danger',
                        action: function(dialog) {
                            var $delButton = this;
                            var $cancelButton = dialog.getButton('btn-cancel');
                            $delButton.disable();
                            $cancelButton.disable();
                            $delButton.spin();
                            dialog.setClosable(false);
                            dialog.setMessage(skinName + ' 스킨을 삭제 중입니다.');

                            $.ajax({
                                type: 'POST'
                                , url: 'design_skin_list_ps.php'
                                , data: {'mode': 'deleteSkin', 'skinName': skinName, 'skinType': '<?php echo $skinType; ?>'}
                                , success: function (res) {
                                    if (res == 'ok') {
                                        dialog.getModalBody().html(skinName + ' 스킨이 삭제 되었습니다. 잠시후 완료 됩니다.<br />만약 ' + skinName + ' 스킨이 남아 있다면 다시 한번 삭제 진행을 해주세요.');
                                        setTimeout(function() {
                                            location.reload();
                                        }, 1000);
                                    } else {
                                        dialog.getModalBody().html(skinName + '스킨 삭제에 실패 하였습니다. <br />실패 이유 : ' + res);
                                        setTimeout(function() {
                                            dialog.close();
                                        }, 3000);
                                    }
                                }
                            });
                        }
                    }
                ]
            });
            return;
        });

        // 스킨 복사
        $('.js-skin-copy').click(function (e) {
            var skinName = $(this).data('skin');
            var params = {
                skinName: skinName ,
                skinType: '<?php echo $skinType; ?>'
            };
            $.get('layer_skin_copy.php', params, function (data) {
                BootstrapDialog.show({
                    title: '스킨 복사',
                    message: $(data),
                    closable: true
                });
            });
        });

        // 스킨 업로드
        $('.js-skin-upload').click(function (e) {
            var params = {
                skinType: '<?php echo $skinType; ?>'
            };
            $.get('layer_skin_upload.php', params, function (data) {
                BootstrapDialog.show({
                    title: '스킨 업로드',
                    message: $(data),
                    closable: true
                });
            });
        });

        $('.js-skin-popup').click(function(){
            $('#freeSkinPopupFm').submit();
        });

        //스킨 정보
        $('.js-skin-info').click(function () {
            var params = {
                skinType: '<?php echo $skinType; ?>',
                skinCode: $(this).data('skin-code')
            };

            $.get('layer_skin_modify.php', params, function (data) {
                BootstrapDialog.show({
                    title: '스킨 정보 변경',
                    message: $(data),
                    closable: true
                });
            });
        });

        // 선택된 스킨리스트 행 배경색 변경
        $('input[name="<?php echo $skinType;?>Skin"]').click(function () {
            $("tbody.checkTr").find("tr").css("background-color", "white");
            $(this).parents("tr").css("background-color", "#FCF8E3");
        });
    });
    //-->
</script>
