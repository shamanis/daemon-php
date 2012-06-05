<?php
require_once 'daemon.php';

class MyDaemon extends DaemonPHP {

    public function run() {
        while (true) {
        }
    }
}

$daemon = new MyDaemon('/tmp/test.pid');

$daemon->setChroot('/home/shaman/work/PHPTest/daemon') //Устанавливаем каталог для chroot
        ->setLog('/my.log')
        ->setErr('/my.err') //После chroot файлы будут созданы в /home/shaman/work/PHPTest/daemon
        ->handle($argv);
?>
