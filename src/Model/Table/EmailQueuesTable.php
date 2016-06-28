<?php

namespace Gourmet\Email\Model\Table;

use Cake\Database\Schema\Table as Schema;
use Cake\ORM\Table;

class EmailQueuesTable extends Table
{
    protected function _initializeSchema(Schema $schema)
    {
        $schema->columnType('email', 'email_json');
        return $schema;
    }
}
