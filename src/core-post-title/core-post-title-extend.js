const { createHigherOrderComponent } = wp.compose;
const { Fragment, useEffect, useState, useRef } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl, PanelRow, TextControl, SelectControl } = wp.components;
const { isURL } = wp.url;

function addAdditionalAttribute( settings, name ) {
    if ( 'core/post-title' !== name ) {
        return settings;
    }

    return {
        ...settings,
        attributes: {
            ...settings.attributes,
            enableCF: {
                type: 'boolean',
                default: false
            },
            CFKey: {
                type: 'string',
                default: ''
            }
        }
    }
}

wp.hooks.addFilter(
    'blocks.registerBlockType',
    'ubc/extension/link-from-cf/post-title/add-attributes',
    addAdditionalAttribute
);

/**
 * Add additional controls to core/post-template block.
 */
const withInspectorControls = createHigherOrderComponent( ( BlockEdit ) => {

    return ( props ) => {
        const { name, attributes, setAttributes } = props;
        const { enableCF, CFKey, isLink } = attributes;
        const [ metaKeys, setMetaKeys ] = useState([]);
    
        if( 'core/post-title' !== name ) {
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
                }
            };
    
            metaKeys();
        }, []);

        return (
            <Fragment>
                <BlockEdit { ...props } />
                <InspectorControls>
                    { isLink ? <PanelBody title="Link Settings" initialOpen={ true }>
                        <ToggleControl
                            label="Enable link to custom field"
                            checked={ enableCF }
                            onChange={ () => {
                                setAttributes({
                                    enableCF: ! enableCF
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
                    </PanelBody> : '' }
                </InspectorControls>
            </Fragment>
        );
    };
}, 'withInspectorControl' );

wp.hooks.addFilter(
    'editor.BlockEdit',
    'ubc/extension/link-from-cf/post-title/add-controls',
    withInspectorControls
);