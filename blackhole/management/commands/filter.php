<?php

require "utils.php";

/**
 * Implementation of hook_help().
 */
function filter_help($path, $arg) {
	switch ($path) {
		case 'admin/help#filter':
			$output = '<p>'. t("The filter module allows administrators to configure text input formats for use on your site. An input format defines the HTML tags, codes, and other input allowed in both content and comments, and is a key feature in guarding against potentially damaging input from malicious users. Two input formats included by default are <em>Filtered HTML</em> (which allows only an administrator-approved subset of HTML tags) and <em>Full HTML</em> (which allows the full set of HTML tags). Additional input formats may be created by an administrator.") .'</p>';
			$output .= '<p>'. t('Each input format uses filters to manipulate text, and most input formats apply several different filters to text in a specific order. Each filter is designed for a specific purpose, and generally either adds, removes or transforms elements within user-entered text before it is displayed. A filter does not change the actual content of a post, but instead, modifies it temporarily before it is displayed. A filter may remove unapproved HTML tags, for instance, while another automatically adds HTML to make links referenced in text clickable.') .'</p>';
			$output .= '<p>'. t('Users can choose between the available input formats when creating or editing content. Administrators can configure which input formats are available to which user roles, as well as choose a default input format.') .'</p>';
			$output .= '<p>'. t('For more information, see the online handbook entry for <a href="@filter">Filter module</a>.', array('@filter' => 'http://drupal.org/handbook/modules/filter/')) .'</p>';
			return $output;
		case 'admin/settings/filters':
			$output = '<p>'. t('<em>Input formats</em> define a way of processing user-supplied text in Drupal. Each input format uses filters to manipulate text, and most input formats apply several different filters to text, in a specific order. Each filter is designed to accomplish a specific purpose, and generally either removes elements from or adds elements to text before it is displayed. Users can choose between the available input formats when submitting content.') .'</p>';
			$output .= '<p>'. t('Use the list below to configure which input formats are available to which roles, as well as choose a default input format (used for imported content, for example). The default format is always available to users. All input formats are available to users in a role with the "administer filters" permission.') .'</p>';
			return $output;
		case 'admin/settings/filters/%':
			return '<p>'. t('Every <em>filter</em> performs one particular change on the user input, for example stripping out malicious HTML or making URLs clickable. Choose which filters you want to apply to text in this input format. If you notice some filters are causing conflicts in the output, you can <a href="@rearrange">rearrange them</a>.', array('@rearrange' => url('admin/settings/filters/'. $arg[3] .'/order'))) .'</p>';
		case 'admin/settings/filters/%/configure':
			return '<p>'. t('If you cannot find the settings for a certain filter, make sure you have enabled it on the <a href="@url">view tab</a> first.', array('@url' => url('admin/settings/filters/'. $arg[3]))) .'</p>';
		case 'admin/settings/filters/%/order':
			$output = '<p>'. t('Because of the flexible filtering system, you might encounter a situation where one filter prevents another from doing its job. For example: a word in an URL gets converted into a glossary term, before the URL can be converted to a clickable link. When this happens, rearrange the order of the filters.') .'</p>';
			$output .= '<p>'. t("Filters are executed from top-to-bottom. To change the order of the filters, modify the values in the <em>Weight</em> column or grab a drag-and-drop handle under the <em>Name</em> column and drag filters to new locations in the list. (Grab a handle by clicking and holding the mouse while hovering over a handle icon.) Remember that your changes will not be saved until you click the <em>Save configuration</em> button at the bottom of the page.") .'</p>';
			return $output;
	}
}

/**
 * Implementation of hook_theme()
 */
function filter_theme() {
	return array(
		'filter_admin_overview' => array(
			'arguments' => array('form' => NULL),
			'file' => 'filter.admin.inc',
		),
		'filter_admin_order' => array(
			'arguments' => array('form' => NULL),
			'file' => 'filter.admin.inc',
		),
		'filter_tips' => array(
			'arguments' => array('tips' => NULL, 'long' => FALSE, 'extra' => ''),
			'file' => 'filter.pages.inc',
		),
		'filter_tips_more_info' => array(
			'arguments' => array(),
		),
	);
}

/**
 * Implementation of hook_menu().
 */
function filter_menu() {
	$items['admin/settings/filters'] = array(
		'title' => 'Input formats',
		'description' => 'Configure how content input by users is filtered, including allowed HTML tags. Also allows enabling of module-provided filters.',
		'page callback' => 'drupal_get_form',
		'page arguments' => array('filter_admin_overview'),
		'access arguments' => array('administer filters'),
		'file' => 'filter.admin.inc',
	);
	$items['admin/settings/filters/list'] = array(
		'title' => 'List',
		'type' => MENU_DEFAULT_LOCAL_TASK,
	);
	$items['admin/settings/filters/add'] = array(
		'title' => 'Add input format',
		'page callback' => 'filter_admin_format_page',
		'access arguments' => array('administer filters'),
		'type' => MENU_LOCAL_TASK,
		'weight' => 1,
		'file' => 'filter.admin.inc',
	);
	$items['admin/settings/filters/delete'] = array(
		'title' => 'Delete input format',
		'page callback' => 'drupal_get_form',
		'page arguments' => array('filter_admin_delete'),
		'access arguments' => array('administer filters'),
		'type' => MENU_CALLBACK,
		'file' => 'filter.admin.inc',
	);
	$items['filter/tips'] = array(
		'title' => 'Compose tips',
		'page callback' => 'filter_tips_long',
		'access callback' => TRUE,
		'type' => MENU_SUGGESTED_ITEM,
		'file' => 'filter.pages.inc',
	);
	$items['admin/settings/filters/%filter_format'] = array(
		'type' => MENU_CALLBACK,
		'title callback' => 'filter_admin_format_title',
		'title arguments' => array(3),
		'page callback' => 'filter_admin_format_page',
		'page arguments' => array(3),
		'access arguments' => array('administer filters'),
		'file' => 'filter.admin.inc',
	);

	$items['admin/settings/filters/%filter_format/edit'] = array(
		'title' => 'Edit',
		'type' => MENU_DEFAULT_LOCAL_TASK,
		'weight' => 0,
		'file' => 'filter.admin.inc',
	);
	$items['admin/settings/filters/%filter_format/configure'] = array(
		'title' => 'Configure',
		'page callback' => 'filter_admin_configure_page',
		'page arguments' => array(3),
		'access arguments' => array('administer filters'),
		'type' => MENU_LOCAL_TASK,
		'weight' => 1,
		'file' => 'filter.admin.inc',
	);
	$items['admin/settings/filters/%filter_format/order'] = array(
		'title' => 'Rearrange',
		'page callback' => 'filter_admin_order_page',
		'page arguments' => array(3),
		'access arguments' => array('administer filters'),
		'type' => MENU_LOCAL_TASK,
		'weight' => 2,
		'file' => 'filter.admin.inc',
	);
	return $items;
}

