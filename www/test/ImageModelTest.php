<?php
namespace aa_image\Test;

use \PHPUnit\Framework\TestCase;


/**
 */
class ImageModelTest extends TestCase
{
    protected $model;
    protected $database;
    protected $app;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function setUp() : void
    {
        \ORM::configure('sqlite::memory:');
        $this->database = \ORM::get_db();
        $this->app = new class {
            public $config = [];
            public function __construct()
            {
                $this->config['MAX_CACHE'] = 1000;
            }

            public function getContainer()
            {
                return new class($this->config) {
                    private $config;
                    public function __construct($config) {
                        $this->config = $config;
                    }
                    public function get($name) {
                        if ($name == 'config')
                        {
                            return $this->config;
                        }
                    }
                };
            }
        };
        $this->model = new \aa_image\Model\ImageModel($this->app, $this->database);
        $this->model->setup();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function tearDown() : void
    {
        \ORM::reset_db();
    }

    public function testAppend()
    {
        $ret = $this->model->get('xxxx-xxxx-00001');
        $this->assertFalse($ret);

        $this->model->append(
            'xxxx-xxxx-00001',
            'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
        );
        $ret = $this->model->get('xxxx-xxxx-00001');
        $this->assertIsObject($ret);
        $this->assertEquals('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', $ret->data);

        $this->model->append(
            'xxxx-xxxx-00002',
            'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
        );

        $this->model->append(
            'xxxx-xxxx-00003',
            'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
        );

        $this->model->append(
            'xxxx-xxxx-00004',
            'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
        );

        $this->model->append(
            'xxxx-xxxx-00005',
            'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
        );

        $this->model->append(
            'xxxx-xxxx-00006',
            'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
        );

        $this->model->append(
            'xxxx-xxxx-00007',
            'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
        );

        $this->model->append(
            'xxxx-xxxx-00008',
            'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
        );

        $this->model->append(
            'xxxx-xxxx-00009',
            'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
        );

        $this->model->append(
            'xxxx-xxxx-00010',
            'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
        );

        $ret = $this->model->get('xxxx-xxxx-00001');
        $this->assertIsObject($ret);
        $ret = $this->model->get('xxxx-xxxx-00002');
        $this->assertIsObject($ret);
        $ret = $this->model->get('xxxx-xxxx-00003');
        $this->assertIsObject($ret);
        $ret = $this->model->get('xxxx-xxxx-00004');
        $this->assertIsObject($ret);
        $ret = $this->model->get('xxxx-xxxx-00005');
        $this->assertIsObject($ret);
        $ret = $this->model->get('xxxx-xxxx-00006');
        $this->assertIsObject($ret);
        $ret = $this->model->get('xxxx-xxxx-00007');
        $this->assertIsObject($ret);
        $ret = $this->model->get('xxxx-xxxx-00008');
        $this->assertIsObject($ret);
        $ret = $this->model->get('xxxx-xxxx-00009');
        $this->assertIsObject($ret);
        $ret = $this->model->get('xxxx-xxxx-00010');
        $this->assertIsObject($ret);

        $this->app->config['MAX_CACHE'] = 5;
        $this->model->append(
            'xxxx-xxxx-00011',
            'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
        );
        $ret = $this->model->get('xxxx-xxxx-00001');
        $this->assertFalse($ret);
        $ret = $this->model->get('xxxx-xxxx-00002');
        $this->assertFalse($ret);
        $ret = $this->model->get('xxxx-xxxx-00003');
        $this->assertFalse($ret);
        $ret = $this->model->get('xxxx-xxxx-00004');
        $this->assertFalse($ret);
        $ret = $this->model->get('xxxx-xxxx-00005');
        $this->assertFalse($ret);
        $ret = $this->model->get('xxxx-xxxx-00006');
        $this->assertFalse($ret);
        $ret = $this->model->get('xxxx-xxxx-00007');
        $this->assertIsObject($ret);
        $ret = $this->model->get('xxxx-xxxx-00008');
        $this->assertIsObject($ret);
        $ret = $this->model->get('xxxx-xxxx-00009');
        $this->assertIsObject($ret);
        $ret = $this->model->get('xxxx-xxxx-00010');
        $this->assertIsObject($ret);
        $ret = $this->model->get('xxxx-xxxx-00011');
        $this->assertIsObject($ret);

    }
}
