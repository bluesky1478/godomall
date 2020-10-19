<form id="frmAddField" name="frmAddField" action="order_add_field_ps.php" method="post" class="content_form">
    <input type="hidden" name="mode" value="<?= $mode; ?>"/>
    <input type="hidden" name="orderAddFieldNo" value="<?= $getData['orderAddFieldNo']; ?>"/>
    <input type="hidden" name="mallSno" value="<?= $mallSno; ?>"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('<?=$adminList;?>');" />
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <?php if ($mallCnt > 1) { ?>
        <ul class="multi-skin-nav nav nav-tabs" style="margin-bottom:20px;">
            <?php foreach ($mallList as $key => $mall) { ?>
                <li role="presentation" class="js-popover <?php echo $mallSno == $mall['sno'] ? 'active' : 'passive'; ?>" data-html="true" data-content="<?php echo $mall['mallName']; ?>" data-placement="top">
                    <a href="./order_add_field_regist.php?mallSno=<?php echo $mall['sno']; ?>">
                        <span class="flag flag-16 flag-<?php echo $mall['domainFl']?>"></span>
                        <span class="mall-name"><?php echo $mall['mallName']; ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <h5 class="table-title gd-help-manual">기본정보</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>항목명</th>
            <td>
                <input type="text" name="orderAddFieldName" value="<?= $getData['orderAddFieldName'] ?>" class="form-control width-xl js-maxlength" maxlength="100"/>
            </td>
        </tr>
        <tr>
            <th>항목설명</th>
            <td>
                <input type="text" name="orderAddFieldDescribed" value="<?= $getData['orderAddFieldDescribed'] ?>" class="form-control width90p js-maxlength" maxlength="250"/>
            </td>
        </tr>
        <tr>
            <th>노출상태</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldDisplay" value="y" <?= $checked['orderAddFieldDisplay']['y'] ?> />노출함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldDisplay" value="n" <?= $checked['orderAddFieldDisplay']['n'] ?> />노출안함
                </label>
            </td>
        </tr>
        <tr>
            <th>필수여부</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldRequired" value="y" <?= $checked['orderAddFieldRequired']['y']; ?> />필수입력
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldRequired" value="n" <?= $checked['orderAddFieldRequired']['n']; ?> />선택입력
                </label>
            </td>
        </tr>
        <tr>
            <th>노출유형</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldType" value="text" <?= $checked['orderAddFieldType']['text']; ?> />텍스트박스(한줄)
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldType" value="textarea" <?= $checked['orderAddFieldType']['textarea']; ?> />텍스트박스(여러줄)
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldType" value="radio" <?= $checked['orderAddFieldType']['radio']; ?> />라디오버튼
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldType" value="checkbox" <?= $checked['orderAddFieldType']['checkbox']; ?> />체크박스
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldType" value="select" <?= $checked['orderAddFieldType']['select']; ?> />셀렉트박스
                </label>
            </td>
        </tr>
        <tr>
            <th style="padding-top:10px; vertical-align: text-top;">상세설정</th>
            <td class="js-text">
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th>입력제한 글자수</th>
                        <td class="form-inline">
                            <input type="text" name="orderAddFieldOption[text][maxlength]" value="<?= $getData['orderAddFieldOption']['text']['maxlength'] ?>" class="form-control width-2xs number" maxlength="3"/>
                            <span class="notice-info">1에서 250이내로 설정 가능합니다.</span>
                        </td>
                    </tr>
                    <tr>
                        <th>암호화</th>
                        <td class="form-inline">
                            <label class="checkbox-inline"><input type="checkbox" name="orderAddFieldOption[text][encryptor]" value="y" <?= $checked['orderAddFieldOption']['text']['encryptor']['y']; ?> class="js-text-encryptor" />암호화</label>
                            <label class="checkbox-inline"><input type="checkbox" name="orderAddFieldOption[text][password]" value="y" <?= $checked['orderAddFieldOption']['text']['password']['y']; ?> class="js-text-password" />마스킹 처리</label>
                            <div class="notice-info">
                                마스킹 처리시 숫자, 영문만 입력 받을 수 있습니다.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="js-textarea">
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th>필드길이(세로)</th>
                        <td class="form-inline">
                            <input type="text" name="orderAddFieldOption[textarea][height]" value="<?= $getData['orderAddFieldOption']['textarea']['height'] ?>" class="form-control width-2xs number" minlength="2" maxlength="3" title="필드길이(세로)는 30에서 200이내로 설정 가능합니다."/>
                            <span class="notice-info">30에서 200이내로 설정 가능합니다.</span>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="js-radio">
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <?php
                    if (is_array($getData['orderAddFieldOption']['radio']['field'])) {
                        foreach ($getData['orderAddFieldOption']['radio']['field'] as $radioKey => $radioVal) {
                            ?>
                            <tr>
                                <th>입력값</th>
                                <td class="form-inline">
                                    <input type="text" name="orderAddFieldOption[radio][field][]" value="<?= $radioVal ?>" class="form-control width-sm js-add-field-key" data-field="radio" maxlength="30"/>
                                    <?php
                                    if ($radioKey == 0) {
                                        ?>
                                        <button type="button" class="btn btn-sm btn-white btn-icon-plus js-add-field-row" data-field="radio">추가</button>
                                        <?php
                                    } else {
                                        ?>
                                        <button type="button" class="btn btn-sm btn-white btn-icon-minus js-del-field-row">삭제</button>
                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <th>입력값</th>
                            <td class="form-inline">
                                <input type="text" name="orderAddFieldOption[radio][field][]" value="<?= $getData['orderAddFieldOption']['radio']['field'] ?>" class="form-control width-sm js-add-field-key" data-field="radio" maxlength="30"/>
                                <button type="button" class="btn btn-sm btn-white btn-icon-plus js-add-field-row" data-field="radio">추가</button>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </td>
            <td class="js-checkbox">
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <?php
                    if (is_array($getData['orderAddFieldOption']['checkbox']['field'])) {
                        foreach ($getData['orderAddFieldOption']['checkbox']['field'] as $checkboxKey => $checkboxVal) {
                            ?>
                            <tr>
                                <th>입력값</th>
                                <td class="form-inline">
                                    <input type="text" name="orderAddFieldOption[checkbox][field][]" value="<?= $checkboxVal ?>" class="form-control width-sm js-add-field-key" data-field="checkbox" maxlength="30"/>
                                    <?php
                                    if ($checkboxKey == 0) {
                                        ?>
                                        <button type="button" class="btn btn-sm btn-white btn-icon-plus js-add-field-row" data-field="checkbox">추가</button>
                                        <?php
                                    } else {
                                        ?>
                                        <button type="button" class="btn btn-sm btn-white btn-icon-minus js-del-field-row">삭제</button>
                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <th>입력값</th>
                            <td class="form-inline">
                                <input type="text" name="orderAddFieldOption[checkbox][field][]" value="<?= $getData['orderAddFieldOption']['checkbox']['field'] ?>" class="form-control width-sm js-add-field-key" data-field="checkbox" maxlength="30"/>
                                <button type="button" class="btn btn-sm btn-white btn-icon-plus js-add-field-row" data-field="checkbox">추가</button>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </td>
            <td class="js-select">
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <?php
                    if (is_array($getData['orderAddFieldOption']['select']['field'])) {
                        foreach ($getData['orderAddFieldOption']['select']['field'] as $selectKey => $selectVal) {
                            ?>
                            <tr>
                                <th>입력값</th>
                                <td class="form-inline">
                                    <input type="text" name="orderAddFieldOption[select][field][]" value="<?= $selectVal ?>" class="form-control width-sm js-add-field-key" data-field="select" maxlength="30"/>
                                    <?php
                                    if ($selectKey == 0) {
                                        ?>
                                        <button type="button" class="btn btn-sm btn-white btn-icon-plus js-add-field-row" data-field="select">추가</button>
                                        <?php
                                    } else {
                                        ?>
                                        <button type="button" class="btn btn-sm btn-white btn-icon-minus js-del-field-row">삭제</button>
                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <th>입력값</th>
                            <td class="form-inline">
                                <input type="text" name="orderAddFieldOption[select][field][]" value="<?= $getData['orderAddFieldOption']['select']['field'] ?>" class="form-control width-sm js-add-field-key" data-field="select" maxlength="30"/>
                                <button type="button" class="btn btn-sm btn-white btn-icon-plus js-add-field-row" data-field="select">추가</button>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="notice-info">
        텍스트박스(한줄) : 입력제한 글자수를 입력하지 않으면 필드길이와 동일하게 설정됩니다.
    </div>
    <div class="notice-info">
        텍스트박스(여러줄) : “프론트>주문서작성/결제” 페이지에 노출된 텍스트박스 내 최대 1,000자까지 입력 가능합니다.
    </div>
    <div class="notice-info">
        라디오버튼/체크박스/셀렉트박스 : 입력값은 추가버튼 또는 Enter키 이용 최대 30개까지 입력 가능합니다.
    </div>
    <div class="notice-info">
        필수입력 선택시 해당 항목은 주문서작성시 필수로 입력해야 주문이 됩니다.
    </div>
    <div class="notice-info">
        [정보통신망 이용촉진 및 정보보호 등에 관한 법률] 제23조의2(주민등록번호의 사용 제한)에 의거하여 2013년 2월 18일부터는 쇼핑몰에서 고객의 주민등록번호를 수집/이용할 수 없으나<br/> ‘해외 구매 대행’ 사업의 경우 관세법에 따라 고객의 주민등록번호 입력이 가능합니다. 주민등록번호는 위와 같은 경우에만 입력 및 수집이 가능하며 그 외 사업자의 경우 법에 위반되므로 반드시 가능 여부를 확인하신 후, 사용 해주시기 바랍니다.
    </div>
    <div class="notice-info">
        주민등록번호와 같은 보안이 필요한 정보는 노출유형을 ‘텍스트박스(한줄)’로 선택 후, 암호화에 체크하신 후 사용하실 수 있습니다.<br/> 암호화 선택된 필드에서 입력된 정보는 쇼핑몰과 관리자 주문 페이지에서는 암호화 처리되며 엑셀 다운로드 시 복호화(암호화된 정보가 풀려서 보여지는 현상) 처리되니 엑셀 관리에 주의 해주시기 바랍니다.
    </div>
    <div class="notice-info mgb30">
        마스킹처리에 체크하시면 주문서작성/결제 화면에서 고객이 정보 입력 시 해당 값이 별표(*)처리가 됩니다.
    </div>

    <h5 class="table-title gd-help-manual">상품조건 설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>노출 상품 선택</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldApplyType" value="all" <?= $checked['orderAddFieldApplyType']['all'] ?> />전체상품
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldApplyType" value="category" <?= $checked['orderAddFieldApplyType']['category'] ?> />특정 카테고리
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldApplyType" value="brand" <?= $checked['orderAddFieldApplyType']['brand'] ?> />특정 브랜드
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldApplyType" value="goods" <?= $checked['orderAddFieldApplyType']['goods'] ?> />특정 상품
                </label>
            </td>
        </tr>
        <tr>
            <th>입력 방법 선택</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldProcess" value="order" <?= $checked['orderAddFieldProcess']['order'] ?> />공통으로 한번만 입력
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldProcess" value="goods" <?= $checked['orderAddFieldProcess']['goods'] ?> />상품별로 입력
                </label>
            </td>
        </tr>
        <tr class="js-apply-all">
            <th>전체 상품</th>
            <td>
                <div class="notice-info">
                    전체 상품에 대해서 주문 시 추가정보를 입력 받습니다.
                </div>
                <div class="notice-info">
                    단, 예외조건에 해당되는 상품은 추가정보가 노출되지 않습니다.
                </div>
            </td>
        </tr>
        <tr class="js-apply-category">
            <th>
                특정 카테고리 선택<br/>
                <button type="button" id="selectApplyCategory" class="btn btn-sm btn-gray" title="적용할 카테고리을 선택해주세요.">카테고리선택</button>
            </th>
            <td class="form-inline">
                <div class="notice-info">
                    선택된 카테고리 내 상품에 대해서 주문 시 추가정보를 입력 받습니다.
                </div>
                <div class="notice-info">
                    단, 예외조건에 해당되는 상품은 추가정보가 노출되지 않습니다.
                </div>
                <div id="orderAddFieldApplyCategory">
                    <?php
                    if ($getData['orderAddFieldApplyType'] == 'category' && $getData['orderAddFieldApplyCategory']) {
                        foreach ($getData['orderAddFieldApplyCategory'] as $k => $v) {
                            ?>
                            <span id="idCategory_<?= $v['no'] ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="orderAddFieldApplyCategory[]" value="<?= $v['no'] ?>"/>
                                <button type="button" class="btn btn-gray"><?= $v['name'] ?></button>
                                <button type="button" class="btn btn-red" data-toggle="delete" data-target="#idCategory_<?= $v['no'] ?>">삭제</button>
                            </span>
                            <?php
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr class="js-apply-brand">
            <th>
                특정 브랜드 선택<br/>
                <button type="button" id="selectApplyBrand" class="btn btn-sm btn-gray" title="적용할 브랜드을 선택해주세요.">브랜드선택</button>
            </th>
            <td class="form-inline">
                <div class="notice-info">
                    선택된 브랜드 내 상품에 대해서 주문 시 추가정보를 입력 받습니다.
                </div>
                <div class="notice-info">
                    단, 예외조건에 해당되는 상품은 추가정보가 노출되지 않습니다.
                </div>
                <div id="orderAddFieldApplyBrand">
                    <?php
                    if ($getData['orderAddFieldApplyType'] == 'brand' && $getData['orderAddFieldApplyBrand']) {
                        foreach ($getData['orderAddFieldApplyBrand'] as $k => $v) {
                            ?>
                            <span id="idBrand_<?= $v['no'] ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="orderAddFieldApplyBrand[]" value="<?= $v['no'] ?>"/>
                                <button type="button" class="btn btn-gray"><?= $v['name'] ?></button>
                                <button type="button" class="btn btn-red" data-toggle="delete" data-target="#idBrand_<?= $v['no'] ?>">삭제</button>
                            </span>
                            <?php
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr class="js-apply-goods">
            <th>
                특정 상품 선택<br/>
                <button type="button" id="selectApplyGoods" class="btn btn-sm btn-gray" title="적용할 상품을 선택해주세요.">상품선택</button>
            </th>
            <td class="form-inline">
                <div class="notice-info">
                    선택된 상품에 대해서 주문 시 추가정보를 입력 받습니다.
                </div>
                <div class="notice-info">
                    단, 예외조건에 해당되는 상품은 추가정보가 노출되지 않습니다.
                </div>
                <table id="orderAddFieldApplyGoods" class="table table-cols">
                    <thead>
                    <tr>
                        <th>번호</th>
                        <th>이미지</th>
                        <th>상품명</th>
                        <th>삭제</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($getData['orderAddFieldApplyType'] == 'goods' && $getData['orderAddFieldApplyGoods']) {
                        foreach ($getData['orderAddFieldApplyGoods'] as $k => $v) {
                            echo '<tr id="idGoods_' . $v['goodsNo'] . '">' . chr(10);
                            echo '<td class="center">' . ($k + 1) . '<input type="hidden" name="orderAddFieldApplyGoods[]" value="' . $v['goodsNo'] . '" /></td>' . chr(10);
                            echo '<td class="center">' . gd_html_goods_image($v['goodsNo'], $v['imageName'], $v['imagePath'], $v['imageStorage'], 50, $v['goodsNm'], '_blank') . '</td>' . chr(10);
                            echo '<td><a href="../goods/goods_register.php?goodsNo=' . $v['goodsNo'] . '" target="_blank">' . gd_remove_tag($v['goodsNm']) . '</a></td>' . chr(10);
                            echo '<td class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idGoods_' . $v['goodsNo'] . '\');" value="삭제" /></td>' . chr(10);
                            echo '</tr>' . chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="4">
                            <input type="button" value="전체삭제" class="btn btn-sm btn-gray" onclick="$('#orderAddFieldApplyGoods tbody').html('');">
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
        </tbody>
    </table>

    <h5 class="table-title gd-help-manual">상품 예외조건 설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>예외 상품 선택</th>
            <td class="form-inline">
                <label class="checkbox-inline js-apply-except-category">
                    <input type="checkbox" name="orderAddFieldExceptCategoryType" value="y" <?= $checked['orderAddFieldExceptCategoryType']['y'] ?> />예외 카테고리
                </label>
                <label class="checkbox-inline js-apply-except-brand">
                    <input type="checkbox" name="orderAddFieldExceptBrandType" value="y" <?= $checked['orderAddFieldExceptBrandType']['y'] ?> />예외 브랜드
                </label>
                <label class="checkbox-inline js-apply-except-goods">
                    <input type="checkbox" name="orderAddFieldExceptGoodsType" value="y" <?= $checked['orderAddFieldExceptGoodsType']['y'] ?> />예외 상품
                </label>
            </td>
        </tr>
        <tr class="js-except-category">
            <th>예외 카테고리 선택</th>
            <td class="form-inline">
                <button type="button" id="selectExceptCategory" class="btn btn-sm btn-gray" title="예외할 카테고리를 선택해주세요.">카테고리선택</button>
                <div id="orderAddFieldExceptCategory">
                    <?php
                    if ($getData['orderAddFieldExceptCategoryType'] == 'y' && $getData['orderAddFieldExceptCategory']) {
                        foreach ($getData['orderAddFieldExceptCategory'] as $k => $v) {
                            ?>
                            <span id="idExceptCategory_<?= $v['no'] ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="orderAddFieldExceptCategory[]" value="<?= $v['no'] ?>"/>
                                <button type="button" class="btn btn-gray"><?= $v['name'] ?></button>
                                <button type="button" class="btn btn-red" data-toggle="delete" data-target="#idExceptCategory_<?= $v['no'] ?>">삭제</button>
                            </span>
                            <?php
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr class="js-except-brand">
            <th>예외 브랜드 선택</th>
            <td class="form-inline">
                <button type="button" id="selectExceptBrand" class="btn btn-sm btn-gray" title="예외할 브랜드을 선택해주세요.">브랜드선택</button>
                <div id="orderAddFieldExceptBrand">
                    <?php
                    if ($getData['orderAddFieldExceptBrandType'] == 'y' && $getData['orderAddFieldExceptBrand']) {
                        foreach ($getData['orderAddFieldExceptBrand'] as $k => $v) {
                            ?>
                            <span id="idExceptBrand_<?= $v['no'] ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="orderAddFieldExceptBrand[]" value="<?= $v['no'] ?>"/>
                                <button type="button" class="btn btn-gray"><?= $v['name'] ?></button>
                                <button type="button" class="btn btn-red" data-toggle="delete" data-target="#idExceptBrand_<?= $v['no'] ?>">삭제</button>
                            </span>
                            <?php
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr class="js-except-goods">
            <th>예외 상품 선택</th>
            <td class="form-inline">
                <button type="button" id="selectExceptGoods" class="btn btn-sm btn-gray" title="예외할 상품을 선택해주세요.">상품선택</button>
                <table id="orderAddFieldExceptGoods" class="table table-cols">
                    <thead>
                    <tr>
                        <th>번호</th>
                        <th>이미지</th>
                        <th>상품명</th>
                        <th>삭제</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($getData['orderAddFieldExceptGoodsType'] == 'y' && $getData['orderAddFieldExceptGoods']) {
                        foreach ($getData['orderAddFieldExceptGoods'] as $k => $v) {
                            echo '<tr id="idExceptGoods_' . $v['goodsNo'] . '">' . chr(10);
                            echo '<td class="center">' . ($k + 1) . '<input type="hidden" name="orderAddFieldExceptGoods[]" value="' . $v['goodsNo'] . '" /></td>' . chr(10);
                            echo '<td class="center">' . gd_html_goods_image($v['goodsNo'], $v['imageName'], $v['imagePath'], $v['imageStorage'], 50, $v['goodsNm'], '_blank') . '</td>' . chr(10);
                            echo '<td><a href="../goods/goods_register.php?goodsNo=' . $v['goodsNo'] . '" target="_blank">' . gd_remove_tag($v['goodsNm']) . '</a></td>' . chr(10);
                            echo '<td class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExceptGoods_' . $v['goodsNo'] . '\');" value="삭제" /></td>' . chr(10);
                            echo '</tr>' . chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="4">
                            <input type="button" value="전체삭제" class="btn btn-sm btn-gray" onclick="$('#orderAddFieldExceptGoods tbody').html('');">
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $("#frmAddField").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                mode: {
                    required: true,
                },
                orderAddFieldName: {
                    required: true,
                },
                orderAddFieldDisplay: {
                    required: true,
                },
                orderAddFieldRequired: {
                    required: true,
                },
                orderAddFieldType: {
                    required: true,
                },
                'orderAddFieldOption[text][maxlength]': {
                    required: function(element) {
                        if ($('input:radio[name="orderAddFieldType"]:checked').val() == 'text') {
                            var returnValue = true;
                        } else {
                            var returnValue = false;
                        }
                        return returnValue;
                    },
                    min: 1,
                    max: 250,
                },
                'orderAddFieldOption[textarea][height]': {
                    required: function(element) {
                        if ($('input:radio[name="orderAddFieldType"]:checked').val() == 'textarea') {
                            var returnValue = true;
                        } else {
                            var returnValue = false;
                        }
                        return returnValue;
                    },
                    min: 30,
                    max: 200,
                },
                'orderAddFieldOption[radio][field][]': {
                    required: function(element) {
                        if ($('input:radio[name="orderAddFieldType"]:checked').val() == 'radio') {
                            var returnValue = true;
                        } else {
                            var returnValue = false;
                        }
                        return returnValue;
                    },
                },
                'orderAddFieldOption[checkbox][field][]': {
                    required: function(element) {
                        if ($('input:radio[name="orderAddFieldType"]:checked').val() == 'checkbox') {
                            var returnValue = true;
                        } else {
                            var returnValue = false;
                        }
                        return returnValue;
                    },
                },
                'orderAddFieldOption[select][field][]': {
                    required: function(element) {
                        if ($('input:radio[name="orderAddFieldType"]:checked').val() == 'select') {
                            var returnValue = true;
                        } else {
                            var returnValue = false;
                        }
                        return returnValue;
                    },
                },
            },
            messages: {
                mode: {
                    required: '정상 접속이 아닙니다.(mode)',
                },
                orderAddFieldName: {
                    required: '항목명을 입력하세요.',
                },
                orderAddFieldDisplay: {
                    required: '노출상태를 선택하세요.',
                },
                orderAddFieldRequired: {
                    required: '필수여부를 선택하세요.',
                },
                orderAddFieldType: {
                    required: '노출유형을 선택하세요.',
                },
                'orderAddFieldOption[text][maxlength]': {
                    required: '입력제한 글자수를 입력하세요.',
                    min: '최소 입력제한 글자수는 1자 입니다.',
                    max: '최대 입력제한 글자수는 250자 입니다.',
                },
                'orderAddFieldOption[textarea][height]': {
                    required: '필드길이(세로)를 입력하세요.',
                    min: '최소 필드길이(세로)는 30 입니다.',
                    max: '최대 필드길이(세로)는 200 입니다.',
                },
                'orderAddFieldOption[radio][field][]': {
                    required: '입력값를 입력하세요.',
                },
                'orderAddFieldOption[checkbox][field][]': {
                    required: '입력값를 입력하세요.',
                },
                'orderAddFieldOption[select][field][]': {
                    required: '입력값를 입력하세요.',
                },
            }
        });
        // 노출유형 선택 시
        $('input:radio[name="orderAddFieldType"]').click(function (e) {
            changeOrderAddFieldType();
            changeOrderAddFieldPassword();
        });
        // 노출 상품 선택 선택 시
        $('input:radio[name="orderAddFieldApplyType"]').click(function (e) {
            changeOrderAddFieldApply();
        });
        // 예외 상품 선택 선택 시
        $('input:checkbox[name^="orderAddFieldExcept"]').click(function (e) {
            changeOrderAddFieldExcept();
        });
        $('.js-text-encryptor').click(function() {
            changeOrderAddFieldPassword();
        });
        $('.js-add-field-row').click(function () {
            if ($('.js-' + $(this).data('field') + ' table tr').length >= 30) {
                alert('입력값은 최대 30개 입니다.');
                return false;
            }
            var field = $(this).data('field');
            var html = '<tr>' +
                '<th>입력값</th>' +
                '<td class="form-inline">' +
                '<input type="text" name="orderAddFieldOption[' + field + '][field][]" value="" class="form-control width-sm js-add-field-key" data-field="' + field + '" maxlength="10"/> <button type="button" class="btn btn-sm btn-white btn-icon-minus js-del-field-row">삭제</button>' +
                '</td>' +
                '</tr>';

            $('.js-' + $(this).data('field') + ' > table').append(html);
        });
        $(document).on("click", ".js-del-field-row", function () {
            $(this).parent('td').parent('tr').remove();
        });
        $(document).on("keypress", ".js-add-field-key", function (e) {
            if (e.which == 13) {
                var field = $(this).data('field');
                $('.js-' + $(this).data('field') + ' .js-add-field-row').trigger('click');
                $('.js-' + $(this).data('field') + ' .js-add-field-key').last().focus();
                e.preventDefault();
                return false;
            }
        });

        // 적용 해당 버튼 선택 시
        $('[id^=selectApply]').click(function (e) {
            var code = (this.id).split('selectApply');
            code = code[1];
            layer_register(code);
        });
        // 제외 해당 버튼 선택 시
        $('[id^=selectExcept]').click(function (e) {
            var code = (this.id).split('selectExcept');
            code = code[1];
            layer_register(code, 'except');
        });
        changeOrderAddFieldType();
        changeOrderAddFieldApply();
        changeOrderAddFieldExcept();
        changeOrderAddFieldPassword();
    });

    function changeOrderAddFieldPassword() {
        if ($('.js-text-encryptor').prop("checked") == true) {
            $('.js-text-password').prop("disabled", false);
        } else {
            $('.js-text-password').prop("disabled", true);
        }
    }

    function changeOrderAddFieldType() {
        var fieldType = ['text', 'textarea', 'file', 'radio', 'checkbox', 'select'];
        $.each(fieldType, function (key, value) {
            if ($('input:radio[name="orderAddFieldType"]:checked').val() == value) {
                $('.js-' + value).show();
                $('.js-' + value + ' input').prop("disabled", false);
            } else {
                $('.js-' + value).hide();
                $('.js-' + value + ' input').prop("checked", false);
                $('.js-' + value + ' input').prop("disabled", true);
            }
        });
    }

    function changeOrderAddFieldApply() {
        var fieldApply = ['all', 'category', 'brand', 'goods'];
        $.each(fieldApply, function (key, value) {
            if ($('input:radio[name="orderAddFieldApplyType"]:checked').val() == value) {
                $('.js-apply-' + value).show();
                $('.js-apply-' + value + ' input').prop("disabled", false);
                $('.js-apply-except-' + value).hide();
                $('.js-apply-except-' + value + ' input').prop("checked", false);
                $('.js-apply-except-' + value + ' input').prop("disabled", true);
                $('.js-except-' + value).hide();
                $('.js-except-' + value + ' input').prop("disabled", true);
            } else {
                $('.js-apply-' + value).hide();
                $('.js-apply-' + value + ' input').prop("disabled", true);
                $('.js-apply-except-' + value).show();
                $('.js-apply-except-' + value + ' input').prop("disabled", false);
            }
        });
    }

    function changeOrderAddFieldExcept() {
        var fieldExcept = ['Category', 'Brand', 'Goods'];
        $.each(fieldExcept, function (key, value) {
            var v = value.toLowerCase();
            if ($('input:checkbox[name="orderAddFieldExcept' + value + 'Type"]').prop("checked") == true) {
                $('.js-except-' + v).show();
                $('.js-except-' + v + ' input').prop("disabled", false);
            } else {
                $('.js-except-' + v).hide();
                $('.js-except-' + v + ' input').prop("disabled", true);
            }
        });
    }

    /**
     * 구매 상품 범위 등록 / 예외 등록 Ajax layer
     *
     * @param string codeStr 타입
     * @param string modeStr 예외 여부
     */
    function layer_register(codeStr, modeStr, isDisabled) {
        var layerFormID = 'addFieldRangeForm';
        var addParam = '';
        var fileStr = '';
        if (typeof modeStr == 'undefined') {
            // 레이어 창
            var parentFormID = 'orderAddFieldApply' + codeStr;
            var dataFormID = 'id' + codeStr;
            var dataInputNm = 'orderAddFieldApply' + codeStr;
            var layerTitle = '적용 ';
        } else {
            var parentFormID = 'orderAddFieldExcept' + codeStr;
            var dataFormID = 'idExcept' + codeStr;
            var dataInputNm = 'orderAddFieldExcept' + codeStr;
            var layerTitle = '제외 ';
        }
        if (codeStr == 'Goods') {
            layerTitle = layerTitle + '상품';
            fileStr = 'goods';
            mode = 'simple';
            $("#" + parentFormID + " thead").show();
            $("#" + parentFormID + " tfoot").show();
        }
        if (codeStr == 'Category') {
            layerTitle = layerTitle + '카테고리';
            fileStr = 'category';
            mode = 'search';
            $("#" + parentFormID + " thead").show();
            $("#" + parentFormID + " tfoot").show();
        }
        if (codeStr == 'Brand') {
            layerTitle = layerTitle + '브랜드';
            fileStr = 'brand';
            mode = 'search';
            $("#" + parentFormID + " thead").show();
            $("#" + parentFormID + " tfoot").show();
        }

        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle,
            "disabled": isDisabled,
//            "callFunc": "",
        };

        layer_add_info(fileStr, addParam);
    }
</script>
