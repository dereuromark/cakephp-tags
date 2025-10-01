<?php

namespace Tags\Utility;

/**
 * Color utility class for tag color operations.
 *
 * Provides validation and utility methods for working with hex colors.
 */
class ColorUtility {

	/**
	 * Check if a color is valid hex format.
	 *
	 * @param string $color Color to validate
	 * @return bool
	 */
	public static function isValidHex(string $color): bool {
		return (bool)preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
	}

	/**
	 * Generate a random hex color.
	 *
	 * @return string Random hex color code
	 */
	public static function random(): string {
		return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
	}

	/**
	 * Normalize a hex color to uppercase with # prefix.
	 *
	 * @param string $color Color to normalize
	 * @return string Normalized color
	 */
	public static function normalize(string $color): string {
		$color = ltrim($color, '#');
		$color = strtoupper($color);

		return '#' . $color;
	}

	/**
	 * Convert hex color to RGB array.
	 *
	 * @param string $color Hex color code
	 * @return array<string, int> Array with keys 'r', 'g', 'b'
	 */
	public static function hexToRgb(string $color): array {
		$hex = ltrim($color, '#');

		return [
			'r' => (int)hexdec(substr($hex, 0, 2)),
			'g' => (int)hexdec(substr($hex, 2, 2)),
			'b' => (int)hexdec(substr($hex, 4, 2)),
		];
	}

	/**
	 * Convert RGB values to hex color.
	 *
	 * @param int $r Red value (0-255)
	 * @param int $g Green value (0-255)
	 * @param int $b Blue value (0-255)
	 * @return string Hex color code
	 */
	public static function rgbToHex(int $r, int $g, int $b): string {
		return sprintf('#%02X%02X%02X', $r, $g, $b);
	}

}