function filter_format_load($arg) {
	return filter_formats($arg);
}

/**
 * Display a filter format form title.
 */
function filter_admin_format_title($format) {
	return $format->name;
}

/**
 * Implementation of hook_perm().
 */
function filter_perm() {
	return array('administer filters');
}

/**
 * Implementation of hook_cron().
 *
 * Expire outdated filter cache entries
 */
function filter_cron() {
	cache_clear_all(NULL, 'cache_filter');
}

/**
 * Implementation of hook_filter_tips().
 */
function filter_filter_tips($delta, $format, $long = FALSE) {
	global $base_url;
	switch ($delta) {
		case 0:
			if (variable_get("filter_html_$format", FILTER_HTML_STRIP) == FILTER_HTML_STRIP) {
				if ($allowed_html = variable_get("allowed_html_$format", '<a> <em> <strong> <cite> <code> <ul> <ol> <li> <dl> <dt> <dd>')) {
					switch ($long) {
						case 0:
							return t('Allowed HTML tags: @tags', array('@tags' => $allowed_html));
						case 1:
							$output = '<p>'. t('Allowed HTML tags: @tags', array('@tags' => $allowed_html)) .'</p>';
							if (!variable_get("filter_html_help_$format", 1)) {
								return $output;
							}

							$output .= t('
<p>This site allows HTML content. While learning all of HTML may feel intimidating, learning how to use a very small number of the most basic HTML "tags" is very easy. This table provides examples for each tag that is enabled on this site.</p>
<p>For more information see W3C\'s <a href="http://www.w3.org/TR/html/">HTML Specifications</a> or use your favorite search engine to find other sites that explain HTML.</p>');
							$tips = array(
								'a' => array( t('Anchors are used to make links to other pages.'), '<a href="'. $base_url .'">'. variable_get('site_name', 'Drupal') .'</a>'),
								'br' => array( t('By default line break tags are automatically added, so use this tag to add additional ones. Use of this tag is different because it is not used with an open/close pair like all the others. Use the extra " /" inside the tag to maintain XHTML 1.0 compatibility'), t('Text with <br />line break')),
								'p' => array( t('By default paragraph tags are automatically added, so use this tag to add additional ones.'), '<p>'. t('Paragraph one.') .'</p> <p>'. t('Paragraph two.') .'</p>'),
								'strong' => array( t('Strong'), '<strong>'. t('Strong') .'</strong>'),
								'em' => array( t('Emphasized'), '<em>'. t('Emphasized') .'</em>'),
								'cite' => array( t('Cited'), '<cite>'. t('Cited') .'</cite>'),
								'code' => array( t('Coded text used to show programming source code'), '<code>'. t('Coded') .'</code>'),
								'b' => array( t('Bolded'), '<b>'. t('Bolded') .'</b>'),
								'u' => array( t('Underlined'), '<u>'. t('Underlined') .'</u>'),
								'i' => array( t('Italicized'), '<i>'. t('Italicized') .'</i>'),
								'sup' => array( t('Superscripted'), t('<sup>Super</sup>scripted')),
								'sub' => array( t('Subscripted'), t('<sub>Sub</sub>scripted')),
								'pre' => array( t('Preformatted'), '<pre>'. t('Preformatted') .'</pre>'),
								'abbr' => array( t('Abbreviation'), t('<abbr title="Abbreviation">Abbrev.</abbr>')),
								'acronym' => array( t('Acronym'), t('<acronym title="Three-Letter Acronym">TLA</acronym>')),
								'blockquote' => array( t('Block quoted'), '<blockquote>'. t('Block quoted') .'</blockquote>'),
								'q' => array( t('Quoted inline'), '<q>'. t('Quoted inline') .'</q>'),
								// Assumes and describes tr, td, th.
								'table' => array( t('Table'), '<table> <tr><th>'. t('Table header') .'</th></tr> <tr><td>'. t('Table cell') .'</td></tr> </table>'),
								'tr' => NULL, 'td' => NULL, 'th' => NULL,
								'del' => array( t('Deleted'), '<del>'. t('Deleted') .'</del>'),
								'ins' => array( t('Inserted'), '<ins>'. t('Inserted') .'</ins>'),
								 // Assumes and describes li.
								'ol' => array( t('Ordered list - use the &lt;li&gt; to begin each list item'), '<ol> <li>'. t('First item') .'</li> <li>'. t('Second item') .'</li> </ol>'),
								'ul' => array( t('Unordered list - use the &lt;li&gt; to begin each list item'), '<ul> <li>'. t('First item') .'</li> <li>'. t('Second item') .'</li> </ul>'),
								'li' => NULL,
								// Assumes and describes dt and dd.
								'dl' => array( t('Definition lists are similar to other HTML lists. &lt;dl&gt; begins the definition list, &lt;dt&gt; begins the definition term and &lt;dd&gt; begins the definition description.'), '<dl> <dt>'. t('First term') .'</dt> <dd>'. t('First definition') .'</dd> <dt>'. t('Second term') .'</dt> <dd>'. t('Second definition') .'</dd> </dl>'),
								'dt' => NULL, 'dd' => NULL,
								'h1' => array( t('Header'), '<h1>'. t('Title') .'</h1>'),
								'h2' => array( t('Header'), '<h2>'. t('Subtitle') .'</h2>'),
								'h3' => array( t('Header'), '<h3>'. t('Subtitle three') .'</h3>'),
								'h4' => array( t('Header'), '<h4>'. t('Subtitle four') .'</h4>'),
								'h5' => array( t('Header'), '<h5>'. t('Subtitle five') .'</h5>'),
								'h6' => array( t('Header'), '<h6>'. t('Subtitle six') .'</h6>')
							);
							$header = array(t('Tag Description'), t('You Type'), t('You Get'));
							preg_match_all('/<([a-z0-9]+)[^a-z0-9]/i', $allowed_html, $out);
							foreach ($out[1] as $tag) {
								if (array_key_exists($tag, $tips)) {
									if ($tips[$tag]) {
										$rows[] = array(
											array('data' => $tips[$tag][0], 'class' => 'description'),
											array('data' => '<code>'. check_plain($tips[$tag][1]) .'</code>', 'class' => 'type'),
											array('data' => $tips[$tag][1], 'class' => 'get')
										);
									}
								}
								else {
									$rows[] = array(
										array('data' => t('No help provided for tag %tag.', array('%tag' => $tag)), 'class' => 'description', 'colspan' => 3),
									);
								}
							}
							$output .= theme('table', $header, $rows);

							$output .= t('
<p>Most unusual characters can be directly entered without any problems.</p>
<p>If you do encounter problems, try using HTML character entities. A common example looks like &amp;amp; for an ampersand &amp; character. For a full list of entities see HTML\'s <a href="http://www.w3.org/TR/html4/sgml/entities.html">entities</a> page. Some of the available characters include:</p>');
							$entities = array(
								array( t('Ampersand'), '&amp;'),
								array( t('Greater than'), '&gt;'),
								array( t('Less than'), '&lt;'),
								array( t('Quotation mark'), '&quot;'),
							);
							$header = array(t('Character Description'), t('You Type'), t('You Get'));
							unset($rows);
							foreach ($entities as $entity) {
								$rows[] = array(
									array('data' => $entity[0], 'class' => 'description'),
									array('data' => '<code>'. check_plain($entity[1]) .'</code>', 'class' => 'type'),
									array('data' => $entity[1], 'class' => 'get')
								);
							}
							$output .= theme('table', $header, $rows);
							return $output;
					}
				}
				else {
					return t('No HTML tags allowed');
				}
			}
			break;

		case 1:
			switch ($long) {
				case 0:
					return t('Lines and paragraphs break automatically.');
				case 1:
					return t('Lines and paragraphs are automatically recognized. The &lt;br /&gt; line break, &lt;p&gt; paragraph and &lt;/p&gt; close paragraph tags are inserted automatically. If paragraphs are not recognized simply add a couple blank lines.');
			}
			break;
		case 2:
			return t('Web page addresses and e-mail addresses turn into links automatically.');
	}
}

