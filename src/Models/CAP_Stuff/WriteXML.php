<?php

namespace App\Models\CAP_Stuff;

/**
 * A legacy, custom XML writer class.
 * NOTE: This class uses older methods for building XML. For new features,
 * it is recommended to use PHP's built-in XMLWriter class, which is more robust and standard.
 * This class has been updated to use modern PHP syntax.
 */
class WriteXML {
    private $file = [];
    private $break = "\r\n";
    private $tabspace = "\t";
    private $tab = 0;
    private $tab_tree = [];

    public function __construct($version, $encoding){
        $this->addRow(('<'.'?xml version="'.$version.'" encoding="'.$encoding.'"'.' ?'.'>'));
    }

    /**
     * Adds a simple, self-contained XML tag with a value.
     * Example: <tag>value</tag> or <tag/>
     */
    public function tagSimple($tag, $value = '', $options = [], $trimtext = false) {
        if ($trimtext) $value = trim($value);
        if ($value == '' && empty($options)) return;

        $row = '<' . $tag . $this->opToArray($options);
        if (trim($value) != '') {
            $row .= '>';
            $row .= htmlspecialchars($value, ENT_QUOTES, "UTF-8");
            $row .= '</' . $tag . '>';
        } else {
            // A special case for the 'summary' tag, which should not be empty.
            if ($tag == 'summary') {
                $row .= '>';
                $row .= 'No Summary';
                $row .= '</' . $tag . '>';
            } else {
                $row .= '/>';
            }
        }
        $this->addRow($row);
    }

    /**
     * Opens a new XML tag and increases indentation.
     */
    public function tagOpen($tag, $options = []) {
        $row = '<' . $tag . $this->opToArray($options) . '>';
        $this->addRow($row);
        $this->tab++;
        array_push($this->tab_tree, $tag);
    }

    /**
     * Closes one or more open XML tags.
     */
    public function tagClose($tag) {
        $c = 0;
        if ($this->tab > 0) {
            do {
                $ltag = array_pop($this->tab_tree);
                $this->tab--;
                $this->addRow('</' . $ltag . '>');
                $c++;
            } while (($ltag != $tag || (is_int($tag) && $c < $tag)) && $this->tab > 0);
        }
    }

    /**
     * Adds a new line of content to the XML file buffer with correct indentation.
     */
    private function addRow($content) {
        $this->file[] = $this->getTabs() . $content . $this->break;
    }

    /**
     * Gets the current indentation string.
     */
    private function getTabs() {
        if ($this->tab == 0) return '';
        return implode('', array_fill(0, $this->tab, $this->tabspace));
    }

    /**
     * Adds an XML comment.
     */
    public function addComment($content) {
        $this->addRow('<!-- ' . $content . ' -->');
    }

    /**
     * Converts an associative array of options into an XML attribute string.
     */
    private function opToArray($options) {
        if (!is_array($options)) return '';
        $str = '';
        foreach ($options as $key => $value) {
            $str .= ' ' . $key . '="' . $value . '"';
        }
        return $str;
    }

    /**
     * Returns the complete XML document as a string.
     */
    public function output() {
        return implode('', $this->file);
    }
}