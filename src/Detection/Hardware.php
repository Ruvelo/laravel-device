<?php

declare(strict_types=1);

namespace Ruvelo\Device\Detection;

use Ruvelo\Device\DeviceType;

/**
 * TVs, consoles and wearables, plus the maker and model of Android devices.
 */
final class Hardware
{
    /**
     * Rules for the devices that aren't phones, tablets or computers:
     * [type, pattern, brand, model]. Tested before Android, because Android TVs
     * look like Android tablets.
     *
     * @var list<array{0: DeviceType, 1: string, 2: string|null, 3: string|null}>
     */
    private const SPECIAL = [
        [DeviceType::Console, 'PlayStation ?5', 'Sony', 'PlayStation 5'],
        [DeviceType::Console, 'PlayStation ?4', 'Sony', 'PlayStation 4'],
        [DeviceType::Console, 'PlayStation (?:Vita|Portable)', 'Sony', 'PlayStation Vita'],
        [DeviceType::Console, 'Xbox Series X', 'Microsoft', 'Xbox Series X'],
        [DeviceType::Console, 'Xbox Series S', 'Microsoft', 'Xbox Series S'],
        [DeviceType::Console, 'Xbox One', 'Microsoft', 'Xbox One'],
        [DeviceType::Console, 'Xbox', 'Microsoft', 'Xbox'],
        [DeviceType::Console, 'Nintendo Switch', 'Nintendo', 'Switch'],
        [DeviceType::Console, 'Nintendo (?:WiiU|Wii U)', 'Nintendo', 'Wii U'],
        [DeviceType::Console, 'Nintendo 3DS', 'Nintendo', '3DS'],
        [DeviceType::Console, 'Valve Steam Gamepad|Steam Deck', 'Valve', 'Steam Deck'],

        [DeviceType::Wearable, 'Pixel Watch', 'Google', 'Pixel Watch'],
        [DeviceType::Wearable, '\bSM-R\d{3}', 'Samsung', 'Galaxy Watch'],
        [DeviceType::Wearable, 'Galaxy Watch', 'Samsung', 'Galaxy Watch'],
        [DeviceType::Wearable, 'watchOS|Apple ?Watch', 'Apple', 'Apple Watch'],
        [DeviceType::Wearable, 'Wear ?OS|; wear\)', null, null],

        [DeviceType::Tv, '(?-i:\bAFT[A-Z0-9]+\b)', 'Amazon', 'Fire TV'],
        [DeviceType::Tv, 'AppleTV|Apple TV|tvOS', 'Apple', 'Apple TV'],
        [DeviceType::Tv, '\bCrKey\b|Chromecast', 'Google', 'Chromecast'],
        [DeviceType::Tv, 'Roku', 'Roku', null],
        [DeviceType::Tv, 'SHIELD Android TV', 'NVIDIA', 'SHIELD TV'],
        [DeviceType::Tv, 'BRAVIA', 'Sony', 'Bravia'],
        [DeviceType::Tv, 'Web0S|NetCast|webOS.TV|LG Browser|\bLGE?;', 'LG', null],
        [DeviceType::Tv, 'SMART-TV.*?Tizen|Tizen.*?(?:SMART-TV|TV)|SmartHub|Samsung.*?SmartTV', 'Samsung', null],
        [DeviceType::Tv, '\bMiTV|MIBOX', 'Xiaomi', null],
        [DeviceType::Tv, 'VIDAA', 'Hisense', null],
        [DeviceType::Tv, 'Viera', 'Panasonic', null],
        [DeviceType::Tv, 'Philips|NETTV', 'Philips', null],
        [DeviceType::Tv, '\bAQUOS', 'Sharp', null],
        [DeviceType::Tv, 'SmartTV|Smart-TV|SMART-TV|HbbTV|GoogleTV|Google TV|Android ?TV|\bDTV\b|Opera TV|; ?TV;|TV Safari|Large Screen|\bTV Build/', null, null],
    ];

