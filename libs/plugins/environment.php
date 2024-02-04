<?php

/*********************************************************************

 Functions for Environment

*********************************************************************/

/**
 * ユーザエージェントから環境判定
 *
 * @param string $useragent
 *
 * @return array
 */
function environment_useragent($useragent)
{
    $browser = null;
    $os      = null;

    // System
    if (preg_match('/ELB-HealthChecker/i', $useragent)) {
        $browser = 'Load Balancer (Tool)';
        $os      = '';
    // Robot
    } elseif (preg_match('/Googlebot/i', $useragent)) {
        $browser = 'Google (Robot)';
        $os      = '';
    } elseif (preg_match('/Yahoo! Slurp/i', $useragent) || preg_match('/^Y!J/i', $useragent)) {
        $browser = 'Yahoo! (Robot)';
        $os      = '';
    } elseif (preg_match('/msnbot/i', $useragent)) {
        $browser = 'MSN (Robot)';
        $os      = '';
    } elseif (preg_match('/mogimogi/i', $useragent)) {
        $browser = 'goo (Robot)';
        $os      = '';
    } elseif (preg_match('/Infoseek SideWinder/i', $useragent)) {
        $browser = 'Infoseek (Robot)';
        $os      = '';
    } elseif (preg_match('/Ask Jeeves/i', $useragent)) {
        $browser = 'Ask (Robot)';
        $os      = '';
    } elseif (preg_match('/BecomeBot/i', $useragent)) {
        $browser = 'Become.com (Robot)';
        $os      = '';
    } elseif (preg_match('/ia_archiver/i', $useragent) || preg_match('/web\.archive\.org/i', $useragent)) {
        $browser = 'Internet Archive (Robot)';
        $os      = '';
    } elseif (preg_match('/bingbot/i', $useragent)) {
        $browser = 'Bing (Robot)';
        $os      = '';
    } elseif (preg_match('/Baidu/i', $useragent)) {
        $browser = 'Baidu (Robot)';
        $os      = '';
    // Preview
    } elseif (preg_match('/line-poker/i', $useragent)) {
        $browser = 'LINE (Preview)';
        $os      = '';
    } elseif (preg_match('/facebookexternalhit/i', $useragent)) {
        $browser = 'Facebook (Preview)';
        $os      = '';
    } elseif (preg_match('/Slackbot/i', $useragent)) {
        $browser = 'Slack (Preview)';
        $os      = '';
    // Tool
    } elseif (preg_match('/WWWC/i', $useragent)) {
        $browser = 'WWWC (Tool)';
        $os      = '';
    } elseif (preg_match('/WWWD/i', $useragent)) {
        $browser = 'WWWD (Tool)';
        $os      = '';
    } elseif (preg_match('/INCM/i', $useragent)) {
        $browser = 'INCM (Tool)';
        $os      = '';
    } elseif (preg_match('/mamimi/i', $useragent)) {
        $browser = 'mamimi (Tool)';
        $os      = '';
    } elseif (preg_match('/loadimpact/i', $useragent)) {
        $browser = 'Load Impact (Tool)';
        $os      = '';
    } elseif (preg_match('/Wget/i', $useragent)) {
        $browser = 'wget (Tool)';
        $os      = '';
    } elseif (preg_match('/curl/i', $useragent)) {
        $browser = 'curl (Tool)';
        $os      = '';
    // Game
    } elseif (preg_match('/WiiU/i', $useragent)) {
        $browser = 'WiiU (Game)';
        $os      = '';
    } elseif (preg_match('/Wii/i', $useragent)) {
        $browser = 'Wii (Game)';
        $os      = '';
    } elseif (preg_match('/New Nintendo 3DS /i', $useragent)) {
        $browser = 'New 3DS (Game)';
        $os      = '';
    } elseif (preg_match('/Nintendo 3DS/i', $useragent)) {
        $browser = '3DS (Game)';
        $os      = '';
    } elseif (preg_match('/Nintendo DSi/i', $useragent)) {
        $browser = 'DSi (Game)';
        $os      = '';
    } elseif (preg_match('/PlayStation Vita/i', $useragent)) {
        $browser = 'PSVita (Game)';
        $os      = '';
    } elseif (preg_match('/(PSP BROWSER|PlayStation Portable)/i', $useragent)) {
        $browser = 'PSP (Game)';
        $os      = '';
    } elseif (preg_match('/PlayStation 5/i', $useragent)) {
        $browser = 'PlayStation 5 (Game)';
        $os      = '';
    } elseif (preg_match('/PlayStation 4/i', $useragent)) {
        $browser = 'PlayStation 4 (Game)';
        $os      = '';
    } elseif (preg_match('/PlayStation 3/i', $useragent)) {
        $browser = 'PlayStation 3 (Game)';
        $os      = '';
    } elseif (preg_match('/PlayStation BB/i', $useragent)) {
        $browser = 'PlayStation 2 (Game)';
        $os      = '';
    } elseif (preg_match('/PlayStation/i', $useragent)) {
        $browser = 'PlayStation (Game)';
        $os      = '';
    } elseif (preg_match('/DreamPassport/i', $useragent)) {
        $browser = 'DreamCast (Game)';
        $os      = '';
    // PDA
    } elseif (preg_match('/sharp pda browser/i', $useragent)) {
        $browser = 'SHARP PDA Browser (PDA)';
        $os      = '';
    } elseif (preg_match('/A(VE-|ve)Front/i', $useragent)) {
        $browser = 'AVE-Front (PDA)';
        $os      = '';
    } elseif (preg_match('/NetFront/i', $useragent)) {
        $browser = 'NetFront (PDA)';
        $os      = '';
    // Phone
    } elseif (preg_match('/Windows Phone 10/i', $useragent)) {
        $browser = 'Windows Phone 10 (Phone)';
        $os      = '';
    } elseif (preg_match('/Windows Phone 8\.1/i', $useragent)) {
        $browser = 'Windows Phone 8.1 (Phone)';
        $os      = '';
    } elseif (preg_match('/Windows Phone 8/i', $useragent)) {
        $browser = 'Windows Phone 8 (Phone)';
        $os      = '';
    } elseif (preg_match('/Windows Phone OS 7/i', $useragent)) {
        $browser = 'Windows Phone 7 (Phone)';
        $os      = '';
    } elseif (preg_match('/Windows Phone 6/i', $useragent)) {
        $browser = 'Windows Phone 6 (Phone)';
        $os      = '';
    } elseif (preg_match('/Windows Phone/i', $useragent)) {
        $browser = 'Windows Phone (Phone)';
        $os      = '';
    } elseif (preg_match('/BlackBerry/i', $useragent) || preg_match('/BB10/i', $useragent)) {
        $browser = 'BlackBerry (Phone)';
        $os      = '';
    } elseif (preg_match('/DoCoMo/i', $useragent)) {
        $browser = 'DoCoMo (Phone)';
        $os      = '';
    } elseif (preg_match('/L\-mode/i', $useragent)) {
        $browser = 'L-mode (Phone)';
        $os      = '';
    } elseif (preg_match('/ASTEL/i', $useragent)) {
        $browser = 'ASTEL (Phone)';
        $os      = '';
    } elseif (preg_match('/J-PHONE/i', $useragent)) {
        $browser = 'J-PHONE (Phone)';
        $os      = '';
    } elseif (preg_match('/Vodafone/i', $useragent)) {
        $browser = 'Vodafone (Phone)';
        $os      = '';
    } elseif (preg_match('/KDDI-/i', $useragent)) {
        $browser = 'au (Phone)';
        $os      = '';
    } elseif (preg_match('/UP\.Browser/i', $useragent)) {
        $browser = 'EZweb (Phone)';
        $os      = '';
    } elseif (preg_match('/PDXGW/i', $useragent)) {
        $browser = 'H" (Phone)';
        $os      = '';
    } elseif (preg_match('/DDIPOCKET/i', $useragent)) {
        $browser = 'AirH"PHONE (Phone)';
        $os      = '';
    } elseif (preg_match('/WILLCOM/i', $useragent)) {
        $browser = 'WILLCOM (Phone)';
        $os      = '';
    } elseif (preg_match('/jig browser/i', $useragent)) {
        $browser = 'jig browser (Phone)';
        $os      = '';
    // PC
    } elseif (preg_match('/Cuam/i', $useragent)) {
        $browser = 'Cuam';
    } elseif (preg_match('/Ninja/i', $useragent)) {
        $browser = 'Ninja';
    } elseif (preg_match('/WWWC/i', $useragent)) {
        $browser = 'WWWC';
    } elseif (preg_match('/NetCaptor/i', $useragent)) {
        $browser = 'NetCaptor';
    } elseif (preg_match('/Sleipnir/i', $useragent)) {
        $browser = 'Sleipnir';
    } elseif (preg_match('/Lunascape/i', $useragent)) {
        $browser = 'Lunascape';
    } elseif (preg_match('/Galeon\//i', $useragent)) {
        $browser = 'Galeon';
    } elseif (preg_match('/Epiphany\//i', $useragent)) {
        $browser = 'Epiphany';
    } elseif (preg_match('/Nautilus\//i', $useragent)) {
        $browser = 'Nautilus';
    } elseif (preg_match('/(Camino|Chimera)\//i', $useragent)) {
        $browser = 'Camino';
    } elseif (preg_match('/K-Meleon/i', $useragent)) {
        $browser = 'K-Meleon';
    } elseif (preg_match('/Sylera/i', $useragent)) {
        $browser = 'Sylera';
    } elseif (preg_match('/Konqueror/i', $useragent)) {
        $browser = 'Konqueror';
    } elseif (preg_match('/iCab/i', $useragent)) {
        $browser = 'iCab';
    } elseif (preg_match('/OmniWeb/i', $useragent)) {
        $browser = 'OmniWeb';
    } elseif (preg_match('/AOL/i', $useragent)) {
        $browser = 'AOL';
    } elseif (preg_match('/Lynx/i', $useragent)) {
        $browser = 'Lynx';
    } elseif (preg_match('/Opera/i', $useragent) || preg_match('/OPR/i', $useragent)) {
        $browser = 'Opera';
    } elseif (preg_match('/Edge\/(\d+)/i', $useragent, $matches)) {
        $browser = 'Edge ' . $matches[1];
    } elseif (preg_match('/Edg\/(\d+)/i', $useragent, $matches)) {
        $browser = 'Edge ' . $matches[1];
    } elseif (preg_match('/Edg/i', $useragent)) {
        $browser = 'Edge';
    } elseif (preg_match('/CriOS\/(\d+)/i', $useragent, $matches)) {
        $browser = 'Chrome ' . $matches[1];
    } elseif (preg_match('/Chrome\/(\d+)/i', $useragent, $matches)) {
        $browser = 'Chrome ' . $matches[1];
    } elseif (preg_match('/Chrome/i', $useragent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/Safari/i', $useragent) && preg_match('/Android/i', $useragent, $matches)) {
        $browser = 'Android標準ブラウザ';
    } elseif (preg_match('/Safari/i', $useragent) && preg_match('/Version\/(\d+)/i', $useragent, $matches)) {
        $browser = 'Safari ' . $matches[1];
    } elseif (preg_match('/Safari/i', $useragent)) {
        $browser = 'Safari';
    } elseif (preg_match('/Firefox\/(\d+)/i', $useragent, $matches)) {
        $browser = 'Firefox ' . $matches[1];
    } elseif (preg_match('/(Firefox|Firebird|Phoenix)/i', $useragent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/Gecko\/(\d+)/i', $useragent, $matches)) {
        $browser = 'Gecko ' . $matches[1];
    } elseif (preg_match('/Gecko/i', $useragent)) {
        $browser = 'Gecko';
    } elseif (preg_match('/Trident\/7/i', $useragent)) {
        $browser = 'Internet Explorer 11';
    } elseif (preg_match('/MSIE 10/i', $useragent)) {
        $browser = 'Internet Explorer 10';
    } elseif (preg_match('/MSIE 9/i', $useragent)) {
        $browser = 'Internet Explorer 9';
    } elseif (preg_match('/MSIE 8/i', $useragent)) {
        $browser = 'Internet Explorer 8';
    } elseif (preg_match('/MSIE 7/i', $useragent)) {
        $browser = 'Internet Explorer 7';
    } elseif (preg_match('/MSIE 6/i', $useragent)) {
        $browser = 'Internet Explorer 6';
    } elseif (preg_match('/MSIE 5\.5/i', $useragent)) {
        $browser = 'Internet Explorer 5.5';
    } elseif (preg_match('/MSIE 5/i', $useragent)) {
        $browser = 'Internet Explorer 5';
    } elseif (preg_match('/MSIE 4/i', $useragent)) {
        $browser = 'Internet Explorer 4';
    } elseif (preg_match('/MSIE 3/i', $useragent)) {
        $browser = 'Internet Explorer 3';
    } elseif (preg_match('/MSIE/i', $useragent)) {
        $browser = 'Internet Explorer';
    } elseif (preg_match('/Netscape\/7/i', $useragent)) {
        $browser = 'Netscape 7';
    } elseif (preg_match('/Netscape ?6/i', $useragent)) {
        $browser = 'Netscape 6';
    } elseif (preg_match('/Mozilla\/4/i', $useragent)) {
        $browser = 'Netscape 4';
    } elseif (preg_match('/Mozilla\/3/i', $useragent)) {
        $browser = 'Netscape 3';
    } elseif (preg_match('/Mozilla\/2/i', $useragent)) {
        $browser = 'Netscape 2';
    } elseif (preg_match('/Mozilla/i', $useragent) || preg_match('/Netscape/i', $useragent) || preg_match('/Gecko/i', $useragent)) {
        $browser = 'Mozilla';
    // Other
    } else {
        $browser = null;
    }

    // OS判別
    if ($os === null) {
        if (preg_match('/Win[dows ]*NT ?10/i', $useragent) || preg_match('/Win[dows ]*NT ?11/i', $useragent)) {
            $os = 'Windows 10 (or later)';
        } elseif (preg_match('/Win[dows ]*NT ?6\.3/i', $useragent)) {
            $os = 'Windows 8.1';
        } elseif (preg_match('/Win[dows ]*NT ?6\.2/i', $useragent)) {
            $os = 'Windows 8';
        } elseif (preg_match('/Win[dows ]*NT ?6\.1/i', $useragent)) {
            $os = 'Windows 7';
        } elseif (preg_match('/Win[dows ]*NT ?6\.0/i', $useragent)) {
            $os = 'Windows Vista';
        } elseif (preg_match('/Win[dows ]*NT ?5\.2/i', $useragent)) {
            $os = 'Windows 2003';
        } elseif (preg_match('/Win[dows ]*NT ?5\.1/i', $useragent) || preg_match('/Win[dows ]*XP/i', $useragent)) {
            $os = 'Windows XP';
        } elseif (preg_match('/Win[dows ]*NT ?5/i', $useragent) || preg_match('/Win[dows ]*2000/i', $useragent)) {
            $os = 'Windows 2000';
        } elseif (preg_match('/Win[dows ]*9x/i', $useragent)) {
            $os = 'Windows Me';
        } elseif (preg_match('/Win[dows ]*98/i', $useragent)) {
            $os = 'Windows 98';
        } elseif (preg_match('/Win[dows ]*95/i', $useragent)) {
            $os = 'Windows 95';
        } elseif (preg_match('/Win[dows ]*NT/i', $useragent)) {
            $os = 'Windows NT';
        } elseif (preg_match('/Win[dows ]*CE/i', $useragent)) {
            $os = 'Windows CE';
        } elseif (preg_match('/Win32/i', $useragent) || preg_match('/Windows/i', $useragent)) {
            $os = 'Windows';
        } elseif (preg_match('/iPod/i', $useragent) && preg_match('/OS (\d+)/i', $useragent, $matches)) {
            $os = 'iOS ' . $matches[1] . '(iPod)';
        } elseif (preg_match('/iPod/i', $useragent)) {
            $os = 'iOS (iPod)';
        } elseif (preg_match('/iPhone/i', $useragent) && preg_match('/OS (\d+)/i', $useragent, $matches)) {
            $os = 'iOS ' . $matches[1] . '(iPhone)';
        } elseif (preg_match('/iPhone/i', $useragent)) {
            $os = 'iOS (iPhone)';
        } elseif (preg_match('/iPad/i', $useragent) && preg_match('/OS (\d+)/i', $useragent, $matches)) {
            $os = 'iOS ' . $matches[1] . '(iPad)';
        } elseif (preg_match('/iPad/i', $useragent)) {
            $os = 'iOS (iPad)';
        } elseif (preg_match('/Mac OS X 13/i', $useragent)) {
            $os = 'macOS 13 Ventura';
        } elseif (preg_match('/Mac OS X 12/i', $useragent)) {
            $os = 'macOS 12 Monterey';
        } elseif (preg_match('/Mac OS X 11/i', $useragent)) {
            $os = 'macOS 11 Big Sur';
        } elseif (preg_match('/Mac OS X 10(\.|\_)15/i', $useragent)) {
            $os = 'macOS 10.15 Catalina';
        } elseif (preg_match('/Mac OS X 10(\.|\_)14/i', $useragent)) {
            $os = 'macOS 10.14 Mojave';
        } elseif (preg_match('/Mac OS X 10(\.|\_)13/i', $useragent)) {
            $os = 'macOS 10.13 High Sierra';
        } elseif (preg_match('/Mac OS X 10(\.|\_)12/i', $useragent)) {
            $os = 'macOS 10.12 Sierra';
        } elseif (preg_match('/Mac OS X 10(\.|\_)11/i', $useragent)) {
            $os = 'Mac OS X 10.11 El Capitan';
        } elseif (preg_match('/Mac OS X 10(\.|\_)10/i', $useragent)) {
            $os = 'Mac OS X 10.10 Yosemite';
        } elseif (preg_match('/Mac OS X 10(\.|\_)9/i', $useragent)) {
            $os = 'Mac OS X 10.9 Mavericks';
        } elseif (preg_match('/Mac OS X 10(\.|\_)8/i', $useragent)) {
            $os = 'Mac OS X 10.8 Mountain Lion';
        } elseif (preg_match('/Mac OS X 10(\.|\_)7/i', $useragent)) {
            $os = 'Mac OS X 10.7 Lion';
        } elseif (preg_match('/Mac OS X 10(\.|\_)6/i', $useragent)) {
            $os = 'Mac OS X 10.6 Snow Leopard';
        } elseif (preg_match('/Mac OS X 10(\.|\_)5/i', $useragent)) {
            $os = 'Mac OS X 10.5 Leopard';
        } elseif (preg_match('/Mac OS X 10/i', $useragent)) {
            $os = 'Mac OS X';
        } elseif (preg_match('/Mac/i', $useragent)) {
            $os = 'Macintosh';
        } elseif (preg_match('/Android ([\d\.]+)/i', $useragent, $matches) && preg_match('/Mobile/i', $useragent)) {
            $os = 'Android ' . $matches[1];
        } elseif (preg_match('/Android/i', $useragent) && preg_match('/Mobile/i', $useragent)) {
            $os = 'Android';
        } elseif (preg_match('/Android ([\d\.]+)/i', $useragent, $matches)) {
            $os = 'Android ' . $matches[1] . ' (Tablet)';
        } elseif (preg_match('/Android/i', $useragent)) {
            $os = 'Android (Tablet)';
        } elseif (preg_match('/CrOS/i', $useragent)) {
            $os = 'Chrome OS';
        } elseif (preg_match('/Ubuntu/i', $useragent)) {
            $os = 'Ubuntu';
        } elseif (preg_match('/Mint/i', $useragent)) {
            $os = 'Mint Linux';
        } elseif (preg_match('/Fedora/i', $useragent)) {
            $os = 'Fedora';
        } elseif (preg_match('/Gentoo/i', $useragent)) {
            $os = 'Gentoo';
        } elseif (preg_match('/FreeBSD/i', $useragent)) {
            $os = 'FreeBSD';
        } elseif (preg_match('/OpenBSD/i', $useragent)) {
            $os = 'OpenBSD';
        } elseif (preg_match('/NetBSD/i', $useragent)) {
            $os = 'NetBSD';
        } elseif (preg_match('/Linux/i', $useragent) || preg_match('/SunOS/i', $useragent) || preg_match('/X11/i', $useragent) || preg_match('/HP-UX/i', $useragent) || preg_match('/OSF1/i', $useragent) || preg_match('/IRIX/i', $useragent)) {
            $os = 'Linux';
        } elseif (preg_match('/sharp pda browser/i', $useragent)) {
            $os = 'ZAURUS';
        } else {
            $os = null;
        }
    }

    if ($os && $browser) {
        $environment = $os . ' + ' . $browser;
    } elseif ($os && !$browser) {
        $environment = $os;
    } elseif (!$os && $browser) {
        $environment = $browser;
    } else {
        $environment = 'Unknown';
    }

    return array($environment, $browser, $os);
}
