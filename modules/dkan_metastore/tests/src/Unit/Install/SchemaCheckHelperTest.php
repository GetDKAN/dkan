<?php

namespace Drupal\Tests\dkan_metastore\Unit\Install;

use PHPUnit\Framework\TestCase;

/**
 * Covers the pure schema-parse helper in dkan_metastore.install.
 *
 * @group dkan
 * @group dkan_metastore
 * @group unit
 */
class SchemaCheckHelperTest extends TestCase {

  protected function setUp(): void {
    parent::setUp();
    // The install file's helper functions aren't autoloaded; pull them in
    // explicitly. Path: tests/src/Unit/Install -> module root is ../../../..
    require_once __DIR__ . '/../../../../dkan_metastore.install';
  }

  public function testValidObjectSchemaReturnsNull(): void {
    $this->assertNull(_dkan_metastore_validate_schema_json('{"type":"object"}'));
  }

  public function testBooleanSchemaTrueReturnsNull(): void {
    $this->assertNull(_dkan_metastore_validate_schema_json('true'));
  }

  public function testBooleanSchemaFalseReturnsNull(): void {
    $this->assertNull(_dkan_metastore_validate_schema_json('false'));
  }

  public function testMalformedJsonReturnsError(): void {
    $msg = _dkan_metastore_validate_schema_json('{not json');
    $this->assertNotNull($msg);
    $this->assertStringContainsString('not valid JSON', $msg);
  }

  public function testStringSchemaReturnsError(): void {
    $msg = _dkan_metastore_validate_schema_json('"hello"');
    $this->assertNotNull($msg);
    $this->assertStringContainsString('object or boolean', $msg);
  }

  public function testNumberSchemaReturnsError(): void {
    $msg = _dkan_metastore_validate_schema_json('42');
    $this->assertNotNull($msg);
    $this->assertStringContainsString('object or boolean', $msg);
  }

  /**
   * $schema must be a string; opis throws ParseException eagerly at the
   * root-level parse step (loadObjectSchema).
   */
  public function testRootLevelStructuralErrorReturnsError(): void {
    $this->assertNotNull(_dkan_metastore_validate_schema_json('{"$schema": 42}'));
  }

  /**
   * Sub-schema errors are deferred by opis's LazySchema and surface during
   * the validate() phase. The helper covers this with the trivial validate
   * call.
   */
  public function testDeepStructuralErrorReturnsError(): void {
    $this->assertNotNull(
      _dkan_metastore_validate_schema_json('{"type":"object","properties":"not-an-object"}')
    );
  }

  /**
   * Draft-04 declaration — opis v2 rejects (only draft-06+ supported).
   * This is the headline failure mode this hook exists to detect.
   */
  public function testDraft04SchemaReturnsError(): void {
    $msg = _dkan_metastore_validate_schema_json(
      '{"$schema": "http://json-schema.org/draft-04/schema#", "type": "object"}'
    );
    $this->assertNotNull($msg);
  }

}
