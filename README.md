DaemonPHP Class
===============

Introduction
------------

This class allows you to easily create UNIX-daemons.

Start-up and use
----------------
All you need to do - is to override the abstract method run()

For example:
    
    <?php
    require_once 'daemon.php';

    class MyDaemon extends DaemonPHP {

        public function run() {
            while (true) {
            }
        }
    }
    
    $daemon = new MyDaemon('/tmp/test.pid');

    $daemon->setChroot('/home/shaman/work/PHPTest/daemon')
        ->setLog('/my.log')
        ->setErr('/my.err')
        ->handle($argv);
    }
    ?>
As an example of a file run.php


* To start:

    `localhost:~$ php run.php start`

* To stop:

    `localhost:~$ php run.php stop`

* To check the status:

    `localhost:~$ php run.php status`

* To restart:

    `localhost:~$ php run.php restart`

Constructor of the class DaemonPHP
----------------------------------
In the constructor you can pass the absolute path to the PID-file daemon.

If no path is passed to the constructor, the default PID-file will be in the same directory under the name `daemon-php.pid`

Method setChroot()
------------------
This method performs chroot to the specified directory.

To do this requires the right of user root.

After completing chrut root directory will be referred to the directory.

For example:

    <?php
    //Some code
    
    $daemon->setChroot('/home/shaman/work/PHPTest/daemon') //Directory for chroot
        ->setLog('/my.log')
        ->setErr('/my.err'); //After chroot files can created in /home/shaman/work/PHPTest/daemon
    
    //Some code
    ?>

Method setLog()
---------------
This method sets the absolute path for the log file.

By default, the file will be created in the current directory under the name `daemon-php.log`

Method setErr()
---------------
This method sets the absolute path for the error log file.

By default, the file will be created in the current directory under the name `daemon-php.err`

Method handle($argv)
---------------
This method handles the command line arguments - `$argv`.

File php_error.php
------------------
This file only appears if there are errors in the PHP.
