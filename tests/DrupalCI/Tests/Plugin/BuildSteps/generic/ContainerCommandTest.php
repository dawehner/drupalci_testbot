<?php

/**
 * @file
 * Contains \DrupalCI\Tests\Plugin\BuildSteps\generic\CommandTest.
 */

namespace DrupalCI\Tests\Plugin\BuildSteps\generic;

use Docker\API\Model\Container;
use DrupalCI\Plugin\BuildSteps\generic\ContainerCommand;
use DrupalCI\Tests\DrupalCITestCase;

/**
 * @coversDefaultClass DrupalCI\Plugin\BuildSteps\generic\ContainerCommand
 */
class ContainerCommandTest extends DrupalCITestCase {

  /**
   * @covers ::run
   */
  public function testRun() {
    $cmd = 'test_command test_argument';
    $instance = new Container([]);

    $body = $this->getMock('GuzzleHttp\Stream\StreamInterface');

    $docker = $this->getMockBuilder('Docker\Docker')
      ->disableOriginalConstructor()
      ->setMethods(['getExecManager', 'getContainerManager'])
      ->getMock();

    $job = $this->getMockBuilder('DrupalCI\Plugin\JobTypes\JobInterface')
      ->getMockForAbstractClass();
    $job->expects($this->once())
      ->method('getDocker')
      ->will($this->returnValue($docker));
    $job->expects($this->once())
      ->method('getExecContainers')
      ->will($this->returnValue(['php' => [['id' => 'drupalci/php-5.4']]]));

    $exec_config = $this->getMockBuilder('Docker\API\Model\ExecConfig')
      ->setMethods(['setTty', 'SetAttachStderr', 'setAttachStdout', 'setAttachStdin', 'setCmd'])
      ->getMock();
    $exec_config->expects($this->once())
      ->method('setTty')
      ->will($this->returnValue($this->returnSelf()));
    $exec_config->expects($this->once())
      ->method('setAttachStderr')
      ->will($this->returnValue($this->returnSelf()));
    $exec_config->expects($this->once())
      ->method('setAttachStdout')
      ->will($this->returnValue($this->returnSelf()));
    $exec_config->expects($this->once())
      ->method('setAttachStdin')
      ->will($this->returnValue($this->returnSelf()));
    $exec_config->expects($this->once())
      ->method('setCmd');

    $exec_manager = $this->getMockBuilder('Docker\Manager\ExecManager')
      ->disableOriginalConstructor()
      ->setMethods(['create', 'start', 'find'])
      ->getMock();
    $docker->expects($this->once())
      ->method('getExecManager')
      ->will($this->returnValue($exec_manager));

    $exec_result = $this->getMock('Docker\API\Model\ExecCreateResult');

    $exec_manager->expects($this->once())
      ->method('create')
      ->will($this->returnValue($exec_result));
    $exec_result->method('getId')
      ->willReturn('abcdef');
    $exec_result->expects($this->once())
      ->method('getId');

    $exec_start_config = $this->getMockBuilder('Docker\API\Model\ExecStartConfig')
      ->setMethods(['setTty', 'setDetach'])
      ->getMock();
    $exec_start_config->expects($this->once())
      ->method('setTty')
      ->will($this->returnValue($this->returnSelf()));
    $exec_start_config->expects($this->once())
      ->method('setDetach')
      ->will($this->returnValue($this->returnSelf()));

    $stream = $this->getMockBuilder('Docker\API\Model\DockerRawStream')
      ->setMethods(['onStderr', 'onStdout', 'wait'])
      ->getMock();

    $exec_manager->expects($this->once())
      ->method('start')
      ->with($exec_result->getId(), $exec_start_config)
      ->will($this->returnValue($stream));

    $exec_command = $this->getMockBuilder('Docker\API\Model\ExecCommand')
      ->setMethods(['getExitCode'])
      ->getMock();
    $exec_command->expects($this->once())
      ->method('getExitCode');
    $exec_manager->expects($this->once())
      ->method('find')
      ->will($this->returnValue($exec_command));



    $command = new ContainerCommand();
    $command->run($job, $cmd);
  }

}
