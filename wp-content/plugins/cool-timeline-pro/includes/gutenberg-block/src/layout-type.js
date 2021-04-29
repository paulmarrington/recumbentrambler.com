const CtlLayoutType=(props)=>{
	if(!props.attributes.layout){
		return null;
	}
	
	if(props.attributes.layout=="horizontal"){
		const horizontal_img=props.LayoutImgPath+"/cool-horizontal-timeline.jpg";
		const divStyle = {
			color: 'white',
			backgroundImage: 'url(' + horizontal_img + ')',
			height:'300px',
			width:'100%'
		  };
		return <div style={divStyle} className="ctl-block-image">
			
				<ul>
			
				
				{ props.type=="contenttm" &&  
				<section>
				<li><strong>Content Horizontal Timeline</strong></li>	
				<li><strong>Post Type:</strong> {props.attributes.posttype}</li>
				<li><strong>Post Taxonomy:</strong> {props.attributes.taxonomy}</li>
				<li><strong>Post Categories:</strong> {
							!!(props.attributes.postcategory) ?(
								props.attributes.postcategory
							):'All'
							}</li>
				<li><strong>Post Tags:</strong> {
							!!(props.attributes.tags) ?(
								props.attributes.tags
							):'All'
							}</li>
				</section>
				}
				{ props.type=="storytm" &&  
				<section>
						<li><strong>Story Horizontal Timeline</strong></li>
						<li><strong>category:</strong> {
							!!(props.attributes.category) ?(
								props.attributes.category
							):'All'
							}</li>
							
				</section>
				
				}
				<li><strong>Layout:</strong> {props.attributes.layout}</li>
				<li><strong>Designs:</strong> <DesignType type={props.attributes.designs}></DesignType></li>
				<li><strong>Skin:</strong> {props.attributes.skin}</li>
				<li><strong>Date Format:</strong> {props.attributes.dateformat}</li>
				</ul>
		</div>;
	}else {
		const vertical_img=props.LayoutImgPath+"/cool-vertical-timeline.jpg";
		const divStylev = {
			color: 'white',
			backgroundImage: 'url(' + vertical_img + ')',
			height:'300px',
			width:'100%'
		  };
		return <div style={divStylev} className="ctl-block-image">
				<ul>
			
			
				{ props.type=="contenttm" && 
					<section>
				<li><strong>Content Vertical Timeline</strong></li>
				<li><strong>Post Type:</strong> {props.attributes.posttype}</li>
				<li><strong>Post Taxonomy:</strong> {props.attributes.taxonomy}</li>
				<li><strong>Post Categories:</strong> {
							!!(props.attributes.postcategory) ?(
								props.attributes.postcategory
							):'All'
							}</li>
				<li><strong>Post Tags:</strong> {
							!!(props.attributes.tags) ?(
								props.attributes.tags
							):'All'
							}</li>
				</section>
			}
				{ props.type=="storytm" &&  
				<section>
					<li><strong>Story Vertical Timeline</strong></li>
					
					<li><strong>category:</strong> {
							!!(props.attributes.category) ?(
								props.attributes.category
							):'All'
							}</li>
				</section>
				}
				<li><strong>Layout:</strong> {props.attributes.layout}</li>
				<li><strong>Designs:</strong> <DesignType type={props.attributes.designs}></DesignType></li>
				<li><strong>Skin:</strong> {props.attributes.skin}</li>
				<li><strong>Date Format:</strong> {props.attributes.dateformat}</li>
				</ul>
		</div>;
	}	
}

const DesignType= (props)=>{
	console.log(props.type);
	switch(props.type) {
		case  'design-2':
		return "Flat Design";
		  break;
		case  'design-3':
		return "Classic Design";
		break;
		case  'design-4':
		return "Elegant Design";
		break;
		case  'design-5':
		return "Clean Design";
		break;
		case 'design-6':
		return "Modern Design";
		break;
		case 'design-7':
		return "Minimal Design";
		break;
		default:
		return 	"Default"
	  }

	
}

export default CtlLayoutType;