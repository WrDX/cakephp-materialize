<?php

App::uses('FormHelper', 'View/Helper');
App::uses('CakeTime', 'Utility');

/**
 * Class MaterializeFormHelper
 *
 * @property MaterializeHtmlHelper $Html
 *
 * TODO: Add validation errors, helper-text maybe?
 * TODO: Add date / time pickers
 *
 * @method text($fieldName, $options = [])
 *
 */
class MaterializeFormHelper extends FormHelper {

    public $helpers = [
        'Html' => [
            'className' => 'Materialize.MaterializeHtml',
        ],
    ];

    protected $_minimizedAttributeFormat = '%s';

    private $_row = true;

    private $_colsizes = 's12';

    public function create($model = null, $options = []) {

        # Row and colsizes
        $this->colsizes($options);

        # Disable HTML5 validation by default
        $options['novalidate'] = $this->Html->getOption($options, 'novalidate', true);

        return parent::create($model, $options);

    }

    public function colsizes($options = []) {
        # Row for all inputs in this form
        $this->_row = $this->Html->getOption($options, 'row', true);

        # Colsizes for all inputs in this form
        $this->_colsizes = $this->Html->getOption($options, 'colsizes', 's12');
    }

    public function input($fieldName, $options = []) {

        # Cache for fallback
        $_options = $options;

        # Entity
        $this->setEntity($fieldName);

        # Parse options
        $options = $this->_parseOptions($options);

        # Label text & attributes
        list($labelText, $labelAttributes) = $this->_labelTextAttributes($fieldName, $options);
        unset($options['label']);

        # Row
        $row = $this->Html->getOption($options, 'row', $this->_row);

        # Colsizes
        $colsizes = $this->Html->getOption($options, 'colsizes', $this->_colsizes);

        # Type
        $type = $this->Html->getOption($options, 'type');

        # Selected
        $selected = $this->Html->getOption($options, 'selected');

        $html = null;

        # Error (provided through options)
        $error = $this->Html->getOption($options, 'error');

        # Error message (provided through options)
        $errorMessage = $this->Html->getOption($options, 'errorMessage');

        # Get error message from model validation
        $errMsg = null;
        if ($type !== 'hidden' && $error !== false) {
            $errMsg = $this->error($fieldName, $errorMessage, ['wrap' => false]);
        }

        switch ($type) {
            case 'text':
            case 'password':
            case 'textarea':
            case 'select':
            case 'datepicker':
            case 'timepicker':

                # Icon
                $iconHtml = null;
                if ($type !== 'select') {
                    $icon = $this->Html->getOption($options, 'icon');
                    if ($icon) {
                        $iconHtml = $this->Html->icon($icon, ['class' => 'prefix']);
                    }
                }

                # Textarea specific
                if ($type === 'textarea') {
                    # Add class
                    $options = $this->addClass($options, 'materialize-textarea');

                    # Remove rows and cols
                    $options['rows'] = false;
                    $options['cols'] = false;
                }

                # Select specific
                if ($type === 'select') {
                    # Force empty false on multiple
                    if ($this->Html->getOption($options, 'multiple', false, false)) {
                        $options['empty'] = false;
                    }
                    # Force empty true on disabled
                    if ($this->Html->getOption($options, 'disabled', false, false)) {
                        $options['empty'] = true;
                    }
                }

                # Date / time specific
                if ($type === 'date' || $type === 'time') {
                    # Add type back into options
                    $options['type'] = $type;
                }

                # Error
                $errorHtml = null;
                if ($errMsg) {
                    $errorHtml = $this->Html->tag('span', $errMsg, ['class' => 'helper-text red-text']);
                }
                $options['error'] = false;

                # Input
                $input = $this->_getInput(compact('type', 'fieldName', 'options', 'selected'));

                # Render html
                $html = $this->Html->tag(
                    'div',
                    (
                        $iconHtml .
                        $input .
                        $this->Html->tag(
                            'label',
                            $labelText,
                            $labelAttributes
                        ) .
                        $errorHtml
                    ),
                    ['class' => 'input-field']
                );

                break;

            case 'checkbox':
                # Filled-in
                $filledIn = $this->Html->getOption($options, 'filled-in');
                if ($filledIn) {
                    $this->Html->appendClass($options, 'filled-in');
                }

                # Error
                $errorHtml = null;
                if ($errMsg) {
                    $errorHtml = $this->Html->tag(
                        'div',
                        $this->Html->tag(
                            'span',
                            $errMsg,
                            [
                                'class' => 'helper-text red-text',
                            ]
                        ),
                        [
                            'style' => 'font-size: 12px; margin-top: 8px;',
                        ]
                    );
                }
                $options['error'] = false;

                # Input
                $input = $this->_getInput(compact('type', 'fieldName', 'options', 'selected'));

                # Render html
                $html = $this->Html->tag(
                    'p',
                    (

                        $this->Html->tag(
                            'label',
                            (
                                $input .
                                $this->Html->span($labelText)
                            )
                        ) .
                        $errorHtml
                    )
                );

                break;

            case 'radio':

                $radioOptions = (array) $options['options'];
                unset($options['options']);

                # With-gap
                $withGap = $this->Html->getOption($options, 'with-gap', true);
                if ($withGap) {
                    $this->Html->appendClass($options, 'with-gap');
                }

                # Error
                $errorHtml = null;
                if ($errMsg) {
                    $errorHtml = $this->Html->tag(
                        'div',
                        $this->Html->tag(
                            'span',
                            $errMsg,
                            [
                                'class' => 'helper-text red-text',
                            ]
                        ),
                        [
                            'style' => 'font-size: 12px; margin-top: 8px;',
                        ]
                    );
                }
                $options['error'] = false;

                $html = $this->Html->tag(
                    'div',
                    (
                        $this->_getInput(compact('type', 'fieldName', 'options', 'radioOptions', 'selected')) .
                        $errorHtml
                    )
                );

                break;

            case 'switch':

                # Labels
                $labelOff = $this->Html->getOption($options, 'label-off', 'Off');
                $labelOn = $this->Html->getOption($options, 'label-on', 'On');

                # Input
                $input = $this->_getInput(compact('type', 'fieldName', 'options', 'selected'));

                # Render html
                $html = $this->Html->tag(
                    'div',
                    $this->Html->tag(
                        'label',
                        (
                            $labelOff .
                            $input .
                            $this->Html->span('', ['class' => 'lever']) .
                            $labelOn
                        )
                    ),
                    [
                        'class' => 'switch',
                    ]
                );

                break;

            default:

                return parent::input($fieldName, $_options);
        }

        # Apply col
        if ($colsizes) {
            $html = $this->Html->tag(
                'div',
                $html,
                ['class' => 'col ' . $colsizes]
            );
        }

        # Apply row
        if ($row) {
            $html = $this->Html->tag(
                'div',
                $html,
                ['class' => 'row']
            );
        }

        return $html;

    }

