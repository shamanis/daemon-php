<?php
/*
Author: Petr Bondarenko
E-mail: public@shamanis.com
Date: 31 May 2012
Description: Class for create UNIX-daemon
*/

class DaemonException extends Exception {}

abstract class DaemonPHP {

    protected $_baseDir;
    protected $_chrootDir = null;
    protected $_pid;
    protected $_log;
    protected $_err;
    
    /**
    * Конструктор класса. Принимает путь к pid-файлу
    * @param string $path Абсолютный путь к PID-файлу
    */
    public function __construct($path=null) {
        $this->_baseDir = dirname(__FILE__);
        $this->_log = $this->_baseDir . '/daemon-php.log';
        $this->_err = $this->_baseDir . '/daemon-php.err';
        if ($path === null) {
            $this->_pid = $this->_baseDir . '/daemon-php.pid';
        } else {
            $this->_pid = $path;
        }
    }
    
    /**
    * Метод устанавливает путь log-файла
    * @param string $path Абсолютный путь к log-файлу
    * @return DaemonPHP
    */
    final public function setLog($path) {
        $this->_log = $path;
        return $this;
    }
    
    /**
    * Метод устанавливает путь err-файла
    * @param string $path Абсолютный путь к err-файлу
    * @return DaemonPHP
    */
    final public function setErr($path) {
        $this->_err = $path;
        return $this;
    }
    
    /**
    * Метод позволяет установить директорию,
    * в которую будет выполнен chroot после старта демона.
    * Данный метод служит для решения проблем безопасности.
    * @param string $path Абсолютный путь chroot-директории 
    */
    final public function setChroot($path) {
        if (function_exists('chroot') {
            throw new DaemonException('Function chroot() has no. Please update you PHP version.');
        }
        $this->_chrootDir = $path;
        return $this;
    }
    
    /**
    * Метод выполняет демонизацию процесса, через double fork
    */
    final protected function demonize() {
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new DaemonException('Not fork process!');
        } else if ($pid) {
            exit(0);
        }
        
        posix_setsid();
        chdir('/');
        
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new DaemonException('Not double fork process!');
        } else if ($pid) {
            $fpid = fopen($this->_pid, 'wb');
            fwrite($fpid, $pid);
            fclose($fpid);
            exit(0);
        }
        
        posix_setsid();
        chdir('/');
        ini_set('error_log', $this->_baseDir . '/php_error.log');
        
        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);
        $STDIN = fopen('/dev/null', 'r');
        
        if ($this->_chrootDir !== null) {
            chroot($this->_chrootDir);
        }
        
        $STDOUT = fopen($this->_log, 'ab');
        if (!is_writable($this->_log))
            throw new DaemonException('LOG-file is not writable!');
        $STDERR = fopen($this->_err, 'ab');
        if (!is_writable($this->_err))
            throw new DaemonException('ERR-file is not writable!');
        $this->run();
    }
    
    /**
    * Метод возвращает PID процесса
    * @return int PID процесса
    */
    final protected function getPID() {
        if (is_readable($this->_pid)) {
            $fpid = (int) file_get_contents($this->_pid);
            return $fpid;
        } else {
            throw new DaemonException('PID-file is not readable!');
        }
    }
    
    /**
    * Метод стартует работу и вызывает метод demonize()
    */
    final public function start() {
        if (file_exists($this->_pid) && posix_kill($this->getPID(), 0)) {
            echo "Process is running on PID: " . $this->getPID() . PHP_EOL;
        } else {
            if (file_exists($this->_pid)) {
                //Если демон не отвечает, а PID-файл существует
                unlink($this->_pid);
            }
            echo "Starting..." . PHP_EOL;
            $this->demonize();
        }
    }
    
    /**
    * Метод останавливает демон
    */
    final public function stop() {
        if (file_exists($this->_pid) && posix_kill($this->getPID(), 0)) {
            echo "Stopping ... ";
            posix_kill($this->getPID(), SIGTERM);
            unlink($this->_pid);
            echo "OK" . PHP_EOL;
        } else {
            if (file_exists($this->_pid)) {
                //Если демон не отвечает, а PID-файл существует
                unlink($this->_pid);
            }
            echo "Process not running!" . PHP_EOL;
        }
    }
    
    /**
    * Метод рестартует демон последовательно вызвав stop() и start()
    */
    final public function restart() {
        $this->stop();
        $this->start();
    }
    
    /**
    * Метод проверяет работу демона
    */
    final public function status() {
        if (file_exists($this->_pid) && posix_kill($this->getPID(), 0)) {
            echo "Process is running on PID: " . $this->getPID() . PHP_EOL;
        } else {
            if (file_exists($this->_pid)) {
                //Если демон не отвечает, а PID-файл существует
                unlink($this->_pid);
            }
            echo "Process not running!" . PHP_EOL;
        }
    }
    
    /**
    * Метод обрабатывает аргументы командной строки
    */
    final public function handle($argv) {
        switch ($argv[1]) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'restart':
                $this->restart();
                break;
            case 'status':
                $this->status();
                break;
            default:
                echo "Unknown command!" . PHP_EOL .
                    "Use: " . $argv[0] . " start|stop|restart|status" . PHP_EOL;
                break;
        }
    }
    
    /**
    * Основной класс демона, в котором выполняется работа.
    * Его необходимо переопределить
    */
    abstract public function run();
}
?>
