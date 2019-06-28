<?php

/*
 * Copyleft 2002 Johann Hanne
 * 
 * From: http://www.bettina-attack.de/jonny/view.php/projects/php_writeexcel/
 * See Documentation on CPAN:
 * http://search.cpan.org/~jmcnamara/Excel-Writer-XLSX-0.50/lib/Excel/Writer/XLSX.pm
 *
 * This is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this software; if not, write to the
 * Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA  02111-1307 USA
 */

/*
 * This is the Spreadsheet::WriteExcel Perl package ported to PHP
 * Spreadsheet::WriteExcel was written by John McNamara, jmcnamara@cpan.org
 */

class writeexcel_biffwriter {
    var $byte_order;
    var $BIFF_version;
    var $_byte_order;
    var $_data;
    var $_datasize;
    var $_limit;
    var $_debug;

    /*
     * Constructor
     */
    function writeexcel_biffwriter() {

        $this->byte_order   = '';
        $this->BIFF_version = 0x0500;
        $this->_byte_order  = '';
        $this->_data        = false;
        $this->_datasize    = 0;
        $this->_limit       = 2080;

        $this->_set_byte_order();
    }

    /*
     * Determine the byte order and store it as class data to avoid
     * recalculating it for each call to new().
     */
    function _set_byte_order() {
        $this->byteorder=0;
        // Check if "pack" gives the required IEEE 64bit float
        $teststr = pack("d", 1.2345);
        $number  = pack("C8", 0x8D, 0x97, 0x6E, 0x12, 0x83, 0xC0, 0xF3, 0x3F);

        if ($number == $teststr) {
            $this->byte_order = 0; // Little Endian
        } elseif ($number == strrev($teststr)) {
            $this->byte_order = 1; // Big Endian
        } else {
            // Give up
            trigger_error("Required floating point format not supported ".
                          "on this platform. See the portability section ".
                          "of the documentation.", E_USER_ERROR);
        }

        $this->_byte_order = $this->byte_order;
    }

    /*
     * General storage function
     */
    function _prepend($data) {

        if (func_num_args()>1) {
            trigger_error("writeexcel_biffwriter::_prepend() ".
                          "called with more than one argument", E_USER_ERROR);
        }

        if ($this->_debug) {
            print "*** writeexcel_biffwriter::_prepend() called:";
            for ($c=0;$c<strlen($data);$c++) {
                if ($c%16==0) {
                    print "\n";
                }
                printf("%02X ", ord($data[$c]));
            }
            print "\n";
        }

        if (strlen($data) > $this->_limit) {
            $data = $this->_add_continue($data);
        }

        $this->_data      = $data . $this->_data;
        $this->_datasize += strlen($data);
    }

    /*
     * General storage function
     */
    function _append($data) {

        if (func_num_args()>1) {
            trigger_error("writeexcel_biffwriter::_append() ".
                          "called with more than one argument", E_USER_ERROR);
        }

        if ($this->_debug) {
            print "*** writeexcel_biffwriter::_append() called:";
            for ($c=0;$c<strlen($data);$c++) {
                if ($c%16==0) {
                    print "\n";
                }
                printf("%02X ", ord($data[$c]));
            }
            print "\n";
        }

        if (strlen($data) > $this->_limit) {
            $data = $this->_add_continue($data);
        }

        $this->_data      = $this->_data . $data;
        $this->_datasize += strlen($data);
    }

    /*
     * Writes Excel BOF record to indicate the beginning of a stream or
     * sub-stream in the BIFF file.
     *
     * $type = 0x0005, Workbook
     * $type = 0x0010, Worksheet
     */
    function _store_bof($type) {

        $record  = 0x0809; // Record identifier
        $length  = 0x0008; // Number of bytes to follow

        $version = $this->BIFF_version;

        // According to the SDK $build and $year should be set to zero.
        // However, this throws a warning in Excel 5. So, use these
        // magic numbers.
        $build  = 0x096C;
        $year   = 0x07C9;

        $header = pack("vv",   $record, $length);
        $data   = pack("vvvv", $version, $type, $build, $year);

        $this->_prepend($header . $data);
    }

    /*
     * Writes Excel EOF record to indicate the end of a BIFF stream.
     */
    function _store_eof() {

        $record = 0x000A; // Record identifier
        $length = 0x0000; // Number of bytes to follow

        $header = pack("vv", $record, $length);

        $this->_append($header);
    }

    /*
     * Excel limits the size of BIFF records. In Excel 5 the limit is 2084
     * bytes. In Excel 97 the limit is 8228 bytes. Records that are longer
     * than these limits must be split up into CONTINUE blocks.
     *
     * This function take a long BIFF record and inserts CONTINUE records as
     * necessary.
     */
    function _add_continue($data) {

        $limit  = $this->_limit;
        $record = 0x003C; // Record identifier

        // The first 2080/8224 bytes remain intact. However, we have to change
        // the length field of the record.
        $tmp = substr($data, 0, $limit);
        $data = substr($data, $limit);
        $tmp = substr($tmp, 0, 2) . pack ("v", $limit-4) . substr($tmp, 4);

        // Strip out chunks of 2080/8224 bytes +4 for the header.
        while (strlen($data) > $limit) {
            $header  = pack("vv", $record, $limit);
            $tmp    .= $header;
            $tmp    .= substr($data, 0, $limit);
            $data    = substr($data, $limit);
        }

        // Mop up the last of the data
        $header  = pack("vv", $record, strlen($data));
        $tmp    .= $header;
        $tmp    .= $data;

        return $tmp;
    }

}


/*
 * This is the Spreadsheet::WriteExcel Perl package ported to PHP
 * Spreadsheet::WriteExcel was written by John McNamara, jmcnamara@cpan.org
 */

class writeexcel_format {

    var $_xf_index;
    var $_font_index;
    var $_font;
    var $_size;
    var $_bold;
    var $_italic;
    var $_color;
    var $_underline;
    var $_font_strikeout;
    var $_font_outline;
    var $_font_shadow;
    var $_font_script;
    var $_font_family;
    var $_font_charset;
    var $_num_format;
    var $_hidden;
    var $_locked;
    var $_text_h_align;
    var $_text_wrap;
    var $_text_v_align;
    var $_text_justlast;
    var $_rotation;
    var $_fg_color;
    var $_bg_color;
    var $_pattern;
    var $_bottom;
    var $_top;
    var $_left;
    var $_right;
    var $_bottom_color;
    var $_top_color;
    var $_left_color;
    var $_right_color;

    /*
     * Constructor
     */
    function writeexcel_format() {
        $_=func_get_args();

        $this->_xf_index       = (sizeof($_)>0) ? array_shift($_) : 0;

        $this->_font_index     = 0;
        $this->_font           = 'Arial';
        $this->_size           = 10;
        $this->_bold           = 0x0190;
        $this->_italic         = 0;
        $this->_color          = 0x7FFF;
        $this->_underline      = 0;
        $this->_font_strikeout = 0;
        $this->_font_outline   = 0;
        $this->_font_shadow    = 0;
        $this->_font_script    = 0;
        $this->_font_family    = 0;
        $this->_font_charset   = 0;

        $this->_num_format     = 0;

        $this->_hidden         = 0;
        $this->_locked         = 1;

        $this->_text_h_align   = 0;
        $this->_text_wrap      = 0;
        $this->_text_v_align   = 2;
        $this->_text_justlast  = 0;
        $this->_rotation       = 0;

        $this->_fg_color       = 0x40;
        $this->_bg_color       = 0x41;

        $this->_pattern        = 0;

        $this->_bottom         = 0;
        $this->_top            = 0;
        $this->_left           = 0;
        $this->_right          = 0;

        $this->_bottom_color   = 0x40;
        $this->_top_color      = 0x40;
        $this->_left_color     = 0x40;
        $this->_right_color    = 0x40;

        // Set properties passed to writeexcel_workbook::addformat()
        if (sizeof($_)>0) {
            call_user_func_array(array(&$this, 'set_properties'), $_);
        }
    }

    /*
     * Copy the attributes of another writeexcel_format object.
     */
    function copy($other) {
        $xf = $this->_xf_index;   // Backup XF index
        foreach ($other as $key->$value) {
                $this->{$key} = $value;
        }
        $this->_xf_index = $xf;   // Restore XF index
    }

    /*
     * Generate an Excel BIFF XF record.
     */
    function get_xf() {

        $_=func_get_args();

        // $record    Record identifier
        // $length    Number of bytes to follow

        // $ifnt      Index to FONT record
        // $ifmt      Index to FORMAT record
        // $style     Style and other options
        // $align     Alignment
        // $icv       fg and bg pattern colors
        // $fill      Fill and border line style
        // $border1   Border line style and color
        // $border2   Border color

        // Set the type of the XF record and some of the attributes.
        if ($_[0] == "style") {
            $style = 0xFFF5;
        } else {
            $style   = $this->_locked;
            $style  |= $this->_hidden << 1;
        }

        // Flags to indicate if attributes have been set.
        $atr_num     = ($this->_num_format != 0) ? 1 : 0;
        $atr_fnt     = ($this->_font_index != 0) ? 1 : 0;
        $atr_alc     =  $this->_text_wrap ? 1 : 0;
        $atr_bdr     = ($this->_bottom   ||
                        $this->_top      ||
                        $this->_left     ||
                        $this->_right) ? 1 : 0;
        $atr_pat     = ($this->_fg_color != 0x41 ||
                        $this->_bg_color != 0x41 ||
                        $this->_pattern  != 0x00) ? 1 : 0;
        $atr_prot    = 0;

        // Reset the default colors for the non-font properties
        if ($this->_fg_color     == 0x7FFF) $this->_fg_color     = 0x40;
        if ($this->_bg_color     == 0x7FFF) $this->_bg_color     = 0x41;
        if ($this->_bottom_color == 0x7FFF) $this->_bottom_color = 0x41;
        if ($this->_top_color    == 0x7FFF) $this->_top_color    = 0x41;
        if ($this->_left_color   == 0x7FFF) $this->_left_color   = 0x41;
        if ($this->_right_color  == 0x7FFF) $this->_right_color  = 0x41;

        // Zero the default border colour if the border has not been set.
        if ($this->_bottom == 0) {
            $this->_bottom_color = 0;
        }
        if ($this->_top    == 0) {
            $this->_top_color    = 0;
        }
        if ($this->_right  == 0) {
            $this->_right_color  = 0;
        }
        if ($this->_left   == 0) {
            $this->_left_color   = 0;
        }

        // The following 2 logical statements take care of special cases in 
        // relation to cell colors and patterns:
        // 1. For a solid fill (_pattern == 1) Excel reverses the role of
        //    foreground and background colors
        // 2. If the user specifies a foreground or background color
        //    without a pattern they probably wanted a solid fill, so we
        //    fill in the defaults.
        if ($this->_pattern <= 0x01 && 
            $this->_bg_color != 0x41 && 
            $this->_fg_color == 0x40 )
        {
            $this->_fg_color = $this->_bg_color;
            $this->_bg_color = 0x40;
            $this->_pattern  = 1;
        }

        if ($this->_pattern <= 0x01 &&
            $this->_bg_color == 0x41 &&
            $this->_fg_color != 0x40 )
        {
            $this->_bg_color = 0x40;
            $this->_pattern  = 1;
        }

        $record         = 0x00E0;
        $length         = 0x0010;

        $ifnt           = $this->_font_index;
        $ifmt           = $this->_num_format;

        $align          = $this->_text_h_align;
        $align         |= $this->_text_wrap     << 3;
        $align         |= $this->_text_v_align  << 4;
        $align         |= $this->_text_justlast << 7;
        $align         |= $this->_rotation      << 8;
        $align         |= $atr_num              << 10;
        $align         |= $atr_fnt              << 11;
        $align         |= $atr_alc              << 12;
        $align         |= $atr_bdr              << 13;
        $align         |= $atr_pat              << 14;
        $align         |= $atr_prot             << 15;

        $icv            = $this->_fg_color;
        $icv           |= $this->_bg_color      << 7;

        $fill           = $this->_pattern;
        $fill          |= $this->_bottom        << 6;
        $fill          |= $this->_bottom_color  << 9;

        $border1        = $this->_top;
        $border1       |= $this->_left          << 3;
        $border1       |= $this->_right         << 6;
        $border1       |= $this->_top_color     << 9;

        $border2        = $this->_left_color;
        $border2       |= $this->_right_color   << 7;

        $header      = pack("vv",       $record, $length);
        $data        = pack("vvvvvvvv", $ifnt, $ifmt, $style, $align,
                                        $icv, $fill,
                                        $border1, $border2);

        return($header . $data);
    }

    /*
     * Generate an Excel BIFF FONT record.
     */
    function get_font() {

        // $record     Record identifier
        // $length     Record length

        // $dyHeight   Height of font (1/20 of a point)
        // $grbit      Font attributes
        // $icv        Index to color palette
        // $bls        Bold style
        // $sss        Superscript/subscript
        // $uls        Underline
        // $bFamily    Font family
        // $bCharSet   Character set
        // $reserved   Reserved
        // $cch        Length of font name
        // $rgch       Font name

        $dyHeight   = $this->_size * 20;
        $icv        = $this->_color;
        $bls        = $this->_bold;
        $sss        = $this->_font_script;
        $uls        = $this->_underline;
        $bFamily    = $this->_font_family;
        $bCharSet   = $this->_font_charset;
        $rgch       = $this->_font;

        $cch        = strlen($rgch);
        $record     = 0x31;
        $length     = 0x0F + $cch;
        $reserved   = 0x00;

        $grbit      = 0x00;

        if ($this->_italic) {
            $grbit     |= 0x02;
        }

        if ($this->_font_strikeout) {
            $grbit     |= 0x08;
        }

        if ($this->_font_outline) {
            $grbit     |= 0x10;
        }

        if ($this->_font_shadow) {
            $grbit     |= 0x20;
        }

        $header  = pack("vv",         $record, $length);
        $data    = pack("vvvvvCCCCC", $dyHeight, $grbit, $icv, $bls,
                                      $sss, $uls, $bFamily,
                                      $bCharSet, $reserved, $cch);

        return($header . $data . $this->_font);
    }

    /*
     * Returns a unique hash key for a font.
     * Used by writeexcel_workbook::_store_all_fonts()
     */
    function get_font_key() {

        # The following elements are arranged to increase the probability of
        # generating a unique key. Elements that hold a large range of numbers
        # eg. _color are placed between two binary elements such as _italic
        #
        $key  = $this->_font.$this->_size.
                $this->_font_script.$this->_underline.
                $this->_font_strikeout.$this->_bold.$this->_font_outline.
                $this->_font_family.$this->_font_charset.
                $this->_font_shadow.$this->_color.$this->_italic;

        $key = preg_replace('/ /', '_', $key); # Convert the key to a single word

        return $key;
    }

    /*
     * Returns the used by Worksheet->_XF()
     */
    function get_xf_index() {
        return $this->_xf_index;
    }

    /*
     * Used in conjunction with the set_xxx_color methods to convert a color
     * string into a number. Color range is 0..63 but we will restrict it
     * to 8..63 to comply with Gnumeric. Colors 0..7 are repeated in 8..15.
     */
    function _get_color($color=false) {

        $colors = array(
                        'aqua'    => 0x0F,
                        'cyan'    => 0x0F,
                        'black'   => 0x08,
                        'blue'    => 0x0C,
                        'brown'   => 0x10,
                        'magenta' => 0x0E,
                        'fuchsia' => 0x0E,
                        'gray'    => 0x17,
                        'grey'    => 0x17,
                        'green'   => 0x11,
                        'lime'    => 0x0B,
                        'navy'    => 0x12,
                        'orange'  => 0x35,
                        'purple'  => 0x14,
                        'red'     => 0x0A,
                        'silver'  => 0x16,
                        'white'   => 0x09,
                        'yellow'  => 0x0D
                       );

        // Return the default color, 0x7FFF, if undef,
        if ($color===false) {
            return 0x7FFF;
        }

        // or the color string converted to an integer,
        if (isset($colors[strtolower($color)])) {
            return $colors[strtolower($color)];
        }

        // or the default color if string is unrecognised,
        if (preg_match('/\D/', $color)) {
            return 0x7FFF;
        }

        // or an index < 8 mapped into the correct range,
        if ($color<8) {
            return $color + 8;
        }

        // or the default color if arg is outside range,
        if ($color>63) {
            return 0x7FFF;
        }

        // or an integer in the valid range
        return $color;
    }

    /*
     * Set cell alignment.
     */
    function set_align($location) {

        // Ignore numbers
        if (preg_match('/\d/', $location)) {
            return;
        }

        $location = strtolower($location);

        switch ($location) {

        case 'left':
            $this->set_text_h_align(1);
            break;

        case 'centre':
        case 'center':
            $this->set_text_h_align(2);
            break;

        case 'right':
            $this->set_text_h_align(3);
            break;

        case 'fill':
            $this->set_text_h_align(4);
            break;

        case 'justify':
            $this->set_text_h_align(5);
            break;

        case 'merge':
            $this->set_text_h_align(6);
            break;

        case 'equal_space':
            $this->set_text_h_align(7);
            break;

        case 'top':
            $this->set_text_v_align(0);
            break;

        case 'vcentre':
        case 'vcenter':
            $this->set_text_v_align(1);
            break;
            break;

        case 'bottom':
            $this->set_text_v_align(2);
            break;

        case 'vjustify':
            $this->set_text_v_align(3);
            break;

        case 'vequal_space':
            $this->set_text_v_align(4);
            break;
        }
    }

    /*
     * Set vertical cell alignment. This is required by the set_properties()
     * method to differentiate between the vertical and horizontal properties.
     */
    function set_valign($location) {
        $this->set_align($location);
    }

    /*
     * This is an alias for the unintuitive set_align('merge')
     */
    function set_merge() {
        $this->set_text_h_align(6);
    }

    /*
     * Bold has a range 0x64..0x3E8.
     * 0x190 is normal. 0x2BC is bold.
     */
    function set_bold($weight=1) {

        if ($weight == 1) {
            // Bold text
            $weight = 0x2BC;
        }

        if ($weight == 0) {
            // Normal text
            $weight = 0x190;
        }

        if ($weight < 0x064) {
            // Lower bound
            $weight = 0x190;
        }

        if ($weight > 0x3E8) {
            // Upper bound
            $weight = 0x190;
        }

        $this->_bold = $weight;
    }

    /*
     * Set all cell borders (bottom, top, left, right) to the same style
     */
    function set_border($style) {
        $this->set_bottom($style);
        $this->set_top($style);
        $this->set_left($style);
        $this->set_right($style);
    }

    /*
     * Set all cell borders (bottom, top, left, right) to the same color
     */
    function set_border_color($color) {
        $this->set_bottom_color($color);
        $this->set_top_color($color);
        $this->set_left_color($color);
        $this->set_right_color($color);
    }

    /*
     * Convert hashes of properties to method calls.
     */
    function set_properties() {

        $_=func_get_args();

        $properties=array();
        foreach($_ as $props) {
            if (is_array($props)) {
                $properties=array_merge($properties, $props);
            } else {
                $properties[]=$props;
            }
        }

        foreach ($properties as $key=>$value) {

            // Strip leading "-" from Tk style properties eg. -color => 'red'.
            $key = preg_replace('/^-/', '', $key);

            /* Make sure method names are alphanumeric characters only, in
               case tainted data is passed to the eval(). */
            if (preg_match('/\W/', $key)) {
                trigger_error("Unknown property: $key.",
                              E_USER_ERROR);
            }

            /* Evaling all $values as a strings gets around the problem of
               some numerical format strings being evaluated as numbers, for
               example "00000" for a zip code. */
            if (is_int($key)) {
                eval("\$this->set_$value();");
            } else {
                eval("\$this->set_$key('$value');");
            }

        }
    }

    function set_font($font) {
        $this->_font=$font;
    }

    function set_size($size) {
        $this->_size=$size;
    }

    function set_italic($italic=1) {
        $this->_italic=$italic;
    }

    function set_color($color) {
        $this->_color=$this->_get_color($color);
    }

    function set_underline($underline=1) {
        $this->_underline=$underline;
    }

    function set_font_strikeout($font_strikeout=1) {
        $this->_font_strikeout=$font_strikeout;
    }

    function set_font_outline($font_outline=1) {
        $this->_font_outline=$font_outline;
    }

    function set_font_shadow($font_shadow=1) {
        $this->_font_shadow=$font_shadow;
    }

    function set_font_script($font_script=1) {
        $this->_font_script=$font_script;
    }

    /* Undocumented */
    function set_font_family($font_family=1) {
        $this->_font_family=$font_family;
    }

    /* Undocumented */
    function set_font_charset($font_charset=1) {
        $this->_font_charset=$font_charset;
    }

    function set_num_format($num_format=1) {
        $this->_num_format=$num_format;
    }

    function set_hidden($hidden=1) {
        $this->_hidden=$hidden;
    }

    function set_locked($locked=1) {
        $this->_locked=$locked;
    }

    function set_text_h_align($align) {
        $this->_text_h_align=$align;
    }

    function set_text_wrap($wrap=1) {
        $this->_text_wrap=$wrap;
    }

    function set_text_v_align($align) {
        $this->_text_v_align=$align;
    }

    function set_text_justlast($text_justlast=1) {
        $this->_text_justlast=$text_justlast;
    }

    function set_rotation($rotation=1) {
        $this->_rotation=$rotation;
    }

    function set_fg_color($color) {
        $this->_fg_color=$this->_get_color($color);
    }

    function set_bg_color($color) {
        $this->_bg_color=$this->_get_color($color);
    }

    function set_pattern($pattern=1) {
        $this->_pattern=$pattern;
    }

    function set_bottom($bottom=1) {
        $this->_bottom=$bottom;
    }

    function set_top($top=1) {
        $this->_top=$top;
    }

    function set_left($left=1) {
        $this->_left=$left;
    }

    function set_right($right=1) {
         $this->_right=$right;
    }

    function set_bottom_color($color) {
        $this->_bottom_color=$this->_get_color($color);
    }

    function set_top_color($color) {
        $this->_top_color=$this->_get_color($color);
    }

    function set_left_color($color) {
        $this->_left_color=$this->_get_color($color);
    }

    function set_right_color($color) {
        $this->_right_color=$this->_get_color($color);
    }

}


/* This file contains source from the PEAR::Spreadsheet class Parser.php file version 0.4 .
   The raiseError was replaced by triggerError function.
   The PEAR::isError was imported to keep compatibility to PEAR::Spreadsheet class 
   
   Imported and adapted by Andreas Brodowski 2003 (andreas.brodowski@oscar-gmbh.com).
   
   There should be no license rights in question because the Parser.php from PEAR class is 
   published under GNU License the same way like this class.
   
   Changes:	03/08/27 Added SPREADSHEET_EXCEL_WRITER_SCOLON for arg seperation in excel functions
 */

/*
 * This is the Spreadsheet::WriteExcel Perl package ported to PHP
 * Spreadsheet::WriteExcel was written by John McNamara, jmcnamara@cpan.org
 */

define('SPREADSHEET_EXCEL_WRITER_ADD',"+");
    // @const SPREADSHEET_EXCEL_WRITER_ADD token identifier for character "+"
define('SPREADSHEET_EXCEL_WRITER_SUB',"-");
    // @const SPREADSHEET_EXCEL_WRITER_SUB token identifier for character "-"
define('SPREADSHEET_EXCEL_WRITER_MUL',"*");
    // @const SPREADSHEET_EXCEL_WRITER_MUL token identifier for character "*"
define('SPREADSHEET_EXCEL_WRITER_DIV',"/");
    // @const SPREADSHEET_EXCEL_WRITER_DIV token identifier for character "/"
define('SPREADSHEET_EXCEL_WRITER_OPEN',"(");
   // @const SPREADSHEET_EXCEL_WRITER_OPEN token identifier for character "("
define('SPREADSHEET_EXCEL_WRITER_CLOSE',")"); 
 // @const SPREADSHEET_EXCEL_WRITER_CLOSE token identifier for character ")"
define('SPREADSHEET_EXCEL_WRITER_COMA',",");
   // @const SPREADSHEET_EXCEL_WRITER_COMA token identifier for character ","
define('SPREADSHEET_EXCEL_WRITER_SCOLON',";"); 
// @const SPREADSHEET_EXCEL_WRITER_SCOLON token identifier for character ";"
define('SPREADSHEET_EXCEL_WRITER_GT',">");
     // @const SPREADSHEET_EXCEL_WRITER_GT token identifier for character ">"
define('SPREADSHEET_EXCEL_WRITER_LT',"<");
     // @const SPREADSHEET_EXCEL_WRITER_LT token identifier for character "<"
define('SPREADSHEET_EXCEL_WRITER_LE',"<=");
    // @const SPREADSHEET_EXCEL_WRITER_LE token identifier for character "<="
define('SPREADSHEET_EXCEL_WRITER_GE',">=");
    // @const SPREADSHEET_EXCEL_WRITER_GE token identifier for character ">="
define('SPREADSHEET_EXCEL_WRITER_EQ',"=");
     // @const SPREADSHEET_EXCEL_WRITER_EQ token identifier for character "="
define('SPREADSHEET_EXCEL_WRITER_NE',"<>");
    // @const SPREADSHEET_EXCEL_WRITER_NE token identifier for character "<>"


