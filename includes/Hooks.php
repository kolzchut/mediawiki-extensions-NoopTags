<?php
/**
 * Additional hooks for the HelenaKiosk skin
 * @ingroup Skins
 */

namespace MediaWiki\Extension\NoopTags;

use Parser;

class Hooks {
	/**
	 * This function catches tags / parser functions of extensions that are disabled in Kiosk mode,
	 * so we can output nothing instead of MediaWiki outputting an error message.
	 *
	 * Hook: ParserFirstCallInit
	 * @param Parser &$parser
	 * @return true
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		global $wgNoopTagsBlacklist, $wgNoopTagsFunctionBlacklist;

		if ( is_array( $wgNoopTagsBlacklist ) ) {
			foreach ( $wgNoopTagsBlacklist as $hook ) {
				$parser->setHook( $hook, [ __CLASS__, 'renderStub' ] );
			};
		}

		if ( is_array( $wgNoopTagsFunctionBlacklist ) ) {
			foreach ( $wgNoopTagsFunctionBlacklist as $hook ) {
				$parser->setFunctionHook( $hook, [ __CLASS__, 'renderStub' ] );
			};
		}

		return true;
	}

	/**
	 * This function is called by the above fake tags/function hooks, to render nothing.
	 *
	 * @return string
	 */
	public static function renderStub() {
		return '';
	}

	/**
	 * This hook has been deprecated since MediaWiki 1.16, but is still around today (1.28).
	 * IMPORTANT: it's gone in 1.33!
	 * Its use here is somewhat of a hack, to dynamically add magic words that are declared
	 * onParserFirstCallInit. However, there's no other way to dynamically declare magic words.
	 *
	 * Hook: ParserFirstCallInit
	 * @param array &$magicWords
	 * @param string $langCode
	 *
	 * @return bool
	 */
	public static function onLanguageGetMagic( array &$magicWords, $langCode ) {
		global $wgNoopTagsBlacklist, $wgNoopTagsFunctionBlacklist;
		$newWords = array_merge( $wgNoopTagsBlacklist, $wgNoopTagsFunctionBlacklist );

		foreach ( $newWords as $newWord ) {
			$magicWords[$newWord] = [ 0, $newWord ];
		}

		return true;
	}

}
