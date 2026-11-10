<?php

use Psr\Log\AbstractLogger;
use Softspring\CmsBundle\Test\Integration\CompiledCacheFixture;
use Softspring\CmsBundle\Entity\CompiledData;

require $argv[1];
require __DIR__.'/CompiledCacheFixture.php';

$mode = $argv[6] ?? 'redis';
$logger = new class($argv[3].'.errors') extends AbstractLogger {
    public function __construct(private string $path) {}
    public function log($level, $message, array $context = []): void
    {
        if (in_array($level, ['warning', 'error', 'critical', 'alert', 'emergency'], true)) {
            file_put_contents($this->path, json_encode([$level, (string) $message, $context])."\n", FILE_APPEND | LOCK_EX);
        }
    }
};
[$db, $store, $cache, $version] = CompiledCacheFixture::create($argv[2], ($argv[5] ?? '') === 'section', !in_array($mode, ['none', 'churn-none'], true), $logger);
$key = $argv[4] ?? 'site/en';
if (isset($argv[8])) {
    touch($argv[8].'.ready.'.getmypid());
    $deadline = microtime(true) + 20;
    while (!file_exists($argv[8])) {
        if (microtime(true) > $deadline) exit(90);
        usleep(10000);
    }
}
for ($i = 0; $i < (int) ($argv[7] ?? 1); ++$i) {
    $generation = $db->fetchOne('SELECT compiled_generation FROM '.$argv[2].'_version WHERE id = ?', [$argv[2]]);
    (new ReflectionProperty($version, 'compiledGeneration'))->setValue($version, $generation);
    $result = $cache->get($version, $key, function () use ($version, $argv, $key, $db): CompiledData {
        if ($db->isTransactionActive()) throw new RuntimeException('Rendering inside a cache transaction');
        file_put_contents($argv[3], "compiled\n", FILE_APPEND | LOCK_EX);
        usleep($key === 'site/fr' ? 1000000 : 300000);
        $value = new CompiledData();
        $value->setVersion($version);
        $value->setKey($key);
        $value->setDataPart('content', 'Shared rendered HTML');

        return $value;
    });
    if ($result->getDataPart('content') !== 'Shared rendered HTML') exit(91);
    if ($mode === 'churn' || $mode === 'churn-none') usleep(10000);
}
