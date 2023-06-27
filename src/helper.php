<?php

if (!function_exists('d')) {
    function d(...$args) {
        echo '<pre>';
        foreach ($args as $arg) {
            print_r($arg);
        }
        echo '</pre>';
    }
}

if (!function_exists('dd')) {
    function dd(...$args) {
        d(...$args);
        die;
    }
}

function dbg($txt, ...$vars) {
    // im servermodus wird der zeitstempel automatisch gesetzt
    //	$log = [date('Y-m-d H:i:s')];
    $log = [];
    if (!is_string($txt)) {
        array_unshift($vars, $txt);
    } else {
        $log[] = $txt;
    }
    $log[] = join(' ', array_map('json_encode', $vars));
    error_log(join(' ', $log));
}

function array_blocklist($arr, $block) {
    if (is_string($block)) {
        $block = explode(' ', $block);
    }
    return array_diff_key($arr, array_flip($block));
}
function array_search_fun($needle_fun, $haystack) {
    foreach ($haystack as $k => $v) {
        if (call_user_func($needle_fun, $v) === true) {
            return [$k, $v];
        }
    }
    return null;
}

function array_delete($array, $idx) {
    array_splice($array, $idx, 1);
    return $array;
}

function get_trace_from_exception($e) {
    $class = get_class($e);
    $pclass = get_parent_class($e);
    $m = $e->getMessage();

    $fm = sprintf(
        "%s:\n   %s line: %s code: %s\n   via %s%s\n",
        $m,
        $e->getFile(),
        $e->getLine(),
        $e->getCode(),
        $class,
        $pclass ? ', ' . $pclass : ''
    );
    $trace .= $fm . $e->getTraceAsString();
    return $trace;
}

function equal_or_in_array($needle, $haystack) {
    return (is_array($haystack) && in_array($needle, $haystack)) ||
        (!is_array($haystack) && $needle == $haystack);
}
