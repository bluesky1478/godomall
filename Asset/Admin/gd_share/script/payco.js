/**
 * Created by hun on 2015-11-04.
 */
function copy_txt(val) {
    if (window.clipboardData) {
        alert("코드를 복사하였습니다. \n원하는 곳에 붙여넣기(Ctrl+V)를 하시면 됩니다.");
        window.clipboardData.setData("Text", val);
    }
    else {
        prompt("코드를 클립보드로 복사(Ctrl+C) 하시고. \n원하는 곳에 붙여넣기(Ctrl+V)를 하시면 됩니다.", val);
    }
}

function changeButtonType(buttonType) {
    var buttonTypeArray = new Array('A', 'B', 'C');
    for (var i = 0; i < buttonTypeArray.length; i++) {
        var displayElement = document.getElementById('buttonType' + buttonTypeArray[i]);
        if (buttonTypeArray[i] == buttonType) {
            displayElement.style.display = 'block';
        }
        else {
            displayElement.style.display = 'none';
        }
    }
}

function defaultTextValSetting(setType, el) {
    var textIDArr = new Array('paycoSellerKey', 'paycoCpId');
    var textMsgArr = new Array('가맹점코드', '상점ID');
    switch (setType) {
        case 'focus':
            el.style.color = '#000000';
            for (var i = 0; i < textIDArr.length; i++) {
                if (el.id == textIDArr[i] && el.value == textMsgArr[i]) {
                    el.value = '';
                    break;
                }
            }
            break;

        case 'blur':
            if (el.value == '') {
                for (var i = 0; i < textIDArr.length; i++) {
                    if (el.id == textIDArr[i]) {
                        el.value = textMsgArr[i];
                        el.style.color = '#A6A6A6';
                        break;
                    }
                }
            }
            break;

        case 'set':
            for (var i = 0; i < textIDArr.length; i++) {
                var textElement = document.getElementById(textIDArr[i]);
                if (textElement.value == '') {
                    textElement.value = textMsgArr[i];
                    textElement.style.color = '#A6A6A6';
                }
            }
            break;

        case 'submit':
            for (var i = 0; i < textIDArr.length; i++) {
                var textElement = document.getElementById(textIDArr[i]);
                if (textElement.value == textMsgArr[i]) textElement.value = '';
            }
            break;
    }
}

function submitSaveService() {
    nsGodoLoadingIndicator.init({
        psObject: document.getElementById('ifrmHidden')
    });
    nsGodoLoadingIndicator.show();
    return true;
}

function submitSaveID() {
    document.getElementById("saveId").style.display = "none";

    var f = document.paycoServiceForm;

    defaultTextValSetting('submit', '');

    if (!chkForm(f)) {
        document.getElementById("saveId").style.display = "block";
        return false;
    }

    if (!confirm("저장시 쇼핑몰에 페이코 구매 버튼이 노출됩니다.\n계속하시겠습니까?")) {
        document.getElementById("saveId").style.display = "block";
        return false;
    }

    nsGodoLoadingIndicator.init({});
    var param = getSaveIdParam(f);
    var ajax = new Ajax.Request("./paycoIndb.php",
        {
            method: "post",
            parameters: "mode=saveID" + param,
            onFailure: function () {
                nsGodoLoadingIndicator.hide();
                alert("죄송합니다. 통신이 정상적이지 않습니다.\n다시한번 시도하여 주세요.");
                document.getElementById("saveId").style.display = "block";
                return false;
            },
            onLoaded: function () {
                nsGodoLoadingIndicator.hide();
            },
            onLoading: function () {
                nsGodoLoadingIndicator.show();
            },
            onComplete: function (req) {
                if (req.status != 200 || req.responseText == '') {
                    alert("죄송합니다. 통신이 정상적이지 않습니다.\n다시한번 시도하여 주세요.");
                    document.getElementById("saveId").style.display = "block";
                    return false;
                }

                //result[0] - 성공여부, result[1] - 메시지
                var result = _returnValidateData = new Array();
                var elNameArr = {
                    seller_key: 'validateCheckMsg_paycoSellerKey',
                    cp_id: 'validateCheckMsg_paycoCpId'
                };
                var msgNameArr = {
                    seller_key: '가맹점코드',
                    cp_id: '상점ID'
                };

                result = req.responseText.split("|");
                if (result[0] == 'success') {
                    alert(result[1]);
                    window.document.location.reload();
                }
                else if (result[0] == 'validateFail') {
                    _returnValidateData = result[1].split("^");
                    for (var i in _returnValidateData) {
                        var returnValidateData = new Array();
                        returnValidateData = _returnValidateData[i].split("@");
                        if (returnValidateData[1] == 'Y') {
                            document.getElementById(elNameArr[returnValidateData[0]]).innerHTML = '사용가능한 ' + msgNameArr[returnValidateData[0]] + ' 입니다';
                            document.getElementById(elNameArr[returnValidateData[0]]).style.color = '#627dce';
                        }
                        else if (returnValidateData[1] == 'N') {
                            document.getElementById(elNameArr[returnValidateData[0]]).innerHTML = '유효한 ' + msgNameArr[returnValidateData[0]] + '가 아닙니다';
                            document.getElementById(elNameArr[returnValidateData[0]]).style.color = 'red';
                        }
                        else {
                            document.getElementById(elNameArr[returnValidateData[0]]).innerHTML = '';
                        }
                    }
                    document.getElementById("saveId").style.display = "block";
                }
                else {
                    alert(result[1]);
                    document.getElementById("saveId").style.display = "block";
                    return false;
                }
            }
        });
}

function getSaveIdParam(f) {
    var param = '';
    var formUseType = '';
    var formTestYn = '';

    //서비스 선택
    for (var i = 0; i < f.useType.length; i++) {
        if (f.useType[i].checked == true) {
            formUseType = f.useType[i].value;
            param += "&useType=" + f.useType[i].value;
            break;
        }
    }

    //사용 설정
    for (var i = 0; i < f.testYn.length; i++) {
        if (f.testYn[i].checked == true) {
            formTestYn = f.testYn[i].value;
            param += "&testYn=" + f.testYn[i].value;
            break;
        }
    }

    if (formUseType != 'N') {
        //가맹점코드
        param += "&paycoSellerKey=" + f.paycoSellerKey.value;
        //상점ID
        param += "&paycoCpId=" + f.paycoCpId.value;
    }

    return param;
}