class writeexcel_formula {

###############################################################################
#
# Class data.
#
var $parser;
var $ptg;
var $_functions;
var $_current_char;
var $_current_token;
var $_lookahead;
var $_debug;
var $_byte_order;
var $_volatile;
var $_workbook;
var $_ext_sheets;
var $_formula;

###############################################################################
#
# new()
#
# Constructor
#
function writeexcel_formula($byte_order) {

    $this->parser          = false;
    $this->ptg             = array();
    $this->_functions       = array();
    $this->_debug          = 0;
    $this->_byte_order     = $byte_order;
    $this->_volatile       = 0;
    $this->_workbook       = "";
    $this->_ext_sheets     = array();
    $this->_current_token  = '';
    $this->_lookahead	   = '';
    $this->_current_char   = 0;    
    $this->_formula	   = '';
}

###############################################################################
#
# _init_parser()
#
# There is a small overhead involved in generating the parser. Therefore, the
# initialisation is delayed until a formula is required. TODO: use a pre-
# compiled header.
#
function _init_parser() {

    $this->_initializeHashes();


    if ($this->_debug) {
        print "Init_parser.\n\n";
    }
}

###############################################################################
#
# parse_formula()
#
# This is the only public method. It takes a textual description of a formula
# and returns a RPN encoded byte string.
#
function parse_formula() {

    $_=func_get_args();

    # Initialise the parser if this is the first call
    if ($this->parser===false) {
        $this->_init_parser();
    }

    $formula = array_shift($_);
    //$str;
    //$tokens;

    if ($this->_debug) {
        print "$formula\n";
    }

    # Build the parse tree for the formula
    
    $this->_formula	 = $formula;
    $this->_current_char = 0;
    $this->_lookahead    = $this->_formula{1};
    $this->_advance($formula);
    $parsetree = $this->_condition();

    $str = $this->toReversePolish($parsetree);

    return $str;
}

function isError($data) {
    return (bool)(is_object($data) &&
                  (get_class($data) == 'pear_error' ||
                  is_subclass_of($data, 'pear_error')));
}

/**
* Class for parsing Excel formulas
*
* @author   Xavier Noguer <xnoguer@rezebra.com>
* @category FileFormats
* @package  Spreadsheet_Excel_Writer
*/

    
/**
* Initialize the ptg and function hashes. 
*
* @access private
*/
function _initializeHashes()
 {
    // The Excel ptg indices
    $this->ptg = array(
        'ptgExp'       => 0x01,
        'ptgTbl'       => 0x02,
        'ptgAdd'       => 0x03,
        'ptgSub'       => 0x04,
        'ptgMul'       => 0x05,
        'ptgDiv'       => 0x06,
        'ptgPower'     => 0x07,        'ptgConcat'    => 0x08,
        'ptgLT'        => 0x09,
        'ptgLE'        => 0x0A,
        'ptgEQ'        => 0x0B,
        'ptgGE'        => 0x0C,
        'ptgGT'        => 0x0D,
        'ptgNE'        => 0x0E,
        'ptgIsect'     => 0x0F,
        'ptgUnion'     => 0x10,
        'ptgRange'     => 0x11,
        'ptgUplus'     => 0x12,
        'ptgUminus'    => 0x13,
        'ptgPercent'   => 0x14,
        'ptgParen'     => 0x15,
        'ptgMissArg'   => 0x16,
        'ptgStr'       => 0x17,
        'ptgAttr'      => 0x19,
        'ptgSheet'     => 0x1A,
        'ptgEndSheet'  => 0x1B,
        'ptgErr'       => 0x1C,
        'ptgBool'      => 0x1D,
        'ptgInt'       => 0x1E,
        'ptgNum'       => 0x1F,
        'ptgArray'     => 0x20,
        'ptgFunc'      => 0x21,
        'ptgFuncVar'   => 0x22,
        'ptgName'      => 0x23,
        'ptgRef'       => 0x24,
        'ptgArea'      => 0x25,
        'ptgMemArea'   => 0x26,
        'ptgMemErr'    => 0x27,
        'ptgMemNoMem'  => 0x28,
        'ptgMemFunc'   => 0x29,
	'ptgRefErr'    => 0x2A,
        'ptgAreaErr'   => 0x2B,
        'ptgRefN'      => 0x2C,
        'ptgAreaN'     => 0x2D,
        'ptgMemAreaN'  => 0x2E,
        'ptgMemNoMemN' => 0x2F,
        'ptgNameX'     => 0x39,
        'ptgRef3d'     => 0x3A,

        'ptgArea3d'    => 0x3B,
        'ptgRefErr3d'  => 0x3C,
        'ptgAreaErr3d' => 0x3D,
        'ptgArrayV'    => 0x40,
        'ptgFuncV'     => 0x41,
        'ptgFuncVarV'  => 0x42,
        'ptgNameV'     => 0x43,
        'ptgRefV'      => 0x44,
        'ptgAreaV'     => 0x45,
        'ptgMemAreaV'  => 0x46,
        'ptgMemErrV'   => 0x47,
        'ptgMemNoMemV' => 0x48,
        'ptgMemFuncV'  => 0x49,
        'ptgRefErrV'   => 0x4A,
        'ptgAreaErrV'  => 0x4B,
        'ptgRefNV'     => 0x4C,
        'ptgAreaNV'    => 0x4D,
        'ptgMemAreaNV' => 0x4E,
        'ptgMemNoMemN' => 0x4F,
        'ptgFuncCEV'   => 0x58,
        'ptgNameXV'    => 0x59,
        'ptgRef3dV'    => 0x5A,
        'ptgArea3dV'   => 0x5B,        'ptgRefErr3dV' => 0x5C,
        'ptgAreaErr3d' => 0x5D,
        'ptgArrayA'    => 0x60,
        'ptgFuncA'     => 0x61,
        'ptgFuncVarA'  => 0x62,
        'ptgNameA'     => 0x63,        'ptgRefA'      => 0x64,
	      'ptgAreaA'     => 0x65,
        'ptgMemAreaA'  => 0x66,
        'ptgMemErrA'   => 0x67,
        'ptgMemNoMemA' => 0x68,
        'ptgMemFuncA'  => 0x69,
        'ptgRefErrA'   => 0x6A,
        'ptgAreaErrA'  => 0x6B,
        'ptgRefNA'     => 0x6C,
        'ptgAreaNA'    => 0x6D,
        'ptgMemAreaNA' => 0x6E,
        'ptgMemNoMemN' => 0x6F,
        'ptgFuncCEA'   => 0x78,
        'ptgNameXA'    => 0x79,
        'ptgRef3dA'    => 0x7A,
        'ptgArea3dA'   => 0x7B,
        'ptgRefErr3dA' => 0x7C,
        'ptgAreaErr3d' => 0x7D
        );
    
    // Thanks to Michael Meeks and Gnumeric for the initial arg values.
    //
    // The following hash was generated by "function_locale.pl" in the distro.
    // Refer to function_locale.pl for non-English function names.
    //
    // The array elements are as follow:
    // ptg:   The Excel function ptg code.
    // args:  The number of arguments that the function takes:
    //           >=0 is a fixed number of arguments.
    //           -1  is a variable  number of arguments.
    // class: The reference, value or array class of the function args.
    // vol:   The function is volatile.
    //
    $this->_functions = array(
	// function                  ptg  args  class  vol
	'COUNT'           => array(   0,   -1,    0,    0 ),
        'IF'              => array(   1,   -1,    1,    0 ),
        'ISNA'            => array(   2,    1,    1,    0 ),
        'ISERROR'         => array(   3,    1,    1,    0 ),
        'SUM'             => array(   4,   -1,    0,    0 ),
        'AVERAGE'         => array(   5,   -1,    0,    0 ),
        'MIN'             => array(   6,   -1,    0,    0 ),
        'MAX'             => array(   7,   -1,    0,    0 ),
        'ROW'             => array(   8,   -1,    0,    0 ),
        'COLUMN'          => array(   9,   -1,    0,    0 ),
        'NA'              => array(  10,    0,    0,    0 ),
        'NPV'             => array(  11,   -1,    1,    0 ),
        'STDEV'           => array(  12,   -1,    0,    0 ),
        'DOLLAR'          => array(  13,   -1,    1,    0 ),
        'FIXED'           => array(  14,   -1,    1,    0 ),
        'SIN'             => array(  15,    1,    1,    0 ),
        'COS'             => array(  16,    1,    1,    0 ),
        'TAN'             => array(  17,    1,    1,    0 ),
        'ATAN'            => array(  18,    1,    1,    0 ),
        'PI'              => array(  19,    0,    1,    0 ),
        'SQRT'            => array(  20,    1,    1,    0 ),
        'EXP'             => array(  21,    1,    1,    0 ),
        'LN'              => array(  22,    1,    1,    0 ),
        'LOG10'           => array(  23,    1,    1,    0 ),
        'ABS'             => array(  24,    1,    1,    0 ),
        'INT'             => array(  25,    1,    1,    0 ),
        'SIGN'            => array(  26,    1,    1,    0 ),
        'ROUND'           => array(  27,    2,    1,    0 ),
        'LOOKUP'          => array(  28,   -1,    0,    0 ),
        'INDEX'           => array(  29,   -1,    0,    1 ),
        'REPT'            => array(  30,    2,    1,    0 ),
        'MID'             => array(  31,    3,    1,    0 ),
        'LEN'             => array(  32,    1,    1,    0 ),
        'VALUE'           => array(  33,    1,    1,    0 ),
        'TRUE'            => array(  34,    0,    1,    0 ),
        'FALSE'           => array(  35,    0,    1,    0 ),
        'AND'             => array(  36,   -1,    0,    0 ),
        'OR'              => array(  37,   -1,    0,    0 ),
        'NOT'             => array(  38,    1,    1,    0 ),
        'MOD'             => array(  39,    2,    1,    0 ),
        'DCOUNT'          => array(  40,    3,    0,    0 ),
        'DSUM'            => array(  41,    3,    0,    0 ),
        'DAVERAGE'        => array(  42,    3,    0,    0 ),
        'DMIN'            => array(  43,    3,    0,    0 ),
        'DMAX'            => array(  44,    3,    0,    0 ),
        'DSTDEV'          => array(  45,    3,    0,    0 ),
        'VAR'             => array(  46,   -1,    0,    0 ),
        'DVAR'            => array(  47,    3,    0,    0 ),
        'TEXT'            => array(  48,    2,    1,    0 ),
        'LINEST'          => array(  49,   -1,    0,    0 ),
        'TREND'           => array(  50,   -1,    0,    0 ),
        'LOGEST'          => array(  51,   -1,    0,    0 ),
        'GROWTH'          => array(  52,   -1,    0,    0 ),
        'PV'              => array(  56,   -1,    1,    0 ),
        'FV'              => array(  57,   -1,    1,    0 ),
        'NPER'            => array(  58,   -1,    1,    0 ),
        'PMT'             => array(  59,   -1,    1,    0 ),
        'RATE'            => array(  60,   -1,    1,    0 ),
        'MIRR'            => array(  61,    3,    0,    0 ),
        'IRR'             => array(  62,   -1,    0,    0 ),
        'RAND'            => array(  63,    0,    1,    1 ),
        'MATCH'           => array(  64,   -1,    0,    0 ),
        'DATE'            => array(  65,    3,    1,    0 ),
        'TIME'            => array(  66,    3,    1,    0 ),
        'DAY'             => array(  67,    1,    1,    0 ),
        'MONTH'           => array(  68,    1,    1,    0 ),
        'YEAR'            => array(  69,    1,    1,    0 ),
        'WEEKDAY'         => array(  70,   -1,    1,    0 ),
        'HOUR'            => array(  71,    1,    1,    0 ),
        'MINUTE'          => array(  72,    1,    1,    0 ),
        'SECOND'          => array(  73,    1,    1,    0 ),
        'NOW'             => array(  74,    0,    1,    1 ),
        'AREAS'           => array(  75,    1,    0,    1 ),
        'ROWS'            => array(  76,    1,    0,    1 ),
        'COLUMNS'         => array(  77,    1,    0,    1 ),
        'OFFSET'          => array(  78,   -1,    0,    1 ),
        'SEARCH'          => array(  82,   -1,    1,    0 ),
        'TRANSPOSE'       => array(  83,    1,    1,    0 ),
        'TYPE'            => array(  86,    1,    1,    0 ),
        'ATAN2'           => array(  97,    2,    1,    0 ),
        'ASIN'            => array(  98,    1,    1,    0 ),
        'ACOS'            => array(  99,    1,    1,    0 ),
        'CHOOSE'          => array( 100,   -1,    1,    0 ),
        'HLOOKUP'         => array( 101,   -1,    0,    0 ),
        'VLOOKUP'         => array( 102,   -1,    0,    0 ),
        'ISREF'           => array( 105,    1,    0,    0 ),
        'LOG'             => array( 109,   -1,    1,    0 ),
        'CHAR'            => array( 111,    1,    1,    0 ),
        'LOWER'           => array( 112,    1,    1,    0 ),
        'UPPER'           => array( 113,    1,    1,    0 ),
        'PROPER'          => array( 114,    1,    1,    0 ),
        'LEFT'            => array( 115,   -1,    1,    0 ),
        'RIGHT'           => array( 116,   -1,    1,    0 ),
        'EXACT'           => array( 117,    2,    1,    0 ),
        'TRIM'            => array( 118,    1,    1,    0 ),
        'REPLACE'         => array( 119,    4,    1,    0 ),
        'SUBSTITUTE'      => array( 120,   -1,    1,    0 ),
        'CODE'            => array( 121,    1,    1,    0 ),
        'FIND'            => array( 124,   -1,    1,    0 ),
        'CELL'            => array( 125,   -1,    0,    1 ),
        'ISERR'           => array( 126,    1,    1,    0 ),
        'ISTEXT'          => array( 127,    1,    1,    0 ),
        'ISNUMBER'        => array( 128,    1,    1,    0 ),
        'ISBLANK'         => array( 129,    1,    1,    0 ),
        'T'               => array( 130,    1,    0,    0 ),
        'N'               => array( 131,    1,    0,    0 ),
        'DATEVALUE'       => array( 140,    1,    1,    0 ),
        'TIMEVALUE'       => array( 141,    1,    1,    0 ),
        'SLN'             => array( 142,    3,    1,    0 ),
        'SYD'             => array( 143,    4,    1,    0 ),
        'DDB'             => array( 144,   -1,    1,    0 ),
        'INDIRECT'        => array( 148,   -1,    1,    1 ),
        'CALL'            => array( 150,   -1,    1,    0 ),
        'CLEAN'           => array( 162,    1,    1,    0 ),
        'MDETERM'         => array( 163,    1,    2,    0 ),
        'MINVERSE'        => array( 164,    1,    2,    0 ),
        'MMULT'           => array( 165,    2,    2,    0 ),
        'IPMT'            => array( 167,   -1,    1,    0 ),
        'PPMT'            => array( 168,   -1,    1,    0 ),
        'COUNTA'          => array( 169,   -1,    0,    0 ),
        'PRODUCT'         => array( 183,   -1,    0,    0 ),
        'FACT'            => array( 184,    1,    1,    0 ),
        'DPRODUCT'        => array( 189,    3,    0,    0 ),
        'ISNONTEXT'       => array( 190,    1,    1,    0 ),
        'STDEVP'          => array( 193,   -1,    0,    0 ),
        'VARP'            => array( 194,   -1,    0,    0 ),
        'DSTDEVP'         => array( 195,    3,    0,    0 ),
        'DVARP'           => array( 196,    3,    0,    0 ),
        'TRUNC'           => array( 197,   -1,    1,    0 ),
        'ISLOGICAL'       => array( 198,    1,    1,    0 ),
        'DCOUNTA'         => array( 199,    3,    0,    0 ),
        'ROUNDUP'         => array( 212,    2,    1,    0 ),
        'ROUNDDOWN'       => array( 213,    2,    1,    0 ),
        'RANK'            => array( 216,   -1,    0,    0 ),
        'ADDRESS'         => array( 219,   -1,    1,    0 ),
        'DAYS360'         => array( 220,   -1,    1,    0 ),
        'TODAY'           => array( 221,    0,    1,    1 ),
        'VDB'             => array( 222,   -1,    1,    0 ),
        'MEDIAN'          => array( 227,   -1,    0,    0 ),
        'SUMPRODUCT'      => array( 228,   -1,    2,    0 ),
        'SINH'            => array( 229,    1,    1,    0 ),
        'COSH'            => array( 230,    1,    1,    0 ),
        'TANH'            => array( 231,    1,    1,    0 ),
        'ASINH'           => array( 232,    1,    1,    0 ),
        'ACOSH'           => array( 233,    1,    1,    0 ),
        'ATANH'           => array( 234,    1,    1,    0 ),
        'DGET'            => array( 235,    3,    0,    0 ),
        'INFO'            => array( 244,    1,    1,    1 ),
        'DB'              => array( 247,   -1,    1,    0 ),
        'FREQUENCY'       => array( 252,    2,    0,    0 ),
        'ERROR.TYPE'      => array( 261,    1,    1,    0 ),
        'REGISTER.ID'     => array( 267,   -1,    1,    0 ),
        'AVEDEV'          => array( 269,   -1,    0,    0 ),
        'BETADIST'        => array( 270,   -1,    1,    0 ),
        'GAMMALN'         => array( 271,    1,    1,    0 ),
        'BETAINV'         => array( 272,   -1,    1,    0 ),
        'BINOMDIST'       => array( 273,    4,    1,    0 ),
        'CHIDIST'         => array( 274,    2,    1,    0 ),
        'CHIINV'          => array( 275,    2,    1,    0 ),
        'COMBIN'          => array( 276,    2,    1,    0 ),
        'CONFIDENCE'      => array( 277,    3,    1,    0 ),
        'CRITBINOM'       => array( 278,    3,    1,    0 ),
        'EVEN'            => array( 279,    1,    1,    0 ),
        'EXPONDIST'       => array( 280,    3,    1,    0 ),
        'FDIST'           => array( 281,    3,    1,    0 ),
        'FINV'            => array( 282,    3,    1,    0 ),
        'FISHER'          => array( 283,    1,    1,    0 ),
        'FISHERINV'       => array( 284,    1,    1,    0 ),
        'FLOOR'           => array( 285,    2,    1,    0 ),
        'GAMMADIST'       => array( 286,    4,    1,    0 ),
        'GAMMAINV'        => array( 287,    3,    1,    0 ),
        'CEILING'         => array( 288,    2,    1,    0 ),
        'HYPGEOMDIST'     => array( 289,    4,    1,    0 ),
        'LOGNORMDIST'     => array( 290,    3,    1,    0 ),
        'LOGINV'          => array( 291,    3,    1,    0 ),
        'NEGBINOMDIST'    => array( 292,    3,    1,    0 ),
        'NORMDIST'        => array( 293,    4,    1,    0 ),
        'NORMSDIST'       => array( 294,    1,    1,    0 ),
        'NORMINV'         => array( 295,    3,    1,    0 ),
        'NORMSINV'        => array( 296,    1,    1,    0 ),
        'STANDARDIZE'     => array( 297,    3,    1,    0 ),
        'ODD'             => array( 298,    1,    1,    0 ),
        'PERMUT'          => array( 299,    2,    1,    0 ),
        'POISSON'         => array( 300,    3,    1,    0 ),
        'TDIST'           => array( 301,    3,    1,    0 ),
        'WEIBULL'         => array( 302,    4,    1,    0 ),
        'SUMXMY2'         => array( 303,    2,    2,    0 ),
        'SUMX2MY2'        => array( 304,    2,    2,    0 ),
        'SUMX2PY2'        => array( 305,    2,    2,    0 ),
        'CHITEST'         => array( 306,    2,    2,    0 ),
        'CORREL'          => array( 307,    2,    2,    0 ),
        'COVAR'           => array( 308,    2,    2,    0 ),
        'FORECAST'        => array( 309,    3,    2,    0 ),
        'FTEST'           => array( 310,    2,    2,    0 ),
        'INTERCEPT'       => array( 311,    2,    2,    0 ),
        'PEARSON'         => array( 312,    2,    2,    0 ),
        'RSQ'             => array( 313,    2,    2,    0 ),
        'STEYX'           => array( 314,    2,    2,    0 ),
        'SLOPE'           => array( 315,    2,    2,    0 ),
        'TTEST'           => array( 316,    4,    2,    0 ),
        'PROB'            => array( 317,   -1,    2,    0 ),
        'DEVSQ'           => array( 318,   -1,    0,    0 ),
        'GEOMEAN'         => array( 319,   -1,    0,    0 ),
        'HARMEAN'         => array( 320,   -1,    0,    0 ),
        'SUMSQ'           => array( 321,   -1,    0,    0 ),
        'KURT'            => array( 322,   -1,    0,    0 ),
        'SKEW'            => array( 323,   -1,    0,    0 ),
        'ZTEST'           => array( 324,   -1,    0,    0 ),
        'LARGE'           => array( 325,    2,    0,    0 ),
        'SMALL'           => array( 326,    2,    0,    0 ),
        'QUARTILE'        => array( 327,    2,    0,    0 ),
        'PERCENTILE'      => array( 328,    2,    0,    0 ),
        'PERCENTRANK'     => array( 329,   -1,    0,    0 ),
        'MODE'            => array( 330,   -1,    2,    0 ),
        'TRIMMEAN'        => array( 331,    2,    0,    0 ),
        'TINV'            => array( 332,    2,    1,    0 ),
        'CONCATENATE'     => array( 336,   -1,    1,    0 ),
        'POWER'           => array( 337,    2,    1,    0 ),
        'RADIANS'         => array( 342,    1,    1,    0 ),
        'DEGREES'         => array( 343,    1,    1,    0 ),
        'SUBTOTAL'        => array( 344,   -1,    0,    0 ),
        'SUMIF'           => array( 345,   -1,    0,    0 ),
        'COUNTIF'         => array( 346,    2,    0,    0 ),
        'COUNTBLANK'      => array( 347,    1,    0,    0 ),
        'ROMAN'           => array( 354,   -1,    1,    0 )
        );
}
    
/**
* Convert a token to the proper ptg value.
*
* @access private
* @param mixed $token The token to convert.
* @return mixed the converted token on success. PEAR_Error if the token
*               is not recognized
*/
function _convert($token)
 {
    if (preg_match('/^"[^"]{0,255}"$/', $token))
 {
        return $this->_convertString($token);
    }
 elseif (is_numeric($token))
 {
        return $this->_convertNumber($token);
    }
    // match references like A1 or $A$1
    
elseif (preg_match('/^\$?([A-Ia-i]?[A-Za-z])\$?(\d+)$/',$token))
 { 
        return $this->_convertRef2d($token);
    }
    // match external references like Sheet1:Sheet2!A1
    elseif (preg_match("/^[A-Za-z0-9_]+(\:[A-Za-z0-9_]+)?\![A-Ia-i]?[A-Za-z](\d+)$/",$token))
 {
 
        return $this->_convertRef3d($token);
    }
    // match ranges like A1:B2
    elseif (preg_match('/^\$?[A-Ia-i]?[A-Za-z]\$?\d+\:\$?[A-Ia-i]?[A-Za-z]\$?\d+$/',$token))
 {
        return $this->_convertRange2d($token);
    }
    // match ranges like A1..B2
    elseif (preg_match('/^\$?[A-Ia-i]?[A-Za-z]\$?\d+\.\.\$?[A-Ia-i]?[A-Za-z]\$?\d+$/',$token))
 {
        return $this->_convertRange2d($token);
    }
    // match external ranges like Sheet1:Sheet2!A1:B2
    elseif (preg_match("/^[A-Za-z0-9_]+(\:[A-Za-z0-9_]+)?\!([A-Ia-i]?[A-Za-z])?(\d+)\:([A-Ia-i]?[A-Za-z])?(\d+)$/",$token))
 {
        return $this->_convertRange3d($token);
    }
    // match external ranges like 'Sheet1:Sheet2'!A1:B2
    elseif (preg_match("/^'[A-Za-z0-9_ ]+(\:[A-Za-z0-9_ ]+)?'\!([A-Ia-i]?[A-Za-z])?(\d+)\:([A-Ia-i]?[A-Za-z])?(\d+)$/",$token))
 {
        return $this->_convertRange3d($token);
    }
    elseif (isset($this->ptg[$token])) // operators (including parentheses)
 {
        return pack("C", $this->ptg[$token]);
    }
    // commented so argument number can be processed correctly. See toReversePolish().
    /*elseif (preg_match("/[A-Z0-9\xc0-\xdc\.]+/",$token))
    {
        return($this->_convertFunction($token,$this->_func_args));
    }*/
    // if it's an argument, ignore the token (the argument remains)
    elseif ($token == 'arg')
 {
        return '';
    }
    // TODO: use real error codes
    trigger_error("Unknown token $token", E_USER_ERROR);
}
    
/**
* Convert a number token to ptgInt or ptgNum
*
* @access private
* @param mixed $num an integer or double for conversion to its ptg value
*/
function _convertNumber($num)
 {

    // Integer in the range 0..2**16-1

    if ((preg_match("/^\d+$/",$num)) and ($num <= 65535)) {
        return(pack("Cv", $this->ptg['ptgInt'], $num));
    }
 else { // A float
        if ($this->_byte_order) { // if it's Big Endian
            $num = strrev($num);
        }
        return pack("Cd", $this->ptg['ptgNum'], $num);
    }
}
    
/**
* Convert a string token to ptgStr
*
* @access private
* @param string $string A string for conversion to its ptg value
*/
function _convertString($string)
 {
    // chop away beggining and ending quotes
    $string = substr($string, 1, strlen($string) - 2);
    return pack("CC", $this->ptg['ptgStr'], strlen($string)).$string;
}

/**
* Convert a function to a ptgFunc or ptgFuncVarV depending on the number of
* args that it takes.
*
* @access private
* @param string  $token    The name of the function for convertion to ptg value.
* @param integer $num_args The number of arguments the function receives.
* @return string The packed ptg for the function
*/
function _convertFunction($token, $num_args)
 {
    $args     = $this->_functions[$token][1];
    $volatile = $this->_functions[$token][3];
    
    // Fixed number of args eg. TIME($i,$j,$k).
    if ($args >= 0) {
        return pack("Cv", $this->ptg['ptgFuncV'], $this->_functions[$token][0]);
    }
    // Variable number of args eg. SUM($i,$j,$k, ..).
    if ($args == -1) {
        return pack("CCv", $this->ptg['ptgFuncVarV'], $num_args, $this->_functions[$token][0]);
    }
}
    
/**
* Convert an Excel range such as A1:D4 to a ptgRefV.
*
* @access private
* @param string $range An Excel range in the A1:A2 or A1..A2 format.
*/
function _convertRange2d($range)
 {
    $class = 2; // as far as I know, this is magick.
    
    // Split the range into 2 cell refs
    if (preg_match('/^\$?([A-Ia-i]?[A-Za-z])\$?(\d+)\:\$?([A-Ia-i]?[A-Za-z])\$?(\d+)$/',$range)) {
        list($cell1, $cell2) = explode(':', $range);
    }
 elseif (preg_match('/^\$?([A-Ia-i]?[A-Za-z])\$?(\d+)\.\.\$?([A-Ia-i]?[A-Za-z])\$?(\d+)$/',$range)) {
        list($cell1, $cell2) = explode('\.\.', $range);
    }
 else {
        // TODO: use real error codes
        trigger_error("Unknown range separator", E_USER_ERROR);
    }
    
    // Convert the cell references
    $cell_array1 = $this->_cellToPackedRowcol($cell1);
    if ($this->isError($cell_array1)) {
        return $cell_array1;
    }
    list($row1, $col1) = $cell_array1;
    $cell_array2 = $this->_cellToPackedRowcol($cell2);
    if ($this->isError($cell_array2)) {
        return $cell_array2;
    }
    list($row2, $col2) = $cell_array2;
    
    // The ptg value depends on the class of the ptg.
    if ($class == 0) {
        $ptgArea = pack("C", $this->ptg['ptgArea']);
    }
 elseif ($class == 1) {
        $ptgArea = pack("C", $this->ptg['ptgAreaV']);
    }
 elseif ($class == 2) {
        $ptgArea = pack("C", $this->ptg['ptgAreaA']);
    }
 else {
        // TODO: use real error codes
        trigger_error("Unknown class $class", E_USER_ERROR);
    }
    return $ptgArea . $row1 . $row2 . $col1. $col2;
}
 
/**
* Convert an Excel 3d range such as "Sheet1!A1:D4" or "Sheet1:Sheet2!A1:D4" to
* a ptgArea3dV.
*
* @access private
* @param string $token An Excel range in the Sheet1!A1:A2 format.
*/
function _convertRange3d($token)
 {
    $class = 2; // as far as I know, this is magick.

    // Split the ref at the ! symbol
    list($ext_ref, $range) = explode('!', $token);

    // Convert the external reference part
    $ext_ref = $this->_packExtRef($ext_ref);
    if ($this->isError($ext_ref)) {
        return $ext_ref;
    }

    // Split the range into 2 cell refs
    list($cell1, $cell2) = explode(':', $range);

    // Convert the cell references
    if (preg_match('/^(\$)?[A-Ia-i]?[A-Za-z](\$)?(\d+)$/', $cell1))
 {
        $cell_array1 = $this->_cellToPackedRowcol($cell1);
        if ($this->isError($cell_array1)) {
            return $cell_array1;
        }
	list($row1, $col1) = $cell_array1;
        $cell_array2 = $this->_cellToPackedRowcol($cell2);
        if ($this->isError($cell_array2)) {
	    return $cell_array2;
        }
        list($row2, $col2) = $cell_array2;
    }
 else { // It's a columns range (like 26:27)
	$cells_array = $this->_rangeToPackedRange($cell1.':'.$cell2);
	if ($this->isError($cells_array)) {
    	    return $cells_array;
        }
	list($row1, $col1, $row2, $col2) = $cells_array;
    }
 
    // The ptg value depends on the class of the ptg.
    if ($class == 0) {
        $ptgArea = pack("C", $this->ptg['ptgArea3d']);
    }
 elseif ($class == 1) {
        $ptgArea = pack("C", $this->ptg['ptgArea3dV']);
    }
 elseif ($class == 2) {
        $ptgArea = pack("C", $this->ptg['ptgArea3dA']);
    }
 else {
        trigger_error("Unknown class $class", E_USER_ERROR);
    }
 
    return $ptgArea . $ext_ref . $row1 . $row2 . $col1. $col2;
}

/**
* Convert an Excel reference such as A1, $B2, C$3 or $D$4 to a ptgRefV.
*
* @access private
* @param string $cell An Excel cell reference
* @return string The cell in packed() format with the corresponding ptg
*/
function _convertRef2d($cell)
 {
    $class = 2; // as far as I know, this is magick.
    
    // Convert the cell reference
    $cell_array = $this->_cellToPackedRowcol($cell);
    if ($this->isError($cell_array)) {
        return $cell_array;
    }
    list($row, $col) = $cell_array;

    // The ptg value depends on the class of the ptg.
    if ($class == 0) {
        $ptgRef = pack("C", $this->ptg['ptgRef']);
    }
 elseif ($class == 1) {
        $ptgRef = pack("C", $this->ptg['ptgRefV']);
    }
 elseif ($class == 2) {
        $ptgRef = pack("C", $this->ptg['ptgRefA']);
    }
 else {
        // TODO: use real error codes
        trigger_error("Unknown class $class",E_USER_ERROR);
    }
    return $ptgRef.$row.$col;
}
    
/**
* Convert an Excel 3d reference such as "Sheet1!A1" or "Sheet1:Sheet2!A1" to a
* ptgRef3dV.
*
* @access private
* @param string $cell An Excel cell reference
* @return string The cell in packed() format with the corresponding ptg
*/
function _convertRef3d($cell)
 {
    $class = 2; // as far as I know, this is magick.
 
    // Split the ref at the ! symbol
    list($ext_ref, $cell) = explode('!', $cell);
 
    // Convert the external reference part
    $ext_ref = $this->_packExtRef($ext_ref);
    if ($this->isError($ext_ref)) {
        return $ext_ref;
    }
 
    // Convert the cell reference part
    list($row, $col) = $this->_cellToPackedRowcol($cell);
 
    // The ptg value depends on the class of the ptg.
    if ($class == 0) {
        $ptgRef = pack("C", $this->ptg['ptgRef3d']);
    } elseif ($class == 1) {
        $ptgRef = pack("C", $this->ptg['ptgRef3dV']);
    } elseif ($class == 2) {
        $ptgRef = pack("C", $this->ptg['ptgRef3dA']);
    }
 else {
        trigger_error("Unknown class $class", E_USER_ERROR);
    }

    return $ptgRef . $ext_ref. $row . $col;
}

/**
* Convert the sheet name part of an external reference, for example "Sheet1" or
* "Sheet1:Sheet2", to a packed structure.
*
* @access private
* @param string $ext_ref The name of the external reference
* @return string The reference index in packed() format
*/
function _packExtRef($ext_ref) {
    $ext_ref = preg_replace("/^'/", '', $ext_ref); // Remove leading  ' if any.
    $ext_ref = preg_replace("/'$/", '', $ext_ref); // Remove trailing ' if any.

    // Check if there is a sheet range eg., Sheet1:Sheet2.
    if (preg_match("/:/", $ext_ref))
 {
        list($sheet_name1, $sheet_name2) = explode(':', $ext_ref);

        $sheet1 = $this->_getSheetIndex($sheet_name1);
        if ($sheet1 == -1) {
            trigger_error("Unknown sheet name $sheet_name1 in formula",E_USER_ERROR);
        }
        $sheet2 = $this->_getSheetIndex($sheet_name2);
        if ($sheet2 == -1) {
            trigger_error("Unknown sheet name $sheet_name2 in formula",E_USER_ERROR);
        }

        // Reverse max and min sheet numbers if necessary
        if ($sheet1 > $sheet2) {
            list($sheet1, $sheet2) = array($sheet2, $sheet1);
        }
    }
 else { // Single sheet name only.
        $sheet1 = $this->_getSheetIndex($ext_ref);
        if ($sheet1 == -1) {
            trigger_error("Unknown sheet name $ext_ref in formula",E_USER_ERROR);
        }
        $sheet2 = $sheet1;
    }
 
    // References are stored relative to 0xFFFF.
    $offset = -1 - $sheet1;

    return pack('vdvv', $offset, 0x00, $sheet1, $sheet2);
}

/**
* Look up the index that corresponds to an external sheet name. The hash of
* sheet names is updated by the addworksheet() method of the 
* Spreadsheet_Excel_Writer_Workbook class.
*
* @access private
* @return integer
*/
function _getSheetIndex($sheet_name)
 {
    if (!isset($this->_ext_sheets[$sheet_name])) {
        return -1;
    }
 else {
        return $this->_ext_sheets[$sheet_name];
    }
}

/**
* This method is used to update the array of sheet names. It is
* called by the addWorksheet() method of the Spreadsheet_Excel_Writer_Workbook class.
*
* @access private
* @param string  $name  The name of the worksheet being added
* @param integer $index The index of the worksheet being added
*/
function set_ext_sheet($name, $index)
 {
    $this->_ext_sheets[$name] = $index;
}

/**
* pack() row and column into the required 3 byte format.
*
* @access private
* @param string $cell The Excel cell reference to be packed
* @return array Array containing the row and column in packed() format
*/
function _cellToPackedRowcol($cell)
 {
    $cell = strtoupper($cell);
    list($row, $col, $row_rel, $col_rel) = $this->_cellToRowcol($cell);
    if ($col >= 256) {
        trigger_error("Column in: $cell greater than 255", E_USER_ERROR);
    }
    if ($row >= 16384) {
        trigger_error("Row in: $cell greater than 16384 ", E_USER_ERROR);
    }

    // Set the high bits to indicate if row or col are relative.
    $row    |= $col_rel << 14;
    $row    |= $row_rel << 15;

    $row     = pack('v', $row);
    $col     = pack('C', $col);

    return array($row, $col);
}
    
/**
* pack() row range into the required 3 byte format.
* Just using maximun col/rows, which is probably not the correct solution
*
* @access private
* @param string $range The Excel range to be packed
* @return array Array containing (row1,col1,row2,col2) in packed() format
*/
function _rangeToPackedRange($range)
 {
    preg_match('/(\$)?(\d+)\:(\$)?(\d+)/', $range, $match);
    // return absolute rows if there is a $ in the ref
    $row1_rel = empty($match[1]) ? 1 : 0;
    $row1     = $match[2];
    $row2_rel = empty($match[3]) ? 1 : 0;
    $row2     = $match[4];
    // Convert 1-index to zero-index
    $row1--;
    $row2--;
    // Trick poor inocent Excel
    $col1 = 0;
    $col2 = 16383; // maximum possible value for Excel 5 (change this!!!)

    //list($row, $col, $row_rel, $col_rel) = $this->_cellToRowcol($cell);
    if (($row1 >= 16384) or ($row2 >= 16384)) {
        trigger_error("Row in: $range greater than 16384 ",E_USER_ERROR);
    }

    // Set the high bits to indicate if rows are relative.
    $row1    |= $row1_rel << 14;
    $row2    |= $row2_rel << 15;

    $row1     = pack('v', $row1);
    $row2     = pack('v', $row2);
    $col1     = pack('C', $col1);
    $col2     = pack('C', $col2);

    return array($row1, $col1, $row2, $col2);
}

/**
* Convert an Excel cell reference such as A1 or $B2 or C$3 or $D$4 to a zero
* indexed row and column number. Also returns two (0,1) values to indicate
* whether the row or column are relative references.
*
* @access private
* @param string $cell The Excel cell reference in A1 format.
* @return array
*/
function _cellToRowcol($cell)
 {
    preg_match('/(\$)?([A-I]?[A-Z])(\$)?(\d+)/',$cell,$match);
    // return absolute column if there is a $ in the ref
    $col_rel = empty($match[1]) ? 1 : 0;
    $col_ref = $match[2];
    $row_rel = empty($match[3]) ? 1 : 0;
    $row     = $match[4];
    
    // Convert base26 column string to a number.
    $expn   = strlen($col_ref) - 1;
    $col    = 0;
    for ($i=0; $i < strlen($col_ref); $i++)
 {
        $col += (ord($col_ref{$i}) - ord('A') + 1) * pow(26, $expn);
        $expn--;
    }
    
    // Convert 1-index to zero-index
    $row--;
    $col--;
    
    return array($row, $col, $row_rel, $col_rel);
}
    
/**
* Advance to the next valid token.
*
* @access private
*/
function _advance()
 {
    $i = $this->_current_char;
    // eat up white spaces
    if ($i < strlen($this->_formula))
 {
        while ($this->_formula{$i} == " ") {
            $i++;
        }
        if ($i < strlen($this->_formula) - 1) {
            $this->_lookahead = $this->_formula{$i+1};
        }
        $token = "";
    }
    while ($i < strlen($this->_formula))
 {
        $token .= $this->_formula{$i};
        if ($i < strlen($this->_formula) - 1) {
            $this->_lookahead = $this->_formula{$i+1};
        }
 else {
            $this->_lookahead = '';
        }
        if ($this->_match($token) != '')
 {
            //if ($i < strlen($this->_formula) - 1) {
            //    $this->_lookahead = $this->_formula{$i+1};
            //}
            $this->_current_char = $i + 1;
            $this->_current_token = $token;
            return 1;
        }
        if ($i < strlen($this->_formula) - 2) {
            $this->_lookahead = $this->_formula{$i+2};
        }
 else {
        // if we run out of characters _lookahead becomes empty
            $this->_lookahead = '';
        }
        $i++;
    }
    //die("Lexical error ".$this->_current_char);
}
    
/**
* Checks if it's a valid token.
*
* @access private
* @param mixed $token The token to check.
* @return mixed       The checked token or false on failure
*/
function _match($token)
 {
    switch($token)
 {
        case SPREADSHEET_EXCEL_WRITER_ADD:
            return($token);
            break;
        case SPREADSHEET_EXCEL_WRITER_SUB:
            return($token);
            break;
        case SPREADSHEET_EXCEL_WRITER_MUL:
            return($token);
            break;
        case SPREADSHEET_EXCEL_WRITER_DIV:
            return($token);
            break;
        case SPREADSHEET_EXCEL_WRITER_OPEN:
            return($token);
            break;
        case SPREADSHEET_EXCEL_WRITER_CLOSE:
            return($token);
            break;
        case SPREADSHEET_EXCEL_WRITER_SCOLON:
            return($token);
            break;
        case SPREADSHEET_EXCEL_WRITER_COMA:
            return($token);
            break;
        case SPREADSHEET_EXCEL_WRITER_GT:
            if ($this->_lookahead == '=') { // it's a GE token
                break;
            }
            return($token);
            break;
        case SPREADSHEET_EXCEL_WRITER_LT:
            // it's a LE or a NE token
            if (($this->_lookahead == '=') or ($this->_lookahead == '>')) {
                break;
            }
            return($token);
            break;
        case SPREADSHEET_EXCEL_WRITER_GE:
            return($token);
            break;
        case SPREADSHEET_EXCEL_WRITER_LE:
            return($token);
            break;
        case SPREADSHEET_EXCEL_WRITER_EQ:
            return($token);
            break;
        case SPREADSHEET_EXCEL_WRITER_NE:
            return($token);
            break;
        default:
            // if it's a reference
            if (preg_match('/^\$?[A-Ia-i]?[A-Za-z]\$?[0-9]+$/',$token) and
               !preg_match("/[0-9]/",$this->_lookahead) and 
               ($this->_lookahead != ':') and ($this->_lookahead != '.') and
               ($this->_lookahead != '!'))
 {
                return $token;
            }
            // If it's an external reference (Sheet1!A1 or Sheet1:Sheet2!A1)
            elseif (preg_match("/^[A-Za-z0-9_]+(\:[A-Za-z0-9_]+)?\![A-Ia-i]?[A-Za-z][0-9]+$/",$token) and
                   !preg_match("/[0-9]/",$this->_lookahead) and
                   ($this->_lookahead != ':') and ($this->_lookahead != '.'))
 {
                return $token;
            }
            // if it's a range (A1:A2)
            elseif (preg_match("/^(\$)?[A-Ia-i]?[A-Za-z](\$)?[0-9]+:(\$)?[A-Ia-i]?[A-Za-z](\$)?[0-9]+$/",$token) and 
                   !preg_match("/[0-9]/",$this->_lookahead))
 {
                return $token;
            }
            // if it's a range (A1..A2)
            elseif (preg_match("/^(\$)?[A-Ia-i]?[A-Za-z](\$)?[0-9]+\.\.(\$)?[A-Ia-i]?[A-Za-z](\$)?[0-9]+$/",$token) and 
                   !preg_match("/[0-9]/",$this->_lookahead))
 {
                return $token;
            }
            // If it's an external range like Sheet1:Sheet2!A1:B2
            elseif (preg_match("/^[A-Za-z0-9_]+(\:[A-Za-z0-9_]+)?\!([A-Ia-i]?[A-Za-z])?[0-9]+:([A-Ia-i]?[A-Za-z])?[0-9]+$/",$token) and
                   !preg_match("/[0-9]/",$this->_lookahead))
 {
                return $token;
            }
	    // If it's an external range like 'Sheet1:Sheet2'!A1:B2
            elseif (preg_match("/^'[A-Za-z0-9_ ]+(\:[A-Za-z0-9_ ]+)?'\!([A-Ia-i]?[A-Za-z])?[0-9]+:([A-Ia-i]?[A-Za-z])?[0-9]+$/",$token) and
                   !preg_match("/[0-9]/",$this->_lookahead))
 {
                return $token;
            }
            // If it's a number (check that it's not a sheet name or range)
            elseif (is_numeric($token) and 
                    (!is_numeric($token.$this->_lookahead) or ($this->_lookahead == '')) and
                    ($this->_lookahead != '!') and ($this->_lookahead != ':'))
 {
                return $token;
            }
            // If it's a string (of maximum 255 characters)
            elseif (preg_match("/^\"[^\"]{0,255}\"$/",$token))
 {
                return $token;
            }
            // if it's a function call
            elseif (preg_match("/^[A-Z0-9\xc0-\xdc\.]+$/i",$token) and ($this->_lookahead == "(")) {
                return $token;
            }
            return '';
    }
}
    
/**
* The parsing method. It parses a formula.
*
* @access public
* @param string $formula The formula to parse, without the initial equal sign (=).
*/
function parse($formula)
 {
    $this->_current_char = 0;
    $this->_formula      = $formula;
    $this->_lookahead    = $formula{1};
    $this->_advance();
    $this->_parse_tree   = $this->_condition();
    if ($this->isError($this->_parse_tree)) {
        return $this->_parse_tree;
    }
}
    
/**
* It parses a condition. It assumes the following rule:
* Cond -> Expr [(">" | "<") Expr]
*
* @access private
* @return mixed The parsed ptg'd tree
*/
function _condition()
 {
    $result = $this->_expression();
    if ($this->isError($result)) {
        return $result;
    }
    if ($this->_current_token == SPREADSHEET_EXCEL_WRITER_LT)
 {
        $this->_advance();
        $result2 = $this->_expression();
        if ($this->isError($result2)) {
            return $result2;
        }
        $result = $this->_createTree('ptgLT', $result, $result2);
    }
 elseif ($this->_current_token == SPREADSHEET_EXCEL_WRITER_GT) 
{
        $this->_advance();
        $result2 = $this->_expression();
        if ($this->isError($result2)) {
            return $result2;
        }
        $result = $this->_createTree('ptgGT', $result, $result2);
    }
 elseif ($this->_current_token == SPREADSHEET_EXCEL_WRITER_LE) 
{
        $this->_advance();
        $result2 = $this->_expression();
        if ($this->isError($result2)) {
            return $result2;
        }
        $result = $this->_createTree('ptgLE', $result, $result2);
    }
 elseif ($this->_current_token == SPREADSHEET_EXCEL_WRITER_GE) 
{
        $this->_advance();
        $result2 = $this->_expression();
        if ($this->isError($result2)) {
            return $result2;
        }
        $result = $this->_createTree('ptgGE', $result, $result2);
    }
 elseif ($this->_current_token == SPREADSHEET_EXCEL_WRITER_EQ) 
{
        $this->_advance();
        $result2 = $this->_expression();
        if ($this->isError($result2)) {
            return $result2;
        }
        $result = $this->_createTree('ptgEQ', $result, $result2);
    }
 elseif ($this->_current_token == SPREADSHEET_EXCEL_WRITER_NE) 
{
        $this->_advance();
        $result2 = $this->_expression();
        if ($this->isError($result2)) {
            return $result2;
        }
        $result = $this->_createTree('ptgNE', $result, $result2);
    }
    return $result;
}

/**
* It parses a expression. It assumes the following rule:
* Expr -> Term [("+" | "-") Term]
*
* @access private
* @return mixed The parsed ptg'd tree
*/
function _expression()
 {
    // If it's a string return a string node
    if (preg_match("/^\"[^\"]{0,255}\"$/", $this->_current_token))
 {
        $result = $this->_createTree($this->_current_token, '', '');
        $this->_advance();
        return $result;
    }
    $result = $this->_term();
    if ($this->isError($result)) {
        return $result;
    }
    while (($this->_current_token == SPREADSHEET_EXCEL_WRITER_ADD) or 
           ($this->_current_token == SPREADSHEET_EXCEL_WRITER_SUB))
 {
        if ($this->_current_token == SPREADSHEET_EXCEL_WRITER_ADD)
 
{
            $this->_advance();
            $result2 = $this->_term();
            if ($this->isError($result2)) {
                return $result2;
            }
            $result = $this->_createTree('ptgAdd', $result, $result2);
        }
 else 
{
            $this->_advance();
            $result2 = $this->_term();
            if ($this->isError($result2)) {
                return $result2;
            }
            $result = $this->_createTree('ptgSub', $result, $result2);
        }
    }
    return $result;
}
    
/**
* This function just introduces a ptgParen element in the tree, so that Excel
* doesn't get confused when working with a parenthesized formula afterwards.
*
* @access private
* @see _fact()
* @return mixed The parsed ptg'd tree
*/
function _parenthesizedExpression()
 {
    $result = $this->_createTree('ptgParen', $this->_expression(), '');
    return $result;
}
    
/**
* It parses a term. It assumes the following rule:
* Term -> Fact [("*" | "/") Fact]
*
* @access private
* @return mixed The parsed ptg'd tree
*/
function _term()
 {
    $result = $this->_fact();
    if ($this->isError($result)) {
        return $result;
    }
    while (($this->_current_token == SPREADSHEET_EXCEL_WRITER_MUL) or 
           ($this->_current_token == SPREADSHEET_EXCEL_WRITER_DIV)) {
        if ($this->_current_token == SPREADSHEET_EXCEL_WRITER_MUL)
 
{
            $this->_advance();
            $result2 = $this->_fact();
            if ($this->isError($result2)) {
                return $result2;
            }
            $result = $this->_createTree('ptgMul', $result, $result2);
        }
 else 
{
            $this->_advance();
            $result2 = $this->_fact();
            if ($this->isError($result2)) {
                return $result2;
            }
            $result = $this->_createTree('ptgDiv', $result, $result2);
        }
    }
    return $result;
}
    
/**
* It parses a factor. It assumes the following rule:
* Fact -> ( Expr )
*       | CellRef
*       | CellRange
*       | Number
*       | Function
*
* @access private
* @return mixed The parsed ptg'd tree
*/
function _fact()
 {
    if ($this->_current_token == SPREADSHEET_EXCEL_WRITER_OPEN)
 {
        $this->_advance();         // eat the "("
        $result = $this->_parenthesizedExpression();
        if ($this->_current_token != SPREADSHEET_EXCEL_WRITER_CLOSE) {
            trigger_error("')' token expected.",E_USER_ERROR);
        }
        $this->_advance();         // eat the ")"
        return $result;
    }
 if (preg_match('/^\$?[A-Ia-i]?[A-Za-z]\$?[0-9]+$/',$this->_current_token))
 {
    // if it's a reference
        $result = $this->_createTree($this->_current_token, '', '');
        $this->_advance();
        return $result;
    }
 elseif (preg_match("/^[A-Za-z0-9_]+(\:[A-Za-z0-9_]+)?\![A-Ia-i]?[A-Za-z][0-9]+$/",$this->_current_token))
 {
    // If it's an external reference (Sheet1!A1 or Sheet1:Sheet2!A1)
        $result = $this->_createTree($this->_current_token, '', '');
        $this->_advance();
        return $result;
    }
 elseif (preg_match("/^(\$)?[A-Ia-i]?[A-Za-z](\$)?[0-9]+:(\$)?[A-Ia-i]?[A-Za-z](\$)?[0-9]+$/",$this->_current_token) or 
              preg_match("/^(\$)?[A-Ia-i]?[A-Za-z](\$)?[0-9]+\.\.(\$)?[A-Ia-i]?[A-Za-z](\$)?[0-9]+$/",$this->_current_token))
 {
    // if it's a range
        $result = $this->_current_token;
        $this->_advance();
        return $result;
    }
 elseif (preg_match("/^[A-Za-z0-9_]+(\:[A-Za-z0-9_]+)?\!([A-Ia-i]?[A-Za-z])?[0-9]+:([A-Ia-i]?[A-Za-z])?[0-9]+$/",$this->_current_token))
 {
    // If it's an external range (Sheet1!A1:B2)
        $result = $this->_current_token;
        $this->_advance();
        return $result;
    }
 elseif (preg_match("/^'[A-Za-z0-9_ ]+(\:[A-Za-z0-9_ ]+)?'\!([A-Ia-i]?[A-Za-z])?[0-9]+:([A-Ia-i]?[A-Za-z])?[0-9]+$/",$this->_current_token))
 {
    // If it's an external range ('Sheet1'!A1:B2)
        $result = $this->_current_token;
        $this->_advance();
        return $result;
    }
 elseif (is_numeric($this->_current_token))
 {
        $result = $this->_createTree($this->_current_token, '', '');
        $this->_advance();
        return $result;
    }
 elseif (preg_match("/^[A-Z0-9\xc0-\xdc\.]+$/i",$this->_current_token))
 {
    // if it's a function call
        $result = $this->_func();
        return $result;
    }
    trigger_error("Sintactic error: ".$this->_current_token.", lookahead: ".
                          $this->_lookahead.", current char: ".$this->_current_char, E_USER_ERROR);
}
    
/**
* It parses a function call. It assumes the following rule:
* Func -> ( Expr [,Expr]* )
*
* @access private
*/
function _func()
 {
    $num_args = 0; // number of arguments received
    $function = $this->_current_token;
    $this->_advance();
    $this->_advance();         // eat the "("
    while ($this->_current_token != ')')
 {
        if ($num_args > 0)
 {
            if ($this->_current_token == SPREADSHEET_EXCEL_WRITER_COMA ||
		$this->_current_token == SPREADSHEET_EXCEL_WRITER_SCOLON) {
                $this->_advance();  // eat the ","
            }
 else {
                trigger_error("Sintactic error: coma expected in ".
                                  "function $function, {$num_args}º arg", E_USER_ERROR);
            }
            $result2 = $this->_condition();
            if ($this->isError($result2)) {
                return $result2;
            }
            $result = $this->_createTree('arg', $result, $result2);
        }
 else { // first argument
            $result2 = $this->_condition();
            if ($this->isError($result2)) {
                return $result2;
            }
            $result = $this->_createTree('arg', '', $result2);
        }
        $num_args++;
    }
    $args = $this->_functions[$function][1];
    // If fixed number of args eg. TIME($i,$j,$k). Check that the number of args is valid.
    if (($args >= 0) and ($args != $num_args)) {
        trigger_error("Incorrect number of arguments in function $function() ",E_USER_ERROR);
    }

    $result = $this->_createTree($function, $result, $num_args);
    $this->_advance();         // eat the ")"
    return $result;
}
    
/**
* Creates a tree. In fact an array which may have one or two arrays (sub-trees)
* as elements.
*
* @access private
* @param mixed $value The value of this node.
* @param mixed $left  The left array (sub-tree) or a final node.
* @param mixed $right The right array (sub-tree) or a final node.
*/
function _createTree($value, $left, $right)
 {
    return(array('value' => $value, 'left' => $left, 'right' => $right));
}
    
/**
* Builds a string containing the tree in reverse polish notation (What you 
* would use in a HP calculator stack).
* The following tree:
* 
*    +
*   / \
*  2   3
*
* produces: "23+"
*
* The following tree:
*
*    +
*   / \
*  3   *
*     / \
*    6   A1
*
* produces: "36A1*+"
*
* In fact all operands, functions, references, etc... are written as ptg's
*
* @access public
* @param array $tree The optional tree to convert.
* @return string The tree in reverse polish notation
*/
function toReversePolish($tree = array())
 {
    $polish = ""; // the string we are going to return
    if (empty($tree)) { // If it's the first call use _parse_tree
        $tree = $this->_parse_tree;
    }
    if (is_array($tree['left']))
 {
        $converted_tree = $this->toReversePolish($tree['left']);
        if ($this->isError($converted_tree)) {
            return $converted_tree;
        }
        $polish .= $converted_tree;
    }
 elseif ($tree['left'] != '') { // It's a final node
        $converted_tree = $this->_convert($tree['left']);
        if ($this->isError($converted_tree)) {
            return $converted_tree;
        }
        $polish .= $converted_tree;
    }
    if (is_array($tree['right']))
 {
        $converted_tree = $this->toReversePolish($tree['right']);
        if ($this->isError($converted_tree)) {
            return $converted_tree;
        }
        $polish .= $converted_tree;
    }
 elseif ($tree['right'] != '') { // It's a final node
        $converted_tree = $this->_convert($tree['right']);
        if ($this->isError($converted_tree)) {
            return $converted_tree;
        }
        $polish .= $converted_tree;
    }
    // if it's a function convert it here (so we can set it's arguments)
    if (preg_match("/^[A-Z0-9\xc0-\xdc\.]+$/",$tree['value']) and
        !preg_match('/^([A-Ia-i]?[A-Za-z])(\d+)$/',$tree['value']) and
        !preg_match("/^[A-Ia-i]?[A-Za-z](\d+)\.\.[A-Ia-i]?[A-Za-z](\d+)$/",$tree['value']) and
        !is_numeric($tree['value']) and
        !isset($this->ptg[$tree['value']]))
 {
        // left subtree for a function is always an array.
        if ($tree['left'] != '') {
            $left_tree = $this->toReversePolish($tree['left']);
        }
 else {
            $left_tree = '';
        }
        if ($this->isError($left_tree)) {
            return $left_tree;
        }
        // add it's left subtree and return.
        return $left_tree.$this->_convertFunction($tree['value'], $tree['right']);
    }
 else
 {
        $converted_tree = $this->_convert($tree['value']);
        if ($this->isError($converted_tree)) {
            return $converted_tree;
        }
    }
    $polish .= $converted_tree;
    return $polish;
}

}


