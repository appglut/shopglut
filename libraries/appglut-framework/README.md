# AppGlut Framework

A modern options framework for WordPress themes and plugins. Build admin panels, Customizer options, metaboxes, taxonomy, user profile, comment and menu item fields, widgets and a shortcode generator from simple PHP arrays, with 51 field types and a polished, dark-mode-ready UI.

**Highlights**

- Admin panel with a sticky header, nested sidebar navigation, instant field search (`/`), light/dark/auto theme, custom accent color and a mobile drawer
- AJAX save (`Ctrl/⌘ + S`) sent as one JSON payload, so big panels never hit PHP's `max_input_vars` limit
- Unsaved-changes indicator, toasts, per-field validation errors, reset section / reset all, JSON import & export
- Conditional logic with chained rules, `and`/`or`, and operators `== != > >= < <= any not-any contains not-contains empty not-empty`
- Repeaters and groups that nest, drag to reorder, duplicate (including nested items) and collapse
- Front-end CSS generated from fields (`output`), plus automatic Google Fonts loading
- Customizer with live CSS preview: fields with `output` update the page without a reload
- Shortcode generator with a searchable picker, live preview and nested (group) shortcodes, for both the classic and the block editor
- Strict server-side sanitizing: unknown keys are dropped, choices are whitelisted, colors/URLs/numbers validated
- No external JS libraries: jQuery, jQuery UI Sortable, and WordPress's own media, CodeMirror and TinyMCE
- RTL support via CSS logical properties; every string is translatable (`appglut-framework` text domain)

## Install

Activate it as a plugin, or bundle it:

```php
require_once get_template_directory() . '/appglut-framework/appglut-framework.php';
```

If several plugins bundle it, the first loaded copy wins. To try the demo panels, add `define( 'APPGLUT_DEMO', true );` to `wp-config.php`. The demos live in `samples/` and show every field.

## Quick start

```php
$prefix = 'mytheme_options';

AppGlut::createOptions( $prefix, array(
	'menu_title'      => 'Theme Options',
	'menu_slug'       => 'mytheme-options',
	'framework_title' => 'My Theme',
	'accent'          => '#0ea5e9',
	'theme'           => 'auto',
) );

AppGlut::createSection( $prefix, array(
	'id'     => 'general',
	'title'  => 'General',
	'icon'   => 'dashicons-admin-generic',
	'fields' => array(
		array( 'id' => 'logo', 'type' => 'media', 'title' => 'Logo' ),
		array( 'id' => 'sticky', 'type' => 'switcher', 'title' => 'Sticky header', 'default' => true ),
		array(
			'id'         => 'sticky-bg',
			'type'       => 'color',
			'title'      => 'Sticky background',
			'dependency' => array( 'sticky', '==', true ),
			'output'     => array( 'selector' => '.site-header.is-sticky', 'property' => 'background-color' ),
		),
	),
) );

// Sub-section: give it a 'parent'.
AppGlut::createSection( $prefix, array( 'parent' => 'general', 'title' => 'Footer', 'fields' => array( /* ... */ ) ) );
```

Reading values (defaults are merged in):

```php
appglut_get_option( 'mytheme_options', 'sticky' );            // single value
appglut_get_option( 'mytheme_options', 'typography.font-size' ); // dot path
appglut_get_meta( $post_id, 'my_metabox', 'subtitle' );
appglut_get_term_meta( $term_id, 'my_term_opts', 'color' );
appglut_get_user_meta( $user_id, 'my_profile', 'twitter' );
appglut_get_comment_meta( $comment_id, 'my_comment', 'featured' );
appglut_get_menu_item_meta( $item_id, 'my_menu', 'icon' );      // or $item->appglut['my_menu'] in a walker
appglut_image_url( appglut_get_option( 'mytheme_options', 'logo' ), 'medium' );
```

## Containers

