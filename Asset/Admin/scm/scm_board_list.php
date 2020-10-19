<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 등록
        $('.js-register').bind('click', function () {
            location.href = './scm_board_register.php';
        });

        //삭제
        $('.js-btn-delete').bind('click', function () {
            if ($('input[name="sno[]"]:checked').length == 0) {
                alert('삭제할 게시글을 선택해주세요.');
            }
            else {
                dialog_confirm('삭제하시겠습니까? \n 이 게시글에 달린 답변글도 삭제됩니다.',function(data){
                    if(data){
                        $('#frmList').submit();
                    }
                });


            }
        })

    });

    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode,
        };

        if (typeStr == 'scm') {
            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }
    //-->
</script>
<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <input type="button" id="btnWrite" class="btn btn-red js-register" value="게시글등록"/>
</div>
<div class="table-title">공급사 게시판 검색</div>
<form id="frmSearch" name="frmSearch" method="get" class="js-form-enter-submit">
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <?php if(gd_is_provider() === false) {?>
            <tr>
                <th>공급사 구분</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="all" <?= gd_isset($search['checked']['scmFl']['all']); ?> onclick="$('#scmLayer').html('');"/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="n" <?= gd_isset($search['checked']['scmFl']['n']); ?> onclick="$('#scmLayer').html('')" ;/>본사
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="y" <?= gd_isset($search['checked']['scmFl']['y']); ?> onclick="layer_register('scm','checkbox')"/>공급사
                    </label>
                    <label class="radio-inline">
                        <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm','checkbox')">공급사 선택</button>
                    </label>
                    <div id="scmLayer" class="selected-btn-group width100p <?=$search['scmFl'] == 'y' && !empty($search['scmNo']) ? 'active' : ''?>">
                        <?php
                        if ($search['scmFl'] == 'y') {
                            if ($search['scmNo']) {
                                foreach ($search['scmNo'] as $k => $v) { ?>
                                    <span id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                <span class="btn"><?= $search['scmNoNm'][$k] ?></span>
                                <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>">삭제</button>
                                </span>
                                <?php }
                            }
                        } ?>
                    </div>
                </td>
            </tr>
            <?php }?>
            <tr>
                <th>분류</th>
                <td class="contents" colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('category', 'category', $category, null, gd_isset($search['category']), '=전체='); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('selectField', 'selectField', $search['searchSelectField'], null, $search['searchField'], '=통합검색='); ?>
                        <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?= $search['searchDate'][0]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?= $search['searchDate'][1]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        <?= gd_search_date($search['searchPeriod'], 'searchDate', false) ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>
</form>
<div class="table-header">
    <div class="pull-left">검색<strong><?= number_format($data['searchCnt']); ?></strong>개 / 전체
        <strong><?= number_format($data['totalCnt']); ?></strong>개
    </div>
</div>
<form id="frmList" action="scm_board_ps.php" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="delete">
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width5p center"><input type="checkbox" class="js-checkall" data-target-name="sno"></th>
            <th class="width5p">번호</th>
            <th class="width10p">공급사명</th>
            <th class="width10p">카테고리</th>
            <th>제목</th>
            <th class="width10p">작성자</th>
            <th class="width10p">작성일</th>
            <th class="width5p">답변</th>
            <th class="width5p">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (is_array(gd_isset($data['noticeList']))) {
            foreach ($data['noticeList'] as $key => $val) {
                ?>
                <tr align="center">
                    <td><?php if ($val['auth'] == 'y') { ?><input type="checkbox" name="sno[]" value="<?= $val['sno']; ?>"/><?php } ?></td>
                    <td><?=$val['iconNotice']; ?></td>
                    <td><?= $val['companyNm'] ? $val['companyNm'] : '본사' ?></td>
                    <td><?= $val['categoryText'] ?></td>
                    <td class="left">
                        <a href="scm_board_view.php?sno=<?= $val['sno'] ?>&<?= $queryString ?>">
                            <?= $val['subject']; ?>
                        </a>
                    </td>
                    <td><?= $val['managerId'] ?>(<?= $val['managerNm'] ?>)<?= $val['deleteText'] ?></td>
                    <td><?= gd_date_format('Y-m-d', $val['regDt']); ?></td>
                    <td>
                        <?php if ($val['isNotice'] == 'n') { ?>
                            <a href="scm_board_register.php?mode=reply&sno=<?= $val['sno'] ?>" class="btn btn-white btn-xs">답변</a>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($val['auth'] == 'y') { ?>
                            <a href="scm_board_register.php?sno=<?= $val['sno'] ?>&mode=modify" class="btn btn-white btn-xs">수정</a>
                        <?php } ?>
                    </td>
                </tr>
                <?php
            }
        }
        ?>


        <?php
        if (gd_isset($data['list'])) {
            foreach ($data['list'] as $key => $val) {
                ?>
                <tr align="center">
                    <td><?php if ($val['auth'] == 'y') { ?><input type="checkbox" name="sno[]" value="<?= $val['sno']; ?>"/><?php } ?></td>
                    <td><?= number_format($val['listNo']); ?></td>
                    <td><?= $val['companyNm'] ? $val['companyNm'] : '본사' ?></td>
                    <td><?= $val['categoryText'] ?></td>
                    <td class="left">
                        <?= $val['iconNotice'] ?>
                        <?= $val['iconReply'] ?>
                        <a href="scm_board_view.php?sno=<?= $val['sno'] ?>&<?= $queryString ?>">
                            <?= $val['subject']; ?>
                            <?= $val['iconFile'] ?>
                        </a>
                    </td>
                    <td><?= $val['managerId'] ?>(<?= $val['managerNm'] ?>)</td>
                    <td><?= gd_date_format('Y-m-d', $val['regDt']); ?></td>
                    <td>
                        <?php if ($val['isNotice'] == 'n') { ?>
                            <a href="scm_board_register.php?mode=reply&sno=<?= $val['sno'] ?>" class="btn btn-white btn-xs">답변</a>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($val['auth'] == 'y') { ?>
                            <a href="scm_board_register.php?sno=<?= $val['sno'] ?>&mode=modify" class="btn btn-white btn-xs">수정</a>
                        <?php } ?>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="9">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left form-inline">
            <span class="action-title">선택한 게시글</span>
            <button type="button" class="btn btn-white js-btn-delete" />삭제</button>
        </div>
    </div>

</form>

<div class="center"><?= $data['pagination'] ?></div>