/*
 * This is the Spreadsheet::WriteExcel Perl package ported to PHP
 * Spreadsheet::WriteExcel was written by John McNamara, jmcnamara@cpan.org
 */

class writeexcel_olewriter {
    var $_OLEfilename;
    var $_OLEtmpfilename; /* ABR */
    var $_filehandle;
    var $_fileclosed;
    var $_internal_fh;
    var $_biff_only;
    var $_size_allowed;
    var $_biffsize;
    var $_booksize;
    var $_big_blocks;
    var $_list_blocks;
    var $_root_start;
    var $_block_count;

    /*
     * Constructor
     */
    function writeexcel_olewriter($filename) {

        $this->_OLEfilename  = $filename;
        $this->_filehandle   = false;
        $this->_fileclosed   = 0;
        $this->_internal_fh  = 0;
        $this->_biff_only    = 0;
        $this->_size_allowed = 0;
        $this->_biffsize     = 0;
        $this->_booksize     = 0;
        $this->_big_blocks   = 0;
        $this->_list_blocks  = 0;
        $this->_root_start   = 0;
        $this->_block_count  = 4;

        $this->_initialize();
    }

    /*
     * Check for a valid filename and store the filehandle.
     */
    function _initialize() {
        $OLEfile = $this->_OLEfilename;

        /* Check for a filename. Workbook.pm will catch this first. */
        if ($OLEfile == '') {
            trigger_error("Filename required", E_USER_ERROR);
        }

        /*
         * If the filename is a resource it is assumed that it is a valid
         * filehandle, if not we create a filehandle.
         */
        if (is_resource($OLEfile)) {
            $fh = $OLEfile;
        } else {
            // Create a new file, open for writing
            $fh = fopen($OLEfile, "wb");
            // The workbook class also checks this but something may have
            // happened since then.
            if (!$fh) {
                trigger_error("Can't open $OLEfile. It may be in use or ".
                              "protected", E_USER_ERROR);
            }

            $this->_internal_fh = 1;
        }

        // Store filehandle
        $this->_filehandle = $fh;
    }