    /**
     * Android model prefixes and who makes them. Codenames we can't place for
     * certain (OPPO and OnePlus both use "CPH", for instance) are left out.
     *
     * @var array<string, string>
     */
    private const ANDROID_BRANDS = [
        '^(?:SAMSUNG|SM-|GT-|SCH-|SGH-|SHV-|Galaxy)' => 'Samsung',
        '^(?:Pixel|Nexus)' => 'Google',
        '^(?:Redmi|POCO|Mi |MI |Xiaomi|M2\d{3}[A-Z]\d|2[0-4]\d{5,}[A-Z]{1,3}$|HM )' => 'Xiaomi',
        '^(?:ONEPLUS|(?:A|BE|DE|EB|GM|HD|IN|KB|LE|NE|PHB|PJ[A-Z])\d{3,4}$)' => 'OnePlus',
        '^RMX\d' => 'Realme',
        '^(?:moto|Motorola|XT\d{4})' => 'Motorola',
        '^(?:LM-|LG-|LG )' => 'LG',
        '^(?:HUAWEI|ELS-|ANA-|NOH-|VOG-|LYA-|MAR-|JNY-|ALN-|BRA-)' => 'Huawei',
        '^HONOR' => 'Honor',
        '^(?:vivo|V2\d{3}[A-Z]?$)' => 'vivo',
        '^(?:Nokia|TA-\d{4})' => 'Nokia',
        '^(?:XQ-|SO-\d|Xperia|G8\d{3})' => 'Sony',
        '^(?:ASUS|ZS\d{3}|AI2\d{3}|Transformer)' => 'ASUS',
        '^(?:Lenovo|TB-?\d)' => 'Lenovo',
        '^TECNO' => 'Tecno',
        '^Infinix' => 'Infinix',
        '^itel' => 'itel',
        '^ZTE' => 'ZTE',
        '^HTC' => 'HTC',
        '^FP\d' => 'Fairphone',
        '^(?:KF[A-Z]{2,}|Kindle)' => 'Amazon',
        '^Nothing' => 'Nothing',
    ];

    /**
     * @return array{type: DeviceType, brand: string|null, model: string|null}|null
     */
    public static function special(string $userAgent): ?array
    {
        foreach (self::SPECIAL as [$type, $pattern, $brand, $model]) {
            if (preg_match('~'.$pattern.'~i', $userAgent)) {
                return ['type' => $type, 'brand' => $brand, 'model' => $model];
            }
        }

        return null;
    }

    /**
     * The model from an Android User-Agent: "SM-S928B" in
     * "(Linux; Android 14; SM-S928B Build/UP1A...)". Null for Chrome's
     * reduced User-Agent, which always says "K".
     */
    public static function androidModel(string $userAgent): ?string
    {
        if (! preg_match('~\(([^)]*Android[^)]*)\)~i', $userAgent, $match)) {
            return null;
        }

        foreach (array_map('trim', explode(';', $match[1])) as $part) {
            $part = trim((string) preg_replace('~\s*Build/.*$~i', '', $part));

            if ($part === '' || $part === 'K'
                || preg_match('~^(?:Linux|U|wv|Mobile|Tablet|TV|Android(?:[ /][\d.]+)?|[a-z]{2}(?:[-_][a-z]{2,4})?|rv:.*|arm.*|x86.*|aarch64.*|HMSCore.*|HarmonyOS.*)$~i', $part)) {
                continue;
            }

            return (string) preg_replace('~^SAMSUNG[ -]~i', '', $part);
        }

        return null;
    }

    /** Who makes an Android model, when the model name gives it away. */
    public static function androidBrand(?string $model, string $userAgent = ''): ?string
    {
        if ($model !== null) {
            foreach (self::ANDROID_BRANDS as $pattern => $brand) {
                if (preg_match('~'.$pattern.'~i', $model)) {
                    return $brand;
                }
            }
        }

        // Older Samsung builds put the maker in the User-Agent. (Samsung Internet
        // alone proves nothing: it runs on other phones too.)
        if (preg_match('~\bSAMSUNG\b~', $userAgent)) {
            return 'Samsung';
        }

        return null;
    }
}
