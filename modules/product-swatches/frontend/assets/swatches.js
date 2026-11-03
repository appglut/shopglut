/**
 * ShopGlut Variation Swatches: swatches drive the hidden WooCommerce <select>, and follow which
 * choices are still available.
 */
( function ( $ ) {
	'use strict';

	function selectOf( box ) {
		return box.find( '.sgs-select select' );
	}

	function refresh( box ) {
		var select = selectOf( box );
		var current = select.val();
		var available = {};
		select.find( 'option' ).each( function () {
			if ( this.value && ! this.disabled ) {
				available[ this.value ] = true;
			}
		} );
		box.find( '.sgs-item' ).each( function () {
			var item = $( this );
			var value = item.attr( 'data-value' );
			var on = value === current;
			item.toggleClass( 'is-on', on ).attr( 'aria-pressed', on ? 'true' : 'false' );
			item.toggleClass( 'is-unavailable', ! available[ value ] );
		} );
		var chosen = box.find( '.sgs-item.is-on' ).attr( 'data-name' ) || '';
		box.find( '.sgs-chosen-value' ).text( chosen );
		// Show the value next to the theme's own label ("Color: Blue") when there is one.
		if ( box.find( '.sgs-chosen' ).length ) {
			var label = $( 'label[for="' + select.attr( 'id' ) + '"]' );
			if ( label.length ) {
				box.find( '.sgs-chosen' ).hide();
				var span = label.find( '.sgs-label-value' );
				if ( ! span.length ) {
					span = $( '<span class="sgs-label-value"></span>' ).appendTo( label );
				}
				span.text( chosen ? ': ' + chosen : '' );
			}
		}
	}

	function init( form ) {
		var boxes = form.find( '.sgs:not(.sgs-t1)' );
		if ( ! boxes.length || form.data( 'shopglutSwatches' ) ) {
			return;
		}
		form.data( 'shopglutSwatches', true );
		boxes.each( function () {
			refresh( $( this ) );
		} );

		form.on( 'click', '.sgs-item', function ( e ) {
			e.preventDefault();
			var item = $( this );
			var box = item.closest( '.sgs' );
			var select = selectOf( box );
			var value = item.attr( 'data-value' );
			if ( item.hasClass( 'is-on' ) ) {
				if ( '1' !== box.attr( 'data-deselect' ) ) {
					return;
				}
				value = '';
			}
			// A choice that no longer fits the other choices: start over with this one.
			if ( value && ! select.find( 'option[value="' + value.replace( /"/g, '\\"' ) + '"]' ).length ) {
				form.find( '.reset_variations' ).trigger( 'click' );
			}
			select.val( value ).trigger( 'change' );
			boxes.each( function () {
				refresh( $( this ) );
			} );
		} );

		// WooCommerce rebuilt the options (after any change) or reset the form.
		form.on( 'woocommerce_update_variation_values reset_data', function () {
			setTimeout( function () {
				boxes.each( function () {
					refresh( $( this ) );
				} );
			}, 0 );
		} );
	}

	$( function () {
		$( '.variations_form' ).each( function () {
			init( $( this ) );
		} );
	} );
	// Quick views and other forms added later.
	$( document.body ).on( 'wc_variation_form', function ( e ) {
		init( $( e.target ) );
	} );
}( jQuery ) );
