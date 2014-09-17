<?php

/**
 * Function to make output on console like console.log() does in javascript.<br>
 * @param mixed $data Data to debug
 * @return console~output open console (Ctrl+Shift+J) and see formatted output
 */
function debug($data) {
    echo "<script>\r\n//<![CDATA[\r\nif(!console){var console={log:function(){}}}console.log(\"".rtrim(str_replace("\n", '\n', print_r($data, true)),'\n') . "\");</script>";
}

/**
 * <p><b><i>make a url friendly string</i></b></p>
 * <p>A <b>Slug</b> is Url Friendly string which does not include any white spaces</p>
 * @param string $value string whose slug is required
 * @param numeric $length [optional]<br>The Length upto which slug is required e.g. <i>100</i>
 * @return string String which is url friendly & is having every white space(s) replaced by "-"
 */
function make_slug($value, $length = null) {
    $value = preg_replace("/@/", ' at ', $value);
    $value = preg_replace("/£/", ' pound ', $value);
    $value = preg_replace("/#/", ' hash ', $value);
    $value = preg_replace("/[\-+]/", ' ', $value);
    $value = preg_replace("/[\s+]/", ' ', $value);
    $value = preg_replace("/[\.+]/", '', $value);
    $value = preg_replace("/[^A-Za-z0-9\.\s]/", '', $value);
    $value = preg_replace("/[\s]/", '-', $value);
    $value = preg_replace("/\-\-+/", '-', $value);
    $value = strtolower($value);
    if (substr($value, -1) == "-") {
        $value = substr($value, 0, -1);
    }
    if (substr($value, 0, 1) == "-") {
        $value = substr($value, 1);
    }
    if (isset($length) && is_numeric($length) && $length > 0) {
        $value = substr($value, 0, $length);
    }
    return $value;
}

/**
 * modified version of print_r()<br /><br />
 * makes a formatted output of an array
 * @param array $array array to be printed
 * @param string $var_name [optional]<br />Shows a description about the output
 */
function pa($array, $var_name = NULL) {
    if (!empty($array)) {
        echo "<pre>";
        if (isset($var_name)) {
            echo "<div style='border:solid 2px red;color:lightgreen;background-color:black;font-size:18px;padding:5px;border-radius:5px'>Data for '<font color=red>" . $var_name . "</font>' as [key] => value</div><br>";
        }
        print_r($array);
        echo "</pre>";
    }
}

/**
 * prints json string from array
 * @param array $Array input array
 * @param boolean $pp [optional]<br />
 * <i>true</i> - pretty printed output.<br />
 * <i>false</i> (default) - simple json output
 */
function pj($Array, $pp = FALSE) {
    header('Content-type: text/plain');
    if ($pp === TRUE)
        echo json_encode($Array, JSON_PRETTY_PRINT);
    else
        echo json_encode($Array);
}

/**
 * make all keys as uppercase or lowercase
 * @param array $Array input array
 * @param boolean $make_lower <b>[optional]</b><br>
 * TRUE - make keys lowercase<br>
 * FALSE (default) - make keys uppercase
 * @param boolean $include_children <b>[optional]</b><br>
 * TRUE - also apply same function to including children arrays<br>
 * FALSE (optional) - apply only to input array
 * @return array Array with function applied over it
 */
function caps_keys($Array, $make_lower = FALSE, $include_children = FALSE) {
    if (is_array($Array)) {
        $cap_keys = array();
        foreach ($Array as $k => $v) {
            if ($include_children === TRUE) {
                if (is_array($v)) {
                    if ($make_lower === TRUE)
                        $cap_keys[strtolower($k)] = caps_keys($v, $make_lower, $include_children);
                    else
                        $cap_keys[strtoupper($k)] = caps_keys($v, $make_lower, $include_children);
                    unset($Array[$k]);
                } else {
                    if ($make_lower === TRUE)
                        $cap_keys[strtolower($k)] = $v;
                    else
                        $cap_keys[strtoupper($k)] = $v;
                    unset($Array[$k]);
                }
            } else {
                if ($make_lower === TRUE)
                    $cap_keys[strtolower($k)] = $v;
                else
                    $cap_keys[strtoupper($k)] = $v;
                unset($Array[$k]);
            }
        }
        $Array = $cap_keys;
    }
    return $Array;
}

function funcs($opts /* internal(T,F), natural(T,F), sort(T,F), return(T,F) */ = array()) {
    if (!empty($opts)) {
        $opts = caps_keys($opts, TRUE);
    }
    $funcs = get_defined_functions();
    if (isset($opts['internal']) && $opts['internal'] === TRUE) {
        $funcs = $funcs['internal'];
    } else {
        $funcs = $funcs['user'];
    }
    if (isset($opts['natural']) && $opts['natural'] === TRUE) {
        foreach ($funcs as $k => $v) {
            $funcs[$k + 1] = $v;
        }
        unset($funcs[0]);
    }
    if (isset($opts['sort']) && $opts['sort'] === TRUE)
        asort($funcs);
    if (isset($opts['return']) && $opts['return'] === TRUE) {
        return $funcs;
    }
    pr($funcs);
}

