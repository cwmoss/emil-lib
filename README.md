# emil

self hosted microservice for transactional, templated based email

WIP - this is work in progress!

## TODO

- [ ] complete apib description
- [ ] data flow for templates
- [ ] who can edit transports?
- [ ] bulk send
- [ ] mail headers/ mail address handling
- [ ] add tests
- [ ] ui

## goals

most of my apps require sending emails. while migrating to the cloud stack, i felt the need to have an email service via http that works the same way as my old in-app functions. i wanted to be able to host this service by myself. most of time i don't need most of the functionality of the big boys' services. so i don't want to subscribe to any service just to be able to send a bunch of mails a month. what i needed was templating. having a single layout for all html emails of one project. 

the api should be easy to consume with `curl`.

you will need a SMTP account for sending.

you will have to create templates.

you can't send a raw email here. everything is based on prepared templates.

this service supports multiple tenants `orgs`.

## example request

    # endpoint of service: http://localhost:1199
    # org: acme
    # template: welcome
    # POST send/{org}/{template}
    curl http://localhost:1199/send/acme/welcome \
      -H "X-Emil-Api: 9ecc433c..."
      -d '{"name":"strange guy","to":"latoya@myspace.com","confirm_token":"mM-Juhu99-EEnlf"}'

    # with basic auth -u api:organization-api-key
	 curl -v http://localhost:1199/send/acme/welcome \
	   -u api:9ecc433c... \
	   -d '{"name":"strange guy","to":"latoya@myspace.com","from":"acme@example.org"}'

## quick start

	 git clone https://github.com/cwmoss/emil.git
	 cd emil
	 composer install
	 # follow the instructions


## Templates

Template Engine is [LightnCandy](https://github.com/zordius/lightncandy) -- handlebars for php

### Template Naming Conventions

* Layout template names start with `__` (two underscores)
* Partials start with `_` (one underscore)
* Message template names start with a lowercase character
* Template files for the email html part end with `.html`
* Template files for the email text part end with `.txt`
* Image Names must end with either `.png` or `.jgp`. Only PNG + JPEG images are supported.
* Maximum allowed file size is 100KB

### Providing Template Data

Template placeholder data can be provided in the following order -- highest priority first:

* via `send` request as json POST data
* via `txt` template as frontmatter data
* via `html` template as frontmatter data
* via organizations preferences data (can be set by the `POST manage/org/acme` request)


All of this data can be used in templates. There are some special keys that are used for e-mail generation:

* `to` receipients address
* `from`, `cc`, `bcc`, `reply-to`, `subject`
* `subject` is the only value, that can contain simple templating: `-d '{"subject":"hallo {{name}}"}'`


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

```
# send mail
curl http://localhost:1199/send/acme/hd_warning -u api:9ecc433c... 
```

## Organizations Preferences

Organizations need at least 2 options:  `api-key` (will be generated on creation) and `transport`, smtp account.
`transport` can only be set by admin.
`api-key` will be displayed only once on creation. it can be updated by organizations `manage/org/apikey` (??)

All other preferences are treated as template/mailheader data.


## Authorization

all admin actions `/admin` must be authorized by either a http header `X-Emil-Admin` containing the admin secret or the http basic auth header with username `admin` and the admin secret as password.

all email sending `/send` or management `/manage` api actions must be authorized by either a http header `X-Emil-Api` containing the organizations secret or the http basic auth header with username `api` and the organizations secret as password.

## API

komplete API Doks are here: [API Blueprint](api-description.apib)

### Send Message


### Manage Templates

Upload (multiple) Templates

`POST /manage/ORG/`

	curl http://localhost:1199/manage/ORG/upload \
		-F "u[]=@welcome.html" -F "u[]=@welcome.txt" \
		-F "u[]=@logo.png" -F "u[]=@__default.html"

Upload single Template

`PUT /manage/ORG/TEMPLATENAME.HTML`

	curl http://localhost:1199/manage/ORG/upload/logo.png -T logo.png


### Admin

Create Organization

## 5 different ways of configuring your server

### 1/ php server mode (ONLY FOR DEVELOPMENT)

all the examples are refering to this setup, since this is the easiest way for development

	 php -S localhost:1199 -t public/ public/index.php

	 # your endpoint
	 http://localhost:1199
	 # example: list organizations
	 http://localhost:1199/admin/orgs

### 2/ no rewrites, everything exposed to webserver (ONLY FOR DEVELOPMENT)

	 # your endpoint
	 http://localhost/dev/projects/emil/public/index.php
	 # example: list organizations
	 http://localhost/dev/projects/emil/public/index.php/admin/orgs

### 3/ rewrites are active, everything exposed to webserver (ONLY FOR DEVELOPMENT)

	 # copy dot.htaccess to public/.htaccess
	 cp dot.htacces public/.htaccess

	 # your endpoint
	 http://localhost/dev/projects/emil/public
	 # example: list organizations
	 http://localhost/dev/projects/emil/public/admin/orgs

### 4/ rewrites are active, /public is exposed by webserver via link

	 # copy dot.htaccess to public/.htaccess
	 cp dot.htacces public/.htaccess

	 # link /public to webserver-root/emil
	 ln -s /Users/rw/dev/emil/public /usr/local/var/www/emil

	 # your endpoint
	 http://localhost/emil
	 # example: list organizations
	 http://localhost/emil/admin/orgs

### 5/ rewrites are active, /public is exposed by webserver via link, environment set in webserver config (RECOMMENDED FOR PRODUCTION)

	 # add rewrite rules in apache location section <Location /emil>...</Location>
	 # add env in location section
	 # SetEnv EMIL_MAIL_TRANSPORT smtp://...
	 # SetEnv EMIL_ADMIN_KEY 449592d38c...

	 # link /public to webserver-root/emil
	 ln -s /Users/rw/dev/emil/public /usr/local/var/www/emil

	 # your endpoint
	 http://localhost/emil
	 # example: list organizations
	 http://localhost/emil/admin/orgs

there are certainly more combinations that are possible. just remember: dont use `.env` files in production. use real environment variables via nginx/apache virtuals host, docker, apache .htaccess etc.

## Credits

* swiftmailer/swiftmailer, sending emails
* zordius/lightncandy, handlebars implementation for php
* bramus/router, routing lib
* vlucas/phpdotenv, dotenv for php
* mnapoli/front-yaml, frontmatter parsing
* starter templates, salted by ..., simpleresponsive by leemunroe/responsive-html-email-template 
* acme logo by [Mackenzie Child](http://acmelogos.com/)