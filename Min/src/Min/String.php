<?php


/*
	strip_tags
	htmlspecialchars --- html_entity_decode
	htmlentities
	addslashes   -----   stripslashes

*/

public static function escape($text) {
  return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

public static function decodeEntities($text) {
  return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
}

public static function render($string) {
    return decodeEntities(strip_tags($string));
}


function drupal_validate_utf8($text) {
  if (strlen($text) == 0) {
    return TRUE;
  }
  return (preg_match('/^./us', $text) == 1);
}



// url







function filter_xss($string, $allowed_tags = ['a', 'em', 'strong', 'cite', 'blockquote', 'code', 'ul', 'ol', 'li', 'dl', 'dt', 'dd']) 
{
	// Only operate on valid UTF-8 strings. This is necessary to prevent cross
	// site scripting issues on Internet Explorer 6.
	if (!validate_utf8($string)) {
	return '';
	}
	// Store the text format.
	//_filter_xss_split($allowed_tags, TRUE);
	// Remove NULL characters (ignored by some browsers).
	$string = str_replace(chr(0), '', $string);
	// Remove Netscape 4 JS entities.
	$string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);

	// Defuse all HTML entities.
	$string = str_replace('&', '&amp;', $string);
	// Change back only well-formed entities in our whitelist:
	// Decimal numeric entities.
	$string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);
	// Hexadecimal numeric entities.
	$string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string);
	// Named entities.
	$string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string);
  
	$splitter = function ($matches) use ($allowed_tags) {
		return _filter_xss_split($matches[1], $allowed_tags);
    };
	
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

function _filter_xss_split($string, $allowed_html) 
{
  
	if (substr($string, 0, 1) != '<') {
		// We matched a lone ">" character.
		return '&gt;';
	} elseif (strlen($string) == 1) {
		// We matched a lone "<" character.
		return '&lt;';
	}

	if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9\-]+)([^>]*)>?|(<!--.*?-->)$%', $string, $matches)) {
		// Seriously malformed.
		return '';
	}

	$slash		= 	trim($matches[1]);
	$elem		= 	&$matches[2];
	$attrlist	= 	&$matches[3];
	$comment	= 	&$matches[4];

	if ($comment) {
		$elem = '!--';
	}

	if (!isset($allowed_html[strtolower($elem)])) {
		// Disallowed HTML element.
		return '';
	}

	if ($comment) {
		return $comment;
	}

	if ($slash != '') {
		return "</$elem>";
	}

	// Is there a closing XHTML slash at the end of the attributes?
	$attrlist = preg_replace('%(\s?)/\s*$%', '\1', $attrlist, -1, $count);
	$xhtml_slash = $count ? ' /' : '';

	// Clean up attributes.
	$attr2 = implode(' ', _filter_xss_attributes($attrlist));
	$attr2 = preg_replace('/[<>]/', '', $attr2);
	$attr2 = strlen($attr2) ? ' ' . $attr2 : '';

	return "<$elem$attr2$xhtml_slash>";
}