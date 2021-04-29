/**
 * Block dependencies
 */

import classnames from 'classnames';
import CtlIcon from './icons';
import CtlLayoutType from './layout-type'

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const baseURL=ctlUrl.CTP_PLUGIN_URL;
const wpBaseURL=ctlUrl.baseURL;
const LayoutImgPath=baseURL+'/includes/gutenberg-block/layout-images';
const { apiFetch } = wp;
const {
	RichText,
	InspectorControls,
	BlockControls,
} = wp.editor;

const { 
	PanelBody,
	PanelRow,
	TextareaControl,
	TextControl,
	Dashicon,
	Toolbar,
	Button,
	SelectControl,
	Tooltip,
	RangeControl,
} = wp.components;
/**
 * Register block

 */
export default registerBlockType( 'cool-content-timeline/ctl-shortcode-block', {
		// Block Title
		title: __( 'Content Timeline Shortcode' ),
		// Block Description
		description: __( 'Cool Content Timeline Shortcode Generator Block.' ),
		// Block Category
		category: 'common',
		// Block Icon
		icon:CtlIcon,
		// Block Keywords
		keywords: [
			__( 'Cool Content timeline' ),
			__( 'Content timeline shortcode' ),
			__( 'Cool Content timeline block' )
		],
	attributes: {
		layout: {
			type: 'string',
			default: 'default'
		},
		skin: {
			type: 'string',
			default: 'default'
		},
		postperpage: {
            type: 'string',
            default:10
        },
		dateformat: {
			type: 'string',
			default:  'F j'
		},
		icons: {
			type: 'string',
			default:  'NO'
		},
		animation: {
			type: 'string',
			default:  'none'
		},
		designs:{
			type: 'string',
			default:  'default'
		},
		storycontent:{
			type: 'string',
			default:  'short'
		},
		compactelepos:{
			type: 'string',
			default:  'main-date'
		},
		pagination:{
			type: 'string',
			default:  'default'
		},
		filters:{
			type: 'string',
			default:  'no'
		},
		filtercategories:{
			type: 'string',
			default:''
		},
		items:{
			type: 'string',
			default:  ''
		},
		autoplay:{
			type: 'string',
			default:  'false'
		},
		starton:{
			type: 'string',
			default: 0
		},
		order:{
			type: 'string',
			default:'DESC'
		},

		// content tm specific fields
		posttype:{
			type: 'string',
			default:'post'
		},
		taxonomy:{
			type: 'string',
			default:'category'
		},
		postcategory:{
			type: 'string',
			default:''
		},
		tags:{
			type: 'string',
			default:''
		}
	},
	// Defining the edit interface
	edit: props => {
		const skinOptions = [
            { value: 'default', label: __( 'Default' ) },
			{ value: 'dark', label: __( 'Dark' ) },
			{ value: 'light', label: __( 'Light' ) }
		];
		const iconOptions = [
            { value: 'NO', label: __( 'NO' ) },
            { value: 'YES', label: __( 'YES' ) }
		];
		const DfromatOptions = [
		 {value:"F j",label:"January 1 (F j)"},
		 {value:"F j Y",label:"January 1 2019 (F j Y)"},
		 {value:"Y-m-d",label:"2019-01-01 (Y-m-d)"},
		 {value:"m/d/Y",label:"01/01/2019 (m/d/Y)"},
		 {value:"d/m/Y",label:"01/01/2019 (d/m/Y)"},
		 {value:"F j Y g:i A",label:"January 1 2019 11:10 AM (F j Y g:i A)"},
		 {value:"Y",label:" 2019(Y)"},
		 {label:"Custom",value:"custom"}
		  ];
		
		const layoutOptions = [
            { value: 'default', label: __( 'Vertical' ) },
			{ value: 'horizontal', label: __( 'Horizontal' ) },
			{ value: 'one-side', label: __( 'One Side Layout' ) },
			{ value: 'compact', label: __( 'Compact Layout' ) }
		];
		const animationOptions=[
			{label:"None",value:"none"},
			{label:"fade",value:"fade"},
		{label:"zoom-in",value:"zoom-in"},
		{label:"flip-right",value:"flip-right"},
		{label:"zoom-out",value:"zoom-out"},
		{label:"fade-up",value:"fade-up"},
		{label:"fade-down",value:"fade-down"},
		{label:"fade-left",value:"fade-left"},
		{label:"fade-right",value:"fade-right"},
		{label:"fade-up-right",value:"fade-up-right"},
		{label:"fade-up-left",value:"fade-up-left"},
		{label:"fade-down-right",value:"fade-down-right"},
		{label:"fade-down-left",value:"fade-down-left"},
		{label:"flip-up",value:"flip-up"},
		{label:"flip-down",value:"flip-down"},
		{label:"flip-left",value:"flip-left"},
		{label:"slide-up",value:"slide-up"},
		{label:"slide-left",value:"slide-left"},
		{label:"slide-right",value:"slide-right"},
		{label:"zoom-in-up",value:"zoom-in-up"},
		{label:"zoom-in-down",value:"zoom-in-down"},
		{label:"slide-down",value:"slide-down"},
		{label:"zoom-in-left",value:"zoom-in-left"},
		{label:"zoom-in-right",value:"zoom-in-right"},
		{label:"zoom-out-up",value:"zoom-out-up"},
		{label:"zoom-out-down",value:"zoom-out-down"},
		{label:"zoom-out-left",value:"zoom-out-left"},
		{label:"zoom-out-right",value:"zoom-out-right"},
		
	];
			const timelineDesigns=[
			{label:"Default",value:"default"},
			{label:"Flat Design",value:"design-2"},
			{label:"Classic Design",value:"design-3"},
			{label:"Elegant Design",value:"design-4"},
			{label:"Clean Design",value:"design-5"},
			{label:"Modern Design",value:"design-6"}
			];
			
			const compact_ele_pos=[
			{label:"On top date/label below title",value:"main-date"},
			{label:"On top title below date/label",value:"main-title"}
			];
			const contentSettings=[{label:"Summary",value:"short"},
			{label:"Full Text",value:"full"}
			];
			const iconsOptions=[{label:"NO",value:"NO"},
			{label:"YES",value:"YES"}];
		
			const timeline_based_on=[{label:"Default(Date Based)",value:"default"},
			{label:"Custom Order",value:"custom"}
			];
			const paginationOptions=[{label:"Default",value:"default"},
			{label:"Ajax Load More",value:"ajax_load_more"}
			];
			const filtersOptions=[{label:"No",value:"no"},
			{label:"Yes",value:"yes"}];		
			const multiItems=[
				{label:"Select items",value:""},
				{label:"1",value:"1"},
				{label:"2",value:"2"},
				{label:"3",value:"3"},
				{label:"4",value:"4"},
				] ;
			const autoplayOptions=[
				{label:"False",value:"false"},
				{label:"True",value:"true"}
			];
		
		return [
			
			!! props.isSelected && (
				<InspectorControls key="inspector">
						<PanelBody title={ __( 'Content Timeline Settings' ) } >
						<TextControl
							label={ __( 'Content Post Type' ) }
							value={ props.attributes.posttype }
							onChange={ ( value ) =>props.setAttributes( { posttype: value } ) }
						/>
						<p>Don't Change This If You Are Creating Blog Posts Timeline or Define Content Type Of Your Timeline Like:- Posts</p>

						<TextControl
							label={ __( 'Taxonomy Name' ) }
							value={ props.attributes.taxonomy }
							onChange={ ( value ) =>props.setAttributes( { taxonomy: value } ) }
						/>
						<p>Don't Change This If You Are Creating Blog Posts Timeline or Define Content Type Taxonomy.</p>	
						<TextControl
							label={ __( 'Specific category(s) (Add category(s) slug - comma separated)' ) }
							value={ props.attributes.postcategory }
							onChange={ ( value ) =>props.setAttributes( { postcategory: value } ) }
						/>	
						<p>Show Category Specific Blog Posts. Like For cooltimeline.com/category/fb-history/ it will be fb-history</p>
						<TextControl
							label={ __( 'Specific tags(add tags slug)' ) }
							value={ props.attributes.tags }
							onChange={ ( value ) =>props.setAttributes( { tags: value } ) }
						/>		
						<p>Show Tag Specific Blog Posts. Like For cooltimeline.com/tag/fb-history/ it will be fb-history.</p>
					<SelectControl
                        label={ __( 'Layout' ) }
                        options={ layoutOptions }
                        value={ props.attributes.layout }
						onChange={ ( value ) =>props.setAttributes( { layout: value } ) }
						/>
							<p>Select your timeline layout</p>
						<SelectControl
                        label={ __( 'Designs' ) }
                        options={ timelineDesigns }
                        value={ props.attributes.designs }
						onChange={ ( value ) =>props.setAttributes( { designs: value } ) }
						/>
						<p>
						Choose Timeline Designs (Check Vertical Designs and Horizontal Designs )
                       <br></br>	<a target="_blank" href="http://www.cooltimeline.com/cool-timeline-pro-vertical-designs">Vertical Timeline demos</a>
                          |   <a target="_blank" href="http://www.cooltimeline.com/horizontal-timeline-designs-demos">Horizontal Timeline demos</a>
						</p>
						<SelectControl
                        label={ __( 'Skin' ) }
                        options={ skinOptions }
                        value={ props.attributes.skin }
						onChange={ ( value ) =>props.setAttributes( { skin: value } ) }
                    	/>
						<p>Create Light, Dark or Colorful Timeline</p>
					<SelectControl
                        label={ __( 'Date Formats' ) }
                        description={ __( 'yes/no' ) }
                        options={ DfromatOptions }
                        value={ props.attributes.dateformat }
						onChange={ ( value ) =>props.setAttributes( { dateformat: value } ) }
                    	/>
				<p>Timeline Stories dates custom formats</p>
					{ props.attributes.layout!="horizontal" &&  
					<section>
							<SelectControl
						label={ __( 'Pagination Settings' ) }
						options={ paginationOptions }
						value={ props.attributes.pagination }
						onChange={ ( value ) =>props.setAttributes( { pagination: value } ) }
						/>
						<SelectControl
						label={ __( 'Category Filters Settings' ) }
						options={ filtersOptions }
						value={ props.attributes.filters }
						onChange={ ( value ) =>props.setAttributes( { filters: value } ) }
						/>			
						<p>Note:-Please add value in Taxonomy field before using it.</p>	
						<TextControl
							label={ __( 'Add categories slug for filters eg(stories,our-history)' ) }
							value={ props.attributes.filtercategories }
							onChange={ ( value ) =>props.setAttributes( { filtercategories: value } ) }
						/>	

						<SelectControl
                        label={ __( 'Icons' ) }
                        description={ __( 'yes/no' ) }
                        options={ iconOptions }
                        value={ props.attributes.icons }
						onChange={ ( value ) => props.setAttributes( { icons: value } ) }
                    	/>
						<p>Display Icons In Timeline Stories. By default Is Dot.</p>
						<SelectControl
                        label={ __( 'Animation' ) }
                        description={ __( 'yes/no' ) }
                        options={ animationOptions }
                        value={ props.attributes.animation }
						onChange={ ( value ) =>props.setAttributes( { animation: value } ) }
                    	/>
						<p>Add Animations Effect Inside Timeline. You Can Check Effects Demo From <a  target='_blank' href='http://michalsnik.github.io/aos/'>AOS</a>.</p>
						{ props.attributes.layout=="compact" &&  
						<SelectControl
                        label={ __( 'Compact Layout Date&Title positon' ) }
                        description={ __( 'yes/no' ) }
                        options={ compact_ele_pos }
                        value={ props.attributes.compactelepos }
						onChange={ ( value ) =>props.setAttributes( { compactelepos: value } ) }
                    	/>
						}
					</section>
					}
					{ props.attributes.layout=="horizontal" &&  
					<section>
					<SelectControl
                        label={ __( 'Display Stories?' ) }
                        options={ multiItems }
                        value={ props.attributes.items }
						onChange={ ( value ) =>props.setAttributes( { items: value } ) }
						/>
						<SelectControl
                        label={ __( 'Autoplay Settings?' ) }
                        options={ autoplayOptions }
                        value={ props.attributes.autoplay }
						onChange={ ( value ) =>props.setAttributes( { autoplay: value } ) }
						/>			
						<TextControl
							label={ __( 'Timeline Starting from Story e.g(2)' ) }
							value={ props.attributes.starton }
							onChange={ ( value ) =>props.setAttributes( { starton: value } ) }
						/>	
					</section>
					}
						<TextControl
							label={ __( 'Posts Per Page' ) }
							value={ props.attributes.postperpage }
							onChange={ ( value ) =>props.setAttributes( { postperpage: value } ) }
						/>
						<p>You Can Show Pagination After These Posts In Vertical Timeline.</p>
					  <SelectControl
                        label={ __( 'Stories Description?' ) }
                        options={ contentSettings }
                        value={ props.attributes.storycontent }
						onChange={ ( value ) =>props.setAttributes( { storycontent: value } ) }
						/>	
					  <SelectControl
                        label={ __( 'Stories Order?' ) }
						options={ [{label:"DESC",value:"DESC"},
								{label:"ASC",value:"ASC"}
							] }
                        value={ props.attributes.order }
						onChange={ ( value ) =>props.setAttributes( { order: value } ) }
						/>	
							<p>Timeline Stories order like:- DESC(2017-1900) , ASC(1900-2017)</p>
					</PanelBody>
				</InspectorControls>
			),
			<div className={ props.className }>			
				<CtlLayoutType  type="contenttm" LayoutImgPath={LayoutImgPath} attributes={props.attributes} />
				<div className="ctl-block-shortcode">
				{ props.attributes.layout=="horizontal" &&  
				<div>
				[cool-content-timeline 
						post-type="{props.attributes.posttype}"
						post-category="{props.attributes.postcategory}"
						taxonomy="{props.attributes.taxonomy}"
						tags="{props.attributes.tags}"

						layout="{props.attributes.layout}" 
						designs="{props.attributes.designs}"
						skin="{props.attributes.skin}"
						show-posts="{props.attributes.postperpage}"
						date-formats="{ props.attributes.dateformat}"
						icons="{props.attributes.icons}"
						story-content="{props.attributes.storycontent}"
						based="{props.attributes.based}"
						items="{props.attributes.items}"
						start-on="{props.attributes.starton}"
						autoplay="{props.attributes.autoplay}"
						order="{props.attributes.order}"
					  ]
				</div>
				}

			{ props.attributes.layout!="horizontal" &&  
				<div>
				[cool-content-timeline 
					post-type="{props.attributes.posttype}"
					post-category="{props.attributes.postcategory}"
					taxonomy="{props.attributes.taxonomy}"
					tags="{props.attributes.tags}"

					layout="{props.attributes.layout}" 
					designs="{props.attributes.designs}"
					skin="{props.attributes.skin}"
					show-posts="{props.attributes.postperpage}"
					date-formats="{ props.attributes.dateformat}"
					icons="{props.attributes.icons}"
					animation="{props.attributes.animation}"
					story-content="{props.attributes.storycontent}"
					based="{props.attributes.based}"
					compact-ele-pos="{props.attributes.compactelepos}"
					pagination="{props.attributes.pagination}"
					filters="{props.attributes.filters}"
					filter-categories="{props.attributes.filtercategories}"
					order="{props.attributes.order}"
				]
				</div>
			}
				

				</div>
			</div>
		];
	},
	// Defining the front-end interface
	save() {
		// Rendering in PHP
		return null;
	},
});
