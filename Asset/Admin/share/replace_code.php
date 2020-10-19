<form id="formPopup" name="formPopup">
    <div class="page-header js-affix affix-top" style="width: 100%;">
        <h3>치환코드 보기</h3>
    </div>
    <table class="table table-rows">
        <colgroup>
            <col class="width-4xs">
            <col class="width-2xs">
            <col class="width-2xs">
        </colgroup>
        <thead>
        <tr>
            <th>번호</th>
            <th>치환코드</th>
            <th>설명</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $index = 1;
        $template = [];
        foreach ($defineCode as $key => $value) {
            $template[] = '<tr><td>' . $index . '</td><td>' . $key . '</td><td>' . $value['desc'] . '</td></tr>';
            $index++;
        }
        echo join('', $template);
        ?>
        </tbody>
    </table>
    <div class="table-btn">
        <button type="button" id="btnClose" class="btn btn-sm btn-black">닫기</button>
    </div>
</form>
<script type="text/javascript">
    <!--
    $('#btnClose').click(function () {
        self.close();
    });
    //-->
</script>
