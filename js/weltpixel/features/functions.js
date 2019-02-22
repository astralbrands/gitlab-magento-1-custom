var $j = jQuery.noConflict();

$j.fn.inlineStyle = function (prop) {
	return this.prop("style")[$j.camelCase(prop)];
};

$j.fn.doOnce = function( func ) {
	this.length && func.apply( this );
	return this;
}

$j.extend($j.infinitescroll.prototype,{
	_setup_portfolioinfiniteitemsloader: function infscr_setup_portfolioinfiniteitemsloader() {
		var opts = this.options,
			instance = this;
		// Bind nextSelector link to retrieve
		$j(opts.nextSelector).click(function(e) {
			if (e.which == 1 && !e.metaKey && !e.shiftKey) {
				e.preventDefault();
				instance.retrieve();
			}
		});
		// Define loadingStart to never hide pager
		instance.options.loading.start = function (opts) {
			opts.loading.msg
				.appendTo(opts.loading.selector)
				.show(opts.loading.speed, function () {
					instance.beginAjax(opts);
				});
		}
	},
	_showdonemsg_portfolioinfiniteitemsloader: function infscr_showdonemsg_portfolioinfiniteitemsloader () {
		var opts = this.options,
			instance = this;
		//Do all the usual stuff
		opts.loading.msg
			.find('img')
			.hide()
			.parent()
			.find('div').html(opts.loading.finishedMsg).animate({ opacity: 1 }, 2000, function () {
				$j(this).parent().fadeOut('normal');
			});
		//And also hide the navSelector
		$j(opts.navSelector).fadeOut('normal');
		// user provided callback when done
		opts.errorCallback.call($j(opts.contentSelector)[0],'done');
	}
});

(function() {
	var lastTime = 0;
	var vendors = ['ms', 'moz', 'webkit', 'o'];
	for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
		window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
		window.cancelAnimationFrame = window[vendors[x]+'CancelAnimationFrame']
									|| window[vendors[x]+'CancelRequestAnimationFrame'];
	}

	if (!window.requestAnimationFrame)
		window.requestAnimationFrame = function(callback, element) {
			var currTime = new Date().getTime();
			var timeToCall = Math.max(0, 16 - (currTime - lastTime));
			var id = window.setTimeout(function() { callback(currTime + timeToCall); },
			  timeToCall);
			lastTime = currTime + timeToCall;
			return id;
		};

	if (!window.cancelAnimationFrame)
		window.cancelAnimationFrame = function(id) {
			clearTimeout(id);
		};
}());



var SEMICOLON = SEMICOLON || {};

