/**
 * AppGlut Framework — customizer bridge.
 *
 * Each framework field lives in its own control. This file initialises the
 * field when the control is embedded, pushes the field's value into its
 * customizer setting, and shows/hides controls from dependency rules using
 * the settings' current values (so rules work across sections).
 */
( function ( $, api, AGF ) {
	'use strict';

	if ( ! api || ! AGF ) {
		return;
	}

	var depControls = [];

	function settingValue( unique, id ) {
		var setting = api( unique + '[' + id + ']' );
		if ( ! setting ) {
			return '';
		}
		var value = setting.get();
		if ( value === true ) {
			return '1';
		}
		if ( value === false ) {
			return '0';
		}
		if ( value === null || value === undefined ) {
			return '';
		}
		if ( $.isArray( value ) ) {
			return value.map( String );
		}
		return typeof value === 'object' ? value : String( value );
	}

	function depVisible( control ) {
		var p = control.params;
		var or = p.depRel === 'or';
		for ( var i = 0; i < p.dep.length; i++ ) {
			var rule = p.dep[ i ];
			var ctrl = api.control( p.unique + '[' + rule.f + ']' );
			// A hidden controller counts as "not matching", like on options pages.
			var ok = ( ! ctrl || ctrl.agfVisible !== false ) && AGF.compare( rule.o, settingValue( p.unique, rule.f ), rule.v );
			if ( or && ok ) {
				return true;
			}
			if ( ! or && ! ok ) {
				return false;
			}
		}
		return ! or;
	}

	function runDependencies() {
		// Two passes so chained rules settle.
		for ( var pass = 0; pass < 2; pass++ ) {
			depControls.forEach( function ( control ) {
				control.agfVisible = depVisible( control );
			} );
		}
		depControls.forEach( function ( control ) {
			control.container.toggleClass( 'agf-cz-hidden', ! control.agfVisible );
		} );
	}

	api.controlConstructor.appglut = api.Control.extend( {
		ready: function () {
			var control = this;
			var $app = control.container.find( '.agf-app' ).first();
			var p = control.params;

			AGF.init( $app );

			if ( p.dep && p.dep.length ) {
				depControls.push( control );
				runDependencies();
			}

			if ( ! control.setting || ! p.fieldId ) {
				return;
			}

			var timer;
			var push = function () {
				clearTimeout( timer );
				timer = setTimeout( function () {
					var data = AGF.serialize( $app );
					var value = data[ p.unique ] ? data[ p.unique ][ p.fieldId ] : undefined;
					control.setting.set( value === undefined ? '' : value );
				}, 220 );
			};

			$app.on( 'change input', push );

			// Show server-side validation messages inside the field.
			control.notifications.bind( 'add', function () {
				control.container.find( '.agf-field' ).first().addClass( 'agf-has-error' );
			} );
			control.notifications.bind( 'remove', function () {
				control.container.find( '.agf-field' ).first().removeClass( 'agf-has-error' );
			} );
		}
	} );

	// Re-check dependencies whenever any framework setting changes.
	api.bind( 'change', function ( setting ) {
		if ( depControls.length && /\[[^\]]+\]$/.test( setting.id ) ) {
			runDependencies();
		}
	} );

	// Editors and sortables measure their size; refresh them when a section opens.
	api.bind( 'ready', function () {
		api.section.each( function ( section ) {
			section.expanded.bind( function ( expanded ) {
				if ( expanded ) {
					section.contentContainer.find( '.agf-app' ).trigger( 'agf:shown' );
				}
			} );
		} );
	} );
}( jQuery, window.wp && window.wp.customize, window.AppGlut ) );
