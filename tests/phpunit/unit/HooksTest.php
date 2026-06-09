<?php

namespace MediaWiki\Extension\NoopTags\Tests\Unit;

use MediaWiki\Extension\NoopTags\Hooks;
use MediaWikiUnitTestCase;

/**
 * @covers \MediaWiki\Extension\NoopTags\Hooks
 */
class HooksTest extends MediaWikiUnitTestCase {

	/**
	 * @dataProvider provideWikitext
	 * @covers \MediaWiki\Extension\NoopTags\Hooks::stripFunctionCalls
	 */
	public function testStripFunctionCalls( string $input, string $expected ): void {
		$this->assertSame(
			$expected,
			Hooks::stripFunctionCalls( $input, [ 'ev', 'evt' ] )
		);
	}

	public static function provideWikitext(): array {
		return [
			'single call' =>
				[ 'A {{#ev:youtube|abc123}} B', 'A  B' ],
			'evt variant' =>
				[ 'A {{#evt:service=youtube|id=abc}} B', 'A  B' ],
			'nested function in argument' =>
				[ 'pre {{#ev:youtube|{{#expr:1+2}}|opt}} post', 'pre  post' ],
			'adjacent calls' =>
				[ '{{#ev:a}}{{#ev:b}}', '' ],
			'nested same function' =>
				[ 'x {{#ev:a|{{#ev:b}}|c}} y', 'x  y' ],
			'whitespace around name and colon' =>
				[ 'ws {{ #ev : youtube|x }} ws', 'ws  ws' ],
			'longer name is not matched' =>
				[ 'keep {{#evx:foo}} this', 'keep {{#evx:foo}} this' ],
			'no calls present' =>
				[ 'plain text, no calls', 'plain text, no calls' ],
			'unbalanced braces are left untouched' =>
				[ 'broken {{#ev:youtube|nope', 'broken {{#ev:youtube|nope' ],
			'call spanning newlines' =>
				[ "before {{#ev:youtube\n|id=abc\n}} after", 'before  after' ],
		];
	}

	/**
	 * @covers \MediaWiki\Extension\NoopTags\Hooks::stripFunctionCalls
	 */
	public function testEmptyFunctionListLeavesTextUnchanged(): void {
		$text = 'A {{#ev:youtube|abc}} B';
		$this->assertSame( $text, Hooks::stripFunctionCalls( $text, [] ) );
	}

	/**
	 * Only blacklisted functions are stripped; other parser functions survive.
	 *
	 * @covers \MediaWiki\Extension\NoopTags\Hooks::stripFunctionCalls
	 */
	public function testNonBlacklistedFunctionsSurvive(): void {
		$text = '{{#ev:youtube|a}} keep {{#if:x|yes}}';
		$this->assertSame( ' keep {{#if:x|yes}}', Hooks::stripFunctionCalls( $text, [ 'ev' ] ) );
	}
}
