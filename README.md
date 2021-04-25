# MediaWiki extension NoopTags

This extension is used to register custom tags and parser functions and make them do absolutely nothing.

What's the use? Well, we have a bunch of extensions that add parser functions or tags, for example EmbedVideo - and in certain modes (such as Kiosk mode) we want to disable the extension and not have an error on screen (because there's no handler for the function).

Unfortunately, with MediaWiki 1.35 (or sometime earlier), the hook we used to add the magic words was removed, and now you also have to add magic word aliases in the i18n file, and therefore this extension is no longer really useful for all.
