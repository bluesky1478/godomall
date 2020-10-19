<?php if ($useEpFl !== 'y') { ?>
<style>
    .modal {
        z-index: 2100;
    }
    .modal-dialog{
        top: 200px;
        left: 50px;
    }
    #header, #menu,
    .js-adminmenu-toggle,
    .gnbAnchor_wrap {
        z-index: 2200;
    }
    .ly_setting.sub_type {
        z-index: 2300;
    }

    #contentLayerDim {
        z-index: 2000;
        background: #000;
        opacity: 0.5;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        position: fixed;
    }
</style>
<?php } ?>
<form id="frmCremaConfig" name="frmCremaConfig" action="../service/crema_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="config"/>
    <input type="hidden" name="excelDownloadReason" value=""/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>

        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        크리마 리뷰 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tbody>
            <tr>
                <th>사용여부</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="useCremaFl" value="y" <?= $checked['useCremaFl']['y']; ?> <?= $disabled['useCremaFl']; ?>/>사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="useCremaFl" value="n" <?= $checked['useCremaFl']['n']; ?> <?= $disabled['useCremaFl']; ?>/>사용안함
                    </label>
                    <div class="notice-info">
                        서비스를 사용하시려면 크리마 리뷰 신청 후 사용이 가능합니다. <a href="https://www.godo.co.kr/echost/power/add/convenience/crema-intro.gd" target="_blank" class="btn-link">신청하기</a>
                    </div>
                    <div class="notice-info">
                        크리마리뷰 사용함 설정 시 네이버쇼핑, 크리테오의 상품 EP의 상품평 개수는 크리마리뷰에 등록 된 상품평 개수로 생성됩니다.
                    </div>
                </td>
            </tr>
            <tr>
                <th>Client ID</th>
                <td class="form-inline">
                    <input type="text" name="cremaClientID" value="<?= $data['clientId']; ?>" class="width-3xl" disabled="disabled" />
                </td>
            </tr>
            <tr>
                <th>Client Secret</th>
                <td class="form-inline">
                    <input type="text" name="cremaClientSecret" value="<?= $data['clientSecret']; ?>" class="width-3xl" disabled="disabled" />
                </td>
            </tr>
            <tr>
                <th>파일 다운로드</th>
                <td class="form-inline">
                    <div>
                        <button type="button" class="btn btn-gray btn-sm js-crema-csv-down" <?= $disabled['download']; ?>>다운로드</button>
                        <?php if (empty($downloadDt) === false) { ?>
                        <span class="notice-info mgl15"><?= $downloadDt; ?> 파일 다운로드 완료</span>
                        <?php } ?>
                        <div class="notice-info">
                            크리마 리뷰 설정을 사용할 수 있도록 상품, 주문, 리뷰 정보를 포함한 파일을 CSV 형식으로 다운로드 합니다.
                        </div>
                        <div class="notice-info">
                            다운로드 받은 파일은 크리마 관리자페이지에 업로드 해주세요. <a href="https://admin.cre.ma/auth/login" target="_blank" class="btn-link">크리마 관리자페이지 바로가기</a>
                        </div>
                        <div class="notice-info">
                            파일에는 고객의 정보가 포함되어있습니다. 고객 정보 보호를 위해 파일 다운로드 시 보안인증이 기본으로 제공됩니다.<br/>보안인증 설정은 <a href="../policy/manage_security.php" target="_blank" class="btn-link">운영 보안 설정</a> 에서 변경 가능합니다.
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>크리마리뷰<br>상품평 개수 업데이트</th>
                <td class="form-inline">
                    <span <?php if ($disabled['reviewCntUpdate']) {?>style="color: #999999;"<?php }?>>
                    (
                    <?php if($isPlusReview) {?>
                        <label class="radio-inline">
                            <input type="radio" name="reviewCntUpdateChannel" value="both" data-channel="전체 리뷰" <?php echo gd_isset($checked['reviewCntUpdateChannel']['both']) ?> <?= $disabled['reviewCntUpdateChannel']; ?>/>
                            전체 리뷰
                        </label>
                    <?php }?>
                    <label class="radio-inline">
                        <input type="radio" name="reviewCntUpdateChannel" value="board" data-channel="일반 리뷰" <?php echo gd_isset($checked['reviewCntUpdateChannel']['board']) ?> <?= $disabled['reviewCntUpdateChannel']; ?>/>
                        일반 리뷰
                    </label>
                    <?php if($isPlusReview) {?>
                        <label class="radio-inline">
                            <input type="radio" name="reviewCntUpdateChannel" value="plusReview" data-channel="플러스 리뷰" <?php echo gd_isset($checked['reviewCntUpdateChannel']['plusReview']) ?> <?= $disabled['reviewCntUpdateChannel']; ?>/>
                            플러스 리뷰
                        </label>
                    <?php }?>
                    ) 기준으로
                    </span>

                    <?php if ($data['reviewCntUpdateType'] == 'updated') {?>
                        <?= date('Y-m-d', strtotime($data['reviewCntUpdateDt'])); ?> 상품평 개수 업데이트 완료.(처리자:<?= $data['reviewCntUpdateManagerId']; ?>)
                    <?php } else {?>
                        <button type="button" class="btn btn-gray btn-sm js-crema-reviewcnt-update" <?= $disabled['reviewCntUpdate']; ?>>업데이트</button>
                    <?php }?>

                    <div class="notice-info">
                        업데이트는 최초 1회만 할 수 있습니다.
                    </div>
                    <div class="notice-info">
                        기존에 저장되어 있던 상품평 개수를 크리마리뷰 상품평 개수로 업데이트합니다.
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</form>
<div class="center">
    <a href="https://admin.cre.ma/auth/login" target="_blank"/><button type="button" class="btn btn-lg btn-black">크리마 관리자페이지 바로가기</button></a>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function() {
        // csv 다운로드
        $('.js-crema-csv-down').on('click', function() {
            create_crema_csv();
        });

        // ep 가이드
        if ('<?=$useEpFl;?>' !== 'y') {
            setGuideLayer();
        }

        // 상품평 개수 업데이트
        $('.js-crema-reviewcnt-update').click(function () {
            reviewCntUpdate();
        });
    });

    // 상품평 개수 업데이트
    function reviewCntUpdate() {
        if ($(':radio[name=reviewCntUpdateChannel]:checked').length == 0) {
            alert('크리마리뷰 상품평 개수로 업데이트 할 기준을 선택해주세요.');
            return;
        }

        var channelName = $(':radio[name=reviewCntUpdateChannel]:checked').eq(0).data('channel');
        var message = "크리마리뷰 상품평 개수를 " + channelName + " 기준으로 업데이트를 진행하시겠습니까?";
        BootstrapDialog.show({
            title: '확인',
            message: message,
            buttons: [
                {
                    id: 'btn-close',
                    label: '취소',
                    action: function(dialogItself){
                        dialogItself.close();
                    }
                },
                {
                    id: 'btn-confirm',
                    label: '<span>확인</span>',
                    cssClass: 'btn-black',
                    action: function(dialog) {
                        var $confirmButton = this;
                        var $closeButton = dialog.getButton('btn-close');
                        $confirmButton.disable();
                        $closeButton.disable();
                        $confirmButton.spin();
                        dialog.setClosable(false);
                        $closeButton.hide();
                        $confirmButton.children().last().text('업데이트 중');

                        $.ajax({
                            method: "POST",
                            url: "./crema_ps.php",
                            data: {
                                mode: "reviewCntUpdate",
                                reviewCntUpdateChannel: $(':radio[name=reviewCntUpdateChannel]:checked').val()
                            },
                            dataType: 'json'
                        }).success(function (result) {
                            if (result.success === true) {
                                dialog.setMessage('크리마리뷰 상품평 개수 업데이트가 완료되었습니다.');
                                dialog.setButtons(
                                    [
                                        {
                                            label: '확인',
                                            cssClass: 'btn-black',
                                            size: BootstrapDialog.SIZE_LARGE,
                                            action: function(dialogItself){
                                                window.location.href = './crema_config.php';
                                            }
                                        }
                                    ]
                                );
                                dialog.updateSize();
                            } else if (result.success !== true) {
                                if (result.msgNo == 'm1') {
                                    message = '업데이트가 이미 진행 중입니다.';
                                } else {
                                    message = '크리마리뷰 상품평 개수 업데이트가 실패되었습니다.<br>다시 시도해 주시기 바랍니다.';
                                }
                                dialog.setMessage(message);
                                dialog.setButtons(
                                    [
                                        {
                                            label: '확인',
                                            cssClass: 'btn-black',
                                            size: BootstrapDialog.SIZE_LARGE,
                                            action: function(dialogItself){
                                                dialogItself.close();
                                            }
                                        }
                                    ]
                                );
                                dialog.updateSize();
                            }
                        }).error(function (e) {
                            layer_close();
                            alert(e.responseText);
                        });
                    }
                }
            ]
        });
    }

    // 가이드 레이어 노출
    function setGuideLayer() {
        var scriptAjaxLayer = _.template($('#scriptAjaxLayer').html());
        $('#content-wrap').append('<div id="contentLayerDim"></div>');
        $('#content-wrap').append(scriptAjaxLayer);
        var dim = $('#contentLayerDim');
        var defaultLayerHeight = dim.outerHeight();
        var minimumContentHeight = 340;
        var contentHeight = 0;
        dim.css('left', $('#menu').outerWidth() + 'px');
        // dim 처리
        var modifyDimBottomStyle = function() {
            if (Math.round($(window).scrollTop()) >= $(document).height() - $(window).height()) {
                dim.css('bottom', ($('#footer').outerHeight() + ($('html').outerHeight() - $('body').outerHeight())) + 'px');
            } else {
                dim.css('bottom', 0);
            }
        };

        // 메뉴 숨기기에 따른 dim 처리
        $(document).on('click', '.js-adminmenu-toggle', function() {
            if ($('.js-adminmenu-toggle').hasClass('active')) {
                dim.css('left', '15px');
                dim.css('bottom', ($('#content').outerHeight() - $('#header').outerHeight() - parseInt($('#content').css('margin-top'))) + 'px');
            } else {
                dim.css('left', $('#menu').outerWidth() + 'px');
                modifyDimBottomStyle();
            }
        });

        // 메뉴 줄이기에 따른 dim 처리
        $('.js-listgroup-toggle').on('click', function() {
            if ($(this).hasClass('active')) {
                dim.css('bottom', 0);
            } else {
                contentHeight = $('#content').outerHeight() - $('#header').outerHeight() - parseInt($('#content').css('margin-top'));
                contentHeight = contentHeight > 1000 ? minimumContentHeight : contentHeight;
                if (defaultLayerHeight !== dim.outerHeight()) {
                    contentHeight -= defaultLayerHeight - dim.outerHeight();
                }
                dim.css('bottom', contentHeight + 'px');
            }
        });

        // 관리자 메뉴 헤더 토글
        $('#menu .panel-heading').click(function (e) {
            modifyDimBottomStyle();
        });

        // 푸터 dim 제외
        $(window).scroll(function() {
            if (Math.round($(window).scrollTop()) >= $(document).height() - $(window).height()) {
                dim.css('bottom', $('#footer').outerHeight() + 'px');
            } else {
                dim.css('bottom', 0);
            }
        });

        // ep 생성 변경 동의
        $(document).on('click', '.js-agree-ep', function () {
            var data = {mode: 'setUseEpFl', useEpFl: 'y'};
            $.ajax({
                method: "POST",
                cache: false,
                url: '../service/crema_ps.php',
                data: data
            }).done(function (result) {
                var title = '안내';
                var message = result.message;
                if (result.success !== true) {
                    title = '경고';
                    message = '설정 저장 중 오류가 발생하였습니다. 다시 시도해 주세요.'
                }
                $('.bootstrap-dialog-title').text(title);
                $('.bootstrap-dialog-message').text(message);
                $('.bootstrap-dialog-footer-buttons > button').removeClass('btn-red js-agree-ep').addClass('btn-black js-reload').text('확인').on('click', function() {
                    location.reload();
                });
            }).fail(function (e) {
                alert(e.responseText);
            });
        });
    }

    // 크리마 파일 생성 프로세스
    function create_crema_csv() {
        $.ajax({
            method: 'post',
            data: {mode: 'setFilePassword'},
            cache: false,
            url: "../service/crema_ps.php",
            success: function (data) {
                if (data.success === false) {
                    dialog_alert(data.message);
                } else {
                    var dialog = BootstrapDialog.dialogs[BootstrapDialog.currentId];
                    if (dialog) {
                        dialog.close();
                    }
                    // 보안인증 및 패스워드 설정
                    if (!_.isUndefined(data.callback) && data.callback === 'open_file_auth') {
                        open_file_auth();
                    } else {
                        set_file_download_reason();
                    }
                }
            },
            error: function (data) {
                console.log(data);
            }
        });
    }

    // 파일 비밀번호 세팅
    function set_file_password() {
        var params = {
            mode: 'createCremaCsv',
            action: '../service/crema_ps.php',
        };
        $.get('../share/layer_file_password.php', params, function (data) {
            BootstrapDialog.show({
                title: '파일 다운로드 설정',
                message: $(data),
                closable: false,
                onshow: function (dialog) {
                    var $modal = dialog.$modal;
                    BootstrapDialog.currentId = $modal.attr('id');
                }
            });
        });
    }

    // 파일 다운로드 보안 인증
    function open_file_auth() {
        var params = {
            subject: 'crema',
            downloadTarget: 'file'
        };
        $.get('../share/layer_excel_auth.php', params, function (data) {
            BootstrapDialog.show({
                title: '파일 다운로드 보안 인증',
                message: $(data),
                closable: false,
                onshow: function (dialog) {
                    var $modal = dialog.$modal;
                    BootstrapDialog.currentId = $modal.attr('id');
                }
            });
        });
    }

    // 파일 다운로드 사유
    function set_file_download_reason() {
        var complied = _.template($('#downloadReason').html());
        var message = complied();
        BootstrapDialog.show({
            title: '파일 다운로드 사유',
            size: BootstrapDialog.SIZE_WIDE,
            message: message,
            buttons: [{
                label: '확인',
                cssClass: 'btn-black',
                hotkey: 32,
                size: BootstrapDialog.SIZE_LARGE,
                action: function (dialog) {
                    if ($('#excelDownloadReason').val() == '') {
                        $('#reasonError').removeClass('display-none');
                    }
                    dialog.close();
                    $("input[name='excelDownloadReason']").val($('#excelDownloadReason').val());
                    set_file_password();
                }
            }]
        });
    }
    //-->
