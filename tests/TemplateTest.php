<?php

declare(strict_types=1);

error_reporting(\E_ALL);

use PHPUnit\Framework\TestCase;
#use function emil\template\load_helper;
#use function emil\template\process;
#use function emil\template\process_string;
use emil\template\processor;

final class TemplateTest extends TestCase {
    public $opts;

    public function setUp(): void {
        $opts = [
            'base' => __DIR__ . '/data',
            'frontparser' => new \Mni\FrontYAML\Parser,
            'markdown' => new \Parsedown(),
            'types' => ['md', 'txt', 'html']
        ];

        $this->opts = $opts;

        // dbg('++ md test', $template, $opts['helper']['markdown']('**hi**'));

        // [$views, $data] = process($template, $data, $opts);
    }

    public function testBasicHelper(): void {
        $p = new processor('t1', $this->opts);
        $out = $p->helper['markdown']('**hi**');
        $this->assertEquals('<p><strong>hi</strong></p>', $out);
    }

    public function testBasicTemplate(): void {
        $data = ['name' => 'otto', 'what' => 'fun'];
        $p = new processor('t1', $this->opts);
        [$views, $data] = $p->run($data);
        $this->assertCount(3, $views);
        // no html version
        $this->assertNull($views['html']);
        $this->assertStringContainsString('have fun', $views['txt']);
        // dd($views);
    }

    public function testStringProcess(): void {
        $data = ['name' => 'otto', 'what' => 'fun', 'subject' => 'hello {{name}}'];
        $subject = processor::process_string($data['subject'], $data);
        $this->assertEquals('hello otto', $subject);
    }

    public function testHtmlLayout(): void {
        $data = ['name' => 'otto', 'what' => 'fun'];
        $p = new processor('t3', $this->opts);
        // dd("processor", $p);
        [$views, $data] = $p->run($data);
        $this->assertCount(3, $views);
        // no txt version
        $this->assertNull($views['txt']);
        $this->assertStringContainsString('Good Day!', $views['html']);

        $this->assertStringContainsString('<head>', $views['html']);
        $this->assertStringContainsString('<i>Sent with Love</i>', $views['html']);
        // dd($views);
    }

    public function testTxtLayout(): void {
        $data = ['name' => 'otto', 'what' => 'fun'];
        $p = new processor('t4', $this->opts);
        // dd("processor", $p);
        [$views, $data] = $p->run($data);
        $this->assertCount(3, $views);
        // no txt version

        $this->assertStringContainsString('Good Day!', $views['html']);
        $this->assertStringContainsString('<head>', $views['html']);
        $this->assertStringContainsString('<i>Sent with Love</i>', $views['html']);

        $this->assertStringContainsString('+++', $views['txt']);
        $this->assertStringContainsString('Sunshine', $views['txt']);
        // dd($views);
    }

    public function testMdTemplate(): void {
        $data = ['name' => 'otto', 'what' => 'fun'];
        $p = new processor('t5', $this->opts);
        // dd("processor", $p);
        [$views, $data] = $p->run($data);
        $this->assertCount(3, $views);
        // no txt version

        $this->assertStringContainsString('Good Day!', $views['html']);
        $this->assertStringContainsString('<head>', $views['html']);
        $this->assertStringContainsString('<i>Sent with Love</i>', $views['html']);
        $this->assertStringContainsString('<h1>hello otto</h1>', $views['html']);
        $this->assertStringContainsString('<em>back</em>', $views['html']);

        $this->assertStringContainsString('+++', $views['txt']);
        $this->assertStringContainsString('Sunshine', $views['txt']);
        $this->assertStringContainsString('# hello otto', $views['txt']);
        $this->assertStringContainsString('_back_', $views['txt']);
        // dd($views);
    }
}
