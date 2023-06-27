<?php

namespace emil\template;

use LightnCandy\LightnCandy;

class processor {
    public string $basedir;
    public array $stage = [];

    public array $parts = [];

    public array $helper = [];
    public array $types = ['md', 'txt', 'html'];

    public array $collected_data = [];
    public string $layout_name = '';

    public $subject = null;

    public function __construct(public string $name, public array $opts = [], public $layout = "") {
        $this->basedir = $opts['base'];
        $this->load_helper($opts);
        $this->process_template($name);
    }

    public function load_helper($opts) {
        $this->helper = include __DIR__ . '/hb_helper.php';
    }

    public function process_template($name) {
        $this->load_template($name);
        $this->collect_data();
        $this->set_layout();

        $this->compile_template();
    }

    public function run($data) {
        $data = array_merge($this->collected_data, $data);
        $base = $this->basedir;

        $embeds = [];
        $res = ['txt' => null, 'html' => null];
        foreach ($this->parts as $type => $part) {
            if (!$part) continue;

            $output = ($part->runner)($data, [
                'helpers' => [
                    'embed' => static function ($context, $options) use (&$embeds, $base) {
                        $file = $base . '/' . $context;
                        // $hash = 'embed-' . md5($file) . '-embed';
                        $hash = md5($file);
                        $embeds[$hash] = $file;
                        dbg('++ embed runtime', $context, $hash, $file);
                        return 'cid:' . $hash;
                    }
                ],
            ]);
            if ($part->post_process) {
                $output = $this->post_process($output);
            }
            $res[$type] = $output;
        }
        $res['embeds'] = $embeds;
        $data['subject'] = self::process_string($data['subject'], $data, $this->helper);

        return [$res, $data];
    }

    public function post_process($txt) {
        $parts = explode('<!-- pp:md -->', $txt);
        if ($parts[2]) {
            $parts[1] = $this->opts['markdown']->text($parts[1]);
            return join("\n", $parts);
        }
        return $txt;
    }

    public function load_template($name) {
        $this->stage[] = 'load';
        $md = part::from_file($name, 'md', $this->basedir, $this->opts['frontparser']);
        if ($md) {
            $this->parts['txt'] = part::from_markdown('txt', $md);
            $this->parts['html'] = part::from_markdown('html', $md);
        } else {
            $this->parts['txt'] = part::from_file($name, 'txt', $this->basedir, $this->opts['frontparser']);
            $this->parts['html'] = part::from_file($name, 'html', $this->basedir, $this->opts['frontparser']);
        }
        return $this;
    }

    public function collect_data() {
        $opts_data = self::array_blocklist($this->opts, 'api_key password transport frontparser markdown base types');
        $this->collected_data = array_merge(
            $opts_data,
            $this->parts['txt'] ? $this->parts['txt']->data : [],
            $this->parts['html'] ? $this->parts['html']->data : [],
        );
    }

    public function set_layout() {
        if (!$this->layout) {
            $this->layout = $this->collected_data['layout'] ?? '';
        }
        if ($this->layout) $this->layout_name = '__' . $this->layout;
    }

    public function compile_template() {
        $this->stage[] = 'compile';

        $base = $this->basedir;
        foreach ($this->parts as $type => $part) {
            if (!$part) continue;
            $src = $part->contents;
            if ($this->layout_name && file_exists($this->basedir . '/' . $this->layout_name . '.' . $type)) {
                $src = sprintf("{{#> %s }}\n%s\n{{/ %s }}", $this->layout_name, $src, $this->layout_name);
            }
            $code = LightnCandy::compile(
                $src,
                [
                    'partialresolver' => static function ($context, $name) use ($base, $part) {
                        $fname = "$base/{$name}.{$part->type}";
                        if (file_exists($fname)) {
                            return file_get_contents($fname);
                        }
                        return "[partial (file:$fname) not found]";
                    },
                    'helpers' => array_merge([
                        'embed' => static function ($context, $options) {
                            // im compile step nix tun,
                            // erst im runstep wird die embedliste produziert
                            return $context;
                        },
                    ], $this->helper),
                    'flags' => LightnCandy::FLAG_ERROR_LOG | LightnCandy::FLAG_RUNTIMEPARTIAL | LightnCandy::FLAG_ADVARNAME
                ]
            );
            $part->set_runner(eval($code));
        }
    }

    // TODO: precompiled subject?
    public static function process_string($str, $data, $helper = []) {
        $r = eval(LightnCandy::compile($str, [
            'helpers' => $helper,
            'flags' => LightnCandy::FLAG_ERROR_LOG | LightnCandy::FLAG_RUNTIMEPARTIAL | LightnCandy::FLAG_ADVARNAME
        ]));
        return $r($data);
    }

    public static function array_blocklist($arr, $block) {
        if (is_string($block)) {
            $block = explode(' ', $block);
        }
        return array_diff_key($arr, array_flip($block));
    }
}