| API | Where | Key args |
| --- | --- | --- |
| `AppGlut::createOptions( $id, $args )` | Admin page | `menu_title`, `menu_slug`, `menu_type` (menu / submenu), `menu_parent`, `menu_capability`, `menu_icon`, `menu_position`, `framework_title`, `framework_subtitle`, `logo`, `version`, `header_links`, `footer_text`, `theme` (light / dark / auto), `accent`, `show_search`, `show_reset_all`, `show_reset_section`, `database` (option / theme_mod / network), `save_defaults`, `output_css`, `enqueue_webfont`, `admin_bar` |
| `AppGlut::createMetabox( $id, $args )` | Post edit screen | `title`, `post_type`, `context`, `priority`, `nav` (horizontal / vertical tabs), `data_type` (serialize / unserialize), `page_templates` |
| `AppGlut::createTaxonomyOptions( $id, $args )` | Term add/edit | `taxonomy`, `data_type` |
| `AppGlut::createProfileOptions( $id, $args )` | User profile | `title`, `data_type` |
| `AppGlut::createWidget( $id, $args )` | Classic widgets | `title`, `description`, `fields`, `callback( $args, $instance )` |
| `AppGlut::createCustomizeOptions( $id, $args )` | Appearance → Customize | `database` (option / theme_mod), `capability`, `transport`, `priority`, `panel`, `output_css`, `live_css`, `enqueue_webfont` |
| `AppGlut::createCommentMetabox( $id, $args )` | Comment edit screen | `title`, `priority`, `nav`, `data_type` |
| `AppGlut::createNavMenuOptions( $id, $args )` | Each menu item | `data_type`, `attach`, `capability` |
| `AppGlut::createShortcoder( $id, $args )` | Post editors | `button_title`, `select_title`, `insert_title`, `show_in_editor`, `gutenberg`, `post_types`, `screens` |

`data_type => 'unserialize'` stores each field as its own meta key, so you can query by it.

## Customizer

```php
AppGlut::createCustomizeOptions( 'mytheme', array( 'database' => 'theme_mod' ) );

AppGlut::createSection( 'mytheme', array( 'id' => 'design', 'title' => 'Design' ) );        // has children → panel
AppGlut::createSection( 'mytheme', array( 'parent' => 'design', 'title' => 'Colors', 'fields' => array(
	array( 'id' => 'accent', 'type' => 'color', 'output' => array( 'selector' => 'a', 'property' => 'color' ) ),
) ) );
AppGlut::createSection( 'mytheme', array( 'assign' => 'title_tagline', 'fields' => array( /* ... */ ) ) ); // existing section
```

Each field becomes the setting `mytheme[field_id]`, so `appglut_get_option( 'mytheme', 'accent' )` works as usual. Fields with `output` preview live. Other fields use the container's `transport` (default `refresh`), or set `'transport' => 'postMessage'` with `'selective_refresh' => array( 'selector' => '.site-title', 'render_callback' => ... )`. `dependency`, `validate` (errors show under the control), `setting_args` and `control_args` work per field.

## Shortcode generator

```php
AppGlut::createShortcoder( 'my_sc', array( 'button_title' => 'Add Shortcode' ) );

AppGlut::createSection( 'my_sc', array(
	'title'     => 'Tabs',
	'icon'      => 'dashicons-index-card',
	'shortcode' => 'my_tabs',
	'fields'    => array( array( 'id' => 'style', 'type' => 'select', 'options' => array( 'pills' => 'Pills' ) ) ),
	'group'     => array(                       // optional repeated child shortcodes
		'shortcode' => 'my_tab',
		'fields'    => array(
			array( 'id' => 'title', 'type' => 'text' ),
			array( 'id' => 'content', 'type' => 'textarea' ), // 'content' = enclosed content
		),
		'callback'  => function ( $atts, $content ) { return '<div class="tab">' . $content . '</div>'; },
	),
	'callback'  => function ( $atts, $content ) { return '<div class="tabs">' . do_shortcode( $content ) . '</div>'; },
) );
```

Output: `[my_tabs style="pills"][my_tab title="One"]…[/my_tab][/my_tabs]`. Attributes equal to their defaults are left out. With `callback` the shortcode is registered for you and `$atts` arrives sanitized, decoded and merged with defaults. If you register it yourself, call `AppGlut::shortcode_atts( $fields, $atts )` to get the same result. Classic editors get a button; the block editor gets a block that turns into a core Shortcode block. Anywhere else (with `screens`), call `AppGlut.shortcoder.open( 'my_sc', function ( code ) { ... } )`. `wp_editor` fields are not supported inside the generator.

## Common field options

| Option | Purpose |
| --- | --- |
| `id`, `type`, `title`, `subtitle`, `desc`, `help` (tooltip), `badge` | Basics |
| `default` | Default value |
| `col` | Grid width 1–12; put fields side by side (`'col' => 6`) |
| `layout` | `inline` (label beside the control) or `stacked` |
| `placeholder`, `attributes`, `class` | Markup tweaks |
| `dependency`, `dependency_relation` | Conditional logic: `array( 'id', '==', 'value' )` or a list of rules |
| `sanitize` | Custom callable replacing the built-in sanitizer |
| `validate` | Callable that gets the submitted value and returns an error message (or `''`). On error the previous value is kept and the message shows on the field. |
| `output`, `output_property`, `output_unit`, `output_important` | Front-end CSS (see below) |

