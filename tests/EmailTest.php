<?php

declare(strict_types=1);

error_reporting(\E_ALL);

use PHPUnit\Framework\TestCase;
use emil\template\processor;

final class EmailTest extends TestCase {
    public $opts;
    public $mailer;

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
        $this->mailer = new emil\mailer(['transport' => 'null://null']);
    }

    public function testBasicEmail(): void {
        $data = ['name' => 'otto', 'what' => 'fun', 'from' => '"Support" office@acme.com', 'to' => 'webmaster@localhost'];
        $p = new processor('t1', $this->opts);

        $email = $this->mailer->create_email(...$p->run($data));

        $mime = $email->toString();
        $this->assertStringContainsString('Content-Type: text/plain', $mime);
        $this->assertStringContainsString('Subject: Welcome otto', $mime);
        $this->assertStringContainsString('From: Support <office@acme.com>', $mime);
        $this->assertStringContainsString("let's have fun", $mime);
    }

    public function testHtmlWithEmbed(): void {
        $data = ['name' => 'otto', 'what' => 'fun', 'from' => '"Support" office@acme.com', 'to' => 'webmaster@localhost'];
        $p = new processor('t2', $this->opts);

        $email = $this->mailer->create_email(...$p->run($data));

        $mime = $email->toString();
        //         dd($mime);
        $this->assertStringContainsString('Content-Type: multipart/related', $mime);
        $this->assertStringContainsString('Content-Type: text/html', $mime);
        $this->assertStringContainsString('Content-Type: image/png', $mime);
        $this->assertStringContainsString('Content-Disposition: inline', $mime);
        $this->assertStringContainsString('@symfony', $mime);
        $this->assertStringContainsString('cid:', $mime);

        $this->assertStringContainsString('Subject: Welcome otto', $mime);
        $this->assertStringContainsString('From: Support <office@acme.com>', $mime);
        $this->assertStringContainsString("Good Day", $mime);
    }

    public function testHtmlWithEmbedInLayout(): void {
        $data = ['name' => 'otto', 'what' => 'fun', 'from' => '"Support" office@acme.com', 'to' => 'webmaster@localhost'];
        $p = new processor('welcome', $this->opts);
        // dd($p->run($data));
        $email = $this->mailer->create_email(...$p->run($data));

        $mime = $email->toString();
        // dd($mime);
        $this->assertStringContainsString('Content-Type: multipart/related', $mime);
        $this->assertStringContainsString('Content-Type: text/html', $mime);
        $this->assertStringContainsString('Content-Type: image/png', $mime);
        $this->assertStringContainsString('Content-Disposition: inline', $mime);
        $this->assertStringContainsString('@symfony', $mime);
        $this->assertStringContainsString('cid:', $mime);

        $this->assertStringContainsString('Subject: Welcome otto', $mime);
        $this->assertStringContainsString('From: Support <office@acme.com>', $mime);
        $this->assertStringContainsString("Thank you for registering", $mime);
    }

    public function testBasicEmailTo(): void {
        $data = ['name' => 'otto', 'what' => 'fun', 'from' => '"Support" office@acme.com'];
        $mailer = new emil\mailer(['transport' => 'null://null'], $this->opts);
        $email = $mailer->send_to('webmaster@localhost', 't1', $data, false);

        $mime = $email->toString();
        $this->assertStringContainsString('Content-Type: text/plain', $mime);
        $this->assertStringContainsString('Subject: Welcome otto', $mime);
        $this->assertStringContainsString('From: Support <office@acme.com>', $mime);
        $this->assertStringContainsString("let's have fun", $mime);
    }

    public function testBasicFluid(): void {
        $data = ['name' => 'otto', 'what' => 'fun', 'from' => '"Support" office@acme.com'];
        $mailer = new emil\mailer(['transport' => 'null://null'], $this->opts);
        $email = $mailer->with('t1')->to('webmaster@localhost', $data, false);

        $mime = $email->toString();
        $this->assertStringContainsString('Content-Type: text/plain', $mime);
        $this->assertStringContainsString('Subject: Welcome otto', $mime);
        $this->assertStringContainsString('From: Support <office@acme.com>', $mime);
        $this->assertStringContainsString("let's have fun", $mime);
    }
}
