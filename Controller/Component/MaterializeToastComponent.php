<?php

/**
 * Class ToastComponent
 *
 * @property object           $params
 * @property Controller       $Controller
 * @property SessionComponent $Session
 */
class MaterializeToastComponent extends Component {

    public $components = [
        'Session',
    ];

    public $settings = [
        'session_key' => 'Toast',
        'types' => [
            'success' => [
                'html' => 'Top, de gegevens zijn opgeslagen',
                'classes' => 'green',
            ],
            'danger' => [
                'html' => 'Oeps! De gegevens konden niet worden opgeslagen',
                'classes' => 'red',
            ],
            'warning' => [
                'html' => 'Let op, er is iets aan de hand',
                'classes' => 'orange',
            ],
            'info' => [
                'html' => 'Abcdefg',
                'classes' => 'blue',
            ],
        ],
    ];

    public function __construct(ComponentCollection $collection, array $settings) {
        $this->Controller = $collection->getController();
        $settings = array_merge($this->settings, (array) $settings);

        parent::__construct($collection, $settings);
    }


    /**
     * Set a toast message and redirect
     *
     * @param       $message
     * @param array $url
     *
     * @param array $options
     *
     * @return bool|CakeResponse|null
     */
    public function redirect($message, $url, $options = []) {
        $options['redirect'] = $url;
        return $this->add($message, $options);
    }


    /**
     * Set a toast message
     *
     * @param       $message
     * @param array $options
     *
     * @return bool|CakeResponse
     */
    public function add($message, $options = []) {

        $toast = [
            'html' => $message,
            'displayLength' => $this->_getOption($options, 'displayLength') ?: 4000,
            'inDuration' => $this->_getOption($options, 'inDuration') ?: 300,
            'outDuration' => $this->_getOption($options, 'outDuration') ?: 375,
            'completeCallback' => $this->_getOption($options, 'completeCallback') ?: null,
            'activationPercent' => $this->_getOption($options, 'activationPercent') ?: 0.8,
            'button' => $this->_getOption($options, 'button') ?: null,
        ];

        $class = explode(' ', $this->_getOption($options, 'class', ''));

        # Check if we dont want the toast to timeout
        $noTimeout = $this->_getOption($options, 'no-timeout', false);
        if ($noTimeout) {
            $toast['displayLength'] = 'Infinity';
        }

        # Toast color
        $color = $this->_getOption($options, 'color');
        if ($color) {
            $class[] = $color;
        }

        # Toast text color
        $textColor = $this->_getOption($options, 'text-color');
        if ($textColor) {
            $class[] = $textColor . '-text';
        }

        $toast['classes'] = trim(implode(' ', $class));

        # Get redirect option
        $redirect = $this->_getOption($options, 'redirect');

        # Fetch previously set toasts
        $toasts = $this->Session->read($this->settings['session_key']);

        # If we don't have any, make the toasts an empty array
        if ( ! $toasts) {
            $toasts = [];
        }

        # Append the new message
        $toasts[] = $toast;

        # Write back to the session
        $this->Session->write($this->settings['session_key'], $toasts);

        # Redirect
        if ($redirect) {
            return $this->Controller->redirect($redirect);
        }
    }

    private function _getOption(&$options, $name, $default = null, $unset = true) {
        if ( ! is_array($options)) {
            debug('$options should be an array');

            return $default;
        }

        # Option is array key
        if (array_key_exists($name, $options)) {
            $value = $options[$name];
            if ($unset) {
                unset($options[$name]);
            }

            return $value;
        }

        # Option is array value, with a numeric key
        $key = array_search($name, $options, true);
        if (is_numeric($key)) {
            if ($unset) {
                unset($options[$key]);
            }

            return true;
        }

        # Option not found
        return $default;
    }

}