function make_array($obj) {
    if (is_object($obj)) {
        $obj = (array) $obj;
        foreach ($obj as $key => $obj_child) {
            $obj_child = make_array($obj_child);
            $obj[$key] = $obj_child;
        }
    } elseif (is_array($obj)) {
        foreach ($obj as $key => $obj_child) {
            $obj_child = make_array($obj_child);
            $obj[$key] = $obj_child;
        }
    }
    return $obj;
}

function make_object($array) {
    if (is_array($array)) {
        foreach ($array as $key => $array_child) {
            $array[$key] = make_object($array_child);
        }
        $array = (object) $array;
    } elseif (is_object($array)) {
        $temp_array = (array) $array;
        $array = make_object($temp_array);
    }
    return $array;
}

function my_substr($string, $length = 10) {
    if (strlen($string) > $length) {
        $string = substr($string, 0, $length) . "...";
    }
    return $string;
}

function getRealIp() {
    $ip = '0.0.0.0';
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ip = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ip = getenv('HTTP_FORWARDED_FOR');
    } elseif (getenv('HTTP_FORWARDED')) {
        $ip = getenv('HTTP_FORWARDED');
    } elseif (getenv('REMOTE_ADDR')) {
        $ip = getenv('REMOTE_ADDR');
    }
    return $ip;
}

function ajax(array $opts /* TYPE,FILTER,DATA */ = null) {
    $url_prefix = '';
    if (isset($opts['PREFIX']) && is_string($opts['PREFIX'])) {
        $url_prefix = $opts['PREFIX'];
    }
    $ch = curl_init();
    $vars_string = '';
    if (isset($opts['DATA']) && is_array($opts['DATA'])) {
        $data = array_filter($opts['DATA']);
        foreach ($data as $var => $val) {
            $vars_string .= $var . '=' . $val . '&';
        }
        $vars_string = rtrim($vars_string, '&');
    }
    curl_setopt($ch, CURLOPT_URL, $url_prefix . ($vars_string ? '?' . $vars_string : ""));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    //curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
    $response = curl_exec($ch);
    curl_close($ch);
    if (isset($opts['TYPE']) && $opts['TYPE']) {
        $type = strtolower($opts['TYPE']);
        $obj_types = array('obj', 'object', '->');
        $array_types = array('arr', 'array', '=>', '[]');
        if (in_array($type, $obj_types)) {
            $response = json_decode($response);
            if (isset($opts['FILTER']) && $opts['FILTER'] === true) {
                $response = make_array($response);
                $response = array_filter($response);
                $response = make_object($response);
            }
        } elseif (in_array($type, $array_types)) {
            $response = json_decode($response);
            $response = make_array($response);
            if (isset($opts['FILTER']) && $opts['FILTER'] === true) {
                $response = array_filter($response);
            }
        }
    } else {
        if (isset($opts['FILTER']) && $opts['FILTER'] === true) {
            $response = json_decode($response);
            $response = make_array($response);
            $response = array_filter($response);
            $response = json_encode($response);
        }
    }
    return $response;
}

function clean($str) {
    $str = @trim($str);
    if (get_magic_quotes_gpc()) {
        $str = stripslashes($str);
    }
    $str = strip_tags($str);
    return $str;
}

function bulk_clean($Array, $clean_contents = FALSE) {
    if (is_array($Array) && !empty($Array)) {
        foreach ($Array as $k => $v) {
            if ($clean_contents === TRUE) {
                if (is_array($v)) {
                    $v = bulk_clean($v, TRUE);
                } else {
                    $v = clean($v);
                }
            } else {
                $v = clean($v);
            }
            $Array[$k] = $v;
        }
    }
    return $Array;
}

function tree($data) {
    // capture the output of print_r
    $out = print_r($data, true);

    // replace something like '[element] => <newline> (' with <a href="javascript:toggleDisplay('...');">...</a><div id="..." style="display: none;">
    $out = preg_replace('/([ \t]*)(\[[^\]]+\][ \t]*\=\>[ \t]*[a-z0-9 \t_]+)\n[ \t]*\(/iUe', "'\\1<a href=\"javascript:toggleDisplay(\''.(\$id = substr(md5(rand().'\\0'), 0, 7)).'\');\">\\2</a><div id=\"'.\$id.'\" style=\"display: none;\">'", $out);

    // replace ')' on its own on a new line (surrounded by whitespace is ok) with '</div>
    $out = preg_replace('/^\s*\)\s*$/m', '</div>', $out);

    // print the javascript function toggleDisplay() and then the transformed output
    echo '<script language="Javascript">function toggleDisplay(id) { document.getElementById(id).style.display = (document.getElementById(id).style.display == "block") ? "none" : "block"; }</script>' . "\n$out";
}
