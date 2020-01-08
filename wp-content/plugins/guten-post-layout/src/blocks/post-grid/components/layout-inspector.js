// Internationalization
const { __ } = wp.i18n;

// Extend component
const { Component, Fragment } = wp.element;

// import Inspector components
const { QueryControls, PanelBody,  RangeControl, ToggleControl, SelectControl, TextControl, Button, ButtonGroup, Tooltip } = wp.components;

const { InspectorControls, AlignmentToolbar, BlockControls, URLInput} = wp.blockEditor;
export default class LayoutInspector extends Component{
    constructor(props){
        super(props);
    }


    render(){

        const { attributes, categoriesList, setAttributes, latestPosts, className, postTypes, media, authors} = this.props;
        const { post_type, categories, order, orderBy, postscount, columns, postLayout, displayPostImage, displayPostDate, displayPostAuthor, displayPostExcerpt, displayPostReadMoreButton, postReadMoreButtonText, align, postImageSizes, carouselLayoutStyle, gridLayoutStyle, postBlockWidth, slidesToShow, autoPlay, navigation, columnGap, linkTarget, equalHeight, imageHeight,

            // cta attrs
            postCtaButtonAlign,
            displayPostCtaButton,
            postCtaButtonText,
            CtaLinkTarget,
            postCtaButtonLink,
            postCtaButtonStyle,
            displayCtaButtonIcon
        } = attributes;

        const orderByOptions = [
            { value: 'date', label: __( 'Date' ) },
            { value: 'title', label: __( 'Title' ) },
            { value: 'menu_order', label: __( 'Menu Order' ) },
            { value: 'rand', label: __( 'Random' ) },
        ];

        const orderOptions = [
            { value: 'asc', label: __( 'Ascending' ) },
            { value: 'desc', label: __( 'Descending' ) },
        ];

        const postImageDefaultSizes = [
            { value: 'full', label: __( 'Full' ) },
            { value: 'guten_post_layout_landscape_large', label: __( 'Landscape Large' ) },
            { value: 'guten_post_layout_portrait_large', label: __( 'Portrait Large' ) },
            { value: 'guten_post_layout_square_large', label: __( 'Square Large' ) },
            { value: 'guten_post_layout_landscape', label: __( 'Landscape Small' ) },
            { value: 'guten_post_layout_portrait', label: __( 'Portrait Small' ) },
            { value: 'guten_post_layout_square', label: __( 'Square Small' ) },
            { value: 'thumbnail', label: __( 'Thumbnail' ) },
        ];

        const defaultCarouselLayoutStyles =  [
            { label: __('Select Your Layout'), value: 'skin_empty'  },
            { label: __('Skin 1'), value: 'skin1'  },
            { label: __('Skin 2'), value: 'skin2'  },
            { label: __('Skin 3'), value: 'skin3'  },
        ];

        const defaultGridLayoutStyles =  [
            { label: __('Select Your Layout'), value: 'g_skin_empty'},
            { label: __('Skin 1'), value: 'g_skin1'  },
            { label: __('Skin 2'), value: 'g_skin2'  },
            { label: __('Skin 3'), value: 'g_skin3'  },
        ];

        const defaultNavigationsStyle =  [
            { label: __('Dots'), value: 'dots'  },
            { label: __('Arrows'), value: 'arrows'  },
            { label: __('None'), value: 'none'  },
        ];

        return(
            <Fragment>
                <PanelBody title={ __( 'Layout Settings' ) } initialOpen={ false }>

                    { postLayout === 'grid' &&
                    <RangeControl
                        label={__('Number of columns')}
                        value={columns}
                        onChange={(value) => setAttributes({columns: value})}
                        min={1}
                        max={6}
                    />
                    }

                    <RangeControl
                        label = { __('Column & Row Gaps' ) }
                        value = { columnGap }
                        min = { 0.01 }
                        max = { 21 }
                        onChange = { ( value ) => setAttributes({ columnGap: value }) }
                    />

                    {postLayout === 'slides' &&
                    <SelectControl
                        label={__('Carousel Skin')}
                        options={defaultCarouselLayoutStyles}
                        value={carouselLayoutStyle}
                        onChange={(newValue) => {
                            setAttributes({carouselLayoutStyle: newValue, gridLayoutStyle : null })
                        }}
                    />
                    }
                    { (postLayout === 'grid' || postLayout === 'list') &&
                    <SelectControl
                        label={__('Grid Skin')}
                        options={defaultGridLayoutStyles}
                        value={gridLayoutStyle}
                        onChange={(newValue) => {
                            setAttributes({gridLayoutStyle: newValue, carouselLayoutStyle: null })
                        }}
                    />
                    }
                    {postLayout === 'slides' &&
                    <RangeControl
                        label={__('Slides To Show')}
                        value={slidesToShow}
                        min={1}
                        max={3}
                        onChange={(value) => setAttributes({slidesToShow: value})}
                    />
                    }
                    {postLayout === 'slides' &&
                    <ToggleControl
                        label={__('Autoplay')}
                        checked={!!autoPlay}
                        onChange={(value) => setAttributes({autoPlay: value})}
                    />
                    }
                    {postLayout === 'slides' &&
                    <SelectControl
                        label={__('Navigation')}
                        options={defaultNavigationsStyle}
                        value={navigation}
                        onChange={(newValue) => {
                            setAttributes({navigation: newValue})
                        }}
                    />
                    }

                    {(gridLayoutStyle === 'g_skin3') &&
                    <ToggleControl
                        label={__('Equal Height')}
                        checked={!!equalHeight}
                        onChange={(value) => setAttributes({equalHeight: value})}
                        initialPosition={1}
                    />
                    }

                    <RangeControl
                        label={__('Image Height')}
                        value={imageHeight}
                        min={100}
                        max={2000}
                        onChange={(value) => setAttributes({imageHeight: value})}
                    />

                </PanelBody>

                <PanelBody title={ __( 'Query Settings' ) }>
                    <SelectControl
                        label = { __( 'Post Types' ) }
                        options={ postTypes && postTypes.map(({ slug, name }) => ( { value: slug, label:name})) }
                        value={ post_type}
                        onChange={(newValue) => { setAttributes({
                            post_type: newValue,
                            categories: ''
                        }) }}
                    />

                    <QueryControls
                        numberOfItems={postscount}
                        categoriesList={ categoriesList ? categoriesList : [] }
                        selectedCategoryId = {categories}
                        onCategoryChange={ ( value ) => setAttributes( {
                            categories: '' !== value ? value : undefined
                        }) }
                        onNumberOfItemsChange={ (value) => setAttributes({ postscount: value }) }
                    />

                    <div className={'gpl-select-panel gpl-mb-10'}>
                        <span className={'gpl-pb-5'}>{ __('Order By') }</span>
                        <SelectControl
                            options={orderByOptions}
                            value={orderBy}
                            onChange={(value) => {setAttributes({  orderBy: '' !== value ? value : 'date' })
                            }}
                        />
                    </div>

                    <div className={'gpl-select-panel gpl-mb-10'}>
                        <span className={'gpl-pb-5'}>{ __('Order') }</span>
                        <SelectControl
                            options={orderOptions}
                            value={order}
                            onChange={(value) => { setAttributes({  order: '' !== value ? value : 'desc' })
                            }}
                        />
                    </div>
                </PanelBody>


                <PanelBody title={ __( 'Additional Settings' ) } initialOpen={ false }>

                    <ToggleControl
                        label = { __('Display Featured Image') }
                        checked = { !!displayPostImage }
                        onChange = { (value) => setAttributes( { displayPostImage: value } ) }
                    />


                    { displayPostImage &&
                    <SelectControl
                        label={__('Image Size')}
                        options={postImageDefaultSizes}
                        value={postImageSizes}
                        onChange={(newValue) => {setAttributes({ postImageSizes: newValue})
                        }}
                    />
                    }
                    {
                        ( (carouselLayoutStyle === 'skin2' || carouselLayoutStyle === 'skin3' ) || (gridLayoutStyle === 'g_skin1' || gridLayoutStyle === 'g_skin3' )) &&
                        <ToggleControl
                            label={__('Display Post Author')}
                            checked={!!displayPostAuthor}
                            onChange={(value) => setAttributes({displayPostAuthor: value})}
                        />
                    }

                    {
                        (( carouselLayoutStyle === 'skin2' || carouselLayoutStyle === 'skin3' ) || ( gridLayoutStyle === 'g_skin1' || gridLayoutStyle === 'g_skin2' || gridLayoutStyle === 'g_skin3') ) &&
                        <ToggleControl
                            label={__('Display Post Date')}
                            checked={!!displayPostDate}
                            onChange={(value) => setAttributes({displayPostDate: value})}
                        />

                    }

                    {
                        (gridLayoutStyle !== 'g_skin1' && gridLayoutStyle !== 'g_skin2') &&
                        <ToggleControl
                            label={__('Display Post Excerpt')}
                            checked={!!displayPostExcerpt}
                            onChange={(value) => setAttributes({displayPostExcerpt: value})}
                        />

                    }

                    {
                        ( (carouselLayoutStyle === 'skin1' || carouselLayoutStyle === 'skin2' || carouselLayoutStyle === 'skin3' ) || (gridLayoutStyle === 'g_skin3')) &&
                        <ToggleControl
                            label={__('Display Post Read More Button')}
                            checked={!!displayPostReadMoreButton}
                            onChange={(value) => setAttributes({displayPostReadMoreButton: value})}
                        />
                    }

                    {
                        ( (carouselLayoutStyle === 'skin1' || carouselLayoutStyle === 'skin2' || carouselLayoutStyle === 'skin3') || (gridLayoutStyle === 'g_skin3')) &&
                        <TextControl
                            label={__('Read More Button Text')}
                            type="text"
                            value={postReadMoreButtonText}
                            onChange={(value) => setAttributes({postReadMoreButtonText: value})}
                        />
                    }
                    {
                        displayPostReadMoreButton &&
                        <ToggleControl
                            label = { __('Open Links in New Tab?') }
                            checked = { !!linkTarget }
                            onChange = { (value) => setAttributes( { linkTarget: value } ) }
                        />
                    }
                </PanelBody>
				{
					postLayout !== 'slides' &&
					<PanelBody title={ __( 'CTA Settings' ) } initialOpen={ false }>
						{
							<ToggleControl
								label={ __( 'Display CTA Button' ) }
								checked={ !! displayPostCtaButton }
								onChange={ ( value ) => setAttributes( { displayPostCtaButton: value } ) }
							/>
						}

						{
							displayPostCtaButton &&
							<div className={ 'gpl-input-panel gpl-mb-10' }>
								<span className={ 'gpl-pb-5' }>{ __( 'CTA Button URL' ) }</span>
								<URLInput
									className="box-top guten-post-layout-flex-1"
									value={ postCtaButtonLink }
									onChange={ ( value ) => {
										setAttributes( { postCtaButtonLink: value } )
									} }
								/>
							</div>
						}

						{ displayPostCtaButton &&
						<TextControl
							label={ __( 'CTA Button Text' ) }
							type="text"
							value={ postCtaButtonText }
							onChange={ ( value ) => setAttributes( { postCtaButtonText: value } ) }
						/>
						}
						{
							displayPostCtaButton &&
							<ToggleControl
								label={ __( 'Open Links in New Tab?' ) }
								checked={ !! CtaLinkTarget }
								onChange={ ( value ) => setAttributes( { CtaLinkTarget: value } ) }
							/>
						}
						{
							displayPostCtaButton &&
							<ToggleControl
								label={ __( 'Active Button View?' ) }
								checked={ !! postCtaButtonStyle }
								onChange={ ( value ) => setAttributes( { postCtaButtonStyle: value } ) }
							/>
						}
						{
							displayPostCtaButton &&
							<ToggleControl
								label={ __( 'Display CTA Button Icon' ) }
								checked={ !! displayCtaButtonIcon }
								onChange={ ( value ) => setAttributes( { displayCtaButtonIcon: value } ) }
							/>
						}

						{
							displayPostCtaButton &&
							<div className="alignment gpl-mb-20">
								<p className="title">{ __( 'Button Align' ) }</p>
								<AlignmentToolbar
									value={ postCtaButtonAlign }
									onChange={ value => setAttributes( { postCtaButtonAlign: value } ) }
								/>
							</div>
						}

					</PanelBody>
				}

            </Fragment>
        );
    }

}
