/**
 * AppGlut Framework — block editor block for each shortcode generator.
 * The block opens the picker and replaces itself with a core Shortcode block.
 */
( function ( wp, window ) {
	'use strict';

	if ( ! wp || ! wp.blocks || ! wp.element ) {
		return;
	}

	var el = wp.element.createElement;
	var Placeholder = wp.components.Placeholder;
	var Button = wp.components.Button;
	var useBlockProps = wp.blockEditor && wp.blockEditor.useBlockProps;

	( window.appglutBlocks || [] ).forEach( function ( conf ) {
		if ( wp.blocks.getBlockType( conf.name ) ) {
			return;
		}

		wp.blocks.registerBlockType( conf.name, {
			apiVersion: 2,
			title: conf.title,
			description: conf.description,
			icon: conf.icon,
			category: conf.category,
			keywords: conf.keywords,
			supports: { html: false, reusable: false },

			edit: function ( props ) {
				var open = function () {
					if ( ! window.AppGlut || ! window.AppGlut.shortcoder ) {
						return;
					}
					window.AppGlut.shortcoder.open( conf.unique, function ( text ) {
						var block = wp.blocks.createBlock( 'core/shortcode', { text: text } );
						wp.data.dispatch( 'core/block-editor' ).replaceBlocks( props.clientId, block );
					} );
				};

				var blockProps = useBlockProps ? useBlockProps() : {};

				return el(
					'div',
					blockProps,
					el(
						Placeholder,
						{ icon: conf.icon, label: conf.title, instructions: conf.description },
						el( Button, { variant: 'primary', isPrimary: true, onClick: open }, conf.button )
					)
				);
			},

			save: function () {
				return null;
			}
		} );
	} );
}( window.wp, window ) );