/**
 * Retrieve a list of input formats.
 */
function filter_formats($index = NULL) {
	global $user;
	static $formats;

	// Administrators can always use all input formats.
	$all = user_access('administer filters');

	if (!isset($formats)) {
		$formats = array();

		$query = 'SELECT * FROM {filter_formats}';

		// Build query for selecting the format(s) based on the user's roles.
		$args = array();
		if (!$all) {
			$where = array();
			foreach ($user->roles as $rid => $role) {
				$where[] = "roles LIKE '%%,%d,%%'";
				$args[] = $rid;
			}
			$query .= ' WHERE '. implode(' OR ', $where) .' OR format = %d';
			$args[] = variable_get('filter_default_format', 1);
		}

		$result = db_query($query, $args);
		while ($format = db_fetch_object($result)) {
			$formats[$format->format] = $format;
		}
	}
	if (isset($index)) {
		return isset($formats[$index]) ? $formats[$index] : FALSE;
	}
	return $formats;
}

/**
 * Build a list of all filters.
 */
function filter_list_all() {
	$filters = array();

	foreach (module_list() as $module) {
		$list = module_invoke($module, 'filter', 'list');
		if (isset($list) && is_array($list)) {
			foreach ($list as $delta => $name) {
				$filters[$module .'/'. $delta] = (object)array('module' => $module, 'delta' => $delta, 'name' => $name);
			}
		}
	}

	uasort($filters, '_filter_list_cmp');

	return $filters;
}

/**
 * Helper function for sorting the filter list by filter name.
 */
function _filter_list_cmp($a, $b) {
	return strcmp($a->name, $b->name);
}

/**
 * Resolve a format id, including the default format.
 */
function filter_resolve_format($format) {
	return $format == FILTER_FORMAT_DEFAULT ? variable_get('filter_default_format', 1) : $format;
}
/**
 * Check if text in a certain input format is allowed to be cached.
 */
function filter_format_allowcache($format) {
	static $cache = array();
	$format = filter_resolve_format($format);
	if (!isset($cache[$format])) {
		$cache[$format] = db_result(db_query('SELECT cache FROM {filter_formats} WHERE format = %d', $format));
	}
	return $cache[$format];
}

/**
 * Retrieve a list of filters for a certain format.
 */
function filter_list_format($format) {
	static $filters = array();

	if (!isset($filters[$format])) {
		$result = db_query("SELECT * FROM {filters} WHERE format = %d ORDER BY weight, module, delta", $format);
		if (db_affected_rows($result) == 0 && !db_result(db_query("SELECT 1 FROM {filter_formats} WHERE format = %d", $format))) {
			// The format has no filters and does not exist, use the default input
			// format.
			$filters[$format] = filter_list_format(variable_get('filter_default_format', 1));
		}
		else {
			$filters[$format] = array();
			while ($filter = db_fetch_object($result)) {
				$list = module_invoke($filter->module, 'filter', 'list');
				if (isset($list) && is_array($list) && isset($list[$filter->delta])) {
					$filter->name = $list[$filter->delta];
					$filters[$format][$filter->module .'/'. $filter->delta] = $filter;
				}
			}
		}
	}

	return $filters[$format];
}

