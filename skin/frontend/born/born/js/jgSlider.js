/*
 * jgSlider v1.0
 */
(function($){
	$.fn.jgSlider=function(options){
		$.fn.jgSlider.defaults={
			effect:"left",
			navName:(".small"),
			navCurClassName:("current"),
			mainName:(".big"),
			mainCurClassName:("current"),
			prevName:(".prev"),
			nextName:(".next"),
			mouseOverStop:true,
			defaultPlay:true,
			defaultPlayTime:3000,
		};
		return this.each(function(){
			var opts = $.extend({},$.fn.jgSlider.defaults,options);
			var slider = $(this);
			var effect = opts.effect;
			
			var mainObj = $(opts.mainName, slider);
			var mainOn = opts.mainCurClassName;
			
			var navObj = $(opts.navName, slider);
			var navNum = mainObj.find("li").size();//得出li元素个数
			var navOn = opts.navCurClassName;
			
			var prevBtn = $(opts.prevName, slider);
			var nextBtn = $(opts.nextName, slider);
			
			var mouseOverStop = opts.mouseOverStop;
			var defaultPlay = opts.defaultPlay;
			var defaultPlayTime = parseInt(opts.defaultPlayTime);
			
			var oWidth = mainObj.width();
			var oHeight = mainObj.height();
			var curIndex = 0;

			switch(effect){
				case "left":
					mainObj.find("ul").css({"position":"absolute","width":oWidth*navNum,"height":oHeight});
					mainObj.find("li").css({"float":"left","display":"inline-block"});
					break;
				case "top":
					mainObj.find("ul").css({"position":"absolute","width":oWidth,"height":oHeight*navNum});
					mainObj.find("li").css({"float":"left","display":"block"});
					break;
			}
			//切换函数
			function changeTo(now){
				switch(effect){
					case "left":
						mainObj.find("ul").animate({left:"-"+ now*oWidth + "px"},500);
						break;
					case "top":
						mainObj.find("ul").animate({top:"-"+ now*oHeight + "px"},500);
						break;
				}
				mainObj.find("li").removeClass(mainOn).eq(now).addClass(mainOn);
				navObj.find("li").removeClass(navOn).eq(now).addClass(navOn);
			};
			//导航点击切换
			navObj.find("li").each(function(index){
				$(this).click(function(){
					curIndex = index;
					changeTo(curIndex);
				})
			});
			//上/下按钮切换
			prevBtn.click(function(){
				curIndex = (curIndex == 0) ? (navNum-1) : (curIndex-1);
				changeTo(curIndex);
			});
			nextBtn.click(function(){
				curIndex = (curIndex == navNum-1) ? 0 : (curIndex+1);
				changeTo(curIndex);
			});
			//自动切换
			if(defaultPlay){
				var autoPlay = setInterval(function(){
					curIndex = (curIndex == navNum-1) ? 0 : (curIndex+1);
					changeTo(curIndex);
				},defaultPlayTime);
			}
			function autoPlayAgain(){
				autoPlay = setInterval(function(){
					curIndex = (curIndex == navNum-1) ? 0 : (curIndex+1);
					changeTo(curIndex);
				},defaultPlayTime)
			};
			//鼠标滑过停止自动切换/移开恢复
			if(mouseOverStop && defaultPlay){
				prevBtn.hover(function(){
					clearInterval(autoPlay);
				},function(){
					autoPlayAgain();
				});
				nextBtn.hover(function(){
					clearInterval(autoPlay);
				},function(){
					autoPlayAgain();
				});
				mainObj.hover(function(){
					clearInterval(autoPlay);
				},function(){
					autoPlayAgain();
				});
			};
		})
	}
})(jQuery)