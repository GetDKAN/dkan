<?php


namespace phpunit\dkan_dataset;

/**
 * Class ResourceFileHashTest
 */
class ResourceFileHashTest extends \PHPUnit_Framework_TestCase
{
  private $local_resource_node;
  private $sha512;

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass()
  {
    module_enable(['filehash']);
    variable_set('filehash_algos', ["sha512" => "sha512", "md5" => 0, "sha1" => 0, "sha256" => 0]);
    variable_set('filehash_local_only', 1);
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $filename = 'gold_prices_states.csv';
    $path = join(DIRECTORY_SEPARATOR, array(__DIR__, 'files', $filename));
    $this->sha512 = hash_file('sha512', $path);
    $file = file_save_data(file_get_contents($path), 'public://' . $filename);
    $this->assertNotEmpty($file->filehash);
    $this->assertNotEmpty($this->sha512);

    $node = (object) [];
    $node->title = "Resource Local File Test Object";
    $node->type = "resource";
    $node->field_upload['und'][0]['fid'] = $file->fid;
    $node->status = 1;
    node_save($node);
    $this->local_resource_node = node_load($node->nid);
  }

  /**
   * Test file hash for uploaded file
   */
  public function test() {
    $node_wrapper = entity_metadata_wrapper('node', $this->local_resource_node);
    $file = $node_wrapper->field_upload->value();
    $this->assertNotEmpty($file);
    $this->assertNotEmpty($file->filehash);

    $hash = $file->filehash;
    $this->assertNotEmpty($hash['sha512']);
    $this->assertEquals($hash['sha512'], $this->sha512);
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown()
  {
    node_delete($this->local_resource_node->nid);
  }

  /**
   * {@inheritdoc}
   */
  public static function tearDownAfterClass() {
    // Clean enabled modules.
    module_disable(['filehash']);
  }
}