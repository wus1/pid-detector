<?php

class PidDetectorTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    protected function getFolderPath()
    {
        return Yii::getAlias(__DIR__ . '/../../vendor/runtime/pids/');
    }

    protected function getFilePath()
    {
        return $this->getFolderPath() . $this->getFileNameWithExtension();
    }

    protected function getFileNameWithExtension()
    {
        return 'tmp.pid';
    }

    protected function getFileName()
    {
        return 'tmp';
    }

    // tests
    public function testPidFileCreated()
    {
        $pidDetector = new \wus1\PidDetector\PidDetector($this->getFolderPath(), $this->getFileName());
        $this->assertTrue($pidDetector->getIsFileExist());
    }

    // tests
    public function testPidBlockAnotherInstance()
    {
        $pidDetector = new \wus1\PidDetector\PidDetector($this->getFolderPath(), $this->getFileName());
        $this->assertTrue($pidDetector->getIsFileExist());

        $pidDetector2 = new \wus1\PidDetector\PidDetector($this->getFolderPath(), $this->getFileName());
        $this->assertTrue($pidDetector2->already_running);
    }

    // tests
    public function testPidFileDeleted()
    {
        $pidDetector = new \wus1\PidDetector\PidDetector($this->getFolderPath(), $this->getFileName());
        $this->assertTrue($pidDetector->getIsFileExist());
        $this->assertTrue(file_exists($this->getFilePath()));
        unset($pidDetector);
        $this->assertFalse(file_exists($this->getFilePath()));
    }

    // tests
    public function testPidFileReadMakeFile()
    {
        $pidDetector = new \wus1\PidDetector\PidDetector($this->getFolderPath(), $this->getFileName());
        $this->assertStringContainsString(date('Y-m-d H:i'), $pidDetector->getModificationDateHuman());
    }

    // tests
    public function testPidFileMakeTimeOk()
    {
        $pidDetector = new \wus1\PidDetector\PidDetector($this->getFolderPath(), $this->getFileName());
        $this->assertStringContainsString('0', $pidDetector->getModificationTimeDays());
        $this->assertTrue($pidDetector->getIsModificationTimeOk());
    }

    // tests
    public function testPidFileMakeTimeNotOk()
    {
        $date = new DateTime();
        $date->modify('-1 day');
        $pidDetector = new \wus1\PidDetector\PidDetector($this->getFolderPath(), $this->getFileName());
        touch($this->getFilePath(), $date->getTimestamp());
        $this->assertStringContainsString('1', $pidDetector->getModificationTimeDays());
        $this->assertFalse($pidDetector->getIsModificationTimeOk());
    }

//    // tests
    public function testPidFileMakeTimeAlert()
    {
        $date = new DateTime();
        $date->modify('-1 day');
        $pidDetector = new \wus1\PidDetector\PidDetector($this->getFolderPath(), $this->getFileName());
        touch($this->getFilePath(), $date->getTimestamp());
        $pidDetector2 = new \wus1\PidDetector\PidDetector($this->getFolderPath(), $this->getFileName());
        $this->assertTrue($pidDetector2->already_running);
        $this->assertTrue($pidDetector2->sendAlertEmail());
        
        $this->tester->seeEmailIsSent();
        $emailMessage = $this->tester->grabLastSentEmail();
        $this->assertArrayHasKey('kontakt@koderama.pl', $emailMessage->getTo());
        $this->assertStringContainsString($pidDetector->getAlertEmailSubject(), $emailMessage->getSubject());
    }
}