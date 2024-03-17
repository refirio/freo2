<?php
header('Content-Type: text/xml; charset=' . MAIN_CHARSET);
e('<?xml version="1.0" encoding="utf-8"?>' . "\n");
?>
<rss version="2.0" xml:lang="ja">
    <channel>
        <title><?php h($GLOBALS['setting']['title']) ?></title>
        <link><?php h($GLOBALS['config']['http_url']) ?>/</link>
        <description><?php h($GLOBALS['setting']['description']) ?></description>
        <language>ja</language>
        <lastBuildDate><?php h(localdate('D, d M Y H:i:s +0900', $_view['entries'][0]['datetime'])) ?></lastBuildDate>
        <docs><?php h($GLOBALS['config']['http_url']) ?>/entry/feed</docs>
        <?php foreach ($_view['entries'] as $entry) : ?>
        <item>
            <title><?php h($entry['title']) ?></title>
            <link><?php h($GLOBALS['config']['http_url']) ?>/entry/detail/<?php h($entry['code']) ?></link>
            <guid isPermaLink="true"><?php h($GLOBALS['config']['http_url']) ?>/entry/detail/<?php h($entry['code']) ?></guid>
            <description><?php h(truncate(strip_tags($entry['text']), 200)) ?></description>
            <pubDate><?php h(localdate('D, d M Y H:i:s +0900', $entry['datetime'])) ?></pubDate>
        </item>
        <?php endforeach ?>
    </channel>
</rss>
