# Images Processor Bot

The goal of task is to test your php skills. Please write your “best code” to get the
best contract.
## Introduction
The task is to write a command line script - Bot. It downloads images from the
Internet.

The workflow should be divided into the following independent steps:
- Schedule list of images to be downloaded.
- Download image and save to local storage.

Each step works with its own queue, e.g. <code>scheduler</code> adds new URLs to
<code>download</code> queue, <code>downloader</code> takes URL from <code>download</code> queue
and saves files.

After all there are the following queues:

- <code>download</code> - URLs ready to be downloaded.
- <code>done</code> - completed URLs.
- <code>failed</code> - failed URLs.

If step fails it should move URL to <code>failed</code> queue. For example, if image
could not be downloaded right now (e.g. due to network problems)
corresponding task should be moved to <code>failed</code> queue.

##Requirements

- Code should be uploaded to public github or bitbucket repository.
- Code format should conform PSR-2.
- There should be composer.json file with list of external
dependencies and autoloading rules.
- Bot should work under any Linux operation system.
- Feel free to choose any storage for queues, e.g. RabbitMQ, beanstalkd or
any relational database. It should be easy to install it on test machine.

##CLI Script

Bot should be implemented as a PHP command line script named <code>bot</code>.
```
$ bot
```
Description of commands are listed below in this section.
##Scheduler
Accepts a file with list of URLs to download and schedule them for download,
i.e. adds to <code>download</code> queue. Only <code>http</code> and <code>https</code> protocols are
supported. If URL is malformed then add it to <code>failed</code> queue instead.
```
$ bot schedule images.txt
```
File <code>images.txt</code> contains list of links, one per line:
```
http://www.example.com/image1.jpg
https://example.net/image2.png
http://example.org/image.php?id=123&size=small
```
##Downloader
Downloads images from the <code>download</code> queue to local temporary folder. If
image is not available moves URL to <code>failed</code> queue. May rename
downloaded image if necessary.
```
$ bot download
```