    /*
     * Set the size of the data to be written to the OLE stream
     *
     * $big_blocks = (109 depot block x (128 -1 marker word)
     *               - (1 x end words)) = 13842
     * $maxsize    = $big_blocks * 512 bytes = 7087104
     */
    function set_size($size) {
        $maxsize = 7087104;

        if ($size > $maxsize) {
            trigger_error("Maximum file size, $maxsize, exceeded. To create ".
                          "files bigger than this limit please use the ".
                          "workbookbig class.", E_USER_ERROR);
            return ($this->_size_allowed = 0);
        }

        $this->_biffsize = $size;

        // Set the min file size to 4k to avoid having to use small blocks
        if ($size > 4096) {
            $this->_booksize = $size;
        } else {
            $this->_booksize = 4096;
        }

        return ($this->_size_allowed = 1);
    }

    /*
     * Calculate various sizes needed for the OLE stream
     */
    function _calculate_sizes() {
        $datasize = $this->_booksize;

        if ($datasize % 512 == 0) {
            $this->_big_blocks = $datasize/512;
        } else {
            $this->_big_blocks = floor($datasize/512)+1;
        }
        // There are 127 list blocks and 1 marker blocks for each big block
        // depot + 1 end of chain block
        $this->_list_blocks = floor(($this->_big_blocks)/127)+1;
        $this->_root_start  = $this->_big_blocks;

        //print $this->_biffsize.    "\n";
        //print $this->_big_blocks.  "\n";
        //print $this->_list_blocks. "\n";
    }

    /*
     * Write root entry, big block list and close the filehandle.
     * This method must be called so that the file contents are
     * actually written.
     */
    function close() {

        if (!$this->_size_allowed) {
            return;
        }

        if (!$this->_biff_only) {
            $this->_write_padding();
            $this->_write_property_storage();
            $this->_write_big_block_depot();
        }

        // Close the filehandle if it was created internally.
        if ($this->_internal_fh) {
            fclose($this->_filehandle);
        }
/* ABR */
        if ($this->_OLEtmpfilename != '') {
            $fh = fopen($this->_OLEtmpfilename, "rb");
            if ($fh == false) {
                trigger_error("Can't read temporary file.", E_USER_ERROR);
            }
            fpassthru($fh);
            fclose($fh);
            unlink($this->_OLEtmpfilename);
        };

        $this->_fileclosed = 1;
    }

    /*
     * Write BIFF data to OLE file.
     */
    function write($data) {
        fputs($this->_filehandle, $data);
    }

    /*
     * Write OLE header block.
     */
    function write_header() {
        if ($this->_biff_only) {
            return;
        }

        $this->_calculate_sizes();

        $root_start      = $this->_root_start;
        $num_lists       = $this->_list_blocks;

        $id              = pack("C8", 0xD0, 0xCF, 0x11, 0xE0,
                                      0xA1, 0xB1, 0x1A, 0xE1);
        $unknown1        = pack("VVVV", 0x00, 0x00, 0x00, 0x00);
        $unknown2        = pack("vv",   0x3E, 0x03);
        $unknown3        = pack("v",    -2);
        $unknown4        = pack("v",    0x09);
        $unknown5        = pack("VVV",  0x06, 0x00, 0x00);
        $num_bbd_blocks  = pack("V",    $num_lists);
        $root_startblock = pack("V",    $root_start);
        $unknown6        = pack("VV",   0x00, 0x1000);
        $sbd_startblock  = pack("V",    -2);
        $unknown7        = pack("VVV",  0x00, -2 ,0x00);
        $unused          = pack("V",    -1);

        fputs($this->_filehandle, $id);
        fputs($this->_filehandle, $unknown1);
        fputs($this->_filehandle, $unknown2);
        fputs($this->_filehandle, $unknown3);
        fputs($this->_filehandle, $unknown4);
        fputs($this->_filehandle, $unknown5);
        fputs($this->_filehandle, $num_bbd_blocks);
        fputs($this->_filehandle, $root_startblock);
        fputs($this->_filehandle, $unknown6);
        fputs($this->_filehandle, $sbd_startblock);
        fputs($this->_filehandle, $unknown7);

        for ($c=1;$c<=$num_lists;$c++) {
            $root_start++;
            fputs($this->_filehandle, pack("V", $root_start));
        }

        for ($c=$num_lists;$c<=108;$c++) {
            fputs($this->_filehandle, $unused);
        }
    }

    /*
     * Write big block depot.
     */
    function _write_big_block_depot() {
        $num_blocks   = $this->_big_blocks;
        $num_lists    = $this->_list_blocks;
        $total_blocks = $num_lists * 128;
        $used_blocks  = $num_blocks + $num_lists + 2;

        $marker       = pack("V", -3);
        $end_of_chain = pack("V", -2);
        $unused       = pack("V", -1);

        for ($i=1;$i<=($num_blocks-1);$i++) {
            fputs($this->_filehandle, pack("V", $i));
        }

        fputs($this->_filehandle, $end_of_chain);
        fputs($this->_filehandle, $end_of_chain);

        for ($c=1;$c<=$num_lists;$c++) {
            fputs($this->_filehandle, $marker);
        }

        for ($c=$used_blocks;$c<=$total_blocks;$c++) {
            fputs($this->_filehandle, $unused);
        }
    }

    /*
     * Write property storage. TODO: add summary sheets
     */
    function _write_property_storage() {
        $rootsize = -2;
        $booksize = $this->_booksize;

        //                name          type  dir start  size
        $this->_write_pps('Root Entry', 0x05,   1,   -2, 0x00);
        $this->_write_pps('Book',       0x02,  -1, 0x00, $booksize);
        $this->_write_pps('',           0x00,  -1, 0x00, 0x0000);
        $this->_write_pps('',           0x00,  -1, 0x00, 0x0000);
    }

    /*
     * Write property sheet in property storage
     */
    function _write_pps($name, $type, $dir, $start, $size) {
        $names           = array();
        $length          = 0;

        if ($name != '') {
            $name   = $name . "\0";
            // Simulate a Unicode string
            $chars=preg_split("''", $name, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($chars as $char) {
                array_push($names, ord($char));
            }
            $length = strlen($name) * 2;
        }

        $rawname         = call_user_func_array('pack', array_merge(array("v*"), $names));
        $zero            = pack("C",  0);

        $pps_sizeofname  = pack("v",  $length);   //0x40
        $pps_type        = pack("v",  $type);     //0x42
        $pps_prev        = pack("V",  -1);        //0x44
        $pps_next        = pack("V",  -1);        //0x48
        $pps_dir         = pack("V",  $dir);      //0x4c

        $unknown1        = pack("V",  0);

        $pps_ts1s        = pack("V",  0);         //0x64
        $pps_ts1d        = pack("V",  0);         //0x68
        $pps_ts2s        = pack("V",  0);         //0x6c
        $pps_ts2d        = pack("V",  0);         //0x70
        $pps_sb          = pack("V",  $start);    //0x74
        $pps_size        = pack("V",  $size);     //0x78

        fputs($this->_filehandle, $rawname);
        fputs($this->_filehandle, str_repeat($zero, (64-$length)));
        fputs($this->_filehandle, $pps_sizeofname);
        fputs($this->_filehandle, $pps_type);
        fputs($this->_filehandle, $pps_prev);
        fputs($this->_filehandle, $pps_next);
        fputs($this->_filehandle, $pps_dir);
        fputs($this->_filehandle, str_repeat($unknown1, 5));
        fputs($this->_filehandle, $pps_ts1s);
        fputs($this->_filehandle, $pps_ts1d);
        fputs($this->_filehandle, $pps_ts2d);
        fputs($this->_filehandle, $pps_ts2d);
        fputs($this->_filehandle, $pps_sb);
        fputs($this->_filehandle, $pps_size);
        fputs($this->_filehandle, $unknown1);
    }

    /*
     * Pad the end of the file
     */
    function _write_padding() {
        $biffsize = $this->_biffsize;

        if ($biffsize < 4096) {
            $min_size = 4096;
        } else {
            $min_size = 512;
        }

        if ($biffsize % $min_size != 0) {
            $padding  = $min_size - ($biffsize % $min_size);
            fputs($this->_filehandle, str_repeat("\0", $padding));
        }
    }

}

/*
 * This is the Spreadsheet::WriteExcel Perl package ported to PHP
 * Spreadsheet::WriteExcel was written by John McNamara, jmcnamara@cpan.org
 */

class writeexcel_workbook extends writeexcel_biffwriter {

