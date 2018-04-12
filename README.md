## Materialize CSS plugin for CakePHP 2.x

Configure by putting the following settings in your AppController:

```
    public $components = [
        'Paginator' => ['className' => 'Materialize.MaterializePaginator'],
    ];

    public $helpers = [
        'Html' => ['className' => 'Materialize.MaterializeHtml'],
        'Icon' => ['className' => 'Materialize.MaterializeIcon'],
        'Form' => ['className' => 'Materialize.MaterializeForm'],
        'Paginator' => ['className' => 'Materialize.MaterializePaginator'],
    ];
```