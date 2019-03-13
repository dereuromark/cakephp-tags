<?php
namespace Tags\Model\Table;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\ORM\Table;
use RuntimeException;

/**
 * @method \Tags\Model\Entity\Tag get($primaryKey, $options = [])
 * @method \Tags\Model\Entity\Tag newEntity($data = null, array $options = [])
 * @method \Tags\Model\Entity\Tag[] newEntities(array $data, array $options = [])
 * @method \Tags\Model\Entity\Tag|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Tags\Model\Entity\Tag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Tags\Model\Entity\Tag[] patchEntities($entities, array $data, array $options = [])
 * @method \Tags\Model\Entity\Tag findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TagsTable extends Table {

	/**
	 * Initialize table config.
	 *
	 * @param array $config Config options
	 * @return void
	 * @throws \RuntimeException
	 */
	public function initialize(array $config) {
		$this->setTable('tags_tags');
		$this->setDisplayField('label'); // Change to name?
		$this->addBehavior('Timestamp');

		/** @var array|bool|string|null $slugger */
		$slugger = Configure::read('Tags.slugBehavior');
		if (!$slugger) {
			return;
		}
		if ($slugger === true) {
			if (Plugin::loaded('Tools')) {
				$this->addBehavior('Tools.Slugged');
				return;
			}
			if (Plugin::loaded('Muffin/Slug')) {
				$this->addBehavior('Muffin/Slug.Slug');
				return;
			}

			throw new RuntimeException('Auto-slug behavior not found, plugin not loaded.');
		}

		$config = [];
		if (!is_string($slugger)) {
			$config = current($slugger);
			$slugger = key($slugger);
		}
		$this->addBehavior($slugger, $config);
	}

}
