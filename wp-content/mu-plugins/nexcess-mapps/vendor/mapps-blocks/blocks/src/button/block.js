//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { RichText } = wp.blockEditor;

/**
 * Register our block
 */
registerBlockType( 'lw/button', {
    title: __( 'Button' ),
    icon: 'tablet',
    category: 'common',
    parent: [
        'lw/hero-gifts',
    ],
    attributes: {
        text: {
            type: `string`,
            source: `text`,
            selector: `a.button`,
        },
        href: {
            type: 'string',
            default: null
        }
    },

    edit: props => {
        const { attributes: { text, href  }, setAttributes } = props;

        return (
            <a
              className={``}
              href=""
            >

            </a>
        );
    },

    save: props => {
        const { attributes: { text, href } } = props;

        return (
            <a href="">

            </a>
        );
    },
} );
