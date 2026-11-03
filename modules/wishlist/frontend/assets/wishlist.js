/**
 * ShopGlut Wishlist: buttons, wishlist page, sharing, QR code and print.
 */
( function () {
	'use strict';

	var cfg = window.shopglutWishlist || {};

	function post( data ) {
		var body = new URLSearchParams();
		Object.keys( data ).forEach( function ( k ) {
			body.append( k, data[ k ] );
		} );
		body.append( 'nonce', cfg.nonce || '' );
		return fetch( cfg.ajax, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		} ).then( function ( r ) {
			return r.json();
		} );
	}

	function notify( message, added ) {
		if ( ! message || 'notification-off' === cfg.notification ) {
			return;
		}
		var box = document.querySelector( '.shopglut-wl-note' );
		if ( ! box ) {
			box = document.createElement( 'div' );
			box.setAttribute( 'role', 'status' );
			document.body.appendChild( box );
		}
		var popup = 'popup-notification' === cfg.notification;
		box.className = 'shopglut-wl-note ' + ( popup ? 'is-popup' : 'is-side pos-' + ( cfg.position || 'bottom-right' ) ) + ( added ? ' is-added' : ' is-removed' );
		box.innerHTML = '';
		var text = document.createElement( 'span' );
		text.textContent = message;
		box.appendChild( text );
		if ( added && cfg.page ) {
			var link = document.createElement( 'a' );
			link.href = cfg.page;
			link.textContent = cfg.i18n.view;
			box.appendChild( link );
		}
		box.classList.add( 'is-visible' );
		clearTimeout( box._t );
		box._t = setTimeout( function () {
			box.classList.remove( 'is-visible' );
		}, 3500 );
	}

	function setCount( count ) {
		document.querySelectorAll( '.shopglut-wl-count' ).forEach( function ( el ) {
			el.textContent = count;
			el.hidden = ! count && el.hasAttribute( 'data-hide-empty' );
		} );
	}

	function setState( id, added ) {
		document.querySelectorAll( '.shopglut-wl-btn[data-product="' + id + '"]' ).forEach( function ( b ) {
			b.classList.toggle( 'is-added', added );
			b.setAttribute( 'aria-pressed', added ? 'true' : 'false' );
			var t = b.querySelector( '.shopglut-wl-text' );
			if ( t ) {
				t.textContent = added ? t.getAttribute( 'data-on' ) : t.getAttribute( 'data-off' );
			}
		} );
	}

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.shopglut-wl-btn' );
		if ( btn ) {
			e.preventDefault();
			var id = btn.getAttribute( 'data-product' );
			if ( btn.classList.contains( 'is-added' ) ) {
				var second = btn.getAttribute( 'data-second' );
				if ( 'goto-wishlist' === second ) {
					window.location.href = cfg.page;
					return;
				}
				if ( 'redirect-to-checkout' === second ) {
					window.location.href = cfg.checkout;
					return;
				}
				if ( 'show-already-exist' === second ) {
					notify( cfg.i18n.already, true );
					return;
				}
			}
			btn.classList.add( 'is-loading' );
			post( { action: 'shopglut_wishlist_toggle', product_id: id } ).then( function ( r ) {
				btn.classList.remove( 'is-loading' );
				if ( ! r.success ) {
					if ( r.data && r.data.login ) {
						window.location.href = r.data.login;
						return;
					}
					notify( r.data && r.data.message, false );
					return;
				}
				setState( id, r.data.added );
				setCount( r.data.count );
				notify( r.data.message, r.data.added );
			} );
			return;
		}

		var remove = e.target.closest( '.shopglut-wl-remove' );
		if ( remove ) {
			e.preventDefault();
			var row = remove.closest( 'tr' );
			if ( row ) {
				row.style.opacity = '.4';
			}
			post( { action: 'shopglut_wishlist_toggle', product_id: remove.getAttribute( 'data-product' ), mode: 'remove' } ).then( function ( r ) {
				if ( r.success ) {
					setCount( r.data.count );
					notify( r.data.message, false );
					if ( ! r.data.count ) {
						window.location.reload();
					} else if ( row ) {
						row.remove();
					}
				}
			} );
			return;
		}

		var copy = e.target.closest( '.shopglut-wl-copy' );
		if ( copy ) {
			var url = copy.getAttribute( 'data-url' );
			( navigator.clipboard ? navigator.clipboard.writeText( url ) : Promise.reject() ).then( function () {
				notify( cfg.i18n.copied, false );
			}, function () {
				window.prompt( '', url );
			} );
			return;
		}

		var qr = e.target.closest( '.shopglut-wl-qr-btn' );
		if ( qr && typeof window.qrcode === 'function' ) {
			var box = qr.closest( '.shopglut-wl-share' ).querySelector( '.shopglut-wl-qr' );
			if ( ! box.hasChildNodes() ) {
				var code = window.qrcode( 0, 'M' );
				code.addData( qr.getAttribute( 'data-url' ) );
				code.make();
				box.innerHTML = code.createSvgTag( { cellSize: 5, margin: 2, scalable: true } );
			}
			box.hidden = ! box.hidden;
			return;
		}

		if ( e.target.closest( '.shopglut-wl-print' ) ) {
			window.print();
		}
	} );
}() );
