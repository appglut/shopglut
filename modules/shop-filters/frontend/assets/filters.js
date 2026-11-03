/**
 * ShopGlut Shop Filters: collect the choices into clean URL parameters and submit.
 */
( function () {
	'use strict';

	function sync( form ) {
		// Term lists: checked boxes / radios / dropdown -> the hidden comma list.
		form.querySelectorAll( '.sgf-terms[data-param]' ).forEach( function ( box ) {
			var hidden = box.querySelector( 'input[type=hidden][name="' + box.getAttribute( 'data-param' ) + '"]' );
			if ( ! hidden ) {
				return;
			}
			var select = box.querySelector( 'select.sgf-select' );
			var values = select ? ( select.value ? [ select.value ] : [] ) : Array.prototype.map.call( box.querySelectorAll( 'input:checked' ), function ( i ) {
				return i.value;
			} );
			hidden.value = values.join( ',' );
		} );
		// Price ranges (radio design).
		var range = form.querySelector( 'input[name=sgf_price_range]:checked' );
		if ( range ) {
			form.querySelector( '.sgf-price-ranges [name=min_price]' ).value = range.getAttribute( 'data-min' );
			form.querySelector( '.sgf-price-ranges [name=max_price]' ).value = range.getAttribute( 'data-max' );
		}
		// Rating.
		var rating = form.querySelector( '.sgf-rating input:checked' );
		var ratingField = form.querySelector( '[name=rating_filter]' );
		if ( ratingField ) {
			ratingField.value = rating ? rating.value : '';
		}
	}

	function cleanSubmit( form ) {
		sync( form );
		var params = new URLSearchParams();
		var hasTerms = {};
		form.querySelectorAll( '.sgf-terms[data-param] input[type=hidden]' ).forEach( function ( h ) {
			if ( h.name.indexOf( 'query_type_' ) !== 0 && h.name.slice( -5 ) !== '_type' ) {
				hasTerms[ h.name ] = !! h.value;
			}
		} );
		Array.prototype.forEach.call( form.elements, function ( el ) {
			if ( ! el.name || el.disabled || 'sgf_price_range' === el.name || 'sgf_rating' === el.name || /_(c|r)$/.test( el.name ) || el.type === 'range' ) {
				return;
			}
			if ( ( el.type === 'checkbox' || el.type === 'radio' ) && ! el.checked ) {
				return;
			}
			if ( '' === el.value ) {
				return;
			}
			// Query type only goes with a chosen term list.
			if ( 0 === el.name.indexOf( 'query_type_' ) && ! hasTerms[ 'filter_' + el.name.slice( 11 ) ] ) {
				return;
			}
			if ( el.name.slice( -5 ) === '_type' && 0 === el.name.indexOf( 'sgf_' ) && ! hasTerms[ el.name.slice( 0, -5 ) ] ) {
				return;
			}
			// Price at its full range is no filter.
			if ( ( 'min_price' === el.name || 'max_price' === el.name ) && el.hasAttribute( 'data-default' ) && parseFloat( el.value ) === parseFloat( el.getAttribute( 'data-default' ) ) ) {
				return;
			}
			params.append( el.name, el.value );
		} );
		var q = params.toString();
		window.location.href = form.getAttribute( 'action' ) + ( q ? ( form.getAttribute( 'action' ).indexOf( '?' ) > -1 ? '&' : '?' ) + q : '' );
	}

	document.addEventListener( 'submit', function ( e ) {
		if ( e.target.classList && e.target.classList.contains( 'shopglut-filters' ) ) {
			e.preventDefault();
			cleanSubmit( e.target );
		}
	} );

	document.addEventListener( 'click', function ( e ) {
		var t = e.target.closest( '.sgf-title' );
		if ( t ) {
			var body = document.getElementById( t.getAttribute( 'aria-controls' ) );
			var open = 'true' === t.getAttribute( 'aria-expanded' );
			t.setAttribute( 'aria-expanded', open ? 'false' : 'true' );
			if ( body ) {
				body.hidden = open;
			}
			return;
		}
		var toggle = e.target.closest( '.sgf-toggle' );
		if ( toggle ) {
			var form = toggle.closest( '.shopglut-filters' );
			var expanded = 'true' === toggle.getAttribute( 'aria-expanded' );
			toggle.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );
			form.classList.toggle( 'is-collapsed', expanded );
		}
	} );

	// Visual state of options; radios can be unchecked by a second click.
	document.addEventListener( 'change', function ( e ) {
		var opt = e.target.closest( '.sgf-opt' );
		if ( opt ) {
			var group = opt.parentNode;
			group.querySelectorAll( '.sgf-opt' ).forEach( function ( o ) {
				o.classList.toggle( 'is-on', o.querySelector( 'input' ).checked );
			} );
		}
	} );
	document.addEventListener( 'mousedown', function ( e ) {
		var radio = e.target.closest( '.sgf-opt' ) && e.target.closest( '.sgf-opt' ).querySelector( 'input[type=radio]' );
		if ( radio ) {
			radio._was = radio.checked;
		}
	} );
	document.addEventListener( 'click', function ( e ) {
		var label = e.target.closest( '.sgf-opt' );
		var radio = label && label.querySelector( 'input[type=radio]' );
		if ( radio && radio._was && e.target === radio ) {
			radio.checked = false;
			radio._was = false;
			label.classList.remove( 'is-on' );
		}
	} );

	// Price slider <-> number fields.
	document.querySelectorAll( '.sgf-price' ).forEach( function ( box ) {
		var rMin = box.querySelector( '.sgf-r-min' ), rMax = box.querySelector( '.sgf-r-max' );
		var nMin = box.querySelector( '[name=min_price]' ), nMax = box.querySelector( '[name=max_price]' );
		function paint() {
			var lo = parseFloat( rMin.min ), hi = parseFloat( rMin.max ) || 1;
			box.style.setProperty( '--a', ( ( parseFloat( rMin.value ) - lo ) / ( hi - lo || 1 ) ) * 100 + '%' );
			box.style.setProperty( '--b', ( ( parseFloat( rMax.value ) - lo ) / ( hi - lo || 1 ) ) * 100 + '%' );
		}
		rMin.addEventListener( 'input', function () {
			if ( parseFloat( rMin.value ) > parseFloat( rMax.value ) ) {
				rMin.value = rMax.value;
			}
			nMin.value = rMin.value;
			paint();
		} );
		rMax.addEventListener( 'input', function () {
			if ( parseFloat( rMax.value ) < parseFloat( rMin.value ) ) {
				rMax.value = rMin.value;
			}
			nMax.value = rMax.value;
			paint();
		} );
		nMin.addEventListener( 'change', function () {
			rMin.value = nMin.value;
			paint();
		} );
		nMax.addEventListener( 'change', function () {
			rMax.value = nMax.value;
			paint();
		} );
		paint();
	} );

	// Small screens start collapsed.
	if ( window.matchMedia( '(max-width: 768px)' ).matches ) {
		document.querySelectorAll( '.shopglut-filters' ).forEach( function ( f ) {
			if ( ! f.querySelector( '.sgf-chip' ) ) {
				f.classList.add( 'is-collapsed' );
				var t = f.querySelector( '.sgf-toggle' );
				if ( t ) {
					t.setAttribute( 'aria-expanded', 'false' );
				}
			}
		} );
	}
}() );
