jQuery("document").ready(function($) {
        $(".cool-timeline-horizontal").find("a[class^='ctl_prettyPhoto']").prettyPhoto({
            social_tools:false,
            show_title:false
        });
         $(".cool-timeline-horizontal").find("a[rel^='ctl_prettyPhoto']").prettyPhoto({
            social_tools:false,
            show_title:false
        });

        function storySlideShow(container){
            container.find(".ctl_slideshow .slides").not('.slick-initialized').each(function(){
            $(this).find("a[class^='ctl_prettyPhoto']").prettyPhoto({ social_tools:false, show_title:false }); 
            var autoplaySpeed=parseInt($(this).data('animationspeed'));
            var slideshow=$(this).data('slideshow');
            $(this).slick({
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
$(".cool-timeline-horizontal.ht-default").each(function() {
            var thisS =$(this);
            var sliderContent= "#" + thisS.attr("date-slider"),
                sliderNav = "#" + thisS.attr("data-nav"),
                rtl = thisS.attr("data-rtl"),
                autoplay = thisS.attr("data-autoplay"),
                autoplaySettings=autoplay=="true"?true:false,
                rtlSettings=rtl=="true"?true:false,
                startOn= parseInt(thisS.attr("data-start-on")),
                speed = parseInt(thisS.attr("data-autoplay-speed"));
                showSlides= 3;
            var  totalStories=$(sliderNav).find("li").length;

            thisS.siblings(".clt_preloader").hide();
            thisS.css("opacity", 1);
            settingObj={
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplaySpeed: speed,
                rtl:rtlSettings,
                asNavFor:sliderNav,
                dots:false,
                autoplay:autoplaySettings,
                infinite:false,
                initialSlide:startOn,
                adaptiveHeight: true,
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
            };
         if(totalStories!==undefined && totalStories<=3){
                settingObj.arrows=true;
                settingObj.nextArrow='<button type="button" style="background:none;" class="ctl-slick-next "><i class="far fa-arrow-alt-circle-right"></i></button>';
                settingObj.prevArrow='<button type="button" style="background:none;" class="ctl-slick-prev"><i class="far fa-arrow-alt-circle-left"></i></button>';
            }else{
                settingObj.arrows=false; 
            }
            $(sliderContent).not('.slick-initialized').slick(settingObj);

            $(sliderNav).not('.slick-initialized').slick({
                slidesToShow:showSlides,
                slidesToScroll: 1,
                asNavFor:sliderContent,
                dots:false,
                infinite:false,
                rtl:rtlSettings,
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
            })

             //enable story slideshow
            storySlideShow(thisS);
        });
    }



);