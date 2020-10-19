<script>
    $(document).ready(function () {
        $("#frmReg").validate({
            dialog:false,
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                'contents[]': "required",

            },
            messages: {
                'contents[]': {
                    required: "내용을 입력하세요.",
                },

            },
        });

        $('.js-add').bind('click', function (e) {
            e.preventDefault();

            count = $('textarea[name="contents[]"]').length;
            if (count > 9) {
                alert('일정은 날짜당 최대 10개까지 추가할 수 있습니다.');
                return;
            }

            var addUploadBox = _.template(
                $("script.template").html()
            );
            $('#sch-content').append(addUploadBox());
            $('textarea[name="contents[]"]:last').focus();
        })

        $('body').on('click', '.js-minus', function () {
            $(this).closest('tr').remove();
        })

        //텍스트 제한
        $('textarea[name="contents[]"]').on('keyup', function () {
            var maxString = 200;
            var currentString = $(this).val();
            if (currentString.length > maxString) {
                alert('최대 ' + maxString + '자까지 입력 가능 합니다.');
                $(this).val(currentString.substring(0, maxString));
            }
        });
    })
</script>
<style>
    #sch-content textarea {
        height: 100px;
        width: 100%;
        border: 1px solid #e6e6e6;
    }

    #sch-content td {
        padding: 15px 10px 0 0;
        vertical-align: top;
        border: 0px;
    }
    .table-rows {
        border-top: 0px;
        border-bottom: 0px;
    }

</style>
<script type="text/template" class="template">
    <tr>
        <td style="padding-left:0px">
            <input type="hidden" mode="sno[]" value="">
            <textarea name="contents[]"></textarea>
        </td>
        <td>
            <button type="button" class="btn btn-white btn-icon-minus js-minus">삭제</button>
        </td>
    </tr>
</script>

<form id="frmReg" name="frmReg" action="../base/schedule_ps.php" method="post" >
    <input type="hidden" name="mode" value="add">
    <input type="hidden" name="scdDt" value="<?= $requestDate; ?>"/>
    <div class="schedule-icon-title">
        <div class="pdl30">
        <span class="date-text"><?= $requestDate ?>
            <span><span style="color: #f91d11;font-weight: bold"><?=count($data) ?>건</span>의 일정이 있습니다.</span></span>
        </span>
        <span class="pull-right pdl5 pdt5"> <button type="button" class="btn btn-white btn-icon-plus js-add">추가</button></span>
        </div>
    </div>


    <table class="table table-rows table-fixed" id="sch-content" style="border; 0px">
        <colgroup>
            <col width="87%">
            <col>
        </colgroup>
        <?php
        if ($data) {
            foreach ($data as $row) {
                ?>
                <tr>
                    <td style="padding-left:0px">
                        <input type="hidden" name="sno[]" value="<?= $row['sno'] ?>">
                        <textarea name="contents[]"><?= gd_htmlspecialchars_stripslashes($row['contents']) ?></textarea>
                    </td>
                    <td>

                            <button type="button" class="btn btn-white btn-icon-minus js-minus">삭제</button>
                    </td>
                </tr>
                <?php
            }
        } else { ?>
            <tr>
                <td style="padding-left:0px">
                    <input type="hidden" mode="sno[]" value="">
                    <textarea name="contents[]"></textarea>
                </td>
                <td>
                    <button type="button" class="btn btn-white btn-icon-minus js-minus">삭제</button>
                </td>
            </tr>
        <?php } ?>

    </table>

    <div class="text-center" style="padding: 20px 0 25px 0;border-top: 1px solid #e6e6e6">
        <button type="button" class="btn btn-xl btn-white js-layer-close mgr5">닫기</button>
        <button type="submit" class="btn btn-xl btn-black">저장</button>
    </div>
</form>
