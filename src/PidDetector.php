<?php

namespace wus1\PidDetector;

class PidDetector
{
    protected $filename;
    public $already_running = false;
    public $alertEmailAddresses = ['kontakt@koderama.pl'];

    function __construct($directory, $filename, $maxTime = false, $callback = false)
    {
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        $this->filename = $directory . '/' . $filename . '.pid';
        if (is_writable($this->filename) || is_writable($directory)) {
            if ($this->getIsFileExist()) {
                $pid = (int)trim(file_get_contents($this->filename));
                if (posix_kill($pid, 0)) {
                    $this->already_running = true;
                }
            }
        } else {
            die("Cannot write to pid file '$this->filename'. Program execution halted.\n");
        }

        if (!$this->already_running) {
            $pid = getmypid();
            file_put_contents($this->filename, $pid);
        }
    }

    public function __destruct()
    {
        if (!$this->already_running && $this->getIsFileExist() && is_writeable($this->filename)) {
            unlink($this->filename);
        }
    }

    public function getIsFileExist()
    {
        return file_exists($this->filename);
    }

    public function getModificationDate()
    {
        $date = null;
        if ($this->getIsFileExist()) {
            $time = filemtime($this->filename);
            if ($time) {
                $date = new \DateTime();
                $date->setTimestamp($time);
            }
        }
        return $date;
    }

    public function getModificationDateHuman()
    {
        $date = $this->getModificationDate();
        if ($date) {
            return $date->format('Y-m-d H:i');
        } else {
            return null;
        }
    }

    public function getModificationTimeDays()
    {
        $date = $this->getModificationDate();
        $now = new \DateTime();
        $diff = $now->diff($date);
        return $diff->format("%a");

    }

    public function getIsModificationTimeOk()
    {
        $days = $this->getModificationTimeDays();
        if ($days == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function serveModificationTime()
    {
        if (!$this->getIsModificationTimeOk()) {
            return $this->sendAlertEmail();
        }
        return false;
    }

    public function getAlertEmailSubject()
    {
        return 'Błąd krytyczny! Wykryto skrypt CLI który uruchomiony jest zbyt długo';
    }

    public function sendAlertEmail()
    {
        return \Yii::$app->mailer->compose()
            ->setFrom(\Yii::$app->params['appEmail'])
            ->setTo($this->alertEmailAddresses)
            ->setTextBody('Aplikacja: ' . \Yii::$app->name . ', ' . \Yii::$app->id . ', ' . $this->filename)
            ->setSubject($this->getAlertEmailSubject())
            ->send();
    }
}
