/**
 * Attribute term screens: color picker and image picker for swatches.
 */
( function ( $ ) {
	'use strict';
	var cfg = window.shopglutSwatchTerms || {};

	$( function () {
		$( '.shopglut-color-field' ).wpColorPicker();
	} );

	// The "Add term" form is reset by AJAX after saving.
	$( document ).ajaxComplete( function ( e, xhr, settings ) {
		if ( settings && settings.data && settings.data.indexOf( 'action=add-tag' ) > -1 ) {
			$( '.shopglut-swatch-image img' ).hide().attr( 'src', '' );
			$( '.shopglut-swatch-image input' ).val( '' );
			$( '.shopglut-swatch-remove' ).hide();
			$( '.shopglut-color-field' ).wpColorPicker( 'color', '' );
		}
	} );

	$( document ).on( 'click', '.shopglut-swatch-pick', function ( e ) {
		e.preventDefault();
		var box = $( this ).closest( '.shopglut-swatch-image' );
		var frame = wp.media( { title: cfg.choose, button: { text: cfg.use }, library: { type: 'image' }, multiple: false } );
		frame.on( 'select', function () {
			var a = frame.state().get( 'selection' ).first().toJSON();
			box.find( 'input' ).val( a.id );
			box.find( 'img' ).attr( 'src', ( a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url ) ).show();
			box.find( '.shopglut-swatch-remove' ).show();
		} );
		frame.open();
	} );

	$( document ).on( 'click', '.shopglut-swatch-remove', function ( e ) {
		e.preventDefault();
		var box = $( this ).closest( '.shopglut-swatch-image' );
		box.find( 'input' ).val( '' );
		box.find( 'img' ).hide();
		$( this ).hide();
	} );
}( jQuery ) );
