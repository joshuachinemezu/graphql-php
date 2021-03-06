<?php

declare(strict_types=1);

namespace GraphQL\Tests\Server;

use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Server\ServerConfig;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;

class ServerConfigTest extends TestCase
{
    public function testDefaults() : void
    {
        $config = ServerConfig::create();
        $this->assertNull($config->getSchema());
        $this->assertNull($config->getContext());
        $this->assertNull($config->getRootValue());
        $this->assertNull($config->getErrorFormatter());
        $this->assertNull($config->getErrorsHandler());
        $this->assertNull($config->getPromiseAdapter());
        $this->assertNull($config->getValidationRules());
        $this->assertNull($config->getFieldResolver());
        $this->assertNull($config->getPersistentQueryLoader());
        $this->assertFalse($config->getDebug());
        $this->assertFalse($config->getQueryBatching());
    }

    public function testAllowsSettingSchema() : void
    {
        $schema = new Schema(['query' => new ObjectType(['name' => 'a', 'fields' => []])]);
        $config = ServerConfig::create()
            ->setSchema($schema);

        $this->assertSame($schema, $config->getSchema());

        $schema2 = new Schema(['query' => new ObjectType(['name' => 'a', 'fields' => []])]);
        $config->setSchema($schema2);
        $this->assertSame($schema2, $config->getSchema());
    }

    public function testAllowsSettingContext() : void
    {
        $config = ServerConfig::create();

        $context = [];
        $config->setContext($context);
        $this->assertSame($context, $config->getContext());

        $context2 = new \stdClass();
        $config->setContext($context2);
        $this->assertSame($context2, $config->getContext());
    }

    public function testAllowsSettingRootValue() : void
    {
        $config = ServerConfig::create();

        $rootValue = [];
        $config->setRootValue($rootValue);
        $this->assertSame($rootValue, $config->getRootValue());

        $context2 = new \stdClass();
        $config->setRootValue($context2);
        $this->assertSame($context2, $config->getRootValue());
    }

    public function testAllowsSettingErrorFormatter() : void
    {
        $config = ServerConfig::create();

        $formatter = function () {
        };
        $config->setErrorFormatter($formatter);
        $this->assertSame($formatter, $config->getErrorFormatter());

        $formatter = 'date'; // test for callable
        $config->setErrorFormatter($formatter);
        $this->assertSame($formatter, $config->getErrorFormatter());
    }

    public function testAllowsSettingErrorsHandler() : void
    {
        $config = ServerConfig::create();

        $handler = function () {
        };
        $config->setErrorsHandler($handler);
        $this->assertSame($handler, $config->getErrorsHandler());

        $handler = 'date'; // test for callable
        $config->setErrorsHandler($handler);
        $this->assertSame($handler, $config->getErrorsHandler());
    }

    public function testAllowsSettingPromiseAdapter() : void
    {
        $config = ServerConfig::create();

        $adapter1 = new SyncPromiseAdapter();
        $config->setPromiseAdapter($adapter1);
        $this->assertSame($adapter1, $config->getPromiseAdapter());

        $adapter2 = new SyncPromiseAdapter();
        $config->setPromiseAdapter($adapter2);
        $this->assertSame($adapter2, $config->getPromiseAdapter());
    }

    public function testAllowsSettingValidationRules() : void
    {
        $config = ServerConfig::create();

        $rules = [];
        $config->setValidationRules($rules);
        $this->assertSame($rules, $config->getValidationRules());

        $rules = [function () {
        },
        ];
        $config->setValidationRules($rules);
        $this->assertSame($rules, $config->getValidationRules());

        $rules = function () {
            return [function () {
            },
            ];
        };
        $config->setValidationRules($rules);
        $this->assertSame($rules, $config->getValidationRules());
    }

    public function testAllowsSettingDefaultFieldResolver() : void
    {
        $config = ServerConfig::create();

        $resolver = function () {
        };
        $config->setFieldResolver($resolver);
        $this->assertSame($resolver, $config->getFieldResolver());

        $resolver = 'date'; // test for callable
        $config->setFieldResolver($resolver);
        $this->assertSame($resolver, $config->getFieldResolver());
    }

    public function testAllowsSettingPersistedQueryLoader() : void
    {
        $config = ServerConfig::create();

        $loader = function () {
        };
        $config->setPersistentQueryLoader($loader);
        $this->assertSame($loader, $config->getPersistentQueryLoader());

        $loader = 'date'; // test for callable
        $config->setPersistentQueryLoader($loader);
        $this->assertSame($loader, $config->getPersistentQueryLoader());
    }

    public function testAllowsSettingCatchPhpErrors() : void
    {
        $config = ServerConfig::create();

        $config->setDebug(true);
        $this->assertTrue($config->getDebug());

        $config->setDebug(false);
        $this->assertFalse($config->getDebug());
    }

    public function testAcceptsArray() : void
    {
        $arr = [
            'schema'                => new Schema([
                'query' => new ObjectType(['name' => 't', 'fields' => ['a' => Type::string()]]),
            ]),
            'context'               => new \stdClass(),
            'rootValue'             => new \stdClass(),
            'errorFormatter'        => function () {
            },
            'promiseAdapter'        => new SyncPromiseAdapter(),
            'validationRules'       => [function () {
            },
            ],
            'fieldResolver'         => function () {
            },
            'persistentQueryLoader' => function () {
            },
            'debug'                 => true,
            'queryBatching'         => true,
        ];

        $config = ServerConfig::create($arr);

        $this->assertSame($arr['schema'], $config->getSchema());
        $this->assertSame($arr['context'], $config->getContext());
        $this->assertSame($arr['rootValue'], $config->getRootValue());
        $this->assertSame($arr['errorFormatter'], $config->getErrorFormatter());
        $this->assertSame($arr['promiseAdapter'], $config->getPromiseAdapter());
        $this->assertSame($arr['validationRules'], $config->getValidationRules());
        $this->assertSame($arr['fieldResolver'], $config->getFieldResolver());
        $this->assertSame($arr['persistentQueryLoader'], $config->getPersistentQueryLoader());
        $this->assertTrue($config->getDebug());
        $this->assertTrue($config->getQueryBatching());
    }

    public function testThrowsOnInvalidArrayKey() : void
    {
        $arr = ['missingKey' => 'value'];

        $this->expectException(InvariantViolation::class);
        $this->expectExceptionMessage('Unknown server config option "missingKey"');

        ServerConfig::create($arr);
    }

    public function testInvalidValidationRules() : void
    {
        $rules  = new \stdClass();
        $config = ServerConfig::create();

        $this->expectException(InvariantViolation::class);
        $this->expectExceptionMessage('Server config expects array of validation rules or callable returning such array, but got instance of stdClass');

        $config->setValidationRules($rules);
    }
}
