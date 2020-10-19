/**
 * Color Chart
 * @author sunny
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */

$(document).ready(function () {
	init_color_picker();
});

function init_color_picker() {
	// ColorPicker
	$('.color-selector')
		.ColorPicker({
			onSubmit: function(hsb, hex, rgb, el) {
				$(el).prev().val('#'+hex); // prev input element
				$(el).ColorPickerHide();
				$(el).css('backgroundColor', '#' + hex);
				$(el).prev().focus();
			}
			, onBeforeShow: function (cal) {
				var color = $($(cal).data('colorpicker').el).prev().val(); // prev input element
				var hex = $.ColorNameToHex(color);
				if (hex != undefined) color = hex;
				$(this).ColorPickerSetColor(color.replace(/#/,''));
			}
		})
		.css('backgroundColor', function(){
			var self = this;
			var ipt = $(this).prev();
			ipt.change(function(){
				$(self).css('backgroundColor', $(this).val());
			});
			return $(this).prev().val();
		});
}

(function($) {
	/**
	 * HEX를 REG로 변환 (#BF0000 - 191,0,0)
	 * @param {String} hex Hex
	 * @return {Object} REG
	 */
	$.HexToRGB = function (hex)
	{
		var hex = parseInt(((hex.indexOf('#') > -1) ? hex.substring(1) : hex), 16);
		return {r: hex >> 16, g: (hex & 0x00FF00) >> 8, b: (hex & 0x0000FF)};
	};

	/**
	 * HEX를 HSB로 변환 (#BF0000 - 0,100,74)
	 * @param {String} hex Hex
	 * @return {Object} HSB
	 */
	$.HexToHSB = function (hex) {
		return $.RGBToHSB($.HexToRGB(hex));
	};

	/**
	 * RGB를 HSB로 변환 (191,0,0 - 0,100,74)
	 * @param {Object} rgb REG
	 * @return {Object} HSB
	 */
	$.RGBToHSB = function (rgb) {
		var hsb = {
			h: 0,
			s: 0,
			b: 0
		};
		var min = Math.min(rgb.r, rgb.g, rgb.b);
		var max = Math.max(rgb.r, rgb.g, rgb.b);
		var delta = max - min;
		hsb.b = max;
		if (max != 0) {

		}
		hsb.s = max != 0 ? 255 * delta / max : 0;
		if (hsb.s != 0) {
			if (rgb.r == max) {
				hsb.h = (rgb.g - rgb.b) / delta;
			} else if (rgb.g == max) {
				hsb.h = 2 + (rgb.b - rgb.r) / delta;
			} else {
				hsb.h = 4 + (rgb.r - rgb.g) / delta;
			}
		} else {
			hsb.h = -1;
		}
		hsb.h *= 60;
		if (hsb.h < 0) {
			hsb.h += 360;
		}
		hsb.s *= 100/255;
		hsb.b *= 100/255;
		return hsb;
	};

	/**
	 * HSB를 RGB로 변환 (0,100,74 - 191,0,0)
	 * @param {Object} hsb HSB
	 * @return {Object} REG
	 */
	$.HSBToRGB = function (hsb) {
		var rgb = {};
		var h = Math.round(hsb.h);
		var s = Math.round(hsb.s*255/100);
		var v = Math.round(hsb.b*255/100);
		if(s == 0) {
			rgb.r = rgb.g = rgb.b = v;
		} else {
			var t1 = v;
			var t2 = (255-s)*v/255;
			var t3 = (t1-t2)*(h%60)/60;
			if(h==360) h = 0;
			if(h<60) {rgb.r=t1;	rgb.b=t2; rgb.g=t2+t3;}
			else if(h<120) {rgb.g=t1; rgb.b=t2;	rgb.r=t1-t3;}
			else if(h<180) {rgb.g=t1; rgb.r=t2;	rgb.b=t2+t3;}
			else if(h<240) {rgb.b=t1; rgb.r=t2;	rgb.g=t1-t3;}
			else if(h<300) {rgb.b=t1; rgb.g=t2;	rgb.r=t2+t3;}
			else if(h<360) {rgb.r=t1; rgb.g=t2;	rgb.b=t1-t3;}
			else {rgb.r=0; rgb.g=0;	rgb.b=0;}
		}
		return {r:Math.round(rgb.r), g:Math.round(rgb.g), b:Math.round(rgb.b)};
	};

	/**
	 * RGB를 HEX로 변환 (191,0,0 - #BF0000)
	 * @param {Object} rgb RGB
	 * @return {String} HEX
	 */
	$.RGBToHex = function (rgb) {
		var hex = [
			rgb.r.toString(16),
			rgb.g.toString(16),
			rgb.b.toString(16)
		];
		$.each(hex, function (nr, val) {
			if (val.length == 1) {
				hex[nr] = '0' + val;
			}
		});
		return hex.join('');
	};

	/**
	 * HSB를 HEX로 변환 (0,100,74 - #BF0000)
	 * @param {Object} hsb HSB
	 * @return {String} HEX
	 */
	$.HSBToHex = function (hsb) {
		return $.RGBToHex($.HSBToRGB(hsb));
	};

	/**
	 * ColorName를 HEX로 변환 (black - #000000)
	 * @param {String} colour ColorName
	 * @return {String} HEX
	 */
	$.ColorNameToHex = function (colour) {
		return colours[colour.toLowerCase()];
	};
	var colours = {
		'aliceblue':'F0F8FF'
		, 'antiquewhite':'FAEBD7'
		, 'aqua':'00FFFF'
		, 'aquamarine':'7FFFD4'
		, 'azure':'F0FFFF'
		, 'beige':'F5F5DC'
		, 'bisque':'FFE4C4'
		, 'black':'000000'
		, 'blanchedalmond':'FFEBCD'
		, 'blue':'0000FF'
		, 'blueviolet':'8A2BE2'
		, 'brown':'A52A2A'
		, 'burlywood':'DEB887'
		, 'cadetblue':'5F9EA0'
		, 'chartreuse':'7FFF00'
		, 'chocolate':'D2691E'
		, 'coral':'FF7F50'
		, 'cornflowerblue':'6495ED'
		, 'cornsilk':'FFF8DC'
		, 'crimson':'DC143C'
		, 'cyan':'00FFFF'
		, 'darkblue':'00008B'
		, 'darkcyan':'008B8B'
		, 'darkgoldenrod':'B8860B'
		, 'darkgray':'A9A9A9'
		, 'darkgreen':'006400'
		, 'darkkhaki':'BDB76B'
		, 'darkmagenta':'8B008B'
		, 'darkolivegreen':'556B2F'
		, 'darkorange':'FF8C00'
		, 'darkorchid':'9932CC'
		, 'darkred':'8B0000'
		, 'darksalmon':'E9967A'
		, 'darkseagreen':'8FBC8F'
		, 'darkslateblue':'483D8B'
		, 'darkslategray':'2F4F4F'
		, 'darkturquoise':'00CED1'
		, 'darkviolet':'9400D3'
		, 'deeppink':'FF1493'
		, 'deepskyblue':'00BFFF'
		, 'dimgray':'696969'
		, 'dodgerblue':'1E90FF'
		, 'firebrick':'B22222'
		, 'floralwhite':'FFFAF0'
		, 'forestgreen':'228B22'
		, 'fuchsia':'FF00FF'
		, 'gainsboro':'DCDCDC'
		, 'ghostwhite':'F8F8FF'
		, 'gold':'FFD700'
		, 'goldenrod':'DAA520'
		, 'gray':'808080'
		, 'green':'008000'
		, 'greenyellow':'ADFF2F'
		, 'honeydew':'F0FFF0'
		, 'hotpink':'FF69B4'
		, 'indianred':'CD5C5C'
		, 'indigo':'4B0082'
		, 'ivory':'FFFFF0'
		, 'khaki':'F0E68C'
		, 'lavender':'E6E6FA'
		, 'lavenderblush':'FFF0F5'
		, 'lawngreen':'7CFC00'
		, 'lemonchiffon':'FFFACD'
		, 'lightblue':'ADD8E6'
		, 'lightcoral':'F08080'
		, 'lightcyan':'E0FFFF'
		, 'lightgoldenrodyellow':'FAFAD2'
		, 'lightgreen':'90EE90'
		, 'lightgrey':'D3D3D3'
		, 'lightpink':'FFB6C1'
		, 'lightsalmon':'FFA07A'
		, 'lightseagreen':'20B2AD'
		, 'lightskyblue':'87CEFA'
		, 'lightslategray':'778899'
		, 'lightsteelblue':'B0C4DA'
		, 'lightyellow':'FFFFE0'
		, 'lime':'00FF00'
		, 'limegreen':'32CD32'
		, 'linen':'FAF0E6'
		, 'magenta':'FF00FF'
		, 'maroon':'800000'
		, 'mediumaquamarine':'66CDAA'
		, 'mediumblue':'0000CD'
		, 'mediumorchid':'BA55D3'
		, 'mediumpurple':'9370DB'
		, 'mediumseagreen':'3CB371'
		, 'mediumslateblue':'7B68EE'
		, 'mediumspringgreen':'00FA9A'
		, 'mediumturquoise':'48D1CC'
		, 'mediumvioletred':'C71585'
		, 'midnightblue':'191970'
		, 'mintcream':'F5FFFA'
		, 'mistyrose':'FFE4E1'
		, 'moccasin':'FFE4B5'
		, 'navajowhite':'FFDEAD'
		, 'navy':'000080'
		, 'oldlace':'FDF5E6'
		, 'olive':'808000'
		, 'olivedrab':'6B8E23'
		, 'orange':'FFA500'
		, 'orangered':'FF4500'
		, 'orchid':'DA70D6'
		, 'palegoldenrod':'EEE8AA'
		, 'palegreen':'98FB98'
		, 'paleturquoise':'AFEEEE'
		, 'palevioletred':'DB7093'
		, 'papayawhip':'FFEFD5'
		, 'peachpuff':'FFDAB9'
		, 'peru':'CD853F'
		, 'pink':'FFC0CB'
		, 'plum':'DDA0DD'
		, 'powderblue':'B0E0E6'
		, 'purple':'800080'
		, 'red':'FF0000'
		, 'rosybrown':'BC8F8F'
		, 'royalblue':'4169E1'
		, 'saddlebrown':'8B4513'
		, 'salmon':'FA8072'
		, 'sandybrown':'F4A460'
		, 'seagreen':'2E8B57'
		, 'seashell':'FFF5EE'
		, 'sienna':'A0522D'
		, 'silver':'C0C0C0'
		, 'skyblue':'87CEEB'
		, 'slateblue':'6A5ACD'
		, 'slategray':'708090'
		, 'snow':'FFFAFA'
		, 'springgreen':'00FF7F'
		, 'steelblue':'4682B4'
		, 'tan':'D2B48C'
		, 'teal':'008080'
		, 'thistle':'D8BFD8'
		, 'tomato':'FF6347'
		, 'turquoise':'40E0D0'
		, 'violet':'EE82EE'
		, 'wheat':'F5DEB3'
		, 'white':'FFFFFF'
		, 'whitesmoke':'F5F5F5'
		, 'yellow':'FFFF00'
		, 'yellowgreen':'9ACD32'
	};
})(jQuery);
