# SCG PWA Manifest Plugin

A MyBB plugin that overrides Tapatalk's PWA manifest with Southern Cross Garrison branding.

## Why This Exists

When Tapatalk is installed on a MyBB forum, it injects its own `manifest.json` for Progressive Web App installations. This means users who "Add to Home Screen" see Tapatalk's logo instead of the forum's branding.

This plugin removes Tapatalk's manifest and replaces it with a custom one, so PWA installations display the correct SCG logo.

## Installation

The folder structure here mirrors what needs to be on the web root. This makes installation easy.

1. Compress the files here excluding this readme.
2. Upload the .zip file to the web root.
3. Extract the .zip file and delete it from the web server.
4. Go to **Admin CP → Configuration → Plugins** and activate "SCG PWA Manifest"
