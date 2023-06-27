<?php

namespace emil\template;

class part {

    public string $post_process = '';
    public $runner = null;

    public function __construct(public string $name, public string $type, public string $contents, public array $data) {
    }

    static function from_file(string $name, string $type, string $base, $parser) {
        $fname = join('/', [$base, $name . '.' . $type]);
        if (!file_exists($fname)) return null;
        $document = $parser->parse(file_get_contents($fname), false);
        return new self($name, $type, $document->getContent() ?? '', $document->getYAML() ?? []);
    }

    static function from_markdown(string $type, self $mdpart) {
        if ($type == 'html') {
            $contents = sprintf('<!-- pp:md -->%s<!-- pp:md -->', $mdpart->contents);
        } else {
            $contents = $mdpart->contents;
        }
        $part = new self($mdpart->name, $type, $contents, $mdpart->data);
        $part->set_markdown();
        return $part;
    }

    public function load() {
    }

    public function set_markdown() {
        if ($this->type == 'html') {
            $this->post_process = 'md';
        }
    }

    public function set_runner($runner) {
        $this->runner = $runner;
    }
}