    var $_filename;
    var $_tmpfilename;
    var $_parser;
    var $_tempdir;
    var $_1904;
    var $_activesheet;
    var $_firstsheet;
    var $_selected;
    var $_xf_index;
    var $_fileclosed;
    var $_biffsize;
    var $_sheetname;
    var $_tmp_format;
    var $_url_format;
    var $_codepage;
    var $_worksheets;
    var $_sheetnames;
    var $_formats;
    var $_palette;

###############################################################################
#
# new()
#
# Constructor. Creates a new Workbook object from a BIFFwriter object.
#
function writeexcel_workbook($filename) {

    $this->writeexcel_biffwriter();

    $tmp_format  = new writeexcel_format();
    $byte_order  = $this->_byte_order;
    $parser      = new writeexcel_formula($byte_order);

    $this->_filename          = $filename;
    $this->_parser            = $parser;
//?    $this->_tempdir           = undef;
    $this->_1904              = 0;
    $this->_activesheet       = 0;
    $this->_firstsheet        = 0;
    $this->_selected          = 0;
    $this->_xf_index          = 16; # 15 style XF's and 1 cell XF.
    $this->_fileclosed        = 0;
    $this->_biffsize          = 0;
    $this->_sheetname         = "Sheet";
    $this->_tmp_format        = $tmp_format;
    $this->_url_format        = false;
    $this->_codepage          = 0x04E4;
    $this->_worksheets        = array();
    $this->_sheetnames        = array();
    $this->_formats           = array();
    $this->_palette           = array();

    # Add the default format for hyperlinks
    $this->_url_format =& $this->addformat(array('color' => 'blue', 'underline' => 1));

    # Check for a filename
    if ($this->_filename == '') {
//todo: print error
        return;
    }

    # Try to open the named file and see if it throws any errors.
    # If the filename is a reference it is assumed that it is a valid
    # filehandle and ignore
    #
//todo

    # Set colour palette.
    $this->set_palette_xl97();
}

###############################################################################
#
# close()
#
# Calls finalization methods and explicitly close the OLEwriter file
# handle.
#
function close() {
    # Prevent close() from being called twice.
    if ($this->_fileclosed) {
        return;
    }

    $this->_store_workbook();
    $this->_fileclosed = 1;
}

//PHPport: method DESTROY deleted

###############################################################################
#
# sheets()
#
# An accessor for the _worksheets[] array
#
# Returns: a list of the worksheet objects in a workbook
#
function &sheets() {
    return $this->_worksheets;
}

//PHPport: method worksheets deleted:
# This method is now deprecated. Use the sheets() method instead.

###############################################################################
#
# addworksheet($name)
#
# Add a new worksheet to the Excel workbook.
# TODO: Add accessor for $self->{_sheetname} for international Excel versions.
#
# Returns: reference to a worksheet object
#
function &addworksheet($name="") {

    # Check that sheetname is <= 31 chars (Excel limit).
    if (strlen($name) > 31) {
        trigger_error("Sheetname $name must be <= 31 chars", E_USER_ERROR);
    }

    $index     = sizeof($this->_worksheets);
    $sheetname = $this->_sheetname;

    if ($name == "") {
        $name = $sheetname . ($index+1);
    }

    # Check that the worksheet name doesn't already exist: a fatal Excel error.
    foreach ($this->_worksheets as $tmp) {
        if ($name == $tmp->get_name()) {
            trigger_error("Worksheet '$name' already exists", E_USER_ERROR);
        }
    }

    $worksheet = new writeexcel_worksheet($name, $index, $this->_activesheet,
                                          $this->_firstsheet,
                                          $this->_url_format, $this->_parser,
                                          $this->_tempdir);

    $this->_worksheets[$index] = &$worksheet;    # Store ref for iterator
    $this->_sheetnames[$index] = $name;         # Store EXTERNSHEET names
    $this->_parser->set_ext_sheet($name, $index); # Store names in Formula.pm
    return $worksheet;
}

###############################################################################
#
# addformat(%properties)
#
# Add a new format to the Excel workbook. This adds an XF record and
# a FONT record. Also, pass any properties to the Format::new().
#
function &addformat($para=false) {
    if($para===false) {
        $format = new writeexcel_format($this->_xf_index);
    } else {
        $format = new writeexcel_format($this->_xf_index, $para);
    }

    $this->_xf_index += 1;
    # Store format reference
    $this->_formats[]=&$format;

    return $format;
}

###############################################################################
#
# set_1904()
#
# Set the date system: 0 = 1900 (the default), 1 = 1904
#
function set_1904($_1904) {
    $this->_1904 = $_1904;
}

###############################################################################
#
# get_1904()
#
# Return the date system: 0 = 1900, 1 = 1904
#
function get_1904() {
    return $this->_1904;
}

###############################################################################
#
# set_custom_color()
#
# Change the RGB components of the elements in the colour palette.
#
function set_custom_color($index, $red, $green, $blue) {
// todo
/*
    # Match a HTML #xxyyzz style parameter
    if (defined $_[1] and $_[1] =~ /^#(\w\w)(\w\w)(\w\w)/ ) {
        @_ = ($_[0], hex $1, hex $2, hex $3);
    }
*/

    $aref    = &$this->_palette;

    # Check that the colour index is the right range
    if ($index < 8 or $index > 64) {
//todo        carp "Color index $index outside range: 8 <= index <= 64";
        return;
    }

    # Check that the colour components are in the right range
    if ( ($red   < 0 || $red   > 255) ||
         ($green < 0 || $green > 255) ||
         ($blue  < 0 || $blue  > 255) )
    {
//todo        carp "Color component outside range: 0 <= color <= 255";
        return;
    }

    $index -=8; # Adjust colour index (wingless dragonfly)

    # Set the RGB value
    $aref[$index] = array($red, $green, $blue, 0);

    return $index +8;
}

###############################################################################
#
# set_palette_xl97()
#
# Sets the colour palette to the Excel 97+ default.
#
function set_palette_xl97() {
    $this->_palette = array(
                            array(0x00, 0x00, 0x00, 0x00),   # 8
                            array(0xff, 0xff, 0xff, 0x00),   # 9
                            array(0xff, 0x00, 0x00, 0x00),   # 10
                            array(0x00, 0xff, 0x00, 0x00),   # 11
                            array(0x00, 0x00, 0xff, 0x00),   # 12
                            array(0xff, 0xff, 0x00, 0x00),   # 13
                            array(0xff, 0x00, 0xff, 0x00),   # 14
                            array(0x00, 0xff, 0xff, 0x00),   # 15
                            array(0x80, 0x00, 0x00, 0x00),   # 16
                            array(0x00, 0x80, 0x00, 0x00),   # 17
                            array(0x00, 0x00, 0x80, 0x00),   # 18
                            array(0x80, 0x80, 0x00, 0x00),   # 19
                            array(0x80, 0x00, 0x80, 0x00),   # 20
                            array(0x00, 0x80, 0x80, 0x00),   # 21
                            array(0xc0, 0xc0, 0xc0, 0x00),   # 22
                            array(0x80, 0x80, 0x80, 0x00),   # 23
                            array(0x99, 0x99, 0xff, 0x00),   # 24
                            array(0x99, 0x33, 0x66, 0x00),   # 25
                            array(0xff, 0xff, 0xcc, 0x00),   # 26
                            array(0xcc, 0xff, 0xff, 0x00),   # 27
                            array(0x66, 0x00, 0x66, 0x00),   # 28
                            array(0xff, 0x80, 0x80, 0x00),   # 29
                            array(0x00, 0x66, 0xcc, 0x00),   # 30
                            array(0xcc, 0xcc, 0xff, 0x00),   # 31
                            array(0x00, 0x00, 0x80, 0x00),   # 32
                            array(0xff, 0x00, 0xff, 0x00),   # 33
                            array(0xff, 0xff, 0x00, 0x00),   # 34
                            array(0x00, 0xff, 0xff, 0x00),   # 35
                            array(0x80, 0x00, 0x80, 0x00),   # 36
                            array(0x80, 0x00, 0x00, 0x00),   # 37
                            array(0x00, 0x80, 0x80, 0x00),   # 38
                            array(0x00, 0x00, 0xff, 0x00),   # 39
                            array(0x00, 0xcc, 0xff, 0x00),   # 40
                            array(0xcc, 0xff, 0xff, 0x00),   # 41
                            array(0xcc, 0xff, 0xcc, 0x00),   # 42
                            array(0xff, 0xff, 0x99, 0x00),   # 43
                            array(0x99, 0xcc, 0xff, 0x00),   # 44
                            array(0xff, 0x99, 0xcc, 0x00),   # 45
                            array(0xcc, 0x99, 0xff, 0x00),   # 46
                            array(0xff, 0xcc, 0x99, 0x00),   # 47
                            array(0x33, 0x66, 0xff, 0x00),   # 48
                            array(0x33, 0xcc, 0xcc, 0x00),   # 49
                            array(0x99, 0xcc, 0x00, 0x00),   # 50
                            array(0xff, 0xcc, 0x00, 0x00),   # 51
                            array(0xff, 0x99, 0x00, 0x00),   # 52
                            array(0xff, 0x66, 0x00, 0x00),   # 53
                            array(0x66, 0x66, 0x99, 0x00),   # 54
                            array(0x96, 0x96, 0x96, 0x00),   # 55
                            array(0x00, 0x33, 0x66, 0x00),   # 56
                            array(0x33, 0x99, 0x66, 0x00),   # 57
                            array(0x00, 0x33, 0x00, 0x00),   # 58
                            array(0x33, 0x33, 0x00, 0x00),   # 59
                            array(0x99, 0x33, 0x00, 0x00),   # 60
                            array(0x99, 0x33, 0x66, 0x00),   # 61
                            array(0x33, 0x33, 0x99, 0x00),   # 62
                            array(0x33, 0x33, 0x33, 0x00),   # 63
                        );

    return 0;
}

###############################################################################
#
# set_palette_xl5()
#
# Sets the colour palette to the Excel 5 default.
#
function set_palette_xl5() {
    $this->_palette = array(
                            array(0x00, 0x00, 0x00, 0x00),   # 8
                            array(0xff, 0xff, 0xff, 0x00),   # 9
                            array(0xff, 0x00, 0x00, 0x00),   # 10
                            array(0x00, 0xff, 0x00, 0x00),   # 11
                            array(0x00, 0x00, 0xff, 0x00),   # 12
                            array(0xff, 0xff, 0x00, 0x00),   # 13
                            array(0xff, 0x00, 0xff, 0x00),   # 14
                            array(0x00, 0xff, 0xff, 0x00),   # 15
                            array(0x80, 0x00, 0x00, 0x00),   # 16
                            array(0x00, 0x80, 0x00, 0x00),   # 17
                            array(0x00, 0x00, 0x80, 0x00),   # 18
                            array(0x80, 0x80, 0x00, 0x00),   # 19
                            array(0x80, 0x00, 0x80, 0x00),   # 20
                            array(0x00, 0x80, 0x80, 0x00),   # 21
                            array(0xc0, 0xc0, 0xc0, 0x00),   # 22
                            array(0x80, 0x80, 0x80, 0x00),   # 23
                            array(0x80, 0x80, 0xff, 0x00),   # 24
                            array(0x80, 0x20, 0x60, 0x00),   # 25
                            array(0xff, 0xff, 0xc0, 0x00),   # 26
                            array(0xa0, 0xe0, 0xe0, 0x00),   # 27
                            array(0x60, 0x00, 0x80, 0x00),   # 28
                            array(0xff, 0x80, 0x80, 0x00),   # 29
                            array(0x00, 0x80, 0xc0, 0x00),   # 30
                            array(0xc0, 0xc0, 0xff, 0x00),   # 31
                            array(0x00, 0x00, 0x80, 0x00),   # 32
                            array(0xff, 0x00, 0xff, 0x00),   # 33
                            array(0xff, 0xff, 0x00, 0x00),   # 34
                            array(0x00, 0xff, 0xff, 0x00),   # 35
                            array(0x80, 0x00, 0x80, 0x00),   # 36
                            array(0x80, 0x00, 0x00, 0x00),   # 37
                            array(0x00, 0x80, 0x80, 0x00),   # 38
                            array(0x00, 0x00, 0xff, 0x00),   # 39
                            array(0x00, 0xcf, 0xff, 0x00),   # 40
                            array(0x69, 0xff, 0xff, 0x00),   # 41
                            array(0xe0, 0xff, 0xe0, 0x00),   # 42
                            array(0xff, 0xff, 0x80, 0x00),   # 43
                            array(0xa6, 0xca, 0xf0, 0x00),   # 44
                            array(0xdd, 0x9c, 0xb3, 0x00),   # 45
                            array(0xb3, 0x8f, 0xee, 0x00),   # 46
                            array(0xe3, 0xe3, 0xe3, 0x00),   # 47
                            array(0x2a, 0x6f, 0xf9, 0x00),   # 48
                            array(0x3f, 0xb8, 0xcd, 0x00),   # 49
                            array(0x48, 0x84, 0x36, 0x00),   # 50
                            array(0x95, 0x8c, 0x41, 0x00),   # 51
                            array(0x8e, 0x5e, 0x42, 0x00),   # 52
                            array(0xa0, 0x62, 0x7a, 0x00),   # 53
                            array(0x62, 0x4f, 0xac, 0x00),   # 54
                            array(0x96, 0x96, 0x96, 0x00),   # 55
                            array(0x1d, 0x2f, 0xbe, 0x00),   # 56
                            array(0x28, 0x66, 0x76, 0x00),   # 57
                            array(0x00, 0x45, 0x00, 0x00),   # 58
                            array(0x45, 0x3e, 0x01, 0x00),   # 59
                            array(0x6a, 0x28, 0x13, 0x00),   # 60
                            array(0x85, 0x39, 0x6a, 0x00),   # 61
                            array(0x4a, 0x32, 0x85, 0x00),   # 62
                            array(0x42, 0x42, 0x42, 0x00),   # 63
                        );

    return 0;
}

###############################################################################
#
# set_tempdir()
#
# Change the default temp directory used by _initialize() in Worksheet.pm.
#
function set_tempdir($tempdir) {
//todo
/*
    croak "$_[0] is not a valid directory"                 unless -d $_[0];
    croak "set_tempdir must be called before addworksheet" if $self->sheets();
*/

    $this->_tempdir = $tempdir;
}

###############################################################################
#
# set_codepage()
#
# See also the _store_codepage method. This is used to store the code page, i.e.
# the character set used in the workbook.
#
function set_codepage($cp) {

    if($cp==1)
      $codepage   = 0x04E4;
    else if($cp==2)
      $codepage   = 0x8000;
    if($codepage)
      $this->_codepage = $codepage;
}


###############################################################################
#
# _store_workbook()
#
# Assemble worksheets into a workbook and send the BIFF data to an OLE
# storage.
#
function _store_workbook() {

    # Ensure that at least one worksheet has been selected.
    if ($this->_activesheet == 0) {
        $this->_worksheets[0]->_selected = 1;
    }

    # Calculate the number of selected worksheet tabs and call the finalization
    # methods for each worksheet
    for ($c=0;$c<sizeof($this->_worksheets);$c++) {
        $sheet=&$this->_worksheets[$c];
        if ($sheet->_selected) {
            $this->_selected++;
        }
        $sheet->_close($this->_sheetnames);
    }

    # Add Workbook globals
    $this->_store_bof(0x0005);

    $this->_store_externs();    # For print area and repeat rows

    $this->_store_names();      # For print area and repeat rows

    $this->_store_codepage();
    
    $this->_store_window1();

    $this->_store_1904();

    $this->_store_all_fonts();

    $this->_store_all_num_formats();

    $this->_store_all_xfs();

    $this->_store_all_styles();

    $this->_store_palette();

    $this->_calc_sheet_offsets();

    # Add BOUNDSHEET records
    for ($c=0;$c<sizeof($this->_worksheets);$c++) {
       $sheet=&$this->_worksheets[$c];
        $this->_store_boundsheet($sheet->_name, $sheet->_offset);
    }

    # End Workbook globals
    $this->_store_eof();

    # Store the workbook in an OLE container
    $this->_store_OLE_file();
}

###############################################################################
#
# _store_OLE_file()
#
# Store the workbook in an OLE container if the total size of the workbook data
# is less than ~ 7MB.
#
function _store_OLE_file() {
## ABR
    if ($this->_tmpfilename != '') {
        $OLE  = new writeexcel_olewriter('/tmp/'.$this->_tmpfilename);
        $OLE->_OLEtmpfilename = '/tmp/'.$this->_tmpfilename;
    } else {
        $OLE  = new writeexcel_olewriter($this->_filename);
        $OLE->_OLEtmpfilename = '';
    };
## END ABR
					            
    # Write Worksheet data if data <~ 7MB
    if ($OLE->set_size($this->_biffsize)) {
        $OLE->write_header();
        $OLE->write($this->_data);

        for ($c=0;$c<sizeof($this->_worksheets);$c++) {
            $sheet=&$this->_worksheets[$c];
            while ($tmp = $sheet->get_data()) {
                $OLE->write($tmp);
            }
            $sheet->cleanup();
        }
    }

    $OLE->close();
}

###############################################################################
#
# _calc_sheet_offsets()
#
# Calculate offsets for Worksheet BOF records.
#
function _calc_sheet_offsets() {

    $BOF     = 11;
    $EOF     = 4;
    $offset  = $this->_datasize;

    foreach ($this->_worksheets as $sheet) {
        $offset += $BOF + strlen($sheet->_name);
    }

    $offset += $EOF;

    for ($c=0;$c<sizeof($this->_worksheets);$c++) {
        $sheet=&$this->_worksheets[$c];
        $sheet->_offset = $offset;
        $offset += $sheet->_datasize;
    }

    $this->_biffsize = $offset;
}

###############################################################################
#
# _store_all_fonts()
#
# Store the Excel FONT records.
#
function _store_all_fonts() {
    # _tmp_format is added by new(). We use this to write the default XF's
    $format = $this->_tmp_format;
    $font   = $format->get_font();

    # Note: Fonts are 0-indexed. According to the SDK there is no index 4,
    # so the following fonts are 0, 1, 2, 3, 5
    #
    for ($c=0;$c<5;$c++) {
        $this->_append($font);
    }

    # Iterate through the XF objects and write a FONT record if it isn't the
    # same as the default FONT and if it hasn't already been used.
    #
    $index = 6;                  # The first user defined FONT

    $key = $format->get_font_key(); # The default font from _tmp_format
    $fonts[$key] = 0;               # Index of the default font

    for ($c=0;$c<sizeof($this->_formats);$c++) {
        $format=&$this->_formats[$c];

        $key = $format->get_font_key();

        if (isset($fonts[$key])) {
            # FONT has already been used
            $format->_font_index = $fonts[$key];
        } else {
            # Add a new FONT record
            $fonts[$key]           = $index;
            $format->_font_index = $index;
            $index++;
            $font = $format->get_font();
            $this->_append($font);
        }
    }
}

###############################################################################
#
# _store_all_num_formats()
#
# Store user defined numerical formats i.e. FORMAT records
#
function _store_all_num_formats() {

    # Leaning num_format syndrome
    $num_formats_list=array();
    $index = 164;

    # Iterate through the XF objects and write a FORMAT record if it isn't a
    # built-in format type and if the FORMAT string hasn't already been used.
    #

    for ($c=0;$c<sizeof($this->_formats);$c++) {
        $format=&$this->_formats[$c];

        $num_format = $format->_num_format;

        # Check if $num_format is an index to a built-in format.
        # Also check for a string of zeros, which is a valid format string
        # but would evaluate to zero.
        #
        if (!preg_match('/^0+\d/', $num_format)) {
            if (preg_match('/^\d+$/', $num_format)) {
                # built-in
                continue;
            }
        }

        if (isset($num_formats[$num_format])) {
            # FORMAT has already been used
            $format->_num_format = $num_formats[$num_format];
        } else {
            # Add a new FORMAT
            $num_formats[$num_format] = $index;
            $format->_num_format    = $index;
            array_push($num_formats_list, $num_format);
            $index++;
        }
    }

    # Write the new FORMAT records starting from 0xA4
    $index = 164;
    foreach ($num_formats_list as $num_format) {
        $this->_store_num_format($num_format, $index);
        $index++;
    }
}

###############################################################################
#
# _store_all_xfs()
#
# Write all XF records.
#
function _store_all_xfs() {
    # _tmp_format is added by new(). We use this to write the default XF's
    # The default font index is 0
    #
    $format = $this->_tmp_format;
    $xf;

    for ($c=0;$c<15;$c++) {
        $xf = $format->get_xf('style'); # Style XF
        $this->_append($xf);
    }

    $xf = $format->get_xf('cell');      # Cell XF
    $this->_append($xf);

    # User defined XFs
    foreach ($this->_formats as $format) {
        $xf = $format->get_xf('cell');
        $this->_append($xf);
    }
}

###############################################################################
#
# _store_all_styles()
#
# Write all STYLE records.
#
function _store_all_styles() {
    $this->_store_style();
}

###############################################################################
#
# _store_externs()
#
# Write the EXTERNCOUNT and EXTERNSHEET records. These are used as indexes for
# the NAME records.
#
function _store_externs() {

    # Create EXTERNCOUNT with number of worksheets
    $this->_store_externcount(sizeof($this->_worksheets));

    # Create EXTERNSHEET for each worksheet
    foreach ($this->_sheetnames as $sheetname) {
        $this->_store_externsheet($sheetname);
    }
}

###############################################################################
#
# _store_names()
#
# Write the NAME record to define the print area and the repeat rows and cols.
#
function _store_names() {

    # Create the print area NAME records
    foreach ($this->_worksheets as $worksheet) {
        # Write a Name record if the print area has been defined
        if ($worksheet->_print_rowmin!==false) {
            $this->_store_name_short(
                $worksheet->_index,
                0x06, # NAME type
                $worksheet->_print_rowmin,
                $worksheet->_print_rowmax,
                $worksheet->_print_colmin,
                $worksheet->_print_colmax
            );
        }
    }

    # Create the print title NAME records
    foreach ($this->_worksheets as $worksheet) {

        $rowmin = $worksheet->_title_rowmin;
        $rowmax = $worksheet->_title_rowmax;
        $colmin = $worksheet->_title_colmin;
        $colmax = $worksheet->_title_colmax;

        # Determine if row + col, row, col or nothing has been defined
        # and write the appropriate record
        #
        if ($rowmin!==false && $colmin!==false) {
            # Row and column titles have been defined.
            # Row title has been defined.
            $this->_store_name_long(
                $worksheet->_index,
                0x07, # NAME type
                $rowmin,
                $rowmax,
                $colmin,
                $colmax
           );
        } elseif ($rowmin!==false) {
            # Row title has been defined.
            $this->_store_name_short(
                $worksheet->_index,
                0x07, # NAME type
                $rowmin,
                $rowmax,
                0x00,
                0xff
            );
        } elseif ($colmin!==false) {
            # Column title has been defined.
            $this->_store_name_short(
                $worksheet->_index,
                0x07, # NAME type
                0x0000,
                0x3fff,
                $colmin,
                $colmax
            );
        } else {
            # Print title hasn't been defined.
        }
    }
}

###############################################################################
###############################################################################
#
# BIFF RECORDS
#

###############################################################################
#
# _store_window1()
#
# Write Excel BIFF WINDOW1 record.
#
function _store_window1() {

    $record    = 0x003D;                 # Record identifier
    $length    = 0x0012;                 # Number of bytes to follow

    $xWn       = 0x0000;                 # Horizontal position of window
    $yWn       = 0x0000;                 # Vertical position of window
    $dxWn      = 0x25BC;                 # Width of window
    $dyWn      = 0x1572;                 # Height of window

    $grbit     = 0x0038;                 # Option flags
    $ctabsel   = $this->_selected;     # Number of workbook tabs selected
    $wTabRatio = 0x0258;                 # Tab to scrollbar ratio

    $itabFirst = $this->_firstsheet;   # 1st displayed worksheet
    $itabCur   = $this->_activesheet;  # Active worksheet

    $header    = pack("vv",        $record, $length);
    $data      = pack("vvvvvvvvv", $xWn, $yWn, $dxWn, $dyWn,
                                   $grbit,
                                   $itabCur, $itabFirst,
                                   $ctabsel, $wTabRatio);

    $this->_append($header . $data);
}

###############################################################################
#
# _store_boundsheet()
#
# Writes Excel BIFF BOUNDSHEET record.
#
function _store_boundsheet($sheetname, $offset) {
    $record    = 0x0085;               # Record identifier
    $length    = 0x07 + strlen($sheetname); # Number of bytes to follow

    //$sheetname = $_[0];                # Worksheet name
    //$offset    = $_[1];                # Location of worksheet BOF
    $grbit     = 0x0000;               # Sheet identifier
    $cch       = strlen($sheetname);   # Length of sheet name

    $header    = pack("vv",  $record, $length);
    $data      = pack("VvC", $offset, $grbit, $cch);

    $this->_append($header . $data . $sheetname);
}

###############################################################################
#
# _store_style()
#
# Write Excel BIFF STYLE records.
#
function _store_style() {
    $record    = 0x0293; # Record identifier
    $length    = 0x0004; # Bytes to follow

    $ixfe      = 0x8000; # Index to style XF
    $BuiltIn   = 0x00;   # Built-in style
    $iLevel    = 0xff;   # Outline style level

    $header    = pack("vv",  $record, $length);
    $data      = pack("vCC", $ixfe, $BuiltIn, $iLevel);

    $this->_append($header . $data);
}

###############################################################################
#
# _store_num_format()
#
# Writes Excel FORMAT record for non "built-in" numerical formats.
#
function _store_num_format($num_format, $index) {
    $record    = 0x041E;                 # Record identifier
    $length    = 0x03 + strlen($num_format);   # Number of bytes to follow

    $format    = $num_format;                  # Custom format string
    $ifmt      = $index;                  # Format index code
    $cch       = strlen($format);        # Length of format string

    $header    = pack("vv", $record, $length);
    $data      = pack("vC", $ifmt, $cch);

    $this->_append($header . $data . $format);
}

###############################################################################
#
# _store_1904()
#
# Write Excel 1904 record to indicate the date system in use.
#
function _store_1904() {
    $record    = 0x0022;         # Record identifier
    $length    = 0x0002;         # Bytes to follow

    $f1904     = $this->_1904; # Flag for 1904 date system

    $header    = pack("vv",  $record, $length);
    $data      = pack("v", $f1904);

    $this->_append($header . $data);
}

###############################################################################
#
# _store_externcount($count)
#
# Write BIFF record EXTERNCOUNT to indicate the number of external sheet
# references in the workbook.
#
# Excel only stores references to external sheets that are used in NAME.
# The workbook NAME record is required to define the print area and the repeat
# rows and columns.
#
# A similar method is used in Worksheet.pm for a slightly different purpose.
#
function _store_externcount($par0) {
    $record   = 0x0016;          # Record identifier
    $length   = 0x0002;          # Number of bytes to follow

    $cxals    = $par0;           # Number of external references

    $header   = pack("vv", $record, $length);
    $data     = pack("v",  $cxals);

    $this->_append($header . $data);
}

###############################################################################
#
# _store_externsheet($sheetname)
#
#
# Writes the Excel BIFF EXTERNSHEET record. These references are used by
# formulas. NAME record is required to define the print area and the repeat
# rows and columns.
#
# A similar method is used in Worksheet.pm for a slightly different purpose.
#
function _store_externsheet($par0) {
    $record      = 0x0017;               # Record identifier
    $length      = 0x02 + strlen($par0); # Number of bytes to follow

    $sheetname   = $par0;                # Worksheet name
    $cch         = strlen($sheetname);   # Length of sheet name
    $rgch        = 0x03;                 # Filename encoding

    $header      = pack("vv",  $record, $length);
    $data        = pack("CC", $cch, $rgch);

    $this->_append($header . $data . $sheetname);
}

###############################################################################
#
# _store_name_short()
#
#
# Store the NAME record in the short format that is used for storing the print
# area, repeat rows only and repeat columns only.
#
function _store_name_short($par0, $par1, $par2, $par3, $par4, $par5) {
    $record          = 0x0018;       # Record identifier
    $length          = 0x0024;       # Number of bytes to follow

    $index           = $par0;        # Sheet index
    $type            = $par1;

    $grbit           = 0x0020;       # Option flags
    $chKey           = 0x00;         # Keyboard shortcut
    $cch             = 0x01;         # Length of text name
    $cce             = 0x0015;       # Length of text definition
    $ixals           = $index +1;    # Sheet index
    $itab            = $ixals;       # Equal to ixals
    $cchCustMenu     = 0x00;         # Length of cust menu text
    $cchDescription  = 0x00;         # Length of description text
    $cchHelptopic    = 0x00;         # Length of help topic text
    $cchStatustext   = 0x00;         # Length of status bar text
    $rgch            = $type;        # Built-in name type

    $unknown03       = 0x3b;
    $unknown04       = 0xffff-$index;
    $unknown05       = 0x0000;
    $unknown06       = 0x0000;
    $unknown07       = 0x1087;
    $unknown08       = 0x8005;

    $rowmin          = $par2;        # Start row
    $rowmax          = $par3;        # End row
    $colmin          = $par4;        # Start column
    $colmax          = $par5;        # end column

    $header          = pack("vv",  $record, $length);
    $data            = pack("v", $grbit);
    $data              .= pack("C", $chKey);
    $data              .= pack("C", $cch);
    $data              .= pack("v", $cce);
    $data              .= pack("v", $ixals);
    $data              .= pack("v", $itab);
    $data              .= pack("C", $cchCustMenu);
    $data              .= pack("C", $cchDescription);
    $data              .= pack("C", $cchHelptopic);
    $data              .= pack("C", $cchStatustext);
    $data              .= pack("C", $rgch);
    $data              .= pack("C", $unknown03);
    $data              .= pack("v", $unknown04);
    $data              .= pack("v", $unknown05);
    $data              .= pack("v", $unknown06);
    $data              .= pack("v", $unknown07);
    $data              .= pack("v", $unknown08);
    $data              .= pack("v", $index);
    $data              .= pack("v", $index);
    $data              .= pack("v", $rowmin);
    $data              .= pack("v", $rowmax);
    $data              .= pack("C", $colmin);
    $data              .= pack("C", $colmax);

    $this->_append($header . $data);
}

###############################################################################
#
# _store_name_long()
#
#
# Store the NAME record in the long format that is used for storing the repeat
# rows and columns when both are specified. This share a lot of code with
# _store_name_short() but we use a separate method to keep the code clean.
# Code abstraction for reuse can be carried too far, and I should know. ;-)
#
function _store_name_long($par0, $par1, $par2, $par3, $par4, $par5) {
    $record          = 0x0018;       # Record identifier
    $length          = 0x003d;       # Number of bytes to follow

    $index           = $par0;        # Sheet index
    $type            = $par1;

    $grbit           = 0x0020;       # Option flags
    $chKey           = 0x00;         # Keyboard shortcut
    $cch             = 0x01;         # Length of text name
    $cce             = 0x002e;       # Length of text definition
    $ixals           = $index +1;    # Sheet index
    $itab            = $ixals;       # Equal to ixals
    $cchCustMenu     = 0x00;         # Length of cust menu text
    $cchDescription  = 0x00;         # Length of description text
    $cchHelptopic    = 0x00;         # Length of help topic text
    $cchStatustext   = 0x00;         # Length of status bar text
    $rgch            = $type;        # Built-in name type

    $unknown01       = 0x29;
    $unknown02       = 0x002b;
    $unknown03       = 0x3b;
    $unknown04       = 0xffff-$index;
    $unknown05       = 0x0000;
    $unknown06       = 0x0000;
    $unknown07       = 0x1087;
    $unknown08       = 0x8008;

    $rowmin          = $par2;        # Start row
    $rowmax          = $par3;        # End row
    $colmin          = $par4;        # Start column
    $colmax          = $par5;        # end column

    $header          = pack("vv",  $record, $length);
    $data            = pack("v", $grbit);
    $data              .= pack("C", $chKey);
    $data              .= pack("C", $cch);
    $data              .= pack("v", $cce);
    $data              .= pack("v", $ixals);
    $data              .= pack("v", $itab);
    $data              .= pack("C", $cchCustMenu);
    $data              .= pack("C", $cchDescription);
    $data              .= pack("C", $cchHelptopic);
    $data              .= pack("C", $cchStatustext);
    $data              .= pack("C", $rgch);
    $data              .= pack("C", $unknown01);
    $data              .= pack("v", $unknown02);
    # Column definition
    $data              .= pack("C", $unknown03);
    $data              .= pack("v", $unknown04);
    $data              .= pack("v", $unknown05);
    $data              .= pack("v", $unknown06);
    $data              .= pack("v", $unknown07);
    $data              .= pack("v", $unknown08);
    $data              .= pack("v", $index);
    $data              .= pack("v", $index);
    $data              .= pack("v", 0x0000);
    $data              .= pack("v", 0x3fff);
    $data              .= pack("C", $colmin);
    $data              .= pack("C", $colmax);
    # Row definition
    $data              .= pack("C", $unknown03);
    $data              .= pack("v", $unknown04);
    $data              .= pack("v", $unknown05);
    $data              .= pack("v", $unknown06);
    $data              .= pack("v", $unknown07);
    $data              .= pack("v", $unknown08);
    $data              .= pack("v", $index);
    $data              .= pack("v", $index);
    $data              .= pack("v", $rowmin);
    $data              .= pack("v", $rowmax);
    $data              .= pack("C", 0x00);
    $data              .= pack("C", 0xff);
    # End of data
    $data              .= pack("C", 0x10);

    $this->_append($header . $data);
}

###############################################################################
#
# _store_palette()
#
# Stores the PALETTE biff record.
#
function _store_palette() {
    $aref            = &$this->_palette;

    $record          = 0x0092;                  # Record identifier
    $length          = 2 + 4 * sizeof($aref);   # Number of bytes to follow
    $ccv             =         sizeof($aref);   # Number of RGB values to follow
    //$data;                                      # The RGB data

    # Pack the RGB data
    foreach($aref as $dat) {
        $data .= call_user_func_array('pack', array_merge(array("CCCC"), $dat));
    }

    $header = pack("vvv",  $record, $length, $ccv);

    $this->_append($header . $data);
}

###############################################################################
#
# _store_codepage()
#
# Stores the CODEPAGE biff record.
#
function _store_codepage() {

    $record          = 0x0042;               # Record identifier
    $length          = 0x0002;               # Number of bytes to follow
    $cv              = $this->_codepage;     # The code page

    $header          = pack("vv", $record, $length);
    $data            = pack("v",  $cv);

    $this->_append($header.$data);
}

}

/*
 * This is the Spreadsheet::WriteExcel Perl package ported to PHP
 * Spreadsheet::WriteExcel was written by John McNamara, jmcnamara@cpan.org
 */

class writeexcel_worksheet extends writeexcel_biffwriter {

    var $_name;
    var $_index;
    var $_activesheet;
    var $_firstsheet;
    var $_url_format;
    var $_parser;
    var $_tempdir;

    var $_ext_sheets;
    var $_using_tmpfile;
    var $_tmpfilename;
    var $_filehandle;
    var $_fileclosed;
    var $_offset;
    var $_xls_rowmax;
    var $_xls_colmax;
    var $_xls_strmax;
    var $_dim_rowmin;
    var $_dim_rowmax;
    var $_dim_colmin;
    var $_dim_colmax;
    var $_colinfo;
    var $_selection;
    var $_panes;
    var $_active_pane;
    var $_frozen;
    var $_selected;

    var $_paper_size;
    var $_orientation;
    var $_header;
    var $_footer;
    var $_hcenter;
    var $_vcenter;
    var $_margin_head;
    var $_margin_foot;
    var $_margin_left;
    var $_margin_right;
    var $_margin_top;
    var $_margin_bottom;

    var $_title_rowmin;
    var $_title_rowmax;
    var $_title_colmin;
    var $_title_colmax;
    var $_print_rowmin;
    var $_print_rowmax;
    var $_print_colmin;
    var $_print_colmax;

    var $_print_gridlines;
    var $_screen_gridlines;
    var $_print_headers;

    var $_fit_page;
    var $_fit_width;
    var $_fit_height;

    var $_hbreaks;
    var $_vbreaks;

    var $_protect;
    var $_password;

    var $_col_sizes;
    var $_row_sizes;

    var $_col_formats;
    var $_row_formats;

    var $_zoom;
    var $_print_scale;

    var $_debug;

    /*
     * Constructor. Creates a new Worksheet object from a BIFFwriter object
     */
    function writeexcel_worksheet($name, $index, &$activesheet, &$firstsheet,
                                  &$url_format, &$parser, $tempdir) {

        $this->writeexcel_biffwriter();

        $rowmax                   = 65536; // 16384 in Excel 5
        $colmax                   = 256;
        $strmax                   = 65536;

        $this->_name              = $name;
        $this->_index             = $index;
        $this->_activesheet       = &$activesheet;
        $this->_firstsheet        = &$firstsheet;
        $this->_url_format        = &$url_format;
        $this->_parser            = &$parser;
        $this->_tempdir           = $tempdir;

        $this->_ext_sheets        = array();
        $this->_using_tmpfile     = 1;
        $this->_tmpfilename       = false;
        $this->_filehandle        = false;
        $this->_fileclosed        = 0;
        $this->_offset            = 0;
        $this->_xls_rowmax        = $rowmax;
        $this->_xls_colmax        = $colmax;
        $this->_xls_strmax        = $strmax;
        $this->_dim_rowmin        = $rowmax +1;
        $this->_dim_rowmax        = 0;
        $this->_dim_colmin        = $colmax +1;
        $this->_dim_colmax        = 0;
        $this->_colinfo           = array();
        $this->_selection         = array(0, 0);
        $this->_panes             = array();
        $this->_active_pane       = 3;
        $this->_frozen            = 0;
        $this->_selected          = 0;

        $this->_paper_size        = 0x0;
        $this->_orientation       = 0x1;
        $this->_header            = '';
        $this->_footer            = '';
        $this->_hcenter           = 0;
        $this->_vcenter           = 0;
        $this->_margin_head       = 0.50;
        $this->_margin_foot       = 0.50;
        $this->_margin_left       = 0.75;
        $this->_margin_right      = 0.75;
        $this->_margin_top        = 1.00;
        $this->_margin_bottom     = 1.00;

        $this->_title_rowmin      = false;
        $this->_title_rowmax      = false;
        $this->_title_colmin      = false;
        $this->_title_colmax      = false;
        $this->_print_rowmin      = false;
        $this->_print_rowmax      = false;
        $this->_print_colmin      = false;
        $this->_print_colmax      = false;

        $this->_print_gridlines   = 1;
        $this->_screen_gridlines  = 1;
        $this->_print_headers     = 0;

        $this->_fit_page          = 0;
        $this->_fit_width         = 0;
        $this->_fit_height        = 0;

        $this->_hbreaks           = array();
        $this->_vbreaks           = array();

        $this->_protect           = 0;
        $this->_password          = false;

        $this->_col_sizes         = array();
        $this->_row_sizes         = array();

        $this->_col_formats       = array();
        $this->_row_formats       = array();

        $this->_zoom              = 100;
        $this->_print_scale       = 100;

        $this->_initialize();
    }

###############################################################################
#
# _initialize()
#
# Open a tmp file to store the majority of the Worksheet data. If this fails,
# for example due to write permissions, store the data in memory. This can be
# slow for large files.
#
function _initialize() {

    # Open tmp file for storing Worksheet data.
    $this->_tmpfilename=tempnam($this->_tempdir, "php_writeexcel");
    $fh=fopen($this->_tmpfilename, "w+b");

    if ($fh) {
        # Store filehandle
        $this->_filehandle = $fh;
    } else {
        # If tempfile() failed store data in memory
        $this->_using_tmpfile = 0;
        $this->_tmpfilename=false;

        if ($this->_index == 0) {
            $dir = $this->_tempdir;

//todo            warn "Unable to create temp files in $dir. Refer to set_tempdir()".
//                 " in the Spreadsheet::WriteExcel documentation.\n" ;
        }
    }
}

