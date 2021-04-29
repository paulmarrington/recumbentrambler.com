jQuery("document").ready(function($) {
    $(".cool-timeline-horizontal").find("a[class^='ctl_prettyPhoto']").prettyPhoto({ social_tools:false, show_title:false }); 
    $(".cool-timeline-horizontal").find("a[rel^='ctl_prettyPhoto']").prettyPhoto({ social_tools:false, show_title:false }); 
    
    function storySlideShow(container){
        container.find(".ctl_slideshow .slides").not('.slick-initialized').each(function(){
        $(this).find("a[class^='ctl_prettyPhoto']").prettyPhoto({ social_tools:false, show_title:false }); 
        var autoplaySpeed=parseInt($(this).data('animationspeed'));
        var slideshow=$(this).data('slideshow');

        $(this).not('.slick-initialized').slick({
            dots: false,
            infinite: false,
            arrows:true,
            mobileFirst:true,
            pauseOnHover:true,
            slidesToShow:1,
            autoplay:slideshow,
            autoplaySpeed:autoplaySpeed,
             adaptiveHeight:true
          });      
        }); 
    }

    $(".cool-timeline-horizontal.ht-design-6,.cool-timeline-horizontal.ht-design-5").each(function(e) {
            var thisS =$(this);
            var sliderContent= "#" + thisS.attr("date-slider"),
                sliderNav = "#" + thisS.attr("data-nav"),
                rtl = thisS.attr("data-rtl"),
                items= parseInt(thisS.attr("data-items")),
                autoplay = thisS.attr("data-autoplay"),
                autoplaySettings=autoplay=="true"?true:false,
                rtlSettings=rtl=="true"?true:false,
                startOn= parseInt(thisS.attr("data-start-on")),
                speed = parseInt(thisS.attr("data-autoplay-speed"));
           
            thisS.siblings(".clt_preloader").hide();
            thisS.css("opacity",1);
            showStories=parseInt(thisS.attr("data-items"));
            totalStories=0;
            console.log(sliderNav);
            var  totalStories=$(sliderNav).find("li").length;
            console.log(totalStories);
            var settingsObj={
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplaySpeed:speed,
                rtl:rtlSettings,
                asNavFor:sliderNav,
                dots:false,
                arrows:true,
                //  autoplay: a,
                infinite:false,
                initialSlide:startOn,
                adaptiveHeight:true,
                responsive: [{
                    breakpoint: 768,
                    settings: {
                        centerPadding: "10px",
                        slidesToShow: 1
                    }
                }, {
                    breakpoint: 480,
                    settings: {
                        centerPadding: "10px",
                        slidesToShow: 1
                    }
                }]
            }

          if(totalStories!==undefined && totalStories<=3){
                settingsObj.arrows=true;
            }else{
               settingsObj.arrows=false; 
            };

       $(sliderContent).not('.slick-initialized').slick(settingsObj);
        
        $(sliderNav).not('.slick-initialized').slick({
            slidesToShow: showStories,
            slidesToScroll: 1,
            autoplaySpeed:speed,
            asNavFor:sliderContent,
            dots:false,
            infinite:false,
            centerMode:true,
            rtl:rtlSettings,
            autoplay:autoplaySettings,
            nextArrow: '<button type="button" class="ctl-slick-next "><i class="far fa-arrow-alt-circle-right"></i></button>',
            prevArrow: '<button type="button" class="ctl-slick-prev"><i class="far fa-arrow-alt-circle-left"></i></button>',
            focusOnSelect:true,
            adaptiveHeight:true,
            initialSlide:startOn,
            responsive: [{
                breakpoint: 768,
                settings: {
                    arrows:true,
                    centerPadding: "10px",
                    slidesToShow: 1
                }
            }, {
                breakpoint: 480,
                settings: {
                    arrows:true,
                    centerPadding: "10px",
                    slidesToShow: 1
                }
            }]
        }).on("beforeChange init", function(e, i, o) {
            for (var s = 0; s < i.$slides.length; s++) {
                var a = $(i.$slides[s]);
                if (a.hasClass("slick-current")) {
                    a.addClass("pi"), a.prevAll().addClass("pi"), a.nextAll().removeClass("pi");
                    break
                }
            }
        }).on("afterChange", function(e, i, o) {
            for (var s = 0; s < i.$slides.length; s++) {
                var a = $(i.$slides[s]);
                if (a.hasClass("slick-current")) {
                    a.removeClass("pi"), a.nextAll().removeClass("pi");
                    break
                }
            }
        });
        //enable story slideshow
        storySlideShow(thisS);

    })
});