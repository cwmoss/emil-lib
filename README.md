# emil

templated based email

WIP - this is work in progress!

## TODO

- [ ] data flow for templates
- [ ] who can edit transports?
- [ ] bulk send
- [ ] mail headers/ mail address handling
- [ ] add more tests

## quick start

     composer install

## Templates

Template Engine is [LightnCandy](https://github.com/zordius/lightncandy) -- handlebars for php

### Template Naming Conventions

- Layout template names start with `__` (two underscores)
- Partials start with `_` (one underscore)
- Message template names start with a lowercase character
- Template files for the email html part end with `.html`
- Template files for the email text part end with `.txt`
- Image Names must end with either `.png` or `.jgp`. Only PNG + JPEG images are supported.
- Maximum allowed file size is 100KB

### Providing Template Data

Template placeholder data can be provided in the following order -- highest priority first:

- via `send` request as json POST data
- via `txt` template as frontmatter data
- via `html` template as frontmatter data

All of this data can be used in templates. There are some special keys that are used for e-mail generation:

- `to` receipients address
- `from`, `cc`, `bcc`, `reply-to`, `subject`
- `subject` is the only value, that can contain simple templating: `-d '{"subject":"hallo {{name}}"}'`

```
# message template: hd_warning.txt
---
subject: our harddisc is full!
to: admin@acme.com
cc: paul@acme.com
category: maintenance
layout: default
---

it happend again. please empty trash.

```

```
# layout template: __default.txt
***
	{{> @partial-block }}
***

file under: {{category}}
```

## Credits

- swiftmailer, sending emails
- zordius/lightncandy, handlebars implementation for php
- mnapoli/front-yaml, frontmatter parsing
- starter templates, salted by ..., simpleresponsive by leemunroe/responsive-html-email-template
- acme logo by [Mackenzie Child](http://acmelogos.com/)
