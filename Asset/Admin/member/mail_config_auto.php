<form id="formConfig" method="post" action="../member/mail_ps.php">
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <input type="button" value="저장" class="btn btn-red btn-save">
    </div>
    <ul class="nav nav-tabs mgb20" role="tablist" id="tabMall">
        <?php foreach ($gGlobal['useMallList'] as $val) { ?>
            <li role="presentation" class="<?= $mallSno == $val['sno'] ? 'active' : ''; ?>">
                <a href="#mall<?= $mallSno ?>" role="tab" data-toggle="tab" aria-controls="mall<?= $val['sno'] ?>" data-mall-sno="<?= $val['sno']; ?>">
                    <span class="flag flag-16 flag-<?= $val['domainFl']; ?>"></span> <?= $mallSno == $val['sno'] ? $gGlobal['mallList'][$mallSno]['mallName'] : ''; ?>
                </a>
            </li>
        <?php } ?>
    </ul>
    <div class="tab-contents">
        <div class="tab-pane" role="tabpanel" id="mallPanel">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs mgb20" role="tablist" id="tabCategory">
                <li role="presentation" class="<?= $activeTab['order']; ?>">
                    <a href="#order" aria-controls="order" role="tab" data-toggle="tab">주문/배송관련</a></li>
                <li role="presentation" class="<?= $activeTab['join']; ?>"><a href="#join"
                                                                              aria-controls="join"
                                                                              role="tab"
                                                                              data-toggle="tab">가입/탈퇴/문의관련</a>
                </li>
                <li role="presentation" class="<?= $activeTab['member']; ?>">
                    <a href="#member" aria-controls="member" role="tab" data-toggle="tab">회원정보관련</a></li>
                <?php if ($isDefaultMall) { ?>
                    <li role="presentation" class="<?= $activeTab['point']; ?>">
                        <a href="#point" aria-controls="point" role="tab" data-toggle="tab">마일리지/예치금관련</a></li>
                    <li role="presentation" class="<?= $activeTab['admin']; ?>">
                        <a href="#admin" aria-controls="admin" role="tab" data-toggle="tab">관리자보안관련</a></li>
                <?php } ?>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content form-inline">
                <?php
                $templateHtml = [];
                $isOrder = ($category == 'order');
                $isFindPassword = (($category == 'join') && ($type == 'findpassword')) || ($category == 'admin' && $type == 'adminsecurity');
                $isBoardReply = ($category == 'join') && ($type == 'qna');
                $templateHtml[] = '<div class="table-title gd-help-manual">자동메일 발송설정</div>';
                if ($mallDomainCheck === false) {
                    $templateHtml[] = '<div class="notice-danger notice-info">쇼핑몰 도메인이 설정되지 않아 자동메일이 발송되지 않습니다. 쇼핑몰 도메인을 설정하세요. <a href="../policy/base_info.php" class="text-red">설정하기 ></a></div>';
                }
                $templateHtml[] = '<div role="tabpanel" class="tab-pane active" id="' . $category . '" data-category="' . $category . '">';
                $templateHtml[] = '<table class="table table-cols"><colgroup> <col class="width-sm"> <col> </colgroup>';
                $templateHtml[] = '<tr><th>유형선택</th><td>' . $typeRadio . '</td></tr>';
                if ($isFindPassword === false) {
                    $templateHtml[] = '<tr><th>발송여부</th><td>' . $typeAutoSendRadio;
                    if ($useApprovalFlag && $type == 'join') {
                        $templateHtml[] = '<br/><label class="mgt5 checkbox-inline"><input type="checkbox" name="mailDisapproval" value="y" ' . $checked['mailDisapproval']['y'] . '>승인대기 회원 포함</label>';
                    }
                    $templateHtml[] = '</td></tr>';
                }
                if ($isBoardReply) {
                    $templateHtml[] = '<tr><th>발송게시판 선택</th><td><button type="button" class="btn btn-sm btn-gray" id="btnBoard">게시판 선택</button>';
                    if (isset($boardInfo) && is_array($boardInfo)) {
                        $htmlBoardList = [];
                        $htmlBoardList[] = '<div id="boardLayer" class="active selected-btn-group">';
                        $htmlBoardList[] = '<h5>선택된 게시판</h5>';
                        foreach ($boardInfo as $key => $value) {
                            $htmlBoardList[] = '<div id="info_board_' . $value['sno'] . '" class="btn-group btn-group-xs">';
                            $htmlBoardList[] = '<input type="hidden" name="boardNo[]" value="' . $value['sno'] . '">';
                            $htmlBoardList[] = '<input type="hidden" name="boardNoNm[]" value="' . $value['bdNm'] . '">';
                            $htmlBoardList[] = '<span class="btn js-board-sno" data-board-name="' . $value['bdNm'] . '" data-board-sno="' . $value['sno'] . '">' . $value['bdNm'] . '</span><button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_board_' . $value['sno'] . '">삭제</button>';
                            $htmlBoardList[] = '</div>';
                        };
                        $templateHtml[] = join('', $htmlBoardList);
                        $templateHtml[] = '</div>';
                    }
                    $templateHtml[] = '</td></tr>';
                }
                if ($isOrder && $type != 'order') {
                    $templateHtml[] = '<tr><th>발송대상</th><td>최근 ' . $sendTargetSelect . ' 주문 건만 발송</td></tr>';
                }
                if ($isOrder && $type == 'delivery') {
                    $templateHtml[] = '<tr><th>발송방식</th><td>' . $sendMailTypeSelect . '</td></tr>';
                }
                $templateHtml[] = '<tr><th>제목</th><td><input type="text" name="subject" style="width:98%;" value="' . gd_htmlspecialchars_addslashes($typeTemplate['subject']) . '"></td></tr>';
                $templateHtml[] = '<tr><th>발송자이메일</th><td><input type="text" name="senderMail" value="' . $typeSendMail . '"> <span class="notice-info">발송자 이메일 정보가 없으면 자동메일이 발송되지 않습니다.</span></td></tr>';
                $templateHtml[] = '<tr><th>내용<br/><button type="button" class="btn btn-xs btn-gray mgt5" id="btnViewReplaceCode" name="btnViewReplaceCode">치환코드 보기</button></th><td>';
                $templateHtml[] = '<textarea name="body" rows="26" style="height:400px;" id="editor" class="form-control width100p" type="editor" required="required">' . $typeTemplate['body'] . '</textarea>';
                $templateHtml[] = '</td></tr>';
                $templateHtml[] = '</table>';
                $templateHtml[] = '</div>';
                echo join('', $templateHtml);
                ?>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript">
    var oEditors;
    var uploadImages = [];

    function addUploadImages(data) {
        uploadImages.push(data);
    }

    function cleanUploadImages() {
        uploadImages = null;
    }

    var mail_config_auto = (function ($) {
        var form, backup, params;

        function validation_submit(func) {
            validate = form.validate();
            validate.settings.submitHandler = func;

            if (validate.form()) {
                form.submit();
            }
        }

        function set_submit_params(category, type, mall_sno) {
            try {
                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
            } catch (e) {
                console.log(e);
            }
            params = $(form).serializeArray();

            if (category == 'join' && type == 'qna') {
                var boardInfo = [];
                $('div[id*="info_board_"]').each(function (idx, item) {
                    var boardNo = $(item).find('input[name="boardNo[]"]').val();
                    var boardName = $(item).find('input[name="boardNoNm[]"]').val();
                    boardInfo.push({sno: boardNo, bdNm: boardName});
                });
                params.push({name: "boardInfo", value: JSON.stringify(boardInfo)});
            }
            params.push({name: "mode", value: "saveAutoConfig"});
            params.push({name: "category", value: category});
            params.push({name: "type", value: type});
            params.push({name: "mallSno", value: mall_sno});
        }

        function diff_backup() {
            var diff = {
                subject: $('input[name="subject"]').val(),
                content: oEditors.getById['editor'].getRawContents()
            };
            diff.subject_length = diff.subject.length;
            diff.content_length = diff.content.length;
            if (diff.subject_length != backup.subject_length) {
                logger.info('diff_backup', diff);
                return true;
            } else {
                if (diff.content_length != backup.content_length) {
                    logger.info('diff_backup', diff);
                    return true;
                }
            }
            return false;
        }

        return {
            save: function () {
                var _category = $('#tabCategory li.active > a', form).attr('aria-controls');
                var _type = $(':radio[name=typeRadio]:checked').val();
                var _mall_sno = $('#tabMall li.active > a', form).data('mall-sno');
                set_submit_params(_category, _type, _mall_sno);
                validation_submit(function () {
                    post_with_reload('../member/mail_ps.php', params);
                });
            },
            move_mall: function (e) {
                e.preventDefault();
                var _mall_sno = $(e.target).data('mall-sno');
                if (typeof _mall_sno == 'undefined') {
                    _mall_sno = $(e.target).closest('a[role="tab"]').data('mall-sno');
                }
                var move_url = '../member/mail_config_auto.php?mallSno=' + _mall_sno;
                if (diff_backup()) {
                    dialog_confirm('저장되지 않은 정보가 있습니다. 정보를 저장하고 이동하시겠습니까?', function (result) {
                        if (result) {
                            set_submit_params(backup.category, backup.type, backup.mall_sno);
                            validation_submit(function () {
                                post_with_reload('../member/mail_ps.php', params, move_url);
                            });
                        } else {
                            location.href = move_url;
                        }
                    });
                } else {
                    location.href = move_url;
                }
            },
            move_category: function (e) {
                e.preventDefault();
                var _category = $(e.target).attr('aria-controls');
                var _mall_sno = $('#tabMall li.active').find('a[role="tab"]').data('mall-sno');
                var move_url = '../member/mail_config_auto.php?category=' + _category + '&mallSno=' + _mall_sno;
                if (diff_backup()) {
                    dialog_confirm('저장되지 않은 정보가 있습니다. 정보를 저장하고 이동하시겠습니까?', function (result) {
                        if (result) {
                            set_submit_params(backup.category, backup.type, backup.mall_sno);
                            validation_submit(function () {
                                post_with_reload('../member/mail_ps.php', params, move_url);
                            });
                        } else {
                            location.href = move_url;
                        }
                    });
                } else {
                    location.href = move_url;
                }
            },
            move_type: function (e) {
                e.preventDefault();
                var _category = $('#tabCategory li[role=presentation].active > a', form).attr('aria-controls');
                var _type = $(e.target).val();
                var _mall_sno = $('#tabMall li.active').find('a[role="tab"]').data('mall-sno');
                var move_url = '../member/mail_config_auto.php?category=' + _category + '&type=' + _type + '&mallSno=' + _mall_sno;
                if (diff_backup()) {
                    dialog_confirm('저장되지 않은 정보가 있습니다. 정보를 저장하고 이동하시겠습니까?', function (result) {
                        if (result) {
                            set_submit_params(backup.category, backup.type, backup.mall_sno);
                            validation_submit(function () {
                                post_with_reload('../member/mail_ps.php', params, move_url);
                            });
                        } else {
                            location.href = move_url;
                        }
                    });
                } else {
                    location.href = move_url;
                }
            },
            init: function () {
                oEditors = [];
                nhn.husky.EZCreator.createInIFrame({
                    oAppRef: oEditors,
                    elPlaceHolder: "editor",
                    sSkinURI: "/admin/gd_share/script/smart/SmartEditor2Skin.html?t=2",
                    htParams: {
                        bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
                        bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
                        bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
                        //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
                        fOnBeforeUnload: function () {
                            if (!uploadImages) {
                                return;
                            }
                            $.ajax({
                                method: "GET",
                                url: "/share/editor_file_uploader.php",
                                data: {mode: 'deleteGarbage', uploadImages: uploadImages.join('^|^')},
                                cache: false
                            }).success(function (data) {
                            }).error(function (e) {
                            });
                        }
                    }, //boolean
                    fOnAppLoad: function () {
                        //예제 코드
                        //oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
                        backup = {
                            subject: $('input[name="subject"]').val(),
                            //                    content: $('#editor').val(),
                            content: oEditors.getById['editor'].getRawContents(),
                            category: $('#tabCategory').find('li[role=presentation].active > a').attr('aria-controls'),
                            type: $(':radio[name=typeRadio]:checked').val(),
                            mall_sno: $('#tabMall').find('li[role=presentation].active > a').data('mall-sno')
                        };
                        backup.subject_length = backup.subject.length;
                        backup.content_length = backup.content.length;
                        logger.info('init', backup);
                    },
                    fCreator: "createSEditor2"
                });
                form = $('#formConfig');
            }
        }
    })(jQuery);

    $(document).ready(function () {
        mail_config_auto.init();
        $('.btn-save').click(mail_config_auto.save);
        $('#tabMall').find('li[role=presentation]').click(mail_config_auto.move_mall);
        $('#tabCategory').find('li[role=presentation]').click(mail_config_auto.move_category);
        $(':radio[name=typeRadio]').click(mail_config_auto.move_type);
        $('#btnViewReplaceCode').click(function (e) {
            e.preventDefault();
            var type = $(':radio[name=typeRadio]:checked').val();
            var default_code = ['{rc_mallNm}'];
            if (type == 'join') {
                default_code.push('{rc_today}');
            }
            replace_code_popup(type, default_code);
        });
        $('#btnBoard').click(function () {
            var addParam = {"mode": "search", "parentPage": "mailAuto"};
            layer_add_info('board', addParam);
        });

        $(':checkbox[name="mailDisapproval"]').change(function (e) {
            if (!e.target.checked) {
                alert('\'승인대기 회원 포함\' 설정을 해제하시면, 승인대기 회원에게 회원가입 자동메일이 발송되지 않습니다.<br/>해당 설정을 해제하실 경우, 가입 승인 시 발송되는 가입승인 자동메일 사용을 권장합니다.');
            }
        });

        // HASH가 있는 경우 자동으로 탭 이동 처리
        if (window.location.hash) {
            $('a[href="' + window.location.hash + '"]').tab('show');
        }
    });
</script>
