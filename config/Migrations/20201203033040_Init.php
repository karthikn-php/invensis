<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Class Init
 * @author Karthikeyan C <karthikn.php@gmail.com>
 */
class Init extends AbstractMigration
{
    /**
     * Migration :- Create Table ..
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     * @author Karthikeyan C <karthikn.php@gmail.com>
     * @return void
     */
    public function up()
    {
        //One Unique User
        $this->table('users', ['primary_key' => 'id'])
            ->addColumn('email', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_STRING, ['limit' => 150])
            ->addColumn('username', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_STRING, ['limit' => 150])
            ->addColumn('full_name', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_STRING, ['limit' => 150])
            ->addColumn('imageURL', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_STRING, ['limit' => 150])
            ->addColumn('created_at', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_DATETIME)
            ->addIndex('email', ['unique' => true, 'name' => 'idx_users_email'])
            ->addIndex('full_name', ['type' => 'fulltext', 'name' => 'idx_comments_full_txt'])
            ->create();
        //One User Can have Multiple Post
        $this->table('posts', ['primary_key' => 'id'])
            ->addColumn('user_id', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_INTEGER)
            ->addColumn('title', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_STRING, ['limit' => 150])
            ->addColumn('subtitle', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_STRING, ['limit' => 150])
            ->addColumn('content', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_TEXT, ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])
            ->addColumn('imageURL', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_STRING, ['limit' => 150])
            ->addColumn('post_status', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_ENUM, ['default' => 'drafted', 'values' => ['drafted', 'published']])
            ->addColumn('published_at', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_DATETIME)
            ->addForeignKey('user_id', 'users', 'id')
            ->addIndex(['title', 'subtitle', 'content'], ['type' => 'fulltext', 'name' => 'idx_post_full_txt'])
            ->create();
        //One User Can have Multiple Comments in a Post
        $this->table('comments', ['primary_key' => 'id'])
            ->addColumn('user_id', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_INTEGER)
            ->addColumn('post_id', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_INTEGER)
            ->addColumn('content', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_TEXT, ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])
            ->addColumn('created_at', \Phinx\Db\Adapter\MysqlAdapter::PHINX_TYPE_DATETIME)
            ->addForeignKey('user_id', 'users', 'id')
            ->addForeignKey('post_id', 'posts', 'id')
            ->addIndex('content', ['type' => 'fulltext', 'name' => 'idx_comments_full_txt'])
            ->create();
    }


    /**
     * Migration Rollback
     * @author Karthikeyan C <karthikn.php@gmail.com>
     * @return void
     */
    public function down()
    {
        $this->table('comments')->drop()->save();
        $this->table('posts')->drop()->save();
        $this->table('users')->drop()->save();
    }
}
