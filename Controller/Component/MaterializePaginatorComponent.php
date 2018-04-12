<?php

App::uses('PaginatorComponent', 'Controller/Component');

/**
 * Class MaterializePaginatorComponent
 *
 * @property object     $params
 * @property Controller $Controller
 */
class MaterializePaginatorComponent extends PaginatorComponent {

    public function startup(Controller $controller) {
        parent::startup($controller);
    }

}

