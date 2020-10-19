<script type="text/javascript">
    $(document).ready(function () {
        $('#frmList').validate({
            dialog: false,
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                dialog_confirm('선택한 게시판을 삭제하시겠습니까?\n\r영구 삭제되어 복원 불가능합니다.', function (result) {
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
                    required: '선택한 게시판이 없습니다.'
                },

            },
        });

        // 등록
        $('.js-register').click(function () {
            location.href = 'board_register.php';
        });
        
        // 관리자 접속 후 사용자화면 클릭했을경우 fl추가
        $('.user-board').click(function (){
            $.post('./article_ps.php', {
                'mode': 'userBoardChk',
                'fl': $(this).data('fl')
            }, function (data) {
                console.log(data);
            });
        });
    });
</script>

<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?>
        <small>생성된 게시판을 수정하고 관리합니다.</small>
    </h3>
    <input type="button" class="js-register btn btn-red" type="button" value="게시판 만들기" />
</div>

<div class="table-title gd-help-manual">게시판 검색</div>
<form id="frmSearchBase" method="get" class="js-form-enter-submit">
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tr>
                <th>검색어</th>
                <td>
                    <div class="form-inline">
                        <?=gd_select_box('key', 'key', array( 'bdId' => '아이디', 'bdNm' => '이름'), '', gd_isset($search['key'])); ?>
                        <input type="text" name="keyword" value="<?=gd_isset($search['keyword']); ?>"
                               class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>유형</th>
                <td>
                    <label class="checkbox-inline"><input type="checkbox" name="boardKind[all]" value="y" <?=$checked['boardKind']['all']?>>전체</label>
                    <label class="checkbox-inline"><input type="checkbox" name="boardKind[default]" value="y" <?=$checked['boardKind']['default']?>>일반형</label>
                    <label class="checkbox-inline"><input type="checkbox" name="boardKind[gallery]" value="y" <?=$checked['boardKind']['gallery']?>>갤러리형</label>
                    <label class="checkbox-inline"><input type="checkbox" name="boardKind[event]" value="y" <?=$checked['boardKind']['event']?>>이벤트형</label>
                    <label class="checkbox-inline"><input type="checkbox" name="boardKind[qa]" value="y" <?=$checked['boardKind']['qa']?>>1:1문의형</label>
                </td>
            </tr>

        </table>
        <div class="table-btn">
            <input type="submit" value="검색" class="btn btn-lg btn-black">
        </div>
    </div>
</form>
<div class="table-header">
    <div class="pull-left">
        검색
        <strong><?=$cnt['search'] ?></strong>개 /
        총 <strong><?=$cnt['total']?></strong>개
    </div>
