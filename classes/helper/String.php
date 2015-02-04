<?php

class Shopgate_Helper_String {

	/**
	 * Removes all disallowed HTML tags from a given string.
	 *
	 * By default the following are allowed:
	 *
	 * "ADDRESS", "AREA", "A", "BASE", "BASEFONT", "BIG", "BLOCKQUOTE", "BODY", "BR",
	 * "B", "CAPTION", "CENTER", "CITE", "CODE", "DD", "DFN", "DIR", "DIV", "DL", "DT",
	 * "EM", "FONT", "FORM", "H1", "H2", "H3", "H4", "H5", "H6", "HEAD", "HR", "HTML",
	 * "ISINDEX", "I", "KBD", "LINK", "LI", "MAP", "MENU", "META", "OL", "OPTION", "PARAM", "PRE",
	 * "IMG", "INPUT", "P", "SAMP", "SELECT", "SMALL", "STRIKE", "STRONG", "STYLE", "SUB", "SUP",
	 * "TABLE", "TD", "TEXTAREA", "TH", "TITLE", "TR", "TT", "UL", "U", "VAR"
	 *
	 *
	 * @param string $string The input string to be filtered.
	 * @param string[] $removeTags The tags to be removed.
	 * @param string[] $additionalAllowedTags Additional tags to be allowed.
	 *
	 * @return string The sanitized string.
	 */
	public function removeTagsFromString($string, $removeTags = array(), $additionalAllowedTags = array()) {
		// all tags available
		$allowedTags = array("ADDRESS", "AREA", "A", "BASE", "BASEFONT", "BIG", "BLOCKQUOTE",
							 "BODY", "BR", "B", "CAPTION", "CENTER", "CITE", "CODE", "DD", "DFN", "DIR", "DIV", "DL", "DT",
							 "EM", "FONT", "FORM", "H1", "H2", "H3", "H4", "H5", "H6", "HEAD", "HR", "HTML", "IMG", "INPUT",
							 "ISINDEX", "I", "KBD", "LINK", "LI", "MAP", "MENU", "META", "OL", "OPTION", "PARAM", "PRE",
							 "P", "SAMP", "SELECT", "SMALL", "STRIKE", "STRONG", "STYLE", "SUB", "SUP",
							 "TABLE", "TD", "TEXTAREA", "TH", "TITLE", "TR", "TT", "UL", "U", "VAR"
		);

		foreach ($allowedTags as &$t) $t = strtolower($t);
		foreach ($removeTags as &$t) $t = strtolower($t);
		foreach ($additionalAllowedTags as &$t) $t = strtolower($t);

		// some tags must be removed completely (including content)
		$string = preg_replace('#<script([^>]*?)>(.*?)</script>#is', '', $string);
		$string = preg_replace('#<style([^>]*?)>(.*?)</style>#is', '', $string);
		$string = preg_replace('#<link([^>]*?)>(.*?)</link>#is', '', $string);

		$string = preg_replace('#<script([^>]*?)/>#is', '', $string);
		$string = preg_replace('#<style([^>]*?)/>#is', '', $string);
		$string = preg_replace('#<link([^>]*?)/>#is', '', $string);

		// add the additional allowed tags to the list
		$allowedTags = array_merge($allowedTags, $additionalAllowedTags);

		// strip the disallowed tags from the list
		$allowedTags = array_diff($allowedTags, $removeTags);

		// add HTML brackets
		foreach ($allowedTags as &$t) $t = "<$t>";

		// let PHP sanitize the string and return it
		return strip_tags($string, implode(",", $allowedTags));
	}
}
