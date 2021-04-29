jQuery("document").ready(function($) {
    $(".cool-timeline-horizontal").find("a[class^='ctl_prettyPhoto']").prettyPhoto({ social_tools:false, show_title:false }); 
    $(".cool-timeline-horizontal").find("a[rel^='ctl_prettyPhoto']").prettyPhoto({ social_tools:false, show_title:false }); 
   
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
             adaptiveHeight: true
          });      
        }); 
    }


 $(".cool-timeline-horizontal.ht-design-3").each(function(i) {
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
          
        $(sliderNav).not('.slick-initialized').slick( {
            slidesToShow: items,
            slidesToScroll: 1, 
            autoplaySpeed:speed, 
            asNavFor:sliderContent,
            dots:false, 
            autoplay:autoplaySettings, 
            rtl:rtlSettings, 
            initialSlide:startOn, 
            focusOnSelect:true, 
            infinite:false, 
            nextArrow:'<button type="button" class="ctl-slick-next ctl-flat-left"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>', 
            prevArrow:'<button type="button" class="ctl-slick-prev ctl-flat-right"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>',
            responsive: [ {
            breakpoint: 980, 
                settings: {
                    slidesToShow: 2, 
                    slidesToScroll: 2,
                    centerPadding: "10px"
                }
            }
            , {
                breakpoint: 768, 
                settings: {
                     arrows:true,
                     centerPadding: "10px", 
                     slidesToShow: 1
                }
            }
            , {
                breakpoint: 480,
                 settings: {
                    arrows:true,
                    centerPadding: "10px", 
                    slidesToShow: 1
                }
            }
            ]
        }
        
        ),
        $(sliderContent).not('.slick-initialized').slick( {
            slidesToShow:items,
            slidesToScroll: 1, 
            asNavFor:sliderNav, 
            arrows:false, 
            dots:false,
            rtl:rtlSettings, 
            initialSlide:startOn,
            infinite:false, 
            adaptiveHeight:true,
            responsive: [ {
             breakpoint: 980, 
                settings: {
                    slidesToShow: 2, 
                    slidesToScroll: 2,
                    centerPadding: "10px"
                }
            }
            , {
            breakpoint: 768, 
                settings: {
                    slidesToShow: 1, 
                    slidesToScroll: 1,
                    centerPadding: "10px"
                 }
                }
            , {
            breakpoint: 480,
             settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                centerPadding: "10px"
             }
             }
            ]
            }

            );
            //enable story slideshow
            storySlideShow(thisS);
    });
        
});