</div>
<form id="frmList" action="../board/board_ps.php" method="post">
    <input type="hidden" name="mode" value="delete">
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width-2xs"><input type="checkbox" class="js-checkall" data-target-name="sno"></th>
            <th class="width-2xs">번호</th>
            <th class="width-md">아이디</th>
            <th >이름</th>
            <th class="width-2xs">신규게시글</th>
            <th class="width-2xs">전체게시글</th>
            <th class="width-2xs">미답변</th>
            <th class="width-xs">유형</th>
            <th>PC쇼핑몰 스킨</th>
            <th>모바일쇼핑몰 스킨</th>
            <th>URL복사</th>
            <th class="width-2xs">사용자화면</th>
            <th class="width-2xs">관리자화면</th>
            <th class="width-2xs">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (isset($data) && is_array($data)) {
            foreach ($data as $val) {
                $bdQuestionCnt = $val['bdQuestionCnt'];
                if(is_numeric($bdAnswerCnt)) {
                    $bdQuestionCnt = number_format($val['bdQuestionCnt ']);
                }
                ?>
                <tr class="center">
                    <td>
                        <input name="sno[]" type="checkbox" value="<?= $val['sno'] ?>" <?php if ($val['bdBasicFl'] == 'y') echo 'disabled' ?>>
                        <input name="seoTagSno[<?= $val['sno'] ?>]" type="hidden" value="<?php echo $val['seoTagSno']; ?>">
                    </td>
                    <td ><?=number_format($val['listNo']); ?></td>
                    <td ><a
                            href="./board_register.php?sno=<?=$val['sno']; ?>" class="btn-link"><?=$val['bdId']; ?></a>
                    </td>
                    <td><a href="./board_register.php?sno=<?=$val['sno']; ?>"><?=$val['bdNm']; ?></a>
                    </td>
                    <td ><?=number_format($val['bdNewListCnt']); ?></td>
                    <td ><?=number_format($val['bdListCnt']); ?></td>
                    <td ><?=$bdQuestionCnt; ?></td>
                    <td><?=$val['bdKindStr']; ?></td>
                    <td>
                        <?php if($gGlobal['isUse']) {?>
                            <?php foreach($gGlobal['useMallList'] as $gVal) {
                                $domainPostfix = $gVal['domainFl'] == 'kr' ? '' : ucfirst($gVal['domainFl']);
                                if(!$val['theme'.$domainPostfix.'Sno'] || empty($val['theme'.$domainPostfix.'Nm'])){
                                    continue;
                                }?>

                                <div style="text-align: left;padding:0 0 10px 10px"><span class="flag flag-16 flag-<?= $gVal['domainFl'] ?>"></span>
                                    <a href="board_theme_register.php?sno=<?=$val['theme'.$domainPostfix.'Sno']?>" target="_blank"><?=$val['theme'.$domainPostfix.'Nm']?></a>
                                </div>
                            <?php }?>
                        <?php }
                        else {?>
                        <a href="board_theme_register.php?sno=<?=$val['themeSno'] ?>" target="_blank"><?=$val['themeNm']; ?></a>
                        <?php }?>
                    </td>
                    <td>
                <?php if($gGlobal['isUse']) {?>
                    <?php foreach($gGlobal['useMallList'] as $gVal) {
                        $domainPostfix = $gVal['domainFl'] == 'kr' ? '' : ucfirst($gVal['domainFl']);
                        if(!$val['mobileTheme'.$domainPostfix.'Sno'] || empty($val['mobileTheme'.$domainPostfix.'Nm'])){
                            continue;
                        }?>
                        <div style="text-align: left;padding:0 0 10px 10px"><span class="flag flag-16 flag-<?= $gVal['domainFl'] ?>"></span>
                            <a href="board_theme_register.php?sno=<?=$val['mobileTheme'.$domainPostfix.'Sno']?>" target="_blank"><?=$val['mobileTheme'.$domainPostfix.'Nm']?></a>
                        </div>
                    <?php }?>
                    <?php }
                    else {?>
                            <a href="board_theme_register.php?sno=<?=$val['mobileThemeSno'] ?>" target="_blank"><?=$val['mobileThemeNm']; ?></a>
                    <?php }?>

                    </td>
                    <td>
                        <button type="button" data-clipboard-text="<?=$val['pageUrl'] ?>" class="js-clipboard btn btn-white btn-sm width-3xs" title="<?=$val['bdNm']; ?>">
                            PC
                        </button>
                        <button type="button" data-clipboard-text="<?=$val['pageMobileUrl'] ?>" class="js-clipboard btn btn-white btn-sm width-3xs" title="<?=$val['bdNm']; ?>">
                            모바일
                        </button>
                    </td>
                    <td>
                        <a href="<?=$val['pageUrl'] ?>" target="_blank" data-fl="user" class="btn btn-white btn-sm user-board">보기</a>
                    </td>
                    <td><a href="./article_list.php?bdId=<?=$val['bdId'] ?>" class="btn btn-white btn-sm">관리</a></td>
                    <td><a  href="./board_register.php?sno=<?=$val['sno']; ?>" class="btn btn-white btn-sm">수정</a></td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left form-inline">
            <span class="action-title">선택한 게시판</span>
            <button type="submit" class="btn btn-white" />삭제</button>
        </div>
    </div>
    <div class="center"><?=$pagination?></div>
</form>
