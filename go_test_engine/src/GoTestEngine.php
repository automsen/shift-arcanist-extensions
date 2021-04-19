<?php
final class GoTestEngine extends ArcanistUnitTestEngine {
  public function run() {
    $command = $this->getConfigurationManager()->getConfigFromAnySource('unit.engine.go.command');
    $future = new ExecFuture($command);

    $sleep_time_micros = 500 * 1000; // 500ms -> microseconds

    do {
      list($stdout, $stderr) = $future->read();
      echo $stdout;
      echo $stderr;
      usleep($sleep_time_micros);
    } while (!$future->isReady());

    list($error, $stdout, $stderr) = $future->resolve();

    $parser = new ArcanistGoTestResultParser();
    return $parser->parseTestResults("", $stdout);
  }
}
