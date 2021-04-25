<?php
/**
 * Additional hooks for the HelenaKiosk skin
 * @ingroup Skins
 */

namespace MediaWiki\Extension\NoopTags;

use Parser;

class Hooks implements
	\MediaWiki\Hook\GetMagicVariableIDsHook,
	\MediaWiki\Hook\ParserFirstCallInitHook {

	/**
	 * This hook is called when the parser initialises for the first time.
	 *
	 * @since 1.35
	 *
	 * @param Parser $parser Parser object being initialised
	 * @return bool|void True or no return value to continue or false to abort
	 */


	/**
	 * This function catches tags / parser functions of extensions that are disabled in Kiosk mode,
	 * so we can output nothing instead of MediaWiki outputting an error message.
	 *
	 * This hook is called when the parser initialises for the first time.
	 *
	 * @param Parser $parser Parser object being initialised
	 * @return void True or no return value to continue or false to abort
	 */
	public function onParserFirstCallInit( $parser ) {
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
	 * We use this hook
	 *
	 * Use this hook to modify the list of magic variables.
	 * Magic variables are localized with the magic word system,
	 * and this hook is called by MagicWordFactory.
	 *
	 * @since 1.35
	 *
	 * @param string[] &$variableIDs array of magic word identifiers
	 * @return void True or no return value to continue or false to abort
	 */
	public function onGetMagicVariableIDs( &$variableIDs ) {
		global $wgNoopTagsBlacklist, $wgNoopTagsFunctionBlacklist;
		$newWords = array_merge( $wgNoopTagsBlacklist, $wgNoopTagsFunctionBlacklist );

		foreach ( $newWords as $newWord ) {
			$variableIDs[$newWord] = $newWord;
		}
	}
}
