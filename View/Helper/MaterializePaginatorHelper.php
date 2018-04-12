<?php

App::uses('PaginatorHelper', 'View/Helper');

/**
 * Class MaterializePaginatorHelper
 *
 * @property MaterializeHtmlHelper $Html
 * @property object                $params
 */
class MaterializePaginatorHelper extends PaginatorHelper {

    public $helpers = [
        'Html' => [
            'className' => 'Materialize.MaterializeHtml',
        ],
    ];

}