/**
 * @defgroup filtering_functions Filtering functions
 * @{
 * Functions for interacting with the content filtering system.
 *
 * For more info, see the hook_filter() documentation.
 *
 * Note: because filters can inject JavaScript or execute PHP code, security is
 * vital here. When a user supplies a $format, you should validate it with
 * filter_access($format) before accepting/using it. This is normally done in
 * the validation stage of the node system. You should for example never make a
 * preview of content in a disallowed format.
 */

/**
 * Run all the enabled filters on a piece of text.
 *
 * @param $text
 *    The text to be filtered.
 * @param $format
 *    The format of the text to be filtered. Specify FILTER_FORMAT_DEFAULT for
 *    the default format.
 * @param $check
 *    Whether to check the $format with filter_access() first. Defaults to TRUE.
 *    Note that this will check the permissions of the current user, so you
 *    should specify $check = FALSE when viewing other people's content. When
 *    showing content that is not (yet) stored in the database (eg. upon preview),
 *    set to TRUE so the user's permissions are checked.
 */
function check_markup($text, $format = FILTER_FORMAT_DEFAULT, $check = TRUE) {
	// When $check = TRUE, do an access check on $format.
	if (isset($text) && (!$check || filter_access($format))) {
		$format = filter_resolve_format($format);

		// Check for a cached version of this piece of text.
		$cache_id = $format .':'. md5($text);
		if ($cached = cache_get($cache_id, 'cache_filter')) {
			return $cached->data;
		}

		// See if caching is allowed for this format.
		$cache = filter_format_allowcache($format);

		// Convert all Windows and Mac newlines to a single newline,
		// so filters only need to deal with one possibility.
		$text = str_replace(array("\r\n", "\r"), "\n", $text);

		// Get a complete list of filters, ordered properly.
		$filters = filter_list_format($format);

		// Give filters the chance to escape HTML-like data such as code or formulas.
		foreach ($filters as $filter) {
			$text = module_invoke($filter->module, 'filter', 'prepare', $filter->delta, $format, $text, $cache_id);
		}

		// Perform filtering.
		foreach ($filters as $filter) {
			$text = module_invoke($filter->module, 'filter', 'process', $filter->delta, $format, $text, $cache_id);
		}

		// Store in cache with a minimum expiration time of 1 day.
		if ($cache) {
			cache_set($cache_id, $text, 'cache_filter', time() + (60 * 60 * 24));
		}
	}
	else {
		$text = t('n/a');
	}

	return $text;
}

/**
 * Generates a selector for choosing a format in a form.
 *
 * @param $value
 *   The ID of the format that is currently selected; uses the default format
 *   if not provided.
 * @param $weight
 *   The weight of the form element within the form.
 * @param $parents
 *   The parents array of the element. Required when defining multiple text
 *   formats on a single form or having a different parent than 'format'.
 *
 * @return
 *   Form API array for the form element.
 *
 * @see filter_form_validate()
 * @ingroup forms
 */
function filter_form($value = FILTER_FORMAT_DEFAULT, $weight = NULL, $parents = array('format')) {
	$value = filter_resolve_format($value);
	$formats = filter_formats();

	$extra = theme('filter_tips_more_info');

	if (count($formats) > 1) {
		$form = array(
			'#type' => 'fieldset',
			'#title' => t('Input format'),
			'#collapsible' => TRUE,
			'#collapsed' => TRUE,
			'#weight' => $weight,
			'#element_validate' => array('filter_form_validate'),
		);
		// Multiple formats available: display radio buttons with tips.
		foreach ($formats as $format) {
			// Generate the parents as the autogenerator does, so we will have a
			// unique id for each radio button.
			$parents_for_id = array_merge($parents, array($format->format));
			$form[$format->format] = array(
				'#type' => 'radio',
				'#title' => $format->name,
				'#default_value' => $value,
				'#return_value' => $format->format,
				'#parents' => $parents,
				'#description' => theme('filter_tips', _filter_tips($format->format, FALSE)),
				'#id' => form_clean_id('edit-'. implode('-', $parents_for_id)),
			);
		}
	}
	else {
		// Only one format available: use a hidden form item and only show tips.
		$format = array_shift($formats);
		$form[$format->format] = array('#type' => 'value', '#value' => $format->format, '#parents' => $parents);
		$tips = _filter_tips(variable_get('filter_default_format', 1), FALSE);
		$form['format']['guidelines'] = array(
			'#title' => t('Formatting guidelines'),
			'#value' => theme('filter_tips', $tips, FALSE, $extra),
		);
	}
	$form[] = array('#value' => $extra);
	return $form;
}

/**
 * Validation callback for filter elements in a form.
 *
 * @see filter_form().
 */
function filter_form_validate($form) {
	foreach (element_children($form) as $key) {
		if ($form[$key]['#value'] == $form[$key]['#return_value']) {
			return;
		}
	}
	form_error($form, t('An illegal choice has been detected. Please contact the site administrator.'));
	watchdog('form', 'Illegal choice %choice in %name element.', array('%choice' => $form[$key]['#value'], '%name' => empty($form['#title']) ? $form['#parents'][0] : $form['#title']), WATCHDOG_ERROR);
}

/**
 * Returns TRUE if the user is allowed to access this format.
 */
function filter_access($format) {
	$format = filter_resolve_format($format);
	if (user_access('administer filters') || ($format == variable_get('filter_default_format', 1))) {
		return TRUE;
	}
	else {
		$formats = filter_formats();
		return isset($formats[$format]);
	}
}

/**
 * @} End of "Filtering functions".
 */


/**
 * Helper function for fetching filter tips.
 */
