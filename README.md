# MediaWiki extension NoopTags

This extension is used to register custom tags and parser functions and make them do absolutely nothing.

What's the use? Well, we have a bunch of extensions that add parser functions or tags, for example EmbedVideo - and in certain modes (such as Kiosk mode) we want to disable the extension and not have an error on screen (because there's no handler for the function).
