<?php

class EU4EncodingConverter
{
    public const CMD_SPACE_PARAM = '-space';

    protected const LOW_BYTE_OFFSET = 14;
    protected const HIGH_BYTE_OFFSET = -9;

    protected const INTERNAL_CHARS = [
        0x00, 0x0A, 0x0D,
        0x20,
        0x22, 0x24,
        0x40, 0x5B, 0x5C,
        0x7B, 0x7D, 0x7E, 0x80,
        0xA3, 0xA4, 0xA7, 0xBD,
        0x3B,    // ;
        0x5D,    // ]
        0x5F,    // _
        0x3D,    // =
        0x23,    // #
        0x2F,    // /
    ];

    protected const DEFAULT_ESCAPE_CHR = 0x10;

    protected $result = '';

    protected static $_uniqueInstance = null;

    public static function getInstance(): EU4EncodingConverter
    {
        if (self::$_uniqueInstance === null) self::$_uniqueInstance = new self();
        return self::$_uniqueInstance;
    }

    protected function __construct() {}

    public function getHex(string $string, bool $whitespace = false): string
    {
        $separator = $whitespace ? ' ' : '';
        $result = [];
        $chars = mb_str_split($string, 1, 'UTF-8');
        foreach ($chars as $char) {
            $result[] = $this->build($char, $whitespace);
        }
        $this->result = implode($separator, $result);
        return strtoupper($this->result);
    }

    protected function build(string $char, bool $whitespace = false): string
    {
        $separator = $whitespace ? ' ' : '';

        $dec = mb_ord($char, 'UTF-8');
        $hex = dechex($dec);
        if ($dec < 256) {
            return $hex;
        }

        $low = hexdec(substr($hex, -2));
        $high = hexdec(substr($hex, 0, 2));

        $escapeChr = self::DEFAULT_ESCAPE_CHR;
        if (in_array($high, self::INTERNAL_CHARS)) {
            $escapeChr += 2;
        }
        if (in_array($low, self::INTERNAL_CHARS)) {
            $escapeChr++;
        }

        switch ($escapeChr) {
            case self::DEFAULT_ESCAPE_CHR + 1:
                $low += self::LOW_BYTE_OFFSET;
                break;
            case self::DEFAULT_ESCAPE_CHR + 2:
                $high += self::HIGH_BYTE_OFFSET;
                break;
            case self::DEFAULT_ESCAPE_CHR + 3:
                $low += self::LOW_BYTE_OFFSET;
                $high += self::HIGH_BYTE_OFFSET;
                break;
            case self::DEFAULT_ESCAPE_CHR:
            default:
                break;
        }

        $hexCodes = [
            $this->padHex(dechex($escapeChr)),
            $this->padHex(dechex($low)),
            $this->padHex(dechex($high)),
        ];

        return implode($separator, $hexCodes);
    }

    protected function cp1252ToUtf8(int $char): int
    {
        $escapeList = [
            0x80 => 0x20AC, 0x82 => 0x201A, 0x83 => 0x0192, 0x84 => 0x201E,
            0x85 => 0x2026, 0x86 => 0x2020, 0x87 => 0x2021, 0x88 => 0x02C6,
            0x89 => 0x2030, 0x8A => 0x0160, 0x8B => 0x2039, 0x8C => 0x0152,
            0x8E => 0x017D, 0x91 => 0x2018, 0x92 => 0x2019, 0x93 => 0x201C,
            0x94 => 0x201D, 0x95 => 0x2022, 0x96 => 0x2013, 0x97 => 0x2014,
            0x98 => 0x02DC, 0x99 => 0x2122, 0x9A => 0x0161, 0x9B => 0x203A,
            0x9C => 0x0153, 0x9E => 0x017E, 0x9F => 0x0178
        ];
        return $escapeList[$char] ?? $char;
    }

    protected function padHex(string $hex): string
    {
        return str_pad($hex, 2, '0', STR_PAD_LEFT);
    }
}
