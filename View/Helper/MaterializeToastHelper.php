<?php
App::uses('AppHelper', 'View/Helper');
App::uses('CakeSession', 'Model/Datasource');

/**
 * Class ToastHelper
 *
 * @property HtmlHelper Html
 */
class MaterializeToastHelper extends AppHelper {

    public $helpers = [
        'Html',
    ];

    public $settings = [
        'session_key' => 'Toast',
    ];

    /**
     * @param string $return_type
     * Can be:
     * 'array' => Will return the toasts messages as an array
     * 'window_variable' => Will output a <script> block with window.toast_messages contain the toast array
     * 'auto_init' => Will output a <script> block and init Materialize toasts with M.toast() for all toast messages;
     *
     * @return mixed|string
     */
    public function render($return_type = 'auto_init') {

        $toasts = CakeSession::read($this->settings['session_key']);

        # Delete the toasts
        CakeSession::delete($this->settings['session_key']);

        foreach ($toasts as &$toast) {
            $toast['html'] = $this->_button($toast);
        }

        # Return the array
        if ($return_type === 'array') {
            return $toasts;
        }

        # Setup the json bitmask
        $bitmask = JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES;
        if (Configure::read('debug') > 0) {
            $bitmask = $bitmask|JSON_PRETTY_PRINT;
        }

        # If we want it as a window variable in a script tag
        if ($return_type === 'window_variable') {

            # Build the json object
            $toasts_object = json_encode($toasts, $bitmask);

            return $this->Html->tag('script', "\nwindow.toast_messages" . " = " . $toasts_object . ";\n");
        }

        $script_content = [];
        foreach ($toasts as $toast) {

            # Build the json object
            $toasts_object = json_encode($toast, $bitmask);

            # Add M.toast() to the script content
            $script_content[] = 'M.toast(' . $toasts_object . ');';
        }

        # Return the script
        return $this->Html->tag('script', "\n" . implode(null, $script_content));
    }

    private function _button($toast) {
        $buttonOptions = $this->Html->getOption($toast, 'button');

        # No button? Return the html
        if ( ! $buttonOptions) {
            return $toast['html'];
        }

        # Get text and url
        $text = $this->Html->getOption($buttonOptions, 'text');
        $url = $this->Html->getOption($buttonOptions, 'url');

        # Button color
        $color = $this->Html->getOption($buttonOptions, 'color');
        if ($color) {
            $this->Html->prependClass($buttonOptions, $color);
        }

        # Text color
        $textColor = $this->Html->getOption($buttonOptions, 'text-color');
        if ($textColor) {
            $this->Html->prependClass($buttonOptions, $textColor . '-text');
        }

        # Add materialize button classes
        $this->Html->prependClass($buttonOptions, 'btn-flat toast-action');

        # If we have an url, make the button a link
        if ($url) {
            return $toast['html'] . ' ' . $this->Html->link($text, $url, $buttonOptions);
        }

        # Return button
        return $toast['html'] . ' ' . $this->Html->button($text, $buttonOptions);
    }

}
