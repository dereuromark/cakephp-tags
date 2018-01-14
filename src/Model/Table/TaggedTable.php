<?php
namespace Muffin\Tags\Model\Table;

use Cake\ORM\Table;

/**
 * @property \Muffin\Tags\Model\Table\TagsTable|\Cake\ORM\Association\BelongsTo $Tags
 *
 * @method \Muffin\Tags\Model\Entity\Tagged get($primaryKey, $options = [])
 * @method \Muffin\Tags\Model\Entity\Tagged newEntity($data = null, array $options = [])
 * @method \Muffin\Tags\Model\Entity\Tagged[] newEntities(array $data, array $options = [])
 * @method \Muffin\Tags\Model\Entity\Tagged|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Muffin\Tags\Model\Entity\Tagged patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Muffin\Tags\Model\Entity\Tagged[] patchEntities($entities, array $data, array $options = [])
 * @method \Muffin\Tags\Model\Entity\Tagged findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TaggedTable extends Table
{

    /**
     * Initialize table config.
     *
     * @param array $config Config options
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('tags_tagged');
		$this->belongsTo('Tags', [
			'className' => 'Muffin/Tags.Tags',
			'foreignKey' => 'tag_id',
			'propertyName' => 'tags',
		]);
        $this->addBehavior('Timestamp');
    }
}