    /*
     * Add data to the beginning of the workbook (note the reverse order)
     * and to the end of the workbook.
     */
    function _close($sheetnames) {

        ///////////////////////////////
        // Prepend in reverse order!!
        //

        $this->_store_dimensions();        // Prepend the sheet dimensions
        $this->_store_password();          // Prepend the sheet password
        $this->_store_protect();           // Prepend the sheet protection
        $this->_store_setup();             // Prepend the page setup
        $this->_store_margin_bottom();     // Prepend the bottom margin
        $this->_store_margin_top();        // Prepend the top margin
        $this->_store_margin_right();      // Prepend the right margin
        $this->_store_margin_left();       // Prepend the left margin
        $this->_store_vcenter();           // Prepend the page vertical
                                           // centering
        $this->_store_hcenter();           // Prepend the page horizontal
                                           // centering
        $this->_store_footer();            // Prepend the page footer
        $this->_store_header();            // Prepend the page header
        $this->_store_vbreak();            // Prepend the vertical page breaks
        $this->_store_hbreak();            // Prepend the horizontal
                                           // page breaks
        $this->_store_wsbool();            // Prepend WSBOOL
        $this->_store_gridset();           // Prepend GRIDSET
        $this->_store_print_gridlines();   // Prepend PRINTGRIDLINES
        $this->_store_print_headers();     // Prepend PRINTHEADERS

        // Prepend EXTERNSHEET references
        $num_sheets = sizeof($sheetnames);
        for ($i = $num_sheets; $i > 0; $i--) {
            $sheetname = $sheetnames[$i-1];
            $this->_store_externsheet($sheetname);
        }

        $this->_store_externcount($num_sheets);   // Prepend the EXTERNCOUNT
                                                  // of external references.

        // Prepend the COLINFO records if they exist
        if (sizeof($this->_colinfo)>0){
            while (sizeof($this->_colinfo)>0) {
                $arrayref = array_pop ($this->_colinfo);
                $this->_store_colinfo($arrayref);
            }
            $this->_store_defcol();
        }

        $this->_store_bof(0x0010);    // Prepend the BOF record

        //
        // End of prepend. Read upwards from here.
        ////////////////////////////////////////////

        // Append
        $this->_store_window2();
        $this->_store_zoom();

        if (sizeof($this->_panes)>0) {
            $this->_store_panes($this->_panes);
        }

        $this->_store_selection($this->_selection);
        $this->_store_eof();
    }

    /*
     * Retrieve the worksheet name.
     */
    function get_name() {
        return $this->_name;
    }

###############################################################################
#
# get_data().
#
# Retrieves data from memory in one chunk, or from disk in $buffer
# sized chunks.
#
function get_data() {

    $buffer = 4096;

    # Return data stored in memory
    if ($this->_data!==false) {
        $tmp=$this->_data;
        $this->_data=false;

        // The next data comes from the temporary file, so prepare
        // it by putting the file pointer to the beginning
        if ($this->_using_tmpfile) {
            fseek($this->_filehandle, 0, SEEK_SET);
        }

        if ($this->_debug) {
            print "*** worksheet::get_data() called (1):";
            for ($c=0;$c<strlen($tmp);$c++) {
                if ($c%16==0) {
                    print "\n";
                }
                printf("%02X ", ord($tmp[$c]));
            }
            print "\n";
        }

        return $tmp;
    }

    # Return data stored on disk
    if ($this->_using_tmpfile) {
        if ($tmp=fread($this->_filehandle, $buffer)) {

            if ($this->_debug) {
                print "*** worksheet::get_data() called (2):";
                for ($c=0;$c<strlen($tmp);$c++) {
                    if ($c%16==0) {
                        print "\n";
                    }
                    printf("%02X ", ord($tmp[$c]));
                }
                print "\n";
            }

            return $tmp;
        }
    }

    # No more data to return
    return false;
}

    /* Remove the temporary file */
    function cleanup() {
      if ($this->_using_tmpfile) {
        fclose($this->_filehandle);
        unlink($this->_tmpfilename);
        $this->_tmpfilename=false;
        $this->_using_tmpfile=false;
      }
    }

    /*
     * Set this worksheet as a selected worksheet, i.e. the worksheet has
     * its tab highlighted.
     */
    function select() {
        $this->_selected = 1;
    }

    /*
     * Set this worksheet as the active worksheet, i.e. the worksheet
     * that is displayed when the workbook is opened. Also set it as
     * selected.
     */
    function activate() {
        $this->_selected = 1;
        $this->_activesheet = $this->_index;
    }

    /*
     * Set this worksheet as the first visible sheet. This is necessary
     * when there are a large number of worksheets and the activated
     * worksheet is not visible on the screen.
     */
    function set_first_sheet() {
        $this->_firstsheet = $this->_index;
    }

    /*
     * Set the worksheet protection flag to prevent accidental modification
     * and to hide formulas if the locked and hidden format properties have
     * been set.
     */
    function protect($password) {
        $this->_protect   = 1;
        $this->_password  = $this->_encode_password($password);
    }

###############################################################################
#
# set_column($firstcol, $lastcol, $width, $format, $hidden)
#
# Set the width of a single column or a range of column.
# See also: _store_colinfo
#
function set_column() {

    $_=func_get_args();

    $cell = $_[0];

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $cell)) {
        $_ = $this->_substitute_cellref($_);
    }

    array_push($this->_colinfo, $_);

    # Store the col sizes for use when calculating image vertices taking
    # hidden columns into account. Also store the column formats.
    #
    if (sizeof($_)<3) {
        # Ensure at least $firstcol, $lastcol and $width
        return;
    }

    $width  = $_[4] ? 0 : $_[2]; # Set width to zero if column is hidden
    $format = $_[3];

    list($firstcol, $lastcol) = $_;

    for ($col=$firstcol;$col<=$lastcol;$col++) {
        $this->_col_sizes[$col]   = $width;
        if ($format) {
            $this->_col_formats[$col] = $format;
        }
    }
}

###############################################################################
#
# set_selection()
#
# Set which cell or cells are selected in a worksheet: see also the
# function _store_selection
#
function set_selection() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    $this->_selection = $_;
}

###############################################################################
#
# freeze_panes()
#
# Set panes and mark them as frozen. See also _store_panes().
#
function freeze_panes() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    $this->_frozen = 1;
    $this->_panes  = $_;
}

###############################################################################
#
# thaw_panes()
#
# Set panes and mark them as unfrozen. See also _store_panes().
#
function thaw_panes() {

    $_=func_get_args();

    $this->_frozen = 0;
    $this->_panes  = $_;
}

    /*
     * Set the page orientation as portrait.
     */
    function set_portrait() {
        $this->_orientation = 1;
    }

    /*
     * Set the page orientation as landscape.
     */
    function set_landscape() {
        $this->_orientation = 0;
    }

    /*
     * Set the paper type. Ex. 1 = US Letter, 9 = A4
     */
    function set_paper($type) {
        $this->_paper_size = $type;
    }

    /*
     * Set the page header caption and optional margin.
     */
    function set_header($string, $margin) {

        if (strlen($string) >= 255) {
            trigger_error("Header string must be less than 255 characters",
                          E_USER_WARNING);
            return;
        }

        $this->_header      = $string;
        $this->_margin_head = $margin;
    }

    /*
     * Set the page footer caption and optional margin.
     */
    function set_footer($string, $margin) {
        if (strlen($string) >= 255) {
            trigger_error("Footer string must be less than 255 characters",
                          E_USER_WARNING);
            return;
        }

        $this->_footer      = $string;
        $this->_margin_foot = $margin;
    }

    /*
     * Center the page horizontally.
     */
    function center_horizontally($hcenter=1) {
        $this->_hcenter = $hcenter;
    }

    /*
     * Center the page horizontally.
     */
    function center_vertically($vcenter=1) {
        $this->_vcenter = $vcenter;
    }

    /*
     * Set all the page margins to the same value in inches.
     */
    function set_margins($margin) {
        $this->set_margin_left($margin);
        $this->set_margin_right($margin);
        $this->set_margin_top($margin);
        $this->set_margin_bottom($margin);
    }

    /*
     * Set the left and right margins to the same value in inches.
     */
    function set_margins_LR($margin) {
        $this->set_margin_left($margin);
        $this->set_margin_right($margin);
    }

    /*
     * Set the top and bottom margins to the same value in inches.
     */
    function set_margins_TB($margin) {
        $this->set_margin_top($margin);
        $this->set_margin_bottom($margin);
    }

    /*
     * Set the left margin in inches.
     */
    function set_margin_left($margin=0.75) {
        $this->_margin_left = $margin;
    }

    /*
     * Set the right margin in inches.
     */
    function set_margin_right($margin=0.75) {
        $this->_margin_right = $margin;
    }

    /*
     * Set the top margin in inches.
     */
    function set_margin_top($margin=1.00) {
        $this->_margin_top = $margin;
    }

    /*
     * Set the bottom margin in inches.
     */
    function set_margin_bottom($margin=1.00) {
        $this->_margin_bottom = $margin;
    }

###############################################################################
#
# repeat_rows($first_row, $last_row)
#
# Set the rows to repeat at the top of each printed page. See also the
# _store_name_xxxx() methods in Workbook.pm.
#
function repeat_rows() {

    $_=func_get_args();

    $this->_title_rowmin  = $_[0];
    $this->_title_rowmax  = isset($_[1]) ? $_[1] : $_[0]; # Second row is optional
}

###############################################################################
#
# repeat_columns($first_col, $last_col)
#
# Set the columns to repeat at the left hand side of each printed page.
# See also the _store_names() methods in Workbook.pm.
#
function repeat_columns() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    $this->_title_colmin  = $_[0];
    $this->_title_colmax  = isset($_[1]) ? $_[1] : $_[0]; # Second col is optional
}

###############################################################################
#
# print_area($first_row, $first_col, $last_row, $last_col)
#
# Set the area of each worksheet that will be printed. See also the
# _store_names() methods in Workbook.pm.
#
function print_area() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    if (sizeof($_) != 4) {
        # Require 4 parameters
        return;
    }

    $this->_print_rowmin = $_[0];
    $this->_print_colmin = $_[1];
    $this->_print_rowmax = $_[2];
    $this->_print_colmax = $_[3];
}

    /*
     * Set the option to hide gridlines on the screen and the printed page.
     * There are two ways of doing this in the Excel BIFF format: The first
     * is by setting the DspGrid field of the WINDOW2 record, this turns off
     * the screen and subsequently the print gridline. The second method is
     * to via the PRINTGRIDLINES and GRIDSET records, this turns off the
     * printed gridlines only. The first method is probably sufficient for
     * most cases. The second method is supported for backwards compatibility.
     */
    function hide_gridlines($option=1) {
        if ($option == 0) {
            $this->_print_gridlines  = 1; # 1 = display, 0 = hide
            $this->_screen_gridlines = 1;
        } elseif ($option == 1) {
            $this->_print_gridlines  = 0;
            $this->_screen_gridlines = 1;
        } else {
            $this->_print_gridlines  = 0;
            $this->_screen_gridlines = 0;
        }
    }

    /*
     * Set the option to print the row and column headers on the printed page.
     * See also the _store_print_headers() method below.
     */
    function print_row_col_headers($headers=1) {
        $this->_print_headers = $headers;
    }

    /*
     * Store the vertical and horizontal number of pages that will define
     * the maximum area printed. See also _store_setup() and _store_wsbool()
     * below.
     */
    function fit_to_pages($width, $height) {
        $this->_fit_page   = 1;
        $this->_fit_width  = $width;
        $this->_fit_height = $height;
    }

    /*
     * Store the horizontal page breaks on a worksheet.
     */
    function set_h_pagebreaks($breaks) {
        $this->_hbreaks=array_merge($this->_hbreaks, $breaks);
    }

    /*
     * Store the vertical page breaks on a worksheet.
     */
    function set_v_pagebreaks($breaks) {
        $this->_vbreaks=array_merge($this->_vbreaks, $breaks);
    }

    /*
     * Set the worksheet zoom factor.
     */
    function set_zoom($scale=100) {
        // Confine the scale to Excel's range
        if ($scale < 10 || $scale > 400) {
            trigger_error("Zoom factor $scale outside range: ".
                          "10 <= zoom <= 400", E_USER_WARNING);
            $scale = 100;
        }

        $this->_zoom = $scale;
    }

    /*
     * Set the scale factor for the printed page.
     */
    function set_print_scale($scale=100) {
        // Confine the scale to Excel's range
        if ($scale < 10 || $scale > 400) {
            trigger_error("Print scale $scale outside range: ".
                          "10 <= zoom <= 400", E_USER_WARNING);
            $scale = 100;
        }

        // Turn off "fit to page" option
        $this->_fit_page = 0;

        $this->_print_scale = $scale;
    }

###############################################################################
#
# write($row, $col, $token, $format)
#
# Parse $token call appropriate write method. $row and $column are zero
# indexed. $format is optional.
#
# Returns: return value of called subroutine
#
function write() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    $token = $_[2];

    # Match an array ref.
    if (is_array($token)) {
        return call_user_func_array(array(&$this, 'write_row'), $_);
    }

    # Match number
    if (preg_match('/^([+-]?)(?=\d|\.\d)\d*(\.\d*)?([Ee]([+-]?\d+))?$/', $token)) {
        return call_user_func_array(array(&$this, 'write_number'), $_);
    }
    # Match http, https or ftp URL
		/* TODO: Seems to cause problems in opening the resulting xls file...
    elseif (preg_match('|^[fh]tt?ps?://|', $token)) {
        return call_user_func_array(array(&$this, 'write_url'), $_);
    }
		*/
    # Match mailto:
    elseif (preg_match('/^mailto:/', $token)) {
        return call_user_func_array(array(&$this, 'write_url'), $_);
    }
    # Match internal or external sheet link
    elseif (preg_match('[^(?:in|ex)ternal:]', $token)) {
        return call_user_func_array(array(&$this, 'write_url'), $_);
    }
    # Match formula
    elseif (preg_match('/^=/', $token)) {
        return call_user_func_array(array(&$this, 'write_formula'), $_);
    }
    # Match blank
    elseif ($token == '') {
        array_splice($_, 2, 1); # remove the empty string from the parameter list
        return call_user_func_array(array(&$this, 'write_blank'), $_);
    }
    # Default: match string
    else {
        return call_user_func_array(array(&$this, 'write_string'), $_);
    }
}

###############################################################################
#
# write_row($row, $col, $array_ref, $format)
#
# Write a row of data starting from ($row, $col). Call write_col() if any of
# the elements of the array ref are in turn array refs. This allows the writing
# of 1D or 2D arrays of data in one go.
#
# Returns: the first encountered error value or zero for no errors
#
function write_row() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    # Catch non array refs passed by user.
    if (!is_array($_[2])) {
        trigger_error("Not an array ref in call to write_row()!", E_USER_ERROR);
    }

    list($row, $col, $tokens)=array_splice($_, 0, 3);
    $options = $_[0];
    $error   = 0;

    foreach ($tokens as $token) {

        # Check for nested arrays
        if (is_array($token)) {
            $ret = $this->write_col($row, $col, $token, $options);
        } else {
            $ret = $this->write    ($row, $col, $token, $options);
        }

        # Return only the first error encountered, if any.
        $error = $error || $ret;
        $col++;
    }

    return $error;
}

###############################################################################
#
# _XF()
#
# Returns an index to the XF record in the workbook.
# TODO
#
# Note: this is a function, not a method.
#
function _XF($row=false, $col=false, $format=false) {

    if ($format) {
        return $format->get_xf_index();
    } elseif (isset($this->_row_formats[$row])) {
        return $this->_row_formats[$row]->get_xf_index();
    } elseif (isset($this->_col_formats[$col])) {
        return $this->_col_formats[$col]->get_xf_index();
    } else {
        return 0x0F;
    }
}

###############################################################################
#
# write_col($row, $col, $array_ref, $format)
#
# Write a column of data starting from ($row, $col). Call write_row() if any of
# the elements of the array ref are in turn array refs. This allows the writing
# of 1D or 2D arrays of data in one go.
#
# Returns: the first encountered error value or zero for no errors
#
function write_col() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    # Catch non array refs passed by user.
    if (!is_array($_[2])) {
        trigger_error("Not an array ref in call to write_row()!", E_USER_ERROR);
    }

    $row     = array_shift($_);
    $col     = array_shift($_);
    $tokens  = array_shift($_);
    $options = $_;

    $error   = 0;

    foreach ($tokens as $token) {

        # write() will deal with any nested arrays
        $ret = $this->write($row, $col, $token, $options);

        # Return only the first error encountered, if any.
        $error = $error || $ret;
        $row++;
    }

    return $error;
}

###############################################################################
###############################################################################
#
# Internal methods
#

###############################################################################
#
# _append(), overloaded.
#
# Store Worksheet data in memory using the base class _append() or to a
# temporary file, the default.
#
function _append($data) {

    if (func_num_args()>1) {
        trigger_error("writeexcel_worksheet::_append() ".
                      "called with more than one argument", E_USER_ERROR);
    }

    if ($this->_using_tmpfile) {

        if ($this->_debug) {
            print "worksheet::_append() called:";
            for ($c=0;$c<strlen($data);$c++) {
                if ($c%16==0) {
                    print "\n";
                }
                printf("%02X ", ord($data[$c]));
            }
            print "\n";
        }

        # Add CONTINUE records if necessary
        if (strlen($data) > $this->_limit) {
            $data = $this->_add_continue($data);
        }

        fputs($this->_filehandle, $data);
        $this->_datasize += strlen($data);
    } else {
        parent::_append($data);
    }
}

###############################################################################
#
# _substitute_cellref()
#
# Substitute an Excel cell reference in A1 notation for  zero based row and
# column values in an argument list.
#
# Ex: ("A4", "Hello") is converted to (3, 0, "Hello").
#
// Exactly one array must be passed!
function _substitute_cellref($_) {
    $cell = strtoupper(array_shift($_));

    # Convert a column range: 'A:A' or 'B:G'
    if (preg_match('/([A-I]?[A-Z]):([A-I]?[A-Z])/', $cell, $reg)) {
        list($dummy, $col1) =  $this->_cell_to_rowcol($reg[1] .'1'); # Add a dummy row
        list($dummy, $col2) =  $this->_cell_to_rowcol($reg[2] .'1'); # Add a dummy row
        return array_merge(array($col1, $col2), $_);
    }

    # Convert a cell range: 'A1:B7'
    if (preg_match('/\$?([A-I]?[A-Z]\$?\d+):\$?([A-I]?[A-Z]\$?\d+)/', $cell, $reg)) {
        list($row1, $col1) =  $this->_cell_to_rowcol($reg[1]);
        list($row2, $col2) =  $this->_cell_to_rowcol($reg[2]);
        return array_merge(array($row1, $col1, $row2, $col2), $_);
    }

    # Convert a cell reference: 'A1' or 'AD2000'
    if (preg_match('/\$?([A-I]?[A-Z]\$?\d+)/', $cell, $reg)) {
        list($row1, $col1) =  $this->_cell_to_rowcol($reg[1]);
        return array_merge(array($row1, $col1), $_);

    }

    trigger_error("Unknown cell reference $cell", E_USER_ERROR);
}

###############################################################################
#
# _cell_to_rowcol($cell_ref)
#
# Convert an Excel cell reference in A1 notation to a zero based row and column
# reference; converts C1 to (0, 2).
#
# Returns: row, column
#
# TODO use functions in Utility.pm
#
function _cell_to_rowcol($cell) {

    preg_match('/\$?([A-I]?[A-Z])\$?(\d+)/', $cell, $reg);

    $col     = $reg[1];
    $row     = $reg[2];

    # Convert base26 column string to number
    # All your Base are belong to us.
    $chars = preg_split('//', $col, -1, PREG_SPLIT_NO_EMPTY);
    $expn  = 0;
    $col      = 0;

    while (sizeof($chars)) {
        $char = array_pop($chars); # LS char first
        $col += (ord($char) -ord('A') +1) * pow(26, $expn);
        $expn++;
    }

    # Convert 1-index to zero-index
    $row--;
    $col--;

    return array($row, $col);
}

    /*
     * This is an internal method that is used to filter elements of the
     * array of pagebreaks used in the _store_hbreak() and _store_vbreak()
     * methods. It:
     *   1. Removes duplicate entries from the list.
     *   2. Sorts the list.
     *   3. Removes 0 from the list if present.
     */
    function _sort_pagebreaks($breaks) {
        // Hash slice to remove duplicates
        foreach ($breaks as $break) {
            $hash["$break"]=1;
        }

        // Numerical sort
        $breaks=array_keys($hash);
        sort($breaks, SORT_NUMERIC);

        // Remove zero
        if ($breaks[0] == 0) {
            array_shift($breaks);
        }

        // 1000 vertical pagebreaks appears to be an internal Excel 5 limit.
        // It is slightly higher in Excel 97/200, approx. 1026
        if (sizeof($breaks) > 1000) {
            array_splice($breaks, 1000);
        }

        return $breaks;
    }

    /*
     * Based on the algorithm provided by Daniel Rentz of OpenOffice.
     */
    function _encode_password($plaintext) {
        $chars=preg_split('//', $plaintext, -1, PREG_SPLIT_NO_EMPTY);
        $count=sizeof($chars);
        $i=0;

        for ($c=0;$c<sizeof($chars);$c++) {
            $char=&$chars[$c];
            $char    = ord($char) << ++$i;
            $low_15  = $char & 0x7fff;
            $high_15 = $char & 0x7fff << 15;
            $high_15 = $high_15 >> 15;
            $char    = $low_15 | $high_15;
        }

        $password = 0x0000;

        foreach ($chars as $char) {
            $password ^= $char;
        }

        $password ^= $count;
        $password ^= 0xCE4B;

        return $password;
    }

###############################################################################
###############################################################################
#
# BIFF RECORDS
#

###############################################################################
#
# write_number($row, $col, $num, $format)
#
# Write a double to the specified row and column (zero indexed).
# An integer can be written as a double. Excel will display an
# integer. $format is optional.
#
# Returns  0 : normal termination
#         -1 : insufficient number of arguments
#         -2 : row or column out of range
#
function write_number() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    # Check the number of args
    if (sizeof($_) < 3) {
        return -1;
    }

    $record  = 0x0203;                        # Record identifier
    $length  = 0x000E;                        # Number of bytes to follow

    $row     = $_[0];                         # Zero indexed row
    $col     = $_[1];                         # Zero indexed column
    $num     = $_[2];
//!!!
    $xf      = $this->_XF($row, $col, $_[3]); # The cell format

    # Check that row and col are valid and store max and min values
    if ($row >= $this->_xls_rowmax) { return -2; }
    if ($col >= $this->_xls_colmax) { return -2; }
    if ($row <  $this->_dim_rowmin) { $this->_dim_rowmin = $row; }
    if ($row >  $this->_dim_rowmax) { $this->_dim_rowmax = $row; }
    if ($col <  $this->_dim_colmin) { $this->_dim_colmin = $col; }
    if ($col >  $this->_dim_colmax) { $this->_dim_colmax = $col; }

    $header    = pack("vv",  $record, $length);
    $data      = pack("vvv", $row, $col, $xf);
    $xl_double = pack("d",   $num);

    if ($this->_byte_order) {
//TODO
        $xl_double = strrev($xl_double);
    }

    $this->_append($header . $data . $xl_double);

    return 0;
}

###############################################################################
#
# write_string ($row, $col, $string, $format)
#
# Write a string to the specified row and column (zero indexed).
# NOTE: there is an Excel 5 defined limit of 255 characters.
# $format is optional.
# Returns  0 : normal termination
#         -1 : insufficient number of arguments
#         -2 : row or column out of range
#         -3 : long string truncated to 255 chars
#
function write_string() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    # Check the number of args
    if (sizeof($_) < 3) {
        return -1;
    }

    $record  = 0x0204;                        # Record identifier
    $length  = 0x0008 + strlen($_[2]);        # Bytes to follow

    $row     = $_[0];                         # Zero indexed row
    $col     = $_[1];                         # Zero indexed column
    $strlen  = strlen($_[2]);
    $str     = $_[2];
    $xf      = $this->_XF($row, $col, $_[3]); # The cell format

    $str_error = 0;

    # Check that row and col are valid and store max and min values
    if ($row >= $this->_xls_rowmax) { return -2; }
    if ($col >= $this->_xls_colmax) { return -2; }
    if ($row <  $this->_dim_rowmin) { $this->_dim_rowmin = $row; }
    if ($row >  $this->_dim_rowmax) { $this->_dim_rowmax = $row; }
    if ($col <  $this->_dim_colmin) { $this->_dim_colmin = $col; }
    if ($col >  $this->_dim_colmax) { $this->_dim_colmax = $col; }

    if ($strlen > $this->_xls_strmax) { # LABEL must be < 255 chars
        $str       = substr($str, 0, $this->_xls_strmax);
        $length    = 0x0008 + $this->_xls_strmax;
        $strlen    = $this->_xls_strmax;
        $str_error = -3;
    }

    $header    = pack("vv",   $record, $length);
    $data      = pack("vvvv", $row, $col, $xf, $strlen);

    $this->_append($header . $data . $str);

    return $str_error;
}

