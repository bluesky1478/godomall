<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?>
        <!--    <small>게시물을 수정하고 관리합니다.</small>-->
    </h3>
</div>

<div class="table-title">게시글 보기</div>
<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tr>
        <th>대상</th>
        <td><?= $data['scmTarget'] ?></td>
    </tr>
    <tr>
        <th>제목</th>
        <td><?= $data['subject'] ?></td>
    </tr>
    <tr>
        <th>카테고리</th>
        <td><?= $data['categoryText'] ?></td>
    </tr>
    <tr>
        <th>작성자</th>
        <td><?= $data['writer'] ?></td>
    </tr>
    <tr>
        <th>파일첨부</th>
        <td><?= $data['uploadFileList'] ?></td>
    </tr>
    <tr>
        <th>내용</th>
        <td><?= $data['contents'] ?></td>
    </tr>
    <tr>
        <th>작성일</th>
        <td>
            <?= $data['regDt'] ?>
        </td>
    </tr>
</table>

<div class="center">
    <?php if ($data['isNotice'] == 'n') { ?>
        <a href="scm_board_register.php?mode=reply&sno=<?= $req['sno'] ?>" class="btn btn-white">답변</a>
    <?php } ?>
    <?php if ($data['auth'] == 'y') { ?>
        <a href="scm_board_register.php?mode=modify&sno=<?= $req['sno'] ?>" class="btn btn-white">수정</a>
        <a href="javascript:articleDelete('<?=$req['sno']?>')" class="btn btn-white">삭제</a>
    <?php } ?>
    <a href="scm_board_list.php?<?= $queryString ?>" class="btn btn-white">리스트</a>
</div>

<div style="padding-top:20px">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col>
            <col class="width-sm"/>
            <col class="width-md"/>
        </colgroup>
        <tbody>
        <?php if ($nextData = $data['relationList']['nextData']) { ?>
            <tr>
                <td>다음글</td>
                <td><a href="scm_board_view.php?sno=<?= $nextData['sno'] ?>"><?= $nextData['subject'] ?></a></td>
                <td><?= $nextData['managerId'] ?>(<?= $nextData['managerNm'] ?>)</td>
                <td><?= $nextData['regDt'] ?></td>
            </tr>
        <?php } ?>

        <?php if ($groupData = $data['relationList']['groupData']) {
            foreach ($groupData as $val) {
                $startBoldTag = $closeBoldTag = '';
                $info = '-';
                if ($req['sno'] == $val['sno']) {
                    $startBoldTag = '<b>';
                    $closeBoldTag = '</b>';
                    $info = '현재글';
                }
                ?>
                <tr>
                    <td><?= $info ?></td>
                    <td><a href="scm_board_view.php?sno=<?= $val['sno'] ?>">
                            <?= $val['replyIcon'] . $startBoldTag . $val['subject'] . $closeBoldTag ?></a></td>
                    <td><?= $val['managerId'] ?>(<?= $val['managerNm'] ?>)</td>
                    <td><?= $val['regDt'] ?></td>
                </tr>
                <?php
            }
        } ?>

        <?php if ($prevData = $data['relationList']['prevData']) { ?>
            <tr>
                <td>이전글</td>
                <td><a href="scm_board_view.php?sno=<?= $prevData['sno'] ?>"><?= $prevData['subject'] ?></a></td>
                <td><?= $prevData['managerId'] ?>(<?= $prevData['managerNm'] ?>)</td>
                <td><?= $prevData['regDt'] ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<script>
    function articleDelete(sno) {
        dialog_confirm('정말 삭제하시겠습니까?', function (result) {
            if(result) {
                ifrmProcess.location.href="scm_board_ps.php?mode=delete&sno="+sno;
            }
        });

    }
</script>
