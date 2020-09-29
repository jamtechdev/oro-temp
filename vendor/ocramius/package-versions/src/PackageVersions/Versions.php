<?php

declare(strict_types=1);

namespace PackageVersions;

/**
 * This class is generated by ocramius/package-versions, specifically by
 * @see \PackageVersions\Installer
 *
 * This file is overwritten at every run of `composer install` or `composer update`.
 */
final class Versions
{
    public const ROOT_PACKAGE_NAME = 'oro/commerce-crm-application';
    /**
     * Array of all available composer packages.
     * Dont read this array from your calling code, but use the \PackageVersions\Versions::getVersion() method instead.
     *
     * @var array<string, string>
     * @internal
     */
    public const VERSIONS          = array (
  'akeneo/batch-bundle' => '0.4.11@712713ee1e15059ce3aed47ade5b83123ab69ac1',
  'ass/xmlsecurity' => 'v1.1.1@c8976519ebbf6e4d953cd781d09df44b7f65fbb8',
  'behat/transliterator' => 'v1.3.0@3c4ec1d77c3d05caa1f0bf8fb3aae4845005c7fc',
  'besimple/soap-client' => 'v0.2.6@50f186fd3e9fafc4ce2d194d5d18e3987d5fbf41',
  'besimple/soap-common' => 'v0.2.6@08aa78c7f1cae4b23cce5e8928dca7243ca4e4fa',
  'box/spout' => 'v2.7.3@3681a3421a868ab9a65da156c554f756541f452b',
  'brick/math' => '0.8.11@bf4894d6373d396666d280a42ecdf1ad75f8222f',
  'cboden/ratchet' => 'v0.4.2@57721e1f184f9e29378fc5363867c47ddda743fd',
  'clue/stream-filter' => 'v1.4.1@5a58cc30a8bd6a4eb8f856adf61dd3e013f53f71',
  'composer/ca-bundle' => '1.2.6@47fe531de31fca4a1b997f87308e7d7804348f7e',
  'composer/composer' => '1.6.5@b184a92419cc9a9c4c6a09db555a94d441cb11c9',
  'composer/semver' => '1.5.1@c6bea70230ef4dd483e6bbcab6005f682ed3a8de',
  'composer/spdx-licenses' => '1.5.2@7ac1e6aec371357df067f8a688c3d6974df68fa5',
  'container-interop/container-interop' => '1.2.0@79cbf1341c22ec75643d841642dd5d6acd83bdb8',
  'defuse/php-encryption' => 'v2.2.1@0f407c43b953d571421e0020ba92082ed5fb7620',
  'doctrine/annotations' => 'v1.6.1@53120e0eb10355388d6ccbe462f1fea34ddadb24',
  'doctrine/cache' => '1.10.0@382e7f4db9a12dc6c19431743a2b096041bcdd62',
  'doctrine/collections' => 'v1.5.0@a01ee38fcd999f34d9bfbcee59dbda5105449cbf',
  'doctrine/common' => '2.12.0@2053eafdf60c2172ee1373d1b9289ba1db7f1fc6',
  'doctrine/data-fixtures' => '1.3.3@f0ee99c64922fc3f863715232b615c478a61b0a3',
  'doctrine/dbal' => 'v2.7.2@c0e5736016a51b427a8cba8bc470fbea78165819',
  'doctrine/doctrine-bundle' => '1.9.1@703fad32e4c8cbe609caf45a71a1d4266c830f0f',
  'doctrine/doctrine-cache-bundle' => '1.4.0@6bee2f9b339847e8a984427353670bad4e7bdccb',
  'doctrine/doctrine-fixtures-bundle' => 'v2.4.1@74b8cc70a4a25b774628ee59f4cdf3623a146273',
  'doctrine/event-manager' => '1.1.0@629572819973f13486371cb611386eb17851e85c',
  'doctrine/inflector' => '1.3.1@ec3a55242203ffa6a4b27c58176da97ff0a7aec1',
  'doctrine/instantiator' => '1.3.0@ae466f726242e637cebdd526a7d991b9433bacf1',
  'doctrine/lexer' => '1.2.0@5242d66dbeb21a30dd8a3e66bf7a73b66e05e1f6',
  'doctrine/orm' => 'v2.6.3@434820973cadf2da2d66e7184be370084cc32ca8',
  'doctrine/persistence' => '1.3.1@5fde0b8c1261e5089ece1525e68be2be27c8b2a6',
  'doctrine/reflection' => 'v1.1.0@bc420ead87fdfe08c03ecc3549db603a45b06d4c',
  'egulias/email-validator' => '2.1.15@e834eea5306d85d67de5a05db5882911d5b29357',
  'evenement/evenement' => 'v3.0.1@531bfb9d15f8aa57454f5f0285b18bec903b8fb7',
  'ezyang/htmlpurifier' => 'v4.10.0@d85d39da4576a6934b72480be6978fb10c860021',
  'friendsofsymfony/jsrouting-bundle' => '2.2.2@be6c7ec335d0f0cf3b6d152d6b64d5772f5919b6',
  'friendsofsymfony/rest-bundle' => '2.3.1@1abdf3d82502ac67b93c7f84c844fa147f0ec70e',
  'gedmo/doctrine-extensions' => 'v2.4.39@c549b40bff560380c53812283d25ce42ee0992e4',
  'gos/pnctl-event-loop-emitter' => 'v0.1.7@93bb0f0e60e4e1f4025f77c8a4fd9ae0c3b45fb3',
  'gos/pubsub-router-bundle' => 'v0.3.5@a3f9666455dc42f38a7ce31ca2fc55bd27421ea0',
  'gos/web-socket-bundle' => 'v1.8.12@15174761596ebe9fb58d037ec144822531922f66',
  'gos/websocket-client' => 'v0.1.2@13bb38cb01acee648fea1a6ca4ad3dc6148da7fe',
  'guzzle/guzzle' => 'v3.7.4@b170b028c6bb5799640e46c8803015b0f9a45ed9',
  'guzzlehttp/psr7' => '1.6.1@239400de7a173fe9901b9ac7c06497751f00727a',
  'hwi/oauth-bundle' => '0.6.3@0963709b04d8ac0d6d6c0c78787f6f59bd0d9a01',
  'imagine/imagine' => 'v0.7.1@a9a702a946073cbca166718f1b02a1e72d742daa',
  'incenteev/composer-parameter-handler' => 'v2.1.3@933c45a34814f27f2345c11c37d46b3ca7303550',
  'jdorn/sql-formatter' => 'v1.2.17@64990d96e0959dff8e059dfcdc1af130728d92bc',
  'jms/cg' => '1.2.0@2152ea2c48f746a676debb841644ae64cae27835',
  'jms/metadata' => '1.7.0@e5854ab1aa643623dc64adde718a8eec32b957a8',
  'jms/parser-lib' => '1.0.0@c509473bc1b4866415627af0e1c6cc8ac97fa51d',
  'jms/serializer' => '1.14.0@ee96d57024af9a7716d56fcbe3aa94b3d030f3ca',
  'jms/serializer-bundle' => '2.4.4@92ee808c64c1c180775a0e57d00e3be0674668fb',
  'justinrainbow/json-schema' => '5.2.9@44c6787311242a979fa15c704327c20e7221a0e4',
  'knplabs/gaufrette' => 'v0.9.0@786247eba04d4693e88a80ca9fdabb634675dcac',
  'knplabs/knp-gaufrette-bundle' => 'v0.5.2@2a3d24efda257023e5d3f21866f1ff18f50c60ba',
  'knplabs/knp-menu' => '2.3.0@655630a1db0b72108262d1a844de3b1ba0885be5',
  'knplabs/knp-menu-bundle' => '2.2.2@267027582a1f1e355276f796f8da0e9f82026bf1',
  'kriswallsmith/assetic' => 'v1.0.5@8ab3638325af9cd144242765494a9dc9b53ab430',
  'kriswallsmith/buzz' => '1.0.1@3d436434ab6019a309b8813839a3693997d03774',
  'lcobucci/jwt' => '3.3.1@a11ec5f4b4d75d1fcd04e133dede4c317aac9e18',
  'leafo/scssphp' => 'v0.6.7@562213cd803e42ea53b0735554794c4022d8db89',
  'league/event' => '2.2.0@d2cc124cf9a3fab2bb4ff963307f60361ce4d119',
  'league/oauth2-server' => '7.4.0@2eb1cf79e59d807d89c256e7ac5e2bf8bdbd4acf',
  'lexik/maintenance-bundle' => 'v2.1.5@3a3e916776934a95834235e4a1d71e4595d515f5',
  'liip/imagine-bundle' => '2.0.0@66959e113d1976d0b23a2617771c2c65d62ea44b',
  'michelf/php-markdown' => '1.9.0@c83178d49e372ca967d1a8c77ae4e051b3a3c75c',
  'monolog/monolog' => '1.23.0@fd8c787753b3a2ad11bc60c063cff1358a32a3b4',
  'mtdowling/cron-expression' => 'v1.2.3@9be552eebcc1ceec9776378f7dcc085246cacca6',
  'mustangostang/spyc' => '0.6.3@4627c838b16550b666d15aeae1e5289dd5b77da0',
  'myclabs/deep-copy' => '1.8.1@3e01bdad3e18354c3dce54466b7fbe33a9f9f7f8',
  'nelmio/api-doc-bundle' => '2.13.3@f0a606b6362c363043e01aa079bee2b0b5eb47a2',
  'nelmio/security-bundle' => '2.5.1@fe1d31eb23c13e0918de9a66df9d315648c5d3d1',
  'nesbot/carbon' => '1.29.2@ed6aa898982f441ccc9b2acdec51490f2bc5d337',
  'nyholm/psr7' => '1.2.1@55ff6b76573f5b242554c9775792bd59fb52e11c',
  'ocramius/package-versions' => '1.5.1@1d32342b8c1eb27353c8887c366147b4c2da673c',
  'ocramius/proxy-manager' => '2.1.1@e18ac876b2e4819c76349de8f78ccc8ef1554cd7',
  'oro/calendar-bundle' => '4.1.0@4dbfced1aa4b841286c0358410ff5d23858a25b1',
  'oro/commerce' => '4.1.0@d61e5abbc89ca6719e3ae2608f4611b3d4e7ddf7',
  'oro/commerce-crm' => '4.1.0@f7cda6b34b296f400f2ef84ab29d275cdb34fe1a',
  'oro/crm' => '4.1.0@a666ff180fac14ebedd459441ace6d8923110bda',
  'oro/crm-call-bundle' => '4.1.0@78c2062d5a2e5100c4fc509c6df4e8a9f0a7e670',
  'oro/crm-dotmailer' => '4.1.0@deb310bad2457bc3aa25f11f2a027c2c98f0aba8',
  'oro/crm-hangouts-call-bundle' => '4.1.0@cef93e62930de841b7e1dc30b31ed1d0699ca591',
  'oro/crm-magento-embedded-contact-us' => '4.1.0@585dfaa03d7d69fc2de81d21ac32cb998a79921e',
  'oro/crm-task-bundle' => '4.1.0@154d5e123f3aedb5b3acb1d1017de1ae6c9f6911',
  'oro/crm-zendesk' => '4.1.0@c751f14e2a9c06c2d25b4f6998799d39939fb457',
  'oro/customer-portal' => '4.1.0@0ba9bb4fac3663f37a612198b527bcf9d7917a7f',
  'oro/doctrine-extensions' => '1.2.2@71b38bd772d68723b3999843d710b039b667426e',
  'oro/marketing' => '4.1.0@85d1caccb6f50226e6833eebd8a17718e525f76b',
  'oro/oauth2-server' => '4.1.0@48c8d918fe00ffe34087caf9d4e178a6587c4b90',
  'oro/platform' => '4.1.0@a0b69d8351842924c191d1572d5f9ffe467aab65',
  'oro/platform-serialised-fields' => '4.1.0@0c9272930ca48da001ac1abf4df9fd7b8a6ed979',
  'oro/redis-config' => '4.1.0@830b40fe3ab84a5b790d8b7cbc88a9d38323acec',
  'paragonie/random_compat' => 'v2.0.18@0a58ef6e3146256cc3dc7cc393927bcc7d1b72db',
  'php-http/client-common' => '1.10.0@c0390ae3c8f2ae9d50901feef0127fb9e396f6b4',
  'php-http/discovery' => '1.7.4@82dbef649ccffd8e4f22e1953c3a5265992b83c0',
  'php-http/httplug' => 'v1.1.0@1c6381726c18579c4ca2ef1ec1498fdae8bdf018',
  'php-http/httplug-bundle' => '1.15.2@35d281804a90f0359aa9da5b5b1f55c18aeafaf8',
  'php-http/logger-plugin' => '1.1.0@c1c6e90717ce350319b7b8bc489f1db35bb523fd',
  'php-http/message' => '1.8.0@ce8f43ac1e294b54aabf5808515c3554a19c1e1c',
  'php-http/message-factory' => 'v1.0.2@a478cb11f66a6ac48d8954216cfed9aa06a501a1',
  'php-http/promise' => 'v1.0.0@dc494cdc9d7160b9a09bd5573272195242ce7980',
  'php-http/stopwatch-plugin' => '1.3.0@de6f39c96f57c60a43d535e27122de505e683f98',
  'phpcollection/phpcollection' => '0.5.0@f2bcff45c0da7c27991bbc1f90f47c4b7fb434a6',
  'phpdocumentor/reflection-common' => '2.0.0@63a995caa1ca9e5590304cd845c15ad6d482a62a',
  'phpdocumentor/reflection-docblock' => '4.3.4@da3fd972d6bafd628114f7e7e036f45944b62e9c',
  'phpdocumentor/type-resolver' => '1.0.1@2e32a6d48972b2c1976ed5d8967145b6cec4a4a9',
  'phpoption/phpoption' => '1.7.2@77f7c4d2e65413aff5b5a8cc8b3caf7a28d81959',
  'piwik/device-detector' => '3.10.2@67e96595cd7649b7967533053fcbfbe02d5c55a3',
  'predis/predis' => 'v1.1.1@f0210e38881631afeafb56ab43405a92cafd9fd1',
  'psr/cache' => '1.0.1@d11b50ad223250cf17b86e38383413f5a6764bf8',
  'psr/container' => '1.0.0@b7ce3b176482dbbc1245ebf52b181af44c2cf55f',
  'psr/http-client' => '1.0.0@496a823ef742b632934724bf769560c2a5c7c44e',
  'psr/http-factory' => '1.0.1@12ac7fcd07e5b077433f5f2bee95b3a771bf61be',
  'psr/http-message' => '1.0.1@f6561bf28d520154e4b0ec72be95418abe6d9363',
  'psr/link' => '1.0.0@eea8e8662d5cd3ae4517c9b864493f59fca95562',
  'psr/log' => '1.1.2@446d54b4cb6bf489fc9d75f55843658e6f25d801',
  'psr/simple-cache' => '1.0.1@408d5eafb83c57f6365a3ca330ff23aa4a5fa39b',
  'ralouphie/getallheaders' => '3.0.3@120b605dfeb996808c31b6477290a714d356e822',
  'ratchet/rfc6455' => 'v0.2.6@879e48c840f8dbc296d68d6a5030673df79bd916',
  'react/cache' => 'v1.0.0@aa10d63a1b40a36a486bdf527f28bac607ee6466',
  'react/dns' => 'v1.2.0@a214d90c2884dac18d0cac6176202f247b66d762',
  'react/event-loop' => 'v0.5.3@228178a947de1f7cd9296d691878569628288c6f',
  'react/promise' => 'v2.7.1@31ffa96f8d2ed0341a57848cbb84d88b89dd664d',
  'react/promise-timer' => 'v1.5.1@35fb910604fd86b00023fc5cda477c8074ad0abc',
  'react/socket' => 'v1.3.0@10f0629ec83ea0fa22597f348623f554227e3ca0',
  'react/stream' => 'v1.1.0@50426855f7a77ddf43b9266c22320df5bf6c6ce6',
  'robloach/component-installer' => '0.2.3@908a859aa7c4949ba9ad67091e67bac10b66d3d7',
  'romanpitak/dotmailer-api-v2-client' => '3.0.0@40a8603a8c7be29afff2a8f0b816f88085f27064',
  'romanpitak/php-rest-client' => 'v1.2.1@728b6c44040a13daeb8033953f5f3cdd3dde1980',
  'salsify/json-streaming-parser' => 'v8.0.1@7d3bc67f495ba161de40f033d6bda5959542bbd0',
  'seld/cli-prompt' => '1.0.3@a19a7376a4689d4d94cab66ab4f3c816019ba8dd',
  'seld/jsonlint' => '1.7.2@e2e5d290e4d2a4f0eb449f510071392e00e10d19',
  'seld/phar-utils' => '1.0.2@84715761c35808076b00908a20317a3a8a67d17e',
  'sensio/framework-extra-bundle' => 'v5.2.4@1fdf591c4b388e62dbb2579de89c1560b33f865d',
  'snc/redis-bundle' => '2.1.13@7c8652c1811c573ae567c1bcaa5e1c65723c5907',
  'stof/doctrine-extensions-bundle' => 'v1.3.0@46db71ec7ffee9122eca3cdddd4ef8d84bae269c',
  'swiftmailer/swiftmailer' => 'v6.2.3@149cfdf118b169f7840bbe3ef0d4bc795d1780c9',
  'symfony/acl-bundle' => 'v1.0.1@5b32179c5319105cfc58884c279d76497f8a63b4',
  'symfony/contracts' => 'v1.1.8@f51bca9de06b7a25b19a4155da7bebad099a5def',
  'symfony/monolog-bundle' => 'v3.3.1@572e143afc03419a75ab002c80a2fd99299195ff',
  'symfony/polyfill-ctype' => 'v1.13.1@f8f0b461be3385e56d6de3dbb5a0df24c0c275e3',
  'symfony/polyfill-iconv' => 'v1.13.1@a019efccc03f1a335af6b4f20c30f5ea8060be36',
  'symfony/polyfill-intl-icu' => 'v1.13.1@b3dffd68afa61ca70f2327f2dd9bbeb6aa53d70b',
  'symfony/polyfill-intl-idn' => 'v1.13.1@6f9c239e61e1b0c9229a28ff89a812dc449c3d46',
  'symfony/polyfill-mbstring' => 'v1.13.1@7b4aab9743c30be783b73de055d24a39cf4b954f',
  'symfony/polyfill-php70' => 'v1.13.1@af23c7bb26a73b850840823662dda371484926c4',
  'symfony/polyfill-php72' => 'v1.13.1@66fea50f6cb37a35eea048d75a7d99a45b586038',
  'symfony/polyfill-php73' => 'v1.13.1@4b0e2222c55a25b4541305a053013d5647d3a25f',
  'symfony/psr-http-message-bridge' => 'v1.3.0@9d3e80d54d9ae747ad573cad796e8e247df7b796',
  'symfony/security-acl' => 'v3.0.4@dc8f10b3bda34e9ddcad49edc7accf61f31fce43',
  'symfony/swiftmailer-bundle' => 'v3.1.2@c0807512fb174cf16ad4c6d3c0beffc28f78f4cb',
  'symfony/symfony' => 'v4.4.2@5973dacbd9587d9bf1234dfcb546c3cbdfbc4c9e',
  'tinymce/tinymce' => '4.6.7@9f9cf10d009892009a296dff48970b079ec78c7a',
  'true/punycode' => 'v2.1.1@a4d0c11a36dd7f4e7cd7096076cab6d3378a071e',
  'twig/extensions' => 'v1.5.4@57873c8b0c1be51caa47df2cdb824490beb16202',
  'twig/twig' => 'v2.10.0@5240e21982885b76629552d83b4ebb6d41ccde6b',
  'ua-parser/uap-php' => 'v3.9.4@8c1e3c6c7698d197368bfaa0d16734c947d94e99',
  'webmozart/assert' => '1.6.0@573381c0a64f155a0d9a23f4b0c797194805b925',
  'willdurand/jsonp-callback-validator' => 'v1.1.0@1a7d388bb521959e612ef50c5c7b1691b097e909',
  'willdurand/negotiation' => 'v2.3.1@03436ededa67c6e83b9b12defac15384cb399dc9',
  'xemlock/htmlpurifier-html5' => 'v0.1.10@32cef47500fb77c2be0f160372095bec15bf0052',
  'zendframework/zend-code' => '3.4.1@268040548f92c2bfcba164421c1add2ba43abaaa',
  'zendframework/zend-diactoros' => '1.8.7@a85e67b86e9b8520d07e6415fcbcb8391b44a75b',
  'zendframework/zend-eventmanager' => '3.2.1@a5e2583a211f73604691586b8406ff7296a946dd',
  'zendframework/zend-loader' => '2.6.1@91da574d29b58547385b2298c020b257310898c6',
  'zendframework/zend-mail' => '2.10.0@d7beb63d5f7144a21ac100072c453e63860cdab8',
  'zendframework/zend-mime' => '2.7.2@c91e0350be53cc9d29be15563445eec3b269d7c1',
  'zendframework/zend-stdlib' => '3.2.1@66536006722aff9e62d1b331025089b7ec71c065',
  'zendframework/zend-validator' => '2.13.0@b54acef1f407741c5347f2a97f899ab21f2229ef',
  'behat/behat' => 'v3.4.3@d60b161bff1b95ec4bb80bb8cb210ccf890314c2',
  'behat/gherkin' => 'v4.6.0@ab0a02ea14893860bca00f225f5621d351a3ad07',
  'behat/mink' => 'dev-master@a534fe7dac9525e8e10ca68e737c3d7e5058ec83',
  'behat/mink-extension' => '2.3.1@80f7849ba53867181b7e412df9210e12fba50177',
  'behat/mink-selenium2-driver' => 'v1.3.1@473a9f3ebe0c134ee1e623ce8a9c852832020288',
  'behat/symfony2-extension' => '2.1.5@d7c834487426a784665f9c1e61132274dbf2ea26',
  'composer/xdebug-handler' => '1.4.0@cbe23383749496fe0f373345208b79568e4bc248',
  'friendsofphp/php-cs-fixer' => 'v2.12.12@48dced3bfcea050b27faad6520cc68129201f01e',
  'fzaninotto/faker' => 'v1.9.1@fc10d778e4b84d5bd315dad194661e091d307c6f',
  'instaclick/php-webdriver' => '1.4.7@b5f330e900e9b3edfc18024a5ec8c07136075712',
  'johnkary/phpunit-speedtrap' => 'v3.0.0@5f1ede99bd53fd0e5ff72669877420e801256d90',
  'mybuilder/phpunit-accelerator' => 'dev-master@91dae70fbeb7b81b9502a9d3ea80d1184c1134b1',
  'nelmio/alice' => 'v3.5.8@4a8d15ea1b70869a8b5eaaf3bf6d68b228322bc4',
  'oro/twig-inspector' => '1.0.2@5a66e476172b89c1f697fee2bf032acec63065a0',
  'pdepend/pdepend' => '2.7.0@cba74e118ce806f97fcb108c00d61ebf2a5a936e',
  'phar-io/manifest' => '1.0.3@7761fcacf03b4d4f16e7ccb606d4879ca431fcf4',
  'phar-io/version' => '2.0.1@45a2ec53a73c70ce41d55cedef9063630abaf1b6',
  'php-cs-fixer/diff' => 'v1.3.0@78bb099e9c16361126c86ce82ec4405ebab8e756',
  'phpmd/phpmd' => '2.6.1@7425e155cf22cdd2b4dd3458a7da4cf6c0201562',
  'phpspec/prophecy' => 'v1.10.2@b4400efc9d206e83138e2bb97ed7f5b14b831cd9',
  'phpunit/php-code-coverage' => '6.1.4@807e6013b00af69b6c5d9ceb4282d0393dbb9d8d',
  'phpunit/php-file-iterator' => '2.0.2@050bedf145a257b1ff02746c31894800e5122946',
  'phpunit/php-text-template' => '1.2.1@31f8b717e51d9a2afca6c9f046f5d69fc27c8686',
  'phpunit/php-timer' => '2.1.2@1038454804406b0b5f5f520358e78c1c2f71501e',
  'phpunit/php-token-stream' => '3.1.1@995192df77f63a59e47f025390d2d1fdf8f425ff',
  'phpunit/phpcov' => '5.0.0@72fb974e6fe9b39d7e0b0d44061d2ba4c49ee0b8',
  'phpunit/phpunit' => '7.5.20@9467db479d1b0487c99733bb1e7944d32deded2c',
  'sebastian/code-unit-reverse-lookup' => '1.0.1@4419fcdb5eabb9caa61a27c7a1db532a6b55dd18',
  'sebastian/comparator' => '3.0.2@5de4fc177adf9bce8df98d8d141a7559d7ccf6da',
  'sebastian/diff' => '3.0.2@720fcc7e9b5cf384ea68d9d930d480907a0c1a29',
  'sebastian/environment' => '4.2.3@464c90d7bdf5ad4e8a6aea15c091fec0603d4368',
  'sebastian/exporter' => '3.1.2@68609e1261d215ea5b21b7987539cbfbe156ec3e',
  'sebastian/finder-facade' => '1.2.3@167c45d131f7fc3d159f56f191a0a22228765e16',
  'sebastian/global-state' => '2.0.0@e8ba02eed7bbbb9e59e43dedd3dddeff4a56b0c4',
  'sebastian/object-enumerator' => '3.0.3@7cfd9e65d11ffb5af41198476395774d4c8a84c5',
  'sebastian/object-reflector' => '1.1.1@773f97c67f28de00d397be301821b06708fca0be',
  'sebastian/phpcpd' => '4.0.0@bb7953b81fb28e55964d76d5fe2dbe725d43fab3',
  'sebastian/recursion-context' => '3.0.0@5b0cd723502bac3b006cbf3dbf7a1e3fcefe4fa8',
  'sebastian/resource-operations' => '2.0.1@4d7a795d35b889bf80a0cc04e08d77cedfa917a9',
  'sebastian/version' => '2.0.1@99732be0ddb3361e16ad77b68ba41efc8e979019',
  'squizlabs/php_codesniffer' => '3.3.2@6ad28354c04b364c3c71a34e4a18b629cc3b231e',
  'symfony/class-loader' => 'v3.4.37@bcdf6ff46e115b29be3186391f29e0da82cd6f72',
  'symfony/phpunit-bridge' => 'v4.3.10@60539d90c2e80d2f33cf6c42cb1ab70362f31cae',
  'theofidry/alice-data-fixtures' => 'v1.0.1@5752bbf979a012bb804c00641478d4d3f879e51d',
  'theseer/fdomdocument' => '1.6.6@6e8203e40a32a9c770bcb62fe37e68b948da6dca',
  'theseer/tokenizer' => '1.1.3@11336f6f84e16a720dae9d8e6ed5019efa85a0f9',
  'oro/commerce-crm-application' => 'dev-b052dc1d59a178cee6d7870f4d304c0ca704ed32@b052dc1d59a178cee6d7870f4d304c0ca704ed32',
);

    private function __construct()
    {
    }

    /**
     * @throws \OutOfBoundsException If a version cannot be located.
     *
     * @psalm-param key-of<self::VERSIONS> $packageName
     */
    public static function getVersion(string $packageName) : string
    {
        if (isset(self::VERSIONS[$packageName])) {
            return self::VERSIONS[$packageName];
        }

        throw new \OutOfBoundsException(
            'Required package "' . $packageName . '" is not installed: check your ./vendor/composer/installed.json and/or ./composer.lock files'
        );
    }
}