function _filter_tips($format, $long = FALSE) {
	if ($format == -1) {
		$formats = filter_formats();
	}
	else {
		$formats = array(db_fetch_object(db_query("SELECT * FROM {filter_formats} WHERE format = %d", $format)));
	}

	$tips = array();

	foreach ($formats as $format) {
		$filters = filter_list_format($format->format);

		$tips[$format->name] = array();
		foreach ($filters as $id => $filter) {
			if ($tip = module_invoke($filter->module, 'filter_tips', $filter->delta, $format->format, $long)) {
				$tips[$format->name][] = array('tip' => $tip, 'id' => $id);
			}
		}
	}

	return $tips;
}


/**
 * Format a link to the more extensive filter tips.
 *
 * @ingroup themeable
 */
function theme_filter_tips_more_info() {
	return '<p>'. l(t('More information about formatting options'), 'filter/tips') .'</p>';
}

/**
 * @defgroup standard_filters Standard filters
 * @{
 * Filters implemented by the filter.module.
 */

/**
 * Implementation of hook_filter().
 *
 * Sets up a basic set of essential filters.
 * - HTML filter: Restricts user-supplied HTML to certain tags, and removes
 *   dangerous components in allowed tags.
 * - Line break converter: Converts newlines into paragraph and break tags.
 * - URL filter: Converts URLs and e-mail addresses into links.
 * - HTML corrector: Fixes faulty HTML.
 */
function filter_filter($op, $delta = 0, $format = -1, $text = '') {
	switch ($op) {
		case 'list':
			return array(0 => t('HTML filter'), 1 => t('Line break converter'), 2 => t('URL filter'), 3 => t('HTML corrector'));

		case 'description':
			switch ($delta) {
				case 0:
					return t('Allows you to restrict whether users can post HTML and which tags to filter out. It will also remove harmful content such as JavaScript events, JavaScript URLs and CSS styles from those tags that are not removed.');
				case 1:
					return t('Converts line breaks into HTML (i.e. &lt;br&gt; and &lt;p&gt; tags).');
				case 2:
					return t('Turns web and e-mail addresses into clickable links.');
				case 3:
					return t('Corrects faulty and chopped off HTML in postings.');
				default:
					return;
			}

		case 'process':
			switch ($delta) {
				case 0:
					return _filter_html($text, $format);
				case 1:
					return _filter_autop($text);
				case 2:
					return _filter_url($text, $format);
				case 3:
					return _filter_htmlcorrector($text);
				default:
					return $text;
			}

		case 'settings':
			switch ($delta) {
				case 0:
					return _filter_html_settings($format);
				case 2:
					return _filter_url_settings($format);
				default:
					return;
			}

		default:
			return $text;
	}
}

/**
 * Settings for the HTML filter.
 */
function _filter_html_settings($format) {
	$form['filter_html'] = array(
		'#type' => 'fieldset',
		'#title' => t('HTML filter'),
		'#collapsible' => TRUE,
	);
	$form['filter_html']["filter_html_$format"] = array(
		'#type' => 'radios',
		'#title' => t('Filter HTML tags'),
		'#default_value' => variable_get("filter_html_$format", FILTER_HTML_STRIP),
		'#options' => array(FILTER_HTML_STRIP => t('Strip disallowed tags'), FILTER_HTML_ESCAPE => t('Escape all tags')),
		'#description' => t('How to deal with HTML tags in user-contributed content. If set to "Strip disallowed tags", dangerous tags are removed (see below). If set to "Escape tags", all HTML is escaped and presented as it was typed.'),
	);
	$form['filter_html']["allowed_html_$format"] = array(
		'#type' => 'textfield',
		'#title' => t('Allowed HTML tags'),
		'#default_value' => variable_get("allowed_html_$format", '<a> <em> <strong> <cite> <code> <ul> <ol> <li> <dl> <dt> <dd>'),
		'#size' => 64,
		'#maxlength' => 1024,
		'#description' => t('If "Strip disallowed tags" is selected, optionally specify tags which should not be stripped. JavaScript event attributes are always stripped.'),
	);
	$form['filter_html']["filter_html_help_$format"] = array(
		'#type' => 'checkbox',
		'#title' => t('Display HTML help'),
		'#default_value' => variable_get("filter_html_help_$format", 1),
		'#description' => t('If enabled, Drupal will display some basic HTML help in the long filter tips.'),
	);
	$form['filter_html']["filter_html_nofollow_$format"] = array(
		'#type' => 'checkbox',
		'#title' => t('Spam link deterrent'),
		'#default_value' => variable_get("filter_html_nofollow_$format", FALSE),
		'#description' => t('If enabled, Drupal will add rel="nofollow" to all links, as a measure to reduce the effectiveness of spam links. Note: this will also prevent valid links from being followed by search engines, therefore it is likely most effective when enabled for anonymous users.'),
	);
	return $form;
}

/**
 * HTML filter. Provides filtering of input into accepted HTML.
 */
function _filter_html($text, $format) {
	if (variable_get("filter_html_$format", FILTER_HTML_STRIP) == FILTER_HTML_STRIP) {
		$allowed_tags = preg_split('/\s+|<|>/', variable_get("allowed_html_$format", '<a> <em> <strong> <cite> <code> <ul> <ol> <li> <dl> <dt> <dd>'), -1, PREG_SPLIT_NO_EMPTY);
		$text = filter_xss($text, $allowed_tags);
	}

	if (variable_get("filter_html_$format", FILTER_HTML_STRIP) == FILTER_HTML_ESCAPE) {
		// Escape HTML
		$text = check_plain($text);
	}

	if (variable_get("filter_html_nofollow_$format", FALSE)) {
		$text = preg_replace('/<a([^>]+)>/i', '<a\\1 rel="nofollow">', $text);
	}

	return trim($text);
}

/**
 * Settings for URL filter.
 */
