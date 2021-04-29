<?php


        /*
            Creating plugin settings panel
         */

        function ctl_option_panel() {

            /**
             * configure your admin page
             */
            $config = array(
                'menu' =>array('top'=>'cool_timeline'), //sub page to settings page
                'page_title' => __('Cool Timeline Pro Settings', 'cool-timeline'), //The name of this page 
                'capability' => 'manage_options', // The capability needed to view the page
                'option_group' => 'cool_timeline_options', //the name of the option to create in the database
                'id' => 'cool_timeline_page', // meta box id, unique per page
                'fields' => array(), // list of fields (can be added by field arrays)
                'local_images' => false, // Use local or hosted images (meta box images for add/remove)
                'use_with_theme' => false          //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
            );

            /**
             * instantiate your admin page
             */
            $options_panel = new BF_Admin_Page_Class_Pro($config);
            $options_panel->OpenTabs_container('');

            /**
             * define your admin page tabs listing
             */
            $options_panel->TabsListing(array(
                'links' => array(
                    'options_1' => __('General Settings', 'cool-timeline'),
                    'options_2' => __('Style Settings', 'cool-timeline'),
                    'options_3' => __('Typography Settings', 'cool-timeline'),
                    'options_4' => __('Stories Settings', 'cool-timeline'),
                    'options_5' => __('Date Settings', 'cool-timeline'),
                    'options_7' => __('Navigation Settings', 'cool-timeline'),
                    'options_8' => __('Timeline Display', 'cool-timeline'),
                    'options_11' => __('Content Timeline Settings', 'cool-timeline'),
                    'options_6' => __('Extra Settings', 'cool-timeline'),
                     'options_10' => __('Migrations', 'cool-timeline'),
                    
                )
            ));

            /**
             * Open admin page first tab
             */
            $options_panel->OpenTab('options_1');

            /**
             * Add fields to your admin page first tab
             * 
             * Simple options:
             * input text, checbox, select, radio 
             * textarea
             */
            //title
            $options_panel->Title(__("General Settings", "cool-timeline"));
            $options_panel->addText('title_text', array('name' => __('Timeline Title (Default) ', 'cool-timeline'), 'std' => 'Cool Timeline', 'desc' => __('', 'cool-timeline')));

            //select field
            $options_panel->addSelect('title_tag', array('h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6'), array('name' => __('Title Heading Tag ', 'cool-timeline'), 'std' => array('h1'), 'desc' => __('', 'cool-timeline')));
            $options_panel->addRadio('title_alignment', array('left' => 'Left',
                'center' => 'Center', 'right' => 'Right'), array('name' => __('Title Alignment ?', 'cool-timeline'), 'std' => array('center'), 'desc' => __('', 'cool-timeline')));
            $options_panel->addRadio('display_title', array('yes' => 'Yes',
                'no' => 'No'), array('name' => __('Display Title ?', 'cool-timeline'), 'std' => array('yes'), 'desc' => __('', 'cool-timeline')));

            $options_panel->addText('post_per_page', array('name' => __('Number of stories to display ?', 'cool-timeline'), 'std' =>20, 'desc' => __('This option is overridden by shortcode. Please check shortcode generator.', 'cool-timeline')));
         
            $options_panel->addText('content_length', array('name' => __('Content Length ? ', 'cool-timeline'), 'std' => 50, 'desc' => __('Please enter no of words', 'cool-timeline')));
            //Image field
            
            $options_panel->addImage('user_avatar', array('name' => __('Timeline default Image (300x300 best fit)', 'cool-timeline'), 'desc' => __('', 'cool-timeline')));

            $options_panel->addRadio('desc_type', array('short' => 'Short (Default)',
                'full' => 'Full (with HTML)'), array('name' => __('Stories Description?', 'cool-timeline'), 'std' => array('short'), 'desc' => __('This option is overridden by shortcode in V2.1. Please check shortcode generator.', 'cool-timeline')));

            $options_panel->addRadio('display_readmore', array('yes' => 'Yes',
                'no' => 'No'), array('name' => __('Display read more ?', 'cool-timeline'), 'std' => array('yes'), 'desc' => __('', 'cool-timeline')));

            $options_panel->addText('read_more_lbl', array('name' => __('Stories Read more Text', 'cool-timeline'), 'std' => '', 'desc' => __('', 'cool-timeline')));


            $options_panel->addRadio('posts_orders', array('DESC' => 'DESC',
                'ASC' => 'ASC'), array('name' => __('Stories Order ?', 'cool-timeline'), 'std' => array('DESC'), 'desc' => __('This option is overridden by shortcode. Please check your shortcode generator.', 'cool-timeline')));
              //select field
              $options_panel->CloseTab();

			 /**
             * Open admin page secondsetting-error-tgmpa tab
             */
            $options_panel->OpenTab('options_2');
            $options_panel->Title(__("Style Settings", "cool-timeline"));
            /**
             * To Create a Conditional Block first create an array of fields (just like a repeater block
             * use the same functions as above but add true as a last param
             */
            $Conditinal_fields[] = $options_panel->addColor('bg_color', array('name' => __('Background Color', 'cool-timeline')), true);

            /**
             * Then just add the fields to the repeater block
             */
            //conditinal block 
            $options_panel->addCondition('background', array(
                'name' => __('Container Background ', 'cool-timeline'),
                'desc' => __('', 'cool-timeline'),
                'fields' => $Conditinal_fields,
                'std' => false
            ));

            //Color field
            $options_panel->addColor('content_bg_color', array('name' => __('Story Background Color', 'cool-timeline'), 'std' =>'#f9f9f9', 'desc' => __('', 'cool-timeline')));

            $options_panel->addColor('content_color', array('name' => __('Content Font Color', 'cool-timeline'),'std' =>'#666666', 'desc' => __('', 'cool-timeline')));
            $options_panel->addColor('title_color', array('name' => __('Story Title Color', 'cool-timeline'),'std' =>'#ffffff', 'desc' => __('', 'cool-timeline')));

            $options_panel->addColor('circle_border_color', array('name' => __('Circle Color', 'cool-timeline'), 'std' =>'#222222', 'desc' => __('', 'cool-timeline')));

            $options_panel->addColor('line_color', array('name' => __('Line Color', 'cool-timeline'), 'std' =>'#000', 'desc' => __('', 'cool-timeline')));
            //Color field
            $options_panel->addColor('first_post', array('name' => __('First Color', 'cool-timeline'), 'std' =>'#02c5be', 'desc' => __('', 'cool-timeline')));
            $options_panel->addColor('second_post', array('name' => __('Second Color', 'cool-timeline'), 'std' =>'#f12945', 'desc' => __('', 'cool-timeline')));
            // $options_panel->addColor('third_post',array('name'=> __('Third Post','cool-timeline'),'std'=>array('#000'), 'desc' => __('','cool-timeline')));
            $options_panel->CloseTab();

			
			
            /**
             * Open admin page third tab
             */
            $options_panel->OpenTab('options_3');

            //title
            $options_panel->Title(__("Typography Settings", "cool-timeline"));
            $options_panel->addTypo('main_title_typo', array('name' => __("Main Title", "cool-timeline"), 'std' => array('size' => '22px', 'color' => '#000000', 'face' => 'arial', 'style' => 'normal'), 'desc' => __('', 'cool-timeline')));

            $options_panel->addTypo('post_title_typo', array('name' => __("Story Title", "cool-timeline"), 'std' => array('size' => '20px', 'color' => '#000000', 'face' => 'arial', 'style' => 'normal'), 'desc' => __('', 'cool-timeline')));

            $options_panel->addRadio('post_title_text_style', array('lowercase' => 'Lowercase',
                'uppercase' => 'Uppercase', 'capitalize' => 'Capitalize',
                'none' => 'None'    
                ), array('name' => __('Story Title Style ?', 'cool-timeline'), 'std' => array('capitalize'), 'desc' => __('', 'cool-timeline')));

            $options_panel->addTypo('post_content_typo', array('name' => __("Story Content", "cool-timeline"), 'std' => array('size' => '14px', 'color' => '#000000', 'face' => 'arial', 'style' => 'normal'), 'desc' => __('', 'cool-timeline')));



            $options_panel->CloseTab();

           

            /**
             * Open admin page third tab
             */
            $options_panel->OpenTab('options_4');
            $options_panel->Title(__("Stories Settings", "cool-timeline"));
           $options_panel->addText('post_type_slug', array('name' => __('Custom slug of timeline stories', 'cool-timeline'), 'std' => '', 'desc' => __('Remember to save the permalink again in settings -> Permalinks.', 'cool-timeline')));

            //An optionl descrption paragraph
            $options_panel->addParagraph(__("Animation Effects option is added in shortcode generator in Version 1.9 or Later","cool-timeline"));

            $options_panel->addRadio('stories_images', array('popup' => 'In Popup(CT Lightbox)','theme-popup' => 'In Popup(Theme Lightbox)',
                'single' => 'Story detail link','disable_links'=>'Disable links'), array('name' => __('Stories Images?', 'cool-timeline'), 'std' => array('popup'), 'desc' => __('*Choose theme lightbox if your theme supports an image lightbox.', 'cool-timeline')));

            $options_panel->addRadio('ctl_slideshow', array('true' => 'Enable',
                'false' => 'Disable'), array('name' => __('Stories Slideshow ?', 'cool-timeline'), 'std' => array('true'), 'desc' => __('', 'cool-timeline')));

        
           /* $options_panel->addRadio('slider_animation', array('slide' => 'Slide',
                'fade' => 'FadeIn'), array('name' => __('Slider animation ?', 'cool-timeline'), 'std' => array('slide'), 'desc' => __('', 'cool-timeline')));
                */
            $options_panel->addText('animation_speed', array('name' => __('Slide Show Speed(For Image Slideshow in Vertical Timeline) ?', 'cool-timeline'), 'std' => '5000', 'desc' => __('Enter the speed in milliseconds 1000 = 1 second', 'cool-timeline')));

            $options_panel->addText('default_icon', array('name' => __('Stories Default Icon', 'cool-timeline'), 'std' => '', 'desc' => __('Please add stories default  icon class from here <a target="_blank" href="http://fontawesome.io/icons">Font Awesome</a>', 'cool-timeline')));


            $options_panel->CloseTab();


            /**
             * Open admin page third tab
             */
            $options_panel->OpenTab('options_5');
            $options_panel->Title(__("Stories Date Settings", "cool-timeline"));
            $options_panel->addRadio('disable_months', array('yes' => 'Yes',
                'no' => 'no'), array('name' => __('Disable Stories Dates ?', 'cool-timeline'), 'std' => array('no'), 'desc' => __('', 'cool-timeline')));

            $options_panel->addRadio('ctl_date_formats', array('M d' => date('M d'),
                'F j, Y' => date('F j, Y'), 'Y-m-d' => date('Y-m-d'),
                'm/d/Y' => date('m/d/Y'), 'd/m/Y' => date('d/m/Y')
                    ), array('name' => __('Stories Date Formats ?', 'cool-timeline'), 'std' => array('M d'), 'desc' => __('', 'cool-timeline')));

            $options_panel->addText('custom_date_formats', array('name' => __('Custom date formats', 'cool-timeline'), 'std' => '', 'desc' => __('Stories date formats   e.g  D,M,Y <a  target="_blank" href="http://php.net/manual/en/function.date.php">Click here to view more</a>', 'cool-timeline')));

            $options_panel->addRadio('custom_date_style', array('no' => 'No(Default style)',
                'yes' => 'Yes'), array('name' => __('Enable custom date styles', 'cool-timeline'), 'std' => array('no'), 'desc' => __('', 'cool-timeline')));

            $options_panel->addTypo('ctl_date_typo', array('name' => __("Stories date Font style", "cool-timeline"), 'std' => array('size' => '22px', 'color' => '#000000', 'face' => 'arial', 'style' => 'normal'), 'desc' => __('Enable custom date styles(Above option) to apply this setting', 'cool-timeline')));
           
		   $options_panel->addRadio('custom_date_color', array('no' => 'No(Default style)',
                'yes' => 'Yes'), array('name' => __('Enable custom date Color', 'cool-timeline'), 'std' => array('no'), 'desc' => __('', 'cool-timeline')));
		   $options_panel->addColor('ctl_date_color', array('name' => __('Stories date color', 'cool-timeline'), 'std' =>'#000000', 'desc' => __('', 'cool-timeline')));



            $options_panel->CloseTab();

            /**
             * Open admin page third tab
             */
            $options_panel->OpenTab('options_7');
            $options_panel->Title(__("Timeline Scrolling Navigation settings", "cool-timeline"));
            $options_panel->addRadio('enable_navigation', array('yes' => 'Yes',
                'no' => 'no'), array('name' => __('Enable Scrolling  Navigation ?', 'cool-timeline'), 'std' => array('yes'), 'desc' => __('', 'cool-timeline')));

            $options_panel->addRadio('navigation_position', array(
                'left' => 'Left Side', 'right' => 'Right Side','bottom' => 'Bottom Fixed ',
                    ), array('name' => __('Scrolling Navigation Position ?', 'cool-timeline'), 'std' => array('right'), 'desc' => __('', 'cool-timeline')));

            $options_panel->addRadio('enable_pagination', array('yes' => 'Yes',
                'no' => 'No'), array('name' => __('Enable Pagination ?', 'cool-timeline'), 'std' => array('yes'), 'desc' => __('Pagination settings added in shortcode Generator in version 2.4', 'cool-timeline')));

            $options_panel->CloseTab();

            /**
             * Open admin page third tab
             */
            $options_panel->OpenTab('options_6');
            /**
             * Editor options:
             * WYSIWYG (tinyMCE editor)
             * Syntax code editor (css,html,js,php)
             */
            //code editor field
            $options_panel->addCode('custom_styles', array('name' =>
            __('Custom Styles', 'cool-timeline'), 'syntax' => 'css'));
         //radio field
            $options_panel->addRadio('disable_FA',
            array('no'=>'No','yes'=>'Yes'),
            array('name'=> __('Disable Font Awesome CSS?','cool-timeline'),
             'std'=> array('no'),
              'desc' => __('Remove Font Awesome icons CSS from all pages','cool-timeline'))
            );

            $options_panel->addRadio('disable_GF',
            array('no'=>'No','yes'=>'Yes'),
            array('name'=> __('Disable Google Font ?','cool-timeline'),
             'std'=> array('no'),
              'desc' => __('Remove google fonts CSS from all pages','cool-timeline'))
            );
            // Close 3rd tab
            //title
            //  $options_panel->Title(__("Editor Options","cool-timeline"));
            //wysiwyg field
           // $options_panel->addWysiwyg('no_posts', array('name' => __('No Timeline Posts content', 'cool-timeline'), 'desc' => __('', 'cool-timeline')));

            $options_panel->CloseTab();

            /**
             * Open admin page third tab
             */
            $options_panel->OpenTab('options_8');
            //An optionl descrption paragraph
            $options_panel->addParagraph('<img src="https://res.cloudinary.com/cooltimeline/image/upload/v1512558943/add-cool-timeline-shortcode.png" style="width:100%">');
            $options_panel->addParagraph('<img style="width:100%" src="https://res.cloudinary.com/cooltimeline/image/upload/v1512558943/add-category-based-timeline.png">');
            $options_panel->addParagraph('Please use below added shortcode for default timeline. <br><br>
		<code><strong>[cool-timeline layout="default" designs="default" skin="default" category="{add here category-slug}" show-posts="10" order="DESC" icons="NO" animations="bounceInUp" date-format="default" story-content="short" based="default" compact-ele-pos="main-date" pagination="default" filters="no"] </strong> </code>');

            $options_panel->addParagraph('Please use below added shortcode for multiple timeline (category based timeline). <br> <br> <code><strong>[cool-timeline layout="default" designs="default" skin="default" category="{add here category-slug}" show-posts="10" order="DESC" icons="NO" animations="bounceInUp" date-format="default" story-content="short" based="default" compact-ele-pos="main-date" pagination="default" filters="no"] </strong></code>');

          $options_panel->addParagraph('Horizontal Timeline. <br><br>
		<code><strong>[cool-timeline layout="horizontal" category="{add here category-slug}" skin="default" designs="default" show-posts="20" order="DESC" items="" icons="NO" story-content="short" date-format="default" based="default" autoplay="false" start-on="0"]</strong> </code>');

            $options_panel->addParagraph('Vertical Content Timeline(any post type). <br><br>
		<code><strong>[cool-content-timeline post-type="post" post-category="" tags="" story-content="short" taxonomy="category" layout="default" designs="default" skin="default" show-posts="10" order="DESC" icons="NO" animations="bounceInUp" date-format="default" pagination="default" filters="no"]</strong> </code>');

            $options_panel->addParagraph('Horizontal Content Timeline(any post type). <br><br>
        <code><strong>[cool-content-timeline post-type="post" post-category="" tags="" autoplay="false" story-content="short" taxonomy="category" layout="horizontal" designs="default" skin="default" show-posts="10" order="DESC" start-on="0" icons="NO" items="" date-format="default"]</strong> </code>');
            $options_panel->CloseTab();

           /**
             * Open admin page third tab
             */
            $options_panel->OpenTab('options_11');
            $options_panel->addRadio('post_meta', array(
                'yes' => 'Yes', 'no' => 'No'
                    ), array('name' => __('Display Post Meta (Categries,Tags) ?', 'cool-timeline'), 'std' => array('yes'), 'desc' => __('', 'cool-timeline')));

            $options_panel->CloseTab();
            /**
             * Open admin page 7th tab
             */
            $options_panel->OpenTab('options_10');
             $options_panel->Title(__("Story Migrations","cool-timeline"));
              $options_panel->content_migration();

            $options_panel->CloseTab();
            $options_panel->CloseTab();

        }