</script>
<script type="text/html" id="downloadReason">
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm">
                <col>
            </colgroup>
            <tbody>
            <tr style="border-top: 1px solid #E6E6E6;">
                <th>사유 선택</th>
                <td>
                    <div class="form-inline">
                        <?= gd_select_box('excelDownloadReason', 'excelDownloadReason', $reasonList, null, null, '=사유 선택=', null, 'form-control'); ?>
                        <div id="reasonError" class="text-red display-none">사유 선택은 필수입니다.</div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="well">
        <div class="notice-info">개인정보의 안전성 확보조치 기준(고시)에 의거하여 개인정보를 다운로드한 경우 사유 확인이 필요합니다.</div>
    </div>
</script>
<?php if ($useEpFl !== 'y') { ?>
<script type="text/html" id="scriptAjaxLayer">
    <div class="modal bootstrap-dialog type-primary fade size-wide in display-block">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="bootstrap-dialog-header">
                        <div class="bootstrap-dialog-title">상품평 개수 EP 생성 방식 변경 안내</div>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="bootstrap-dialog-body">
                        <div class="bootstrap-dialog-message">
                            크리마리뷰 사용 시 네이버 쇼핑, 크리테오의 상품 EP의 상품평 개수가 크리마리뷰에 등록 된 상품평 개수로 생성되도록 개선되었습니다. <br/><br/>기존에 크리마리뷰 사용 중인 고객분들은<br/>변경된 EP 생성 방식에 동의 해야 적용됩니다.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="bootstrap-dialog-footer">
                        <div class="bootstrap-dialog-footer-buttons">
                            <?php if (\Session::get('manager.isSuper') === 'y') { ?>
                            <button class="btn btn-red btn-lg js-agree-ep">상품평 개수 EP 생성 방식 변경 동의</button>
                            <?php } else { ?>
                            <div class="text-danger">
                                쇼핑몰 '최고운영자'가 상품평 개수 EP 생성 방식 변경 안내 내용을 확인하지 않았습니다. '최고운영자'로 로그인 후 안내 사항 동의 바랍니다.
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>
<?php } ?>