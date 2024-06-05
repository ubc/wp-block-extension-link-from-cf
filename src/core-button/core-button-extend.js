const { createHigherOrderComponent } = wp.compose;
const { Fragment, useEffect, useState, useRef } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl, PanelRow, TextControl, SelectControl } = wp.components;
const { isURL } = wp.url;

function addAdditionalAttribute( settings, name ) {
    if ( 'core/button' !== name ) {
        return settings;
    }

    return {
        ...settings,
        attributes: {
            ...settings.attributes,
            linkToPost: {
                type: 'boolean',
                default: false
            },
            enableCF: {
                type: 'boolean',
                default: false
            },
            CFKey: {
                type: 'string',
                default: ''
            },
            openInNewTab: {
                type: 'boolean',
                default: false
            },
        },
        usesContext: [ 'postId' ]
    }
}

wp.hooks.addFilter(
    'blocks.registerBlockType',
    'ubc/extension/link-from-cf/button/add-attributes',
    addAdditionalAttribute
);

/**
 * Add additional controls to core/post-template block.
 */
const withInspectorControls = createHigherOrderComponent( ( BlockEdit ) => {

    return ( props ) => {
        const { name, attributes, setAttributes } = props;
        const { enableCF, CFKey, linkToPost, openInNewTab } = attributes;
        const [ metaKeys, setMetaKeys ] = useState([]);
    
        if( 'core/button' !== name ) {
            return <BlockEdit { ...props } />;
        }

        useEffect(() => {
            const metaKeys = async() => {
    
                const data = new FormData();
    
                data.append( 'action', 'wp_link_from_cf_get_meta_keys' );
                data.append( 'nonce', wp_link_from_cf.nonce );
            
                const response = await fetch( ajaxurl, {
                  method: "POST",
                  credentials: 'same-origin',
                  body: data
                } );
                const responseJson = await response.json();
                
                if( responseJson.success ) {
                    setMetaKeys( responseJson.data );

                    if ( '' === CFKey ) {
                        setAttributes({
                            CFKey: responseJson.data[0]
                        });
                    }
                }
            };
    
            metaKeys();
        }, []);

        return (
            <Fragment>
                <BlockEdit { ...props } />
                <InspectorControls>
                    <PanelBody title="Link Settings" initialOpen={ true }>
                     <ToggleControl
                            label="Enable link to post"
                            checked={ linkToPost }
                            onChange={ () => {
                                setAttributes({
                                    linkToPost: ! linkToPost,
                                    enableCF: linkToPost ? enableCF : false
                                });
                            } }
                        />
                        <ToggleControl
                            label="Enable link to custom field"
                            checked={ enableCF }
                            onChange={ () => {
                                setAttributes({
                                    enableCF: ! enableCF,
                                    linkToPost: enableCF ? linkToPost : false
                                });
                            } }
                        />
                        { enableCF ? <SelectControl
                            label="Link to Custom Field"
                            value={ CFKey }
                            options={ metaKeys.map(key => {
                                return {
                                    label: key,
                                    value: key
                                };
                            }) }
                            onChange={ ( newMetaKey ) => {
                                setAttributes({
                                    CFKey: newMetaKey
                                });
                            } }
                            __nextHasNoMarginBottom
                        />
                        : '' }
                        { linkToPost || enableCF ? 
                            <ToggleControl
                                label="Open in new tab"
                                checked={ openInNewTab }
                                onChange={ () => {
                                    setAttributes({
                                        openInNewTab: ! openInNewTab,
                                    });
                                } }
                            /> : null
                        }
                        
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}, 'withInspectorControl' );

wp.hooks.addFilter(
    'editor.BlockEdit',
    'ubc/extension/link-from-cf/button/add-controls',
    withInspectorControls
);