<form id="frmBannerGroup" name="frmBannerGroup" method="post" action="./banner_ps.php" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?php echo $mode;?>" />
    <input type="hidden" name="sno" value="<?php echo $data['sno'];?>" />
    <input type="hidden" name="bannerGroupCode" value="<?php echo $data['bannerGroupCode'];?>" />
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th class="require">구분</th>
            <td>
                <?php if ($mode === 'banner_group_modify') {?>
                    <input type="hidden" name="bannerGroupDeviceType" value="<?php echo $data['bannerGroupDeviceType'];?>" />
                    <span class="bold"><?php echo $bannerDeviceType[$data['bannerGroupDeviceType']];?>쇼핑몰</span>
                <?php } else {?>
                    <?php foreach ($bannerDeviceType as $dKey => $dVal) {?>
                        <label class="radio-inline">
                            <input type="radio" name="bannerGroupDeviceType" value="<?php echo $dKey;?>" <?php echo $checked['bannerGroupDeviceType'][$dKey]; ?> /><?php echo $dVal;?>쇼핑몰
                        </label>
                    <?php }?>
                <?php }?>
            </td>
        </tr>
        <tr>
            <th class="require">디자인 스킨</th>
            <td class="form-inline">
                <?php if ($mode === 'banner_group_modify') {?>
                <input type="hidden" name="skinName" value="<?php echo $data['skinName'];?>" />
                <span class="bold"><?php echo $data['selectedSkinName'];?></span>
                <?php } else {?>
                <select name="skinName" class="form-control width-2xl">
                </select>
                <?php }?>
            </td>
        </tr>
        <tr>
            <th class="require">그룹 종류</th>
            <td>
                <div class="form-inline">
                    <select id="bannerGroupType" name="bannerGroupType" class="form-control js-groupSelect">
                        <?php
                        foreach($bannerGroupTypeFl as $gKey => $gVal){
                            echo '<option value="' . $gKey . '" ' . gd_isset($selected['bannerGroupType'][$gKey]) . '>' . $gVal . '</option>'.PHP_EOL;
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
        </tbody>
        <tbody class="js-type-category">
        <tr>
            <th class="require">분류선택</th>
            <td>
                <div class="form-inline">
                    <?php echo $cate->getMultiCategoryBox('catdCd_category', $data['cateCd'], 'class="form-control"'); ?>
                </div>
            </td>
        </tr>
        </tbody>
        <tbody class="js-type-brand">
        <tr>
            <th class="require">브랜드 선택</th>
            <td>
                <div class="form-inline">
                    <?php echo $brand->getMultiCategoryBox('catdCd_brand', $data['cateCd'], 'class="form-control"'); ?>
                </div>
            </td>
        </tr>
        </tbody>
        <tr>
            <th class="require">배너 그룹명</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="bannerGroupName" value="<?php echo $data['bannerGroupName'];?>" maxlength="20" class="form-control js-maxlength width-2xl"/>
                    <span class="font-num text-darkblue js-banner-group-code"></span>
                </div>
            </td>
        </tr>
    </table>

    <div class="text-center">
        <input type="submit" value="배너 그룹 <?php echo $modeTxt;?>" class="btn btn-red js-submit"/>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 배너 그룹 정보 저장
        $("#frmBannerGroup").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            dialog: false,
            rules: {
                bannerGroupName: "required"
            },
            messages: {
                bannerGroupName: {
                    required: '배너 그룹명 입력해 주세요.'
                }
            }
        });

        // 구분에 따른 스킨 출력
        setBannerGroupSkinChange();
        $('#frmBannerGroup input:radio[name=bannerGroupDeviceType]').click(function () {
            setBannerGroupSkinChange();
        });

        // 배너 그룹 타입에 따른 선택
        $('.js-groupSelect').each(function () {
            setBannerGroupTypeChange();
        });
        $('.js-groupSelect').change(function () {
            setBannerGroupTypeChange();
        });
    });

    /**
     * 배너 그룹 타입에 따른 선택
     */
    function setBannerGroupTypeChange(){
        var groupVal = $('.js-groupSelect option:selected').val();
        var $categoryObj = $('#frmBannerGroup').find('.js-type-category');
        var $brandObj = $('#frmBannerGroup').find('.js-type-brand');

        if (groupVal == 'category') {
            $categoryObj.show();
            $brandObj.hide();
        } else if (groupVal == 'brand') {
            $categoryObj.hide();
            $brandObj.show();
        } else {
            $categoryObj.hide();
            $brandObj.hide();
        }
    }

    /**
     * 구분에 따른 스킨 출력
     */
    function setBannerGroupSkinChange() {
        var frontSkinOption = '<?php echo implode('', $skinList['front']);?>';
        var mobileSkinOption = '<?php echo implode('', $skinList['mobile']);?>';
        var selectDeviceType = $('#frmBannerGroup input:radio[name=bannerGroupDeviceType]:checked').val();
        var selectSkinOption = '';

        // 구분 선택에 따른 select option 값
        if (selectDeviceType == 'front') {
            selectSkinOption = frontSkinOption;
        } else {
            selectSkinOption = mobileSkinOption;
        }

        // select option 처리
        $('#frmBannerGroup select[name=skinName]').html(selectSkinOption);
    }
    //-->
</script>