    public function datepicker($fieldName, $options = []) {

        $this->Html->prependClass($options, 'datepicker');

        $options['type'] = 'text';

        # Datepicker options
        $datepickerOptions = $this->Html->getOption($options, 'datepicker-options', []);

        # Format
        $datepickerOptions['format'] = $this->Html->getOption($datepickerOptions, 'format', 'yyyy-mm-dd');

        # Submit format
        $datepickerOptions['submitFormat'] = $this->Html->getOption($datepickerOptions, 'submitFormat', 'yyyy-mm-dd');

        # Add datepicker-options to input
        $options['data-datepicker-options'] = json_encode($datepickerOptions);

        return $this->text($fieldName, $options);

    }

    public function timepicker($fieldName, $options = []) {

        $this->Html->prependClass($options, 'timepicker');

        $options['type'] = 'text';

        # Datepicker options
        $timepickerOptions = $this->Html->getOption($options, 'timepicker-options', []);

        # twelveHour
        $timepickerOptions['twelveHour'] = $this->Html->getOption($timepickerOptions, 'twelveHour', false);

        # Add timepicker-options to input
        $options['data-timepicker-options'] = json_encode($timepickerOptions);

        return $this->text($fieldName, $options);

    }

    public function radio($fieldName, $options = [], $attributes = []) {

        $attributes['options'] = $options;
        $attributes = $this->_initInputField($fieldName, $attributes);
        unset($attributes['options']);

        $showEmpty = $this->_extractOption('empty', $attributes);
        if ($showEmpty) {
            $showEmpty = ($showEmpty === true) ? __d('cake', 'empty') : $showEmpty;
            $options = ['' => $showEmpty] + $options;
        }
        unset($attributes['empty'], $attributes['legend'], $attributes['fieldset'], $attributes['label'], $attributes['between']);

        $value = null;
        if (isset($attributes['value'])) {
            $value = $attributes['value'];
        } else {
            $value = $this->value($fieldName);
        }

        $disabled = [];
        if (isset($attributes['disabled'])) {
            $disabled = $attributes['disabled'];
        }

        $out = [];

        $hiddenField = isset($attributes['hiddenField']) ? $attributes['hiddenField'] : true;
        unset($attributes['hiddenField']);

        if (isset($value) && is_bool($value)) {
            $value = $value ? 1 : 0;
        }

        $this->_domIdSuffixes = [];
        foreach ($options as $optValue => $optTitle) {
            $optionsHere = ['value' => $optValue, 'disabled' => false];
            if (is_array($optTitle)) {
                if (isset($optTitle['value'])) {
                    $optionsHere['value'] = $optTitle['value'];
                }

                $optionsHere += $optTitle;
                $optTitle = $optionsHere['name'];
                unset($optionsHere['name']);
            }

            if (isset($value) && strval($optValue) === strval($value)) {
                $optionsHere['checked'] = 'checked';
            }
            $isNumeric = is_numeric($optValue);
            if ($disabled && ( ! is_array($disabled) || in_array((string) $optValue, $disabled, ! $isNumeric))) {
                $optionsHere['disabled'] = true;
            }
            $tagName = $attributes['id'] . $this->domIdSuffix($optValue);

            $optTitle = $this->Html->span($optTitle);

            $allOptions = $optionsHere + $attributes;
            $out[] = $this->Html->p(
                $this->Html->label(
                    $this->Html->useTag('radio', $attributes['name'], $tagName,
                        array_diff_key($allOptions, ['name' => null, 'type' => null, 'id' => null]),
                        $optTitle
                    )
                )
            );

        }
        $hidden = null;

        if ($hiddenField) {
            if ( ! isset($value) || $value === '') {
                $hidden = $this->hidden($fieldName, [
                    'form' => isset($attributes['form']) ? $attributes['form'] : null,
                    'id' => $attributes['id'] . '_',
                    'value' => $hiddenField === true ? '' : $hiddenField,
                    'name' => $attributes['name'],
                ]);
            }
        }

        return $hidden . implode(null, $out);

    }

