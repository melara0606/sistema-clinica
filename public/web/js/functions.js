/*------------------------------------
	Theme Name: Doctor
	Start Date : 
	End Date : 
	Last change: 
	Version: 1.0
	Assigned to:
	Primary use:
---------------------------------------*/
/*	
	
	* Document Scroll		
		
	* Document Ready
		- Menu Triangle
		- Responsive Caret
		- Google Map
		- Scrolling Navigation
		- Add Easing Effect
		- Search
		- Rev Slider
		- Client Carousel
		- Team Carousel
		- News Carousel
		- Gallery Section 
		- Gallery Detail
		- Testimonial Slider
		- Contact Map
		- Quick Contact Form
		- Questiong Form
		- Appointment Form

	* Window Load
		- Site Loader
*/

(function($) {

	"use strict"
	
	/* - Menu Switch * */
	function menu_switch(){
		var width = $(window).width();
		if( width > 991 ) {
			$(".menu-switch > a").on("click", function() {
				$(".ownavigation .navbar-nav").toggleClass("menu-open")
			});
		} else {
			$(".ownavigation .navbar-nav").removeClass("menu-open");
		}
	}
	
	/* - Menu Triangle */
	function menu_triangle() {
		var width = $(window).width();
		if( width > 991 ) {
			$( ".header_s16 .ownavigation .nav > li" ).each(function (i){
				var menu_width;			
				menu_width = $(".header_s16 .ownavigation .nav > li").eq(i).width();
				var border_width = menu_width / 2;
				$(".header_s16 .ownavigation .nav > li > a").eq(i).find(' > span').remove();
				$(".header_s16 .ownavigation .nav > li > a").eq(i).append("<span class='left-brd'></span>","<span class='right-brd'></span>");
				$(".header_s16 .ownavigation .nav > li > a .left-brd").eq(i).css("border-right-width",border_width);
				$(".header_s16 .ownavigation .nav > li > a .right-brd").eq(i).css("border-left-width",border_width);		
			});
		} else {
			$( ".header_s16 .ownavigation .nav > li" ).each(function (i){
				$(".header_s16 .ownavigation .nav > li > a").eq(i).find(' > span').remove();
			});
		}
	}

	/* - Responsive Caret* */
	function menu_dropdown_open(){
		var width = $(window).width();
		if( width > 991 ) {
			if($(".ownavigation .nav li.ddl-active").length ) {
				$(".ownavigation .nav > li").removeClass("ddl-active");
				$(".ownavigation .nav li .dropdown-menu").removeAttr("style");
			}
		} else {
			$(".ownavigation .nav li .dropdown-menu").removeAttr("style");
		}
	}
	
	/* - Expand Panel Resize * */
	function panel_resize(){
		var width = $(window).width();
		if( width > 991 ) {
			if($(".header-section #slidepanel").length ) {
				$(".header-section #slidepanel").removeAttr("style");
			}
		}
	}
	
	/* - Google Map* */
	function initialize(obj) {
		var lat = $("#"+obj).attr("data-lat");
        var lng = $("#"+obj).attr("data-lng");
		var contentString = $("#"+obj).attr("data-string");
		var myLatlng = new google.maps.LatLng(lat,lng);
		var map, marker, infowindow;
		var image = "assets/images/marker.png";
		var zoomLevel = parseInt($("#"+obj).attr("data-zoom") ,10);		
		var styles = [{"featureType":"landscape","stylers":[{"saturation":" "},{"lightness":" "},{"visibility":"on"}]},{"featureType":"poi","stylers":[{"saturation":" "},{"lightness":" "},{"visibility":"simplified"}]},{"featureType":"road.highway","stylers":[{"saturation":" "},{"visibility":"simplified"}]},{"featureType":"road.arterial","stylers":[{"saturation":" "},{"lightness":" "},{"visibility":"on"}]},{"featureType":"road.local","stylers":[{"saturation":" "},{"lightness":" "},{"visibility":"on"}]},{"featureType":"transit","stylers":[{"saturation":" "},{"visibility":"simplified"}]},{"featureType":"administrative.province","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":" "},{"saturation":" "}]},{"featureType":"water","elementType":"geometry","stylers":[{"hue":"#ffff00"},{"lightness":" "},{"saturation":" "}]}]
		var styledMap = new google.maps.StyledMapType(styles,{name: "Styled Map"});	
		
		var mapOptions = {
			zoom: zoomLevel,
			disableDefaultUI: true,
			center: myLatlng,
            scrollwheel: false,
			mapTypeControlOptions: {
            mapTypeIds: [google.maps.MapTypeId.ROADMAP, "map_style"]
			}
		}
		
		map = new google.maps.Map(document.getElementById(obj), mapOptions);	
		
		map.mapTypes.set("map_style", styledMap);
		map.setMapTypeId("map_style");
		
		infowindow = new google.maps.InfoWindow({
			content: contentString
		});      
	    
        marker = new google.maps.Marker({
			position: myLatlng,
			map: map,
			icon: image
		});

		google.maps.event.addListener(marker, "click", function() {
			infowindow.open(map,marker);
		});	
	}
	
	function sticky_menu() {
		var menu_scroll = $('header[class*="header_s"]').offset().top;
		var scroll_top = $(window).scrollTop();
		
		if ( scroll_top > menu_scroll ) {
			$(".header_s .ownavigation").addClass("navbar-fixed-top animated fadeInDown");
		} else {
			$(".header_s .ownavigation").removeClass("navbar-fixed-top animated fadeInDown"); 
		}
	}

	/* ## Document Ready - Handler for .ready() called */
	$(document).ready(function($) {

		/* - Scrolling Navigation* */
		var width	=	$(window).width();
		var height	=	$(window).height();
		
		/* - Set Sticky Menu* */
		if( $(".header_s .ownavigation").length ) {
			sticky_menu();
		}

		/* - Responsive Caret* */
		$(".ddl-switch").on("click", function() {
			var li = $(this).parent();
			if ( li.hasClass("ddl-active") || li.find(".ddl-active").length !== 0 || li.find(".dropdown-menu").is(":visible") ) {
				li.removeClass("ddl-active");
				li.children().find(".ddl-active").removeClass("ddl-active");
				li.children(".dropdown-menu").slideUp();
			}
			else {
				li.addClass("ddl-active");
				li.children(".dropdown-menu").slideDown();
			}
		});
		
		/* - Expand Panel * */
		$("#slideit").on ("click", function() {
			$("#slidepanel").slideDown(1000);
			$("html").animate({ scrollTop: 0 }, 1000);
		});	

		/* Collapse Panel * */
		$("#closeit").on("click", function() {
			$("#slidepanel").slideUp("slow");
			$("html").animate({ scrollTop: 0 }, 1000);
		});	
		
		/* Switch buttons from "Log In | Register" to "Close Panel" on click * */
		$("#toggle a").on("click", function() {
			$("#toggle a").toggle();
		});
		
		/* - Color Switcher */
		if( $('#choose_style').length ) {

			 $("#default").on("click", function() {
				$("#color" ).attr("href","assets/css/color-schemes/default.css");
				return false;
			});

			$("#blue").on("click", function() {
				$("#color" ).attr("href","assets/css/color-schemes/blue.css");
				return false;
			});
			
			$("#green").on("click", function() {
				$("#color" ).attr("href","assets/css/color-schemes/green.css");
				return false;
			});

			$("#coral" ).on("click", function() {
				$("#color" ).attr("href","assets/css/color-schemes/coral.css");
				return false;
			});

			$("#cyan" ).on("click", function() {
				$("#color" ).attr("href","assets/css/color-schemes/cyan.css");
				return false;
			});

			$("#eggplant" ).on("click", function() {
				$("#color" ).attr("href","assets/css/color-schemes/eggplant.css");
				return false;
			});

			$("#pink" ).on("click", function() {
				$("#color" ).attr("href","assets/css/color-schemes/pink.css");
				return false;
			});

			$("#slateblue" ).on("click", function() {
				$("#color" ).attr("href","assets/css/color-schemes/slateblue.css");
				return false;
			});

			$("#gold" ).on("click", function() {
				$("#color" ).attr("href","assets/css/color-schemes/gold.css");
				return false;
			});

			$("#red" ).on("click", function() {
				$("#color" ).attr("href","assets/css/color-schemes/red.css");
				return false;
			});

			// picker buttton
			$(".picker_close").on("click", function() {
				$("#choose_style").toggleClass("position");
			});
		}
		
		$(".color-switcher-block li a").on("click", function() {
			$(".color-switcher-block li").removeClass("active");
			$(this).parent().addClass("active");
		});
		
		/* - Cookies */
		var layout = $.cookie('choose-switcher-box > ul li');
		if (!($.cookie('choose-switcher-box > ul li'))) {
			
			$.cookie('choose-switcher-box > ul li', 'full-view', 365);
			layout = $.cookie('choose-switcher-box > ul li');
			$('#choose_style .choose-switcher-box > ul li a[id="full_view"]');
			
		} else {
			
			if (layout=="box-view") {
				
				$("body").addClass("boxed-layout");
				
			} else { 
			
				$("body").removeClass("boxed-layout");
			};
			
		};
		
		/* - View Box */
		$("#full_view").on("click", function(e) {
			e.preventDefault(); 
			$("body").removeClass("boxed-layout");
			$.cookie('choose-switcher-box > ul li', 'full-view', 365);
			document.location.reload();
		});
		
		$("#box_view").on("click", function(e) {
			e.preventDefault(); 
			$("body").addClass("boxed-layout");
			$.cookie('choose-switcher-box > ul li', 'box-view', 365);
			document.location.reload();
		});
		
		/* - Rev Slider */
		if($(".slider-section").length){
			$("#home-slider1").revolution({
				sliderType:"standard",
				sliderLayout:"auto",
				delay:6000,
				navigation: {
					arrows:{
						enable:true,
						style:"uranus"
					},
					bullets: {
						enable:true,
						style:"zeus",
						hide_onleave:false,						
						direction:"horizontal",
						h_align:"center",
						v_align:"bottom",
						h_offset:0,
						v_offset:-45,
						space:10,
						tmp:''
					}
				},
				responsiveLevels:[1920,1024,768,480],
				gridwidth:[1920,1024,768,480],
				gridheight:[989,800,700,480]
			});
		}
		
		/* - Menu Switch */
		if($(".menu-switch").length){
			menu_switch();
		}
		
		/* - Menu Triangle */
		menu_triangle();
		
		panel_resize();
		
		/* - Search* */
		if($(".search-box").length){
			$("#search").on("click", function(){
				$(".search-box").addClass("active")
			});
			$(".search-box span").on("click", function(){
				$(".search-box").removeClass("active")
			});
		}
		
		$('#datetimepicker1 input').datepicker({ });
		
		/* - Client Carousel */
		if( $(".clients-carousel").length ) {
			$(".clients-carousel").owlCarousel({
				loop: true,
				margin: 0,
				nav: true,
				dots: false,
				autoplay: false,
				responsive:{
					0:{
						items: 1
					},
					500:{
						items: 2
					},
					600:{
						items: 3
					},
					1000:{
						items: 4
					}
				}
			});
		}
		
		/* - Team Carousel */
		if( $(".team-carousel").length ) {
			$(".team-carousel").owlCarousel({
				loop: true,
				margin: 0,
				nav: true,
				dots: false,
				autoplay: false,
				responsive:{
					0:{
						items: 1
					},
					480:{
						items: 2
					},
					600:{
						items: 3
					},
					1000:{
						items: 4
					}
				}
			});
		}
		
		/* - News Carousel */
		if( $(".news-carousel").length ) {
			$(".news-carousel").owlCarousel({
				loop: true,
				margin: 0,
				nav: true,
				dots: false,
				autoplay: false,
				responsive:{
					0:{
						items: 1
					},
					768:{
						items: 2
					}
				}
			});
		}
		
		/* - Gallery Section */		
		if( $(".content-image-block").length ){
			$(".content-block-hover").magnificPopup({
				delegate: "a.zoom-in",
				type: "image",
				tLoading: "Loading image #%curr%...",
				mainClass: "mfp-img-mobile",
				gallery: {
					enabled: true,
					navigateByImgClick: false,
					preload: [0,1] // Will preload 0 - before current, and 1 after the current image
				},
				image: {
					tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',				
				}
			});
		}
		
		/* - Gallery Detail */
		if( $(".gallery-details-thumb").length ){
			$('.gallery-details-thumb').flexslider({
				animation: "slide",
				controlNav: false,
				animationLoop: false,
				slideshow: false,
				itemWidth: 230,
				itemMargin: 30,
				asNavFor: '.gallery-details-full'
			});

			$('.gallery-details-full').flexslider({
				animation: "slide",
				controlNav: false,
				animationLoop: false,
				slideshow: false,
				sync: ".gallery-details-thumb"
			});
		}
		
		/* - Testimonial Slider */			
		$(".testimonial-slider").slick({
			centerMode: true,
			centerPadding: "190px",
			slidesToShow: 3,
			autoplay: false,
			responsive: [
				{
					breakpoint: 1366,
					settings: {
						centerPadding: "90px",
					}
				},
				{
					breakpoint: 1200,
					settings: {
						centerPadding: "0",
					}
				},
				{
					breakpoint: 768,
					settings: {
						arrows: false,
						centerMode: true,
						centerPadding: "0",
						slidesToShow: 1
					}
				}
			]
		});

		/* - Contact Map* */
		if($("#map-canvas-contact").length===1){
			initialize("map-canvas-contact");
		}
		if($("#map-canvas-contact-1").length===1){
			initialize("map-canvas-contact-1");
		}
		
		/* - Quick Contact Form */
		$( "#btn_submit" ).on( "click", function(event) {
			event.preventDefault();
			var mydata = $("form").serialize();
			$.ajax({
				type: "POST",
				dataType: "json",
				url: "contact.php",
				data: mydata,
				success: function(data) {
					if( data["type"] == "error" ){
						$("#alert-msg").html(data["msg"]);
						$("#alert-msg").removeClass("alert-msg-success");
						$("#alert-msg").addClass("alert-msg-failure");
						$("#alert-msg").show();
					} else {
						$("#alert-msg").html(data["msg"]);
						$("#alert-msg").addClass("alert-msg-success");
						$("#alert-msg").removeClass("alert-msg-failure");					
						$("#input_name").val("");
						$("#input_phone").val("");
						$("#textarea_message").val("");
						$("#alert-msg").show();
					}
				},
				error: function(xhr, textStatus, errorThrown) {
					//alert(textStatus);
				}
			});
			return false;
		});/* Quick Contact Form /- */
		
		/* - Questiong Form */
		$( "#que_btn_submit" ).on( "click", function(event) {
			event.preventDefault();
			var mydata = $("form").serialize();
			$.ajax({
				type: "POST",
				dataType: "json",
				url: "question.php",
				data: mydata,
				success: function(data) {
					if( data["type"] == "error" ){
						$("#alert-msg-que").html(data["msg"]);
						$("#alert-msg-que").removeClass("alert-msg-success");
						$("#alert-msg-que").addClass("alert-msg-failure");
						$("#alert-msg-que").show();
					} else {
						$("#alert-msg-que").html(data["msg"]);
						$("#alert-msg-que").addClass("alert-msg-success");
						$("#alert-msg-que").removeClass("alert-msg-failure");					
						$("#input_fname").val("");
						$("#input_email").val("");
						$("#input_subject").val("");
						$("#input_message").val("");
						$("#alert-msg-que").show();
					}
				},
				error: function(xhr, textStatus, errorThrown) {
					//alert(textStatus);
				}
			});
			return false;
		});/* - Questiong Form *- */
		
		/* - Appointment Form */
		$( "#appointment_submit" ).on( "click", function(event) {
			event.preventDefault();
			var mydata = $("form").serialize();
			$.ajax({
				type: "POST",
				dataType: "json",
				url: "appointment.php",
				data: mydata,
				success: function(data) {
					if( data["type"] == "error" ){
						$("#appointment-alert-msg").html(data["msg"]);
						$("#appointment-alert-msg").removeClass("alert-msg-success");
						$("#appointment-alert-msg").addClass("alert-msg-failure");
						$("#appointment-alert-msg").show();
					} else {
						$("#appointment-alert-msg").html(data["msg"]);
						$("#appointment-alert-msg").addClass("alert-msg-success");
						$("#appointment-alert-msg").removeClass("alert-msg-failure");					
						$("#patient_name").val("");
						$("#patient_email").val("");
						$("#patient_date").val("");
						$("#patient_time").val("");
						$("#patient_department").val("");
						$("#patient_message").val("");
						$("#appointment-alert-msg").show();
					}
				},
				error: function(xhr, textStatus, errorThrown) {
					//alert(textStatus);
				}
			});
			return false;
		});/* - Appointment Form /- */
		
	});	/* - Document Ready /- */
	
	/* Event - Window Scroll */
	$(window).on("scroll",function() {
		/* - Set Sticky Menu* */
		if( $(".header_s .ownavigation").length ) {
			sticky_menu();
		}
	});
	
	$( window ).on("resize",function() {
		
		var width	=	$(window).width();
		var height	=	$(window).height();
		
		/* - Expand Panel Resize */
		panel_resize();
		
		/* - Menu Switch */
		if($(".menu-switch").length){
			menu_switch();
		}
		
		/* - Menu Triangle */
		menu_triangle();
		
	});
	
	/* ## Window Load - Handler for .load() called */
	$(window).on("load",function() {
		/* - Site Loader* */
		if ( !$("html").is(".ie6, .ie7, .ie8") ) {
			$("#site-loader").delay(1000).fadeOut("slow");
		}
		else {
			$("#site-loader").css("display","none");
		}
		
		/* - Gallery Section */	
		if( $(".portfolio-list").length ) {
			var $container = $(".portfolio-list");
			$container.isotope({
			  itemSelector: ".portfolio-list > li",
			  gutter: 0,
			  transitionDuration: "0.5s"
			});

			$("#filters a").on("click",function(){
				$("#filters a").removeClass("active");
				$(this).addClass("active");
				var selector = $(this).attr("data-filter");
				$container.isotope({ filter: selector });		
				return false;
			});
		}
		
	});

})(jQuery);