function _filter_url_settings($format) {
	$form['filter_urlfilter'] = array(
		'#type' => 'fieldset',
		'#title' => t('URL filter'),
		'#collapsible' => TRUE,
	);
	$form['filter_urlfilter']['filter_url_length_'. $format] = array(
		'#type' => 'textfield',
		'#title' => t('Maximum link text length'),
		'#default_value' => variable_get('filter_url_length_'. $format, 72),
		'#maxlength' => 4,
		'#description' => t('URLs longer than this number of characters will be truncated to prevent long strings that break formatting. The link itself will be retained; just the text portion of the link will be truncated.'),
	);
	return $form;
}

/**
 * URL filter. Automatically converts text web addresses (URLs, e-mail addresses,
 * FTP links, etc.) into hyperlinks.
 */
function _filter_url($text, $format) {
	// Pass length to regexp callback
	_filter_url_trim(NULL, variable_get('filter_url_length_'. $format, 72));

	$text = ' '. $text .' ';

	// Match absolute URLs.
	$text = preg_replace_callback("`(<p>|<li>|<br\s*/?>|[ \n\r\t\(])((http://|https://|ftp://|mailto:|smb://|afp://|file://|gopher://|news://|ssl://|sslv2://|sslv3://|tls://|tcp://|udp://)([a-zA-Z0-9@:%_+*~#?&=.,/;-]*[a-zA-Z0-9@:%_+*~#&=/;-]))([.,?!]*?)(?=(</p>|</li>|<br\s*/?>|[ \n\r\t\)]))`i", '_filter_url_parse_full_links', $text);

	// Match e-mail addresses.
	$text = preg_replace("`(<p>|<li>|<br\s*/?>|[ \n\r\t\(])([A-Za-z0-9._-]+@[A-Za-z0-9._+-]+\.[A-Za-z]{2,4})([.,?!]*?)(?=(</p>|</li>|<br\s*/?>|[ \n\r\t\)]))`i", '\1<a href="mailto:\2">\2</a>\3', $text);

	// Match www domains/addresses.
	$text = preg_replace_callback("`(<p>|<li>|[ \n\r\t\(])(www\.[a-zA-Z0-9@:%_+*~#?&=.,/;-]*[a-zA-Z0-9@:%_+~#\&=/;-])([.,?!]*?)(?=(</p>|</li>|<br\s*/?>|[ \n\r\t\)]))`i", '_filter_url_parse_partial_links', $text);
	$text = substr($text, 1, -1);

	return $text;
}

/**
 * Scan input and make sure that all HTML tags are properly closed and nested.
 */