## Field types

| Group | Types |
| --- | --- |
| Text | `text` (input_type email / url / password / tel, prefix, suffix, icon, copy, counter), `textarea` (counter, autosize, allow_html), `number`, `hidden` |
| Choice | `switcher`, `checkbox` (single or group, card style), `radio` (card style), `button_set` (multiple, icons), `image_select`, `palette`, `select` (search, multiple, optgroups, AJAX), `tags`, `rating` |
| Design | `color` (alpha, presets), `color_group`, `link_color` (normal / hover / active / visited / focus), `gradient`, `box_shadow`, `border`, `spacing`, `dimensions`, `typography` (80+ Google Fonts, live preview), `background`, `slider`, `spinner` |
| Media | `media`, `gallery`, `upload`, `icon` (searchable Dashicons picker, your own `icons` list, or an SVG set from `AppGlut::registerIconSet()` with optional `group` tabs), `map` (OpenStreetMap preview), `link`, `date` (date / time / datetime / month / week, `range`) |
| Editors | `code_editor` (WordPress CodeMirror: css, scss, js, json, html, php, …), `wp_editor` |
| Containers | `fieldset`, `repeater`, `group`, `key_value`, `accordion`, `tabbed`, `sortable`, `sorter` |
| UI only | `heading`, `subheading`, `notice` (info / success / warning / danger / accent), `submessage`, `content`, `divider`, `button`, `callback`, `backup` |

**Data sources.** Instead of an array, `options` for select / checkbox / radio can be `posts`, `pages`, `post_type` (+ `query_args`), `categories`, `tags`, `terms` (+ `taxonomy`), `menus`, `users`, `roles`, `post_types`, `taxonomies`, `sidebars`, `menu_locations`, `image_sizes`, or any callable. Add `'ajax' => true` to a select to search remotely as the user types. Its query arguments are signed on the server, so the browser cannot change them.

## CSS output

```php
'output' => '.site-title'                                         // property inferred (color fields → color)
'output' => array( 'selector' => 'a', 'property' => 'color', 'important' => true )
'output' => array(
	array( 'selector' => '.btn', 'property' => 'background-color' ),
	array( 'selector' => '.btn', 'property' => 'border-color', 'media' => '(min-width: 768px)' ),
)
```

Typography, background, spacing (`property` padding / margin), border, dimensions, gradient and box_shadow write all their declarations. Used Google Fonts are enqueued automatically with only the weights you picked.

## Custom field types

```php
class My_Field_hello extends AppGlut_Field {
	public function render() {
		echo '<input type="text" class="agf-input" name="' . esc_attr( $this->name() ) . '" value="' . esc_attr( $this->value ) . '">';
	}
	public function sanitize( $value ) {
		return sanitize_text_field( $value );
	}
}
AppGlut::registerField( 'hello', 'My_Field_hello' );
```

JavaScript hook for custom fields: `AppGlut.fields.hello = function ( $scope ) { ... }`. It runs on page load and for every new repeater item.

## Hooks

- Filters: `appglut_{id}_args`, `appglut_{id}_sections`, `appglut_{id}_save`, `appglut_get_data`, `appglut_fonts`, `appglut_field_class`, `appglut_url`
- Actions: `appglut_loaded`, `appglut_{id}_save_before`, `appglut_{id}_saved`, `appglut_{id}_reset`
- JS: `AppGlut.init`, `AppGlut.serialize`, `AppGlut.toast`, `AppGlut.confirm`, `AppGlut.modal`, `AppGlut.compare`, `AppGlut.shortcoder.open`

## Structure

```
appglut-framework.php      loader / plugin header
includes/                  core, containers, output, ajax, data (fonts, dashicons)
fields/                    field types, grouped by family
assets/css|js/appglut.*    admin UI (+ appglut-customize.js, appglut-shortcoder*.js)
samples/                   demo panels (APPGLUT_DEMO)
docs/index.html            full HTML documentation (rebuild: python3 docs/build/build_docs.py .)
languages/                 appglut-framework.pot; put appglut-framework-{locale}.mo files here
```

Requires WordPress 5.6+ and PHP 7.2+. The admin UI targets current evergreen browsers; it uses CSS `color-mix()` and `:has()`.
