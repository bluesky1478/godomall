<script type="text/javascript">
<!--
	function copyCode(val) {
		$('#replaceCode').val(val);
		$("#replaceCode").select();
		copyTxt($("#replaceCode").val());
	}

	$(document).ready(function(){
		$("#frm").validate({
			submitHandler: function (form) {
				oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
				oEditors.getById["editor2"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
				form.target = 'ifrmProcess';
				form.submit();
			},
			// onclick: false, // <-- add this option
			rules: {
                subject: {maxlength:[30], required:true},
				contents: {
					required: function (textarea) {
						var editorcontent = oEditors.getById[textarea.id].getIR();	// 에디터의 내용 가져오기.
						editorcontent = editorcontent.replace(/<[^>]*>/gi, '').replace('&nbsp;', '');
						return editorcontent.length === 0;
					}
				},
				answer: {
					required: function (textarea) {
						var editorcontent = oEditors.getById[textarea.id].getIR();	// 에디터의 내용 가져오기.
						editorcontent = editorcontent.replace(/<[^>]*>/gi, '').replace('&nbsp;', '');
						return editorcontent.length === 0;
					}
				},
			},
			messages: {
                subject: {
                    required: '제목을 입력해주세요.',
                    maxlength: '제목은 최대 30자 까지 입력 가능합니다.'
                },
				contents: {
					required: '내용을 입력해주세요'
				},
				answer: {
					required: '답변을 입력해주세요'
				},
			}
		});
	});
//-->
</script>

<form id="frm" action="../board/faq_ps.php" method="post">
	<input type="hidden" name="mode" id="mode" value="<?=gd_isset($mode)?>" />
	<input type="hidden" name="sno" id="sno" value="<?=gd_isset($data['sno'])?>" />
	<div class="page-header js-affix">
		<h3><?= end($naviMenu->location); ?> <small>FAQ를 <?=gd_isset($modeTxt);?>합니다.</small></h3>
		<input type="submit" value="저장" class="btn btn-red" />
	</div>

    <?php if ($gGlobal['isUse'] == 'y') { ?>
        <input type="hidden" name="mallSno" value="<?=$mallSno?>">
        <ul class="multi-skin-nav nav nav-tabs" style="margin-bottom:20px;">
            <?php foreach ($gGlobal['useMallList'] as $key => $mall) {
                ?>
                <li role="presentation" class="js-popover <?=$mallSno == $mall['sno'] ? 'active' : 'passive'; ?>" data-html="true" data-content="<?=$mall['mallName']; ?>" data-placement="top">
                    <a href="./faq_register.php?mallSno=<?=$mall['sno']; ?>">
                        <span class="flag flag-16 flag-<?=$mall['domainFl']?>"></span>
                        <span class="mall-name"><?=$mall['mallName']; ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

	<div class="table-title gd-help-manual"><?= end($naviMenu->location); ?></div>
	<div>
		<table class="table table-cols">
		<colgroup><col class="width-md" /><col /></colgroup>
		<tr>
			<th >
				구분
			</th>
			<td>
				<?=gd_select_box('category', 'category', gd_code('03001',$mallSno), null, gd_isset($data['category'])) ?>
			</td>
		</tr>
		<tr>
			<th >
				Best
			</th>
			<td class="form-inline">
				<label class="checkbox-inline"><input type="checkbox" name="isBest" value="y"  <?=gd_isset($checked['isBest']['y'])?>> BEST FAQ 노출</label>

			</td>
		</tr>
		<tr>
			<th class="require">제목</th>
			<td>
				<input type="text" name="subject" value="<?=gd_isset($data['subject']);?>" class="form-control" />
			</td>
		</tr>
		<tr>
			<th class="require">내용</th>
			<td>
				<!-- mini editor -->
				<textarea name="contents" style="width:100%; height:350px;" id="editor" required="required" label="내용" class="replaceCode"><?=gd_isset($data['contents']);?></textarea>
			</td>
		</tr>
		<tr>
			<th >답변</th>
			<td>
				<!-- mini editor -->
				<textarea name="answer" style="width:100%; height:350px;" id="editor2" required="required" label="답변" class="replaceCode"><?=gd_isset($data['answer']);?></textarea>
			</td>
		</tr>
		</table>
	</div>
</form>

<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js" charset="utf-8"></script>
<script type="text/javascript">
	nhn.husky.EZCreator.createInIFrame({
		oAppRef: oEditors,
		elPlaceHolder: "editor2",
		sSkinURI: "<?=PATH_ADMIN_GD_SHARE?>script/smart/SmartEditor2Skin.php",
		htParams: {
			bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
			bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
			bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
			//aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
			fOnBeforeUnload: function () {
				//alert("완료!");
			}
		}, //boolean
		fOnAppLoad: function () {
			//예제 코드
			//oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
		},
		fCreator: "createSEditor2"
	});
</script>

