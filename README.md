# SCG Tapatalk Fixer

Two issues:
- Tapatalk overrides our favicon and webmanifest with their own branding. No, this is our website, not your app, bugger off. We rewrite it back to ours.
- Tapatalk breaks emojis on the forum because they didn't ship half of the emoji images that they rewrite the content to. So we rewrite them back to emojis.

## Installation

The folder structure here mirrors what needs to be on the web root. This makes installation easy.

1. Compress the files here excluding this readme.
2. Upload the .zip file to the web root.
3. Extract the .zip file and delete it from the web server.
4. Go to **Admin CP → Configuration → Plugins** and activate "SCG Tapatalk Fixer"
