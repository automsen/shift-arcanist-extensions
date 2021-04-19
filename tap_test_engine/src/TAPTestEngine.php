<?php

final class TAPTestEngine extends ArcanistUnitTestEngine {

  public function run() {
    $command = $this->getConfigurationManager()->getConfigFromAnySource('unit.engine.tap.command');

    $future = new ExecFuture($command);
    $sleep_time_micros = 500 * 1000;
    do {
      list($stdout, $stderr) = $future->read();
      echo $stdout;
      echo $stderr;
      usleep($sleep_time_micros);
    } while (!$future->isReady());

    list($error, $stdout, $stderr) = $future->resolve();
    return $this->parseOutput($stdout);
  }

  public function shouldEchoTestResults() {
    return true;
  }

  private function parseOutput($output) {
    $results = array();
    $lines = explode(PHP_EOL, $output);

    foreach($lines as $index => $line) {
      preg_match('/^(not ok|ok)\s+\d+\s+-?(.*)/', $line, $matches);
      if (count($matches) < 3) continue;

      $result = new ArcanistUnitTestResult();
      $result->setName(trim($matches[2]));

      switch (trim($matches[1])) {
        case 'ok':
          $result->setResult(ArcanistUnitTestResult::RESULT_PASS);
          break;

        case 'not ok':
          $exception_message = trim($lines[$index + 1]);
          $result->setResult(ArcanistUnitTestResult::RESULT_FAIL);
          $result->setUserData($exception_message);
          break;

        default:
          break;
      }

      $results[] = $result;
    }

    return $results;
  }
}
