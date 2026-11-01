/**
 * AppGlut Framework — admin UI.
 *
 * Public API (window.AppGlut):
 *   AppGlut.init( $scope )            Initialise fields inside $scope (after inserting markup).
 *   AppGlut.fields.myType = fn        Register an initialiser for custom field types: fn( $scope ).
 *   AppGlut.serialize( $el )          Nested object of the named inputs inside $el.
 *   AppGlut.toast( message, type )    Show a notification (type: success | error | info).
 *   AppGlut.confirm( message )        Promise resolved when the user confirms.
 *
 * Events: 'agf:init' on the scope, 'agf:shown' when a hidden area becomes
 * visible, 'agf:sync' on inputs whose value was changed programmatically.
 */
( function ( $, window, document ) {
	'use strict';

	var L = window.appglutL10n || {};
	var AGF = window.AppGlut = window.AppGlut || {};
	var uidCounter = 0;

	AGF.fields = AGF.fields || {};

	function data() {
		return window.appglutData || {};
	}

	AGF.uid = function ( prefix ) {
		uidCounter += 1;
		return ( prefix || 'agf' ) + '-' + Date.now().toString( 36 ) + '-' + uidCounter;
	};

	/** Elements matching selector in $scope (scope included) not yet initialised for key. */
	function once( $scope, selector, key ) {
		return $scope.find( selector ).addBack( selector ).filter( function () {
			this.agfInit = this.agfInit || {};
			if ( this.agfInit[ key ] ) {
				return false;
			}
			this.agfInit[ key ] = true;
			return true;
		} );
	}

	function debounce( fn, wait ) {
		var timer;
		return function () {
			var args = arguments, ctx = this;
			clearTimeout( timer );
			timer = setTimeout( function () {
				fn.apply( ctx, args );
			}, wait );
		};
	}

	function escapeHtml( str ) {
		return String( str == null ? '' : str ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	function themeOf( $el ) {
		var $app = $el && $el.closest ? $el.closest( '.agf-app' ) : $();
		return $app.length ? $app.attr( 'data-theme' ) : 'light';
	}

	/* =====================================================================
	 * Toasts & dialogs
	 * =================================================================== */

	function toastHost() {
		var $host = $( 'body > .agf-toast-host' );
		if ( ! $host.length ) {
			$host = $( '<div class="agf-ui agf-toast-host" aria-live="polite"></div>' ).appendTo( 'body' );
		}
		return $host;
	}

	AGF.toast = function ( message, type, theme ) {
		var $host = toastHost().attr( 'data-theme', theme || themeOf( $( '.agf-app' ).first() ) );
		var icons = { success: 'dashicons-yes-alt', error: 'dashicons-warning', info: 'dashicons-info-outline' };
		type = icons[ type ] ? type : 'info';
		var $toast = $( '<div class="agf-toast agf-toast-' + type + '" role="status"><i class="dashicons ' + icons[ type ] + '"></i><span></span><button type="button" class="agf-toast-close" aria-label="Close">&times;</button></div>' );
		$toast.find( 'span' ).text( message );
		$host.append( $toast );
		requestAnimationFrame( function () {
			$toast.addClass( 'agf-in' );
		} );
		var close = function () {
			$toast.removeClass( 'agf-in' );
			setTimeout( function () {
				$toast.remove();
			}, 250 );
		};
		$toast.on( 'click', '.agf-toast-close', close );
		setTimeout( close, type === 'error' ? 6000 : 3200 );
	};

	/** Generic modal. Returns { $modal, close }. */
	function modal( opts ) {
		var $modal = $(
			'<div class="agf-ui agf-modal" role="dialog" aria-modal="true">' +
				'<div class="agf-modal-backdrop"></div>' +
				'<div class="agf-modal-dialog ' + ( opts.size ? 'agf-modal-' + opts.size : '' ) + '">' +
					'<div class="agf-modal-head"><h3></h3><button type="button" class="agf-icon-btn agf-modal-close" aria-label="Close"><i class="dashicons dashicons-no-alt"></i></button></div>' +
					'<div class="agf-modal-body"></div>' +
					( opts.footer ? '<div class="agf-modal-foot"></div>' : '' ) +
				'</div>' +
			'</div>'
		).attr( 'data-theme', opts.theme || 'light' );

		$modal.find( 'h3' ).text( opts.title || '' );
		$modal.find( '.agf-modal-body' ).append( opts.body || '' );
		if ( opts.footer ) {
			$modal.find( '.agf-modal-foot' ).append( opts.footer );
		}

		var close = function () {
			$modal.removeClass( 'agf-in' );
			$( document ).off( 'keydown.agfmodal' );
			setTimeout( function () {
				$modal.remove();
			}, 200 );
			if ( opts.onClose ) {
				opts.onClose();
			}
		};

		$modal.on( 'click', '.agf-modal-backdrop, .agf-modal-close', close );
		$( document ).on( 'keydown.agfmodal', function ( e ) {
			if ( e.key === 'Escape' ) {
				close();
			}
		} );

		$modal.appendTo( 'body' );
		requestAnimationFrame( function () {
			$modal.addClass( 'agf-in' );
		} );

		return { $modal: $modal, close: close };
	}

	AGF.modal = modal;

	AGF.confirm = function ( message, opts ) {
		opts = opts || {};
		return new Promise( function ( resolve ) {
			var done = false;
			var $ok = $( '<button type="button" class="agf-btn ' + ( opts.danger ? 'agf-btn-danger-solid' : 'agf-btn-primary' ) + '"></button>' ).text( opts.ok || 'OK' );
			var $cancel = $( '<button type="button" class="agf-btn agf-btn-ghost"></button>' ).text( opts.cancel || 'Cancel' );
			var m = modal( {
				title: opts.title || '',
				body: $( '<p class="agf-confirm-text"></p>' ).text( message ),
				footer: $cancel.add( $ok ),
				size: 'sm',
				theme: opts.theme,
				onClose: function () {
					if ( ! done ) {
						resolve( false );
					}
				}
			} );
			$ok.on( 'click', function () {
				done = true;
				resolve( true );
				m.close();
			} );
			$cancel.on( 'click', m.close );
			setTimeout( function () {
				$ok.trigger( 'focus' );
			}, 50 );
		} );
	};

	/* =====================================================================
	 * Serializer — builds nested data from input names like a[b][0][c] / a[b][]
	 * =================================================================== */

	AGF.serialize = function ( $el ) {
		var out = {};

		$( $el ).find( ':input[name]' ).each( function () {
			var el = this;
			if ( el.disabled || el.type === 'file' || el.type === 'submit' || el.type === 'button' ) {
				return;
			}
			if ( ( el.type === 'checkbox' || el.type === 'radio' ) && ! el.checked ) {
				return;
			}

			var values = el.tagName === 'SELECT' && el.multiple ? $( el ).val() || [] : [ $( el ).val() ];
			var name = el.name;
			var keys = [];
			var base = name.split( '[' )[ 0 ];
			keys.push( base );
			name.replace( /\[([^\]]*)\]/g, function ( m, key ) {
				keys.push( key );
			} );

			values.forEach( function ( value ) {
				var node = out;
				for ( var i = 0; i < keys.length; i++ ) {
					var key = keys[ i ];
					var last = i === keys.length - 1;
					var nextIsPush = ! last && keys[ i + 1 ] === '';

					if ( key === '' ) {
						if ( last ) {
							node.push( value );
							return;
						}
						var child = {};
						node.push( child );
						node = child;
						continue;
					}

					if ( last ) {
						node[ key ] = value;
					} else {
						if ( typeof node[ key ] !== 'object' || node[ key ] === null ) {
							node[ key ] = nextIsPush ? [] : {};
						}
						node = node[ key ];
					}
				}
			} );
		} );

		return out;
	};

	/* =====================================================================
	 * Conditional logic (dependencies)
	 * =================================================================== */

	var SCOPES = '.agf-repeater-item, .agf-fieldset, .agf-accordion, .agf-tabbed, .agf-sortable, .agf-app';

	/** Current value of a field wrapper (string or array). */
	AGF.fieldValue = function ( $wrap ) {
		var values = [];
		var multi = false;
		var $inputs = $wrap.find( ':input[name]' ).not( '[data-agf-skip]' ).filter( function () {
			return $( this ).closest( '.agf-field' )[ 0 ] === $wrap[ 0 ];
		} );

		if ( ! $inputs.length ) {
			return '';
		}

		$inputs.each( function () {
			var el = this;
			var isArrayName = /\[\]$/.test( el.name );
			if ( el.type === 'checkbox' ) {
				if ( isArrayName ) {
					multi = true;
					if ( el.checked ) {
						values.push( el.value );
					}
				} else {
					values.push( el.checked ? el.value : '0' );
				}
			} else if ( el.type === 'radio' ) {
				if ( el.checked ) {
					values.push( el.value );
				}
			} else if ( el.tagName === 'SELECT' && el.multiple ) {
				multi = true;
				values = values.concat( $( el ).val() || [] );
			} else {
				if ( isArrayName ) {
					multi = true;
				}
				values.push( $( el ).val() );
			}
		} );

		if ( multi ) {
			return values;
		}
		return values.length ? String( values[ values.length - 1 ] ) : '';
	};

	function findController( $field, id ) {
		var $scope = $field.parent().closest( SCOPES );
		while ( $scope.length ) {
			var $found = $scope.find( '.agf-field[data-field-id="' + id + '"]' ).filter( function () {
				return $( this ).parent().closest( SCOPES )[ 0 ] === $scope[ 0 ];
			} );
			if ( $found.length ) {
				return $found.first();
			}
			if ( $scope.hasClass( 'agf-app' ) ) {
				break;
			}
			$scope = $scope.parent().closest( SCOPES );
		}
		return $();
	}

	function compare( op, actual, expected ) {
		var list = $.isArray( actual ) ? actual.map( String ) : [ String( actual ) ];
		var exp = String( expected );
		var expList = exp.split( ',' ).map( function ( s ) {
			return s.trim();
		} );
		var a = parseFloat( $.isArray( actual ) ? actual.length : actual );
		var b = parseFloat( exp );
		var isEmpty = $.isArray( actual ) ? actual.length === 0 : ( actual === '' || actual === null || actual === undefined );

		switch ( op ) {
			case '==':
			case '=':
				return list.indexOf( exp ) > -1 || ( ! $.isArray( actual ) && String( actual ) === exp );
			case '!=':
				return list.indexOf( exp ) === -1;
			case '>':
				return a > b;
			case '>=':
				return a >= b;
			case '<':
				return a < b;
			case '<=':
				return a <= b;
			case 'any':
				return list.some( function ( v ) {
					return expList.indexOf( v ) > -1;
				} );
			case 'not-any':
				return ! list.some( function ( v ) {
					return expList.indexOf( v ) > -1;
				} );
			case 'contains':
				return list.join( ',' ).indexOf( exp ) > -1;
			case 'not-contains':
				return list.join( ',' ).indexOf( exp ) === -1;
			case 'empty':
				return isEmpty;
			case 'not-empty':
				return ! isEmpty;
		}
		return true;
	}

	AGF.compare = compare;

	AGF.runDependencies = function ( $root ) {
		var $fields = $root.find( '.agf-field[data-dep]' );
		if ( ! $fields.length ) {
			return;
		}
		// A few passes so chained dependencies settle.
		for ( var pass = 0; pass < 3; pass++ ) {
			$fields.each( function () {
				var $field = $( this );
				var rules = $field.data( 'dep' ) || [];
				var or = $field.attr( 'data-dep-rel' ) === 'or';
				var result = ! or;

				for ( var i = 0; i < rules.length; i++ ) {
					var rule = rules[ i ];
					var $ctrl = findController( $field, rule.f );
					var ok = $ctrl.length && ! $ctrl.hasClass( 'agf-dep-hidden' ) && compare( rule.o, AGF.fieldValue( $ctrl ), rule.v );
					if ( or && ok ) {
						result = true;
						break;
					}
					if ( ! or && ! ok ) {
						result = false;
						break;
					}
				}

				$field.toggleClass( 'agf-dep-hidden', ! result );
			} );
		}
	};

	function bindDependencies( $app ) {
		if ( $app[ 0 ].agfDeps ) {
			return;
		}
		$app[ 0 ].agfDeps = true;
		var run = debounce( function () {
			AGF.runDependencies( $app );
		}, 60 );
		$app.on( 'change input agf:sync', ':input', run );
		$app.on( 'agf:init', run );
	}

	/* =====================================================================
	 * Field initialisers
	 * =================================================================== */

	/* ---- Text: password reveal, copy, counters, autosize ---- */
	AGF.fields.text = function ( $scope ) {
		once( $scope, '.agf-counter', 'counter' ).each( function () {
			var $counter = $( this );
			var $input = $counter.prev( '.agf-input-group' ).find( '.agf-input' ).add( $counter.prev( 'textarea' ) ).first();
			var max = parseInt( $counter.data( 'max' ), 10 ) || 0;
			var update = function () {
				var len = ( $input.val() || '' ).length;
				$counter.text( max ? len + ' / ' + max : len + ' ' + ( L.characters || 'characters' ) ).toggleClass( 'agf-over', !! ( max && len >= max ) );
			};
			$input.on( 'input agf:sync', update );
			update();
		} );

		once( $scope, '.agf-autosize', 'autosize' ).each( function () {
			var el = this;
			var resize = function () {
				el.style.height = 'auto';
				el.style.height = el.scrollHeight + 2 + 'px';
			};
			$( el ).on( 'input agf:sync', resize );
			setTimeout( resize, 0 );
		} );
	};

	$( document ).on( 'click', '.agf-reveal', function () {
		var $input = $( this ).siblings( 'input' );
		var show = $input.attr( 'type' ) === 'password';
		$input.attr( 'type', show ? 'text' : 'password' );
		$( this ).find( '.dashicons' ).toggleClass( 'dashicons-visibility', ! show ).toggleClass( 'dashicons-hidden', show );
	} );

	function copyText( text ) {
		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText( text );
		}
		var $tmp = $( '<textarea style="position:fixed;opacity:0"></textarea>' ).val( text ).appendTo( 'body' );
		$tmp[ 0 ].select();
		document.execCommand( 'copy' );
		$tmp.remove();
		return Promise.resolve();
	}

	$( document ).on( 'click', '.agf-copy', function () {
		var $btn = $( this );
		copyText( $btn.siblings( 'input' ).val() || '' ).then( function () {
			AGF.toast( L.copied || 'Copied', 'success', themeOf( $btn ) );
		} );
	} );

	/* ---- Color ---- */
	function parseColor( str ) {
		str = String( str || '' ).trim().toLowerCase();
		var m;
		if ( ( m = str.match( /^#([0-9a-f]{3,8})$/ ) ) ) {
			var h = m[ 1 ];
			if ( h.length === 3 || h.length === 4 ) {
				h = h.split( '' ).map( function ( c ) {
					return c + c;
				} ).join( '' );
			}
			if ( h.length !== 6 && h.length !== 8 ) {
				return null;
			}
			return {
				r: parseInt( h.substr( 0, 2 ), 16 ),
				g: parseInt( h.substr( 2, 2 ), 16 ),
				b: parseInt( h.substr( 4, 2 ), 16 ),
				a: h.length === 8 ? Math.round( parseInt( h.substr( 6, 2 ), 16 ) / 2.55 ) / 100 : 1
			};
		}
		if ( ( m = str.match( /^rgba?\(\s*(\d+)\s*,?\s*(\d+)\s*,?\s*(\d+)\s*(?:[,\/]\s*([\d.]+%?)\s*)?\)$/ ) ) ) {
			var a = m[ 4 ] === undefined ? 1 : ( m[ 4 ].indexOf( '%' ) > -1 ? parseFloat( m[ 4 ] ) / 100 : parseFloat( m[ 4 ] ) );
			return { r: +m[ 1 ], g: +m[ 2 ], b: +m[ 3 ], a: Math.max( 0, Math.min( 1, a ) ) };
		}
		return null;
	}

	function toHex( c ) {
		return '#' + [ c.r, c.g, c.b ].map( function ( n ) {
			return ( '0' + Math.max( 0, Math.min( 255, n ) ).toString( 16 ) ).slice( -2 );
		} ).join( '' );
	}

	function formatColor( c ) {
		return c.a >= 1 ? toHex( c ) : 'rgba(' + c.r + ', ' + c.g + ', ' + c.b + ', ' + Math.round( c.a * 100 ) / 100 + ')';
	}

	AGF.fields.color = function ( $scope ) {
		once( $scope, '.agf-color', 'color' ).each( function () {
			var $wrap = $( this );
			var $text = $wrap.find( '.agf-color-text' );
			var $native = $wrap.find( '.agf-color-native' );
			var $alpha = $wrap.find( '.agf-color-alpha' );
			var $chip = $wrap.find( '.agf-color-chip' );

			var paint = function () {
				var val = $text.val();
				var c = parseColor( val );
				$chip.css( 'background', val || 'transparent' ).toggleClass( 'agf-is-empty', ! val );
				if ( c ) {
					$native.val( toHex( c ) );
					$alpha.val( Math.round( c.a * 100 ) ).css( '--agf-alpha-color', toHex( c ) );
				}
			};

			var set = function ( val ) {
				$text.val( val ).trigger( 'change' );
				paint();
			};

			$text.on( 'input change agf:sync', paint );

			$native.on( 'input', function () {
				var c = parseColor( this.value );
				var current = parseColor( $text.val() );
				c.a = current ? current.a : 1;
				if ( $wrap.data( 'alpha' ) !== 1 && $wrap.attr( 'data-alpha' ) !== '1' ) {
					c.a = 1;
				}
				set( formatColor( c ) );
			} );

			$alpha.on( 'input', function () {
				var c = parseColor( $text.val() ) || { r: 0, g: 0, b: 0, a: 1 };
				c.a = parseInt( this.value, 10 ) / 100;
				set( formatColor( c ) );
			} );

			$wrap.on( 'click', '.agf-color-reset', function () {
				set( $wrap.attr( 'data-default' ) || '' );
			} );

			$wrap.on( 'click', '.agf-color-preset', function () {
				set( $( this ).data( 'color' ) );
			} );

			paint();
		} );
	};

	/* ---- Slider / spinner / linked sides ---- */
	AGF.fields.slider = function ( $scope ) {
		once( $scope, '.agf-slider', 'slider' ).each( function () {
			var $range = $( this ).find( '.agf-range' );
			var $num = $( this ).find( 'input[type=number]' );
			var paint = function () {
				var min = parseFloat( $range.attr( 'min' ) ) || 0;
				var max = parseFloat( $range.attr( 'max' ) ) || 100;
				var pct = ( ( parseFloat( $range.val() ) - min ) / ( max - min ) ) * 100;
				$range.css( '--agf-fill', pct + '%' );
			};
			$range.on( 'input', function () {
				$num.val( this.value ).trigger( 'change' );
				paint();
			} );
			$num.on( 'input change agf:sync', function () {
				if ( this.value !== '' ) {
					$range.val( this.value );
				}
				paint();
			} );
			paint();
		} );

		// Plain ranges (gradient) show their value.
		once( $scope, '.agf-mini-range .agf-range', 'range-output' ).each( function () {
			var $range = $( this );
			var $out = $range.closest( '.agf-mini-range' ).find( 'output' );
			$range.on( 'input agf:sync', function () {
				$out.text( this.value + ( $range.data( 'suffix' ) || '' ) );
			} );
		} );
	};

	$( document ).on( 'click', '.agf-spinner-btn', function () {
		var $input = $( this ).siblings( 'input' );
		var step = parseFloat( $input.attr( 'step' ) ) || 1;
		var min = $input.attr( 'min' ) !== '' ? parseFloat( $input.attr( 'min' ) ) : -Infinity;
		var max = $input.attr( 'max' ) !== '' ? parseFloat( $input.attr( 'max' ) ) : Infinity;
		var val = ( parseFloat( $input.val() ) || 0 ) + step * parseInt( $( this ).data( 'dir' ), 10 );
		val = Math.max( min, Math.min( max, Math.round( val * 1000 ) / 1000 ) );
		$input.val( val ).trigger( 'change' );
	} );

	$( document ).on( 'click', '.agf-link-sides', function () {
		var pressed = $( this ).attr( 'aria-pressed' ) !== 'true';
		$( this ).attr( 'aria-pressed', pressed ? 'true' : 'false' );
		if ( pressed ) {
			var $inputs = $( this ).closest( '.agf-sides' ).find( '.agf-side-input' );
			$inputs.val( $inputs.first().val() ).trigger( 'change' );
		}
	} );

	$( document ).on( 'input', '.agf-side-input', function () {
		var $sides = $( this ).closest( '.agf-sides' );
		if ( $sides.find( '.agf-link-sides' ).attr( 'aria-pressed' ) === 'true' ) {
			$sides.find( '.agf-side-input' ).not( this ).val( this.value );
		}
	} );

	/* ---- Gradient & box shadow previews ---- */
	AGF.fields.previews = function ( $scope ) {
		once( $scope, '.agf-gradient', 'gradient' ).each( function () {
			var $wrap = $( this );
			var $preview = $wrap.find( '.agf-gradient-preview' );
			var val = function ( key ) {
				return $wrap.find( '[name$="[' + key + ']"]' ).val();
			};
			var paint = function () {
				var stops = ( val( 'start' ) || 'transparent' ) + ' ' + ( val( 'start_pos' ) || 0 ) + '%, ' + ( val( 'end' ) || 'transparent' ) + ' ' + ( val( 'end_pos' ) || 100 ) + '%';
				$preview.css( 'background-image', val( 'type' ) === 'radial' ? 'radial-gradient(circle, ' + stops + ')' : 'linear-gradient(' + ( val( 'angle' ) || 0 ) + 'deg, ' + stops + ')' );
			};
			$wrap.on( 'input change agf:sync', ':input', paint );
			paint();
		} );

		once( $scope, '.agf-box-shadow', 'shadow' ).each( function () {
			var $wrap = $( this );
			var $preview = $wrap.find( '.agf-box-shadow-preview' );
			var paint = function () {
				var get = function ( key ) {
					return parseFloat( $wrap.find( '[data-key="' + key + '"]' ).val() ) || 0;
				};
				var color = $wrap.find( '.agf-color-text' ).val();
				var inset = $wrap.find( '[data-key="inset"]' ).is( ':checked' ) ? 'inset ' : '';
				$preview.css( 'box-shadow', color ? inset + get( 'x' ) + 'px ' + get( 'y' ) + 'px ' + get( 'blur' ) + 'px ' + get( 'spread' ) + 'px ' + color : 'none' );
			};
			$wrap.on( 'input change agf:sync', ':input', paint );
			paint();
		} );
	};

	/* ---- Typography ---- */
	var loadedFonts = {};

	function fontsDatalist() {
		if ( document.getElementById( 'agf-fonts-list' ) ) {
			return;
		}
		var fonts = data().fonts || { websafe: {}, google: {} };
		var html = '<datalist id="agf-fonts-list">';
		Object.keys( fonts.websafe || {} ).forEach( function ( name ) {
			html += '<option value="' + escapeHtml( name ) + '">System</option>';
		} );
		Object.keys( fonts.google || {} ).forEach( function ( name ) {
			html += '<option value="' + escapeHtml( name ) + '">Google · ' + escapeHtml( fonts.google[ name ].category ) + '</option>';
		} );
		$( 'body' ).append( html + '</datalist>' );
	}

	function loadGoogleFont( family ) {
		var fonts = data().fonts || {};
		var info = fonts.google && fonts.google[ family ];
		if ( ! info || loadedFonts[ family ] ) {
			return;
		}
		loadedFonts[ family ] = true;
		$( '<link rel="stylesheet">' ).attr( 'href', 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent( family ).replace( /%20/g, '+' ) + ':wght@' + info.weights.join( ';' ) + '&display=swap' ).appendTo( 'head' );
	}

	AGF.fields.typography = function ( $scope ) {
		var $items = once( $scope, '.agf-typography', 'typography' );
		if ( ! $items.length ) {
			return;
		}
		fontsDatalist();

		$items.each( function () {
			var $wrap = $( this );
			var $preview = $wrap.find( '.agf-typography-preview' );
			var get = function ( key ) {
				return $wrap.find( '[name$="[' + key + ']"]' ).val() || '';
			};

			var paint = function () {
				var family = get( 'font-family' );
				var fonts = data().fonts || {};
				var info = fonts.google && fonts.google[ family ];

				$wrap.find( '.agf-font-weight option' ).each( function () {
					this.disabled = !! ( info && this.value && info.weights.indexOf( parseInt( this.value, 10 ) ) === -1 );
				} );

				if ( ! $preview.length ) {
					return;
				}
				if ( info ) {
					loadGoogleFont( family );
				}
				var withUnit = function ( key, unitKey ) {
					var v = get( key );
					return v === '' ? '' : v + get( unitKey );
				};
				$preview.css( {
					fontFamily: family ? '"' + family + '"' : '',
					fontWeight: get( 'font-weight' ),
					fontStyle: get( 'font-style' ),
					fontSize: withUnit( 'font-size', 'unit' ),
					lineHeight: withUnit( 'line-height', 'line-height-unit' ),
					letterSpacing: withUnit( 'letter-spacing', 'letter-spacing-unit' ),
					textAlign: get( 'text-align' ),
					textTransform: get( 'text-transform' ),
					textDecoration: get( 'text-decoration' ),
					color: get( 'color' )
				} );
			};

			$wrap.on( 'input change agf:sync', ':input', debounce( paint, 120 ) );
			paint();
		} );
	};

	/* ---- Enhanced select (search, multiple, ajax) ---- */
	function SmartSelect( select ) {
		var self = this;
		this.$select = $( select ).addClass( 'agf-ss-native' );
		this.multiple = select.multiple;
		this.search = this.$select.attr( 'data-search' ) === '1';
		this.query = this.$select.attr( 'data-query' );
		this.placeholder = this.$select.attr( 'data-placeholder' ) || ( this.multiple ? '' : '—' );
		this.results = null;

		this.$ui = $( '<div class="agf-ss' + ( this.multiple ? ' agf-ss-multi' : '' ) + '"><div class="agf-ss-control" tabindex="0" role="combobox" aria-expanded="false"></div><div class="agf-ss-dropdown" hidden>' + ( this.search ? '<div class="agf-ss-search-wrap"><input type="text" class="agf-ss-search" autocomplete="off"></div>' : '' ) + '<ul class="agf-ss-options" role="listbox"></ul></div></div>' );
		this.$control = this.$ui.find( '.agf-ss-control' );
		this.$dropdown = this.$ui.find( '.agf-ss-dropdown' );
		this.$list = this.$ui.find( '.agf-ss-options' );
		this.$input = this.$ui.find( '.agf-ss-search' ).attr( 'placeholder', L.typeToSearch || 'Search…' );
		this.$select.after( this.$ui );

		this.renderControl();

		this.$control.on( 'click', function ( e ) {
			if ( $( e.target ).closest( '.agf-ss-tag-remove' ).length ) {
				return;
			}
			self.toggle();
		} );
		this.$control.on( 'keydown', function ( e ) {
			if ( e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown' ) {
				e.preventDefault();
				self.open();
			}
		} );
		this.$control.on( 'click', '.agf-ss-tag-remove', function () {
			self.toggleValue( $( this ).closest( '.agf-ss-tag' ).data( 'value' ) );
		} );
		this.$list.on( 'mousedown', 'li[data-value]', function ( e ) {
			e.preventDefault();
			self.pick( $( this ) );
		} );
		this.$list.on( 'mousemove', 'li[data-value]', function () {
			self.$list.children( '.agf-hl' ).removeClass( 'agf-hl' );
			$( this ).addClass( 'agf-hl' );
		} );
		this.$input.on( 'input', debounce( function () {
			self.filter( self.$input.val() );
		}, this.query ? 300 : 0 ) );
		this.$ui.on( 'keydown', function ( e ) {
			self.keys( e );
		} );
		this.$select.on( 'change agf:sync', function () {
			self.renderControl();
		} );
	}

	SmartSelect.prototype = {
		options: function () {
			var items = [];
			this.$select.children().each( function () {
				if ( this.tagName === 'OPTGROUP' ) {
					items.push( { group: this.label } );
					$( this ).children( 'option' ).each( function () {
						items.push( { value: this.value, label: this.text } );
					} );
				} else if ( this.value !== '' ) {
					items.push( { value: this.value, label: this.text } );
				}
			} );
			return items;
		},

		selected: function () {
			var val = this.$select.val();
			return ( $.isArray( val ) ? val : ( val ? [ val ] : [] ) ).map( String );
		},

		renderControl: function () {
			var self = this;
			var sel = this.selected();
			this.$control.empty();

			if ( this.multiple ) {
				sel.forEach( function ( v ) {
					var label = self.$select.find( 'option' ).filter( function () {
						return this.value === v;
					} ).text();
					self.$control.append( $( '<span class="agf-ss-tag"></span>' ).attr( 'data-value', v ).text( label ).append( '<button type="button" class="agf-ss-tag-remove" aria-label="Remove">&times;</button>' ) );
				} );
				if ( ! sel.length ) {
					this.$control.append( $( '<span class="agf-ss-placeholder"></span>' ).text( this.placeholder || ( L.typeToSearch || '' ) ) );
				}
			} else {
				var text = sel.length ? this.$select.find( 'option:selected' ).text() : this.placeholder;
				this.$control.append( $( '<span class="agf-ss-value"></span>' ).toggleClass( 'agf-ss-placeholder', ! sel.length ).text( text ) );
			}
			this.$control.append( '<span class="agf-ss-arrow" aria-hidden="true"></span>' );
		},

		renderList: function ( items ) {
			var sel = this.selected();
			var html = '';
			items.forEach( function ( item ) {
				if ( item.group !== undefined ) {
					html += '<li class="agf-ss-group">' + escapeHtml( item.group ) + '</li>';
				} else {
					html += '<li role="option" data-value="' + escapeHtml( item.value ) + '" class="' + ( sel.indexOf( String( item.value ) ) > -1 ? 'agf-selected' : '' ) + '">' + escapeHtml( item.label ) + '</li>';
				}
			} );
			if ( ! html ) {
				html = '<li class="agf-ss-empty">' + escapeHtml( this.query && ! this.$input.val() ? ( L.typeToSearch || '' ) : ( L.noResults || 'No results' ) ) + '</li>';
			}
			this.$list.html( html );
			this.$list.children( '[data-value]' ).first().addClass( 'agf-hl' );
		},

		filter: function ( term ) {
			var self = this;
			term = String( term || '' ).toLowerCase().trim();

			if ( this.query ) {
				if ( ! term ) {
					this.renderList( this.options().filter( function ( o ) {
						return o.value !== undefined && self.selected().indexOf( o.value ) > -1;
					} ) );
					return;
				}
				this.$list.html( '<li class="agf-ss-empty">' + escapeHtml( L.searching || 'Searching…' ) + '</li>' );
				$.post( L.ajaxUrl, {
					action: 'appglut_query',
					nonce: L.queryNonce,
					query: this.query,
					sig: this.$select.attr( 'data-sig' ),
					term: term
				} ).done( function ( res ) {
					self.results = res && res.success ? res.data : [];
					self.renderList( self.results );
				} ).fail( function () {
					self.renderList( [] );
				} );
				return;
			}

			var items = this.options();
			if ( term ) {
				items = items.filter( function ( o ) {
					return o.group === undefined && o.label.toLowerCase().indexOf( term ) > -1;
				} );
			}
			this.renderList( items );
		},

		open: function () {
			if ( ! this.$dropdown.prop( 'hidden' ) ) {
				return;
			}
			$( '.agf-ss-open' ).not( this.$ui ).each( function () {
				$( this ).data( 'agfSS' ).close();
			} );
			this.$ui.data( 'agfSS', this ).addClass( 'agf-ss-open' );
			this.$control.attr( 'aria-expanded', 'true' );
			this.$dropdown.prop( 'hidden', false );
			this.$input.val( '' );
			this.filter( '' );
			if ( this.search ) {
				this.$input.trigger( 'focus' );
			}
			var self = this;
			$( document ).on( 'mousedown.agfss', function ( e ) {
				if ( ! $( e.target ).closest( self.$ui ).length ) {
					self.close();
				}
			} );
		},

		close: function () {
			this.$ui.removeClass( 'agf-ss-open' );
			this.$control.attr( 'aria-expanded', 'false' );
			this.$dropdown.prop( 'hidden', true );
			$( document ).off( 'mousedown.agfss' );
		},

		toggle: function () {
			if ( this.$dropdown.prop( 'hidden' ) ) {
				this.open();
			} else {
				this.close();
			}
		},

		ensureOption: function ( value, label ) {
			var exists = this.$select.find( 'option' ).filter( function () {
				return this.value === String( value );
			} ).length;
			if ( ! exists ) {
				this.$select.append( $( '<option></option>' ).val( value ).text( label ) );
			}
		},

		toggleValue: function ( value ) {
			value = String( value );
			var sel = this.selected();
			var idx = sel.indexOf( value );
			if ( this.multiple ) {
				if ( idx > -1 ) {
					sel.splice( idx, 1 );
				} else {
					sel.push( value );
				}
				this.$select.val( sel );
			} else {
				this.$select.val( value );
			}
			this.$select.trigger( 'change' );
		},

		pick: function ( $li ) {
			var value = String( $li.data( 'value' ) );
			this.ensureOption( value, $li.text() );
			this.toggleValue( value );
			if ( this.multiple ) {
				$li.toggleClass( 'agf-selected' );
				this.$input.trigger( 'focus' );
			} else {
				this.close();
				this.$control.trigger( 'focus' );
			}
		},

		keys: function ( e ) {
			if ( this.$dropdown.prop( 'hidden' ) ) {
				return;
			}
			var $items = this.$list.children( '[data-value]' );
			var $hl = $items.filter( '.agf-hl' );
			var idx = $items.index( $hl );
			if ( e.key === 'ArrowDown' || e.key === 'ArrowUp' ) {
				e.preventDefault();
				idx = e.key === 'ArrowDown' ? Math.min( $items.length - 1, idx + 1 ) : Math.max( 0, idx - 1 );
				$hl.removeClass( 'agf-hl' );
				var $next = $items.eq( idx ).addClass( 'agf-hl' );
				if ( $next.length ) {
					$next[ 0 ].scrollIntoView( { block: 'nearest' } );
				}
			} else if ( e.key === 'Enter' ) {
				e.preventDefault();
				if ( $hl.length ) {
					this.pick( $hl );
				}
			} else if ( e.key === 'Escape' ) {
				e.stopPropagation();
				this.close();
				this.$control.trigger( 'focus' );
			} else if ( e.key === 'Tab' ) {
				this.close();
			}
		}
	};

	AGF.fields.select = function ( $scope ) {
		once( $scope, 'select.agf-select-enhance', 'select' ).each( function () {
			new SmartSelect( this ); // eslint-disable-line no-new
		} );
	};

	/* ---- Tags ---- */
	function addTag( $wrap, text ) {
		text = String( text || '' ).trim();
		var max = parseInt( $wrap.data( 'max' ), 10 ) || 0;
		var $tags = $wrap.find( '.agf-tag' );
		if ( ! text ) {
			return;
		}
		if ( max && $tags.length >= max ) {
			AGF.toast( L.maxItems || 'Limit reached', 'info', themeOf( $wrap ) );
			return;
		}
		var exists = $tags.find( 'input' ).filter( function () {
			return this.value.toLowerCase() === text.toLowerCase();
		} ).length;
		if ( exists ) {
			return;
		}
		var $tag = $( '<span class="agf-tag"><span></span><input type="hidden"><button type="button" class="agf-tag-remove" aria-label="Remove">&times;</button></span>' );
		$tag.find( 'span' ).first().text( text );
		$tag.find( 'input' ).attr( 'name', $wrap.attr( 'data-name' ) ).val( text );
		$wrap.find( '.agf-tags-input' ).before( $tag );
		$tag.find( 'input' ).trigger( 'change' );
	}

	$( document ).on( 'keydown', '.agf-tags-input', function ( e ) {
		var $wrap = $( this ).closest( '.agf-tags' );
		if ( e.key === 'Enter' || e.key === ',' ) {
			e.preventDefault();
			addTag( $wrap, this.value );
			this.value = '';
		} else if ( e.key === 'Backspace' && ! this.value ) {
			$wrap.find( '.agf-tag' ).last().remove();
			$wrap.trigger( 'change' );
		}
	} );
	$( document ).on( 'blur', '.agf-tags-input', function () {
		if ( this.value ) {
			addTag( $( this ).closest( '.agf-tags' ), this.value );
			this.value = '';
		}
	} );
	$( document ).on( 'paste', '.agf-tags-input', function ( e ) {
		var text = ( e.originalEvent.clipboardData || window.clipboardData ).getData( 'text' );
		if ( text.indexOf( ',' ) > -1 ) {
			e.preventDefault();
			var $wrap = $( this ).closest( '.agf-tags' );
			text.split( ',' ).forEach( function ( t ) {
				addTag( $wrap, t );
			} );
		}
	} );
	$( document ).on( 'click', '.agf-tag-remove', function () {
		var $wrap = $( this ).closest( '.agf-tags' );
		$( this ).closest( '.agf-tag' ).remove();
		$wrap.trigger( 'change' );
	} );
	$( document ).on( 'click', '.agf-tags', function ( e ) {
		if ( e.target === this ) {
			$( this ).find( '.agf-tags-input' ).trigger( 'focus' );
		}
	} );

	/* ---- Rating: clicking the current star clears it ---- */
	$( document ).on( 'mousedown', '.agf-rating label', function () {
		this.agfWasChecked = $( this ).find( 'input' ).is( ':checked' );
	} );
	$( document ).on( 'click', '.agf-rating label', function ( e ) {
		if ( this.agfWasChecked ) {
			e.preventDefault();
			$( this ).closest( '.agf-rating' ).find( '.agf-rating-none' ).prop( 'checked', true ).trigger( 'change' );
			this.agfWasChecked = false;
		}
	} );

	/* ---- Media / upload / gallery ---- */
	function mediaFrame( opts ) {
		if ( ! window.wp || ! wp.media ) {
			AGF.toast( 'Media library is not available on this screen.', 'error' );
			return null;
		}
		return wp.media( opts );
	}

	function bestSize( attachment ) {
		var sizes = attachment.sizes || {};
		var size = sizes.medium || sizes.thumbnail || sizes.full;
		return size ? size.url : ( attachment.type === 'image' ? attachment.url : '' );
	}

	$( document ).on( 'click', '.agf-media-select', function () {
		var $media = $( this ).closest( '.agf-media' );
		var library = $media.data( 'library' );
		var frame = mediaFrame( {
			title: library === 'image' ? L.selectImage : L.selectFile,
			button: { text: L.useThis },
			library: library ? { type: library } : {},
			multiple: false
		} );
		if ( ! frame ) {
			return;
		}
		frame.on( 'select', function () {
			var a = frame.state().get( 'selection' ).first().toJSON();
			var thumb = bestSize( a );
			$media.find( '.agf-media-url' ).val( a.url );
			$media.find( '.agf-media-id' ).val( a.id );
			$media.find( '.agf-media-extra' ).each( function () {
				var key = $( this ).data( 'key' );
				$( this ).val( key === 'thumbnail' ? thumb : ( a[ key ] !== undefined ? a[ key ] : '' ) );
			} );
			var $preview = $media.find( '.agf-media-preview' ).removeClass( 'agf-is-empty' ).empty();
			if ( thumb ) {
				$preview.append( $( '<img alt="">' ).attr( 'src', thumb ) );
			} else {
				$preview.append( $( '<span class="agf-media-file"><i class="dashicons dashicons-media-default"></i><span></span></span>' ).find( 'span' ).text( a.filename ).end() );
			}
			$media.find( '.agf-media-remove' ).prop( 'hidden', false );
			$media.find( '.agf-media-url' ).trigger( 'change' );
		} );
		frame.open();
	} );

	$( document ).on( 'click', '.agf-media-remove', function () {
		var $media = $( this ).closest( '.agf-media' );
		$media.find( '.agf-media-url, .agf-media-id, .agf-media-extra' ).val( '' );
		$media.find( '.agf-media-preview' ).addClass( 'agf-is-empty' ).html( '<span class="agf-media-placeholder"><i class="dashicons dashicons-format-image"></i></span>' );
		$( this ).prop( 'hidden', true );
		$media.find( '.agf-media-url' ).trigger( 'change' );
	} );

	$( document ).on( 'change', '.agf-media-url[type=url]', function () {
		var $media = $( this ).closest( '.agf-media' );
		var url = this.value;
		var $preview = $media.find( '.agf-media-preview' );
		if ( url && /\.(jpe?g|png|gif|webp|avif|svg)(\?.*)?$/i.test( url ) && ! $preview.find( 'img' ).length ) {
			$preview.removeClass( 'agf-is-empty' ).html( $( '<img alt="">' ).attr( 'src', url ) );
			$media.find( '.agf-media-remove' ).prop( 'hidden', false );
		}
	} );

	$( document ).on( 'click', '.agf-upload-btn', function () {
		var $wrap = $( this ).closest( '.agf-upload' );
		var library = $wrap.data( 'library' );
		var frame = mediaFrame( {
			title: L.selectFile,
			button: { text: L.useThis },
			library: library ? { type: library } : {},
			multiple: false
		} );
		if ( ! frame ) {
			return;
		}
		frame.on( 'select', function () {
			$wrap.find( '.agf-upload-url' ).val( frame.state().get( 'selection' ).first().toJSON().url ).trigger( 'change' );
		} );
		frame.open();
	} );

	function galleryUpdate( $gallery ) {
		var ids = $gallery.find( '.agf-gallery-list li' ).map( function () {
			return $( this ).data( 'id' );
		} ).get();
		$gallery.find( '.agf-gallery-ids' ).val( ids.join( ',' ) ).trigger( 'change' );
		$gallery.find( '.agf-gallery-list' ).toggleClass( 'agf-is-empty', ! ids.length );
		$gallery.find( '.agf-gallery-clear' ).prop( 'hidden', ! ids.length );
	}

	AGF.fields.gallery = function ( $scope ) {
		once( $scope, '.agf-gallery-list', 'gallery' ).each( function () {
			if ( $.fn.sortable ) {
				$( this ).sortable( {
					items: '> li',
					tolerance: 'pointer',
					placeholder: 'agf-gallery-placeholder',
					stop: function () {
						galleryUpdate( $( this ).closest( '.agf-gallery' ) );
					}
				} );
			}
		} );
	};

	$( document ).on( 'click', '.agf-gallery-add', function () {
		var $gallery = $( this ).closest( '.agf-gallery' );
		var frame = mediaFrame( {
			title: L.addImages,
			button: { text: L.addImages },
			library: { type: 'image' },
			multiple: 'add'
		} );
		if ( ! frame ) {
			return;
		}
		frame.on( 'open', function () {
			var selection = frame.state().get( 'selection' );
			String( $gallery.find( '.agf-gallery-ids' ).val() ).split( ',' ).forEach( function ( id ) {
				if ( id ) {
					var att = wp.media.attachment( id );
					att.fetch();
					selection.add( att );
				}
			} );
		} );
		frame.on( 'select', function () {
			var $list = $gallery.find( '.agf-gallery-list' ).empty();
			frame.state().get( 'selection' ).each( function ( model ) {
				var a = model.toJSON();
				var src = ( a.sizes && a.sizes.thumbnail ) ? a.sizes.thumbnail.url : a.url;
				$list.append( $( '<li><img alt=""><button type="button" class="agf-gallery-remove" aria-label="Remove">&times;</button></li>' ).attr( 'data-id', a.id ).find( 'img' ).attr( 'src', src ).end() );
			} );
			galleryUpdate( $gallery );
		} );
		frame.open();
	} );

	$( document ).on( 'click', '.agf-gallery-remove', function () {
		var $gallery = $( this ).closest( '.agf-gallery' );
		$( this ).closest( 'li' ).remove();
		galleryUpdate( $gallery );
	} );

	$( document ).on( 'click', '.agf-gallery-clear', function () {
		var $gallery = $( this ).closest( '.agf-gallery' );
		$gallery.find( '.agf-gallery-list' ).empty();
		galleryUpdate( $gallery );
	} );

	/* ---- Icon picker ----
	 * Icons come from a registered SVG set (data-set), a custom class list
	 * (data-icons) or the bundled Dashicons list.
	 */
	function iconItems( $picker ) {
		var set = $picker.attr( 'data-set' );
		if ( set ) {
			return ( ( data().iconSets || {} )[ set ] || [] ).map( function ( row ) {
				return { key: row[ 0 ], label: row[ 1 ], html: row[ 2 ], group: row[ 3 ] || '' };
			} );
		}
		var list = $picker.data( 'icons' ) || data().icons || [];
		return list.map( function ( icon ) {
			var cls = /^dashicons-/.test( icon ) ? 'dashicons ' + icon : icon;
			return { key: icon, label: icon.replace( /^dashicons-/, '' ).replace( /[-_]/g, ' ' ), html: '<i class="agf-icon ' + escapeHtml( cls ) + '"></i>' };
		} );
	}

	$( document ).on( 'click', '.agf-icon-select', function () {
		var $picker = $( this ).closest( '.agf-icon-picker' );
		var items = iconItems( $picker );
		var current = $picker.find( '.agf-icon-value' ).val();

		var $body = $( '<div class="agf-icon-browser"><div class="agf-search agf-search-block"><i class="dashicons dashicons-search"></i><input type="search" class="agf-search-input"></div><div class="agf-icon-groups" hidden></div><div class="agf-icon-grid"></div><p class="agf-icon-count"></p></div>' );
		var $grid = $body.find( '.agf-icon-grid' ).toggleClass( 'agf-icon-grid--lg', $picker.hasClass( 'agf-icon-picker--lg' ) );
		var html = '';
		items.forEach( function ( it ) {
			html += '<button type="button" class="agf-icon-cell' + ( it.key === current ? ' agf-active' : '' ) + '" data-icon="' + escapeHtml( it.key ) + '" data-group="' + escapeHtml( it.group ) + '" data-label="' + escapeHtml( String( it.label ).toLowerCase() ) + '" title="' + escapeHtml( it.label ) + '">' + it.html + '<span class="agf-icon-cell-label">' + escapeHtml( it.label ) + '</span></button>';
		} );
		$grid.html( html );
		$body.find( '.agf-icon-count' ).text( items.length + ' icons' );

		// Group tabs (only for sets whose icons have a group). Start on the group of the current icon.
		var groups = [];
		items.forEach( function ( it ) {
			if ( it.group && groups.indexOf( it.group ) === -1 ) {
				groups.push( it.group );
			}
		} );
		var activeGroup = '';
		if ( groups.length > 1 ) {
			var currentItem = items.filter( function ( i ) {
				return String( i.key ) === String( current );
			} )[ 0 ];
			activeGroup = currentItem && currentItem.group ? currentItem.group : groups[ 0 ];
			var tabs = '<button type="button" class="agf-icon-group" data-group="">' + escapeHtml( L.all || 'All' ) + '</button>';
			groups.forEach( function ( name ) {
				tabs += '<button type="button" class="agf-icon-group" data-group="' + escapeHtml( name ) + '">' + escapeHtml( name ) + '</button>';
			} );
			$body.find( '.agf-icon-groups' ).html( tabs ).prop( 'hidden', false );
		}

		var m = modal( { title: $picker.attr( 'data-title' ) || 'Icons', body: $body, size: 'lg', theme: themeOf( $picker ) } );
		// Use the accent of the place the picker lives in (e.g. the customizer's admin color).
		var accent = window.getComputedStyle( $picker[ 0 ] ).getPropertyValue( '--agf-accent' ).trim();
		if ( accent ) {
			m.$modal[ 0 ].style.setProperty( '--agf-accent', accent );
		}

		var $search = $body.find( 'input' );
		function filterIcons() {
			var term = $search.val().toLowerCase().trim();
			var shown = 0;
			// A search looks in every group; otherwise only the open tab is shown.
			$grid.children().each( function () {
				var match = term
					? this.getAttribute( 'data-label' ).indexOf( term ) > -1 || this.getAttribute( 'data-icon' ).toLowerCase().indexOf( term ) > -1
					: ! activeGroup || this.getAttribute( 'data-group' ) === activeGroup;
				this.hidden = ! match;
				shown += match ? 1 : 0;
			} );
			$body.find( '.agf-icon-group' ).each( function () {
				this.classList.toggle( 'agf-active', ! term && this.getAttribute( 'data-group' ) === activeGroup );
			} );
			$body.find( '.agf-icon-count' ).text( shown + ' icons' );
		}
		$body.on( 'click', '.agf-icon-group', function () {
			activeGroup = this.getAttribute( 'data-group' );
			$search.val( '' );
			filterIcons();
			$grid.closest( '.agf-modal-body' ).scrollTop( 0 );
		} );
		$search.attr( 'placeholder', L.typeToSearch || 'Search…' ).on( 'input', filterIcons ).trigger( 'focus' );
		filterIcons();

		var $active = $grid.find( '.agf-active' );
		if ( $active.length ) {
			setTimeout( function () {
				$active[ 0 ].scrollIntoView( { block: 'center' } );
			}, 60 );
		}

		$grid.on( 'click', '.agf-icon-cell', function () {
			var key = $( this ).data( 'icon' );
			var it = items.filter( function ( i ) {
				return String( i.key ) === String( key );
			} )[ 0 ];
			$picker.find( '.agf-icon-value' ).val( key ).trigger( 'change' );
			$picker.find( '.agf-icon-preview' ).removeClass( 'agf-is-empty' ).html( it ? it.html : '' );
			$picker.find( '.agf-icon-name' ).text( it ? it.label : key );
			$picker.find( '.agf-btn.agf-icon-select' ).text( L.change || 'Change' );
			$picker.find( '.agf-icon-remove' ).prop( 'hidden', false );
			m.close();
		} );
	} );

	$( document ).on( 'click', '.agf-icon-remove', function () {
		var $picker = $( this ).closest( '.agf-icon-picker' );
		$picker.find( '.agf-icon-value' ).val( '' ).trigger( 'change' );
		$picker.find( '.agf-icon-preview' ).addClass( 'agf-is-empty' ).empty();
		$picker.find( '.agf-icon-name' ).text( '' );
		$( this ).prop( 'hidden', true );
	} );

	/* ---- Map preview ---- */
	AGF.fields.map = function ( $scope ) {
		once( $scope, '.agf-map', 'map' ).each( function () {
			var $map = $( this );
			var $frame = $map.find( '.agf-map-frame' );
			var paint = function () {
				var lat = parseFloat( $map.find( '.agf-map-lat' ).val() );
				var lng = parseFloat( $map.find( '.agf-map-lng' ).val() );
				var zoom = parseInt( $map.find( '.agf-map-zoom' ).val(), 10 ) || 12;
				if ( isNaN( lat ) || isNaN( lng ) ) {
					$frame.find( 'iframe' ).remove();
					$frame.find( '.agf-map-empty' ).prop( 'hidden', false );
					return;
				}
				var d = 180 / Math.pow( 2, zoom );
				var src = 'https://www.openstreetmap.org/export/embed.html?bbox=' + [ lng - d, lat - d / 2, lng + d, lat + d / 2 ].join( ',' ) + '&layer=mapnik&marker=' + lat + ',' + lng;
				var $iframe = $frame.find( 'iframe' );
				if ( ! $iframe.length ) {
					$iframe = $( '<iframe loading="lazy" referrerpolicy="no-referrer" title="Map"></iframe>' ).appendTo( $frame );
				}
				$iframe.attr( 'src', src );
				$frame.find( '.agf-map-empty' ).prop( 'hidden', true );
			};
			$map.on( 'input change agf:sync', 'input', debounce( paint, 600 ) );
			paint();
		} );
	};

	/* ---- Code editor ---- */
	AGF.fields.code = function ( $scope ) {
		once( $scope, '.agf-code-input', 'code' ).each( function () {
			var ta = this;
			var settings = ( data().code || {} )[ $( ta ).data( 'mode' ) ];
			if ( ! settings || ! window.wp || ! wp.codeEditor ) {
				$( ta ).addClass( 'agf-code-plain' );
				return;
			}
			var conf = $.extend( true, {}, settings );
			conf.codemirror = $.extend( {}, conf.codemirror, { lineNumbers: true, lineWrapping: true, viewportMargin: Infinity } );
			var editor = wp.codeEditor.initialize( ta, conf );
			var height = parseInt( $( ta ).closest( '.agf-code' ).css( '--agf-code-h' ), 10 ) || 260;
			editor.codemirror.setSize( null, height );
			editor.codemirror.on( 'change', function ( cm ) {
				cm.save();
				$( ta ).trigger( 'change' );
			} );
			$( ta ).data( 'agfCm', editor.codemirror );
		} );
	};

	function refreshEditors( $el ) {
		$el.find( '.CodeMirror' ).each( function () {
			if ( this.CodeMirror ) {
				this.CodeMirror.refresh();
			}
		} );
	}

	$( document ).on( 'agf:shown', function ( e ) {
		refreshEditors( $( e.target ) );
	} );

	/* ---- WordPress editor ---- */
	AGF.fields.wpEditor = function ( $scope ) {
		once( $scope, '.agf-wp-editor-input', 'wpeditor' ).each( function () {
			initEditor( this );
		} );
	};

	function initEditor( ta ) {
		if ( ! window.wp || ! wp.editor || ! wp.editor.initialize ) {
			return;
		}
		var s = $( ta ).data( 'editor' ) || {};
		if ( ! ta.id ) {
			ta.id = AGF.uid( 'agf-editor' ).replace( /[^a-z0-9_]/gi, '_' );
		}
		wp.editor.initialize( ta.id, {
			tinymce: s.tinymce ? {
				wpautop: true,
				height: s.height || 220,
				setup: function ( ed ) {
					ed.on( 'change keyup undo redo', function () {
						ed.save();
						$( ta ).trigger( 'change' );
					} );
				}
			} : false,
			quicktags: !! s.quicktags,
			mediaButtons: !! s.mediaButtons
		} );
	}

	function removeEditors( $el ) {
		$el.find( '.agf-wp-editor-input' ).each( function () {
			if ( this.id && window.wp && wp.editor && wp.editor.remove ) {
				if ( window.tinymce && tinymce.get( this.id ) ) {
					tinymce.get( this.id ).save();
				}
				wp.editor.remove( this.id );
				this.agfInit.wpeditor = false;
			}
		} );
	}

	/** Flush rich editors to their textareas before reading values. */
	function flushEditors( $el ) {
		if ( window.tinymce && tinymce.triggerSave ) {
			tinymce.triggerSave();
		}
		$el.find( '.agf-code-input' ).each( function () {
			var cm = $( this ).data( 'agfCm' );
			if ( cm ) {
				cm.save();
			}
		} );
	}

	/* ---- Tabbed ---- */
	$( document ).on( 'click', '.agf-tabbed-tab', function () {
		var $tabbed = $( this ).closest( '.agf-tabbed' );
		var tab = String( $( this ).data( 'tab' ) );
		$tabbed.children( '.agf-tabbed-nav' ).children().removeClass( 'agf-active' );
		$( this ).addClass( 'agf-active' );
		$tabbed.children( '.agf-tabbed-pane' ).each( function () {
			this.hidden = String( $( this ).data( 'tab' ) ) !== tab;
		} );
		$tabbed.children( '.agf-tabbed-pane:not([hidden])' ).trigger( 'agf:shown' );
	} );

	$( document ).on( 'toggle', '.agf-accordion-item', function () {
		if ( this.open ) {
			$( this ).trigger( 'agf:shown' );
		}
	} );

	/* ---- Notice dismiss ---- */
	$( document ).on( 'click', '.agf-notice-close', function () {
		$( this ).closest( '.agf-field' ).slideUp( 150 );
	} );

	/* ---- Sortable & sorter ---- */
	AGF.fields.sortable = function ( $scope ) {
		if ( ! $.fn.sortable ) {
			return;
		}
		once( $scope, '.agf-sortable', 'sortable' ).each( function () {
			$( this ).sortable( {
				items: '> .agf-sortable-item',
				handle: '> .agf-repeater-handle',
				axis: 'y',
				placeholder: 'agf-sort-placeholder',
				forcePlaceholderSize: true,
				stop: function () {
					$( this ).trigger( 'change' );
				}
			} );
		} );

		once( $scope, '.agf-sorter', 'sorter' ).each( function () {
			var $lists = $( this ).find( '.agf-sorter-list' );
			$lists.sortable( {
				connectWith: $lists,
				placeholder: 'agf-sort-placeholder',
				forcePlaceholderSize: true,
				update: function () {
					var $list = $( this );
					$list.children( 'li' ).each( function () {
						$( this ).find( 'input' ).attr( 'name', $list.attr( 'data-name' ) + '[' + $list.data( 'list' ) + '][' + $( this ).data( 'key' ) + ']' );
					} );
					$list.trigger( 'change' );
				}
			} );
		} );
	};

	/* =====================================================================
	 * Repeater / group / key-value
	 * =================================================================== */

	function repItems( $rep ) {
		return $rep.children( '.agf-repeater-items' ).children( '.agf-repeater-item' );
	}

	function replacePrefix( $el, from, to ) {
		var swap = function ( value ) {
			return value && value.indexOf( from ) === 0 ? to + value.slice( from.length ) : value;
		};
		$el.find( '[name]' ).each( function () {
			this.setAttribute( 'name', swap( this.getAttribute( 'name' ) ) );
		} );
		$el.find( '[data-prefix]' ).each( function () {
			this.setAttribute( 'data-prefix', swap( this.getAttribute( 'data-prefix' ) ) );
		} );
		$el.find( '[data-name]' ).each( function () {
			this.setAttribute( 'data-name', swap( this.getAttribute( 'data-name' ) ) );
		} );
		$el.find( 'template' ).each( function () {
			this.innerHTML = this.innerHTML.split( from ).join( to );
		} );
	}

	/** Renumber items 0..n-1 in DOM order (two phases so radio groups never collide). */
	function reindex( $rep ) {
		var prefix = $rep.attr( 'data-prefix' );
		var $items = repItems( $rep );
		var tmp = '__agft' + Date.now().toString( 36 );

		$items.each( function ( i ) {
			var old = this.getAttribute( 'data-index' );
			if ( old !== String( i ) ) {
				replacePrefix( $( this ), prefix + '[' + old + ']', prefix + '[' + tmp + i + ']' );
				this.setAttribute( 'data-index', tmp + i );
			}
		} );
		$items.each( function ( i ) {
			var cur = this.getAttribute( 'data-index' );
			if ( cur === tmp + i ) {
				replacePrefix( $( this ), prefix + '[' + cur + ']', prefix + '[' + i + ']' );
				this.setAttribute( 'data-index', i );
			}
		} );
		repState( $rep );
	}

	function repState( $rep ) {
		var $items = repItems( $rep );
		var max = parseInt( $rep.data( 'max' ), 10 ) || 0;
		$rep.children( '.agf-repeater-empty' ).prop( 'hidden', $items.length > 0 );
		$rep.children( '.agf-repeater-footer' ).find( '.agf-repeater-count' ).text( max ? $items.length + ' / ' + max : '' );
		$rep.children( '.agf-repeater-footer' ).find( '.agf-repeater-add' ).prop( 'disabled', !! ( max && $items.length >= max ) );
		$items.each( function ( i ) {
			$( this ).find( '> .agf-repeater-head .agf-repeater-num' ).text( '#' + ( i + 1 ) );
			repTitle( $rep, $( this ) );
		} );
	}

	function repTitle( $rep, $item ) {
		var field = $rep.attr( 'data-title-field' );
		var $title = $item.find( '> .agf-repeater-head .agf-repeater-title' );
		if ( ! $title.length ) {
			return;
		}
		var value = '';
		if ( field ) {
			var $f = $item.children( '.agf-repeater-body' ).children( '.agf-field[data-field-id="' + field + '"]' );
			value = $f.find( ':input[name]' ).first().val() || '';
		}
		$title.text( value || $title.attr( 'data-fallback' ) || '' ).toggleClass( 'agf-is-fallback', ! value );
	}

	/** New item from the template; its inputs use index = current item count (reindexed later). */
	function newItem( $rep ) {
		var tpl = $rep.children( 'template.agf-repeater-template' )[ 0 ];
		var index = repItems( $rep ).length;
		var html = tpl.innerHTML.split( $rep.attr( 'data-placeholder' ) ).join( String( index ) );
		var $item = $( $.parseHTML( html.trim(), document, true ) ).filter( '.agf-repeater-item' );
		$item.data( 'agfPrefix', $rep.attr( 'data-prefix' ) + '[' + index + ']' );
		return $item;
	}

	/** Make $dst match the nested repeater structure of $src, then copy values. Both are uninitialised. */
	function copyItem( $src, $dst ) {
		var nested = function ( $item ) {
			return $item.find( '.agf-repeater' ).filter( function () {
				return $( this ).parent().closest( '.agf-repeater-item' )[ 0 ] === $item[ 0 ];
			} );
		};
		var $dstNested = nested( $dst );
		nested( $src ).each( function ( i ) {
			var $dRep = $dstNested.eq( i );
			repItems( $( this ) ).each( function () {
				var $child = newItem( $dRep );
				$dRep.children( '.agf-repeater-items' ).append( $child );
				copyItem( $( this ), $child );
			} );
		} );

		var srcPrefix = $src.closest( '.agf-repeater' ).attr( 'data-prefix' ) + '[' + $src.attr( 'data-index' ) + ']';
		var dstPrefix = $dst.data( 'agfPrefix' );
		var map = {};
		$src.find( ':input[name]' ).each( function () {
			var rel = this.name.slice( srcPrefix.length );
			( map[ rel ] = map[ rel ] || [] ).push( this );
		} );

		$dst.find( ':input[name]' ).each( function () {
			var el = this;
			var sources = map[ el.name.slice( dstPrefix.length ) ];
			if ( ! sources ) {
				return;
			}
			if ( el.type === 'checkbox' || el.type === 'radio' ) {
				el.checked = sources.some( function ( s ) {
					return s.value === el.value && s.checked;
				} );
			} else if ( el.tagName === 'SELECT' ) {
				// Copy options too: ajax selects only hold the options picked so far.
				$( el ).html( $( sources[ 0 ] ).html() ).val( $( sources[ 0 ] ).val() );
			} else {
				el.value = sources.shift().value;
			}
		} );

		// Lists built in JS (tags, gallery, media previews, sorter) copy as markup.
		$src.find( '.agf-tags, .agf-gallery-list, .agf-media-preview, .agf-icon-preview' ).each( function ( i ) {
			var $target = $dst.find( '.agf-tags, .agf-gallery-list, .agf-media-preview, .agf-icon-preview' ).eq( i );
			if ( $( this ).hasClass( 'agf-tags' ) ) {
				$target.find( '.agf-tag' ).remove();
				$( this ).find( '.agf-tag' ).each( function () {
					var $tag = $( this ).clone();
					$tag.find( 'input' ).attr( 'name', $target.attr( 'data-name' ) );
					$target.find( '.agf-tags-input' ).before( $tag );
				} );
			} else {
				$target.attr( 'class', this.className ).html( $( this ).html() );
			}
		} );
	}

	$( document ).on( 'click', '.agf-repeater-add', function () {
		var $rep = $( this ).closest( '.agf-repeater' );
		var max = parseInt( $rep.data( 'max' ), 10 ) || 0;
		if ( max && repItems( $rep ).length >= max ) {
			AGF.toast( L.maxItems, 'info', themeOf( $rep ) );
			return;
		}
		var $item = newItem( $rep );
		$rep.children( '.agf-repeater-items' ).append( $item );
		AGF.init( $item );
		reindex( $rep );
		$item.find( ':input:visible' ).first().trigger( 'focus' );
		$rep.trigger( 'change' );
	} );

	$( document ).on( 'click', '.agf-repeater-clone', function () {
		var $src = $( this ).closest( '.agf-repeater-item' );
		var $rep = $src.closest( '.agf-repeater' );
		var max = parseInt( $rep.data( 'max' ), 10 ) || 0;
		if ( max && repItems( $rep ).length >= max ) {
			AGF.toast( L.maxItems, 'info', themeOf( $rep ) );
			return;
		}
		flushEditors( $src );
		var $item = newItem( $rep );
		copyItem( $src, $item );
		$item.removeClass( 'agf-collapsed' );
		$src.after( $item );
		AGF.init( $item );
		reindex( $rep );
		$rep.trigger( 'change' );
	} );

	$( document ).on( 'click', '.agf-repeater-remove', function () {
		var $item = $( this ).closest( '.agf-repeater-item' );
		var $rep = $item.closest( '.agf-repeater' );
		var min = parseInt( $rep.data( 'min' ), 10 ) || 0;
		if ( min && repItems( $rep ).length <= min ) {
			AGF.toast( L.minItems, 'info', themeOf( $rep ) );
			return;
		}
		removeEditors( $item );
		$item.addClass( 'agf-removing' );
		setTimeout( function () {
			$item.remove();
			reindex( $rep );
			$rep.trigger( 'change' );
		}, 160 );
	} );

	$( document ).on( 'click', '.agf-repeater-toggle', function () {
		var $item = $( this ).closest( '.agf-repeater-item' ).toggleClass( 'agf-collapsed' );
		if ( ! $item.hasClass( 'agf-collapsed' ) ) {
			$item.trigger( 'agf:shown' );
		}
	} );

	$( document ).on( 'input change', '.agf-repeater-body :input', function () {
		var $item = $( this ).closest( '.agf-repeater-item' );
		repTitle( $item.closest( '.agf-repeater' ), $item );
	} );

	AGF.fields.repeater = function ( $scope ) {
		once( $scope, '.agf-repeater', 'repeater' ).each( function () {
			var $rep = $( this );
			repState( $rep );
			if ( $rep.attr( 'data-sortable' ) === '1' && $.fn.sortable ) {
				$rep.children( '.agf-repeater-items' ).sortable( {
					items: '> .agf-repeater-item',
					handle: '> .agf-repeater-head > .agf-repeater-handle',
					axis: 'y',
					placeholder: 'agf-sort-placeholder',
					forcePlaceholderSize: true,
					start: function ( e, ui ) {
						removeEditors( ui.item );
					},
					stop: function ( e, ui ) {
						reindex( $rep );
						AGF.fields.wpEditor( ui.item );
						refreshEditors( ui.item );
						$rep.trigger( 'change' );
					}
				} );
			}
		} );
	};

	/* =====================================================================
	 * Init
	 * =================================================================== */

	AGF.init = function ( $scope ) {
		$scope = $( $scope );
		$.each( AGF.fields, function ( name, fn ) {
			try {
				fn( $scope );
			} catch ( err ) {
				if ( window.console ) {
					window.console.error( '[AppGlut] field init "' + name + '" failed', err );
				}
			}
		} );
		var $app = $scope.closest( '.agf-app' ).add( $scope.find( '.agf-app' ) );
		$app.each( function () {
			bindDependencies( $( this ) );
			AGF.runDependencies( $( this ) );
		} );
		$scope.trigger( 'agf:init' );
	};

	/* =====================================================================
	 * Panel shell (options pages & metabox tabs)
	 * =================================================================== */

	function storageGet( key ) {
		try {
			return window.localStorage.getItem( key );
		} catch ( e ) {
			return null;
		}
	}

	function storageSet( key, value ) {
		try {
			window.localStorage.setItem( key, value );
		} catch ( e ) {}
	}

	function showSection( $app, id, skipHash ) {
		var $panels = $app.find( '.agf-panel' );
		var $panel = $panels.filter( '[data-section="' + id + '"]' );
		if ( ! $panel.length ) {
			return;
		}
		$panels.prop( 'hidden', true );
		$panel.prop( 'hidden', false );
		$app.attr( 'data-active', id );

		var $links = $app.find( '.agf-nav-link' );
		$links.removeClass( 'agf-active' );
		var $link = $links.filter( '[data-nav-id="' + id + '"]' ).addClass( 'agf-active' );
		$app.find( '.agf-nav-item.agf-has-children' ).each( function () {
			var contains = $( this ).find( $link ).length > 0;
			$( this ).toggleClass( 'agf-open', contains || ( $( this ).hasClass( 'agf-open' ) && ! $app.is( '.agf-mode-metabox' ) ) );
			$( this ).toggleClass( 'agf-child-active', contains && ! $link.parent().is( this ) );
		} );

		$app.removeClass( 'agf-nav-open' );
		$panel.trigger( 'agf:shown' );

		if ( $app.is( '.agf-mode-options' ) ) {
			storageSet( 'agf-tab-' + $app.data( 'unique' ), id );
			if ( ! skipHash && window.history && history.replaceState ) {
				history.replaceState( null, '', '#tab=' + id );
			}
			var main = $app.find( '.agf-main' )[ 0 ];
			if ( main && ! skipHash ) {
				var top = $app.offset().top - 40;
				if ( window.scrollY > top ) {
					window.scrollTo( { top: top, behavior: 'smooth' } );
				}
			}
		}
	}

	function setDirty( $app, dirty ) {
		$app.toggleClass( 'agf-dirty', dirty );
		$app.find( '.agf-status' ).attr( 'data-state', dirty ? 'dirty' : 'clean' );
	}

	function setBusy( $app, busy ) {
		$app.toggleClass( 'agf-busy', busy );
		$app.find( '.agf-save' ).prop( 'disabled', busy );
	}

	function request( $app, action, extra ) {
		return $.ajax( {
			url: L.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: $.extend( { action: 'appglut_' + action, unique: $app.data( 'unique' ), nonce: $app.data( 'nonce' ) }, extra || {} )
		} );
	}

	function failMessage( xhr ) {
		return ( xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ) || L.error;
	}

	function showErrors( $app, errors ) {
		$app.find( '.agf-field.agf-has-error' ).removeClass( 'agf-has-error' ).find( '> .agf-field-body > .agf-error-text' ).prop( 'hidden', true ).text( '' );
		$app.find( '.agf-nav-link.agf-has-error' ).removeClass( 'agf-has-error' );
		var first = null;
		$.each( errors || {}, function ( id, message ) {
			$app.find( '.agf-field[data-field-id="' + id + '"]' ).each( function () {
				$( this ).addClass( 'agf-has-error' ).find( '> .agf-field-body > .agf-error-text' ).text( message ).prop( 'hidden', false );
				var section = $( this ).closest( '.agf-panel' ).data( 'section' );
				$app.find( '.agf-nav-link[data-nav-id="' + section + '"]' ).addClass( 'agf-has-error' );
				first = first || section;
			} );
		} );
		return first;
	}

	function save( $app ) {
		if ( $app.hasClass( 'agf-busy' ) ) {
			return;
		}
		var $form = $app.find( '.agf-form' );
		flushEditors( $app );
		setBusy( $app, true );

		request( $app, 'save', { data: JSON.stringify( AGF.serialize( $form ) ) } ).done( function ( res ) {
			if ( ! res || ! res.success ) {
				AGF.toast( failMessage(), 'error', $app.attr( 'data-theme' ) );
				return;
			}
			var errorSection = showErrors( $app, res.data.errors );
			setDirty( $app, false );
			if ( errorSection ) {
				showSection( $app, errorSection );
				AGF.toast( res.data.message || L.invalid, 'error', $app.attr( 'data-theme' ) );
			} else {
				AGF.toast( res.data.message || L.saved, 'success', $app.attr( 'data-theme' ) );
			}
			$app.trigger( 'agf:saved', [ res.data ] );
		} ).fail( function ( xhr ) {
			AGF.toast( failMessage( xhr ), 'error', $app.attr( 'data-theme' ) );
		} ).always( function () {
			setBusy( $app, false );
		} );
	}

	function reloadAfter( $app, res ) {
		setDirty( $app, false );
		AGF.toast( ( res && res.data && res.data.message ) || L.saved, 'success', $app.attr( 'data-theme' ) );
		setTimeout( function () {
			window.location.reload();
		}, 700 );
	}

	function initSearch( $app ) {
		var $input = $app.find( '.agf-search-input' ).first();
		if ( ! $input.length ) {
			return;
		}

		var run = debounce( function () {
			var term = $input.val().toLowerCase().trim();
			var $panels = $app.find( '.agf-panel' );
			var $fields = $panels.find( '> .agf-panel-body > .agf-field' );

			$app.find( '.agf-nav-link' ).removeClass( 'agf-has-match' );

			if ( ! term ) {
				$app.removeClass( 'agf-searching' );
				$fields.removeClass( 'agf-search-hidden' );
				$app.find( '.agf-search-empty' ).prop( 'hidden', true );
				showSection( $app, $app.attr( 'data-active' ) || $panels.first().data( 'section' ), true );
				return;
			}

			$app.addClass( 'agf-searching' );
			var total = 0;
			$panels.each( function () {
				var $panel = $( this );
				var hits = 0;
				$panel.find( '> .agf-panel-body > .agf-field' ).each( function () {
					var $f = $( this );
					if ( $f.data( 'agfText' ) === undefined ) {
						$f.data( 'agfText', $f.find( '.agf-field-title, .agf-field-subtitle, .agf-desc, .agf-check-label, .agf-switch-label, .agf-heading, .agf-subheading' ).text().toLowerCase() + ' ' + ( $f.data( 'field-id' ) || '' ) );
					}
					var match = $f.data( 'agfText' ).indexOf( term ) > -1;
					$f.toggleClass( 'agf-search-hidden', ! match );
					hits += match ? 1 : 0;
				} );
				$panel.prop( 'hidden', ! hits );
				if ( hits ) {
					$app.find( '.agf-nav-link[data-nav-id="' + $panel.data( 'section' ) + '"]' ).addClass( 'agf-has-match' );
				}
				total += hits;
			} );
			$app.find( '.agf-search-empty' ).prop( 'hidden', total > 0 );
			refreshEditors( $app );
		}, 150 );

		$input.on( 'input search', run );
		$input.on( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) {
				$input.val( '' );
				run();
			}
		} );
	}

	function initApp( $app ) {
		var mode = $app.data( 'mode' );
		var unique = $app.data( 'unique' );

		// Navigation.
		$app.on( 'click', '.agf-nav-link', function ( e ) {
			e.preventDefault();
			var $item = $( this ).parent( '.agf-nav-item' );
			if ( $item.hasClass( 'agf-has-children' ) && ! $( this ).closest( '.agf-nav-sub' ).length ) {
				var wasOpen = $item.hasClass( 'agf-open' );
				if ( $app.hasClass( 'agf-searching' ) ) {
					$app.find( '.agf-search-input' ).val( '' ).trigger( 'input' );
				}
				showSection( $app, $( this ).data( 'section' ) );
				$item.toggleClass( 'agf-open', ! wasOpen || $( this ).data( 'section' ) === $( this ).data( 'nav-id' ) );
				return;
			}
			if ( $app.hasClass( 'agf-searching' ) ) {
				$app.find( '.agf-search-input' ).val( '' );
				$app.removeClass( 'agf-searching' ).find( '.agf-search-hidden' ).removeClass( 'agf-search-hidden' );
				$app.find( '.agf-search-empty' ).prop( 'hidden', true );
			}
			showSection( $app, $( this ).data( 'section' ) );
		} );

		if ( mode === 'metabox' ) {
			return;
		}

		// Initial section: hash > stored > first.
		var hash = ( window.location.hash.match( /tab=([\w-]+)/ ) || [] )[ 1 ];
		var initial = hash || storageGet( 'agf-tab-' + unique );
		if ( initial && $app.find( '.agf-panel[data-section="' + initial + '"]' ).length ) {
			showSection( $app, initial, true );
		}

		$app.on( 'click', '.agf-nav-toggle', function () {
			$app.toggleClass( 'agf-nav-open' );
		} );

		// Theme toggle.
		$app.on( 'click', '.agf-theme-toggle', function () {
			var next = $app.attr( 'data-theme' ) === 'dark' ? 'light' : 'dark';
			$app.attr( 'data-theme', next );
			storageSet( 'agf-theme-' + unique, next );
		} );

		// Dirty state.
		var $form = $app.find( '.agf-form' );
		$form.on( 'change input', ':input:not(.agf-search-input)', function () {
			setDirty( $app, true );
		} );
		$form.on( 'change', '.agf-repeater, .agf-tags, .agf-sortable, .agf-sorter-list', function () {
			setDirty( $app, true );
		} );
		$( window ).on( 'beforeunload', function () {
			if ( $app.hasClass( 'agf-dirty' ) ) {
				return L.unsaved;
			}
		} );

		// Save.
		$form.on( 'submit', function ( e ) {
			e.preventDefault();
			save( $app );
		} );
		$( document ).on( 'keydown', function ( e ) {
			if ( ( e.ctrlKey || e.metaKey ) && ( e.key === 's' || e.key === 'S' ) ) {
				e.preventDefault();
				save( $app );
			}
			if ( e.key === '/' && ! $( e.target ).is( ':input, [contenteditable]' ) ) {
				e.preventDefault();
				$app.find( '.agf-search-input' ).trigger( 'focus' );
			}
		} );

		// Reset.
		$app.on( 'click', '.agf-reset-section', function () {
			AGF.confirm( L.resetSection, { theme: $app.attr( 'data-theme' ), ok: $( this ).text() } ).then( function ( ok ) {
				if ( ok ) {
					request( $app, 'reset', { section: $app.attr( 'data-active' ) } ).done( function ( res ) {
						reloadAfter( $app, res );
					} ).fail( function ( xhr ) {
						AGF.toast( failMessage( xhr ), 'error', $app.attr( 'data-theme' ) );
					} );
				}
			} );
		} );
		$app.on( 'click', '.agf-reset-all', function () {
			AGF.confirm( L.resetAll, { theme: $app.attr( 'data-theme' ), ok: $( this ).text(), danger: true } ).then( function ( ok ) {
				if ( ok ) {
					request( $app, 'reset' ).done( function ( res ) {
						reloadAfter( $app, res );
					} ).fail( function ( xhr ) {
						AGF.toast( failMessage( xhr ), 'error', $app.attr( 'data-theme' ) );
					} );
				}
			} );
		} );

		// Backup.
		$app.on( 'click', '.agf-backup-download, .agf-backup-copy', function () {
			var copy = $( this ).hasClass( 'agf-backup-copy' );
			request( $app, 'export' ).done( function ( res ) {
				if ( ! res || ! res.success ) {
					AGF.toast( L.error, 'error', $app.attr( 'data-theme' ) );
					return;
				}
				var json = JSON.stringify( res.data.data, null, 2 );
				if ( copy ) {
					copyText( json ).then( function () {
						AGF.toast( L.copied, 'success', $app.attr( 'data-theme' ) );
					} );
					return;
				}
				var url = URL.createObjectURL( new Blob( [ json ], { type: 'application/json' } ) );
				$( '<a>' ).attr( { href: url, download: res.data.filename } ).appendTo( 'body' )[ 0 ].click();
				setTimeout( function () {
					URL.revokeObjectURL( url );
				}, 1000 );
			} ).fail( function ( xhr ) {
				AGF.toast( failMessage( xhr ), 'error', $app.attr( 'data-theme' ) );
			} );
		} );
		$app.on( 'change', '.agf-backup-file input[type=file]', function () {
			var file = this.files && this.files[ 0 ];
			var $area = $( this ).closest( '.agf-backup-import' ).find( '.agf-backup-data' );
			if ( file ) {
				var reader = new FileReader();
				reader.onload = function () {
					$area.val( reader.result );
				};
				reader.readAsText( file );
			}
			this.value = '';
		} );
		$app.on( 'click', '.agf-backup-run', function () {
			var raw = $( this ).closest( '.agf-backup-import' ).find( '.agf-backup-data' ).val();
			try {
				JSON.parse( raw );
			} catch ( err ) {
				AGF.toast( L.invalidJson, 'error', $app.attr( 'data-theme' ) );
				return;
			}
			AGF.confirm( L.importConfirm, { theme: $app.attr( 'data-theme' ) } ).then( function ( ok ) {
				if ( ok ) {
					request( $app, 'import', { data: raw } ).done( function ( res ) {
						reloadAfter( $app, res );
					} ).fail( function ( xhr ) {
						AGF.toast( failMessage( xhr ), 'error', $app.attr( 'data-theme' ) );
					} );
				}
			} );
		} );

		initSearch( $app );

		// Sticky header shadow once the page scrolls.
		var $header = $app.find( '.agf-header' );
		$( window ).on( 'scroll', debounce( function () {
			$header.toggleClass( 'agf-scrolled', window.scrollY > $app.offset().top );
		}, 20 ) );
	}

	/* ---- Menu items added on Appearance → Menus arrive by AJAX ---- */
	$( document ).ajaxComplete( function ( e, xhr, settings ) {
		if ( settings && typeof settings.data === 'string' && settings.data.indexOf( 'action=add-menu-item' ) > -1 ) {
			setTimeout( function () {
				$( '#menu-to-edit .agf-app' ).each( function () {
					if ( ! this.agfBooted ) {
						this.agfBooted = true;
						AGF.init( $( this ) );
					}
				} );
			}, 50 );
		}
	} );

	/* ---- Taxonomy "add term" form: reset fields after WordPress adds a term ---- */
	$( document ).ajaxComplete( function ( e, xhr, settings ) {
		if ( ! settings || typeof settings.data !== 'string' || settings.data.indexOf( 'action=add-tag' ) === -1 ) {
			return;
		}
		if ( xhr.responseText && xhr.responseText.indexOf( 'wp_error' ) > -1 ) {
			return;
		}
		$( '#addtag .agf-app' ).each( function () {
			var $app = $( this );
			$app.find( '.agf-repeater-item' ).remove();
			$app.find( '.agf-repeater' ).each( function () {
				repState( $( this ) );
			} );
			$app.find( ':input[name]' ).each( function () {
				if ( this.type === 'checkbox' || this.type === 'radio' ) {
					this.checked = this.defaultChecked;
				} else if ( this.tagName === 'SELECT' ) {
					$( this ).find( 'option' ).each( function () {
						this.selected = this.defaultSelected;
					} );
				} else if ( this.type !== 'hidden' || $( this ).is( '.agf-media-url, .agf-media-id, .agf-media-extra, .agf-gallery-ids, .agf-icon-value' ) ) {
					this.value = this.defaultValue;
				}
				$( this ).trigger( 'agf:sync' );
			} );
			$app.find( '.agf-tag' ).remove();
		} );
	} );

	/* ---- Boot ---- */
	$( function () {
		$( '.agf-app' ).each( function () {
			var $app = $( this );
			// Skip widget templates, the customizer (appglut-customize.js boots each control) and shortcode forms (booted on open).
			if ( $app.closest( '#available-widgets, .widget-tpl, template, #customize-controls' ).length || $app.is( '.agf-mode-customize, .agf-mode-shortcode' ) ) {
				return;
			}
			this.agfBooted = true;
			if ( $app.is( '.agf-mode-options, .agf-mode-metabox' ) ) {
				initApp( $app );
			}
			AGF.init( $app );
		} );
	} );

	// Classic widgets & the customizer insert widget forms dynamically.
	$( document ).on( 'widget-added widget-updated', function ( e, $widget ) {
		$( $widget ).find( '.agf-app' ).each( function () {
			AGF.init( $( this ) );
		} );
	} );
}( jQuery, window, document ) );
