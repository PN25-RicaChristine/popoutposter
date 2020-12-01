<?php

namespace Composer;

use Composer\Semver\VersionParser;






class InstalledVersions
{
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => 'dev-master',
    'version' => 'dev-master',
    'aliases' => 
    array (
    ),
    'reference' => 'c068ce295ed2efeb9278d893f5d895adafc57d33',
    'name' => 'liquidweb/nexcess-mapps',
  ),
  'versions' => 
  array (
    'composer/installers' => 
    array (
      'pretty_version' => 'v1.9.0',
      'version' => '1.9.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'b93bcf0fa1fccb0b7d176b0967d969691cd74cca',
    ),
    'cweagans/composer-patches' => 
    array (
      'pretty_version' => '1.7.0',
      'version' => '1.7.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'ae02121445ad75f4eaff800cc532b5e6233e2ddf',
    ),
    'liquidweb/nexcess-mapps' => 
    array (
      'pretty_version' => 'dev-master',
      'version' => 'dev-master',
      'aliases' => 
      array (
      ),
      'reference' => 'c068ce295ed2efeb9278d893f5d895adafc57d33',
    ),
    'liquidweb/nexcess-mapps-dashboard' => 
    array (
      'pretty_version' => 'v1.3.5',
      'version' => '1.3.5.0',
      'aliases' => 
      array (
      ),
      'reference' => '0acdf2d33a00eddc379d0670cfad53465fd45cb9',
    ),
    'liquidweb/woocommerce-upper-limits' => 
    array (
      'pretty_version' => 'v1.0.3',
      'version' => '1.0.3.0',
      'aliases' => 
      array (
      ),
      'reference' => '594dc658c309572a5968d5481b2ff974f0e1ba4e',
    ),
    'roundcube/plugin-installer' => 
    array (
      'replaced' => 
      array (
        0 => '*',
      ),
    ),
    'shama/baton' => 
    array (
      'replaced' => 
      array (
        0 => '*',
      ),
    ),
    'stevegrunwell/wp-admin-tabbed-settings-pages' => 
    array (
      'pretty_version' => 'v0.2.0',
      'version' => '0.2.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'd8fbcce4bde4fbee623c659166b42bce0a7d09d8',
    ),
    'stevegrunwell/wp-cache-remember' => 
    array (
      'pretty_version' => 'v1.1.1',
      'version' => '1.1.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'f37b8e79d9cb05f5fd0e8035b0ffecbb1ded182e',
    ),
    'wp-cli/wp-config-transformer' => 
    array (
      'pretty_version' => 'v1.2.8',
      'version' => '1.2.8.0',
      'aliases' => 
      array (
      ),
      'reference' => '0bb2b9162c38ca72370380aea11dc06e431e13a5',
    ),
    'wpackagist-plugin/display-environment-type' => 
    array (
      'pretty_version' => '1.3',
      'version' => '1.3.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'tags/1.3',
    ),
    'wpackagist-plugin/varnish-http-purge' => 
    array (
      'pretty_version' => '4.8.1',
      'version' => '4.8.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'tags/4.8.1',
    ),
    'wpackagist-plugin/wp-fail2ban' => 
    array (
      'pretty_version' => '4.3.0.8',
      'version' => '4.3.0.8',
      'aliases' => 
      array (
      ),
      'reference' => 'tags/4.3.0.8',
    ),
  ),
);







public static function getInstalledPackages()
{
return array_keys(self::$installed['versions']);
}









public static function isInstalled($packageName)
{
return isset(self::$installed['versions'][$packageName]);
}














public static function satisfies(VersionParser $parser, $packageName, $constraint)
{
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
}










public static function getVersionRanges($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

$ranges = array();
if (isset(self::$installed['versions'][$packageName]['pretty_version'])) {
$ranges[] = self::$installed['versions'][$packageName]['pretty_version'];
}
if (array_key_exists('aliases', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['aliases']);
}
if (array_key_exists('replaced', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['replaced']);
}
if (array_key_exists('provided', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['provided']);
}

return implode(' || ', $ranges);
}





public static function getVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['version'])) {
return null;
}

return self::$installed['versions'][$packageName]['version'];
}





public static function getPrettyVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['pretty_version'])) {
return null;
}

return self::$installed['versions'][$packageName]['pretty_version'];
}





public static function getReference($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['reference'])) {
return null;
}

return self::$installed['versions'][$packageName]['reference'];
}





public static function getRootPackage()
{
return self::$installed['root'];
}







public static function getRawData()
{
return self::$installed;
}



















public static function reload($data)
{
self::$installed = $data;
}
}
