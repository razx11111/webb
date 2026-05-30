<?php

namespace App\Models\CAP_Stuff;

class xml {
    var $file;
    var $break = "\r\n";
    var $tabspace = "\t";

    var $tab = 0;
    var $tab_tree = array();

    function xml($version, $encoding){
        $this->addrow(('<'.'?xml version="'.$version.'" encoding="'.$encoding.'"'.' ?'.'>'));
        $this->addrow(('<'.'?xml-stylesheet type="text/xsl" href="cap_style.xsl" ?'.'>'));
    }

    function tagSimple($tag,$value='', $options=array(), $trimtext=false) {
        if ($trimtext == 1) $value = trim($value); 
        if($value == '' and empty($options)) return '';

        $row = '<'.$tag.$this->opToArray($options);
        if(trim($value) != '') {
            $row .= '>';
            $row.= htmlspecialchars(($value), ENT_QUOTES, "UTF-8");	
            $row.= ('</'.$tag.'>');
        }
        else {
            if($tag == 'summary'){
                $row.= '>';
                $row.= 'No Summary';
                $row.= ('</'.$tag.'>');
            }
            else {
                $row.= ('/>');
            }
        }

        $this->addRow($row);
    }

    function tagOpen($tag, $options = array()) {
        $row = '<'.$tag.$this->opToArray($options).'>';
        $this->addRow($row);
        $this->tab++;
        array_push($this->tab_tree, $tag);
    }

    function tagClose($tag) {
        $c=0;
        if($this->tab > 0) {
            do {
                $ltag = array_pop($this->tab_tree);
                $this->tab--;
                $this->addRow('</'.$ltag.'>');
                $c++;
            } while(($ltag != $tag || ( is_int($tag) && $c < $tag )) && $this->tab > 0  );		
        }
    }

    function addRow($content) {
        $this->file[] = $this->tab().$content.$this->break;
    }

    function tab() {
        if($this->tab==0) return '';
        return implode('', array_fill(0, $this->tab, $this->tabspace));
    }

    function cdata($content) {  
        return '<![CDATA['.$content.']]>';
    }

    function addComment($content) {
        $this->addRow('<!-- '.$content.' -->');
    }

    function addEmptyRow($name, $attributes = array()) {
        $this->addRow('');
    }

    function opToArray($options) {
        if(!is_array($options)) return '';
        $str = '';
        foreach($options as $key => $value) {
            $str .= ' '.$key.'="'.$value.'"';
        }
        return $str;
    }

    function output() {
        return implode($this->file);
    }
}