    /**
     * Generates an input element
     *
     * @param array $args The options for the input element
     *
     * @return string The generated input element
     */
    protected function _getInput($args) {

        /**
         * @var $type
         * @var $fieldName
         * @var $options
         */
        extract($args);

        switch ($type) {
            case 'datepicker':
                return $this->datepicker($fieldName, $options);
            case 'timepicker':
                return $this->timepicker($fieldName, $options);
            case 'switch':
                return $this->checkbox($fieldName, $options);
        }

        return parent::_getInput($args);

    }

    private function _labelTextAttributes($fieldName, $options = []) {

        if ($fieldName === null) {
            $fieldName = implode('.', $this->entity());
        }

        $labelAttributes = $this->domId([], 'for');

        $labelOptions = $this->Html->getOption($options, 'label');

        $labelText = null;
        if (is_array($labelOptions)) {
            if (isset($labelOptions['text'])) {
                $labelText = $labelOptions['text'];
                unset($labelOptions['text']);
            }
            $labelAttributes = array_merge($labelAttributes, $labelOptions);
        } elseif (is_string($labelOptions)) {
            $labelText = $labelOptions;
        }

        if ($labelText === null) {
            if (strpos($fieldName, '.') !== false) {
                $fieldElements = explode('.', $fieldName);
                $labelText = array_pop($fieldElements);
            } else {
                $labelText = $fieldName;
            }
            if (substr($labelText, -3) === '_id') {
                $labelText = substr($labelText, 0, -3);
            }
            $labelText = __(Inflector::humanize(Inflector::underscore($labelText)));
        }

        if (isset($options['id']) && is_string($options['id'])) {
            $labelAttributes = array_merge($labelAttributes, ['for' => $options['id']]);
        }

        # Add class active to $labelAttributes if input has placeholder or is prefilled through request data or default
        if (Hash::get($options, 'placeholder') || Hash::get($options, 'value') || Hash::get($this->value($options, $fieldName) ?: [], 'value')) {
            $this->Html->appendClass($labelAttributes, 'active');
        }

        return [$labelText, $labelAttributes];

    }

}
