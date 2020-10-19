<form id="frmCoupon" name="frmCoupon" action="visit_ps.php" method="post" class="content_form">
    <input type="hidden" name="mode" value="<?= $mode; ?>"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small>방문통계 설정을 하실 수 있습니다.</small>
        </h3>
        <input type="submit" value="저장하기" class="btn btn-red"/>
    </div>

    <h5>■방문통계 방문횟수 유지 시간 설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>
                방문통계 방문횟수 유지 시간 설정
            </th>
            <td><input type="text" name="visitNumberTime" value="<?= $data['visitNumberTime']; ?>" class="width-lg">초
            </td>
        </tr>
        </tbody>
    </table>

    <h5>■방문통계 신규방문자 유지 시간 설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>
                방문통계 신규방문자 유지 시간 설정
            </th>
            <td><input type="text" name="visitNewCountTime" value="<?= $data['visitNewCountTime']; ?>" class="width-lg">초
            </td>
        </tr>
        </tbody>
    </table>

    <h5>■방문통계 제외 페이지 설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>
                방문통계 제외 페이지 설정
            </th>
            <td id="td_except">
                <?php
                if ($data['exceptPage']) {
                    $num = 0;
                    foreach ($data['exceptPage'] as $key => $val) {
                        $num++;
                        ?>
                        <div>
                            <input type="text" name="exceptPage[]" value="<?= $val; ?>" class="width-lg">
                            <?php
                            if ($num > 1) {
                                ?>
                                <button type="button" name="delExcept" class="btn_except_del">삭제</button>
                                <?php
                            } else {
                                ?>
                                <button type="button" name="addExcept" id="btn_except_add">추가</button>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div>
                        <input type="text" name="exceptPage[]" value="" class="width-lg">
                        <button type="button" name="addExcept" id="btn_except_add">추가</button>
                    </div>
                    <?php
                }
                ?>
            </td>
        </tr>
        </tbody>
    </table>

    <h5>■유입경로 설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>
                유입경로 설정
            </th>
            <td id="td_inflow">
                <?php
                if ($data['inflowAgent']) {
                    $num = 0;
                    foreach ($data['inflowAgent'] as $key => $val) {
                        $num++;
                        ?>
                        <div>
                            <input type="text" name="agentInflowCode[]" value="<?= $key; ?>" class="width-lg"> ->
                            <input type="text" name="inflowCode[]" value="<?= $val; ?>" class="width-lg">
                            <?php
                            if ($num > 1) {
                                ?>
                                <button type="button" name="delInflow" class="btn_inflow_del">삭제</button>
                                <?php
                            } else {
                                ?>
                                <button type="button" name="addInflow" id="btn_inflow_add">추가</button>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div>
                        <input type="text" name="agentInflowCode[]" value="" class="width-lg"> ->
                        <input type="text" name="inflowCode[]" value="" class="width-lg">
                        <button type="button" name="addInflow" id="btn_inflow_add">추가</button>
                    </div>
                    <?php
                }
                ?>
            </td>
        </tr>
        </tbody>
    </table>

    <h5>■OS 설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>
                OS 설정
            </th>
            <td id="td_os">
                <?php
                if ($data['osAgent']) {
                    $num = 0;
                    foreach ($data['osAgent'] as $key => $val) {
                        $num++;
                        ?>
                        <div>
                            <input type="text" name="agentOsCode[]" value="<?= $key; ?>" class="width-lg"> ->
                            <input type="text" name="osCode[]" value="<?= $val; ?>" class="width-lg">
                            <?php
                            if ($num > 1) {
                                ?>
                                <button type="button" name="delOs" class="btn_os_del">삭제</button>
                                <?php
                            } else {
                                ?>
                                <button type="button" name="addOs" id="btn_os_add">추가</button>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div>
                        <input type="text" name="agentOsCode[]" value="" class="width-lg"> ->
                        <input type="text" name="osCode[]" value="" class="width-lg">
                        <button type="button" name="addOs" id="btn_os_add">추가</button>
                    </div>
                    <?php
                }
                ?>
            </td>
        </tr>
        </tbody>
    </table>

    <h5>■Browser 설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>
                Browser 설정
            </th>
            <td id="td_browser">
                <?php
                if ($data['browserAgent']) {
                    $num = 0;
                    foreach ($data['browserAgent'] as $key => $val) {
                        $num++;
                        ?>
                        <div>
                            <input type="text" name="agentBrowserCode[]" value="<?= $key; ?>" class="width-lg"> ->
                            <input type="text" name="browserCode[]" value="<?= $val; ?>" class="width-lg">
                            <?php
                            if ($num > 1) {
                                ?>
                                <button type="button" name="delBrowser" class="btn_os_del">삭제</button>
                                <?php
                            } else {
                                ?>
                                <button type="button" name="addBrowser" id="btn_browser_add">추가</button>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div>
                        <input type="text" name="agentBrowserCode[]" value="" class="width-lg"> ->
                        <input type="text" name="browserCode[]" value="" class="width-lg">
                        <button type="button" name="addBrowser" id="btn_browser_add">추가</button>
                    </div>
                    <?php
                }
                ?>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="text-center">
        <input type="submit" value="저장" class="btn btn-red btn-lg"/>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $("#frmCoupon").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                mode: {
                    required: true,
                },
                couponDisplayType: {
                    required: true,
                },
                couponAutoRecoverType: {
                    required: true,
                },
            },
        });

        // 제외 페이지 정보 추가
        $("#btn_except_add").click(function (e) {
            exceptAdd();
        });
        // 제외 페이지 정보 삭제
        $(".btn_except_del").click(function (e) {
            $(this).closest('div').remove();
        });
        // 유입경로 정보 추가
        $("#btn_inflow_add").click(function (e) {
            inflowAdd();
        });
        // 유입경로 정보 삭제
        $(".btn_inflow_del").click(function (e) {
            $(this).closest('div').remove();
        });
        // OS 정보 추가
        $("#btn_os_add").click(function (e) {
            osAdd();
        });
        // OS 정보 삭제
        $(".btn_os_del").click(function (e) {
            $(this).closest('div').remove();
        });
        // browser 정보 추가
        $("#btn_browser_add").click(function (e) {
            browserAdd();
        });
        // browser 정보 삭제
        $(".btn_browser_del").click(function (e) {
            $(this).closest('div').remove();
        });
    });

    function exceptAdd() {
        var trCount = ($('#td_except div').length);
        trCount = trCount + 1;
        var addExcept = '<div>';
        addExcept += '<br/><input type="text" name="exceptPage[]" value="" class="width-xl">';
        addExcept += ' <button type="button" name="addExcept" id="btn_except_del' + trCount + '">삭제</button>';
        addExcept += '</div>';
        $('#td_except').append(addExcept);
        // 정보 삭제(동적 삭제)
        $('#btn_except_del' + trCount).on('click', function (e) {
            $(this).closest('div').remove();
        });
    }

    function inflowAdd() {
        var trCount = ($('#td_inflow div').length);
        trCount = trCount + 1;
        var addInflow = '<div>';
        addInflow += '<br/><input type="text" name="agentInflowCode[]" value="" class="width-lg"> -> <input type="text" name="inflowCode[]" value="" class="width-lg">';
        addInflow += ' <button type="button" name="addInflow" id="btn_inflow_del' + trCount + '">삭제</button>';
        addInflow += '</div>';
        $('#td_inflow').append(addInflow);
        // 정보 삭제(동적 삭제)
        $('#btn_inflow_del' + trCount).on('click', function (e) {
            $(this).closest('div').remove();
        });
    }

    function osAdd() {
        var trCount = ($('#td_os div').length);
        trCount = trCount + 1;
        var addOs = '<div>';
        addOs += '<br/><input type="text" name="agentOsCode[]" value="" class="width-lg"> -> <input type="text" name="osCode[]" value="" class="width-lg">';
        addOs += ' <button type="button" name="addOs" id="btn_os_del' + trCount + '">삭제</button>';
        addOs += '</div>';
        $('#td_os').append(addOs);
        // 정보 삭제(동적 삭제)
        $('#btn_os_del' + trCount).on('click', function (e) {
            $(this).closest('div').remove();
        });
    }

    function browserAdd() {
        var trCount = ($('#td_browser div').length);
        trCount = trCount + 1;
        var addBrowser = '<div>';
        addBrowser += '<br/><input type="text" name="agentBrowserCode[]" value="" class="width-lg"> -> <input type="text" name="browserCode[]" value="" class="width-lg">';
        addBrowser += ' <button type="button" name="addBrowser" id="btn_browser_del' + trCount + '">삭제</button>';
        addBrowser += '</div>';
        $('#td_browser').append(addBrowser);
        // 정보 삭제(동적 삭제)
        $('#btn_browser_del' + trCount).on('click', function (e) {
            $(this).closest('div').remove();
        });
    }
</script>
