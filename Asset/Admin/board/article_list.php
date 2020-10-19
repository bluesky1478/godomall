<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?>
        <small>게시물을 수정하고 관리합니다.</small>
    </h3>
    <?php ?>
    <?php if ($board->canWrite() == 'y') { ?>
        <input type="button" id="btnWrite" class="btn btn-red" onclick="btnWrite('<?= $req['bdId'] ?>');" value="등록">
    <?php } ?>
</div>
<div class="table-title gd-help-manual">게시글 관리</div>
<form name="frmSearch" id="frmSearch" action="article_list.php" class="frmSearch js-form-enter-submit">
    <div class="search-detail-box">
        <table class="table table-cols">
            <tr>
                <th class="width-xs">게시판</th>
                <td class="width-3xl">
                    <?php if (!gd_is_provider()) { ?>
                        <select name="bdId" id="bdId" class="form-control width-lg">
                            <?php
                            if (isset($boards) && is_array($boards)) {
                                foreach ($boards as $val) {
                                    ?>
                                    <option
                                        value="<?= $val['bdId'] ?>" <?php if ($val['bdId'] == $bdList['cfg']['bdId'])
                                        echo "selected='selected'" ?> data-bdReplyStatusFl="<?=$val['bdReplyStatusFl']?>" data-bdEventFl="<?=$val['bdEventFl']?>" data-bdGoodsPtFl="<?=$val['bdGoodsPtFl']?>" data-bdGoodsFl="<?=$val['bdGoodsFl']?>"><?= $val['bdNm'] . '(' . $val['bdId'] . ')' ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    <?php } else { ?>
                        <?= $bdList['cfg']['bdNm'] ?> (<?= $bdList['cfg']['bdId'] ?>)
                        <input type="hidden" name="bdId" value="<?= $bdList['cfg']['bdId'] ?>"/>
                    <?php } ?>

                </td>
                <th class="width-xs">말머리</th>
                <td>
                    <div class="form-inline">
                        <?= gd_isset($bdList['categoryBox'], '-'); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>일자</th>
                <td colspan="3">
                    <div class="form-inline">
                        <select name="searchDateFl" class="form-control">
                            <option value="regDt" <?php if ($req['searchDateFl'] == 'regDt') echo 'selected' ?>>
                                등록일
                            </option>
                            <option value="modDt" <?php if ($req['searchDateFl'] == 'modDt') echo 'selected' ?>>
                                수정일
                            </option>
                        </select>

                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="rangDate[]"
                                   value="<?= $req['rangDate'][0]; ?>">
                                    <span class="input-group-addon">
                                        <span class="btn-icon-calendar">
                                        </span>
                                    </span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="rangDate[]"
                                   value="<?= $req['rangDate'][1]; ?>">
                                    <span class="input-group-addon">
                                        <span class="btn-icon-calendar">
                                        </span>
                                    </span>
                        </div>
                        <?= gd_search_date(gd_isset($req['searchPeriod'], 6), 'rangDate', false) ?>
                    </div>
                </td>
            </tr>
            <tr class="js-if-bdGoodsPtFl">
                <?php  if ($bdList['cfg']['bdAnswerStatusFl'] == 'y' || $bdList['cfg']['bdReplyStatusFl'] == 'y') { ?>
                    <th>답변상태</th>
                    <td class="width-xl">
                        <div class="form-inline">
                            <select name="replyStatus" class="form-control">
                                <option value="">=전체=</option>
                                <?php foreach ($board::REPLY_STATUS_LIST as $key => $val) { ?>
                                    <option value="<?= $key ?>" <?php if ($req['replyStatus'] == $key) echo 'selected' ?>><?= $val ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </td>
                <?php } ?>
                <?php if ($bdList['cfg']['bdGoodsPtFl'] == 'y') { ?>
                    <th>평점</th>
                    <td>
                        <select name="goodsPt" class="form-control">
                            <option value="">=전체=</option>
                            <?php
                            for ($i = 1; $i < 6; $i++) { ?>
                                <option
                                        value="<?= $i ?>" <?php if ((string)$i == (string)$req['goodsPt']) echo 'selected' ?>><?= $i ?></option>
                            <?php } ?>
                        </select>
                    </td>
                <?php } ?>
            </tr>
            <?php if ($bdList['cfg']['bdEventFl'] == 'y') { ?>
                <tr class="js-if-bdEventFl">
                    <th>이벤트 기간</th>
                    <td colspan="3">
                        <div class="form-inline">
                            <div class="input-group js-datepicker">
                                <input name="rangEventDate[]" class="form-control width-xs" type="text"
                                       value="<?= gd_isset($req['rangEventDate'][0]) ?>"
                                       placeholder="수기입력 가능">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                            ~
                            <div class="input-group js-datepicker">
                                <input name="rangEventDate[]" class="form-control width-xs" type="text"
                                       value="<?= gd_isset($req['rangEventDate'][1]) ?>"
                                       placeholder="수기입력 가능">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <select class="form-control" name="searchField">
                            <option value="subject" <?php if ($req['searchField'] == 'subject') echo 'selected' ?>>
                                제목
                            </option>
                            <option
                                value="writerNick" <?php if ($req['searchField'] == 'writerNick') echo 'selected' ?>>닉네임
                            </option>
                            <option
                                value="writerNm" <?php if ($req['searchField'] == 'writerNm') echo 'selected' ?>>이름
                            </option>
                            <option
                                value="writerId" <?php if ($req['searchField'] == 'writerId') echo 'selected' ?>>아이디
                            </option>
                            <option
                                value="contents" <?php if ($req['searchField'] == 'contents') echo 'selected' ?>>내용
                            </option>
                            <option
                                value="subject_contents" <?php if ($req['searchField'] == 'subject_contents') echo 'selected' ?>>
                                제목+내용
                            </option>
                            <option class="js-if-bdGoodsFl"
                                value="goodsNm" <?php if ($req['searchField'] == 'goodsNm') echo 'selected' ?>>
                                상품명
                            </option>
                            <option class="js-if-bdGoodsFl"
                                value="goodsNo" <?php if ($req['searchField'] == 'goodsNo') echo 'selected' ?>>
                                상품코드
                            </option>
                            <option class="js-if-bdGoodsFl"
                                value="goodsCd" <?php if ($req['searchField'] == 'goodsCd') echo 'selected' ?>>
                                자체상품코드
                            </option>

                        </select>

                        <input name="searchWord" value="<?= gd_isset($req['searchWord']) ?>"
                               class="form-control width-3xl">
                    </div>
                </td>
            </tr>
        </table>
        <div class="table-btn">
            <input type="submit" value="검색" class="btn btn-lg btn-black">
        </div>
</form>

<div class="table-header">
    <div class="pull-left">
        검색&nbsp;<strong><?=number_format($bdList['cnt']['search']) ?></strong>개/
        전체&nbsp;<strong><?= number_format($bdList['cnt']['total']) ?></strong>개
    </div>
    <div class="pull-right">
        <div class="form-inline">
            <?= gd_select_box('sort', 'sort', $bdList['sort'], null, $req['sort']); ?>
            <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum',10)); ?>
        </div>
    </div>
</div>

<form name="frmList" id="frmList" action="article_ps.php" method="post">
    <input type="hidden" name="bdId" value="<?= $bdList['cfg']['bdId'] ?>">
    <input type="hidden" name="mode" value="delete">
    <table id="listTbl" cellpadding="0" cellspacing="0" class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width-2xs"><input type="checkbox" class="js-checkall" data-target-name="sno"></th>
            <th class="width-2xs">번호</th>
            <?php if ($bdList['cfg']['bdGoodsFl'] === 'y' && $bdList['cfg']['bdGoodsType'] === 'goods') { ?>
                <th class="width-sm">상품이미지</th>
            <?php } ?>
            <th>제목</th>
            <th class="width-sm">작성자</th>
            <th class="width-sm">작성일</th>
            <th class="width-2xs">조회</th>
            <?php  if ($bdList['cfg']['bdAnswerStatusFl'] == 'y' || $bdList['cfg']['bdReplyStatusFl'] == 'y') { ?>
                <th class="width-sm">답변상태</th>
            <?php } ?>
            <?php if ($bdList['cfg']['bdRecommendFl'] == 'y') { ?>
                <th class="width-2xs"> 추천</th>
            <?php } ?>
            <?php if ($bdList['cfg']['bdGoodsPtFl'] == 'y') { ?>
                <th class="width-2xs">평점</th>
            <?php } ?>

            <th class="width-sm">수정/답변</th>
        </tr>
        </thead>
        <?php
        if (gd_array_is_empty($bdList['list']) === false) {
            foreach ($bdList['list'] as $val) {
                if ($bdList['cfg']['bdGoodsFl'] === 'y' && $bdList['cfg']['bdGoodsType'] === 'goods') {
                    //게시글 관리에서 노출되는 상품이미지 항목의 노이미지 노출을 위해 imageStorage가 없는 경우 local 셋팅
                    if(!gd_isset($val['imageStorage'])){
                        $val['imageStorage'] = 'local';
                    }
                }
                ?>
                <tr class="center">
                    <td><input name="sno[]" type="checkbox" value="<?= $val['sno'] ?>" <?php if($val['auth']['delete'] != 'y') echo 'disabled'?>></td>
                    <td class="font-num">
                        <?php if ($val['isNotice'] == 'y') {
                            echo gd_isset($bdList['cfg']['bdIconNotice']);
                        } else {
                            echo $val['articleListNo'] ;
                        } ?>
                    </td>
                    <?php if ($bdList['cfg']['bdGoodsFl'] === 'y' && $bdList['cfg']['bdGoodsType'] === 'goods') { ?>
                        <td><?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?></td>
                    <?php } ?>
                    <td align="left">
                        <?= $val['gapReply'] ?><?php if ($val['groupThread'] != '')
                            echo gd_isset($bdList['cfg']['bdIconReply']); ?>
                        <a class="<?php if ($val['isNotice'] == 'y') {
                            echo 'notice';
                        } ?>"
                           href="javascript:btnView('<?= $bdList['cfg']['bdId'] ?>',<?= $val['sno'] ?>);">
                            <?php
                            if ($val['category']) {
                                echo '[' . $val['category'] . ']';
                            } ?>
                            <?= $val['subject']; ?>
                        </a>
                        <?php if ($bdList['cfg']['bdMemoFl'] == 'y' && $val['memoCnt']) {
                            echo '&nbsp;<span class="memoCnt">[' . gd_isset($val['memoCnt']) . ']</span>';
                        } ?>
                        <?php if ($val['isSecret'] == 'y') {
                            echo gd_isset($bdList['cfg']['bdIconSecret']);
                        } ?>
                        <?php if ($val['isNew'] == 'y')
                            echo gd_isset($bdList['cfg']['bdIconNew']); ?>
                        <?php if ($val['isHot'] == 'y')
                            echo gd_isset($bdList['cfg']['bdIconHot']); ?>
                        <?php if ($val['isFile'] == 'y')
                            echo gd_isset($bdList['cfg']['bdIconFile']); ?>
                        <img src="/admin/gd_share/img/icon_grid_open.png" alt="팝업창열기" class="hand mgl5" border="0" onclick="javascript:articleViewPopup('<?= $val['sno'] ?>');">
                    </td>
                    <td><?php if ($val['memNo'] > 0 && !gd_is_provider()) {
                            echo "<a   class='js-layer-crm hand' data-member-no='" . $val['memNo'] . "' >";
                            $aTagClose = '</a>';
                        } ?>
                        <?= $val['writer'] . $aTagClose ?>
                    </td>
                    <td><?= $val['regDate'] ?></td>
                    <td><?= number_format($val['hit']) ?></td>
                    <?php  if ($bdList['cfg']['bdAnswerStatusFl'] == 'y' || $bdList['cfg']['bdReplyStatusFl'] == 'y') { ?>
                        <td>
                            <?= $val['replyStatusText'] ?>
                        </td>
                    <?php } ?>
                    <?php if ($bdList['cfg']['bdRecommendFl'] == 'y') { ?>
                        <td class="width-2xs">  <?= gd_isset($val['recommend'], 0) ?></td>
                    <?php } ?>

                    <?php if ($bdList['cfg']['bdGoodsPtFl'] == 'y') { ?>
                        <td class="width-2xs"><?= gd_isset($val['goodsPt'], 0) ?></td>
                    <?php } ?>
                    <td>
                        <?php if($val['auth']['modify'] == 'y') {?>
                        <a onclick="btnModifyWrite('<?= $req['bdId'] ?>', <?= $val['sno'] ?>);"
                           class="btn btn-white btn-sm">수정</a>
                        <?php }?>
                        <?php if(!$val['adminFl'] && $val['auth']['reply'] == 'y') {?>
                        <a onclick="btnReplyWrite('<?= $req['bdId'] ?>',<?= $val['sno'] ?>);"
                           class="btn  btn-white btn-sm">답변</a>
                        <?php }?>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="7" height="50" class="no-data">게시물이 없습니다.</td>
            </tr>
        <?php } ?>
    </table>

    <div class="table-action">
        <div class="pull-left form-inline">
            <span class="action-title">선택한 게시글</span>
            <button type="submit" class="btn btn-white js-btn-delete"/>
            삭제</button>
        </div>

        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearch" data-target-list-form="frmList" data-target-list-sno="sno" data-search-count="<?=$bdList['cnt']['search']?>" data-total-count="<?=$bdList['cnt']['total']?>">엑셀다운로드</button>
        </div>
    </div>

    <div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog"
         aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">엑셀 다운로드</h4>
                </div>

                <div class="modal-body">
                    <p> 다운받을 항목을 선택해주세요.</p>
                    <select name="excelDownloadType" class="form-control">
                        <option value="1">게시글 전체 다운로드</option>
                        <option value="2">선택한 게시글다운로드</option>
                        <option value="3">댓글 전체 다운로드</option>
                        <option value="4">선택한 댓글 다운로드</option>
                    </select>
                    <!--  <a href="./board_excel.php?bdId=<?php /*echo $bdList['cfg']['bdId']*/ ?>">다운로드</a>-->
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-red" onclick="excelDownload(this.form)">확인
                    </button>
                    <button type="button" class="btn btn-white" data-dismiss="modal">취소</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function writeArticle(sno) {
            frame_popup("article_write.php?bdId=<?=$bdList['cfg']['bdId']?>&mode=write&sno=" + ((sno) ? sno : ""), "<?=$bdList['cfg']['bdNm']?> 게시판", 'wide-lg');
        }

        function replyArticle(sno) {
            frame_popup("article_write.php?bdId=<?=$bdList['cfg']['bdId']?>&mode=reply&sno=" + ((sno) ? sno : ""), "<?=$bdList['cfg']['bdNm']?> 게시판", 'wide-xlg');
        }

        function modifyArticle(sno, hasParent) {
            if (hasParent) {
                frame_popup("article_write.php?bdId=<?=$bdList['cfg']['bdId']?>&mode=modify&sno=" + ((sno) ? sno : ""), "<?=$bdList['cfg']['bdNm']?> 게시판", 'wide-xlg');
            }
            else {
                frame_popup("article_write.php?bdId=<?=$bdList['cfg']['bdId']?>&mode=modify&sno=" + ((sno) ? sno : ""), "<?=$bdList['cfg']['bdNm']?> 게시판", 'wide-lg');
            }
        }

        function view(bdId, sno) {
            location.href = "article_view.php?bdId=" + bdId + "&sno=" + sno;
        }
        function articleViewPopup(sno) {
            window.open("../board/article_view.php?bdId=<?=$bdList['cfg']['bdId']?>&popupMode=yes&mode=reply&sno=" + sno, "<?=$bdList['cfg']['bdNm']?> 게시판", 'width=1200,height=750,scrollbars=yes,resizable=yes');
        }

        $(document).ready(function () {
            $('select[name=\'pageNum\']').change(function () {
                $('#frmSearch').submit();
            });

            $('select[name=\'sort\']').change(function () {
                $('#frmSearch').submit();
            });

            $('select[name=bdId]').bind('change',function(){
                location.href='article_list.php?bdId='+$(this).val();
            })

            $('#frmList').validate({
                ignore: ':hidden',
                dialog: false,
                submitHandler: function (form) {
                    var bdReplyDelFl = '<?=$bdList['cfg']['bdReplyDelFl']?>';
                    var confirmMsg = '';
                    if (bdReplyDelFl == 'reply') {
                        confirmMsg = '<br> 해당 글의 답변글도 함께 삭제되며\n\r';
                    }
                    form.target = 'ifrmProcess';
                    dialog_confirm('선택한 글을 삭제하시겠습니까?\n\r ' + confirmMsg + '영구 삭제되어 복원 불가능합니다.', function (result) {
                        if (result) {
                            form.submit();
                        }
                    });
                },
                rules: {
                    'sno[]': {
                        required: true
                    }
                },
                messages: {
                    'sno[]': {
                        required: '선택하신 글이 없습니다.'
                    },

                },
            });
        });

        function excelDownload(frm) {
            var bdId = '<?=$bdList['cfg']['bdId']?>';
            var downloadtype = frm.excelDownloadType.value;
            var sno = [];
            $("input[name='sno[]']:checked").each(function () {
                sno.push($(this).val());
            });

            var snos = sno.join('-');
            if (downloadtype == '1' || downloadtype == '2') {
                location.href = './board_excel.php?downloadtype='+downloadtype+'&bdId=' + bdId + '&snos=' + encodeURI(snos);
            }
            else if (downloadtype == '3' || downloadtype == '4') {
                location.href = './memo_excel.php?downloadtype='+downloadtype+'&bdId=' + bdId + '&snos=' + encodeURI(snos);
            }
        }

    </script>
    <div class="center"><?= $bdList['pagination'] ?></div>
</form>
</div>
