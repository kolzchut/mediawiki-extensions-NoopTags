# MediaWiki extension NoopTags

This extension is used to register custom tags and parser functions and make them do absolutely nothing.

What's the use? Well, we have a bunch of extensions that add parser functions or tags, for example EmbedVideo - and in certain modes (such as Kiosk mode) we want to disable the extension and not have an error on screen (because there's no handler for the function).

## How it works

* **Tags** listed in `$wgNoopTagsBlacklist` are registered with `Parser::setHook()` and render nothing.
* **Parser functions** listed in `$wgNoopTagsFunctionBlacklist` are stripped out of the raw wikitext on the `ParserBeforeInternalParse` hook, before the parser tries to expand them.

We do *not* register the parser functions with `Parser::setFunctionHook()`, because that requires a magic word, and magic words live in the localisation cache. As this extension is typically loaded only in some modes (e.g. Kiosk) while that cache is shared with - and usually built by - requests where it is not loaded, the magic word could be missing at runtime and throw an `invalid magic word` exception. Stripping the calls from wikitext avoids any dependency on magic words or the localisation cache.
