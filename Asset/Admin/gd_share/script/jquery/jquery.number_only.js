/**
 * number only - jQuery plugin
 *
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */

/**
 * input 박스에 숫자만 입력 받기
 *
 * 숫자만 입력을 받고, Backspace, Enter, 좌우 화살표, Ctrl + C, Ctrl + V 도 가능함
 * Ctrl + V 시 숫자이외의 문자가 있는 경우 제외시킴
 * ,(쉼표) 나 .(콤마) -(마이너스) 도 입력이 됨
 * @param integer stringLength 입력받을 숫자의 수
 * @param integer maxNum 입력받은 수의 최대값
 * @param integer defaultNum 입력받을 수가 최대값 이상인 경우 기본값
 * @param string "d" ,(쉼표) .(콤마) -(마이너스) 입력안됨. arg이름(위치)없음.  ex) $('#ex').number_only("d"); or $('#ex').number_only(5,100,10,"d");
 * <code>
 * <script type="text/javascript">
 * $('#formatTest').number_only(5,100,10);
 * </script>
 * <input type="text" id="formatTest" name="formatTest" value="" />
 * </code>
 */
(function($){
	$.fn.number_only = function(stringLength, maxNum, defaultNum) {
		/* 숫자만입력. sj */
		var onlyDigit = false;
		for(var i = 0; i < arguments.length; i++) {
			if (arguments[i] == 'd') {
				onlyDigit = true;
				break;
			}
		}
		return this.each(function() {
			$(this).keydown(function(event){
				// 숫자만입력. sj
				return (keyCodeObj[event.keyCode] && (!onlyDigit || (onlyDigit && $.inArray(event.keyCode, [188, 109, 189, 110, 190]) == -1))) ? true : false;
//				return keyCodeObj[event.keyCode] ? true : false;
			});
			$(this).keyup(function(event){
				// 해당 input text 에 기 작성된 숫자와 입력된 숫자를 비교
				var oldText	= $(this).val();
				// 숫자만입력. sj
				var newText	= (!onlyDigit)? oldText.replace(/[^0-9,.-]+/g,"") : oldText.replace(/[^0-9]+/g,"");
//				var newText	= oldText.replace(/[^0-9,.-]+/g,"");
				if(stringLength > 0 && newText.length > stringLength){
					newText	= newText.substr(0,stringLength);
				}
				var chkText	= parseInt(newText.replace(/[,]+/g,""));
				if (chkText > maxNum) {
					if (defaultNum) {
						newText	= defaultNum;
					} else {
						newText	= 0;
					}
				}
				// 비교후 동일하지 않은 경우 제한 새로운 숫자로 갱신 (탭으로 이동시 블럭 해제도 해결)
				if (oldText != newText) {
					$(this).val(newText);
				}
			});
			$(this).trigger('keyup');
		});
	};

	var keyCodeObj = {
		8	: 'Backspace',
		9	: 'Tab',
		13	: 'Enter',
		17	: 'Ctrl',
		16	: 'Shift',
		35	: 'End',
		36	: 'home',
		37	: 'Left Arrow',
		39	: 'Right Arrow',
		46	: 'Delete',
		48	: '0 - 키보드',
		49	: '1 - 키보드',
		50	: '2 - 키보드',
		51	: '3 - 키보드',
		52	: '4 - 키보드',
		53	: '5 - 키보드',
		54	: '6 - 키보드',
		55	: '7 - 키보드',
		56	: '8 - 키보드',
		57	: '9 - 키보드',
		96	: '0 - 키패드',
		97	: '1 - 키패드',
		98	: '2 - 키패드',
		99	: '3 - 키패드',
		100	: '4 - 키패드',
		101	: '5 - 키패드',
		102	: '6 - 키패드',
		103	: '7 - 키패드',
		104	: '8 - 키패드',
		105	: '9 - 키패드',
		67	: 'C , Ctrl + C',
		86	: 'V , Ctrl + V',
		188	: ',',
		109	: '- - 키패드',
		189	: '- - 키보드',
		110	: '. - 키패드',
		190	: '. - 키보드'
	};
})(jQuery);