(function($j){

	// USE STRICT
	"use strict";

	SEMICOLON.initialize = {

		init: function(){

			SEMICOLON.initialize.responsiveClasses();
			SEMICOLON.initialize.imagePreload( '.portfolio-item:not(:has(.fslider)) img' );
			SEMICOLON.initialize.stickyElements();
			SEMICOLON.initialize.goToTop();
			SEMICOLON.initialize.fullScreen();
			SEMICOLON.initialize.verticalMiddle();
			SEMICOLON.initialize.lightbox();
			SEMICOLON.initialize.resizeVideos();
			SEMICOLON.initialize.imageFade();
			SEMICOLON.initialize.pageTransition();
			SEMICOLON.initialize.dataStyles();
			SEMICOLON.initialize.dataResponsiveHeights();

			$j('.fslider').addClass('preloader2');

		},

		responsiveClasses: function(){
			var jRes = jRespond([
				{
					label: 'smallest',
					enter: 0,
					exit: 479
				},{
					label: 'handheld',
					enter: 480,
					exit: 767
				},{
					label: 'tablet',
					enter: 768,
					exit: 991
				},{
					label: 'laptop',
					enter: 992,
					exit: 1199
				},{
					label: 'desktop',
					enter: 1200,
					exit: 10000
				}
			]);
			jRes.addFunc([
				{
					breakpoint: 'desktop',
					enter: function() { $jbody.addClass('device-lg'); },
					exit: function() { $jbody.removeClass('device-lg'); }
				},{
					breakpoint: 'laptop',
					enter: function() { $jbody.addClass('device-md'); },
					exit: function() { $jbody.removeClass('device-md'); }
				},{
					breakpoint: 'tablet',
					enter: function() { $jbody.addClass('device-sm'); },
					exit: function() { $jbody.removeClass('device-sm'); }
				},{
					breakpoint: 'handheld',
					enter: function() { $jbody.addClass('device-xs'); },
					exit: function() { $jbody.removeClass('device-xs'); }
				},{
					breakpoint: 'smallest',
					enter: function() { $jbody.addClass('device-xxs'); },
					exit: function() { $jbody.removeClass('device-xxs'); }
				}
			]);
		},

		imagePreload: function(selector, parameters){
			var params = {
				delay: 250,
				transition: 400,
				easing: 'linear'
			};
			$j.extend(params, parameters);

			$j(selector).each(function() {
				var image = $j(this);
				image.css({visibility:'hidden', opacity: 0, display:'block'});
				image.wrap('<span class="preloader" />');
				image.one("load", function(evt) {
					$j(this).delay(params.delay).css({visibility:'visible'}).animate({opacity: 1}, params.transition, params.easing, function() {
						$j(this).unwrap('<span class="preloader" />');
					});
				}).each(function() {
					if(this.complete) $j(this).trigger("load");
				});
			});
		},

		verticalMiddle: function(){
			if( $jverticalMiddleEl.length > 0 ) {
				$jverticalMiddleEl.each( function(){
					var element = $j(this),
						verticalMiddleH = element.outerHeight(),
						headerHeight = $jheader.outerHeight();

					if( element.parents('#slider').length > 0 && !element.hasClass('ignore-header') ) {
						if( $jheader.hasClass('transparent-header') && ( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ) ) {
							verticalMiddleH = verticalMiddleH - 70;
							if( $jslider.next('#header').length > 0 ) { verticalMiddleH = verticalMiddleH + headerHeight; }
						}
					}

					if( $jbody.hasClass('device-xs') || $jbody.hasClass('device-xxs') ) {
						if( element.parents('.full-screen').length && !element.parents('.force-full-screen').length ){
							element.css({ position: 'relative', top: '0', width: 'auto', marginTop: '0', padding: '60px 0' }).addClass('clearfix');
						} else {
							element.css({ position: 'absolute', top: '50%', width: '100%', marginTop: -(verticalMiddleH/2)+'px' });
						}
					} else {
						element.css({ position: 'absolute', top: '50%', width: '100%', marginTop: -(verticalMiddleH/2)+'px' });
					}
				});
			}
		},

		stickyElements: function(){
			if( $jsiStickyEl.length > 0 ) {
				var siStickyH = $jsiStickyEl.outerHeight();
				$jsiStickyEl.css({ marginTop: -(siStickyH/2)+'px' });
			}

			if( $jdotsMenuEl.length > 0 ) {
				var opmdStickyH = $jdotsMenuEl.outerHeight();
				$jdotsMenuEl.css({ marginTop: -(opmdStickyH/2)+'px' });
			}
		},

		goToTop: function(){
			$jgoToTopEl.click(function() {
				$j('body,html').stop(true).animate({scrollTop:0},400);
				return false;
			});
		},

		goToTopScroll: function(){
			if( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') || $jbody.hasClass('device-sm') ) {
				if($jwindow.scrollTop() > 450) {
					$jgoToTopEl.fadeIn();
				} else {
					$jgoToTopEl.fadeOut();
				}
			}
		},

		fullScreen: function(){
			if( $jfullScreenEl.length > 0 ) {
				$jfullScreenEl.each( function(){
					var element = $j(this),
						scrHeight = $jwindow.height();
					if( element.attr('id') == 'slider' ) {
						var sliderHeightOff = $jslider.offset().top;
						scrHeight = scrHeight - sliderHeightOff;
						if( element.hasClass('slider-parallax') ) {
							var transformVal = element.css('transform'),
								transformX = transformVal.match(/-?[\d\.]+/g);
							scrHeight = ( $jwindow.height() + Number(transformX[5]) ) - sliderHeightOff;
						}
						if( $j('#slider.with-header').next('#header:not(.transparent-header)').length > 0 && ( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ) ) {
							var headerHeightOff = $jheader.outerHeight();
							scrHeight = scrHeight - headerHeightOff;
						}
					}
					if( element.parents('.full-screen').length > 0 ) { scrHeight = element.parents('.full-screen').height(); }

					if( $jbody.hasClass('device-xs') || $jbody.hasClass('device-xxs') ) {
						if( !element.hasClass('force-full-screen') ){ scrHeight = 'auto'; }
					}

					element.css('height', scrHeight);
					if( element.attr('id') == 'slider' && !element.hasClass('canvas-slider-grid') ) { if( element.has('.swiper-slide') ) { element.find('.swiper-slide').css('height', scrHeight); } }
				});
			}
		},

		maxHeight: function(){
			if( $jcommonHeightEl.length > 0 ) {
				$jcommonHeightEl.each( function(){
					var element = $j(this);
					if( element.has('.common-height') ) {
						SEMICOLON.initialize.commonHeight( element.find('.common-height') );
					}

					SEMICOLON.initialize.commonHeight( element );
				});
			}
		},

		commonHeight: function( element ){
			var maxHeight = 0;
			element.children('[class^=col-]').each(function() {
				var element = $j(this).children('div');
				if( element.hasClass('max-height') ){
					maxHeight = element.outerHeight();
				} else {
					if (element.outerHeight() > maxHeight)
					maxHeight = element.outerHeight();
				}
			});

			element.children('[class^=col-]').each(function() {
				$j(this).height(maxHeight);
			});
		},

		testimonialsGrid: function(){
			if( $jtestimonialsGridEl.length > 0 ) {
				if( $jbody.hasClass('device-sm') || $jbody.hasClass('device-md') || $jbody.hasClass('device-lg') ) {
					var maxHeight = 0;
					$jtestimonialsGridEl.each( function(){
						$j(this).find("li > .testimonial").each(function(){
						   if ($j(this).height() > maxHeight) { maxHeight = $j(this).height(); }
						});
						$j(this).find("li").height(maxHeight);
						maxHeight = 0;
					});
				} else {
					$jtestimonialsGridEl.find("li").css({ 'height': 'auto' });
				}
			}
		},

		lightbox: function(){
			var $jlightboxImageEl = $j('[data-lightbox="image"]'),
				$jlightboxGalleryEl = $j('[data-lightbox="gallery"]'),
				$jlightboxIframeEl = $j('[data-lightbox="iframe"]'),
				$jlightboxAjaxEl = $j('[data-lightbox="ajax"]'),
				$jlightboxAjaxGalleryEl = $j('[data-lightbox="ajax-gallery"]');

			if( $jlightboxImageEl.length > 0 ) {
				$jlightboxImageEl.magnificPopup({
					type: 'image',
					closeOnContentClick: true,
					closeBtnInside: false,
					fixedContentPos: true,
					mainClass: 'mfp-no-margins mfp-fade', // class to remove default margin from left and right side
					image: {
						verticalFit: true
					}
				});
			}

			if( $jlightboxGalleryEl.length > 0 ) {
				$jlightboxGalleryEl.each(function() {
					var element = $j(this);

					if( element.find('a[data-lightbox="gallery-item"]').parent('.clone').hasClass('clone') ) {
						element.find('a[data-lightbox="gallery-item"]').parent('.clone').find('a[data-lightbox="gallery-item"]').attr('data-lightbox','');
					}

					element.magnificPopup({
						delegate: 'a[data-lightbox="gallery-item"]',
						type: 'image',
						closeOnContentClick: true,
						closeBtnInside: false,
						fixedContentPos: true,
						mainClass: 'mfp-no-margins mfp-fade', // class to remove default margin from left and right side
						image: {
							verticalFit: true
						},
						gallery: {
							enabled: true,
							navigateByImgClick: true,
							preload: [0,1] // Will preload 0 - before current, and 1 after the current image
						}
					});
				});
			}

			if( $jlightboxIframeEl.length > 0 ) {
				$jlightboxIframeEl.magnificPopup({
					disableOn: 600,
					type: 'iframe',
					removalDelay: 160,
					preloader: false,
					fixedContentPos: false
				});
			}

			if( $jlightboxAjaxEl.length > 0 ) {
				$jlightboxAjaxEl.magnificPopup({
					type: 'ajax',
					closeBtnInside: false,
					callbacks: {
						ajaxContentAdded: function(mfpResponse) {
							SEMICOLON.widget.loadFlexSlider();
							SEMICOLON.initialize.resizeVideos();
							SEMICOLON.widget.masonryThumbs();
						},
						open: function() {
							$jbody.addClass('ohidden');
						},
						close: function() {
							$jbody.removeClass('ohidden');
						}
					}
				});
			}

			if( $jlightboxAjaxGalleryEl.length > 0 ) {
				$jlightboxAjaxGalleryEl.magnificPopup({
					delegate: 'a[data-lightbox="ajax-gallery-item"]',
					type: 'ajax',
					closeBtnInside: false,
					gallery: {
						enabled: true,
						preload: 0,
						navigateByImgClick: false
					},
					callbacks: {
						ajaxContentAdded: function(mfpResponse) {
							SEMICOLON.widget.loadFlexSlider();
							SEMICOLON.initialize.resizeVideos();
							SEMICOLON.widget.masonryThumbs();
						},
						open: function() {
							$jbody.addClass('ohidden');
						},
						close: function() {
							$jbody.removeClass('ohidden');
						}
					}
				});
			}
		},

		resizeVideos: function(){
			if ( $j().fitVids ) {
				$j("#content,#footer,#slider:not(.revslider-wrap),.landing-offer-media,.portfolio-ajax-modal").fitVids({
					customSelector: "iframe[src^='http://www.dailymotion.com/embed']",
					ignore: '.no-fv'
				});
			}
		},

		imageFade: function(){
			$j('.image_fade').hover( function(){
				$j(this).filter(':not(:animated)').animate({opacity: 0.8}, 400);
			}, function() {
				$j(this).animate({opacity: 1}, 400);
			});
		},

		blogTimelineEntries: function(){
			$j('.post-timeline.grid-2').find('.entry').each( function(){
				var position = $j(this).inlineStyle('left');
				if( position == '0px' ) {
					$j(this).removeClass('alt');
				} else {
					$j(this).addClass('alt');
				}
				$j(this).find('.entry-timeline').fadeIn();
			});
		},

		pageTransition: function(){
			if( !$jbody.hasClass('no-transition') ){
				var animationIn = $jbody.attr('data-animation-in'),
					animationOut = $jbody.attr('data-animation-out'),
					durationIn = $jbody.attr('data-speed-in'),
					durationOut = $jbody.attr('data-speed-out'),
					loaderStyle = $jbody.attr('data-loader'),
					loaderStyleHtml = '<div class="css3-spinner-bounce1"></div><div class="css3-spinner-bounce2"></div><div class="css3-spinner-bounce3"></div>';

				if( !animationIn ) { animationIn = 'fadeIn'; }
				if( !animationOut ) { animationOut = 'fadeOut'; }
				if( !durationIn ) { durationIn = 1500; }
				if( !durationOut ) { durationOut = 800; }

				if( loaderStyle == '2' ) {
					loaderStyleHtml = '<div class="css3-spinner-flipper"></div>';
				} else if( loaderStyle == '3' ) {
					loaderStyleHtml = '<div class="css3-spinner-double-bounce1"></div><div class="css3-spinner-double-bounce2"></div>';
				} else if( loaderStyle == '4' ) {
					loaderStyleHtml = '<div class="css3-spinner-rect1"></div><div class="css3-spinner-rect2"></div><div class="css3-spinner-rect3"></div><div class="css3-spinner-rect4"></div><div class="css3-spinner-rect5"></div>';
				} else if( loaderStyle == '5' ) {
					loaderStyleHtml = '<div class="css3-spinner-cube1"></div><div class="css3-spinner-cube2"></div>';
				} else if( loaderStyle == '6' ) {
					loaderStyleHtml = '<div class="css3-spinner-scaler"></div>';
				}

				$jwrapper.animsition({
					inClass : animationIn,
					outClass : animationOut,
					inDuration : Number(durationIn),
					outDuration : Number(durationOut),
					linkElement : '#primary-menu ul li a:not([target="_blank"]):not([href^=#])',
					loading : true,
					loadingParentElement : 'body',
					loadingClass : 'css3-spinner',
					loadingHtml : loaderStyleHtml,
					unSupportCss : [
									 'animation-duration',
									 '-webkit-animation-duration',
									 '-o-animation-duration'
								   ],
					overlay : false,
					overlayClass : 'animsition-overlay-slide',
					overlayParentElement : 'body'
				});
			}
		},

		topScrollOffset: function() {
			var topOffsetScroll = 0;

			if( ( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ) && !SEMICOLON.isMobile.any() ) {
				if( $jheader.hasClass('sticky-header') ) {
					if( $jpagemenu.hasClass('dots-menu') ) { topOffsetScroll = 100; } else { topOffsetScroll = 144; }
				} else {
					if( $jpagemenu.hasClass('dots-menu') ) { topOffsetScroll = 140; } else { topOffsetScroll = 184; }
				}

				if( !$jpagemenu.length ) {
					if( $jheader.hasClass('sticky-header') ) { topOffsetScroll = 100; } else { topOffsetScroll = 140; }
				}
			} else {
				topOffsetScroll = 40;
			}

			return topOffsetScroll;
		},

		defineColumns: function( element ){
			var column = 4;

			if( element.hasClass('portfolio-full') ) {
				if( element.hasClass('portfolio-3') ) column = 3;
				else if( element.hasClass('portfolio-5') ) column = 5;
				else if( element.hasClass('portfolio-6') ) column = 6;
				else column = 4;

				if( $jbody.hasClass('device-sm') && ( column == 4 || column == 5 || column == 6 ) ) {
					column = 3;
				} else if( $jbody.hasClass('device-xs') && ( column == 3 || column == 4 || column == 5 || column == 6 ) ) {
					column = 2;
				} else if( $jbody.hasClass('device-xxs') ) {
					column = 1;
				}
			} else if( element.hasClass('masonry-thumbs') ) {

				var lgCol = element.attr('data-lg-col'),
					mdCol = element.attr('data-md-col'),
					smCol = element.attr('data-sm-col'),
					xsCol = element.attr('data-xs-col'),
					xxsCol = element.attr('data-xxs-col');

				if( element.hasClass('coll-2') ) column = 2;
				else if( element.hasClass('coll-3') ) column = 3;
				else if( element.hasClass('coll-5') ) column = 5;
				else if( element.hasClass('coll-6') ) column = 6;
				else column = 4;

				if( $jbody.hasClass('device-lg') ) {
					if( lgCol ) { column = Number(lgCol); }
				} else if( $jbody.hasClass('device-md') ) {
					if( mdCol ) { column = Number(mdCol); }
				} else if( $jbody.hasClass('device-sm') ) {
					if( smCol ) { column = Number(smCol); }
				} else if( $jbody.hasClass('device-xs') ) {
					if( xsCol ) { column = Number(xsCol); }
				} else if( $jbody.hasClass('device-xxs') ) {
					if( xxsCol ) { column = Number(xxsCol); }
				}

			}

			return column;
		},

		setFullColumnWidth: function( element ){

			if( element.hasClass('portfolio-full') ) {
				var columns = SEMICOLON.initialize.defineColumns( element );
				var containerWidth = element.width();
				if( containerWidth == ( Math.floor(containerWidth/columns) * columns ) ) { containerWidth = containerWidth - 1; }
				var postWidth = Math.floor(containerWidth/columns);
				if( $jbody.hasClass('device-xxs') ) { var deviceSmallest = 1; } else { var deviceSmallest = 0; }
				element.find(".portfolio-item").each(function(index){
					if( deviceSmallest == 0 && $j(this).hasClass('wide') ) { var elementSize = ( postWidth*2 ); } else { var elementSize = postWidth; }
					$j(this).css({"width":elementSize+"px"});
				});
			} else if( element.hasClass('masonry-thumbs') ) {
				var columns = SEMICOLON.initialize.defineColumns( element ),
					containerWidth = element.innerWidth(),
					windowWidth = $jwindow.width();
				if( containerWidth == windowWidth ){
					containerWidth = windowWidth*1.004;
					element.css({ 'width': containerWidth+'px' });
				}
				var postWidth = (containerWidth/columns);

				postWidth = Math.floor(postWidth);

				if( ( postWidth * columns ) >= containerWidth ) { element.css({ 'margin-right': '-1px' }); }

				element.children('a').css({"width":postWidth+"px"});

				var firstElementWidth = element.find('a:eq(0)').outerWidth();

				element.isotope({
					masonry: {
						columnWidth: firstElementWidth
					}
				});

				var bigImageNumbers = element.attr('data-big');
				if( bigImageNumbers ) {
					bigImageNumbers = bigImageNumbers.split(",");
					var bigImageNumber = '',
						bigi = '';
					for( bigi = 0; bigi < bigImageNumbers.length; bigi++ ){
						bigImageNumber = Number(bigImageNumbers[bigi]) - 1;
						element.find('a:eq('+bigImageNumber+')').css({ width: firstElementWidth*2 + 'px' });
					}
					var t = setTimeout( function(){
						element.isotope('layout');
					}, 1000 );
				}
			}

		},

		aspectResizer: function(){
			var $jaspectResizerEl = $j('.aspect-resizer');
			if( $jaspectResizerEl.length > 0 ) {
				$jaspectResizerEl.each( function(){
					var element = $j(this),
						elementW = element.inlineStyle('width'),
						elementH = element.inlineStyle('height'),
						elementPW = element.parent().innerWidth();
				});
			}
		},

		dataStyles: function(){
			var $jdataStyleXxs = $j('[data-style-xxs]'),
				$jdataStyleXs = $j('[data-style-xs]'),
				$jdataStyleSm = $j('[data-style-sm]'),
				$jdataStyleMd = $j('[data-style-md]'),
				$jdataStyleLg = $j('[data-style-lg]');

			if( $jdataStyleXxs.length > 0 ) {
				$jdataStyleXxs.each( function(){
					var element = $j(this),
						elementStyle = element.attr('data-style-xxs');

					if( $jbody.hasClass('device-xxs') ) {
						if( elementStyle != '' ) { element.attr( 'style', elementStyle ); }
					}
				});
			}

			if( $jdataStyleXs.length > 0 ) {
				$jdataStyleXs.each( function(){
					var element = $j(this),
						elementStyle = element.attr('data-style-xs');

					if( $jbody.hasClass('device-xs') ) {
						if( elementStyle != '' ) { element.attr( 'style', elementStyle ); }
					}
				});
			}

			if( $jdataStyleSm.length > 0 ) {
				$jdataStyleSm.each( function(){
					var element = $j(this),
						elementStyle = element.attr('data-style-sm');

					if( $jbody.hasClass('device-sm') ) {
						if( elementStyle != '' ) { element.attr( 'style', elementStyle ); }
					}
				});
			}

			if( $jdataStyleMd.length > 0 ) {
				$jdataStyleMd.each( function(){
					var element = $j(this),
						elementStyle = element.attr('data-style-md');

					if( $jbody.hasClass('device-md') ) {
						if( elementStyle != '' ) { element.attr( 'style', elementStyle ); }
					}
				});
			}

			if( $jdataStyleLg.length > 0 ) {
				$jdataStyleLg.each( function(){
					var element = $j(this),
						elementStyle = element.attr('data-style-lg');

					if( $jbody.hasClass('device-lg') ) {
						if( elementStyle != '' ) { element.attr( 'style', elementStyle ); }
					}
				});
			}
		},

		dataResponsiveHeights: function(){
			var $jdataHeightXxs = $j('[data-height-xxs]'),
				$jdataHeightXs = $j('[data-height-xs]'),
				$jdataHeightSm = $j('[data-height-sm]'),
				$jdataHeightMd = $j('[data-height-md]'),
				$jdataHeightLg = $j('[data-height-lg]');

			if( $jdataHeightXxs.length > 0 ) {
				$jdataHeightXxs.each( function(){
					var element = $j(this),
						elementHeight = element.attr('data-height-xxs');

					if( $jbody.hasClass('device-xxs') ) {
						if( elementHeight != '' ) { element.css( 'height', elementHeight ); }
					}
				});
			}

			if( $jdataHeightXs.length > 0 ) {
				$jdataHeightXs.each( function(){
					var element = $j(this),
						elementHeight = element.attr('data-height-xs');

					if( $jbody.hasClass('device-xs') ) {
						if( elementHeight != '' ) { element.css( 'height', elementHeight ); }
					}
				});
			}

			if( $jdataHeightSm.length > 0 ) {
				$jdataHeightSm.each( function(){
					var element = $j(this),
						elementHeight = element.attr('data-height-sm');

					if( $jbody.hasClass('device-sm') ) {
						if( elementHeight != '' ) { element.css( 'height', elementHeight ); }
					}
				});
			}

			if( $jdataHeightMd.length > 0 ) {
				$jdataHeightMd.each( function(){
					var element = $j(this),
						elementHeight = element.attr('data-height-md');

					if( $jbody.hasClass('device-md') ) {
						if( elementHeight != '' ) { element.css( 'height', elementHeight ); }
					}
				});
			}

			if( $jdataHeightLg.length > 0 ) {
				$jdataHeightLg.each( function(){
					var element = $j(this),
						elementHeight = element.attr('data-height-lg');

					if( $jbody.hasClass('device-lg') ) {
						if( elementHeight != '' ) { element.css( 'height', elementHeight ); }
					}
				});
			}
		},

		stickFooterOnSmall: function(){
			var windowH = $jwindow.height(),
				wrapperH = $jwrapper.height();

			if( $jfooter.length > 0 && $jwrapper.has('#footer') ) {
				if( windowH > wrapperH ) {
					$jfooter.css({ 'margin-top': ( windowH - wrapperH ) });
				}
			}
		}

	};

	SEMICOLON.header = {

		init: function(){

			SEMICOLON.header.superfish();
			SEMICOLON.header.menufunctions();
			SEMICOLON.header.fullWidthMenu();
			SEMICOLON.header.overlayMenu();
			SEMICOLON.header.stickyMenu();
			SEMICOLON.header.sideHeader();
			SEMICOLON.header.sidePanel();
			SEMICOLON.header.onePageScroll();
			SEMICOLON.header.onepageScroller();
			SEMICOLON.header.darkLogo();
			SEMICOLON.header.topsearch();
			SEMICOLON.header.topcart();

		},

		superfish: function(){

			if ( $j().superfish ) {
				if( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ) {
					$j('#primary-menu ul ul, #primary-menu ul .mega-menu-content').css('display', 'block');
					SEMICOLON.header.menuInvert();
				}

				$j('body:not(.side-header) #primary-menu > ul, body:not(.side-header) #primary-menu > div > ul,.top-links > ul').superfish({
					popUpSelector: 'ul,.mega-menu-content,.top-link-section',
					delay: 250,
					speed: 350,
					animation: {opacity:'show'},
					animationOut:  {opacity:'hide'},
					cssArrows: false
				});

				$j('body.side-header #primary-menu > ul').superfish({
					popUpSelector: 'ul',
					delay: 250,
					speed: 350,
					animation: {opacity:'show',height:'show'},
					animationOut:  {opacity:'hide',height:'hide'},
					cssArrows: false
				});
			}

		},

		menuInvert: function() {

			$j('#primary-menu .mega-menu-content, #primary-menu ul ul').each( function( index, element ){
				var $jmenuChildElement = $j(element);
				var windowWidth = $jwindow.width();
				var menuChildOffset = $jmenuChildElement.offset();
				var menuChildWidth = $jmenuChildElement.width();
				var menuChildLeft = menuChildOffset.left;

				if(windowWidth - (menuChildWidth + menuChildLeft) < 0) {
					$jmenuChildElement.addClass('menu-pos-invert');
				}
			});

		},

		menufunctions: function(){

			$j( '#primary-menu ul li:has(ul)' ).addClass('sub-menu');
			$j( '.top-links ul li:has(ul) > a' ).append( ' <i class="icon-angle-down"></i>' );
			$j( '.top-links > ul' ).addClass( 'clearfix' );

			if( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ) {
				$j('#primary-menu.sub-title > ul > li,#primary-menu.sub-title > div > ul > li').hover(function() {
					$j(this).prev().css({ backgroundImage : 'none' });
				}, function() {
					$j(this).prev().css({ backgroundImage : 'url("images/icons/menu-divider.png")' });
				});

				$j('#primary-menu.sub-title').children('ul').children('.current').prev().css({ backgroundImage : 'none' });
				$j('#primary-menu.sub-title').children('div').children('ul').children('.current').prev().css({ backgroundImage : 'none' });
			}

			if( SEMICOLON.isMobile.Android() ) {
				$j( '#primary-menu ul li.sub-menu' ).children('a').on('touchstart', function(e){
					if( !$j(this).parent('li.sub-menu').hasClass('sfHover') ) {
						e.preventDefault();
					}
				});
			}

			if( SEMICOLON.isMobile.Windows() ) {
				$j('#primary-menu > ul, #primary-menu > div > ul,.top-links > ul').superfish('destroy').addClass('windows-mobile-menu');

				$j( '#primary-menu ul li:has(ul)' ).append('<a href="#" class="wn-submenu-trigger"><i class="icon-angle-down"></i></a>');

				$j( '#primary-menu ul li.sub-menu' ).children('a.wn-submenu-trigger').click( function(e){
					$j(this).parent().toggleClass('open');
					$j(this).parent().find('> ul, > .mega-menu-content').stop(true,true).toggle();
					return false;
				});
			}

		},

		fullWidthMenu: function(){
			if( $jbody.hasClass('stretched') ) {
				if( $jheader.find('.container-fullwidth').length > 0 ) { $j('.mega-menu .mega-menu-content').css({ 'width': $jwrapper.width() - 120 }); }
				if( $jheader.hasClass('full-header') ) { $j('.mega-menu .mega-menu-content').css({ 'width': $jwrapper.width() - 60 }); }
			} else {
				if( $jheader.find('.container-fullwidth').length > 0 ) { $j('.mega-menu .mega-menu-content').css({ 'width': $jwrapper.width() - 120 }); }
				if( $jheader.hasClass('full-header') ) { $j('.mega-menu .mega-menu-content').css({ 'width': $jwrapper.width() - 80 }); }
			}
		},

		overlayMenu: function(){
			if( $jbody.hasClass('overlay-menu') ) {
				var overlayMenuItem = $j('#primary-menu').children('ul').children('li'),
					overlayMenuItemHeight = overlayMenuItem.outerHeight(),
					overlayMenuItemTHeight = overlayMenuItem.length * overlayMenuItemHeight,
					firstItemOffset = ( $jwindow.height() - overlayMenuItemTHeight ) / 2;

				$j('#primary-menu').children('ul').children('li:first-child').css({ 'margin-top': firstItemOffset+'px' });
			}
		},

		stickyMenu: function( headerOffset ){
			if ($jwindow.scrollTop() > headerOffset) {
				if( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ) {
					$j('body:not(.side-header) #header:not(.no-sticky)').addClass('sticky-header');
					$j('#page-menu:not(.dots-menu,.no-sticky)').addClass('sticky-page-menu');
					if( !$jheaderWrap.hasClass('force-not-dark') ) { $jheaderWrap.removeClass('not-dark'); }
					SEMICOLON.header.stickyMenuClass();
				} else if( $jbody.hasClass('device-xs') || $jbody.hasClass('device-xxs') || $jbody.hasClass('device-sm') ) {
					if( $jbody.hasClass('sticky-responsive-menu') ) {
						$j('#header:not(.no-sticky)').addClass('responsive-sticky-header');
						SEMICOLON.header.stickyMenuClass();
					}
				} else {
					SEMICOLON.header.removeStickyness();
				}
			} else {
				SEMICOLON.header.removeStickyness();
			}
		},

		removeStickyness: function(){
			if( $jheader.hasClass('sticky-header') ){
				$j('body:not(.side-header) #header:not(.no-sticky)').removeClass('sticky-header');
				$j('#page-menu:not(.dots-menu,.no-sticky)').removeClass('sticky-page-menu');
				$jheader.removeClass().addClass(oldHeaderClasses);
				$jheaderWrap.removeClass().addClass(oldHeaderWrapClasses);
				if( !$jheaderWrap.hasClass('force-not-dark') ) { $jheaderWrap.removeClass('not-dark'); }
				SEMICOLON.slider.swiperSliderMenu();
				SEMICOLON.slider.revolutionSliderMenu();
			}
			if( $jheader.hasClass('responsive-sticky-header') ){
				$j('body.sticky-responsive-menu #header').removeClass('responsive-sticky-header');
			}
			if( $jbody.hasClass('device-xs') || $jbody.hasClass('device-xxs') || $jbody.hasClass('device-sm') ) {
				$jheader.removeClass().addClass(oldHeaderClasses);
				$jheaderWrap.removeClass().addClass(oldHeaderWrapClasses);
				if( !$jheaderWrap.hasClass('force-not-dark') ) { $jheaderWrap.removeClass('not-dark'); }
			}
		},

		sideHeader: function(){
			$j("#header-trigger").click(function(){
				$j('body.open-header').toggleClass("side-header-open");
				return false;
			});
		},

		sidePanel: function(){
			if( $jbody.hasClass('side-push-panel') ) {
				$j("#side-panel-trigger,#side-panel-trigger-close").click(function(){
					$j('body.side-push-panel').toggleClass("side-panel-open");
					return false;
				});
			}
		},

		onePageScroll: function(){
			if( $jonePageMenuEl.length > 0 ){
				var onePageSpeed = $jonePageMenuEl.attr('data-speed'),
					onePageOffset = $jonePageMenuEl.attr('data-offset'),
					onePageEasing = $jonePageMenuEl.attr('data-easing');

				if( !onePageSpeed ) { onePageSpeed = 1000; }
				if( !onePageEasing ) { onePageEasing = 'easeOutQuad'; }

				$jonePageMenuEl.find('a[data-href]').click(function(){
					var element = $j(this),
						divScrollToAnchor = element.attr('data-href'),
						divScrollSpeed = element.attr('data-speed'),
						divScrollOffset = element.attr('data-offset'),
						divScrollEasing = element.attr('data-easing');

					if( $j( divScrollToAnchor ).length > 0 ) {

						if( !onePageOffset ) {
							var onePageOffsetG = SEMICOLON.initialize.topScrollOffset();
						} else {
							var onePageOffsetG = onePageOffset;
						}

						if( !divScrollSpeed ) { divScrollSpeed = onePageSpeed; }
						if( !divScrollOffset ) { divScrollOffset = onePageOffsetG; }
						if( !divScrollEasing ) { divScrollEasing = onePageEasing; }

						if( $jonePageMenuEl.hasClass('no-offset') ) { divScrollOffset = 0; }

						onePageGlobalOffset = Number(divScrollOffset);

						$jonePageMenuEl.find('li').removeClass('current');
						$jonePageMenuEl.find('a[data-href="' + divScrollToAnchor + '"]').parent('li').addClass('current');

						$j('#primary-menu > ul, #primary-menu > .container > ul').toggleClass('show', function() {
							$j('html,body').stop(true).animate({
								'scrollTop': $j( divScrollToAnchor ).offset().top - Number(divScrollOffset)
							}, Number(divScrollSpeed), divScrollEasing);
						}, false);

						onePageGlobalOffset = Number(divScrollOffset);
					}

					return false;
				});
			}
		},

		onepageScroller: function(){
			$jonePageMenuEl.find('li').removeClass('current');
			$jonePageMenuEl.find('a[data-href="#' + SEMICOLON.header.onePageCurrentSection() + '"]').parent('li').addClass('current');
		},

		onePageCurrentSection: function(){
			var currentOnePageSection = 'home';

			$jpageSectionEl.each(function(index) {
				var h = $j(this).offset().top;
				var y = $jwindow.scrollTop();

				var offsetScroll = 100 + onePageGlobalOffset;

				if( y + offsetScroll >= h && y < h + $j(this).height() && $j(this).attr('id') != currentOnePageSection ) {
					currentOnePageSection = $j(this).attr('id');
				}
			});

			return currentOnePageSection;
		},

		darkLogo: function(){
			if( ( $jheader.hasClass('dark') || $jbody.hasClass('dark') ) && !$jheaderWrap.hasClass('not-dark') ) {
				if( defaultDarkLogo ){ defaultLogo.find('img').attr('src', defaultDarkLogo); }
				if( retinaDarkLogo ){ retinaLogo.find('img').attr('src', retinaDarkLogo); }
			} else {
				if( defaultLogoImg ){ defaultLogo.find('img').attr('src', defaultLogoImg); }
				if( retinaLogoImg ){ retinaLogo.find('img').attr('src', retinaLogoImg); }
			}
		},

		stickyMenuClass: function(){
			if( stickyMenuClasses ) { var newClassesArray = stickyMenuClasses.split(/ +/); } else { var newClassesArray = ''; }
			var noOfNewClasses = newClassesArray.length;

			if( noOfNewClasses > 0 ) {
				var i = 0;
				for( i=0; i<noOfNewClasses; i++ ) {
					if( newClassesArray[i] == 'not-dark' ) {
						$jheader.removeClass('dark');
						$jheaderWrap.addClass('not-dark');
					} else if( newClassesArray[i] == 'dark' ) {
						$jheaderWrap.removeClass('not-dark force-not-dark');
						if( !$jheader.hasClass( newClassesArray[i] ) ) {
							$jheader.addClass( newClassesArray[i] );
						}
					} else if( !$jheader.hasClass( newClassesArray[i] ) ) {
						$jheader.addClass( newClassesArray[i] );
					}
				}
			}

		},

		topsocial: function(){
			if( $jtopSocialEl.length > 0 ){
				if( $jbody.hasClass('device-md') || $jbody.hasClass('device-lg') ) {
					$jtopSocialEl.show();
					$jtopSocialEl.find('a').css({width: 40});

					$jtopSocialEl.find('.ts-text').each( function(){
						var $jclone = $j(this).clone().css({'visibility': 'hidden', 'display': 'inline-block', 'font-size': '13px', 'font-weight':'bold'}).appendTo($jbody),
							cloneWidth = $jclone.innerWidth() + 52;
						$j(this).parent('a').attr('data-hover-width',cloneWidth);
						$jclone.remove();
					});

					$jtopSocialEl.find('a').hover(function() {
						if( $j(this).find('.ts-text').length > 0 ) {
							$j(this).css({width: $j(this).attr('data-hover-width')});
						}
					}, function() {
						$j(this).css({width: 40});
					});
				} else {
					$jtopSocialEl.show();
					$jtopSocialEl.find('a').css({width: 40});

					$jtopSocialEl.find('a').each(function() {
						var topIconTitle = $j(this).find('.ts-text').text();
						$j(this).attr('title', topIconTitle);
					});

					$jtopSocialEl.find('a').hover(function() {
						$j(this).css({width: 40});
					}, function() {
						$j(this).css({width: 40});
					});

					if( $jbody.hasClass('device-xxs') ) {
						$jtopSocialEl.hide();
						$jtopSocialEl.slice(0, 8).show();
					}
				}
			}
		},

		topsearch: function(){

			$j(document).on('click', function(event) {
				if (!$j(event.target).closest('#top-search').length) { $jbody.toggleClass('top-search-open', false); }
				if (!$j(event.target).closest('#top-cart').length) { $jtopCart.toggleClass('top-cart-open', false); }
				if (!$j(event.target).closest('#page-menu').length) { $jpagemenu.toggleClass('pagemenu-active', false); }
				if (!$j(event.target).closest('#side-panel').length) { $jbody.toggleClass('side-panel-open', false); }
			});

			$j("#top-search-trigger").click(function(e){
				$jbody.toggleClass('top-search-open');
				$jtopCart.toggleClass('top-cart-open', false);
				$j( '#primary-menu > ul, #primary-menu > div > ul' ).toggleClass("show", false);
				$jpagemenu.toggleClass('pagemenu-active', false);
				if ($jbody.hasClass('top-search-open')){
					$jtopSearch.find('input').focus();
				}
				e.stopPropagation();
				e.preventDefault();
			});

		},

		topcart: function(){

			$j("#top-cart-trigger").click(function(e){
				$jpagemenu.toggleClass('pagemenu-active', false);
				$jtopCart.toggleClass('top-cart-open');
				e.stopPropagation();
				e.preventDefault();
			});

		}

	};

	SEMICOLON.slider = {

		init: function() {

			SEMICOLON.slider.sliderParallax();
			SEMICOLON.slider.sliderElementsFade();
			SEMICOLON.slider.captionPosition();

		},

		sliderParallaxOffset: function(){
			var sliderParallaxOffsetTop = 0;
			var headerHeight = $jheader.outerHeight();
			if( $jbody.hasClass('side-header') || $jheader.hasClass('transparent-header') ) { headerHeight = 0; }
			if( $jpageTitle.length > 0 ) {
				var pageTitleHeight = $jpageTitle.outerHeight();
				sliderParallaxOffsetTop = pageTitleHeight + headerHeight;
			} else {
				sliderParallaxOffsetTop = headerHeight;
			}

			if( $jslider.next('#header').length > 0 ) { sliderParallaxOffsetTop = 0; }

			return sliderParallaxOffsetTop;
		},

		sliderParallax: function(){
			if( $jsliderParallaxEl.length > 0 ) {
				if( ( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ) && !SEMICOLON.isMobile.any() ) {
					var parallaxOffsetTop = SEMICOLON.slider.sliderParallaxOffset(),
						parallaxElHeight = $jsliderParallaxEl.outerHeight();

					if( ( parallaxElHeight + parallaxOffsetTop + 50 ) > $jwindow.scrollTop() ){
						if ($jwindow.scrollTop() > parallaxOffsetTop) {
							var tranformAmount = (($jwindow.scrollTop()-parallaxOffsetTop) / 1.5 ).toFixed(2);
							var tranformAmount2 = (($jwindow.scrollTop()-parallaxOffsetTop) / 7 ).toFixed(2);
							$jsliderParallaxEl.stop(true,true).transition({ y: tranformAmount },0);
							$j('.slider-parallax .slider-caption,.ei-title').stop(true,true).transition({ y: -tranformAmount2 },0);
						} else {
							$j('.slider-parallax,.slider-parallax .slider-caption,.ei-title').transition({ y: 0 },0);
						}
					}
				} else {
					$j('.slider-parallax,.slider-parallax .slider-caption,.ei-title').transition({ y: 0 },0);
				}
			}
		},

		sliderElementsFade: function(){
			if( $jsliderParallaxEl.length > 0 ) {
				if( ( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ) && !SEMICOLON.isMobile.any() ) {
					var parallaxOffsetTop = SEMICOLON.slider.sliderParallaxOffset();
					if( $jslider.length > 0 ) {
						if( $jheader.hasClass('transparent-header') || $j('body').hasClass('side-header') ) { var tHeaderOffset = 100; } else { var tHeaderOffset = 0; }
						$jsliderParallaxEl.find('#slider-arrow-left,#slider-arrow-right,.vertical-middle:not(.no-fade),.slider-caption,.ei-title,.camera_prev,.camera_next').css({'opacity':( ( 100 + ( $jslider.offset().top + parallaxOffsetTop + tHeaderOffset ) - $jwindow.scrollTop() ) ) /90});
					}
				} else {
					$jsliderParallaxEl.find('#slider-arrow-left,#slider-arrow-right,.vertical-middle:not(.no-fade),.slider-caption,.ei-title,.camera_prev,.camera_next').css({'opacity': 1});
				}
			}
		},

		captionPosition: function(){
			$jslider.find('.slider-caption').each(function(){
				var scapHeight = $j(this).outerHeight();
				var scapSliderHeight = $jslider.outerHeight();
				if( $j(this).parents('#slider').prev('#header').hasClass('transparent-header') && ( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ) ) {
					if( $j(this).parents('#slider').prev('#header').hasClass('floating-header') ) {
						$j(this).css({ top: ( scapSliderHeight + 160 - scapHeight ) / 2 + 'px' });
					} else {
						$j(this).css({ top: ( scapSliderHeight + 100 - scapHeight ) / 2 + 'px' });
					}
				} else {
					$j(this).css({ top: ( scapSliderHeight - scapHeight ) / 2 + 'px' });
				}
			});
		},

		swiperSliderMenu: function(){
			if( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ) {
				var activeSlide = $jslider.find('.swiper-slide.swiper-slide-visible');
				SEMICOLON.slider.headerSchemeChanger(activeSlide);
			}
		},

		revolutionSliderMenu: function(){
			if( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ) {
				var activeSlide = $jslider.find('.current-sr-slide-visible');
				SEMICOLON.slider.headerSchemeChanger(activeSlide);
			}
		},

		headerSchemeChanger: function( activeSlide ){
			if( activeSlide.length > 0 ) {
				if( activeSlide.hasClass('dark') ){
					$j('#header.transparent-header:not(.sticky-header,.semi-transparent,.floating-header)').addClass('dark');
					$j('#header.transparent-header.sticky-header,#header.transparent-header.semi-transparent.sticky-header,#header.transparent-header.floating-header.sticky-header').removeClass('dark');
					$jheaderWrap.removeClass('not-dark');
				} else {
					if( $jbody.hasClass('dark') ) {
						activeSlide.addClass('not-dark');
						$j('#header.transparent-header:not(.semi-transparent,.floating-header)').removeClass('dark');
						$j('#header.transparent-header:not(.sticky-header,.semi-transparent,.floating-header)').find('#header-wrap').addClass('not-dark');
					} else {
						$j('#header.transparent-header:not(.semi-transparent,.floating-header)').removeClass('dark');
						$jheaderWrap.removeClass('not-dark');
					}
				}
				SEMICOLON.header.darkLogo();
			}
		},

		owlCaptionInit: function(){
			if( $jowlCarouselEl.length > 0 ){
				$jowlCarouselEl.each( function(){
					var element = $j(this);
					if( element.find('.owl-dot').length > 0 ) {
						element.find('.owl-controls').addClass('with-carousel-dots');
					}
				});
			}
		}

	};

	SEMICOLON.portfolio = {

		init: function(){

			SEMICOLON.portfolio.ajaxload();

		},

		portfolioDescMargin: function(){
			var $jportfolioOverlayEl = $j('.portfolio-overlay');
			if( $jportfolioOverlayEl.length > 0 ){
				$jportfolioOverlayEl.each(function() {
					var element = $j(this);
					if( element.find('.portfolio-desc').length > 0 ) {
						var portfolioOverlayHeight = element.outerHeight();
						var portfolioOverlayDescHeight = element.find('.portfolio-desc').outerHeight();
						if( element.find('a.left-icon').length > 0 || element.find('a.right-icon').length > 0 || element.find('a.center-icon').length > 0 ) {
							var portfolioOverlayIconHeight = 40 + 20;
						} else {
							var portfolioOverlayIconHeight = 0;
						}
						var portfolioOverlayMiddleAlign = ( portfolioOverlayHeight - portfolioOverlayDescHeight - portfolioOverlayIconHeight ) / 2
						element.find('.portfolio-desc').css({ 'margin-top': portfolioOverlayMiddleAlign });
					}
				});
			}
		},

		arrange: function(){
			SEMICOLON.initialize.setFullColumnWidth( $jportfolio );
			$j('#portfolio.portfolio-full').isotope('layout');
		},

		ajaxload: function(){
			$j('.portfolio-ajax .portfolio-item a.center-icon').click( function(e) {
				var portPostId = $j(this).parents('.portfolio-item').attr('id');
				if( !$j(this).parents('.portfolio-item').hasClass('portfolio-active') ) {
					SEMICOLON.portfolio.loadItem(portPostId, prevPostPortId);
				}
				e.preventDefault();
			});
		},

		newNextPrev: function( portPostId ){
			var portNext = SEMICOLON.portfolio.getNextItem(portPostId);
			var portPrev = SEMICOLON.portfolio.getPrevItem(portPostId);
			$j('#next-portfolio').attr('data-id', portNext);
			$j('#prev-portfolio').attr('data-id', portPrev);
		},

		loadItem: function( portPostId, prevPostPortId, getIt ){
			if(!getIt) { getIt = false; }
			var portNext = SEMICOLON.portfolio.getNextItem(portPostId);
			var portPrev = SEMICOLON.portfolio.getPrevItem(portPostId);
			if(getIt == false) {
				SEMICOLON.portfolio.closeItem();
				$jportfolioAjaxLoader.fadeIn();
				var portfolioDataLoader = $j('#' + portPostId).attr('data-loader');
				$jportfolioDetailsContainer.load(portfolioDataLoader, { portid: portPostId, portnext: portNext, portprev: portPrev },
				function(){
					SEMICOLON.portfolio.initializeAjax(portPostId);
					SEMICOLON.portfolio.openItem();
					$jportfolioItems.removeClass('portfolio-active');
					$j('#' + portPostId).addClass('portfolio-active');
				});
			}
		},

		closeItem: function(){
			if( $jportfolioDetails && $jportfolioDetails.height() > 32 ) {
				$jportfolioAjaxLoader.fadeIn();
				$jportfolioDetails.find('#portfolio-ajax-single').fadeOut('600', function(){
					$j(this).remove();
				});
				$jportfolioDetails.removeClass('portfolio-ajax-opened');
			}
		},

		openItem: function(){
			var noOfImages = $jportfolioDetails.find('img').length;
			var noLoaded = 0;

			if( noOfImages > 0 ) {
				$jportfolioDetails.find('img').on('load', function(){
					noLoaded++;
					var topOffsetScroll = SEMICOLON.initialize.topScrollOffset();
					if(noOfImages === noLoaded) {
						$jportfolioDetailsContainer.css({ 'display': 'block' });
						$jportfolioDetails.addClass('portfolio-ajax-opened');
						$jportfolioAjaxLoader.fadeOut();
						var t=setTimeout(function(){
							SEMICOLON.widget.loadFlexSlider();
							SEMICOLON.initialize.lightbox();
							SEMICOLON.initialize.resizeVideos();
							SEMICOLON.widget.masonryThumbs();
							$j('html,body').stop(true).animate({
								'scrollTop': $jportfolioDetails.offset().top - topOffsetScroll
							}, 900, 'easeOutQuad');
						},500);
					}
				});
			} else {
				var topOffsetScroll = SEMICOLON.initialize.topScrollOffset();
				$jportfolioDetailsContainer.css({ 'display': 'block' });
				$jportfolioDetails.addClass('portfolio-ajax-opened');
				$jportfolioAjaxLoader.fadeOut();
				var t=setTimeout(function(){
					SEMICOLON.widget.loadFlexSlider();
					SEMICOLON.initialize.lightbox();
					SEMICOLON.initialize.resizeVideos();
					SEMICOLON.widget.masonryThumbs();
					$j('html,body').stop(true).animate({
						'scrollTop': $jportfolioDetails.offset().top - topOffsetScroll
					}, 900, 'easeOutQuad');
				},500);
			}
		},

		getNextItem: function( portPostId ){
			var portNext = '';
			var hasNext = $j('#' + portPostId).next();
			if(hasNext.length != 0) {
				portNext = hasNext.attr('id');
			}
			return portNext;
		},

		getPrevItem: function( portPostId ){
			var portPrev = '';
			var hasPrev = $j('#' + portPostId).prev();
			if(hasPrev.length != 0) {
				portPrev = hasPrev.attr('id');
			}
			return portPrev;
		},

		initializeAjax: function( portPostId ){
			prevPostPortId = $j('#' + portPostId);

			$j('#next-portfolio, #prev-portfolio').click( function() {
				var portPostId = $j(this).attr('data-id');
				$jportfolioItems.removeClass('portfolio-active');
				$j('#' + portPostId).addClass('portfolio-active');
				SEMICOLON.portfolio.loadItem(portPostId,prevPostPortId);
				return false;
			});

			$j('#close-portfolio').click( function() {
				$jportfolioDetailsContainer.fadeOut('600', function(){
					$jportfolioDetails.find('#portfolio-ajax-single').remove();
				});
				$jportfolioDetails.removeClass('portfolio-ajax-opened');
				$jportfolioItems.removeClass('portfolio-active');
				return false;
			});
		}

	};

	SEMICOLON.widget = {

		init: function(){

			SEMICOLON.widget.animations();
			SEMICOLON.widget.youtubeBgVideo();
			SEMICOLON.widget.tabs();
			SEMICOLON.widget.tabsJustify();
			SEMICOLON.widget.toggles();
			SEMICOLON.widget.accordions();
			SEMICOLON.widget.counter();
			SEMICOLON.widget.roundedSkill();
			SEMICOLON.widget.progress();
			SEMICOLON.widget.flickrFeed();
			SEMICOLON.widget.instagramPhotos( window.weltpixel_instagram.access_token, window.weltpixel_instagram.client_id );
			SEMICOLON.widget.dribbbleShots();
			SEMICOLON.widget.navTree();
			SEMICOLON.widget.textRotater();
			SEMICOLON.widget.linkScroll();
			SEMICOLON.widget.extras();

		},

		parallax: function(){
			if( !SEMICOLON.isMobile.any() ){
				$j.stellar({
					horizontalScrolling: false,
					verticalOffset: 150,
					responsive: true
				});
			} else {
				$jparallaxEl.addClass('mobile-parallax');
				$jparallaxPageTitleEl.addClass('mobile-parallax');
			}
		},

		animations: function(){
			var $jdataAnimateEl = $j('[data-animate]');
			if( $jdataAnimateEl.length > 0 ){
				if( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') || $jbody.hasClass('device-sm') ){
					$jdataAnimateEl.each(function(){
						var element = $j(this),
							animationDelay = element.attr('data-delay'),
							animationDelayTime = 0;

						if( animationDelay ) { animationDelayTime = Number( animationDelay ) + 500; } else { animationDelayTime = 500; }

						if( !element.hasClass('animated') ) {
							element.addClass('not-animated');
							var elementAnimation = element.attr('data-animate');
							element.appear(function () {
								setTimeout(function() {
									element.removeClass('not-animated').addClass( elementAnimation + ' animated');
								}, animationDelayTime);
							},{accX: 0, accY: -120},'easeInCubic');
						}
					});
				}
			}
		},

		loadFlexSlider: function(){
			var $jflexSliderEl = $j('.fslider').find('.flexslider');
			if( $jflexSliderEl.length > 0 ){
				$jflexSliderEl.each(function() {
					var $jflexsSlider = $j(this),
						flexsAnimation = $jflexsSlider.parent('.fslider').attr('data-animation'),
						flexsEasing = $jflexsSlider.parent('.fslider').attr('data-easing'),
						flexsDirection = $jflexsSlider.parent('.fslider').attr('data-direction'),
						flexsSlideshow = $jflexsSlider.parent('.fslider').attr('data-slideshow'),
						flexsPause = $jflexsSlider.parent('.fslider').attr('data-pause'),
						flexsSpeed = $jflexsSlider.parent('.fslider').attr('data-speed'),
						flexsVideo = $jflexsSlider.parent('.fslider').attr('data-video'),
						flexsPagi = $jflexsSlider.parent('.fslider').attr('data-pagi'),
						flexsArrows = $jflexsSlider.parent('.fslider').attr('data-arrows'),
						flexsThumbs = $jflexsSlider.parent('.fslider').attr('data-thumbs'),
						flexsHover = $jflexsSlider.parent('.fslider').attr('data-hover'),
						flexsSheight = true,
						flexsUseCSS = false;

					if( !flexsAnimation ) { flexsAnimation = 'slide'; }
					if( !flexsEasing || flexsEasing == 'swing' ) {
						flexsEasing = 'swing';
						flexsUseCSS = true;
					}
					if( !flexsDirection ) { flexsDirection = 'horizontal'; }
					if( !flexsSlideshow ) { flexsSlideshow = true; } else { flexsSlideshow = false; }
					if( !flexsPause ) { flexsPause = 5000; }
					if( !flexsSpeed ) { flexsSpeed = 600; }
					if( !flexsVideo ) { flexsVideo = false; }
					if( flexsDirection == 'vertical' ) { flexsSheight = false; }
					if( flexsPagi == 'false' ) { flexsPagi = false; } else { flexsPagi = true; }
					if( flexsThumbs == 'true' ) { flexsPagi = 'thumbnails'; } else { flexsPagi = flexsPagi; }
					if( flexsArrows == 'false' ) { flexsArrows = false; } else { flexsArrows = true; }
					if( flexsHover == 'false' ) { flexsHover = false; } else { flexsHover = true; }

					$jflexsSlider.flexslider({
						selector: ".slider-wrap > .slide",
						animation: flexsAnimation,
						easing: flexsEasing,
						direction: flexsDirection,
						slideshow: flexsSlideshow,
						slideshowSpeed: Number(flexsPause),
						animationSpeed: Number(flexsSpeed),
						pauseOnHover: flexsHover,
						video: flexsVideo,
						controlNav: flexsPagi,
						directionNav: flexsArrows,
						smoothHeight: flexsSheight,
						useCSS: flexsUseCSS,
						start: function(slider){
							SEMICOLON.widget.animations();
							SEMICOLON.initialize.verticalMiddle();
							slider.parent().removeClass('preloader2');
							var t = setTimeout( function(){ $j('#portfolio.portfolio-masonry,#portfolio.portfolio-full,#posts.post-masonry').isotope('layout'); }, 1200 );
							SEMICOLON.initialize.lightbox();
							$j('.flex-prev').html('<i class="icon-angle-left"></i>');
							$j('.flex-next').html('<i class="icon-angle-right"></i>');
							SEMICOLON.portfolio.portfolioDescMargin();
						},
						after: function(){
							if( $jportfolio.has('portfolio-full') ) {
								$j('#portfolio.portfolio-full').isotope('layout');
								SEMICOLON.portfolio.portfolioDescMargin();
							}
						}
					});
				});
			}
		},

		html5Video: function(){
			var videoEl = $j('.video-wrap:has(video)');
			if( videoEl.length > 0 ) {
				videoEl.each(function(){
					var element = $j(this),
						elementVideo = element.find('video'),
						outerContainerWidth = element.outerWidth(),
						outerContainerHeight = element.outerHeight(),
						innerVideoWidth = elementVideo.outerWidth(),
						innerVideoHeight = elementVideo.outerHeight();

					if( innerVideoHeight < outerContainerHeight ) {
						var videoAspectRatio = innerVideoWidth/innerVideoHeight,
							newVideoWidth = outerContainerHeight * videoAspectRatio,
							innerVideoPosition = (newVideoWidth - outerContainerWidth) / 2;
						elementVideo.css({ 'width': newVideoWidth+'px', 'height': outerContainerHeight+'px', 'left': -innerVideoPosition+'px' });
					} else {
						var innerVideoPosition = (innerVideoHeight - outerContainerHeight) / 2;
						elementVideo.css({ 'width': innerVideoWidth+'px', 'height': innerVideoHeight+'px', 'top': -innerVideoPosition+'px' });
					}

					if( SEMICOLON.isMobile.any() ) {
						var placeholderImg = elementVideo.attr('poster');

						if( placeholderImg != '' ) {
							element.append('<div class="video-placeholder" style="background-image: url('+ placeholderImg +');"></div>')
						}
					}
				});
			}
		},

		youtubeBgVideo: function(){
			if( $jyoutubeBgPlayerEl.length > 0 ){
				$jyoutubeBgPlayerEl.each( function(){
					var element = $j(this),
						ytbgVideo = element.attr('data-video'),
						ytbgMute = element.attr('data-mute'),
						ytbgRatio = element.attr('data-ratio'),
						ytbgQuality = element.attr('data-quality'),
						ytbgOpacity = element.attr('data-opacity'),
						ytbgContainer = element.attr('data-container'),
						ytbgOptimize = element.attr('data-optimize'),
						ytbgLoop = element.attr('data-loop'),
						ytbgVolume = element.attr('data-volume'),
						ytbgStart = element.attr('data-start'),
						ytbgStop = element.attr('data-stop'),
						ytbgAutoPlay = element.attr('data-autoplay'),
						ytbgFullScreen = element.attr('data-fullscreen');

					if( ytbgMute == 'false' ) { ytbgMute = false; } else { ytbgMute = true; }
					if( !ytbgRatio ) { ytbgRatio = '16/9'; }
					if( !ytbgQuality ) { ytbgQuality = 'hd720'; }
					if( !ytbgOpacity ) { ytbgOpacity = 1; }
					if( !ytbgContainer ) { ytbgContainer = 'self'; }
					if( ytbgOptimize == 'false' ) { ytbgOptimize = false; } else { ytbgOptimize = true; }
					if( ytbgLoop == 'false' ) { ytbgLoop = false; } else { ytbgLoop = true; }
					if( !ytbgVolume ) { ytbgVolume = 1; }
					if( !ytbgStart ) { ytbgStart = 0; }
					if( !ytbgStop ) { ytbgStop = 0; }
					if( ytbgAutoPlay == 'false' ) { ytbgAutoPlay = false; } else { ytbgAutoPlay = true; }
					if( ytbgFullScreen == 'true' ) { ytbgFullScreen = true; } else { ytbgFullScreen = false; }

					element.mb_YTPlayer({
						videoURL: ytbgVideo,
						mute: ytbgMute,
						ratio: ytbgRatio,
						quality: ytbgQuality,
						opacity: ytbgOpacity,
						containment: ytbgContainer,
						optimizeDisplay: ytbgOptimize,
						loop: ytbgLoop,
						vol: ytbgVolume,
						startAt: ytbgStart,
						stopAt: ytbgStop,
						autoplay: ytbgAutoPlay,
						realfullscreen: ytbgFullScreen,
						showYTLogo: false,
						showControls: false
					});
				});
			}
		},

		tabs: function(){
			var $jtabs = $j('.tabs:not(.customjs)');
			if( $jtabs.length > 0 ) {
				$jtabs.each( function(){
					var element = $j(this),
						elementSpeed = element.attr('data-speed'),
						tabActive = element.attr('data-active');

					if( !elementSpeed ) { elementSpeed = 400; }
					if( !tabActive ) { tabActive = 0; } else { tabActive = tabActive - 1; }

					element.tabs({
						active: Number(tabActive),
						show: {
							effect: "fade",
							duration: Number(elementSpeed)
						}
					});
				});
			}
		},

		tabsJustify: function(){
			if( !$j('body').hasClass('device-xxs') && !$j('body').hasClass('device-xs') ){
				var $jtabsJustify = $j('.tabs.tabs-justify');
				if( $jtabsJustify.length > 0 ) {
					$jtabsJustify.each( function(){
						var element = $j(this),
							elementTabs = element.find('.tab-nav > li'),
							elementTabsNo = elementTabs.length,
							elementContainer = 0,
							elementWidth = 0;

						if( element.hasClass('tabs-bordered') || element.hasClass('tabs-bb') ) {
							elementContainer = element.find('.tab-nav').outerWidth();
						} else {
							if( element.find('tab-nav').hasClass('tab-nav2') ) {
								elementContainer = element.find('.tab-nav').outerWidth() - (elementTabsNo * 10);
							} else {
								elementContainer = element.find('.tab-nav').outerWidth() - 30;
							}
						}

						elementWidth = Math.floor(elementContainer/elementTabsNo);
						elementTabs.css({ 'width': elementWidth + 'px' });

					});
				}
			} else { $j('.tabs.tabs-justify').find('.tab-nav > li').css({ 'width': 'auto' }); }
		},

		toggles: function(){
			var $jtoggle = $j('.toggle');
			if( $jtoggle.length > 0 ) {
				$jtoggle.each( function(){
					var element = $j(this),
						elementState = element.attr('data-state');

					if( elementState != 'open' ){
						element.find('.togglec').hide();
					} else {
						element.find('.togglet').addClass("toggleta");
					}

					element.find('.togglet').click(function(){
						$j(this).toggleClass('toggleta').next('.togglec').slideToggle(300);
						return true;
					});
				});
			}
		},

		accordions: function(){
			var $jaccordionEl = $j('.accordion');
			if( $jaccordionEl.length > 0 ){
				$jaccordionEl.each( function(){
					var element = $j(this),
						elementState = element.attr('data-state'),
						accordionActive = element.attr('data-active');

					if( !accordionActive ) { accordionActive = 0; } else { accordionActive = accordionActive - 1; }

					element.find('.acc_content').hide();

					if( elementState != 'closed' ) {
						element.find('.acctitle:eq('+ Number(accordionActive) +')').addClass('acctitlec').next().show();
					}

					element.find('.acctitle').click(function(){
						if( $j(this).next().is(':hidden') ) {
							element.find('.acctitle').removeClass('acctitlec').next().slideUp("normal");
							$j(this).toggleClass('acctitlec').next().slideDown("normal");
						}
						return false;
					});
				});
			}
		},

		counter: function(){
			var $jcounterEl = $j('.counter:not(.counter-instant)');
			if( $jcounterEl.length > 0 ){
				$jcounterEl.each(function(){
					var element = $j(this);
					var counterElementComma = $j(this).find('span').attr('data-comma');
					if( !counterElementComma ) { counterElementComma = false; } else { counterElementComma = true; }
					if( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ){
						element.appear( function(){
							SEMICOLON.widget.runCounter( element, counterElementComma );
						},{accX: 0, accY: -120},'easeInCubic');
					} else {
						SEMICOLON.widget.runCounter( element, counterElementComma );
					}
				});
			}
		},

		runCounter: function( counterElement,counterElementComma ){
			if( counterElementComma == true ) {
				counterElement.find('span').countTo({
					formatter: function (value, options) {
						value = value.toFixed(options.decimals);
						value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
						return value;
					}
				});
			} else {
				counterElement.find('span').countTo();
			}
		},

		roundedSkill: function(){
			var $jroundedSkillEl = $j('.rounded-skill');
			if( $jroundedSkillEl.length > 0 ){
				$jroundedSkillEl.each(function(){
					var element = $j(this);

					var roundSkillSize = element.attr('data-size');
					var roundSkillAnimate = element.attr('data-animate');
					var roundSkillWidth = element.attr('data-width');
					var roundSkillColor = element.attr('data-color');
					var roundSkillTrackColor = element.attr('data-trackcolor');

					if( !roundSkillSize ) { roundSkillSize = 140; }
					if( !roundSkillAnimate ) { roundSkillAnimate = 2000; }
					if( !roundSkillWidth ) { roundSkillWidth = 8; }
					if( !roundSkillColor ) { roundSkillColor = '#0093BF'; }
					if( !roundSkillTrackColor ) { roundSkillTrackColor = 'rgba(0,0,0,0.04)'; }

					var properties = {roundSkillSize:roundSkillSize, roundSkillAnimate:roundSkillAnimate, roundSkillWidth:roundSkillWidth, roundSkillColor:roundSkillColor, roundSkillTrackColor:roundSkillTrackColor};

					if( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ){
						element.css({'width':roundSkillSize+'px','height':roundSkillSize+'px'}).animate({opacity: '0'}, 10);
						element.appear( function(){
							if (!element.hasClass('skills-animated')) {
								element.css({opacity: '1'});
								SEMICOLON.widget.runRoundedSkills( element, properties );
								element.addClass('skills-animated');
							}
						},{accX: 0, accY: -120},'easeInCubic');
					} else {
						SEMICOLON.widget.runRoundedSkills( element, properties );
					}
				});
			}
		},

		runRoundedSkills: function( element, properties ){
			element.easyPieChart({
				size: Number(properties.roundSkillSize),
				animate: Number(properties.roundSkillAnimate),
				scaleColor: false,
				trackColor: properties.roundSkillTrackColor,
				lineWidth: Number(properties.roundSkillWidth),
				lineCap: 'square',
				barColor: properties.roundSkillColor
			});
		},

		progress: function(){
			var $jprogressEl = $j('.progress');
			if( $jprogressEl.length > 0 ){
				$jprogressEl.each(function(){
					var element = $j(this),
						skillsBar = element.parent('li'),
						skillValue = skillsBar.attr('data-percent');

					if( $jbody.hasClass('device-lg') || $jbody.hasClass('device-md') ){
						element.appear( function(){
							if (!skillsBar.hasClass('skills-animated')) {
								element.find('.counter-instant span').countTo();
								skillsBar.find('.progress').css({width: skillValue + "%"}).addClass('skills-animated');
							}
						},{accX: 0, accY: -120},'easeInCubic');
					} else {
						element.find('.counter-instant span').countTo();
						skillsBar.find('.progress').css({width: skillValue + "%"});
					}
				});
			}
		},

		flickrFeed: function(){
			var $jflickrFeedEl = $j('.flickr-feed');
			if( $jflickrFeedEl.length > 0 ){
				$jflickrFeedEl.each(function() {
					var element = $j(this),
						flickrFeedID = element.attr('data-id'),
						flickrFeedCount = element.attr('data-count'),
						flickrFeedType = element.attr('data-type'),
						flickrFeedTypeGet = 'photos_public.gne';

					if( flickrFeedType == 'group' ) { flickrFeedTypeGet = 'groups_pool.gne'; }
					if( !flickrFeedCount ) { flickrFeedCount = 9; }

					element.jflickrfeed({
						feedapi: flickrFeedTypeGet,
						limit: Number(flickrFeedCount),
						qstrings: {
							id: flickrFeedID
						},
						itemTemplate: '<a href="{{image_b}}" title="{{title}}" data-lightbox="gallery-item">' +
											'<img src="{{image_s}}" alt="{{title}}" />' +
									  '</a>'
					}, function(data) {
						SEMICOLON.initialize.lightbox();
					});
				});
			}
		},

		instagramPhotos: function( c_accessToken, c_clientID ){
			var $jinstagramPhotosEl = $j('.instagram-photos');
			if( $jinstagramPhotosEl.length > 0 ){
				$j.fn.spectragram.accessData = {
					accessToken: c_accessToken,
					clientID: c_clientID
				};

				$jinstagramPhotosEl.each(function() {
					var element = $j(this),
						instaGramUsername = element.attr('data-user'),
						instaGramTag = element.attr('data-tag'),
						instaGramCount = element.attr('data-count'),
						instaGramType = element.attr('data-type');

					if( !instaGramCount ) { instaGramCount = 9; }

					if( instaGramType == 'tag' ) {
						element.spectragram('getRecentTagged',{
							query: instaGramTag,
							max: Number( instaGramCount ),
							size: 'medium',
							wrapEachWith: ' '
						});
					} else if( instaGramType == 'user' ) {
						element.spectragram('getUserFeed',{
							query: instaGramUsername,
							max: Number( instaGramCount ),
							size: 'medium',
							wrapEachWith: ' '
						});
					} else {
						element.spectragram('getPopular',{
							max: Number( instaGramCount ),
							size: 'medium',
							wrapEachWith: ' '
						});
					}
				});
			}
		},

		dribbbleShots: function(){
			var $jdribbbleShotsEl = $j('.dribbble-shots');
			if( $jdribbbleShotsEl.length > 0 ){
				$jdribbbleShotsEl.each(function() {
					var element = $j(this),
						dribbbleUsername = element.attr('data-user'),
						dribbbleCount = element.attr('data-count'),
						dribbbleList = element.attr('data-list'),
						dribbbleType = element.attr('data-type');

					if( !dribbbleCount ) { dribbbleCount = 9; }

					if( dribbbleType == 'follows' ) {
						$j.jribbble.getShotsThatPlayerFollows( dribbbleUsername , function (followedShots) {
							var html = [];
							$j.each(followedShots.shots, function (i, shot) {
								html.push('<a href="' + shot.url + '" target="_blank">');
								html.push('<img src="' + shot.image_teaser_url + '" ');
								html.push('alt="' + shot.title + '"></a>');
							});
							element.html(html.join(''));
						}, {page: 1, per_page: Number(dribbbleCount)});
					} else if( dribbbleType == 'user' ) {
						$j.jribbble.getShotsByPlayerId( dribbbleUsername , function (playerShots) {
							var html = [];
							$j.each(playerShots.shots, function (i, shot) {
								html.push('<a href="' + shot.url + '" target="_blank">');
								html.push('<img src="' + shot.image_teaser_url + '" ');
								html.push('alt="' + shot.title + '"></a>');
							});
							element.html(html.join(''));
						}, {page: 1, per_page: Number(dribbbleCount)});
					} else if( dribbbleType == 'list' ) {
						$j.jribbble.getShotsByList( dribbbleList , function (listDetails) {
							var html = [];
							$j.each(listDetails.shots, function (i, shot) {
								html.push('<a href="' + shot.url + '" target="_blank">');
								html.push('<img src="' + shot.image_teaser_url + '" ');
								html.push('alt="' + shot.title + '"></a>');
							});
							element.html(html.join(''));
						}, {page: 1, per_page: Number(dribbbleCount)});
					}
				});
			}
		},

		navTree: function(){
			var $jnavTreeEl = $j('.nav-tree');
			if( $jnavTreeEl.length > 0 ){
				$jnavTreeEl.each( function(){
					var element = $j(this),
						elementSpeed = element.attr('data-speed'),
						elementEasing = element.attr('data-easing');

					if( !elementSpeed ) { elementSpeed = 250; }
					if( !elementEasing ) { elementEasing = 'swing'; }

					element.find( 'ul li:has(ul)' ).addClass('sub-menu');
					element.find( 'ul li:has(ul) > a' ).append( ' <i class="icon-angle-down"></i>' );

					element.find( 'ul li:has(ul) > a' ).click( function(e){
						var childElement = $j(this);
						element.find( 'ul li' ).not(childElement.parents()).removeClass('active');
						childElement.parent().children('ul').slideToggle( Number(elementSpeed), elementEasing, function(){
							$j(this).find('ul').hide();
							$j(this).find('li.active').removeClass('active');
						});
						element.find( 'ul li > ul' ).not(childElement.parent().children('ul')).not(childElement.parents('ul')).slideUp( Number(elementSpeed), elementEasing );
						childElement.parent('li:has(ul)').toggleClass('active');
						e.preventDefault();
					});
				});
			}
		},

		masonryThumbs: function(){
			var $jmasonryThumbsEl = $j('.masonry-thumbs');
			if( $jmasonryThumbsEl.length > 0 ){
				$jmasonryThumbsEl.each( function(){
					var masonryItemContainer = $j(this);
					SEMICOLON.widget.masonryThumbsArrange( masonryItemContainer );
				});
			}
		},

		masonryThumbsArrange: function( element ){
			SEMICOLON.initialize.setFullColumnWidth( element );
			element.isotope('layout');
		},
                
                magentonotifications: function() {                                                           
                    $j('.messages ul li').each(function(idx, li) { 
                        toastr.clear();
                        var notifyType = $j(li).parent().parent().attr('class');
                        
                        toastr.options.positionClass = 'toast-bottom-full-width';
                        toastr.options.extendedTimeOut = 0;
                        toastr.options.timeOut = 0;         
                        toastr.options.tapToDismiss = false;
                        toastr.options.closeButton = true;
                        toastr.options.closeHtml = '<button><i class="icon-remove"></i></button>';
                        var notifyMsg = $j(li).find('span').html();
                        
                        if (notifyType == 'error-msg') {
                            toastr.error('<i class=icon-remove-sign></i>' + notifyMsg);
                        } else if (notifyType == 'success-msg') {                                                       
                            toastr.success('<i class=icon-ok-sign></i> ' + notifyMsg);
                        } else {
                            toastr.info('<i class=icon-warning-sign></i> ' + notifyMsg);
                        }
                    });
                    
                    return true;
                },

		notifications: function( element ){
			toastr.clear();
			var notifyElement = $j(element),
				notifyPosition = notifyElement.attr('data-notify-position'),
				notifyType = notifyElement.attr('data-notify-type'),
				notifyMsg = notifyElement.attr('data-notify-msg'),
				notifyCloseButton = notifyElement.attr('data-notify-close');

			if( !notifyPosition ) { notifyPosition = 'toast-top-right'; } else { notifyPosition = 'toast-' + notifyElement.attr('data-notify-position'); }
			if( !notifyMsg ) { notifyMsg = 'Please set a message!'; }
			if( notifyCloseButton == 'true' ) { notifyCloseButton = true; } else { notifyCloseButton = false; }

			toastr.options.positionClass = notifyPosition;
			toastr.options.closeButton = notifyCloseButton;
			toastr.options.closeHtml = '<button><i class="icon-remove"></i></button>';

			if( notifyType == 'warning' ) {
				toastr.warning(notifyMsg);
			} else if( notifyType == 'error' ) {
				toastr.error(notifyMsg);
			} else if( notifyType == 'success' ) {
				toastr.success(notifyMsg);
			} else {
				toastr.info(notifyMsg);
			}

			return false;
		},

		textRotater: function(){
			if( $jtextRotaterEl.length > 0 ){
				$jtextRotaterEl.each(function(){
					var element = $j(this),
						trRotate = $j(this).attr('data-rotate'),
						trSpeed = $j(this).attr('data-speed'),
						trSeparator = $j(this).attr('data-separator');

					if( !trRotate ) { trRotate = "fade"; }
					if( !trSpeed ) { trSpeed = 1200; }
					if( !trSeparator ) { trSeparator = ","; }

					var tRotater = $j(this).find('.t-rotate');

					tRotater.Morphext({
						animation: trRotate,
						separator: trSeparator,
						speed: Number(trSpeed)
					});
				});
			}
		},

		linkScroll: function(){
			$j("a[data-scrollto]").click(function(){
				var element = $j(this),
					divScrollToAnchor = element.attr('data-scrollto'),
					divScrollSpeed = element.attr('data-speed'),
					divScrollOffset = element.attr('data-offset'),
					divScrollEasing = element.attr('data-easing');

					if( !divScrollSpeed ) { divScrollSpeed = 750; }
					if( !divScrollOffset ) { divScrollOffset = SEMICOLON.initialize.topScrollOffset(); }
					if( !divScrollEasing ) { divScrollEasing = 'easeOutQuad'; }

				$j('html,body').stop(true).animate({
					'scrollTop': $j( divScrollToAnchor ).offset().top - Number(divScrollOffset)
				}, Number(divScrollSpeed), divScrollEasing);

				return false;
			});
		},

		extras: function(){
			$j('[data-toggle="tooltip"]').tooltip({container: 'body'});
			$j('#primary-menu-trigger,#overlay-menu-close').click(function() {
				$j( '#primary-menu > ul, #primary-menu > div > ul' ).toggleClass("show");
				return false;
			});
			$j('#page-submenu-trigger').click(function() {
				$jbody.toggleClass('top-search-open', false);
				$jpagemenu.toggleClass("pagemenu-active");
				return false;
			});
			$jpagemenu.find('nav').click(function(e){
				$jbody.toggleClass('top-search-open', false);
				$jtopCart.toggleClass('top-cart-open', false);
			});
			if( SEMICOLON.isMobile.any() ){
				$jbody.addClass('device-touch');
			}
			// var el = {
			//     darkLogo : $j("<img>", {src: defaultDarkLogo}),
			//     darkRetinaLogo : $j("<img>", {src: retinaDarkLogo})
			// };
			// el.darkLogo.prependTo("body");
			// el.darkRetinaLogo.prependTo("body");
			// el.darkLogo.css({'position':'absolute','z-index':'-100'});
			// el.darkRetinaLogo.css({'position':'absolute','z-index':'-100'});
		}

	};

	SEMICOLON.isMobile = {
		Android: function() {
			return navigator.userAgent.match(/Android/i);
		},
		BlackBerry: function() {
			return navigator.userAgent.match(/BlackBerry/i);
		},
		iOS: function() {
			return navigator.userAgent.match(/iPhone|iPad|iPod/i);
		},
		Opera: function() {
			return navigator.userAgent.match(/Opera Mini/i);
		},
		Windows: function() {
			return navigator.userAgent.match(/IEMobile/i);
		},
		any: function() {
			return (SEMICOLON.isMobile.Android() || SEMICOLON.isMobile.BlackBerry() || SEMICOLON.isMobile.iOS() || SEMICOLON.isMobile.Opera() || SEMICOLON.isMobile.Windows());
		}
	};

	SEMICOLON.documentOnResize = {

		init: function(){

			var t = setTimeout( function(){
				SEMICOLON.header.topsocial();
				SEMICOLON.header.fullWidthMenu();
				SEMICOLON.header.overlayMenu();
				SEMICOLON.initialize.fullScreen();
				SEMICOLON.initialize.verticalMiddle();
				SEMICOLON.initialize.maxHeight();
				SEMICOLON.initialize.testimonialsGrid();
				SEMICOLON.slider.captionPosition();
				SEMICOLON.portfolio.arrange();
				SEMICOLON.portfolio.portfolioDescMargin();
				SEMICOLON.widget.tabsJustify();
				SEMICOLON.widget.html5Video();
				SEMICOLON.widget.masonryThumbs();
				SEMICOLON.initialize.dataStyles();
				SEMICOLON.initialize.dataResponsiveHeights();
			}, 500 );

		}

	};

	SEMICOLON.documentOnReady = {

		init: function(){
			SEMICOLON.initialize.init();
			SEMICOLON.header.init();
			if( $jslider.length > 0 ) { SEMICOLON.slider.init(); }
			if( $jportfolio.length > 0 ) { SEMICOLON.portfolio.init(); }
			SEMICOLON.widget.init();
			SEMICOLON.documentOnReady.windowscroll();
		},

		windowscroll: function(){
			
			var headerOffset = $jheader.offset().top;
			var headerWrapOffset = $jheaderWrap.offset().top;

			var headerDefinedOffset = $jheader.attr('data-sticky-offset');
			if( typeof headerDefinedOffset !== 'undefined' ) {
				if( headerDefinedOffset == 'full' ) {
					headerWrapOffset = $jwindow.height();
					var headerOffsetNegative = $jheader.attr('data-sticky-offset-negative');
					if( typeof headerOffsetNegative !== 'undefined' ) { headerWrapOffset = headerWrapOffset - headerOffsetNegative - 1; }
				} else {
					headerWrapOffset = Number(headerDefinedOffset);
				}
			}

			$jwindow.on( 'scroll', function(){

				SEMICOLON.initialize.goToTopScroll();
				$j('body.open-header.close-header-on-scroll').removeClass("side-header-open");
				SEMICOLON.header.stickyMenu( headerWrapOffset );
				SEMICOLON.header.darkLogo();

			});

			window.addEventListener('scroll', function(){
				requestAnimationFrame(function(){
					SEMICOLON.slider.sliderParallax();
					SEMICOLON.slider.sliderElementsFade();
				});
			}, false);

			if( $jonePageMenuEl.length > 0 ){
				$jwindow.scrolled(function() {
					SEMICOLON.header.onepageScroller();
				});
			}
		}

	};

	SEMICOLON.documentOnLoad = {

		init: function(){
			SEMICOLON.slider.captionPosition();
			SEMICOLON.slider.swiperSliderMenu();
			SEMICOLON.slider.revolutionSliderMenu();
			SEMICOLON.initialize.maxHeight();
			SEMICOLON.initialize.testimonialsGrid();
			SEMICOLON.initialize.verticalMiddle();
			SEMICOLON.initialize.stickFooterOnSmall();
			SEMICOLON.portfolio.portfolioDescMargin();
			SEMICOLON.portfolio.arrange();
			SEMICOLON.widget.parallax();
			SEMICOLON.widget.loadFlexSlider();
			SEMICOLON.widget.html5Video();
			SEMICOLON.widget.masonryThumbs();
			SEMICOLON.slider.owlCaptionInit();
			SEMICOLON.header.topsocial();
		}

	};

	var $jwindow = $j(window),
        $jbody = $j('body'),
        $jwrapper = $j('#wrapper'),
        $jheader = $j('#top'),
        $jheaderWrap = $j('#top'),
        $jfooter = $j('#footer'),
        oldHeaderClasses = $jheader.attr('class'),
        oldHeaderWrapClasses = $jheaderWrap.attr('class'),
        stickyMenuClasses = $jheader.attr('data-sticky-class'),
        defaultLogo = $j('#logo').find('.standard-logo'),
        defaultLogoWidth = defaultLogo.find('img').outerWidth(),
        retinaLogo = $j('#logo').find('.retina-logo'),
        defaultLogoImg = defaultLogo.find('img').attr('src'),
        retinaLogoImg = retinaLogo.find('img').attr('src'),
        defaultDarkLogo = defaultLogo.attr('data-dark-logo'),
        retinaDarkLogo = retinaLogo.attr('data-dark-logo'),
        $jpagemenu = $j('#page-menu'),
        $jonePageMenuEl = $j('.one-page-menu'),
        onePageGlobalOffset = 0,
        $jportfolio = $j('#portfolio'),
        $jslider = $j('#slider'),
        $jsliderParallaxEl = $j('.slider-parallax'),
        $jpageTitle = $j('#page-title'),
        $jportfolioItems = $j('.portfolio-ajax').find('.portfolio-item'),
        $jportfolioDetails = $j('#portfolio-ajax-wrap'),
        $jportfolioDetailsContainer = $j('#portfolio-ajax-container'),
        $jportfolioAjaxLoader = $j('#portfolio-ajax-loader'),
        prevPostPortId = '',
        $jtopSearch = $j('#top-search'),
        $jtopCart = $j('#top-cart'),
        $jverticalMiddleEl = $j('.vertical-middle'),
        $jtopSocialEl = $j('#top-social').find('li'),
        $jsiStickyEl = $j('.si-sticky'),
        $jdotsMenuEl = $j('.dots-menu'),
        $jgoToTopEl = $j('#gotoTop'),
        $jfullScreenEl = $j('.full-screen'),
        $jcommonHeightEl = $j('.common-height'),
        $jtestimonialsGridEl = $j('.testimonials-grid'),
        $jpageSectionEl = $j('.page-section'),
        $jowlCarouselEl = $j('.owl-carousel'),
        $jparallaxEl = $j('.parallax'),
        $jparallaxPageTitleEl = $j('.page-title-parallax'),
        $jyoutubeBgPlayerEl = $j('.yt-bg-player'),
        $jtextRotaterEl = $j('.text-rotater');

	$j(document).ready( SEMICOLON.documentOnReady.init );
	$jwindow.load( SEMICOLON.documentOnLoad.init );
	$jwindow.on( 'resize', SEMICOLON.documentOnResize.init );
        SEMICOLON.widget.magentonotifications();

})(jQuery);