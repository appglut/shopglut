/**
 * ShopGlut Popups: triggers, frequency, devices, accessibility and the subscribe form.
 * Runs in the browser so pages can be cached.
 */
( function () {
	'use strict';

	var cfg = window.shopglutPopups || {};
	var root = document.querySelector( '.shopglut-popups' );
	if ( ! root ) {
		return;
	}
	var maxOpen = parseInt( root.getAttribute( 'data-max' ), 10 ) || 1;
	var opened = 0;
	var lastFocus = null;

	function store( key, value ) {
		try {
			if ( undefined === value ) {
				return JSON.parse( localStorage.getItem( key ) || 'null' );
			}
			localStorage.setItem( key, JSON.stringify( value ) );
		} catch ( e ) {
			return null;
		}
	}

	function device() {
		var w = window.innerWidth;
		return w < 768 ? 'mobile' : ( w < 1025 ? 'tablet' : 'desktop' );
	}

	function allowed( c ) {
		if ( c.preview ) {
			return true;
		}
		if ( c.devices && c.devices.length && c.devices.indexOf( device() ) === -1 ) {
			return false;
		}
		var s = store( 'shopglut_popup_' + c.id ) || {};
		if ( s.done && c.stopAfter ) {
			return false;
		}
		switch ( c.showAgain ) {
			case 'always':
				return true;
			case 'session':
				try {
					return ! sessionStorage.getItem( 'shopglut_popup_' + c.id );
				} catch ( e ) {
					return true;
				}
			case 'never':
				return ! s.closed;
			default:
				return ! s.closed || ( Date.now() - s.closed ) > ( c.days || 7 ) * 864e5;
		}
	}

	function remember( c, done ) {
		var s = store( 'shopglut_popup_' + c.id ) || {};
		s.closed = Date.now();
		if ( done ) {
			s.done = true;
		}
		store( 'shopglut_popup_' + c.id, s );
		try {
			sessionStorage.setItem( 'shopglut_popup_' + c.id, '1' );
		} catch ( e ) {}
	}

	function focusables( el ) {
		return Array.prototype.slice.call( el.querySelectorAll( 'a[href], button:not([disabled]), input:not([type=hidden]):not([tabindex="-1"]), select, textarea' ) ).filter( function ( x ) {
			return x.offsetParent !== null;
		} );
	}

	function open( popup, force ) {
		var c = popup._cfg;
		if ( popup.classList.contains( 'is-open' ) ) {
			return;
		}
		var counts = 'bar' !== c.type;
		if ( ! force && counts && opened >= maxOpen ) {
			return;
		}
		if ( counts ) {
			opened++;
		}
		lastFocus = document.activeElement;
		popup.hidden = false;
		var close = popup.querySelector( '.shopglut-popup-close' );
		if ( close && c.closeDelay > 0 ) {
			close.style.visibility = 'hidden';
			setTimeout( function () {
				close.style.visibility = '';
			}, c.closeDelay * 1000 );
		}
		requestAnimationFrame( function () {
			popup.classList.add( 'is-open' );
		} );
		if ( c.lockScroll ) {
			document.documentElement.classList.add( 'shopglut-popup-locked' );
		}
		if ( c.push ) {
			var h = popup.querySelector( '.shopglut-popup-box' ).offsetHeight;
			document.documentElement.style.setProperty( '--sgp-bar-' + ( popup.classList.contains( 'pos-bottom' ) ? 'bottom' : 'top' ), h + 'px' );
			document.documentElement.classList.add( popup.classList.contains( 'pos-bottom' ) ? 'shopglut-bar-bottom' : 'shopglut-bar-top' );
		}
		if ( 'bar' !== c.type && 'slide-in' !== c.type ) {
			var f = focusables( popup.querySelector( '.shopglut-popup-box' ) );
			( f[ 0 ] || popup.querySelector( '.shopglut-popup-box' ) ).focus( { preventScroll: true } );
		}
		if ( c.autoClose > 0 ) {
			setTimeout( function () {
				shut( popup, false );
			}, c.autoClose * 1000 );
		}
	}

	function shut( popup, done ) {
		if ( ! popup.classList.contains( 'is-open' ) ) {
			return;
		}
		var c = popup._cfg;
		popup.classList.remove( 'is-open' );
		setTimeout( function () {
			popup.hidden = true;
		}, 300 );
		if ( c.lockScroll ) {
			document.documentElement.classList.remove( 'shopglut-popup-locked' );
		}
		if ( c.push ) {
			document.documentElement.classList.remove( 'shopglut-bar-top', 'shopglut-bar-bottom' );
		}
		if ( ! c.preview ) {
			remember( c, done );
		}
		if ( lastFocus && lastFocus.focus ) {
			lastFocus.focus( { preventScroll: true } );
		}
	}

	function arm( popup ) {
		var c = popup._cfg;
		// Links to #shopglut-popup-ID always open the popup.
		document.addEventListener( 'click', function ( e ) {
			var a = e.target.closest( 'a[href="#shopglut-popup-' + c.id + '"]' );
			var sel = c.selector && e.target.closest( c.selector );
			if ( a || ( 'click' === c.openOn && sel ) ) {
				e.preventDefault();
				open( popup, true );
			}
		} );
		if ( ! allowed( c ) ) {
			return;
		}
		if ( 'scroll' === c.openOn ) {
			var onScroll = function () {
				var max = document.documentElement.scrollHeight - window.innerHeight;
				if ( max <= 0 || ( window.scrollY / max ) * 100 >= c.scroll ) {
					window.removeEventListener( 'scroll', onScroll );
					open( popup );
				}
			};
			window.addEventListener( 'scroll', onScroll, { passive: true } );
		} else if ( 'load' === c.openOn ) {
			setTimeout( function () {
				open( popup );
			}, Math.max( 0, c.delay ) * 1000 );
		}
	}

	root.querySelectorAll( '.shopglut-popup' ).forEach( function ( popup ) {
		try {
			popup._cfg = JSON.parse( popup.getAttribute( 'data-config' ) );
		} catch ( e ) {
			return;
		}
		if ( popup._cfg.preview ) {
			popup._cfg.openOn = 'load';
			popup._cfg.delay = 0;
		}
		arm( popup );

		popup.addEventListener( 'click', function ( e ) {
			var act = e.target.closest( '[data-action]' );
			if ( e.target.classList.contains( 'shopglut-popup-overlay' ) && popup._cfg.overlay ) {
				shut( popup, false );
				return;
			}
			if ( ! act ) {
				return;
			}
			var a = act.getAttribute( 'data-action' );
			if ( 'close' === a ) {
				shut( popup, '1' === act.getAttribute( 'data-done' ) );
			} else if ( 'link' === a ) {
				remember( popup._cfg, true );
			} else if ( 'copy' === a ) {
				var code = act.getAttribute( 'data-code' );
				if ( navigator.clipboard ) {
					navigator.clipboard.writeText( code );
				}
				act.textContent = cfg.i18n.copied;
			}
		} );

		popup.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && popup._cfg.esc ) {
				shut( popup, false );
			}
			if ( 'Tab' === e.key && 'modal' === popup._cfg.type || 'Tab' === e.key && 'subscribe' === popup._cfg.type ) {
				var f = focusables( popup.querySelector( '.shopglut-popup-box' ) );
				if ( ! f.length ) {
					return;
				}
				if ( e.shiftKey && document.activeElement === f[ 0 ] ) {
					e.preventDefault();
					f[ f.length - 1 ].focus();
				} else if ( ! e.shiftKey && document.activeElement === f[ f.length - 1 ] ) {
					e.preventDefault();
					f[ 0 ].focus();
				}
			}
		} );

		var form = popup.querySelector( '.shopglut-popup-form' );
		if ( form ) {
			form.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var msg = form.querySelector( '.shopglut-popup-msg' );
				var email = form.querySelector( '[name=email]' );
				var consent = form.querySelector( '[name=consent]' );
				if ( ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email.value ) ) {
					msg.textContent = cfg.i18n.email;
					email.focus();
					return;
				}
				if ( consent && consent.required && ! consent.checked ) {
					msg.textContent = cfg.i18n.consent;
					return;
				}
				var data = new FormData( form );
				data.append( 'action', 'shopglut_popup_subscribe' );
				data.append( 'page', window.location.href );
				form.classList.add( 'is-loading' );
				fetch( cfg.ajax, { method: 'POST', body: data, credentials: 'same-origin' } ).then( function ( r ) {
					return r.json();
				} ).then( function ( r ) {
					form.classList.remove( 'is-loading' );
					msg.textContent = ( r.data && r.data.message ) || ( r.success ? msg.getAttribute( 'data-success' ) : cfg.i18n.error );
					if ( ! r.success ) {
						return;
					}
					form.classList.add( 'is-done' );
					remember( popup._cfg, true );
					if ( 'close' === popup._cfg.after ) {
						setTimeout( function () {
							shut( popup, true );
						}, 3000 );
					} else if ( 'redirect' === popup._cfg.after && popup._cfg.redirect ) {
						window.location.href = popup._cfg.redirect;
					}
				} ).catch( function () {
					form.classList.remove( 'is-loading' );
					msg.textContent = cfg.i18n.error;
				} );
			} );
		}
	} );
}() );
