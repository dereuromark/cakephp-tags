<?php
namespace Tags\Test\TestCase\View\Helper;

use Cake\Http\ServerRequest as Request;
use Cake\TestSuite\Stub\Response;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Tags\View\Helper\TagHelper;

class TagHelperTest extends TestCase {

	/**
	 * @var \Cake\View\View
	 */
	protected $View;

	/**
	 * @var \Tags\View\Helper\TagHelper
	 */
	protected $TagHelper;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$request = new Request();
		$response = new Response();
		$this->View = new View($request, $response);
		$this->TagHelper = new TagHelper($this->View);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->TagHelper);
	}

	/**
	 * @return void
	 */
	public function testControl() {
		$result = $this->TagHelper->control();

		$expected = '<div class="input text"><label for="tag-list">Tag List</label><input type="text" name="tag_list" id="tag-list"/></div>';
		$this->assertSame($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testControlArray() {
		$this->TagHelper->config('strategy', 'array');
		$result = $this->TagHelper->control();

		$expected = '<div class="input select"><label for="tag-list">Tag List</label><input type="hidden" name="tag_list" value=""/><select name="tag_list[]" multiple="multiple" id="tag-list"></select></div>';
		$this->assertSame($expected, $result);
	}

}
