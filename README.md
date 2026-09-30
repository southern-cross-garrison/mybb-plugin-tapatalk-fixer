# SCG Tapatalk Fixer

A MyBB plugin that undoes the parts of the Tapatalk plugin that make the forum worse on the web.

## Why This Exists

### PWA manifest

When Tapatalk is installed on a MyBB forum, it injects its own `manifest.json` for Progressive Web App installations. This means users who "Add to Home Screen" see Tapatalk's logo instead of the forum's branding.

This plugin removes Tapatalk's manifest and replaces it with a custom one, so PWA installations display the correct SCG logo.

### Emoji

The Tapatalk app posts most emoji as its own numbered BBCode, like `[emoji3060]`, and the Tapatalk plugin shows each one as an image from `emoji.tapatalk-cdn.com`. That CDN only has images up to `emoji2221`, but the app has kept adding codes, so newer emoji (and skin tones, which the app posts as a separate code) show up as broken images. The numbers are Tapatalk's own, not Unicode codepoints.

This plugin replaces those images, and any codes left in a post, with the real emoji before the page is sent, using the app's own table in `inc/plugins/tapatalk_fixer/emoji_map.php`. A code newer than that table is removed. It also replaces the empty `<span class="emoji emoji1f60a">` sprites Tapatalk writes for some emoji, whose class names are codepoints. Nothing in the database changes, so older posts are fixed too.

The table was taken from `com.tapatalk.base.util.EmojiCodeToUnicodeMap` in the Tapatalk Android app, version 8.9.32.F. If the app adds codes past 3604, take the same class from a newer app and regenerate the file.

## Installation

The folder structure here mirrors what needs to be on the web root. This makes installation easy.

1. Compress the files here excluding this readme.
2. Upload the .zip file to the web root.
3. Extract the .zip file and delete it from the web server.
4. Go to **Admin CP → Configuration → Plugins** and activate "SCG Tapatalk Fixer"

## Upgrading from SCG PWA Manifest

This plugin used to be called SCG PWA Manifest, in `inc/plugins/scg_manifest.php`. MyBB identifies a plugin by its file name, so the renamed plugin is a new one to MyBB:

1. Go to **Admin CP → Configuration → Plugins** and deactivate "SCG PWA Manifest".
2. Delete `inc/plugins/scg_manifest.php` from the web server.
3. Install and activate "SCG Tapatalk Fixer" as above.
