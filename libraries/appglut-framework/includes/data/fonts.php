<?php
/**
 * Fonts offered by the typography field.
 *
 * 'websafe' => family => fallback stack.
 * 'google'  => family => array( category, weights ). Weights "100-900" expand in steps of 100.
 *
 * Filter with 'appglut_fonts'.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$appglut_google = array(
	'Inter'              => array( 'sans-serif', '100-900' ),
	'Roboto'             => array( 'sans-serif', '100,300,400,500,700,900' ),
	'Open Sans'          => array( 'sans-serif', '300-800' ),
	'Lato'               => array( 'sans-serif', '100,300,400,700,900' ),
	'Montserrat'         => array( 'sans-serif', '100-900' ),
	'Poppins'            => array( 'sans-serif', '100-900' ),
	'Raleway'            => array( 'sans-serif', '100-900' ),
	'Nunito'             => array( 'sans-serif', '200-900' ),
	'Nunito Sans'        => array( 'sans-serif', '200-900' ),
	'Source Sans 3'      => array( 'sans-serif', '200-900' ),
	'Noto Sans'          => array( 'sans-serif', '100-900' ),
	'Work Sans'          => array( 'sans-serif', '100-900' ),
	'Rubik'              => array( 'sans-serif', '300-900' ),
	'Mulish'             => array( 'sans-serif', '200-900' ),
	'DM Sans'            => array( 'sans-serif', '100-900' ),
	'Manrope'            => array( 'sans-serif', '200-800' ),
	'Outfit'             => array( 'sans-serif', '100-900' ),
	'Plus Jakarta Sans'  => array( 'sans-serif', '200-800' ),
	'Space Grotesk'      => array( 'sans-serif', '300-700' ),
	'Sora'               => array( 'sans-serif', '100-800' ),
	'Lexend'             => array( 'sans-serif', '100-900' ),
	'Urbanist'           => array( 'sans-serif', '100-900' ),
	'Figtree'            => array( 'sans-serif', '300-900' ),
	'Red Hat Display'    => array( 'sans-serif', '300-900' ),
	'Inter Tight'        => array( 'sans-serif', '100-900' ),
	'Fira Sans'          => array( 'sans-serif', '100-900' ),
	'IBM Plex Sans'      => array( 'sans-serif', '100-700' ),
	'Ubuntu'             => array( 'sans-serif', '300,400,500,700' ),
	'PT Sans'            => array( 'sans-serif', '400,700' ),
	'Oswald'             => array( 'sans-serif', '200-700' ),
	'Roboto Condensed'   => array( 'sans-serif', '100-900' ),
	'Barlow'             => array( 'sans-serif', '100-900' ),
	'Barlow Condensed'   => array( 'sans-serif', '100-900' ),
	'Heebo'              => array( 'sans-serif', '100-900' ),
	'Karla'              => array( 'sans-serif', '200-800' ),
	'Cabin'              => array( 'sans-serif', '400-700' ),
	'Archivo'            => array( 'sans-serif', '100-900' ),
	'Exo 2'              => array( 'sans-serif', '100-900' ),
	'Titillium Web'      => array( 'sans-serif', '200,300,400,600,700,900' ),
	'Josefin Sans'       => array( 'sans-serif', '100-700' ),
	'Quicksand'          => array( 'sans-serif', '300-700' ),
	'Comfortaa'          => array( 'sans-serif', '300-700' ),
	'Hind'               => array( 'sans-serif', '300-700' ),
	'Kanit'              => array( 'sans-serif', '100-900' ),
	'Prompt'             => array( 'sans-serif', '100-900' ),
	'Mukta'              => array( 'sans-serif', '200-800' ),
	'Assistant'          => array( 'sans-serif', '200-800' ),
	'Cairo'              => array( 'sans-serif', '200-900' ),
	'Tajawal'            => array( 'sans-serif', '200,300,400,500,700,800,900' ),
	'Hind Siliguri'      => array( 'sans-serif', '300-700' ),
	'Noto Sans Bengali'  => array( 'sans-serif', '100-900' ),
	'Noto Sans Arabic'   => array( 'sans-serif', '100-900' ),
	'Noto Sans JP'       => array( 'sans-serif', '100-900' ),
	'Playfair Display'   => array( 'serif', '400-900' ),
	'Merriweather'       => array( 'serif', '300,400,700,900' ),
	'Lora'               => array( 'serif', '400-700' ),
	'Noto Serif'         => array( 'serif', '100-900' ),
	'PT Serif'           => array( 'serif', '400,700' ),
	'Roboto Slab'        => array( 'serif', '100-900' ),
	'Libre Baskerville'  => array( 'serif', '400,700' ),
	'Crimson Text'       => array( 'serif', '400,600,700' ),
	'EB Garamond'        => array( 'serif', '400-800' ),
	'Cormorant Garamond' => array( 'serif', '300-700' ),
	'Bitter'             => array( 'serif', '100-900' ),
	'Arvo'               => array( 'serif', '400,700' ),
	'Alegreya'           => array( 'serif', '400-900' ),
	'Spectral'           => array( 'serif', '200-800' ),
	'IBM Plex Serif'     => array( 'serif', '100-700' ),
	'DM Serif Display'   => array( 'serif', '400' ),
	'Abril Fatface'      => array( 'display', '400' ),
	'Bebas Neue'         => array( 'display', '400' ),
	'Anton'              => array( 'display', '400' ),
	'Righteous'          => array( 'display', '400' ),
	'Lobster'            => array( 'display', '400' ),
	'Pacifico'           => array( 'handwriting', '400' ),
	'Dancing Script'     => array( 'handwriting', '400-700' ),
	'Caveat'             => array( 'handwriting', '400-700' ),
	'Satisfy'            => array( 'handwriting', '400' ),
	'Great Vibes'        => array( 'handwriting', '400' ),
	'Permanent Marker'   => array( 'handwriting', '400' ),
	'JetBrains Mono'     => array( 'monospace', '100-800' ),
	'Fira Code'          => array( 'monospace', '300-700' ),
	'Source Code Pro'    => array( 'monospace', '200-900' ),
	'Roboto Mono'        => array( 'monospace', '100-700' ),
	'IBM Plex Mono'      => array( 'monospace', '100-700' ),
);

$appglut_fonts = array(
	'websafe' => array(
		'system-ui'       => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
		'Arial'           => 'Helvetica, sans-serif',
		'Helvetica'       => 'Arial, sans-serif',
		'Verdana'         => 'Geneva, sans-serif',
		'Tahoma'          => 'Geneva, sans-serif',
		'Trebuchet MS'    => 'Helvetica, sans-serif',
		'Georgia'         => 'serif',
		'Times New Roman' => 'Times, serif',
		'Palatino'        => '"Palatino Linotype", serif',
		'Courier New'     => 'Courier, monospace',
		'Lucida Console'  => 'Monaco, monospace',
	),
	'google'  => array(),
);

foreach ( $appglut_google as $appglut_family => $appglut_info ) {
	$appglut_weights = array();
	foreach ( explode( ',', $appglut_info[1] ) as $appglut_part ) {
		if ( false !== strpos( $appglut_part, '-' ) ) {
			list( $appglut_from, $appglut_to ) = array_map( 'intval', explode( '-', $appglut_part ) );
			$appglut_weights                   = array_merge( $appglut_weights, range( $appglut_from, $appglut_to, 100 ) );
		} else {
			$appglut_weights[] = (int) $appglut_part;
		}
	}
	$appglut_fonts['google'][ $appglut_family ] = array(
		'category' => 'handwriting' === $appglut_info[0] ? 'cursive' : ( 'display' === $appglut_info[0] ? 'sans-serif' : $appglut_info[0] ),
		'weights'  => $appglut_weights,
	);
}

unset( $appglut_google, $appglut_family, $appglut_info, $appglut_weights, $appglut_part, $appglut_from, $appglut_to );

return apply_filters( 'appglut_fonts', $appglut_fonts );
