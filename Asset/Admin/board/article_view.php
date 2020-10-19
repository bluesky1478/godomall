<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?>
    </h3>
    <?php ?>
</div>
<?php include "_article_detail.php" ?>
<?php if ($bdView['cfg']['bdReplyStatusFl'] == 'y') { ?>
    <div class="table-title gd-help-manual">게시글답변</div>
    <table class="table table-cols">
        <col class="width-md"/>
        <col/>
        <tr>
            <th>답변 작성자</th>
            <td>
                <?= $bdView['data']['answerWriter'] ?>
            </td>
        </tr>

        <tr>
            <th>답변 상태</th>
            <td>
                <?= $bdView['data']['replyStatusText'] ?>
            </td>
        </tr>

        <tr>
            <th>답변 제목</th>
            <td>
                <?= gd_isset($bdView['data']['answerSubject'], '-') ?>
            </td>
        </tr>
        <tr>
            <th>답변 내용</th>
            <td>
                <?= gd_isset($bdView['data']['workedAnswerContents'], '-'); ?>
            </td>
        </tr>
    </table>
<?php } ?>
<div class="text-center">
    <?php if($req['popupMode'] !='yes') { // CRM 팝업모드가 아닐 경우 ?>
    <a href="javascript:btnList('<?= $req['bdId'] ?>')" class="btn btn-white">목록</a>
    <?php } ?>
    <?php if($bdView['data']['auth']['modify'] == 'y'){?>
    <a href="javascript:btnModifyWrite('<?= $req['bdId'] ?>','<?= $req['sno'] ?>')" class="btn btn-white">수정</a>
    <?php }?>
    <?php if($bdView['data']['auth']['reply'] == 'y'){?>
    <a href="javascript:btnReplyWrite('<?= $req['bdId'] ?>','<?= $req['sno'] ?>')" class="btn btn-white">답변</a>
    <?php }?>
    <?php if($bdView['data']['auth']['delete'] == 'y'){?>
    <a href="javascript:btnDelete('<?= $req['bdId'] ?>','<?= $req['sno'] ?>', '<?=$req['popupMode'] ?>', '<?=$bdView['cfg']['bdReplyDelFl']?>')" class="btn btn-white js-btn-delete">삭제</a>
    <?php }?>
</div>

<style>
    textarea {
        width: 85% !important;;
        height: 70px !important;;
    }

    .js-btn-modify, .js-btn-reply, .js-btn-memo-save {
        margin-left: 10px;
        height: 70px;
        width: 13%;
    }
</style>
