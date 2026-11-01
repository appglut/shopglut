/**
 * AppGlut Framework — shortcode generator.
 *
 *   AppGlut.shortcoder.open( 'my_shortcodes', function ( shortcode ) { ... } );
 *
 * Classic editors get a button (rendered in PHP); the block editor uses
 * appglut-shortcoder-block.js, which calls open() too.
 */
( function ( $, window, document ) {
	'use strict';

	var AGF = window.AppGlut;
	if ( ! AGF ) {
		return;
	}
	var L = window.appglutL10n || {};
	var instances = {};

	/* ---- Encoding (mirrors AppGlut::shortcode_atts in PHP) ---- */

	// WordPress runs stripcslashes() on attribute values, so backslashes are doubled.
	function escText( s ) {
		return String( s ).replace( /\\/g, '\\\\' ).replace( /"/g, '&quot;' ).replace( /\[/g, '&#91;' ).replace( /\]/g, '&#93;' );
	}

	function escJson( v ) {
		return JSON.stringify( v ).replace( /\[/g, '\\u005b' ).replace( /\]/g, '\\u005d' ).replace( /'/g, '\\u0027' ).replace( /\\/g, '\\\\' );
	}

	function isScalarList( v ) {
		return $.isArray( v ) && v.every( function ( x ) {
			return x === null || typeof x !== 'object';
		} );
	}

	function sameAsDefault( value, def ) {
		if ( def === undefined ) {
			return value === '' || ( $.isArray( value ) && ! value.length ) || ( $.isPlainObject( value ) && $.isEmptyObject( value ) );
		}
		if ( def === null || typeof def !== 'object' ) {
			return ! $.isArray( value ) && typeof value !== 'object' && String( value ) === String( def === null ? '' : def );
		}
		if ( isScalarList( def ) && isScalarList( value ) ) {
			return def.map( String ).join( ',' ) === value.map( String ).join( ',' );
		}
		return JSON.stringify( value ) === JSON.stringify( def );
	}

	function attrs( values, defaults ) {
		var out = '';
		$.each( values || {}, function ( key, value ) {
			if ( value === undefined || value === null || sameAsDefault( value, ( defaults || {} )[ key ] ) ) {
				return;
			}
			if ( isScalarList( value ) ) {
				out += ' ' + key + '="' + escText( value.join( ',' ) ) + '"';
			} else if ( typeof value === 'object' ) {
				out += ' ' + key + "='" + escJson( value ) + "'";
			} else {
				out += ' ' + key + '="' + escText( value ) + '"';
			}
		} );
		return out;
	}

	function toList( group ) {
		if ( $.isArray( group ) ) {
			return group;
		}
		return Object.keys( group || {} ).sort( function ( a, b ) {
			return a - b;
		} ).map( function ( k ) {
			return group[ k ];
		} );
	}

	function build( $form, unique ) {
		var all = AGF.serialize( $form );
		var root = all[ 'agfsc_' + unique ] || {};
		var values = $.extend( {}, root[ $form.attr( 'data-id' ) ] || {} );
		var tag = $form.attr( 'data-tag' );
		var groupTag = $form.attr( 'data-group-tag' );
		var content = values.content;
		var group = values.__group;

		delete values.content;
		delete values.__group;

		var code = '[' + tag + attrs( values, $form.data( 'defaults' ) ) + ']';

		if ( groupTag ) {
			var gdefaults = $form.data( 'group-defaults' ) || {};
			toList( group ).forEach( function ( item ) {
				item = $.extend( {}, item );
				var inner = item.content;
				delete item.content;
				code += '\n[' + groupTag + attrs( item, gdefaults ) + ']' + ( inner ? inner : '' ) + '[/' + groupTag + ']';
			} );
			code += '\n';
		}

		if ( typeof content === 'string' && content !== '' ) {
			code += content;
		}
		if ( groupTag || $form.attr( 'data-content' ) === '1' ) {
			code += '[/' + tag + ']';
		}
		return code;
	}

	/* ---- Modal ---- */

	function Instance( unique ) {
		var tpl = document.querySelector( 'template.agf-sc-template[data-unique="' + unique + '"]' );
		this.unique = unique;
		this.ok = !! tpl;
		if ( ! tpl ) {
			return;
		}

		this.$modal = $( document.importNode( tpl.content, true ).firstElementChild ).appendTo( 'body' ).hide();
		this.$forms = this.$modal.find( '.agf-mode-shortcode' );
		this.$active = $();
		this.callback = null;
		this.bind();
	}

	Instance.prototype = {
		bind: function () {
			var self = this;
			var $m = this.$modal;
			var refresh;

			$m.on( 'click', '.agf-modal-backdrop, .agf-modal-close', function () {
				self.close();
			} );
			$m.on( 'click', '.agf-sc-card', function () {
				self.show( $( this ).attr( 'data-id' ) );
			} );
			$m.on( 'click', '.agf-sc-back', function () {
				self.picker();
			} );
			$m.on( 'input', '.agf-sc-filter', function () {
				var term = this.value.toLowerCase().trim();
				var shown = 0;
				$m.find( '.agf-sc-card' ).each( function () {
					var match = ! term || $( this ).text().toLowerCase().indexOf( term ) > -1;
					$( this ).prop( 'hidden', ! match );
					shown += match ? 1 : 0;
				} );
				$m.find( '.agf-sc-empty' ).prop( 'hidden', shown > 0 );
			} );

			refresh = function () {
				clearTimeout( self.timer );
				self.timer = setTimeout( function () {
					self.preview();
				}, 120 );
			};
			this.$forms.on( 'change input', refresh );

			$m.on( 'click', '.agf-sc-copy', function () {
				var text = self.code();
				var done = function () {
					AGF.toast( L.copied || 'Copied', 'success', $m.attr( 'data-theme' ) );
				};
				if ( navigator.clipboard && window.isSecureContext ) {
					navigator.clipboard.writeText( text ).then( done );
				} else {
					var $t = $( '<textarea>' ).val( text ).appendTo( 'body' ).trigger( 'select' );
					document.execCommand( 'copy' );
					$t.remove();
					done();
				}
			} );
			$m.on( 'click', '.agf-sc-insert', function () {
				var text = self.code();
				var cb = self.callback;
				self.close();
				if ( cb ) {
					cb( text );
				}
			} );
		},

		open: function ( callback ) {
			var self = this;
			this.callback = callback;

			if ( ! this.inited ) {
				this.inited = true;
				AGF.init( this.$forms );
			}

			this.$modal.show();
			$( document ).on( 'keydown.agfsc', function ( e ) {
				// Let open dropdowns/pickers inside the form handle Escape first.
				if ( e.key === 'Escape' && ! e.isDefaultPrevented() && ! $( '.agf-modal' ).not( self.$modal ).filter( ':visible' ).length ) {
					self.close();
				}
			} );
			requestAnimationFrame( function () {
				self.$modal.addClass( 'agf-in' );
			} );

			if ( this.$forms.length === 1 ) {
				this.show( this.$forms.attr( 'data-id' ), true );
			} else {
				this.picker();
			}
		},

		close: function () {
			var $m = this.$modal;
			$( document ).off( 'keydown.agfsc' );
			$m.removeClass( 'agf-in' );
			setTimeout( function () {
				$m.hide();
			}, 200 );
		},

		picker: function () {
			var $m = this.$modal;
			this.$active = $();
			this.$forms.prop( 'hidden', true );
			$m.find( '.agf-sc-picker' ).prop( 'hidden', false );
			$m.find( '.agf-sc-back, .agf-sc-foot' ).prop( 'hidden', true );
			$m.find( '.agf-sc-heading' ).text( $m.find( '.agf-sc-heading' ).attr( 'data-default' ) );
			setTimeout( function () {
				$m.find( '.agf-sc-filter' ).trigger( 'focus' );
			}, 60 );
		},

		show: function ( id, only ) {
			var $m = this.$modal;
			this.$active = this.$forms.filter( '[data-id="' + id + '"]' );
			this.$forms.prop( 'hidden', true );
			this.$active.prop( 'hidden', false ).trigger( 'agf:shown' );
			$m.find( '.agf-sc-picker' ).prop( 'hidden', true );
			$m.find( '.agf-sc-back' ).prop( 'hidden', !! only );
			$m.find( '.agf-sc-foot' ).prop( 'hidden', false );
			$m.find( '.agf-sc-heading' ).text( this.$active.attr( 'data-title' ) );
			AGF.runDependencies( this.$active );
			this.preview();
			this.$active.find( ':input:visible' ).first().trigger( 'focus' );
		},

		code: function () {
			return this.$active.length ? build( this.$active, this.unique ) : '';
		},

		preview: function () {
			this.$modal.find( '.agf-sc-preview' ).text( this.code() );
		}
	};

	function instance( unique ) {
		if ( ! instances[ unique ] ) {
			instances[ unique ] = new Instance( unique );
		}
		return instances[ unique ].ok ? instances[ unique ] : null;
	}

	/* ---- Classic editor ---- */

	function insertIntoEditor( editorId, text ) {
		var ed = window.tinymce && editorId ? window.tinymce.get( editorId ) : null;
		if ( ed && ! ed.isHidden() ) {
			ed.focus();
			ed.execCommand( 'mceInsertContent', false, text.replace( /\n/g, '<br>' ) );
			return;
		}
		var ta = document.getElementById( editorId );
		if ( ! ta ) {
			if ( window.send_to_editor ) {
				window.send_to_editor( text );
			}
			return;
		}
		var start = ta.selectionStart || 0;
		var end = ta.selectionEnd || 0;
		ta.value = ta.value.slice( 0, start ) + text + ta.value.slice( end );
		ta.selectionStart = ta.selectionEnd = start + text.length;
		ta.focus();
		$( ta ).trigger( 'input' ).trigger( 'change' );
	}

	$( document ).on( 'click', '.agf-sc-open', function ( e ) {
		e.preventDefault();
		var editorId = $( this ).attr( 'data-editor' );
		var inst = instance( $( this ).attr( 'data-unique' ) );
		if ( inst ) {
			inst.open( function ( text ) {
				insertIntoEditor( editorId, text );
			} );
		}
	} );

	AGF.shortcoder = {
		open: function ( unique, callback ) {
			var inst = instance( unique );
			if ( inst ) {
				inst.open( callback );
			}
			return !! inst;
		},
		build: build
	};
}( jQuery, window, document ) );