function _filter_htmlcorrector($text) {
	// Prepare tag lists.
	static $no_nesting, $single_use;
	if (!isset($no_nesting)) {
		// Tags which cannot be nested but are typically left unclosed.
		$no_nesting = drupal_map_assoc(array('li', 'p'));

		// Single use tags in HTML4
		$single_use = drupal_map_assoc(array('base', 'meta', 'link', 'hr', 'br', 'param', 'img', 'area', 'input', 'col', 'frame'));
	}

	// Properly entify angles.
	$text = preg_replace('@<(?=[^a-zA-Z!/]|$)@', '&lt;', $text);

	// Split tags from text.
	$split = preg_split('/<(!--.*?--|[^>]+?)>/s', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
	// Note: PHP ensures the array consists of alternating delimiters and literals
	// and begins and ends with a literal (inserting $null as required).

	$tag = false; // Odd/even counter. Tag or no tag.
	$stack = array();
	$output = '';
	foreach ($split as $value) {
		// Process HTML tags.
		if ($tag) {
			// Passthrough comments.
			if (substr($value, 0, 3) == '!--') {
				$output .= '<'. $value .'>';
			}
			else {
				list($tagname) = preg_split('/\s/', strtolower($value), 2);
				// Closing tag
				if ($tagname{0} == '/') {
					$tagname = substr($tagname, 1);
					// Discard XHTML closing tags for single use tags.
					if (!isset($single_use[$tagname])) {
						// See if we possibly have a matching opening tag on the stack.
						if (in_array($tagname, $stack)) {
							// Close other tags lingering first.
							do {
								$output .= '</'. $stack[0] .'>';
							} while (array_shift($stack) != $tagname);
						}
						// Otherwise, discard it.
					}
				}
				// Opening tag
				else {
					// See if we have an identical 'no nesting' tag already open and close it if found.
					if (count($stack) && ($stack[0] == $tagname) && isset($no_nesting[$stack[0]])) {
						$output .= '</'. array_shift($stack) .'>';
					}
					// Push non-single-use tags onto the stack
					if (!isset($single_use[$tagname])) {
						array_unshift($stack, $tagname);
					}
					// Add trailing slash to single-use tags as per X(HT)ML.
					else {
						$value = rtrim($value, ' /') .' /';
					}
					$output .= '<'. $value .'>';
				}
			}
		}
		else {
			// Passthrough all text.
			$output .= $value;
		}
		$tag = !$tag;
	}
	// Close remaining tags.
	while (count($stack) > 0) {
		$output .= '</'. array_shift($stack) .'>';
	}
	return $output;
}

/**
 * Make links out of absolute URLs.
 */
function _filter_url_parse_full_links($match) {
	$match[2] = decode_entities($match[2]);
	$caption = check_plain(_filter_url_trim($match[2]));
	$match[2] = check_url($match[2]);
	return $match[1] .'<a href="'. $match[2] .'" title="'. $match[2] .'">'. $caption .'</a>'. $match[5];
}

/**
 * Make links out of domain names starting with "www."
 */
function _filter_url_parse_partial_links($match) {
	$match[2] = decode_entities($match[2]);
	$caption = check_plain(_filter_url_trim($match[2]));
	$match[2] = check_plain($match[2]);
	return $match[1] .'<a href="http://'. $match[2] .'" title="'. $match[2] .'">'. $caption .'</a>'. $match[3];
}

/**
 * Shortens long URLs to http://www.example.com/long/url...
 */
function _filter_url_trim($text, $length = NULL) {
	static $_length;
	if ($length !== NULL) {
		$_length = $length;
	}

	// Use +3 for '...' string length.
	if (strlen($text) > $_length + 3) {
		$text = substr($text, 0, $_length) .'...';
	}

	return $text;
}

/**
 * Convert line breaks into <p> and <br> in an intelligent fashion.
 * Based on: http://photomatt.net/scripts/autop
 */
function _filter_autop($text) {
	// All block level tags
	$block = '(?:table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|address|p|h[1-6]|hr)';

	// Split at <pre>, <script>, <style> and </pre>, </script>, </style> tags.
	// We don't apply any processing to the contents of these tags to avoid messing
	// up code. We look for matched pairs and allow basic nesting. For example:
	// "processed <pre> ignored <script> ignored </script> ignored </pre> processed"
	$chunks = preg_split('@(<(?:!--.*?--|/?(?:pre|script|style|object)[^>]*)>)@si', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
	// Note: PHP ensures the array consists of alternating delimiters and literals
	// and begins and ends with a literal (inserting NULL as required).
	$ignore = FALSE;
	$ignoretag = '';
	$output = '';
	foreach ($chunks as $i => $chunk) {
		if ($i % 2) {
			// Passthrough comments.
			if (substr($chunk, 1, 3) == '!--') {
				$output .= $chunk;
			}
			else {
				// Opening or closing tag?
				$open = ($chunk[1] != '/');
				list($tag) = split('[ >]', substr($chunk, 2 - $open), 2);
				if (!$ignore) {
					if ($open) {
						$ignore = TRUE;
						$ignoretag = $tag;
					}
				}
				// Only allow a matching tag to close it.
				else if (!$open && $ignoretag == $tag) {
					$ignore = FALSE;
					$ignoretag = '';
				}
			}
		}
		else if (!$ignore) {
			$chunk = preg_replace('|\n*$|', '', $chunk) ."\n\n"; // just to make things a little easier, pad the end
			$chunk = preg_replace('|<br />\s*<br />|', "\n\n", $chunk);
			$chunk = preg_replace('!(<'. $block .'[^>]*>)!', "\n$1", $chunk); // Space things out a little
			$chunk = preg_replace('!(</'. $block .'>)!', "$1\n\n", $chunk); // Space things out a little
			$chunk = preg_replace("/\n\n+/", "\n\n", $chunk); // take care of duplicates
			$chunk = preg_replace('/^\n|\n\s*\n$/', '', $chunk);
			$chunk = '<p>'. preg_replace('/\n\s*\n\n?(.)/', "</p>\n<p>$1", $chunk) ."</p>\n"; // make paragraphs, including one at the end
			$chunk = preg_replace("|<p>(<li.+?)</p>|", "$1", $chunk); // problem with nested lists
			$chunk = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $chunk);
			$chunk = str_replace('</blockquote></p>', '</p></blockquote>', $chunk);
			$chunk = preg_replace('|<p>\s*</p>\n?|', '', $chunk); // under certain strange conditions it could create a P of entirely whitespace
			$chunk = preg_replace('!<p>\s*(</?'. $block .'[^>]*>)!', "$1", $chunk);
			$chunk = preg_replace('!(</?'. $block .'[^>]*>)\s*</p>!', "$1", $chunk);
			$chunk = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $chunk); // make line breaks
			$chunk = preg_replace('!(</?'. $block .'[^>]*>)\s*<br />!', "$1", $chunk);
			$chunk = preg_replace('!<br />(\s*</?(?:p|li|div|th|pre|td|ul|ol)>)!', '$1', $chunk);
			$chunk = preg_replace('/&([^#])(?![A-Za-z0-9]{1,8};)/', '&amp;$1', $chunk);
		}
		$output .= $chunk;
	}
	return $output;
}

/**
 * Very permissive XSS/HTML filter for admin-only use.
 *
 * Use only for fields where it is impractical to use the
 * whole filter system, but where some (mainly inline) mark-up
 * is desired (so check_plain() is not acceptable).
 *
 * Allows all tags that can be used inside an HTML body, save
 * for scripts and styles.
 */
function filter_xss_admin($string) {
	return filter_xss($string, array('a', 'abbr', 'acronym', 'address', 'b', 'bdo', 'big', 'blockquote', 'br', 'caption', 'cite', 'code', 'col', 'colgroup', 'dd', 'del', 'dfn', 'div', 'dl', 'dt', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'ins', 'kbd', 'li', 'ol', 'p', 'pre', 'q', 'samp', 'small', 'span', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'tt', 'ul', 'var'));
}

/**
 * Filters an HTML string to prevent cross-site-scripting (XSS) vulnerabilities.
 *
 * Based on kses by Ulf Harnhammar, see http://sourceforge.net/projects/kses.
 * For examples of various XSS attacks, see http://ha.ckers.org/xss.html.
 *
 * This code does four things:
 * - Removes characters and constructs that can trick browsers.
 * - Makes sure all HTML entities are well-formed.
 * - Makes sure all HTML tags and attributes are well-formed.
 * - Makes sure no HTML tags contain URLs with a disallowed protocol (e.g.
 *   javascript:).
 *
 * @param $string
 *   The string with raw HTML in it. It will be stripped of everything that can
 *   cause an XSS attack.
 * @param $allowed_tags
 *   An array of allowed tags.
 *
 * @return
 *   An XSS safe version of $string, or an empty string if $string is not
 *   valid UTF-8.
 *
 * @see drupal_validate_utf8()
 * @ingroup sanitization
 */