###############################################################################
#
# write_blank($row, $col, $format)
#
# Write a blank cell to the specified row and column (zero indexed).
# A blank cell is used to specify formatting without adding a string
# or a number.
#
# A blank cell without a format serves no purpose. Therefore, we don't write
# a BLANK record unless a format is specified. This is mainly an optimisation
# for the write_row() and write_col() methods.
#
# Returns  0 : normal termination (including no format)
#         -1 : insufficient number of arguments
#         -2 : row or column out of range
#
function write_blank() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    # Check the number of args
    if (sizeof($_) < 2) {
        return -1;
    }

    # Don't write a blank cell unless it has a format
    if (!isset($_[2])) {
        return 0;
    }

    $record  = 0x0201;                        # Record identifier
    $length  = 0x0006;                        # Number of bytes to follow

    $row     = $_[0];                         # Zero indexed row
    $col     = $_[1];                         # Zero indexed column
    $xf      = $this->_XF($row, $col, $_[2]); # The cell format

    # Check that row and col are valid and store max and min values
    if ($row >= $this->_xls_rowmax) { return -2; }
    if ($col >= $this->_xls_colmax) { return -2; }
    if ($row <  $this->_dim_rowmin) { $this->_dim_rowmin = $row; }
    if ($row >  $this->_dim_rowmax) { $this->_dim_rowmax = $row; }
    if ($col <  $this->_dim_colmin) { $this->_dim_colmin = $col; }
    if ($col >  $this->_dim_colmax) { $this->_dim_colmax = $col; }

    $header    = pack("vv",  $record, $length);
    $data      = pack("vvv", $row, $col, $xf);

    $this->_append($header . $data);

    return 0;
}

###############################################################################
#
# write_formula($row, $col, $formula, $format)
#
# Write a formula to the specified row and column (zero indexed).
# The textual representation of the formula is passed to the parser in
# Formula.pm which returns a packed binary string.
#
# $format is optional.
#
# Returns  0 : normal termination
#         -1 : insufficient number of arguments
#         -2 : row or column out of range
#
function write_formula() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    # Check the number of args
    if (sizeof($_) < 3) {
        return -1;
    }

    $record    = 0x0006;     # Record identifier
    $length=0;                 # Bytes to follow

    $row       = $_[0];      # Zero indexed row
    $col       = $_[1];      # Zero indexed column
    $formula   = $_[2];      # The formula text string

    # Excel normally stores the last calculated value of the formula in $num.
    # Clearly we are not in a position to calculate this a priori. Instead
    # we set $num to zero and set the option flags in $grbit to ensure
    # automatic calculation of the formula when the file is opened.
    #
    $xf        = $this->_XF($row, $col, $_[3]); # The cell format
    $num       = 0x00;                          # Current value of formula
    $grbit     = 0x03;                          # Option flags
    $chn       = 0x0000;                        # Must be zero

    # Check that row and col are valid and store max and min values
    if ($row >= $this->_xls_rowmax) { return -2; }
    if ($col >= $this->_xls_colmax) { return -2; }
    if ($row <  $this->_dim_rowmin) { $this->_dim_rowmin = $row; }
    if ($row >  $this->_dim_rowmax) { $this->_dim_rowmax = $row; }
    if ($col <  $this->_dim_colmin) { $this->_dim_colmin = $col; }
    if ($col >  $this->_dim_colmax) { $this->_dim_colmax = $col; }

    # Strip the = sign at the beginning of the formula string
    $formula = preg_replace('/^=/', "", $formula);

    # Parse the formula using the parser in Formula.pm
    $parser =& $this->_parser;
    $formula   = $parser->parse_formula($formula);

    $formlen = strlen($formula); # Length of the binary string
    $length     = 0x16 + $formlen;  # Length of the record data

    $header    = pack("vv",      $record, $length);
    $data      = pack("vvvdvVv", $row, $col, $xf, $num,
                                  $grbit, $chn, $formlen);

    $this->_append($header . $data . $formula);

    return 0;
}

###############################################################################
#
# write_url($row, $col, $url, $string, $format)
#
# Write a hyperlink. This is comprised of two elements: the visible label and
# the invisible link. The visible label is the same as the link unless an
# alternative string is specified. The label is written using the
# write_string() method. Therefore the 255 characters string limit applies.
# $string and $format are optional and their order is interchangeable.
#
# The hyperlink can be to a http, ftp, mail, internal sheet, or external
# directory url.
#
# Returns  0 : normal termination
#         -1 : insufficient number of arguments
#         -2 : row or column out of range
#         -3 : long string truncated to 255 chars
#
function write_url() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    # Check the number of args
    if (sizeof($_) < 3) {
        return -1;
    }

    # Add start row and col to arg list
    return call_user_func_array(array(&$this, 'write_url_range'),
                                  array_merge(array($_[0], $_[1]), $_));
}

###############################################################################
#
# write_url_range($row1, $col1, $row2, $col2, $url, $string, $format)
#
# This is the more general form of write_url(). It allows a hyperlink to be
# written to a range of cells. This function also decides the type of hyperlink
# to be written. These are either, Web (http, ftp, mailto), Internal
# (Sheet1!A1) or external ('c:\temp\foo.xls#Sheet1!A1').
#
# See also write_url() above for a general description and return values.
#
function write_url_range() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    # Check the number of args
    if (sizeof($_) < 5) {
        return -1;
    }

    # Reverse the order of $string and $format if necessary.
//TODO    ($_[5], $_[6]) = ($_[6], $_[5]) if (ref $_[5]);

    $url = $_[4];

    # Check for internal/external sheet links or default to web link
    if (preg_match('[^internal:]', $url)) {
        return call_user_func_array(array(&$this, '_write_url_internal'), $_);
    }

    if (preg_match('[^external:]', $url)) {
        return call_user_func_array(array(&$this, '_write_url_external'), $_);
    }

    return call_user_func_array(array(&$this, '_write_url_web'), $_);
}

###############################################################################
#
# _write_url_web($row1, $col1, $row2, $col2, $url, $string, $format)
#
# Used to write http, ftp and mailto hyperlinks.
# The link type ($options) is 0x03 is the same as absolute dir ref without
# sheet. However it is differentiated by the $unknown2 data stream.
#
# See also write_url() above for a general description and return values.
#
function _write_url_web() {

    $_=func_get_args();

    $record      = 0x01B8;                       # Record identifier
    $length      = 0x00000;                      # Bytes to follow

    $row1        = $_[0];                        # Start row
    $col1        = $_[1];                        # Start column
    $row2        = $_[2];                        # End row
    $col2        = $_[3];                        # End column
    $url         = $_[4];                        # URL string
    if (isset($_[5])) {
        $str         = $_[5];                        # Alternative label
    }
    $xf          = $_[6] ? $_[6] : $this->_url_format;  # The cell format

    # Write the visible label using the write_string() method.
    if(!isset($str)) {
        $str            = $url;
    }

    $str_error   = $this->write_string($row1, $col1, $str, $xf);

    if ($str_error == -2) {
        return $str_error;
    }

    # Pack the undocumented parts of the hyperlink stream
    $unknown1    = pack("H*", "D0C9EA79F9BACE118C8200AA004BA90B02000000");
    $unknown2    = pack("H*", "E0C9EA79F9BACE118C8200AA004BA90B");

    # Pack the option flags
    $options     = pack("V", 0x03);

    # Convert URL to a null terminated wchar string
    $url            = join("\0", preg_split("''", $url, -1, PREG_SPLIT_NO_EMPTY));
    $url            = $url . "\0\0\0";

    # Pack the length of the URL
    $url_len     = pack("V", strlen($url));

    # Calculate the data length
    $length         = 0x34 + strlen($url);

    # Pack the header data
    $header      = pack("vv",   $record, $length);
    $data        = pack("vvvv", $row1, $row2, $col1, $col2);

    # Write the packed data
    $this->_append($header.
                   $data.
                   $unknown1.
                   $options.
                   $unknown2.
                   $url_len.
                   $url);

    return $str_error;
}

###############################################################################
#
# _write_url_internal($row1, $col1, $row2, $col2, $url, $string, $format)
#
# Used to write internal reference hyperlinks such as "Sheet1!A1".
#
# See also write_url() above for a general description and return values.
#
function _write_url_internal() {

    $_=func_get_args();

    $record      = 0x01B8;                       # Record identifier
    $length      = 0x00000;                      # Bytes to follow

    $row1        = $_[0];                        # Start row
    $col1        = $_[1];                        # Start column
    $row2        = $_[2];                        # End row
    $col2        = $_[3];                        # End column
    $url         = $_[4];                        # URL string
    if (isset($_[5])) {
        $str         = $_[5];                        # Alternative label
    }
    $xf          = $_[6] ? $_[6] : $this->_url_format;  # The cell format

    # Strip URL type
    $url = preg_replace('s[^internal:]', '', $url);

    # Write the visible label
    if (!isset($str)) {
        $str = $url;
    }
    $str_error   = $this->write_string($row1, $col1, $str, $xf);

    if ($str_error == -2) {
        return $str_error;
    }

    # Pack the undocumented parts of the hyperlink stream
    $unknown1    = pack("H*", "D0C9EA79F9BACE118C8200AA004BA90B02000000");

    # Pack the option flags
    $options     = pack("V", 0x08);

    # Convert the URL type and to a null terminated wchar string
    $url            = join("\0", preg_split("''", $url, -1, PREG_SPLIT_NO_EMPTY));
    $url            = $url . "\0\0\0";

    # Pack the length of the URL as chars (not wchars)
    $url_len     = pack("V", int(strlen($url)/2));

    # Calculate the data length
    $length         = 0x24 + strlen($url);

    # Pack the header data
    $header      = pack("vv",   $record, $length);
    $data        = pack("vvvv", $row1, $row2, $col1, $col2);

    # Write the packed data
    $this->_append($header.
                   $data.
                   $unknown1.
                   $options.
                   $url_len.
                   $url);

    return $str_error;
}

###############################################################################
#
# _write_url_external($row1, $col1, $row2, $col2, $url, $string, $format)
#
# Write links to external directory names such as 'c:\foo.xls',
# c:\foo.xls#Sheet1!A1', '../../foo.xls'. and '../../foo.xls#Sheet1!A1'.
#
# Note: Excel writes some relative links with the $dir_long string. We ignore
# these cases for the sake of simpler code.
#
# See also write_url() above for a general description and return values.
#
function _write_url_external() {

    $_=func_get_args();

    # Network drives are different. We will handle them separately
    # MS/Novell network drives and shares start with \\
    if (preg_match('[^external:\\\\]', $_[4])) {
        return call_user_func_array(array(&$this, '_write_url_external_net'), $_);
    }

    $record      = 0x01B8;                       # Record identifier
    $length      = 0x00000;                      # Bytes to follow

    $row1        = $_[0];                        # Start row
    $col1        = $_[1];                        # Start column
    $row2        = $_[2];                        # End row
    $col2        = $_[3];                        # End column
    $url         = $_[4];                        # URL string
    if (isset($_[5])) {
        $str         = $_[5];                        # Alternative label
    }
    $xf          = $_[6] ? $_[6] : $this->_url_format;  # The cell format

    # Strip URL type and change Unix dir separator to Dos style (if needed)
    #
    $url            = preg_replace('[^external:]', '', $url);
    $url            = preg_replace('[/]', "\\", $url);

    # Write the visible label
    if (!isset($str)) {
        $str = preg_replace('[\#]', ' - ', $url);
    }
    $str_error   = $this->write_string($row1, $col1, $str, $xf);
    if ($str_error == -2) {
        return $str_error;
    }

    # Determine if the link is relative or absolute:
    #   relative if link contains no dir separator, "somefile.xls"
    #   relative if link starts with up-dir, "..\..\somefile.xls"
    #   otherwise, absolute
    #
    $absolute    = 0x02; # Bit mask

    if (!preg_match('[\\]', $url)) {
        $absolute    = 0x00;
    }

    if (preg_match('[^\.\.\\]', $url)) {
        $absolute    = 0x00;
    }

    # Determine if the link contains a sheet reference and change some of the
    # parameters accordingly.
    # Split the dir name and sheet name (if it exists)
    #
    list($dir_long, $sheet) = preg_split('/\#/', $url);
    $link_type           = 0x01 | $absolute;

//!!!
    if (isset($sheet)) {
        $link_type |= 0x08;
        $sheet_len  = pack("V", length($sheet) + 0x01);
        $sheet      = join("\0", explode('', $sheet));
        $sheet     .= "\0\0\0";
    } else {
        $sheet_len   = '';
        $sheet       = '';
    }

    # Pack the link type
    $link_type      = pack("V", $link_type);


    # Calculate the up-level dir count e.g.. (..\..\..\ == 3)
/* TODO
    $up_count    = 0;
    $up_count++       while $dir_long =~ s[^\.\.\\][];
    $up_count       = pack("v", $up_count);
*/

    # Store the short dos dir name (null terminated)
    $dir_short   = $dir_long . "\0";

    # Store the long dir name as a wchar string (non-null terminated)
    $dir_long       = join("\0", preg_split('', $dir_long, -1, PREG_SPLIT_NO_EMPTY));
    $dir_long       = $dir_long . "\0";

    # Pack the lengths of the dir strings
    $dir_short_len = pack("V", strlen($dir_short)      );
    $dir_long_len  = pack("V", strlen($dir_long)       );
    $stream_len    = pack("V", strlen($dir_long) + 0x06);

    # Pack the undocumented parts of the hyperlink stream
    $unknown1 =pack("H*",'D0C9EA79F9BACE118C8200AA004BA90B02000000'       );
    $unknown2 =pack("H*",'0303000000000000C000000000000046'               );
    $unknown3 =pack("H*",'FFFFADDE000000000000000000000000000000000000000');
    $unknown4 =pack("v",  0x03                                            );

    # Pack the main data stream
    $data        = pack("vvvv", $row1, $row2, $col1, $col2) .
                      $unknown1     .
                      $link_type    .
                      $unknown2     .
                      $up_count     .
                      $dir_short_len.
                      $dir_short    .
                      $unknown3     .
                      $stream_len   .
                      $dir_long_len .
                      $unknown4     .
                      $dir_long     .
                      $sheet_len    .
                      $sheet        ;

    # Pack the header data
    $length         = strlen($data);
    $header      = pack("vv",   $record, $length);

    # Write the packed data
    $this->_append($header . $data);

    return $str_error;
}

###############################################################################
#
# write_url_xxx($row1, $col1, $row2, $col2, $url, $string, $format)
#
# Write links to external MS/Novell network drives and shares such as
# '//NETWORK/share/foo.xls' and '//NETWORK/share/foo.xls#Sheet1!A1'.
#
# See also write_url() above for a general description and return values.
#
function _write_url_external_net() {

    $_=func_get_args();

    $record      = 0x01B8;                       # Record identifier
    $length      = 0x00000;                      # Bytes to follow

    $row1        = $_[0];                        # Start row
    $col1        = $_[1];                        # Start column
    $row2        = $_[2];                        # End row
    $col2        = $_[3];                        # End column
    $url         = $_[4];                        # URL string
    if(isset($_[5])) {
         $str         = $_[5];                        # Alternative label
    }
    $xf          = $_[6] ? $_[6] : $this->_url_format;  # The cell format

    # Strip URL type and change Unix dir separator to Dos style (if needed)
    #
    $url            = preg_replace('[^external:]', "", $url);
    $url            = preg_replace('[/]', "\\");

    # Write the visible label
    if (!isset($str)) {
        $str = preg_replace('[\#]', " - ", $url);
    }

    $str_error   = $this->write_string($row1, $col1, $str, $xf);
    if ($str_error == -2) {
        return $str_error;
    }

    # Determine if the link contains a sheet reference and change some of the
    # parameters accordingly.
    # Split the dir name and sheet name (if it exists)
    #
    list($dir_long , $sheet) = preg_split('\#', $url);
    $link_type           = 0x0103; # Always absolute

//!!!
    if (isset($sheet)) {
        $link_type |= 0x08;
        $sheet_len  = pack("V", strlen($sheet) + 0x01);
        $sheet      = join("\0", preg_split("''", $sheet, -1, PREG_SPLIT_NO_EMPTY));
        $sheet     .= "\0\0\0";
    } else {
        $sheet_len   = '';
        $sheet       = '';
    }

    # Pack the link type
    $link_type      = pack("V", $link_type);

    # Make the string null terminated
    $dir_long       = $dir_long . "\0";

    # Pack the lengths of the dir string
    $dir_long_len  = pack("V", strlen($dir_long));

    # Store the long dir name as a wchar string (non-null terminated)
    $dir_long       = join("\0", preg_split("''", $dir_long, -1, PREG_SPLIT_NO_EMPTY));
    $dir_long       = $dir_long . "\0";

    # Pack the undocumented part of the hyperlink stream
    $unknown1    = pack("H*",'D0C9EA79F9BACE118C8200AA004BA90B02000000');

    # Pack the main data stream
    $data        = pack("vvvv", $row1, $row2, $col1, $col2) .
                      $unknown1     .
                      $link_type    .
                      $dir_long_len .
                      $dir_long     .
                      $sheet_len    .
                      $sheet        ;

    # Pack the header data
    $length         = strlen($data);
    $header      = pack("vv",   $record, $length);

    # Write the packed data
    $this->_append($header . $data);

    return $str_error;
}

###############################################################################
#
# set_row($row, $height, $XF)
#
# This method is used to set the height and XF format for a row.
# Writes the  BIFF record ROW.
#
function set_row() {

    $_=func_get_args();

    $record      = 0x0208;               # Record identifier
    $length      = 0x0010;               # Number of bytes to follow

    $rw          = $_[0];                # Row Number
    $colMic      = 0x0000;               # First defined column
    $colMac      = 0x0000;               # Last defined column
    //$miyRw;                              # Row height
    $irwMac      = 0x0000;               # Used by Excel to optimise loading
    $reserved    = 0x0000;               # Reserved
    $grbit       = 0x01C0;               # Option flags. (monkey) see $1 do
    //$ixfe;                               # XF index
    if (isset($_[2])) {
        $format      = $_[2];                # Format object
    }

    # Check for a format object
    if (isset($_[2])) {
        $ixfe = $format->get_xf_index();
    } else {
        $ixfe = 0x0F;
    }

    # Use set_row($row, undef, $XF) to set XF without setting height
    if (isset($_[1])) {
        $miyRw = $_[1] *20;
    } else {
        $miyRw = 0xff;
    }

    $header   = pack("vv",       $record, $length);
    $data     = pack("vvvvvvvv", $rw, $colMic, $colMac, $miyRw,
                                 $irwMac,$reserved, $grbit, $ixfe);

    $this->_append($header . $data);

    # Store the row sizes for use when calculating image vertices.
    # Also store the column formats.
    #
    # Ensure at least $row and $height
    if (sizeof($_) < 2) {
        return;
    }

    $this->_row_sizes[$_[0]]  = $_[1];
    if (isset($_[2])) {
        $this->_row_formats[$_[0]] = $_[2];
    }
}

    /*
     * Writes Excel DIMENSIONS to define the area in which there is data.
     */
    function _store_dimensions() {
        $record    = 0x0000;               // Record identifier
        $length    = 0x000A;               // Number of bytes to follow
        $row_min   = $this->_dim_rowmin;   // First row
        $row_max   = $this->_dim_rowmax;   // Last row plus 1
        $col_min   = $this->_dim_colmin;   // First column
        $col_max   = $this->_dim_colmax;   // Last column plus 1
        $reserved  = 0x0000;               // Reserved by Excel

        $header    = pack("vv",    $record, $length);
        $data      = pack("vvvvv", $row_min, $row_max,
                                   $col_min, $col_max, $reserved);
        $this->_prepend($header . $data);
    }

    /*
     * Write BIFF record Window2.
     */
    function _store_window2() {
        $record         = 0x023E;       // Record identifier
        $length         = 0x000A;       // Number of bytes to follow

        $grbit          = 0x00B6;       // Option flags
        $rwTop          = 0x0000;       // Top row visible in window
        $colLeft        = 0x0000;       // Leftmost column visible in window
        $rgbHdr         = 0x00000000;   // Row/column heading and gridline
                                        // color

        // The options flags that comprise $grbit
        $fDspFmla       = 0;                          // 0 - bit
        $fDspGrid       = $this->_screen_gridlines;   // 1
        $fDspRwCol      = 1;                          // 2
        $fFrozen        = $this->_frozen;             // 3
        $fDspZeros      = 1;                          // 4
        $fDefaultHdr    = 1;                          // 5
        $fArabic        = 0;                          // 6
        $fDspGuts       = 1;                          // 7
        $fFrozenNoSplit = 0;                          // 0 - bit
        $fSelected      = $this->_selected;           // 1
        $fPaged         = 1;                          // 2

        $grbit             = $fDspFmla;
        $grbit            |= $fDspGrid       << 1;
        $grbit            |= $fDspRwCol      << 2;
        $grbit            |= $fFrozen        << 3;
        $grbit            |= $fDspZeros      << 4;
        $grbit            |= $fDefaultHdr    << 5;
        $grbit            |= $fArabic        << 6;
        $grbit            |= $fDspGuts       << 7;
        $grbit            |= $fFrozenNoSplit << 8;
        $grbit            |= $fSelected      << 9;
        $grbit            |= $fPaged         << 10;

        $header  = pack("vv",   $record, $length);
        $data    = pack("vvvV", $grbit, $rwTop, $colLeft, $rgbHdr);

        $this->_append($header . $data);
    }

    /*
     * Write BIFF record DEFCOLWIDTH if COLINFO records are in use.
     */
    function _store_defcol() {
        $record   = 0x0055;   // Record identifier
        $length   = 0x0002;   // Number of bytes to follow

        $colwidth = 0x0008;   // Default column width

        $header   = pack("vv", $record, $length);
        $data     = pack("v",  $colwidth);

        $this->_prepend($header . $data);
    }

###############################################################################
#
# _store_colinfo($firstcol, $lastcol, $width, $format, $hidden)
#
# Write BIFF record COLINFO to define column widths
#
# Note: The SDK says the record length is 0x0B but Excel writes a 0x0C
# length record.
#
function _store_colinfo($_) {

    $record   = 0x007D;          # Record identifier
    $length   = 0x000B;          # Number of bytes to follow

    $colFirst = $_[0] ? $_[0] : 0;      # First formatted column
    $colLast  = $_[1] ? $_[1] : 0;      # Last formatted column
    $coldx    = $_[2] ? $_[2] : 8.43;   # Col width, 8.43 is Excel default

    $coldx       += 0.72;           # Fudge. Excel subtracts 0.72 !?
    $coldx       *= 256;            # Convert to units of 1/256 of a char

    //$ixfe;                       # XF index
    $grbit    = $_[4] || 0;      # Option flags
    $reserved = 0x00;            # Reserved
    $format   = $_[3];           # Format object

    # Check for a format object
    if (isset($_[3])) {
        $ixfe = $format->get_xf_index();
    } else {
        $ixfe = 0x0F;
    }

    $header   = pack("vv",     $record, $length);
    $data     = pack("vvvvvC", $colFirst, $colLast, $coldx,
                               $ixfe, $grbit, $reserved);
    $this->_prepend($header . $data);
}

###############################################################################
#
# _store_selection($first_row, $first_col, $last_row, $last_col)
#
# Write BIFF record SELECTION.
#
function _store_selection($_) {

    $record   = 0x001D;                  # Record identifier
    $length   = 0x000F;                  # Number of bytes to follow

    $pnn      = $this->_active_pane;     # Pane position
    $rwAct    = $_[0];                   # Active row
    $colAct   = $_[1];                   # Active column
    $irefAct  = 0;                       # Active cell ref
    $cref     = 1;                       # Number of refs

    $rwFirst  = $_[0];                   # First row in reference
    $colFirst = $_[1];                   # First col in reference
    $rwLast   = $_[2] ? $_[2] : $rwFirst;       # Last  row in reference
    $colLast  = $_[3] ? $_[3] : $colFirst;      # Last  col in reference

    # Swap last row/col for first row/col as necessary
    if ($rwFirst > $rwLast) {
        list($rwFirst, $rwLast) = array($rwLast, $rwFirst);
    }

    if ($colFirst > $colLast) {
        list($colFirst, $colLast) = array($colLast, $colFirst);
    }

    $header   = pack("vv",           $record, $length);
    $data     = pack("CvvvvvvCC",    $pnn, $rwAct, $colAct,
                                     $irefAct, $cref,
                                     $rwFirst, $rwLast,
                                     $colFirst, $colLast);

    $this->_append($header . $data);
}

    /*
     * Write BIFF record EXTERNCOUNT to indicate the number of external
     * sheet references in a worksheet.
     *
     * Excel only stores references to external sheets that are used in
     * formulas. For simplicity we store references to all the sheets in
     * the workbook regardless of whether they are used or not. This reduces
     * the overall complexity and eliminates the need for a two way dialogue
     * between the formula parser the worksheet objects.
     */
    function _store_externcount($cxals) {
        // $cxals   Number of external references

        $record   = 0x0016;   // Record identifier
        $length   = 0x0002;   // Number of bytes to follow

        $header   = pack("vv", $record, $length);
        $data     = pack("v",  $cxals);

        $this->_prepend($header . $data);
    }

    /*
     * Writes the Excel BIFF EXTERNSHEET record. These references are used
     * by formulas. A formula references a sheet name via an index. Since we
     * store a reference to all of the external worksheets the EXTERNSHEET
     * index is the same as the worksheet index.
     */
    function _store_externsheet($sheetname) {
        $record    = 0x0017;         # Record identifier
        // $length   Number of bytes to follow

        // $cch      Length of sheet name
        // $rgch     Filename encoding

        // References to the current sheet are encoded differently to
        // references to external sheets.
        if ($this->_name == $sheetname) {
            $sheetname = '';
            $length    = 0x02;  // The following 2 bytes
            $cch       = 1;     // The following byte
            $rgch      = 0x02;  // Self reference
        } else {
            $length    = 0x02 + strlen($sheetname);
            $cch       = strlen($sheetname);
            $rgch      = 0x03;  // Reference to a sheet in the current
                                // workbook
        }

        $header     = pack("vv",  $record, $length);
        $data       = pack("CC", $cch, $rgch);

        $this->_prepend($header . $data . $sheetname);
    }

