<?php

// dbg('loading helper', $opts);

return [
    'pluralize' => static function ($total, $singular, $plural) {
        return $total == 1 ? $singular : str_replace('{n}', $total, $plural);
    },
    'pluralize0' => static function ($total, $zero, $singular, $plural) {
        return (!$total) ? $zero : ($total == 1 ? $singular : str_replace('{n}', $total, $plural));
    },
    'markdown' => static function ($md) use ($opts) {
        // can't use binded opts here
        // at eval time
        // maybe double add handler on eval time
        // TODO: some tests
        // return $opts['markdown']->text($md);
        $pd = new \Parsedown();
        return $pd->text($md);
    }
];
