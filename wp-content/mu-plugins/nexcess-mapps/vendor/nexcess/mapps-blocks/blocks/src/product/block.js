//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { RichText, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { Button } = wp.components;

/**
 * Register our block
 */
registerBlockType( 'lw/product', {
    title: __( 'Product' ),
    icon: 'tablet',
    category: 'common',
    parent: ['lw/products'],
    attributes: {
        header: {
            type: `array`,
            source: `children`,
            selector: `h3.title`,
        },
        price: {
            type: `array`,
            source: `children`,
            selector: `div.price`,
        },
        desc: {
            type: `array`,
            source: `children`,
            selector: `div.desc`,
        },
        imgUrl: {
            type: 'string',
            default: 'https://via.placeholder.com/350'
        }
    },

    edit: props => {
        const { attributes: { header, price, desc, imgUrl  }, setAttributes } = props;

        return (
                <li>
                    <MediaUpload
                        onSelect={ (media) => setAttributes({imgUrl: media.sizes.full.url }) }
                        render={ ({open}) => {
                            return <img
                                src={imgUrl}
                                onClick={open}
                            />;
                        }}
                    />
                    <div className="bg-color-main-500 color-secondary-500 p-2">
                        <RichText
                            tagName="h3"
                            className="title uppercase font-bold font-header"
                            onChange={header => {
                                setAttributes({header})
                            }}
                            value={header}
                            placeholder="Product name"
                        />
                        <RichText
                            tagName="div"
                            className="price"
                            onChange={price => {
                                setAttributes({price})
                            }}
                            value={price}
                            placeholder="Price"
                        />
                        <RichText
                            tagName="div"
                            className="desc uppercase"
                            onChange={desc => {
                                setAttributes({desc})
                            }}
                            value={desc}
                            placeholder="Short description"
                        />
                    </div>
                </li>
        );
    },

    save: props => {
        const { attributes: { header, price, desc, imgUrl } } = props;

        return (
            <li>
                <img src={imgUrl} alt=""/>
                <div className="bg-color-main-500 color-secondary-500 p-2">
                    <RichText.Content
                        tagName="h3"
                        className="title uppercase font-bold font-header"
                        value={header}
                    />
                    <RichText.Content
                        tagName="div"
                        className="price"
                        value={price}
                    />
                    <RichText.Content
                        tagName="div"
                        className="desc uppercase"
                        value={desc}
                    />
                </div>
            </li>
        );
    },
} );
