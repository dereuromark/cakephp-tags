<?php

namespace Tags\Test\TestCase\Utility;

use Cake\TestSuite\TestCase;
use Tags\Utility\ColorUtility;

class ColorUtilityTest extends TestCase {

	/**
	 * @return void
	 */
	public function testIsValidHex() {
		$this->assertTrue(ColorUtility::isValidHex('#FF5733'));
		$this->assertTrue(ColorUtility::isValidHex('#000000'));
		$this->assertTrue(ColorUtility::isValidHex('#ffffff'));
		$this->assertTrue(ColorUtility::isValidHex('#AbCdEf'));

		$this->assertFalse(ColorUtility::isValidHex('FF5733'));
		$this->assertFalse(ColorUtility::isValidHex('#FF573'));
		$this->assertFalse(ColorUtility::isValidHex('#FF57333'));
		$this->assertFalse(ColorUtility::isValidHex('#GG5733'));
		$this->assertFalse(ColorUtility::isValidHex(''));
	}

	/**
	 * @return void
	 */
	public function testRandom() {
		$color = ColorUtility::random();

		$this->assertTrue(ColorUtility::isValidHex($color));
		$this->assertStringStartsWith('#', $color);
		$this->assertSame(7, strlen($color));
	}

	/**
	 * @return void
	 */
	public function testRandomUniqueness() {
		$colors = [];
		for ($i = 0; $i < 10; $i++) {
			$colors[] = ColorUtility::random();
		}

		$this->assertSame(count($colors), count(array_unique($colors)), 'Random colors should be different');
	}

	/**
	 * @return void
	 */
	public function testNormalize() {
		$this->assertSame('#FF5733', ColorUtility::normalize('#ff5733'));
		$this->assertSame('#FF5733', ColorUtility::normalize('ff5733'));
		$this->assertSame('#FF5733', ColorUtility::normalize('FF5733'));
		$this->assertSame('#ABCDEF', ColorUtility::normalize('#aBcDeF'));
	}

	/**
	 * @return void
	 */
	public function testHexToRgb() {
		$result = ColorUtility::hexToRgb('#FF5733');
		$expected = [
			'r' => 255,
			'g' => 87,
			'b' => 51,
		];
		$this->assertSame($expected, $result);

		$result = ColorUtility::hexToRgb('#000000');
		$expected = [
			'r' => 0,
			'g' => 0,
			'b' => 0,
		];
		$this->assertSame($expected, $result);

		$result = ColorUtility::hexToRgb('#ffffff');
		$expected = [
			'r' => 255,
			'g' => 255,
			'b' => 255,
		];
		$this->assertSame($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testHexToRgbWithoutHash() {
		$result = ColorUtility::hexToRgb('FF5733');
		$expected = [
			'r' => 255,
			'g' => 87,
			'b' => 51,
		];
		$this->assertSame($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testRgbToHex() {
		$this->assertSame('#FF5733', ColorUtility::rgbToHex(255, 87, 51));
		$this->assertSame('#000000', ColorUtility::rgbToHex(0, 0, 0));
		$this->assertSame('#FFFFFF', ColorUtility::rgbToHex(255, 255, 255));
		$this->assertSame('#0A0B0C', ColorUtility::rgbToHex(10, 11, 12));
	}

	/**
	 * @return void
	 */
	public function testRgbToHexAndBack() {
		$originalColor = '#FF5733';
		$rgb = ColorUtility::hexToRgb($originalColor);
		$hexColor = ColorUtility::rgbToHex($rgb['r'], $rgb['g'], $rgb['b']);

		$this->assertSame($originalColor, $hexColor);
	}

}
