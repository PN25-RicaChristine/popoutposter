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
registerBlockType( 'lw/review', {
    title: __( 'Review' ),
    icon: 'tablet',
    category: 'common',
    parent: ['lw/customer-reviews'],
    attributes: {
        quote: {
            type: `array`,
            source: `children`,
            selector: `div.quote`,
        },
        person: {
            type: `array`,
            source: `children`,
            selector: `div.person`,
        },
        imgUrl: {
            type: 'string',
            default: 'https://via.placeholder.com/100'
        }
    },

    edit: props => {
        const { attributes: { quote, person, imgUrl  }, setAttributes } = props;

        return (
            <li>
                <div className="flex mb-2">
                    <MediaUpload
                        onSelect={ (media) => setAttributes({imgUrl: media.sizes.full.url }) }
                        className="border-frontend w-32 h-32"
                        render={ ({open}) => {
                            return <img
                                className="w-24"
                                src={imgUrl}
                                onClick={open}
                            />;
                        }}
                    />
                    <div className="ml-6">
                        <RichText
                            tagName="div"
                            className="quote italic"
                            onChange={quote => {
                                setAttributes({quote})
                            }}
                            value={quote}
                            placeholder="Quote"
                        />
                        <RichText
                            tagName="div"
                            className="person text-sm mt-2"
                            onChange={person => {
                                setAttributes({person})
                            }}
                            value={person}
                            placeholder="Person name"
                        />
                    </div>
                </div>
            </li>
        );
    },

    save: props => {
        const { attributes: { quote, person, imgUrl } } = props;

        return (
            <li>
                <div className="flex mb-2">
                    <img className="w-24" src={imgUrl} alt=""/>
                    <div className="ml-6">
                        <RichText.Content
                            tagName="div"
                            className="quote italic"
                            value={quote}
                        />
                        <RichText.Content
                            tagName="div"
                            className="person text-sm mt-2"
                            value={person}
                        />
                    </div>
                </div>
            </li>
        );
    },
} );
