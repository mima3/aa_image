<?php
namespace aa_image\Model;

class ImageModel
{
    protected $database;
    protected $app;
    public function __construct($app, $database)
    {
        $this->app = $app;
        $this->database = $database;
    }

    public function __destruct()
    {
        $this->app = null;
        $this->database = null;
    }
    public function setup()
    {
        $this->database->exec(
            "CREATE TABLE IF NOT EXISTS image (
                id TEXT,
                data BLOB,
                last_access TEXT,
                PRIMARY KEY(id)
            );"
        );
        $this->database->exec(
            "CREATE INDEX IF NOT EXISTS ix_image ON image(last_access);"
        );
    }
    public function get(string $key)
    {
        $record = \ORM::for_table('image')
            ->where_equal('id', $key)
            ->find_one();
        if ($record) {
            $record->set('last_access', date("Y/m/d H:i:s"));
            $record->save();
        }
        return $record;
    }

    public function append($key, $data)
    {
        $row = \ORM::for_table('image')->create();
        $row->id = $key;
        $row->data = $data;
        $row->last_access = date("Y/m/d H:i:s");
        $row->save();
        $count = \ORM::for_table('image')->count();
        $container = $this->app->getContainer();
        $max_count = $container->get('config')['MAX_CACHE'];

        if ($count > $max_count) {
            $this->database->exec(
                "delete from image where id in (select id from image  order by last_access asc limit " . ($count - $max_count) . ");"
            );
        }
    }
}
