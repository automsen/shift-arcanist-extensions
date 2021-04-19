<?php

final class FlowTestEngine extends ArcanistUnitTestEngine {

  const FLOW_NO_ERROR_MESSAGE = 'Found 0 errors';

  public function run() {
    $command = $this->getConfigurationManager()->getConfigFromAnySource('unit.engine.flow.command');

    $future = new ExecFuture($command);
    $sleep_time_micros = 500 * 1000;
    do {
      list($stdout, $stderr) = $future->read();
      usleep($sleep_time_micros);
    } while (!$future->isReady());

    list($error, $stdout, $stderr) = $future->resolve();
    if ($stderr) {
      echo $stderr;
    }
    $test_result = new ArcanistUnitTestResult();
    $test_result->setName('Flow: JavaScript static type checking');
    if (strpos($stdout, self::FLOW_NO_ERROR_MESSAGE) !== false) {
      $test_result->setResult(ArcanistUnitTestResult::RESULT_PASS);
    } else {
      $test_result->setResult(ArcanistUnitTestResult::RESULT_FAIL);
    }
    $test_result->setUserData($stdout);
    return array($test_result);
  }

  public function shouldEchoTestResults() {
    return false;
  }

  /**
   * CURRENTLY UNUSED
   * The Flow error messages are too detailed so instead display the error itself.
   * This was a stab at showing the error message from $stdout
   *
   *  scripts/flow check --json
   *
   */
  private function parseOutput($output) {
    $parser = new PhutilJSONParser();
    $report = $parser->parse($output);

    $results = array();
    if (!$report['passed']) {
      foreach ($report['errors'] as $error) {
        $result = new ArcanistUnitTestResult();
        $message0 = $error['message'][0];
        $result->setName("{$message0['path']}:{$message0['line']}:{$message0['start']},{$message0['end']}: {$message0['descr']}");
        $result->setResult(ArcanistUnitTestResult::RESULT_FAIL);
        $result->setUserData($error['message'][1]['descr']);

        $results[] = $result;
      }
    }

    return $results;
  }
}