###############################################################################
#
# _store_panes()
#
#
# Writes the Excel BIFF PANE record.
# The panes can either be frozen or thawed (unfrozen).
# Frozen panes are specified in terms of a integer number of rows and columns.
# Thawed panes are specified in terms of Excel's units for rows and columns.
#
function _store_panes($_) {

    $record  = 0x0041;       # Record identifier
    $length  = 0x000A;       # Number of bytes to follow

    $y       = $_[0] ? $_[0] : 0;   # Vertical split position
    $x       = $_[1] ? $_[1] : 0;   # Horizontal split position
    if (isset($_[2])) {
        $rwTop   = $_[2];        # Top row visible
    }
    if (isset($_[3])) {
        $colLeft = $_[3];        # Leftmost column visible
    }
    if (isset($_[4])) {
        $pnnAct  = $_[4];        # Active pane
    }

    # Code specific to frozen or thawed panes.
    if ($this->_frozen) {
        # Set default values for $rwTop and $colLeft
        if (!isset($rwTop)) {
            $rwTop   = $y;
        }
        if (!isset($colLeft)) {
            $colLeft = $x;
        }
    } else {
        # Set default values for $rwTop and $colLeft
        if (!isset($rwTop)) {
            $rwTop   = 0;
        }
        if (!isset($colLeft)) {
            $colLeft = 0;
        }

        # Convert Excel's row and column units to the internal units.
        # The default row height is 12.75
        # The default column width is 8.43
        # The following slope and intersection values were interpolated.
        #
        $y = 20*$y      + 255;
        $x = 113.879*$x + 390;
    }

    # Determine which pane should be active. There is also the undocumented
    # option to override this should it be necessary: may be removed later.
    #
    if (!isset($pnnAct)) {
        # Bottom right
        if ($x != 0 && $y != 0) {
            $pnnAct = 0;
        }
        # Top right
        if ($x != 0 && $y == 0) {
            $pnnAct = 1;
        }
        # Bottom left
        if ($x == 0 && $y != 0) {
            $pnnAct = 2;
        }
        # Top left
        if ($x == 0 && $y == 0) {
            $pnnAct = 3;
        }
    }

    $this->_active_pane = $pnnAct; # Used in _store_selection

    $header     = pack("vv",    $record, $length);
    $data       = pack("vvvvv", $x, $y, $rwTop, $colLeft, $pnnAct);

    $this->_append($header . $data);
}

    /*
     * Store the page setup SETUP BIFF record.
     */
    function _store_setup() {
        $record       = 0x00A1;                // Record identifier
        $length       = 0x0022;                // Number of bytes to follow

        $iPaperSize   = $this->_paper_size;    // Paper size
        $iScale       = $this->_print_scale;   // Print scaling factor
        $iPageStart   = 0x01;                  // Starting page number
        $iFitWidth    = $this->_fit_width;     // Fit to number of pages wide
        $iFitHeight   = $this->_fit_height;    // Fit to number of pages high
        $grbit        = 0x00;                  // Option flags
        $iRes         = 0x0258;                // Print resolution
        $iVRes        = 0x0258;                // Vertical print resolution
        $numHdr       = $this->_margin_head;   // Header Margin
        $numFtr       = $this->_margin_foot;   // Footer Margin
        $iCopies      = 0x01;                  // Number of copies

        $fLeftToRight = 0x0;                   // Print over then down
        $fLandscape   = $this->_orientation;   // Page orientation
        $fNoPls       = 0x0;                   // Setup not read from printer
        $fNoColor     = 0x0;                   // Print black and white
        $fDraft       = 0x0;                   // Print draft quality
        $fNotes       = 0x0;                   // Print notes
        $fNoOrient    = 0x0;                   // Orientation not set
        $fUsePage     = 0x0;                   // Use custom starting page

        $grbit        = $fLeftToRight;
        $grbit       |= $fLandscape    << 1;
        $grbit       |= $fNoPls        << 2;
        $grbit       |= $fNoColor      << 3;
        $grbit       |= $fDraft        << 4;
        $grbit       |= $fNotes        << 5;
        $grbit       |= $fNoOrient     << 6;
        $grbit       |= $fUsePage      << 7;

        $numHdr = pack("d", $numHdr);
        $numFtr = pack("d", $numFtr);

        if ($this->_byte_order) {
            $numHdr = strrev($numHdr);
            $numFtr = strrev($numFtr);
        }

        $header = pack("vv",         $record, $length);
        $data1  = pack("vvvvvvvv",   $iPaperSize,
                                     $iScale,
                                     $iPageStart,
                                     $iFitWidth,
                                     $iFitHeight,
                                     $grbit,
                                     $iRes,
                                     $iVRes);
        $data2  = $numHdr . $numFtr;
        $data3  = pack("v", $iCopies);

        $this->_prepend($header . $data1 . $data2 . $data3);
    }

    /*
     * Store the header caption BIFF record.
     */
    function _store_header() {
        $record  = 0x0014;           // Record identifier

        $str     = $this->_header;   // header string
        $cch     = strlen($str);     // Length of header string
        $length  = 1 + $cch;         // Bytes to follow

        $header  = pack("vv",  $record, $length);
        $data    = pack("C",   $cch);

        $this->_append($header . $data . $str);
    }

    /*
     * Store the footer caption BIFF record.
     */
    function _store_footer() {
        $record  = 0x0015;           // Record identifier

        $str     = $this->_footer;   // Footer string
        $cch     = strlen($str);     // Length of footer string
        $length  = 1 + $cch;         // Bytes to follow

        $header  = pack("vv",  $record, $length);
        $data    = pack("C",   $cch);

        $this->_append($header . $data . $str);
    }

    /*
     * Store the horizontal centering HCENTER BIFF record.
     */
    function _store_hcenter() {
        $record   = 0x0083;   // Record identifier
        $length   = 0x0002;   // Bytes to follow

        $fHCenter = $this->_hcenter;   // Horizontal centering

        $header   = pack("vv",  $record, $length);
        $data     = pack("v",   $fHCenter);

        $this->_append($header . $data);
    }

     /*
      * Store the vertical centering VCENTER BIFF record.
      */
    function _store_vcenter() {
        $record   = 0x0084;   // Record identifier
        $length   = 0x0002;   // Bytes to follow

        $fVCenter = $this->_vcenter;   // Horizontal centering

        $header   = pack("vv",  $record, $length);
        $data     = pack("v",   $fVCenter);

        $this->_append($header . $data);
    }

    /*
     * Store the LEFTMARGIN BIFF record.
     */
    function _store_margin_left() {
        $record  = 0x0026;   // Record identifier
        $length  = 0x0008;   // Bytes to follow

        $margin  = $this->_margin_left;   // Margin in inches

        $header  = pack("vv",  $record, $length);
        $data    = pack("d",   $margin);

        if ($this->_byte_order) {
            $data = strrev($data);
        }

        $this->_append($header . $data);
    }

    /*
     * Store the RIGHTMARGIN BIFF record.
     */
    function _store_margin_right() {
        $record  = 0x0027;   // Record identifier
        $length  = 0x0008;   // Bytes to follow

        $margin  = $this->_margin_right;   // Margin in inches

        $header  = pack("vv",  $record, $length);
        $data    = pack("d",   $margin);

        if ($this->_byte_order) {
            $data = strrev($data);
        }

        $this->_append($header . $data);
    }

    /*
     * Store the TOPMARGIN BIFF record.
     */
    function _store_margin_top() {
        $record  = 0x0028;   // Record identifier
        $length  = 0x0008;   // Bytes to follow

        $margin  = $this->_margin_top;   // Margin in inches

        $header  = pack("vv",  $record, $length);
        $data    = pack("d",   $margin);

        if ($this->_byte_order) {
            $data = strrev($data);
        }

        $this->_append($header . $data);
    }

    /*
     * Store the BOTTOMMARGIN BIFF record.
     */
    function _store_margin_bottom() {
        $record  = 0x0029;   // Record identifier
        $length  = 0x0008;   // Bytes to follow

        $margin  = $this->_margin_bottom;   // Margin in inches

        $header  = pack("vv",  $record, $length);
        $data    = pack("d",   $margin);

        if ($this->_byte_order) {
            $data = strrev($data);
        }

        $this->_append($header . $data);
    }

###############################################################################
#
# merge_cells($first_row, $first_col, $last_row, $last_col)
#
# This is an Excel97/2000 method. It is required to perform more complicated
# merging than the normal align merge in Format.pm
#
function merge_cells() {

    $_=func_get_args();

    // Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    $record  = 0x00E5;                   # Record identifier
    $length  = 0x000A;                   # Bytes to follow

    $cref     = 1;                       # Number of refs
    $rwFirst  = $_[0];                   # First row in reference
    $colFirst = $_[1];                   # First col in reference
    $rwLast   = $_[2] ? $_[2] : $rwFirst;       # Last  row in reference
    $colLast  = $_[3] ? $_[3] : $colFirst;      # Last  col in reference

    // Swap last row/col for first row/col as necessary
    if ($rwFirst > $rwLast) {
        list($rwFirst, $rwLast) = array($rwLast, $rwFirst);
    }

    if ($colFirst > $colLast) {
        list($colFirst, $colLast) = array($colLast, $colFirst);
    }

    $header   = pack("vv",       $record, $length);
    $data     = pack("vvvvv",    $cref,
                                 $rwFirst, $rwLast,
                                 $colFirst, $colLast);

    $this->_append($header . $data);
}

    /*
     * Write the PRINTHEADERS BIFF record.
     */
    function _store_print_headers() {
        $record      = 0x002a;   // Record identifier
        $length      = 0x0002;   // Bytes to follow

        $fPrintRwCol = $this->_print_headers;   // Boolean flag

        $header      = pack("vv",  $record, $length);
        $data        = pack("v",   $fPrintRwCol);

        $this->_prepend($header . $data);
    }

    /*
     * Write the PRINTGRIDLINES BIFF record. Must be used in conjunction
     * with the GRIDSET record.
     */
    function _store_print_gridlines() {
        $record      = 0x002b;   // Record identifier
        $length      = 0x0002;   // Bytes to follow

        $fPrintGrid  = $this->_print_gridlines;   // Boolean flag

        $header      = pack("vv",  $record, $length);
        $data        = pack("v",   $fPrintGrid);

        $this->_prepend($header . $data);
    }

    /*
     * Write the GRIDSET BIFF record. Must be used in conjunction with the
     * PRINTGRIDLINES record.
     */
    function _store_gridset() {
        $record      = 0x0082;   // Record identifier
        $length      = 0x0002;   // Bytes to follow

        $fGridSet    = !$this->_print_gridlines;   // Boolean flag

        $header      = pack("vv",  $record, $length);
        $data        = pack("v",   $fGridSet);

        $this->_prepend($header . $data);
    }

    /*
     * Write the WSBOOL BIFF record, mainly for fit-to-page. Used in
     * conjunction with the SETUP record.
     */
    function _store_wsbool() {
        $record      = 0x0081;   # Record identifier
        $length      = 0x0002;   # Bytes to follow

        // $grbit   Option flags

        // The only option that is of interest is the flag for fit to page.
        // So we set all the options in one go.
        if ($this->_fit_page) {
            $grbit = 0x05c1;
        } else {
            $grbit = 0x04c1;
        }

        $header      = pack("vv",  $record, $length);
        $data        = pack("v",   $grbit);

        $this->_prepend($header . $data);
    }

    /*
     * Write the HORIZONTALPAGEBREAKS BIFF record.
     */
    function _store_hbreak() {
        // Return if the user hasn't specified pagebreaks
        if(sizeof($this->_hbreaks)==0) {
            return;
        }

        # Sort and filter array of page breaks
        $breaks  = $this->_sort_pagebreaks($this->_hbreaks);

        $record  = 0x001b;             // Record identifier
        $cbrk    = sizeof($breaks);    // Number of page breaks
        $length  = ($cbrk + 1) * 2;    // Bytes to follow

        $header  = pack("vv",  $record, $length);
        $data    = pack("v",   $cbrk);

        // Append each page break
        foreach ($breaks as $break) {
            $data .= pack("v", $break);
        }

        $this->_prepend($header . $data);
    }

    /*
     * Write the VERTICALPAGEBREAKS BIFF record.
     */
    function _store_vbreak() {
        // Return if the user hasn't specified pagebreaks
        if(sizeof($this->_vbreaks)==0) {
            return;
        }

        // Sort and filter array of page breaks
        $breaks  = $this->_sort_pagebreaks($this->_vbreaks);

        $record  = 0x001a;            // Record identifier
        $cbrk    = sizeof($breaks);   // Number of page breaks
        $length  = ($cbrk + 1) * 2;   // Bytes to follow

        $header  = pack("vv",  $record, $length);
        $data    = pack("v",   $cbrk);

        // Append each page break
        foreach ($breaks as $break) {
            $data .= pack("v", $break);
        }

        $this->_prepend($header . $data);
    }

    /*
     * Set the Biff PROTECT record to indicate that the worksheet is
     * protected.
     */
    function _store_protect() {
        // Exit unless sheet protection has been specified
        if (!$this->_protect) {
            return;
        }

        $record      = 0x0012;            // Record identifier
        $length      = 0x0002;            // Bytes to follow

        $fLock       = $this->_protect;   // Worksheet is protected

        $header      = pack("vv", $record, $length);
        $data        = pack("v",  $fLock);

        $this->_prepend($header . $data);
    }

    /*
     * Write the worksheet PASSWORD record.
     */
    function _store_password() {
        // Exit unless sheet protection and password have been specified
        if (!$this->_protect || !$this->_password) {
            return;
        }

        $record      = 0x0013;             // Record identifier
        $length      = 0x0002;             // Bytes to follow

        $wPassword   = $this->_password;   // Encoded password

        $header      = pack("vv", $record, $length);
        $data        = pack("v",  $wPassword);

        $this->_prepend($header . $data);
    }

###############################################################################
#
# insert_bitmap($row, $col, $filename, $x, $y, $scale_x, $scale_y)
#
# Insert a 24bit bitmap image in a worksheet. The main record required is
# IMDATA but it must be proceeded by a OBJ record to define its position.
#
function insert_bitmap() {

    $_=func_get_args();

    # Check for a cell reference in A1 notation and substitute row and column
    if (preg_match('/^\D/', $_[0])) {
        $_ = $this->_substitute_cellref($_);
    }

    $row         = $_[0];
    $col         = $_[1];
    $bitmap      = $_[2];
    $x           = $_[3] ? $_[3] : 0;
    $y           = $_[4] ? $_[4] : 0;
    $scale_x     = $_[5] ? $_[5] : 1;
    $scale_y     = $_[6] ? $_[6] : 1;

    list($width, $height, $size, $data) = $this->_process_bitmap($bitmap);

    # Scale the frame of the image.
    $width  *= $scale_x;
    $height *= $scale_y;

    # Calculate the vertices of the image and write the OBJ record
    $this->_position_image($col, $row, $x, $y, $width, $height);

    # Write the IMDATA record to store the bitmap data
    $record      = 0x007f;
    $length      = 8 + $size;
    $cf          = 0x09;
    $env         = 0x01;
    $lcb         = $size;

    $header      = pack("vvvvV", $record, $length, $cf, $env, $lcb);

    $this->_append($header . $data);
}

    /*
     * Calculate the vertices that define the position of the image as
     * required by the OBJ record.
     *
     *        +------------+------------+
     *        |     A      |      B     |
     *  +-----+------------+------------+
     *  |     |(x1,y1)     |            |
     *  |  1  |(A1)._______|______      |
     *  |     |    |              |     |
     *  |     |    |              |     |
     *  +-----+----|    BITMAP    |-----+
     *  |     |    |              |     |
     *  |  2  |    |______________.     |
     *  |     |            |        (B2)|
     *  |     |            |     (x2,y2)|
     *  +---- +------------+------------+
     *
     * Example of a bitmap that covers some of the area from cell A1 to
     * cell B2.
     *
     * Based on the width and height of the bitmap we need to calculate 8
     *vars:
     *    $col_start, $row_start, $col_end, $row_end, $x1, $y1, $x2, $y2.
     * The width and height of the cells are also variable and have to be
     * taken into account.
     * The values of $col_start and $row_start are passed in from the calling
     * function. The values of $col_end and $row_end are calculated by
     * subtracting the width and height of the bitmap from the width and
     * height of the underlying cells.
     * The vertices are expressed as a percentage of the underlying cell
     * width as follows (rhs values are in pixels):
     *
     *      x1 = X / W *1024
     *      y1 = Y / H *256
     *      x2 = (X-1) / W *1024
     *      y2 = (Y-1) / H *256
     *
     *      Where:  X is distance from the left side of the underlying cell
     *              Y is distance from the top of the underlying cell
     *              W is the width of the cell
     *              H is the height of the cell
     *
     * Note: the SDK incorrectly states that the height should be expressed
     * as a percentage of 1024.
     */
    function _position_image($col_start, $row_start, $x1, $y1,
                             $width, $height) {
        // $col_start   Col containing upper left corner of object
        // $x1          Distance to left side of object

        // $row_start   Row containing top left corner of object
        // $y1          Distance to top of object

        // $col_end     Col containing lower right corner of object
        // $x2          Distance to right side of object

        // $row_end     Row containing bottom right corner of object
        // $y2          Distance to bottom of object

        // $width       Width of image frame
        // $height      Height of image frame

        // Initialise end cell to the same as the start cell
        $col_end = $col_start;
        $row_end = $row_start;

        // Zero the specified offset if greater than the cell dimensions
        if ($x1 >= $this->_size_col($col_start)) {
            $x1 = 0;
        }
        if ($y1 >= $this->_size_row($row_start)) {
            $y1 = 0;
        }

        $width  = $width  + $x1 -1;
        $height = $height + $y1 -1;

        // Subtract the underlying cell widths to find the end cell of the
        // image
        while ($width >= $this->_size_col($col_end)) {
            $width -= $this->_size_col($col_end);
            $col_end++;
        }

        // Subtract the underlying cell heights to find the end cell of the
        // image
        while ($height >= $this->_size_row($row_end)) {
            $height -= $this->_size_row($row_end);
            $row_end++;
        }

        // Bitmap isn't allowed to start or finish in a hidden cell, i.e. a
        // cell with zero height or width.
        if ($this->_size_col($col_start) == 0) { return; }
        if ($this->_size_col($col_end)   == 0) { return; }
        if ($this->_size_row($row_start) == 0) { return; }
        if ($this->_size_row($row_end)   == 0) { return; }

        // Convert the pixel values to the percentage value expected by Excel
        $x1 = $x1     / $this->_size_col($col_start) * 1024;
        $y1 = $y1     / $this->_size_row($row_start) *  256;
        $x2 = $width  / $this->_size_col($col_end)   * 1024;
        $y2 = $height / $this->_size_row($row_end)   *  256;

        $this->_store_obj_picture($col_start, $x1, $row_start, $y1,
                                  $col_end, $x2, $row_end, $y2);
    }

    /*
     * Convert the width of a cell from user's units to pixels. By
     * interpolation the relationship is: y = 7x +5. If the width
     * hasn't been set by the user we use the default value. If the
     * col is hidden we use a value of zero.
     */
    function _size_col($col) {
        // Look up the cell value to see if it has been changed
        if (isset($this->_col_sizes[$col])) {
            if ($this->_col_sizes[$col] == 0) {
                return 0;
            } else {
                return floor(7 * $this->_col_sizes[$col] + 5);
            }
        } else {
            return 64;
        }
    }

    /*
     * Convert the height of a cell from user's units to pixels. By
     * interpolation # the relationship is: y = 4/3x. If the height
     * hasn't been set by the user we use the default value. If the
     * row is hidden we use a value of zero. (Not possible to hide row
     * yet).
     */
    function _size_row($row) {
        // Look up the cell value to see if it has been changed
        if (isset($this->_row_sizes[$row])) {
            if ($this->_row_sizes[$row] == 0) {
                return 0;
            } else {
                return floor(4/3 * $this->_row_sizes[$row]);
            }
        } else {
            return 17;
        }
    }

    /*
     * Store the OBJ record that precedes an IMDATA record. This could
     * be generalized to support other Excel objects.
     */
    function _store_obj_picture($col_start, $x1, $row_start, $y1,
                                $col_end, $x2, $row_end, $y2) {
        $record      = 0x005d;       // Record identifier
        $length      = 0x003c;       // Bytes to follow

        $cObj        = 0x0001;       // Count of objects in file (set to 1)
        $OT          = 0x0008;       // Object type. 8 = Picture
        $id          = 0x0001;       // Object ID
        $grbit       = 0x0614;       // Option flags

        $colL        = $col_start;   // Col containing upper left corner of
                                     // object
        $dxL         = $x1;          // Distance from left side of cell

        $rwT         = $row_start;   // Row containing top left corner of
                                     // object
        $dyT         = $y1;          // Distance from top of cell

        $colR        = $col_end;     // Col containing lower right corner of 
                                     // object
        $dxR         = $x2;          // Distance from right of cell

        $rwB         = $row_end;     // Row containing bottom right corner of
                                     // object
        $dyB         = $y2;          // Distance from bottom of cell

        $cbMacro     = 0x0000;       // Length of FMLA structure
        $Reserved1   = 0x0000;       // Reserved
        $Reserved2   = 0x0000;       // Reserved

        $icvBack     = 0x09;         // Background colour
        $icvFore     = 0x09;         // Foreground colour
        $fls         = 0x00;         // Fill pattern
        $fAuto       = 0x00;         // Automatic fill
        $icv         = 0x08;         // Line colour
        $lns         = 0xff;         // Line style
        $lnw         = 0x01;         // Line weight
        $fAutoB      = 0x00;         // Automatic border
        $frs         = 0x0000;       // Frame style
        $cf          = 0x0009;       // Image format, 9 = bitmap
        $Reserved3   = 0x0000;       // Reserved
        $cbPictFmla  = 0x0000;       // Length of FMLA structure
        $Reserved4   = 0x0000;       // Reserved
        $grbit2      = 0x0001;       // Option flags
        $Reserved5   = 0x0000;       // Reserved

        $header      = pack("vv", $record, $length);
        $data        = pack("V",  $cObj);
        $data       .= pack("v",  $OT);
        $data       .= pack("v",  $id);
        $data       .= pack("v",  $grbit);
        $data       .= pack("v",  $colL);
        $data       .= pack("v",  $dxL);
        $data       .= pack("v",  $rwT);
        $data       .= pack("v",  $dyT);
        $data       .= pack("v",  $colR);
        $data       .= pack("v",  $dxR);
        $data       .= pack("v",  $rwB);
        $data       .= pack("v",  $dyB);
        $data       .= pack("v",  $cbMacro);
        $data       .= pack("V",  $Reserved1);
        $data       .= pack("v",  $Reserved2);
        $data       .= pack("C",  $icvBack);
        $data       .= pack("C",  $icvFore);
        $data       .= pack("C",  $fls);
        $data       .= pack("C",  $fAuto);
        $data       .= pack("C",  $icv);
        $data       .= pack("C",  $lns);
        $data       .= pack("C",  $lnw);
        $data       .= pack("C",  $fAutoB);
        $data       .= pack("v",  $frs);
        $data       .= pack("V",  $cf);
        $data       .= pack("v",  $Reserved3);
        $data       .= pack("v",  $cbPictFmla);
        $data       .= pack("v",  $Reserved4);
        $data       .= pack("v",  $grbit2);
        $data       .= pack("V",  $Reserved5);

        $this->_append($header . $data);
    }

    /*
     * Convert a 24 bit bitmap into the modified internal format used by
     * Windows. This is described in BITMAPCOREHEADER and BITMAPCOREINFO
     * structures in the MSDN library.
     */
    function _process_bitmap($bitmap) {
        // Open file and binmode the data in case the platform needs it.
        $bmp=fopen($bitmap, "rb");
        if (!$bmp) {
            trigger_error("Could not open file '$bitmap'.", E_USER_ERROR);
        }

        $data=fread($bmp, filesize($bitmap));

        // Check that the file is big enough to be a bitmap.
        if (strlen($data) <= 0x36) {
            trigger_error("$bitmap doesn't contain enough data.",
                          E_USER_ERROR);
        }

        // The first 2 bytes are used to identify the bitmap.
        if (substr($data, 0, 2) != "BM") {
            trigger_error("$bitmap doesn't appear to to be a ".
                          "valid bitmap image.", E_USER_ERROR);
        }

        // Remove bitmap data: ID.
        $data = substr($data, 2);

        // Read and remove the bitmap size. This is more reliable than reading
        // the data size at offset 0x22.
        $array = unpack("Vsize", $data);
        $data = substr($data, 4);
        $size   =  $array["size"];
        $size  -=  0x36;   # Subtract size of bitmap header.
        $size  +=  0x0C;   # Add size of BIFF header.

        // Remove bitmap data: reserved, offset, header length.
        $data = substr($data, 12);

        // Read and remove the bitmap width and height. Verify the sizes.
        $array = unpack("Vwidth/Vheight", $data);
        $data = substr($data, 8);
        $width = $array["width"];
        $height = $array["height"];

        if ($width > 0xFFFF) {
            trigger_error("$bitmap: largest image width supported is 64k.",
                          E_USER_ERROR);
        }

        if ($height > 0xFFFF) {
            trigger_error("$bitmap: largest image height supported is 64k.",
                          E_USER_ERROR);
        }

        // Read and remove the bitmap planes and bpp data. Verify them.
        $array = unpack("vplanes/vbitcount", $data);
        $data = substr($data, 4);
        $planes = $array["planes"];
        $bitcount = $array["bitcount"];

        if ($bitcount != 24) {
            trigger_error("$bitmap isn't a 24bit true color bitmap.",
                          E_USER_ERROR);
        }

        if ($planes != 1) {
            trigger_error("$bitmap: only 1 plane supported in bitmap image.",
                          E_USER_ERROR);
        }

        // Read and remove the bitmap compression. Verify compression.
        $array = unpack("Vcompression", $data);
        $data = substr($data, 4);
        $compression = $array["compression"];

        if ($compression != 0) {
            trigger_error("$bitmap: compression not supported in bitmap image.",
                          E_USER_ERROR);
        }

        // Remove bitmap data: data size, hres, vres, colours, imp. colours.
        $data = substr($data, 20);

        // Add the BITMAPCOREHEADER data
        $header = pack("Vvvvv", 0x000c, $width, $height, 0x01, 0x18);
        $data = $header . $data;

        return array($width, $height, $size, $data);
    }

    /*
     * Store the window zoom factor. This should be a reduced fraction but for
     * simplicity we will store all fractions with a numerator of 100.
     */
    function _store_zoom() {
        // If scale is 100% we don't need to write a record
        if ($this->_zoom == 100) {
            return;
        }

        $record = 0x00A0; // Record identifier
        $length = 0x0004; // Bytes to follow

        $header = pack("vv", $record, $length);
        $data   = pack("vv", $this->_zoom, 100);

        $this->_append($header . $data);
    }

}

?>
