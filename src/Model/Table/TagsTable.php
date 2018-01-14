<?php
namespace Muffin\Tags\Model\Table;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\ORM\Table;
use RuntimeException;

class TagsTable extends Table
{

    /**
     * Initialize table config.
     *
     * @param array $config Config options
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('tags_tags');
        $this->displayField('label'); // Change to name?
        $this->addBehavior('Timestamp');

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

			throw new RuntimeException('Auto-slug behaviors not found');
		}

		$this->addBehavior($slugger);
    }
}
