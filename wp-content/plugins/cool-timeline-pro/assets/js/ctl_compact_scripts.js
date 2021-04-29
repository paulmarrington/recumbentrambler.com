(function($) {

    function initMasonry( $grid ) {
        var $grid = $grid.masonry({
            itemSelector: '.timeline-mansory',
            initLayout: false,
        });
        // layout images after they are loaded
        $grid.imagesLoaded().progress( function() {
            $grid.masonry('layout');
        });
        
        $grid.one('layoutComplete', function() {
            var leftPos = 0;
            var topPosDiff;
            $grid.find('.timeline-mansory').each(function(index) {              
               
                leftPos = $(this).position().left;
                if (leftPos <= 0) {
                    $(this).removeClass("ctl-right").addClass('ctl-left');
                } else {
                    $(this).removeClass("ctl-left").addClass('ctl-right');
                }

                topPosDiff = $(this).position().top - $(this).prev().position().top;
                if(topPosDiff < 40) {

                    $(this).find('.timeline-icon').removeClass("compact-iconup").addClass('compact-icondown');
                    $(this).prev().find('.timeline-icon').removeClass("compact-icondown").addClass('compact-iconup');

                    $(this).find('.content-title').removeClass("compact-afterup").addClass('compact-afterdown');
                    $(this).prev().find('.content-title').removeClass("compact-afterdown").addClass('compact-afterup');

                    $(this).find('.timeline-content').removeClass("compact-afterup").addClass('compact-afterdown');
                    $(this).prev().find('.timeline-content').removeClass("compact-afterdown").addClass('compact-afterup');
                }
            });
            $(".timeline-icon").addClass("showit");
            $(".content-title").addClass("showit-after");
        });
    }


    $(document).ready(function() {
        initMasonry( $('.clt-compact-cont') );
    }); 
    
    $(window).on("load", function() {
        initMasonry( $('.clt-compact-cont') );
    });

    $(window).on("resize", function() {
        initMasonry( $('.clt-compact-cont') );
    });

    $('body').on('click', '.ct-cat-filters', function() {
        setTimeout(function() {
            initMasonry( $('.clt-compact-cont') );
            var timeline_id=jQuery('.cooltimeline_cont').attr('id');
        	var animations=jQuery("#"+timeline_id).attr("data-animations");
        	if(animations!="none") {
                AOS.refreshHard();
            }
        }, 2000);
    });

})(jQuery);