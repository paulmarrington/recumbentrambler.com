jQuery('document').ready(function($){
	var timelineWrapper=$(".cool_timeline");
	timelineWrapper.each(function(){
		storySlideShow($(this));
		ctlStoryPopup($(this));
		ctlYearNavigation($(this));
	});
	if(timelineWrapper.hasClass('main-design-7')){
		$(".timeline-post").find("a[ref^='prettyPhoto']").on("click",function(){
			var id=$(this).attr("href");
			$(id).find(".ctl_info").find("iframe").css("width","100%");
		 });
	}

	ctlStoryAnimation();
	var ele_width=timelineWrapper.find('.timeline-content').find(".ctl_info").width();
	ele_width=ele_width-20;
	var value =ele_width
    value *= 1;
    var valueHeight = Math.round((value/4)*3);
	timelineWrapper.find('.full-width > iframe').height(valueHeight);


	function Utils() {
	}

//detect element position in page
Utils.prototype = {
    constructor: Utils,
    isElementInView: function (element, fullyInView) {
        var pageTop = $(window).scrollTop();
        var pageBottom = pageTop + $(window).height();
        var elementTop = parseInt($(element).offset().top)+200;
        var elementBottom = elementTop + parseInt($(element).height())-500;

        if (fullyInView === true) {
            return ((pageTop < elementTop) && (pageBottom > elementBottom));
        } else {
            return ((elementTop <= pageBottom) && (elementBottom >= pageTop));
        }
    }
};

var Utils = new Utils();

	

function storySlideShow(container){
	container.find(".ctl_flexslider .slides").not('.slick-initialized').each(function(){
	$(this).find("a[class^='ctl_prettyPhoto']").prettyPhoto({ social_tools:false, show_title:false }); 
	var autoplaySpeed=parseInt($(this).data('animationspeed'));
	var slideshow=$(this).data('slideshow');
	var animation=$(this).data('animation');

	if ($(this).parents('.cool-timeline').hasClass('compact')) {
		var autoHeight = false;
	}else {
		var autoHeight = true;
	}

	$(this).slick({
		dots: false,
		infinite: false,
		arrows:true,
		mobileFirst:true,
		pauseOnHover:true,
		slidesToShow:1,
		autoplay:slideshow,
		autoplaySpeed:autoplaySpeed,
		 adaptiveHeight: autoHeight,
	  });      
	}); 
}

// stories pretty photo popup
function ctlStoryPopup(timelineWrapper){
		// applied lightbox in story images
	timelineWrapper.find("a[class^='ctl_prettyPhoto']").prettyPhoto({
		social_tools: false 
		});
		timelineWrapper.find("a[rel^='ctl_prettyPhoto']").prettyPhoto({
		   social_tools: false
	   });
	   timelineWrapper.find("a[ref^='prettyPhoto']").prettyPhoto({
		   social_tools: false,
		   show_title:false,
		  changepicturecallback: function(){ ctlInitialize(); },
		   callback: function(){ctlUnInitialize()},
		  }); 
}

// year navigation
function ctlYearNavigation(timelineWrapper){
		// creates year scrolling navigation
		var pagination= timelineWrapper.attr('data-pagination');
		var pagination_position= timelineWrapper.attr('data-pagination-position');
		var bull_cls='';
		var position='';
		if(pagination_position=="left"){
			 bull_cls='section-bullets-left';
			position='left';
		}else if(pagination_position=="right"){
			 bull_cls='section-bullets-right';
			position='right';
		}else if(pagination_position=="bottom"){
			 bull_cls='section-bullets-bottom';
			position='bottom';
		}
		timelineWrapper.each(function(index){
			var id=$(this).attr("id");
		if(id!==undefined){
			if(pagination=="yes"){
			  $('body').sectionScroll({
			  // CSS class for bullet navigation
			  bulletsClass:bull_cls,
			  // CSS class for sectioned content
			  sectionsClass:'scrollable-section',
			  // scroll duration in ms
			  scrollDuration: 1500,
			  // displays titles on hover
			  titles: true,
			  // top offset in pixels
			  topOffset:2,
			  // easing opiton
			  easing: '',
			  id:id,
			  position:position,
			});
			}
		}
		});
		
		if(pagination=="yes"){
		$('.ctl-bullets-container').hide();
		timelineWrapper.each(function(){
		if (typeof  $(this).attr("id") !== typeof undefined &&  $(this).attr("id") !== false) {
			 var id="#"+ $(this).attr("id");
			 var nav_id="#"+ $(this).attr("id")+'-navi';
			 $(nav_id).find('li').removeClass('active');
			 var offset = $(id).offset();
			  var t_height =$(id).height();
		
		 $(window).scroll(function () {
		  var isElementInView = Utils.isElementInView($(id), false);
			if (isElementInView) {
			  $(nav_id).show();
			} else {
				 $(nav_id).hide(); 
			}
			});
		}
		 });
	   }
}

// on scroll stories animations
function ctlStoryAnimation(){
	// enabled animation on page scroll
	$(".cooltimeline_cont").each(function(index ){
		var timeline_id=$(this).attr('id');
		var animations=$("#"+timeline_id).attr("data-animations");
	if(animations!="none") {
		// You can also pass an optional settings object
		// below listed default settings
		AOS.init({
			// Global settings:
			disable:'mobile', // accepts following values: 'phone', 'tablet', 'mobile', boolean, expression or function
			startEvent: 'DOMContentLoaded', // name of the event dispatched on the document, that AOS should initialize on
			offset: 75, // offset (in px) from the original trigger point
			delay: 0, // values from 0 to 3000, with step 50ms
			duration: 750, // values from 0 to 3000, with step 50ms
			easing: 'ease-in-out-sine', // default easing for AOS animations
			mirror: true,
		});
			
			}
	});
}
// popup slide show in minimal design
function ctlInitialize(){
	$(".ctl_popup_slick .slides").each(function(){
	  $(this).slick({
		  dots: false,
		  infinite: false,
		  slidesToShow:1,
		  autoplay:false,
		  autoplaySpeed: 2000,
		   adaptiveHeight: true
		});      
	  }); 
	}
	function ctlUnInitialize(){
	  $(".ctl_popup_slick .slides").each(function(){
		$(this).slick('unslick');      
	  }); 
  
	  }

});


jQuery(window).on('load', function($) {
	var timeline_id=jQuery('.cooltimeline_cont').attr('id');
	var animations=jQuery("#"+timeline_id).attr("data-animations");
	if(animations!="none") {
		setTimeout(function(){ 
		AOS.refresh(); }, 500);
	}
});