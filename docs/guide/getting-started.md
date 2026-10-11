# Getting started

Complete [installation](./installation.md). This example adds tags to an existing Articles table and edit page without requiring JavaScript or a tag counter column.

In `ArticlesTable::initialize()`, keep the existing table setup and add:

```php
$this->addBehavior('Tags.Tag', ['taggedCounter' => false]);
```

The behavior creates the associations and prepares the `tag_list` field for forms. In `ArticlesController`, load existing tags before patching:

```php
public function edit(string $id): ?\Cake\Http\Response {
    $articles = $this->fetchTable('Articles');
    $article = $articles->get($id, contain: ['Tags']);
    if ($this->getRequest()->is(['patch', 'post', 'put'])) {
        $article = $articles->patchEntity($article, $this->getRequest()->getData());
        if ($articles->save($article)) {
            $this->Flash->success('The article and its tags have been saved.');

            return $this->redirect(['action' => 'edit', $article->id]);
        }
        $this->Flash->error('The article could not be saved.');
    }
    $this->set(compact('article'));

    return null;
}
```

In the existing edit template, add `tag_list` inside the article form:

```php
<?= $this->Form->create($article) ?>
<?= $this->Form->control('title') ?>
<?= $this->Form->control('tag_list', ['label' => 'Tags']) ?>
<?= $this->Form->button('Save') ?>
<?= $this->Form->end() ?>
```

Enter `CakePHP, Tutorials`, save, then reopen the edit page. Both tags should appear in the text field. Remove one and save to unlink it from the article. Clearing the field removes that article's tag links; it does not delete the shared tag records.

Keep the application's authorization, CSRF protection and existing entity accessibility rules for article fields. The behavior makes its configured association property accessible during marshalling; it does not grant access to unrelated article fields.

For non-form code, the same input works through the ORM:

```php
$articles = $this->fetchTable('Articles');
$article = $articles->get($id, contain: ['Tags']);
$article = $articles->patchEntity($article, ['tag_list' => 'CakePHP, Tutorials']);
$articles->saveOrFail($article);
```

Always contain the current association before updating an existing entity. The ORM needs those entities to retain and remove links correctly.
