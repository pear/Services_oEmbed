<?php

require_once('PEAR/PackageFileManager2.php');

PEAR::setErrorHandling(PEAR_ERROR_DIE);

$packagexml = new PEAR_PackageFileManager2;

$packagexml->setOptions(array(
    'baseinstalldir'    => '/',
    'simpleoutput'      => true,
    'packagedirectory'  => './',
    'filelistgenerator' => 'file',
    'ignore'            => array('phpunit-bootstrap.php', 'phpunit.xml', 'test.php', 'generatePackage.php'),
    'dir_roles' => array('tests' => 'test'),
));

$packagexml->setPackage('Services_oEmbed');
$packagexml->setSummary('A package for consuming oEmbed');
$packagexml->setDescription(
    'oEmbed (http://www.oembed.com) is an open specification for discovering '
    . 'more information about URI\'s. oEmbed services return structure meta-data '
    . 'about a URI (e.g. type of object, title, author information, thumbnail details, etc.).'
);

$packagexml->setChannel('pear.php.net');
$packagexml->setAPIVersion('0.2.0');
$packagexml->setReleaseVersion('0.2.0');

$packagexml->setReleaseStability('alpha');

$packagexml->setAPIStability('alpha');

$packagexml->setNotes('
* Fixes E_STRICT warnings, per #18042
* Removed dependency on PEAR_Exception
* Removed dependency on HTTP_Request (lingering require_once)
* Added support for phpunit 3.5
* Added generatePackage.php for easy package maintenance
');
$packagexml->setPackageType('php');
$packagexml->addRelease();

$packagexml->detectDependencies();

$packagexml->addMaintainer('lead',
                           'shupp',
                           'Bill Shupp',
                           'shupp@php.net');
$packagexml->addMaintainer('lead',
                           'jstump',
                           'Joe Stump',
                           'joe@joestump.net');
$packagexml->setLicense('New BSD License',
                        'http://www.opensource.org/licenses/bsd-license.php');

$packagexml->setPhpDep('5.2.0');
$packagexml->setPearinstallerDep('1.4.0b1');
$packagexml->addPackageDepWithChannel('required', 'Validate', 'pear.php.net', '0.8.4');
$packagexml->addPackageDepWithChannel('required', 'Net_URL2', 'pear.php.net', '0.2.0');
$packagexml->addExtensionDep('required', 'json'); 
$packagexml->addExtensionDep('required', 'pcre'); 
$packagexml->addExtensionDep('required', 'libxml'); 
$packagexml->addExtensionDep('required', 'SimpleXML'); 
$packagexml->addExtensionDep('required', 'curl'); 


$packagexml->generateContents();
$packagexml->writePackageFile();

?>
