<?php

App::uses('HtmlHelper', 'View/Helper');

/**
 * Class MaterializeHtmlHelper
 *
 * @method button($text, $options = [])
 * @method em($text, $options = [])
 * @method i($text, $options = [])
 * @method p($text, $options = [])
 * @method small($text, $options = [])
 * @method span($text, $options = [])
 *
 * @method label($text, $options = [])
 *
 * @method td($text, $options = [])
 * @method th($text, $options = [])
 * @method tr($text, $options = [])
 *
 * @method ul($text, $options = [])
 * @method li($text, $options = [])
 *
 * @method h1($text, $options = [])
 * @method h2($text, $options = [])
 * @method h3($text, $options = [])
 * @method h4($text, $options = [])
 * @method h5($text, $options = [])
 * @method h6($text, $options = [])
 *
 */
class MaterializeHtmlHelper extends HtmlHelper {

    protected $_minimizedAttributeFormat = '%s';

    public function __construct(View $View, array $settings = []) {

        if (empty($settings['configFile'])) {
            $settings['configFile'] = 'html5_tags';
        }

        parent::__construct($View, $settings);
    }

    public function __call($tag, $params) {

        $string = Hash::get($params, 0, '');
        $options = Hash::get($params, 1, []);

        if (preg_match('/^h(?<n>[1-6])$/', $tag, $matches)) {
            return $this->hn($matches['n'], $string, $options);
        }

        return $this->tag($tag, $string, $options);

    }

    public function hn($n, $string, $options = []) {

        return $this->tag(
            'div',
            $this->tag(
                'div',
                $this->tag(
                    'h' . $n, ($string ?: '&nbsp;'), $options),
                ['class' => 'col s12']
            ),
            ['class' => 'row']
        );

    }

    public function icon($icon, $options = []) {

        # Size, given as ['size' => 'large']
        $size = $this->getOption($options, 'size');

        # Size, given as ['large']
        if ( ! $size) {
            foreach (['tiny', 'small', 'medium', 'large'] as $size_option) {
                if ($this->getOption($options, $size_option)) {
                    $size = $size_option;
                }
            }
        }

        # Add size class
        if ($size) {
            $this->prependClass($options, $size);
        }

        # Add material-icons class
        $this->prependClass($options, 'material-icons');

        # Return <i> tag
        return $this->i($icon, $options);

    }

    /**
     * Create html according to $format using $out
     *
     * @param $format array Format in which order to return $out
     * @param $out    array Html elements to format
     *
     * @return string Formatted html
     */
    public function format($format, $out) {
        if (empty($format)) {
            return null;
        }

        $html = '';
        foreach ($format as $key => $value) {
            if (is_array($value)) {
                # Remove identifier
                if (is_string($key)) {
                    $key = preg_replace('/\[:[^\]]*\]/', '', $key);
                }
                if (strpos($key, '.') !== false) {
                    # Allow tag.class notation
                    list ($tag, $class) = explode('.', $key, 2);
                    if (strpos($class, '.') !== false) {
                        $class = str_replace('.', ' ', $class);
                    }
                    $open = $this->openTag($tag, ['class' => trim($class) ?: false]);
                    $close = $this->closeTag($tag);
                } else {
                    # Use $out data
                    list($open, $close) = $out[$key];
                }
                # Add html
                $html .= $open;
                $html .= $this->format($value, $out);
                $html .= $close;
            } else {
                # Remove identifier
                if (is_string($value)) {
                    $value = preg_replace('/\[:[^\]]*\]/', '', $value);
                }
                $html .= Hash::get($out, $value);
            }
        }

        return $html;
    }

    public function formatRemove(&$format, $identifier) {
        if (is_array($format)) {
            foreach ($format as $key => $value) {

                if (is_string($key) && strpos($key, '[:' . $identifier . ']') !== false) {
                    # Key has identifier
                    unset($format[$key]);
                    continue;
                }

                if (is_string($value) && strpos($value, '[:' . $identifier . ']') !== false) {
                    # Value has identifier
                    unset($format[$key]);
                    continue;
                }

                if (is_array($value)) {
                    $this->formatRemove($format[$key], $identifier);
                }
            }
        }
    }

    /**
     * Create an html open tag
     *
     * @param string $name
     * @param array  $options
     *
     * @return string Open tag html
     */
    public function openTag($name, $options = []) {
        return $this->tag($name, null, $options);
    }

    /**
     * Create an html close tag
     *
     * @param string $name
     *
     * @return string Close tag html
     */
    public function closeTag($name) {
        return '</' . $name . '>';
    }

    public function prependClass(&$options = [], $class = null) {
        $options['class'] = trim($class . ' ' . Hash::get($options, 'class', ''));

        return $options;
    }

    public function appendClass(&$options = [], $class = null) {
        $options['class'] = trim(Hash::get($options, 'class', '') . ' ' . $class);

        return $options;
    }

    public function getOption(&$options, $name, $default = null, $unset = true) {
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

    public function unsetOption(&$options, $names) {
        if ( ! is_array($options)) {
            debug('$options should be an array');

            return false;
        }

        if ( ! is_array($names)) {
            $names = [$names];
        }

        foreach ($names as $name) {
            # Option is array key
            if (array_key_exists($name, $options)) {
                unset($options[$name]);

                continue;
            }

            # Option is array value
            $key = array_search($name, $options, true);
            if ($key !== false) {
                unset($options[$key]);

                continue;
            }
        }

        return $options;
    }

}
