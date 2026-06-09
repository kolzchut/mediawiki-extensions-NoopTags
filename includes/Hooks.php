<?php
/**
 * Hooks for the NoopTags extension.
 *
 * Masks tags and parser functions that belong to extensions which are disabled in
 * certain modes (e.g. Kiosk), so their leftover wikitext renders nothing instead of
 * a parser error.
 *
 * @ingroup NoopTags
 */

namespace MediaWiki\Extension\NoopTags;

use MediaWiki\Hook\ParserBeforeInternalParseHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use Parser;

class Hooks implements
	ParserFirstCallInitHook,
	ParserBeforeInternalParseHook {

	/**
	 * Register stub handlers for blacklisted *tags*.
	 *
	 * Tags only need Parser::setHook(), which requires no magic word, so they are safe
	 * to register here. Parser *functions* are handled in onParserBeforeInternalParse()
	 * instead - see the explanation there.
	 *
	 * @param Parser $parser Parser object being initialised
	 * @return void
	 */
	public function onParserFirstCallInit( $parser ) {
		global $wgNoopTagsBlacklist;

		if ( is_array( $wgNoopTagsBlacklist ) ) {
			foreach ( $wgNoopTagsBlacklist as $tag ) {
				$parser->setHook( $tag, [ __CLASS__, 'renderStub' ] );
			}
		}
	}

	/**
	 * Strip blacklisted parser-function calls from the raw wikitext before the parser
	 * expands them.
	 *
	 * We deliberately avoid Parser::setFunctionHook() for these. setFunctionHook()
	 * requires a registered magic word, and magic words live in the localisation cache.
	 * Because this extension is only loaded in some modes (e.g. Kiosk) while that cache
	 * is shared with - and usually built by - requests where it is NOT loaded, the
	 * magic word can be absent at runtime, throwing "invalid magic word" and aborting
	 * the whole parse. Stripping the calls here needs no magic word and works no matter
	 * how (or where) the localisation cache was built.
	 *
	 * This hook fires before replaceVariables(), i.e. before {{#func:...}} is expanded.
	 *
	 * @param Parser $parser
	 * @param string &$text
	 * @param \StripState $stripState
	 * @return void
	 */
	public function onParserBeforeInternalParse( $parser, &$text, $stripState ) {
		global $wgNoopTagsFunctionBlacklist;

		if ( is_array( $wgNoopTagsFunctionBlacklist ) && $wgNoopTagsFunctionBlacklist ) {
			$text = self::stripFunctionCalls( $text, $wgNoopTagsFunctionBlacklist );
		}
	}

	/**
	 * Remove every {{#func:...}} call for the given function names, honouring nested
	 * braces so arguments that contain other templates/functions are consumed too.
	 *
	 * Public so the pure string logic can be unit-tested without globals or a Parser.
	 *
	 * @param string $text
	 * @param string[] $functions Parser-function names, without the leading '#'
	 * @return string
	 */
	public static function stripFunctionCalls( $text, array $functions ) {
		foreach ( $functions as $function ) {
			// Match "{{" + optional whitespace + "#func" + optional whitespace + ":".
			// This will not match a longer name (e.g. '#ev' won't match '{{#evt:').
			$startPattern = '/\{\{\s*#' . preg_quote( $function, '/' ) . '\s*:/i';

			while ( preg_match( $startPattern, $text, $m, PREG_OFFSET_CAPTURE ) ) {
				$start = $m[0][1];
				$end = self::findClosingBraces( $text, $start );
				if ( $end === null ) {
					// Unbalanced braces - leave the remainder untouched rather than
					// risk mangling the page.
					break;
				}
				$text = substr( $text, 0, $start ) . substr( $text, $end );
			}
		}

		return $text;
	}

	/**
	 * Given the offset of an opening "{{", return the offset just past its matching
	 * "}}", or null if the braces are unbalanced.
	 *
	 * @param string $text
	 * @param int $start Offset of the opening "{{"
	 * @return int|null
	 */
	private static function findClosingBraces( $text, $start ) {
		$depth = 0;
		$len = strlen( $text );

		$i = $start;
		while ( $i < $len - 1 ) {
			$pair = substr( $text, $i, 2 );
			if ( $pair === '{{' ) {
				$depth++;
				$i += 2;
			} elseif ( $pair === '}}' ) {
				$depth--;
				$i += 2;
				if ( $depth === 0 ) {
					return $i;
				}
			} else {
				$i++;
			}
		}

		return null;
	}

	/**
	 * Stub callback for masked tags: render nothing.
	 *
	 * @return string
	 */
	public static function renderStub() {
		return '';
	}
}
