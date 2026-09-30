<?php
/**
 * SCG Tapatalk Fixer Plugin
 *
 * Undoes the parts of the Tapatalk plugin that make the forum worse on the web:
 *
 * - Overrides Tapatalk's PWA manifest with SCG branding, so users who install the forum as a
 *   Progressive Web App see the SCG logo instead of Tapatalk's default icons.
 * - Turns Tapatalk's emoji back into real emoji. The app posts most emoji as its own numbered
 *   BBCode, [emojiNNN], which the Tapatalk plugin renders as images from emoji.tapatalk-cdn.com.
 *   That CDN stops at 2221 while the app keeps adding codes, so newer emoji and skin tones
 *   show as broken images. It also writes some emoji as empty <span class="emoji emojiXXXX">
 *   sprites that only draw with its stylesheet, which the theme does not load.
 *
 * @author Kevin Brown (TK-33151)
 * @version 2.0
 */

// Disallow direct access to this file for security reasons
if(!defined('IN_MYBB'))
{
    die('This file cannot be accessed directly.');
}

// Hook into global_end to modify output after Tapatalk has added its manifest
$plugins->add_hook('global_end', 'tapatalk_fixer_manifest');

// Run after Tapatalk's own hooks, which use the default priority of 10
$plugins->add_hook('parse_message_end', 'tapatalk_fixer_parse_message', 100);
$plugins->add_hook('pre_output_page', 'tapatalk_fixer_output_page', 100);

/**
 * Plugin information for the MyBB plugin manager
 */
function tapatalk_fixer_info()
{
    return array(
        'name'          => 'SCG Tapatalk Fixer',
        'description'   => 'Fixes what the Tapatalk plugin does to the forum on the web: uses a PWA manifest with SCG branding instead of Tapatalk icons, and shows Tapatalk emoji as real emoji instead of images from Tapatalk\'s CDN.',
        'website'       => 'https://github.com/southern-cross-garrison/mybb-plugin-tapatalk-fixer',
        'author'        => 'SCG Web Team',
        'authorsite'    => 'https://scg.501staustralia.com/',
        'version'       => '2.0',
        'compatibility' => '18*',
        'codename'      => 'tapatalk_fixer'
    );
}

/**
 * Called when the plugin is activated
 */
function tapatalk_fixer_activate()
{
    // Nothing special needed on activation
}

/**
 * Called when the plugin is deactivated
 */
function tapatalk_fixer_deactivate()
{
    // Nothing special needed on deactivation
}

/**
 * Main function that removes Tapatalk's manifest and adds our own
 * Runs at the global_end hook, after Tapatalk has injected its manifest
 */
function tapatalk_fixer_manifest()
{
    global $headerinclude;

    // Remove Tapatalk's manifest link (handles both attribute orders)
    // Pattern 1: href before rel
    $headerinclude = preg_replace(
        '/<link[^>]*href=["\'][^"\']*tapatalk-cdn\.com[^"\']*["\'][^>]*rel=["\']manifest["\'][^>]*>/i',
        '',
        $headerinclude
    );

    // Pattern 2: rel before href
    $headerinclude = preg_replace(
        '/<link[^>]*rel=["\']manifest["\'][^>]*href=["\'][^"\']*tapatalk-cdn\.com[^"\']*["\'][^>]*>/i',
        '',
        $headerinclude
    );

    // Also catch any manifest link pointing to groups.tapatalk-cdn.com specifically
    $headerinclude = preg_replace(
        '/<link[^>]*href=["\']https:\/\/groups\.tapatalk-cdn\.com\/static\/manifest\/[^"\']*["\'][^>]*>/i',
        '',
        $headerinclude
    );

    // Add our custom manifest link
    // The manifest.json file should be placed in the forum root directory
    $headerinclude .= "\n" . '<link rel="manifest" href="/forum/manifest.json">' . "\n";
}

/**
 * Converts Tapatalk emoji in a parsed message, including any [emojiNNN] codes Tapatalk has not
 * turned into images yet, so a later hook of theirs finds nothing to convert
 */
function tapatalk_fixer_parse_message($message)
{
    $message = tapatalk_fixer_convert_emoji_html($message);

    if(stripos($message, '[emoji') === false)
    {
        return $message;
    }

    return preg_replace_callback('/\[emoji(\d{1,4})\]/i', function($match)
    {
        return tapatalk_fixer_emoji($match[1]);
    }, $message);
}

/**
 * Catches Tapatalk emoji added to the page after the message was parsed. Codes are left alone
 * here: on this page they can be in an editor, and converting them would rewrite the post.
 */
function tapatalk_fixer_output_page($contents)
{
    return tapatalk_fixer_convert_emoji_html($contents);
}

/**
 * Replaces Tapatalk's emoji images and sprite spans with the emoji they show
 */
function tapatalk_fixer_convert_emoji_html($html)
{
    if(strpos($html, 'emoji.tapatalk-cdn.com') !== false)
    {
        $html = preg_replace_callback(
            '/<img\b[^>]*?\bsrc=(["\'])(?:https?:)?\/\/emoji\.tapatalk-cdn\.com\/emoji(\d+)\.png\1[^>]*>/i',
            function($match)
            {
                return tapatalk_fixer_emoji($match[2]);
            },
            $html
        );
    }

    if(stripos($html, 'emoji emoji') !== false)
    {
        // The sprite's class names are codepoints, e.g. emoji1f60a
        $html = preg_replace_callback(
            '/<span\s+class=(["\'])emoji\s+emoji([0-9a-f]{2,6})\1\s*>\s*<\/span>/i',
            function($match)
            {
                $codepoint = hexdec($match[2]);
                $emoji = html_entity_decode('&#x'.$match[2].';', ENT_QUOTES | ENT_HTML5, 'UTF-8');

                // Ask for the emoji rather than the text glyph for older symbols like U+2600
                if($codepoint >= 0x2000 && $codepoint < 0x10000)
                {
                    $emoji .= "\u{FE0F}";
                }

                return $emoji;
            },
            $html
        );
    }

    return $html;
}

/**
 * The emoji for a Tapatalk code, or nothing for a code newer than our table, since Tapatalk's
 * CDN has no image for those either
 */
function tapatalk_fixer_emoji($code)
{
    static $emoji_map;

    if($emoji_map === null)
    {
        $emoji_map = require MYBB_ROOT.'inc/plugins/tapatalk_fixer/emoji_map.php';
    }

    $code = (int)$code;

    return isset($emoji_map[$code]) ? $emoji_map[$code] : '';
}