function filter_xss($string, $allowed_tags = array('a', 'em', 'strong', 'cite', 'code', 'ul', 'ol', 'li', 'dl', 'dt', 'dd')) {
	// Only operate on valid UTF-8 strings. This is necessary to prevent cross
	// site scripting issues on Internet Explorer 6.
	if (!drupal_validate_utf8($string)) {
		return '';
	}
	// Store the input format
	_filter_xss_split($allowed_tags, TRUE);
	// Remove NUL characters (ignored by some browsers)
	$string = str_replace(chr(0), '', $string);
	// Remove Netscape 4 JS entities
	$string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);

	// Defuse all HTML entities
	$string = str_replace('&', '&amp;', $string);
	// Change back only well-formed entities in our whitelist
	// Decimal numeric entities
	$string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);
	// Hexadecimal numeric entities
	$string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string);
	// Named entities
	$string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string);

	return preg_replace_callback('%
		(
		<(?=[^a-zA-Z!/])  # a lone <
		|                 # or
		<!--.*?-->        # a comment
		|                 # or
		<[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
		|                 # or
		>                 # just a >
		)%x', '_filter_xss_split', $string);
}

/**
 * Processes an HTML tag.
 *
 * @param @m
 *   An array with various meaning depending on the value of $store.
 *   If $store is TRUE then the array contains the allowed tags.
 *   If $store is FALSE then the array has one element, the HTML tag to process.
 * @param $store
 *   Whether to store $m.
 * @return
 *   If the element isn't allowed, an empty string. Otherwise, the cleaned up
 *   version of the HTML element.
 */
function _filter_xss_split($m, $store = FALSE) {
	static $allowed_html;

	if ($store) {
		$allowed_html = array_flip($m);
		return;
	}

	$string = $m[1];

	if (substr($string, 0, 1) != '<') {
		// We matched a lone ">" character
		return '&gt;';
	}
	else if (strlen($string) == 1) {
		// We matched a lone "<" character
		return '&lt;';
	}

	if (!preg_match('%^(?:<\s*(/\s*)?([a-zA-Z0-9]+)([^>]*)>?|(<!--.*?-->))$%', $string, $matches)) {
		// Seriously malformed
		return '';
	}

	$slash = trim($matches[1]);
	$elem = &$matches[2];
	$attrlist = &$matches[3];
	$comment = &$matches[4];

	if ($comment) {
		$elem = '!--';
	}

	if (!isset($allowed_html[strtolower($elem)])) {
		// Disallowed HTML element
		return '';
	}

	if ($comment) {
		return $comment;
	}

	if ($slash != '') {
		return "</$elem>";
	}

	// Is there a closing XHTML slash at the end of the attributes?
	// In PHP 5.1.0+ we could count the changes, currently we need a separate match
	$xhtml_slash = preg_match('%\s?/\s*$%', $attrlist) ? ' /' : '';
	$attrlist = preg_replace('%(\s?)/\s*$%', '\1', $attrlist);

	// Clean up attributes
	$attr2 = implode(' ', _filter_xss_attributes($attrlist));
	$attr2 = preg_replace('/[<>]/', '', $attr2);
	$attr2 = strlen($attr2) ? ' '. $attr2 : '';

	return "<$elem$attr2$xhtml_slash>";
}

/**
 * Processes a string of HTML attributes.
 *
 * @return
 *   Cleaned up version of the HTML attributes.
 */
function _filter_xss_attributes($attr) {
	$attrarr = array();
	$mode = 0;
	$attrname = '';

	while (strlen($attr) != 0) {
		// Was the last operation successful?
		$working = 0;

		switch ($mode) {
			case 0:
				// Attribute name, href for instance
				if (preg_match('/^([-a-zA-Z]+)/', $attr, $match)) {
					$attrname = strtolower($match[1]);
					$skip = ($attrname == 'style' || substr($attrname, 0, 2) == 'on');
					$working = $mode = 1;
					$attr = preg_replace('/^[-a-zA-Z]+/', '', $attr);
				}

				break;

			case 1:
				// Equals sign or valueless ("selected")
				if (preg_match('/^\s*=\s*/', $attr)) {
					$working = 1; $mode = 2;
					$attr = preg_replace('/^\s*=\s*/', '', $attr);
					break;
				}

				if (preg_match('/^\s+/', $attr)) {
					$working = 1; $mode = 0;
					if (!$skip) {
						$attrarr[] = $attrname;
					}
					$attr = preg_replace('/^\s+/', '', $attr);
				}

				break;

			case 2:
				// Attribute value, a URL after href= for instance
				if (preg_match('/^"([^"]*)"(\s+|$)/', $attr, $match)) {
					$thisval = filter_xss_bad_protocol($match[1]);

					if (!$skip) {
						$attrarr[] = "$attrname=\"$thisval\"";
					}
					$working = 1;
					$mode = 0;
					$attr = preg_replace('/^"[^"]*"(\s+|$)/', '', $attr);
					break;
				}

				if (preg_match("/^'([^']*)'(\s+|$)/", $attr, $match)) {
					$thisval = filter_xss_bad_protocol($match[1]);

					if (!$skip) {
						$attrarr[] = "$attrname='$thisval'";;
					}
					$working = 1; $mode = 0;
					$attr = preg_replace("/^'[^']*'(\s+|$)/", '', $attr);
					break;
				}

				if (preg_match("%^([^\s\"']+)(\s+|$)%", $attr, $match)) {
					$thisval = filter_xss_bad_protocol($match[1]);

					if (!$skip) {
						$attrarr[] = "$attrname=\"$thisval\"";
					}
					$working = 1; $mode = 0;
					$attr = preg_replace("%^[^\s\"']+(\s+|$)%", '', $attr);
				}

				break;
		}

		if ($working == 0) {
			// not well formed, remove and try again
			$attr = preg_replace('/
				^
				(
				"[^"]*("|$)     # - a string that starts with a double quote, up until the next double quote or the end of the string
				|               # or
				\'[^\']*(\'|$)| # - a string that starts with a quote, up until the next quote or the end of the string
				|               # or
				\S              # - a non-whitespace character
				)*              # any number of the above three
				\s*             # any number of whitespaces
				/x', '', $attr);
			$mode = 0;
		}
	}

	// the attribute list ends with a valueless attribute like "selected"
	if ($mode == 1) {
		$attrarr[] = $attrname;
	}
	return $attrarr;
}

/**
 * @} End of "Standard filters".
 */

echo filter_filter('process', $argv[1], $argv[2], file_get_contents("php://stdin"));